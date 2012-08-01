<?php
/**
 * @version: $Id: section.php 2294 2012-03-12 12:15:27Z Radek Suski $
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
 * $Date: 2012-03-12 13:15:27 +0100 (Mon, 12 Mar 2012) $
 * $Revision: 2294 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/ctrl/section.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'controller' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:08:52 PM
 */
class SPSectionCtrl extends SPController
{
    /**
     * @var string
     */
    protected $_defTask = 'view';
    /**
     * @var string
     */
    protected $_type = 'section';

    /**
     */
    protected function view()
    {
        /* determine template package */
        $tplPckg = Sobi::Cfg( 'section.template', 'default' );
        Sobi::ReturnPoint();

        /* load template config */
        $this->template();
        $this->tplCfg( $tplPckg );

        /* get limits - if defined in template config - otherwise from the section config */
        $eLimit = $this->tKey( $this->template, 'entries_limit', Sobi::Cfg( 'list.entries_limit', 2 ) );
        $eInLine = $this->tKey( $this->template, 'entries_in_line', Sobi::Cfg( 'list.entries_in_line', 2 ) );
        $cInLine = $this->tKey( $this->template, 'categories_in_line', Sobi::Cfg( 'list.categories_in_line', 2 ) );
        $cLim = $this->tKey( $this->template, 'categories_limit', -1 );
        $entriesRecursive = $this->tKey( $this->template, 'entries_recursive', Sobi::Cfg( 'list.entries_recursive', false ) );

        /* get the site to display */
        $site = SPRequest::int( 'site', 1 );
        $eLimStart = ( ( $site - 1 ) * $eLimit );

        /* get the right ordering */
        $eOrder = $this->parseOrdering( 'entries', 'eorder', $this->tKey( $this->template, 'entries_ordering', Sobi::Cfg( 'list.entries_ordering', 'name.asc' ) ) );
        $cOrder = $this->parseOrdering( 'categories', 'corder', $this->tKey( $this->template, 'categories_ordering', Sobi::Cfg( 'list.categories_ordering', 'name.asc' ) ) );

        /* get entries */
        $eCount = count( $this->getEntries( $eOrder, 0, 0, true, null, $entriesRecursive ) );
        $entries = $this->getEntries( $eOrder, $eLimit, $eLimStart, false, null, $entriesRecursive );
        $categories = array();
        if ( $cLim ) {
            $categories = $this->getCats( $cOrder, $cLim );
        }

        /* create page navigation */
        $pnc = SPLoader::loadClass( 'helpers.pagenav_' . $this->tKey( $this->template, 'template_type', 'xslt' ) );
        /* @var SPPageNavXSLT $pn */
        $pn = new $pnc( $eLimit, $eCount, $site, array( 'sid' => SPRequest::sid() ) );

        /* handle meta data */
        SPFactory::header()->objMeta( $this->_model );

        /* add pathway */
        SPFactory::mainframe()->addObjToPathway( $this->_model );
        $this->_model->countVisit();

        /* get view class */
        $class = SPLoader::loadView( $this->_type );
        $view = new $class( $this->template );
        $view->assign( $eLimit, '$eLimit' );
        $view->assign( $eLimStart, '$eLimStart' );
        $view->assign( $eCount, '$eCount' );
        $view->assign( $cInLine, '$cInLine' );
        $view->assign( $eInLine, '$eInLine' );
        $view->assign( $this->_task, 'task' );
        $view->assign( $this->_model, $this->_type );
        $view->setConfig( $this->_tCfg, $this->template );
        $view->setTemplate( $tplPckg . '.' . $this->templateType . '.' . $this->template );
        $view->assign( $categories, 'categories' );
        $view->assign( $pn->get(), 'navigation' );
        $view->assign( SPFactory::user()->getCurrent(), 'visitor' );
        $view->assign( $entries, 'entries' );
        Sobi::Trigger( $this->name(), 'View', array( &$view ) );
        $view->display( $this->_type );
    }

    /**
     * @param string $eOrder
     * @param int $eLimit
     * @param int $eLimStart
     * @return array
     */
    public function getCats( $cOrder, $cLim = 0 )
    {
        $categories = array();
        $cOrder = trim( $cOrder );
        $cLim = $cLim > 0 ? $cLim : 0;
        if ( $this->_model->getChilds( 'category' ) ) {
            /* var SPDb $db */
            $db =& SPFactory::db();
            $oPrefix = null;

            /* load needed definitions */
            SPLoader::loadClass( 'models.dbobject' );
            $cClass = SPLoader::loadModel( 'category' );
            $conditions = array();

            switch ( $cOrder ) {
                case 'name.asc':
                case 'name.desc':
                    $table = $db->join( array(
                        array( 'table' => 'spdb_language', 'as' => 'splang', 'key' => 'id' ),
                        array( 'table' => 'spdb_object', 'as' => 'spo', 'key' => 'id' )
                    ) );
                    $oPrefix = 'spo.';
                    $conditions[ 'spo.oType' ] = 'category';
                    $conditions[ 'splang.sKey' ] = 'name';
                    $conditions[ 'splang.language' ] = array( Sobi::Lang( false ), Sobi::DefLang(), 'en-GB' );
                    if ( strstr( $cOrder, '.' ) ) {
                        $cOrder = explode( '.', $cOrder );
                        $cOrder = 'sValue.' . $cOrder[ 1 ];
                    }
                    break;
                case 'position.asc':
                case 'position.desc':
                    $table = $db->join( array(
                        array( 'table' => 'spdb_relations', 'as' => 'sprl', 'key' => 'id' ),
                        array( 'table' => 'spdb_object', 'as' => 'spo', 'key' => 'id' )
                    ) );
                    $conditions[ 'spo.oType' ] = 'category';
                    $oPrefix = 'spo.';
                    break;
                default:
                    $table = 'spdb_object';
                    break;
            }

            /* check user permissions for the visibility */
            if ( Sobi::My( 'id' ) ) {
                if ( !( Sobi::Can( 'category.access.*' ) ) ) {
                    if ( Sobi::Can( 'category.access.unapproved_own' ) ) {
                        $conditions[ ] = $db->argsOr( array( 'approved' => '1', 'owner' => Sobi::My( 'id' ) ) );
                    }
                    else {
                        $conditions[ $oPrefix . 'approved' ] = '1';
                    }
                }
                if ( !( Sobi::Can( 'category.access.unpublished' ) ) ) {
                    if ( Sobi::Can( 'category.access.unpublished_own' ) ) {
                        $conditions[ ] = $db->argsOr( array( 'state' => '1', 'owner' => Sobi::My( 'id' ) ) );
                    }
                    else {
                        $conditions[ $oPrefix . 'state' ] = '1';
                    }
                }
                if ( !( Sobi::Can( 'category.access.*' ) ) ) {
                    if ( Sobi::Can( 'category.access.expired_own' ) ) {
                        $conditions[ ] = $db->argsOr( array( '@VALID' => $db->valid( $oPrefix . 'validUntil', $oPrefix . 'validSince' ), 'owner' => Sobi::My( 'id' ) ) );
                    }
                    else {
                        $conditions[ 'state' ] = '1';
                        $conditions[ '@VALID' ] = $db->valid( $oPrefix . 'validUntil', $oPrefix . 'validSince' );
                    }
                }
            }
            else {
                $conditions = array_merge( $conditions, array( $oPrefix . 'state' => '1', $oPrefix . 'approved' => '1', '@VALID' => $db->valid( $oPrefix . 'validUntil', $oPrefix . 'validSince' ) ) );
            }
            $conditions[ $oPrefix . 'id' ] = $this->_model->getChilds( 'category' );
            try {
                $db->select( $oPrefix . 'id', $table, $conditions, $cOrder, $cLim, 0, true );
                $results = $db->loadResultArray();
            }
            catch ( SPException $x ) {
                Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
            }
            Sobi::Trigger( $this->name(), 'AfterGetCategories', array( &$results ) );
            if ( $results && count( $results ) ) {
                foreach ( $results as $i => $cid ) {
                    $categories[ $i ] = $cid; // new $cClass();
                    //$categories[ $i ]->init( $cid );
                }
            }
        }
        return $categories;
    }

    protected function userPermissionsQuery( &$conditions, $oPrefix = null )
    {
        $db =& SPFactory::db();
        if ( !( Sobi::Can( 'entry.access.*' ) ) ) {
            if ( Sobi::Can( 'entry.access.unpublished_own' ) ) {
                $conditions[ ] = $db->argsOr( array( 'state' => '1', 'owner' => Sobi::My( 'id' ) ) );
                if( !( Sobi::Can( 'entry.access.unapproved_own' ) || Sobi::Can( 'entry.access.unapproved_any' ) ) ) {
                    $conditions[ $oPrefix . 'approved' ] = '1';
                }
            }
            else {
                $conditions[ $oPrefix . 'state' ] = '1';
            }
        }
        if ( !( Sobi::Can( 'entry.access.*' ) ) ) {
            // @todo: expired permission
            if ( Sobi::Can( 'entry.access.expired_own' ) ) {
                $conditions[ ] = $db->argsOr( array( '@VALID' => $db->valid( $oPrefix . 'validUntil', $oPrefix . 'validSince' ), 'owner' => Sobi::My( 'id' ) ) );
            }
            else {
                // conflicts with "entry.access.unpublished_own" See #521
                //$conditions[ 'state' ] = '1';
                $conditions[ '@VALID' ] = $db->valid( $oPrefix . 'validUntil', $oPrefix . 'validSince' );
            }
        }
        return $conditions;
    }

    /**
     * @param string $eOrder
     * @param int $eLimit
     * @param int $eLimStart
     * @return array
     */
    public function getEntries( $eOrder, $eLimit = null, $eLimStart = null, $count = false, $conditions = array(), $entriesRecursive = false, $pid = 0 )
    {
        /* var SPDb $db */
        $db =& SPFactory::db();
        $eClass = SPLoader::loadModel( 'entry' );
        $entries = array();
        $eDir = 'asc';
        $oPrefix = null;
        $conditions = is_array( $conditions ) ? $conditions : array();

        /* get the ordering and the direction */
        if ( strstr( $eOrder, '.' ) ) {
            $eOr = explode( '.', $eOrder );
            $eOrder = array_shift( $eOr );
            $eDir = implode( '.', $eOr );
        }
//
//        if ( strstr( $eOrder, '.' ) ) {
//            $eOrder = explode( '.', $eOrder );
//            $eDir = $eOrder[ 1 ];
//            $eOrder = $eOrder[ 0 ];
//        }
        $pid = $pid ? $pid : SPRequest::sid();
        /* if sort by name, then sort by the name field */
        if ( $eOrder == 'name' ) {
            $eOrder = SPFactory::config()->nameField()->get( 'fid' );
        }
        if ( $entriesRecursive ) {
            $pids = $this->_model->getChilds( 'category', true );
            if ( is_array( $pids ) ) {
                $pids = array_keys( $pids );
            }
            $pids[ ] = SPRequest::sid();
            $conditions[ 'sprl.pid' ] = $pids;
        }
        else {
            $conditions[ 'sprl.pid' ] = $pid;
        }
        if ( $pid == -1 ) {
            unset( $conditions[ 'sprl.pid' ] );
        }

        /* sort by field */
        if ( strstr( $eOrder, 'field_' ) ) {
            static $field = null;
            $specificMethod = false;
            if ( !$field ) {
                try {
                    $db->select( 'fieldType', 'spdb_field', array( 'nid' => $eOrder, 'section' => Sobi::Section() ) );
                    $fType = $db->loadResult();
                }
                catch ( SPException $x ) {
                    Sobi::Error( $this->name(), SPLang::e( 'CANNOT_DETERMINE_FIELD_TYPE', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
                }
                if ( $fType ) {
                    $field = SPLoader::loadClass( 'opt.fields.' . $fType );
                }
            }
            if ( $field && method_exists( $field, 'sortBy' ) ) {
                $table = null;
                $oPrefix = null;
                $specificMethod = call_user_func_array( array( $field, 'sortBy' ), array( &$table, &$conditions, &$oPrefix, &$eOrder, &$eDir ) );
            }
            if ( !$specificMethod ) {
                $table = $db->join(
                    array(
                        array( 'table' => 'spdb_field', 'as' => 'fdef', 'key' => 'fid' ),
                        array( 'table' => 'spdb_field_data', 'as' => 'fdata', 'key' => 'fid' ),
                        array( 'table' => 'spdb_object', 'as' => 'spo', 'key' => array( 'fdata.sid', 'spo.id' ) ),
                        array( 'table' => 'spdb_relations', 'as' => 'sprl', 'key' => array( 'fdata.sid', 'sprl.id' ) ),
                    )
                );
                $oPrefix = 'spo.';
                $conditions[ 'spo.oType' ] = 'entry';
                $conditions[ 'fdef.nid' ] = $eOrder;
                $eOrder = 'baseData.' . $eDir;
            }
        }
        else {
            $table = $db->join( array(
                array( 'table' => 'spdb_relations', 'as' => 'sprl', 'key' => 'id' ),
                array( 'table' => 'spdb_object', 'as' => 'spo', 'key' => 'id' )
            ) );
            $conditions[ 'spo.oType' ] = 'entry';
            $eOrder = $eOrder . '.' . $eDir;
            $oPrefix = 'spo.';
            if ( strstr( $eOrder, 'valid' ) ) {
                $eOrder = $oPrefix . $eOrder;
            }
        }

        /* check user permissions for the visibility */
        if ( Sobi::My( 'id' ) ) {
            $this->userPermissionsQuery( $conditions, $oPrefix );
            if( isset( $conditions[ $oPrefix . 'state' ] ) && $conditions[ $oPrefix . 'state' ] ) {
                $conditions[ 'sprl.copy' ] = 0;
            }
        }
        else {
            $conditions = array_merge( $conditions, array( $oPrefix . 'state' => '1', '@VALID' => $db->valid( $oPrefix . 'validUntil', $oPrefix . 'validSince' ) ) );
            $conditions[ 'sprl.copy' ] = '0';
        }
        try {
            $db->select( $oPrefix . 'id', $table, $conditions, $eOrder, $eLimit, $eLimStart, true );
            $results = $db->loadResultArray();
        }
        catch ( SPException $x ) {
            Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
        }
        Sobi::Trigger( $this->name(), 'AfterGetEntries', array( &$results, $count ) );
        if ( count( $results ) && !$count ) {
            $memLimit = ( int )ini_get( 'memory_limit' ) * 2097152;
            foreach ( $results as $i => $sid ) {
                // it needs too much memory moving the object creation to the view
                //$entries[ $i ] = SPFactory::Entry( $sid );
                $entries[ $i ] = $sid;
            }
        }
        if ( $count ) {
            return $results;
        }
        return $entries;
    }
}
