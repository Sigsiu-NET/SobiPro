<?php
/**
 * @version: $Id: entry.php 2182 2012-01-23 16:25:58Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-01-23 17:25:58 +0100 (Mon, 23 Jan 2012) $
 * $Revision: 2182 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/adm/entry.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'controller' );
SPLoader::loadController( 'entry' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:38:30 PM
 */
class SPEntryAdmCtrl extends SPEntryCtrl
{
    /**
     */
    public function execute()
    {
        $r = false;
        switch ( $this->_task ) {
            case 'edit':
            case 'add':
                $r = true;
                SPLoader::loadClass( 'html.input' );
                $this->editForm();
                break;
            case 'approve':
            case 'unapprove':
                $r = true;
                $this->approval( $this->_task == 'approve' );
                break;
            case 'up':
            case 'down':
                $r = true;
                $this->singleReorder( $this->_task == 'up' );
                break;
            case 'clone':
                $r = true;
                $this->_model = null;
                SPRequest::set( 'entry_id', 0, 'post' );
                SPRequest::set( 'entry_state', 0, 'post' );
                $this->save( false, true );
                break;
            case 'reorder':
                $r = true;
                $this->reorder();
                break;
            case 'search':
                $this->search();
                break;
            default:
                /* case plugin didn't registered this task, it was an error */
                if ( !parent::execute() ) {
                    Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
                }
                else {
                    $r = true;
                }
                break;
        }
        return $r;
    }

    protected function search()
    {
        $term = SPRequest::string( 'search', null, false /*, 'post' */ );
        $fid = Sobi::Cfg( 'entry.name_field' );
        /**
         * @var $field SPField
         */
        $field = SPFactory::Model( 'field' );
        $field->init( $fid );
        $data = $field->searchSuggest( $term, Sobi::Section(), true, true );
        SPFactory::mainframe()->cleanBuffer();
        echo json_encode( $data );
        exit;
    }

    protected function save( $apply, $clone = false )
    {
        if ( !( SPFactory::mainframe()->checkToken() ) ) {
            Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
        }
        $sid = SPRequest::sid() ? SPRequest::sid() : SPRequest::int( 'entry_id' );
        $apply = ( int )$apply;
        if ( !$this->_model ) {
            $this->setModel( SPLoader::loadModel( $this->_type ) );
        }
        $this->_model->init( $sid );
        $this->_model->getRequest( $this->_type );
        $this->authorise( $this->_model->get( 'id' ) ? 'edit' : 'add' );
        $this->_model->save();
        $sid = $this->_model->get( 'id' );
        if ( $apply || $clone ) {
            if ( $clone ) {
                $msg = Sobi::Txt( 'MSG.OBJ_CLONED', array( 'type' => Sobi::Txt( $this->_model->get( 'oType' ) ) ) );
            }
            else {
                $msg = Sobi::Txt( 'MSG.OBJ_SAVED', array( 'type' => Sobi::Txt( $this->_model->get( 'oType' ) ) ) );
            }
            Sobi::Redirect( Sobi::Url( array( 'task' => $this->_type . '.edit', 'sid' => $sid ) ), $msg );
        }
        else {
            Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), Sobi::Txt( 'MSG.OBJ_SAVED', array( 'type' => Sobi::Txt( $this->_model->get( 'oType' ) ) ) ) );
        }
    }

    /**
     */
    protected function approval( $approve )
    {
        $sids = SPRequest::arr( 'e_sid', array() );
        if ( !count( $sids ) ) {
            $sids = array( $this->_model->get( 'id' ) );
        }
        foreach ( $sids as $sid ) {
            try {
                SPFactory::db()->update( 'spdb_object', array( 'approved' => $approve ? 1 : 0 ), array( 'id' => $sid, 'oType' => 'entry' ) );
                if ( $approve ) {
                    SPFactory::Entry( $sid )->approveFields( $approve );
                }
                else {
                    SPFactory::cache()->deleteObj( 'entry', $sid );
                }
            } catch ( SPException $x ) {
                Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
            }
            SPFactory::cache()->purgeSectionVars();
        }
        Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), $approve ? 'Entry has been approved' : 'Entry has been un-approved' );
    }

    /**
     */
    private function reorder()
    {
        /* @var SPdb $db */
        $db =& SPFactory::db();
        $sids = SPRequest::arr( 'ep_sid', array() );
        /* re-order it to the valid ordering */
        $order = array();
        asort( $sids );
        $eLimStart = SPRequest::int( 'eLimStart', 0 );
        $eLimit = Sobi::GetUserState( 'adm.entries.limit', 'elimit', Sobi::Cfg( 'adm_list.entries_limit', 25 ) );
        $LimStart = $eLimStart ? ( ( $eLimStart - 1 ) * $eLimit ) : $eLimStart;

        if ( count( $sids ) ) {
            $c = 0;
            foreach ( $sids as $sid => $pos ) {
                $order[ ++$c ] = $sid;
            }
        }
        $pid = SPRequest::int( 'sid' );
        foreach ( $order as $sid ) {
            try {
                $db->update( 'spdb_relations', array( 'position' => ++$LimStart ), array( 'id' => $sid, 'oType' => 'entry', 'pid' => $pid ) );
                SPFactory::cache()->deleteObj( 'entry', $sid );
            } catch ( SPException $x ) {
                Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
            }
        }
        Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), 'Entries are re-ordered now' );
    }

    /**
     * @param bool $up
     */
    private function singleReorder( $up )
    {
        /* @var SPdb $db */
        $db =& SPFactory::db();
        $eq = $up ? '<' : '>';
        $dir = $up ? 'position.desc' : 'position.asc';
        $current = $this->_model->getPosition( SPRequest::int( 'pid' ) );
        try {
            $db->select( 'position, id', 'spdb_relations', array( 'position' . $eq => $current, 'oType' => 'entry', 'pid' => SPRequest::int( 'pid' ) ), $dir, 1 );
            $interchange = $db->loadAssocList();
            if ( $interchange && count( $interchange ) ) {
                $db->update( 'spdb_relations', array( 'position' => $interchange[ 0 ][ 'position' ] ), array( 'oType' => 'entry', 'pid' => SPRequest::int( 'pid' ), 'id' => $this->_model->get( 'id' ) ), 1 );
                $db->update( 'spdb_relations', array( 'position' => $current ), array( 'oType' => 'entry', 'pid' => SPRequest::int( 'pid' ), 'id' => $interchange[ 0 ][ 'id' ] ), 1 );
            }
            else {
                $current = $up ? $current-- : $current++;
                $db->update( 'spdb_relations', array( 'position' => $current ), array( 'oType' => 'entry', 'pid' => SPRequest::int( 'pid' ), 'id' => $this->_model->get( 'id' ) ), 1 );
            }
        } catch ( SPException $x ) {
            Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
        }
        Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), "[MSG]Entry Position Changed" );
    }

    /**
     */
    private function editForm()
    {
        $sid = SPRequest::sid();
        $sid = $sid ? $sid : SPRequest::int( 'pid' );

        /* if adding new */
        if ( !$this->_model ) {
            $this->setModel( SPLoader::loadModel( 'entry' ) );
        }
        $this->_model->formatDatesToEdit();
        $id = $this->_model->get( 'id' );
        if ( !$id ) {
            $this->_model->set( 'state', 1 );
        }
        $this->_model->loadFields( Sobi::Reg( 'current_section' ), true );
        $this->_model->formatDatesToEdit();

        if ( $this->_model->isCheckedOut() ) {
            SPMainFrame::msg( Sobi::Txt( 'EN.IS_CHECKED_OUT' ), SPC::ERROR_MSG );
        }
        else {
            /* check out the model */
            $this->_model->checkOut();
        }
        /* get fields for this section */
        /* @var SPEntry $this->_model */
        $fields = $this->_model->get( 'fields' );

        if ( !count( $fields ) ) {
            throw new SPException( SPLang::e( 'CANNOT_GET_FIELDS_IN_SECTION', Sobi::Reg( 'current_section' ) ) );
        }
        $f = array();
        foreach ( $fields as $field ) {
            if ( ( $field->get( 'enabled' ) ) && $field->enabled( 'form' ) ) {
                $f[ ] = $field;
            }
        }
        /* create the validation script to check if required fields are filled in and the filters, if any, match */
        $this->createValidationScript( $fields );

        $class = SPLoader::loadView( 'entry', true );
        $view = new $class();
        $view->assign( $this->_model, 'entry' );

        /* get the categories */
        $cats = $this->_model->getCategories( true );
        if ( count( $cats ) ) {
            $tCats = array();
            foreach ( $cats as $cid ) {
                /* ROTFL ... damn I like arrays ;-) */
                $tCats2 = SPFactory::config()->getParentPath( $cid, true );
                if ( is_array( $tCats2 ) && count( $tCats2 ) ) {
                    $tCats[ ] = implode( Sobi::Cfg( 'string.path_separator' ), $tCats2 );
                }
            }
            if ( count( $tCats ) ) {
                $view->assign( implode( "\n", $tCats ), 'parent_path' );
            }
            $view->assign( implode( ", ", $cats ), 'parents' );
        }
        elseif ( $this->_model->get( 'valid' ) ) {
            $parent = ( ( $sid == Sobi::Reg( 'current_section' ) ) ? 0 : $sid );
            if ( $parent ) {
                $view->assign( implode( Sobi::Cfg( 'string.path_separator', ' > ' ), SPFactory::config()->getParentPath( $parent, true ) ), 'parent_path' );
            }
            $view->assign( $parent, 'parents' );
        }
        else {
            $n = null;
            $view->assign( $n, 'parents' );
            $view->assign( $n, 'parent_path' );
        }

        $view->assign( $this->_task, 'task' );
        $view->assign( $f, 'fields' );
        $view->assign( $id, 'id' );
        $view->assign( SPFactory::CmsHelper()->userSelect( 'entry.owner', ( $this->_model->get( 'owner' ) ? $this->_model->get( 'owner' ) : ( $this->_model->get( 'id' ) ? 0 : Sobi::My( 'id' ) ) ), true ), 'owner' );
        $view->assign( Sobi::Reg( 'current_section' ), 'sid' );
        $view->loadConfig( 'entry.edit' );
        $view->setTemplate( 'entry.edit' );
        $view->addHidden( $sid, 'pid' );
        $view->display();
    }
}
