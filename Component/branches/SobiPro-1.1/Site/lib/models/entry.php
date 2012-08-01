<?php
/**
 * @version: $Id: entry.php 2527 2012-07-02 13:57:23Z Radek Suski $
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
 * $Date: 2012-07-02 15:57:23 +0200 (Mon, 02 Jul 2012) $
 * $Revision: 2527 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/models/entry.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadModel( 'datamodel' );
SPLoader::loadModel( 'dbobject' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:14:27 PM
 */
class SPEntry extends SPDBObject implements SPDataModel
{
    /**
     * @var array
     */
    private static $types = array(
        'description' => 'html',
        'icon' => 'string',
        'showIcon' => 'int',
        'introtext' => 'string',
        'showIntrotext' => 'int',
        'parseDesc' => 'int',
        'position' => 'int'
    );
    /**
     * @var
     */
    protected $oType = 'entry';
    /**
     * @var array categories where the entry belongs to
     */
    protected $categories = array();
    /**
     * @var array
     */
    protected $fields = array();
    /**
     * @var array
     */
    protected $fieldsNids = array();
    /**
     * @var array
     */
    protected $fieldsIds = array();
    /**
     * @var string
     */
    protected $nameField = null;
    /**
     * @var array
     */
    private $data = array();
    /**
     * @var bool
     */
    private $_loaded = false;
    /**
     * @var int
     */
    public $position = 0;
    /**
     * @var bool
     */
    protected $valid = true;
    /**
     * @var int
     */
    public $primary = 0;
    /**
     * @var string
     */
    public $url = '';

    public function __construct()
    {
        parent::__construct();
        if ( Sobi::Cfg( 'entry.publish_limit', 0 ) ) {
            $this->validUntil = gmdate( 'Y-m-d H:i:s', time() + ( Sobi::Cfg( 'entry.publish_limit', 0 ) * 24 * 3600 ) );
        }
    }

    /**
     * Full init
     */
    public function loadTable()
    {
        if ( $this->id ) {
            $cats = $this->getCategories( true );
            $this->section = Sobi::Section();
            if ( isset( $cats[ 0 ] ) ) {
                $sid = SPFactory::config()->getParentPath( $cats[ 0 ], false );
                $this->section = isset( $sid[ 0 ] ) && $sid[ 0 ] ? $sid[ 0 ] : Sobi::Section();
            }
            // we need to get some information from the object table
            $this->valid = count( $this->categories ) > 0;
            $this->loadFields();
            Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( &$this->fields ) );
            if ( count( $this->fields ) ) {
                foreach ( $this->fields as $field ) {
                    /* create field aliases */
                    $this->fieldsIds[ $field->get( 'id' ) ] = $field;
                    $this->fieldsNids[ $field->get( 'nid' ) ] = $field;
                }
            }
            $this->primary =& $this->parent;
            $this->url = Sobi::Url( array( 'title' => $this->get( 'name' ), 'pid' => $this->get( 'primary' ), 'sid' => $this->id ) );
            Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( &$this->fieldsIds, &$this->fieldsNids ) );
        }
    }

    /**
     * Std. getter. Returns a property of the object or the default value if the property is not set.
     * @param string $attr
     * @param mixed $default
     * @return mixed
     */
    public function get( $attr, $default = null )
    {
        if ( strstr( $attr, 'field_' ) ) {
            if ( isset( $this->fieldsNids[ trim( $attr ) ] ) ) {
                return $this->fieldsNids[ trim( $attr ) ]->data();
            }
        }
        else {
            return parent::get( $attr, $default );
        }
    }

    /**
     * After an entry has been approved, all fields cp
     * @return void
     */
    public function approveFields( $approve )
    {
        Sobi::Trigger( $this->name(), 'Approve', array( $this->id, $approve, &$this->fields ) );
        SPFactory::cache()->purgeSectionVars();
        SPFactory::cache()->deleteObj( 'entry', $this->id );
        foreach ( $this->fields as $field ) {
            //$field->enabled( 'form' );
            $field->approve( $this->id );
        }
        if ( $approve ) {
            $db =& SPFactory::db();
            try {
                $count = $db->select( 'COUNT(id)', 'spdb_relations', array( 'id' => $this->id, 'copy' => '1', 'oType' => 'entry' ) )->loadResult();
                if ( $count ) {
                    $db->delete( 'spdb_relations', array( 'id' => $this->id, 'copy' => '0', 'oType' => 'entry' ) );
                    $db->update( 'spdb_relations', array( 'copy' => '0' ), array( 'id' => $this->id, 'copy' => '1', 'oType' => 'entry' ) );
                }
            }
            catch ( SPException $x ) {
                Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
            }
        }
        SPFactory::cache()->purgeSectionVars();
        SPFactory::cache()->deleteObj( 'entry', $this->id );
        Sobi::Trigger( $this->name(), 'AfterApprove', array( $this->id, $approve ) );
    }

    /**
     * @param int $state
     * @param string $reason
     */
    public function changeState( $state, $reason = null )
    {
        Sobi::Trigger( $this->name(), 'ChangeState', array( $this->id, $state ) );
        $db =& SPFactory::db();
        try {
            $db->update( 'spdb_object', array( 'state' => ( int )$state, 'stateExpl' => $reason ), array( 'id' => $this->id ) );
        }
        catch ( SPException $x ) {
            Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
        }
        foreach ( $this->fields as $field ) {
            $field->changeState( $this->id, $state );
        }
        SPFactory::cache()->purgeSectionVars();
        SPFactory::cache()->deleteObj( 'entry', $this->id );
        Sobi::Trigger( $this->name(), 'AfterChangeState', array( $this->id, $state ) );
    }

    /**
     * @param mixed $sid
     * @return SPField
     */
    public function & getField( $ident )
    {
        $field = null;
        if ( is_int( $ident ) ) {
            if ( isset( $this->fieldsIds[ $ident ] ) ) {
                $field =& $this->fieldsIds[ $ident ];
            }
            else {
                throw new SPException( SPLang::e( 'THERE_IS_NO_SUCH_FIELD', $ident ) );
            }
        }
        else {
            if ( isset( $this->fieldsNids[ $ident ] ) ) {
                $field =& $this->fieldsNids[ $ident ];
            }
            else {
                throw new SPException( SPLang::e( 'THERE_IS_NO_SUCH_FIELD', $ident ) );
            }
        }
        Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( $ident, &$this->fieldsIds, &$this->fieldsNids ) );
        return $field;
    }

    /**
     * @param string $by
     * @return SPField[]
     */
    public function & getFields( $by = 'name' )
    {
        $fields =& $this->fields;
        switch ( $by ) {
            case 'name':
            case 'nid':
                $fields =& $this->fieldsNids;
                break;
            case 'id':
            case 'fid':
                $fields =& $this->fieldsIds;
                break;
        }
        Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( &$fields ) );
        return $fields;
    }

    /**
     * @return int
     */
    public function getPosition( $cid )
    {
        if ( $this->id ) {
            if ( !( count( $this->categories ) ) ) {
                $this->getCategories();
            }
        }
        return isset( $this->categories[ $cid ][ 'position' ] ) ? $this->categories[ $cid ][ 'position' ] : 0;
    }

    /**
     * Return the primary category for this entry
     * @return array
     */
    public function getPrimary()
    {
        if ( !( count( $this->categories ) ) ) {
            $this->getCategories();
        }
        return $this->categories[ $this->primary ];
    }

    /**
     * @return array
     */
    public function getCategories( $arr = false )
    {
        if ( $this->id ) {
            if ( !( count( $this->categories ) ) ) {
                /* @var SPdb $db */
                $db =& SPFactory::db();
                /* get fields */
                try {
                    $c = array( 'id' => $this->id, 'oType' => 'entry' );
                    if ( !( $this->approved || ( SPRequest::task() == 'entry.edit' || ( Sobi::Can( 'entry.access.unapproved_any' ) ) ) ) ) {
                        $c[ 'copy' ] = '0';
                    }
                    $db->select( array( 'pid', 'position', 'validSince', 'validUntil' ), 'spdb_relations', $c, 'position' );
                    $categories = $db->loadAssocList( 'pid' );
                    /* validate categories - case some of them has been deleted */
                    $cats = array_keys( $categories );
                    if ( count( $cats ) ) {
                        $cats = $db->select( 'id', 'spdb_object', array( 'id' => $cats ) )->loadResultArray();
                    }
                    if ( count( $categories ) ) {
                        foreach ( $categories as $i => $c ) {
                            if ( !( in_array( $i, $cats ) ) ) {
                                unset( $categories[ $i ] );
                            }
                        }
                    }
                    /* push the main category to the top of this array */
                    if ( isset( $categories [ $this->parent ] ) ) {
                        $main = $categories [ $this->parent ];
                        unset( $categories[ $this->parent ] );
                        $this->categories[ $this->parent ] = $main;
                    }
                    foreach ( $categories as $cid => $cat ) {
                        $this->categories[ $cid ] = $cat;
                    }
                    if ( $this->categories ) {
                        $labels = SPLang::translateObject( array_keys( $this->categories ), 'name', 'category' );
                        foreach ( $labels as $t ) {
                            $this->categories[ $t[ 'id' ] ][ 'name' ] = $t[ 'value' ];
                        }
                    }
                    Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( &$this->categories ) );
                }
                catch ( SPException $x ) {
                    Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_RELATIONS_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
                }
            }
            if ( $arr ) {
                return array_keys( $this->categories );
            }
            else {
                return $this->categories;
            }
        }
        else {
            return array();
        }
    }

    private function nameField()
    {
        /* get the field id of the field contains the entry name */
        if ( $this->section == Sobi::Section() || !( $this->section ) ) {
            $nameField = Sobi::Cfg( 'entry.name_field' );
        }
        else {
            $nameField = SPFactory::db()
                    ->select( 'sValue', 'spdb_config', array( 'section' => $this->section, 'sKey' => 'name_field', 'cSection' => 'entry' ) )
                    ->loadResult();
        }
        return $nameField;
    }

    public function validateCache()
    {
        static $remove = array( 'name', 'nid', 'metaDesc', 'metaKeys', 'metaRobots', 'options', 'oType', 'parent' );
        $core = SPFactory::object( $this->id );
        foreach ( $core as $a => $v ) {
            if ( !( in_array( $a, $remove ) ) ) {
                $this->_set( $a, $v );
            }
        }
    }

    /**
     * @param int $sid
     * @return void
     */
    public function loadFields( $sid = 0, $enabled = false )
    {
        $sid = $sid ? $sid : $this->section;
        /* @var SPdb $db */
        $db =& SPFactory::db();

        static $fields = array();
        static $lang = null;
        $lang = $lang ? $lang : Sobi::Lang( false );
        if ( !isset( $fields[ $sid ] ) ) {
            /* get fields */
            try {
                if ( $enabled ) {
                    $db->select( '*', 'spdb_field', array( 'section' => $sid, 'enabled' => 1 ), 'position' );
                }
                else {
                    $db->select( '*', 'spdb_field', array( 'section' => $sid ), 'position' );
                }
                $fields[ $sid ] = $db->loadObjectList();
                Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( &$fields ) );
            }
            catch ( SPException $x ) {
                Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELDS_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
            }
        }
        $nameField = $this->nameField();

        if ( !( $this->_loaded ) ) {
            if ( count( $fields[ $sid ] ) ) {
                $fmod = SPLoader::loadModel( 'field', defined( 'SOBIPRO_ADM' ) );
                /* if it is an entry - prefetch the basic fields data */
                if ( $this->id ) {
                    $noCopy = $this->checkCopy();
                    /* in case the entry is approved, or we are aditing an entry, or the user can see unapproved changes */
                    if ( $this->approved || $noCopy ) {
                        $ordering = 'copy.desc';
                    }
                    /* otherweise - if the entry is not approved, get the non-copies first */
                    else {
                        $ordering = 'copy.asc';
                    }
                    try {
                        $fdata = $db
                                ->select( '*', 'spdb_field_data', array( 'sid' => $this->id ), $ordering )
                                ->loadObjectList();
                        $fieldsdata = array();
                        if ( count( $fdata ) ) {
                            foreach ( $fdata as $data ) {
                                /* if it has been already set - check if it is not better language choose */
                                if ( isset( $fieldsdata[ $data->fid ] ) ) {
                                    /*
                                              * I know - the whole thing could be shorter
                                              * but it is better to understand and debug this way
                                              */
                                    if ( $data->lang == $lang ) {
                                        if ( $noCopy ) {
                                            if ( !( $data->copy ) ) {
                                                $fieldsdata[ $data->fid ] = $data;
                                            }
                                        }
                                        else {
                                            $fieldsdata[ $data->fid ] = $data;
                                        }
                                    }
                                    /* set for cache other lang */
                                    else {
                                        $fieldsdata[ 'langs' ][ $data->lang ][ $data->fid ] = $data;
                                    }
                                }
                                else {
                                    if ( $noCopy ) {
                                        if ( !( $data->copy ) ) {
                                            $fieldsdata[ $data->fid ] = $data;
                                        }
                                    }
                                    else {
                                        $fieldsdata[ $data->fid ] = $data;
                                    }
                                }
                            }
                        }
                        unset( $fdata );
                        SPFactory::registry()->set( 'fields_data_' . $this->id, $fieldsdata );
                    }
                    catch ( SPException $x ) {
                        Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
                    }
                }
                foreach ( $fields[ $sid ] as $f ) {
                    /* @var SPField $field */
                    $field = new $fmod();
                    $field->extend( $f );
                    $field->loadData( $this->id );
                    $this->fields[ ] = $field;
                    $this->fieldsNids[ $field->get( 'nid' ) ] = $this->fields[ count( $this->fields ) - 1 ];
                    $this->fieldsIds[ $field->get( 'fid' ) ] = $this->fields[ count( $this->fields ) - 1 ];
                    /* case it was the name field */
                    if ( $field->get( 'fid' ) == $nameField ) {
                        /* get the entry name */
                        $this->name = $field->getRaw();
                        /* save the nid (name id) of the field where the entry name is saved */
                        $this->nameField = $field->get( 'nid' );
                    }
                }
                $this->_loaded = true;
            }
        }
    }

    private function checkCopy()
    {
        return !(
                in_array( SPRequest::task(), array( 'entry.approve', 'entry.edit', 'entry.save', 'entry.submit', 'entry.payment' ) ) ||
                        Sobi::Can( 'entry.access.unapproved_any' ) ||
                        ( $this->owner == Sobi::My( 'id' ) && Sobi::Can( 'entry.manage.own' ) ) ||
                        Sobi::Can( 'entry.manage.*' )
        );
    }

    /**
     * @return array
     */
    protected function types()
    {
        return self::$types;
    }

    /**
     */
    public function delete()
    {
        parent::delete();
        Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( $this->id ) );
        foreach ( $this->fields as $field ) {
            $field->deleteData( $this->id );
        }
        SPFactory::cache()->purgeSectionVars();
        SPFactory::cache()->deleteObj( 'entry', $this->id );
    }


    /**
     * (non-PHPdoc)
     * @see Site/lib/models/SPDBObject#save()
     */
    public function save( $request = 'post' )
    {
        Sobi::Trigger( $this->name(), ucfirst( __FUNCTION__ ), array( $this->id ) );
        /* save the base object data */
        /* @var SPdb $db */
        $this->loadFields( Sobi::Reg( 'current_section' ) );
        $db =& SPFactory::db();
        $db->transaction();
        if ( !$this->nid ) {
            $this->nid = SPRequest::string( $this->nameField, null, false, $request );
            $this->name = $this->nid;
        }
        if ( Sobi::Cfg( 'entry.publish_limit', 0 ) && !( defined( 'SOBI_ADM_PATH' ) ) ) {
            SPRequest::set( 'entry_createdTime', 0, $request );
            SPRequest::set( 'entry_validSince', 0, $request );
            SPRequest::set( 'entry_validUntil', 0, $request );
            $this->validUntil = gmdate( 'Y-m-d H:i:s', time() + ( Sobi::Cfg( 'entry.publish_limit', 0 ) * 24 * 3600 ) );
        }
        $preState = array(
            'approved' => $this->approved,
            'state' => $this->state,
            'new' => !( $this->id )
        );
        parent::save( $request );
        $nameField = $this->nameField();
        /* get the fields for this section */
        foreach ( $this->fields as $field ) {
            /* @var $field SPField */
            try {
                if ( $field->enabled( 'form', $preState[ 'new' ] ) ) {
                    $field->saveData( $this, $request );
                }
                if ( $field->get( 'id' ) == $nameField ) {
                    /* get the entry name */
                    $this->name = $field->getRaw();
                    /* save the nid (name id) of the field where the entry name is saved */
                    $this->nameField = $field->get( 'nid' );
                }
            }
            catch ( SPException $x ) {
                if ( SPRequest::task() != 'entry.clone' ) {
                    /**
                     * @todo
                     *  - need to save these data in cache and relocate user back to the edit form
                     *  - what do we do with the already saved data if it was new entry ??
                     *  - what if the user breaks the submission
                     **/
                    $db->rollback();
                    // @todo: it should goes to the controller
                    Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_FIELS_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
                    Sobi::Redirect(
                        Sobi::GetUserState( 'back_url', Sobi::Url( array( 'task' => 'entry.add', 'sid' => Sobi::Section() ) ) ),
                        SPLang::e( 'CANNOT_SAVE_FIELS_DATA', $x->getMessage() ),
                        SPC::ERROR_MSG,
                        true
                    );
                    exit();
                }
                else {
                    Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_FIELS_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
                }
            }
        }
        $values = array();
        /* get categories */
        $cats = SPRequest::arr( 'entry_parent', array(), $request );
        /* by default it shoul be comma saparated string */
        if ( !( count( $cats ) ) ) {
            $cats = SPRequest::string( 'entry_parent', null, $request );
            if ( strlen( $cats ) && strpos( $cats, ',' ) ) {
                $cats = explode( ',', $cats );
                foreach ( $cats as $i => $cat ) {
                    $c = ( int )trim( $cat );
                    if ( $c ) {
                        $cats[ $i ] = $c;
                    }
                    else {
                        unset( $cats[ $i ] );
                    }
                }
            }
            else {
                $cats = array( ( int )$cats );
            }
        }
        if ( is_array( $cats ) && count( $cats ) ) {
            /* get the ordering in these categories */
            try {
                $db->select( 'pid, MAX(position)', 'spdb_relations', array( 'pid' => $cats, 'oType' => 'entry' ), null, 0, 0, false, 'pid' );
                $cPos = $db->loadAssocList( 'pid' );
                $currPos = $db->select( array( 'pid', 'position' ), 'spdb_relations', array( 'id' => $this->id, 'oType' => 'entry' ) )->loadAssocList( 'pid' );
            }
            catch ( SPException $x ) {
                Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
            }
            /* set the right position */
            foreach ( $cats as $i => $cat ) {
                $copy = 0;
                if ( !( $this->approved ) ) {
                    $copy = isset( $this->categories[ $cats[ $i ] ] ) ? 0 : 1;
                }
                else {
                    $db->delete( 'spdb_relations', array( 'id' => $this->id, 'oType' => 'entry' ) );
                }
                if ( isset( $currPos[ $cat ] ) ) {
                    $pos = $currPos[ $cat ][ 'position' ];
                }
                else {
                    $pos = isset( $cPos[ $cat ] ) ? $cPos[ $cat ][ 'MAX(position)' ] : 0;
                    $pos++;
                }
                $values[ ] = array( 'id' => $this->id, 'pid' => $cats[ $i ], 'oType' => 'entry', 'position' => $pos, 'validSince' => $this->validSince, 'validUntil' => $this->validUntil, 'copy' => $copy );
            }
            try {
                $db->insertArray( 'spdb_relations', $values, true );
            }
            catch ( SPException $x ) {
                Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
            }
        }
        else {
            throw new SPException( SPLang::e( 'MISSING_CATEGORY' ) );
        }
        /* trigger possible state changes */
        if ( $preState[ 'approved' ] != $this->approved ) {
            if( $this->approved ) {
                $this->approveFields( true );
	            // it's being done by the method above - removing
                //Sobi::Trigger( $this->name(), 'AfterApprove', array( $this->id, $this->approved ) );
            }
        }
        if ( $preState[ 'state' ] != $this->state ) {
            Sobi::Trigger( $this->name(), 'AfterChangeState', array( $this->id, $this->state ) );
        }
        SPFactory::cache()->purgeSectionVars();
        SPFactory::cache()->deleteObj( 'entry', $this->id );
        Sobi::Trigger( $this->name(), 'After' . ucfirst( __FUNCTION__ ), array( &$this ) );
    }
}
