<?php
/**
 * @version: $Id: field.php 2335 2012-03-28 10:30:14Z Sigrid Suski $
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
 * $Date: 2012-03-28 12:30:14 +0200 (Wed, 28 Mar 2012) $
 * $Revision: 2335 $
 * $Author: Sigrid Suski $
 * File location: components/com_sobipro/lib/models/field.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadModel( 'field' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 09-Mar-2009 12:00:45 PM
 */
class SPField extends SPObject
{
    /**
     * @var stdClass
     */
    protected $_fData = null;
    /**
     * @var string
     */
    protected $lang = null;
    /**
     * @var mixed
     */
    protected $_rawData = null;
    /**
     * @var string
     */
    protected $_data = null;
    /**
     * @var string
     */
    protected $fieldType = null;
    /**
     * @var SPFieldInterface
     */
    protected $_type = null;
    /**
     * @var string
     */
    protected $type = null;
    /**
     * @var bool
     */
    protected $addToMetaDesc = null;
    /**
     * @var string
     */
    private $_loaded = false;
    /**
     * @var bool
     */
    private $_class = null;
    /**
     * @var bool
     */
    protected $addToMetaKeys = null;
    /**
     * @var int
     */
    protected $adminField = null;
    /**
     * @var string
     */
    protected $nid = null;
    /**
     * @var string
     */
    protected $dataType = null;
    /**
     * @var int
     */
    protected $priority = 5;
    /**
     * @var string
     */
    protected $description = null;
    /**
     * @var string
     */
    protected $defaultValue = null;
    /**
     * @var int
     */
    protected $editLimit = null;
    /**
     * @var bool
     */
    protected $_off = false;
    /**
     * @var bool
     */
    protected $enabled = null;
    /**
     * @var double
     */
    protected $fee = 0;
    /**
     * @var int
     */
    protected $id = 0;
    /**
     * @var int
     */
    protected $fid = 0;
    /**
     * @var string
     */
    protected $filter = null;
    /**
     * @var bool
     */
    protected $isFree = true;
    /**
     * @var bool
     */
    protected $withLabel = true;
    /**
     * @var string
     */
    protected $name = null;
    /**
     * @var string
     */
    protected $note = null;
    /**
     * @var string
     */
    protected $cssClass = 'spField';
    /**
     * @var int
     */
    protected $position = null;
    /**
     * @var bool
     */
    protected $required = null;
    /**
     * @var int
     */
    protected $section = null;
    /**
     * @var string
     */
    protected $showIn = null;
    /**
     * @var bool
     */
    protected $multiLang = null;
    /**
     * @var bool
     */
    protected $uniqueData = null;
    /**
     * @var bool
     */
    protected $admList = null;
    /**
     * @var bool
     */
    protected $validate = null;
    /**
     * @var int
     */
    protected $version = 0;
    /**
     * @var int
     */
    protected $sid = 0;
    /**
     * @var array
     */
    protected $allowedAttributes = array();
    /**
     * @var array
     */
    protected $allowedTags = array();
    /**
     * @var bool
     */
    protected $editable = null;
    /**
     * @var string
     */
    protected $editor = null;
    /**
     * @var string
     */
    protected $inSearch = null;
    /**
     * @var array
     */
    protected $params = array();
    /**
     * @var bool
     */
    protected $parse = null;
    /**
     * @var string
     */
    protected $notice = null;
    /**
     * @var string
     */
    protected $template = null;
    /**
     * @var string
     */
    protected $label = null;
    /**
     * @var string
     */
    protected $suffix = null;
    /**
     * @var string
     */
    protected $currentView = null;
    /**
     * @var array
     */
    private $_translatable = array( 'name', 'description', 'suffix' );

    /**
     * @return mixed
     */
    public function getRaw()
    {
        if ( is_string( $this->_rawData ) ) {
            $this->_rawData = stripslashes( $this->_rawData );
        }
        $r = $this->_rawData;
        Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$r ) );
        return $r;
    }

    private function checkMethod( $method )
    {
        if ( !( $this->_type ) && class_exists( $this->_class ) && in_array( $method, get_class_methods( $this->_class ) ) ) {
            $this->fullInit();
        }
    }

    public function setRawData( $data )
    {
        $this->_rawData = $data;
    }

    /**
     * @return string
     */
    public function data( $html = false, $raw = false )
    {
        if ( $this->_off ) {
            return null;
        }
        Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$this->_data ) );
        if ( !( $raw ) ) {
            $this->checkMethod( 'cleanData' );
        }
        if ( $this->_type && method_exists( $this->_type, 'cleanData' ) ) {
            $r =& $this->_type->cleanData( $html );
        }
        else {
            $r =& $this->_data;
        }
        if ( $this->parse ) {
            Sobi::Trigger( 'Parse', 'Content', array( &$r ) );
        }
        return is_string( $r ) ? SPLang::clean( $r ) : $r;
    }

    /**
     * @return array
     */
    public function struct()
    {
        if ( $this->_off ) {
            return null;
        }
        $this->checkMethod( 'struct' );
        if ( $this->_type && method_exists( $this->_type, 'struct' ) ) {
            $r = $this->_type->struct();
        }
        else {
            $attributes = array();
            if ( strlen( $this->data() ) ) {
                $this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData';
                $this->cssClass = $this->cssClass . ' ' . $this->nid;
                $css = explode( ' ', $this->cssClass );
                if ( count( $css ) ) {
                    $this->cssClass = implode( ' ', array_unique( $css ) );
                }
                if ( $this->_type && method_exists( $this->_type, 'setCSS' ) ) {
                    $this->_type->setCSS( $this->cssClass );
                }

                $attributes = array(
                    'lang' => Sobi::Lang( false ),
                    'class' => $this->cssClass
                );
            }
            $r = array(
                '_complex' => 1,
                '_data' => $this->data(),
                '_attributes' => $attributes
            );
        }
        Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$r ) );
        return $r;
    }


    /**
     */
    public function __construct()
    {
        $this->id =& $this->fid;
    }

    public function & init( $id )
    {
        $this->fid = $id;
        /* @var SPdb $db */
        $db =& SPFactory::db();
        try {
            $db->select( '*', 'spdb_field', array( 'fid' => $id ) );
            $field = $db->loadObject();
            Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$field ) );
        }
        catch ( SPException $x ) {
            Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
        }
        $this->extend( $field );
        return $this;
    }

    /**
     * @param stdClass $obj
     */
    public function extend( $obj )
    {
        Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$obj ) );
        if ( !empty( $obj ) ) {
            foreach ( $obj as $k => $v ) {
                $this->_set( $k, $v );
            }
        }
        $this->getClass();
        $this->loadTables();
    }

    private function fullInit()
    {
        if ( !( $this->_loaded ) ) {
            $this->_loaded = true;
            $this->loadType();
            if ( $this->sid ) {
                if ( $this->_type && method_exists( $this->_type, 'loadData' ) ) {
                    $this->_type->loadData( $this->sid, $this->_fData, $this->_rawData, $this->_data );
                }
            }
        }
    }

    /**
     * @param string $var
     * @param mixed $val
     */
    public function set( $var, $val )
    {
        if ( isset( $this->$var ) ) {
            $this->$var = $val;
        }
    }

    public function delete()
    {
        Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( $this->id ) );
        if ( $this->_type && method_exists( $this->_type, 'delete' ) ) {
            $this->_type->delete();
        }
        /* @var SPdb $db */
        $db =& SPFactory::db();
        try {
            $db->delete( 'spdb_field', array( 'fid' => $this->id ) );
        }
        catch ( SPException $x ) {
            Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
        }
        try {
            $db->delete( 'spdb_field_data', array( 'fid' => $this->id ) );
        }
        catch ( SPException $x ) {
            Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
        }
        try {
            $db->delete( 'spdb_language', array( 'fid' => $this->id, 'oType' => 'field' ) );
        }
        catch ( SPException $x ) {
            Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
        }
        return Sobi::Txt( 'FD.DELETED', array( 'field' => $this->name ) );
    }

    /**
     * Creates the field type object (Proxy pattern)
     *
     */
    public function loadType()
    {
        if ( $this->type && class_exists( $this->_class ) ) {
            $implements = class_implements( $this->_class );
            if ( is_array( $implements ) && in_array( 'SPFieldInterface', $implements ) ) {
                $this->_type = new $this->_class( $this );
            }
        }
        elseif ( $this->type ) {
            $this->enabled = false;
            $this->_off = true;
            Sobi::Error( 'Field', sprintf( 'Field type %s does not exists', $this->fieldType ), SPC::WARNING );
        }
    }

    private function getClass()
    {
        if ( !( $this->_class ) ) {
            $this->type =& $this->fieldType;
            if ( SPLoader::translatePath( 'opt.fields.' . $this->fieldType ) ) {
                SPLoader::loadClass( 'opt.fields.fieldtype' );
                $this->_class = SPLoader::loadClass( 'opt.fields.' . $this->fieldType );
            }
            if ( !( $this->_class ) ) {
                $this->_off = true;
                Sobi::Error( 'Field', sprintf( 'Field type %s does not exists', $this->fieldType ), SPC::WARNING );
            }
        }
        return $this->_class;
    }

    /**
     */
    private function loadTables()
    {
        try {
            $labels = SPFactory::db()->select(
                array( 'sValue', 'sKey' ), 'spdb_language',
                array( 'fid' => $this->id, 'sKey' => $this->_translatable, 'language' => Sobi::Lang( false ), 'oType' => 'field' )
            )->loadAssocList( 'sKey' );
            if ( !( count( $labels ) ) ) {
                // last failback
                $labels = SPFactory::db()->select(
                    array( 'sValue', 'sKey' ), 'spdb_language',
                    array( 'fid' => $this->id, 'sKey' => $this->_translatable, 'language' => 'en-GB', 'oType' => 'field' )
                )->loadAssocList( 'sKey' );
            }
            if ( Sobi::Lang( false ) != Sobi::DefLang() ) {
                $labels2 = SPFactory::db()->select(
                    array( 'sValue', 'sKey' ), 'spdb_language',
                    array( 'fid' => $this->id, 'sKey' => $this->_translatable, 'language' => Sobi::DefLang(), 'oType' => 'field' )
                )->loadAssocList( 'sKey' );
                $labels = array_merge( $labels2, $labels );
            }
        }
        catch ( SPException $x ) {
            Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
        }
        if ( count( $labels ) ) {
            Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$labels ) );
            foreach ( $labels as $k => $v ) {
                $this->_set( $k, $v[ 'sValue' ] );
            }
        }
        $this->priority = $this->priority ? $this->priority : 5;
        /* if field is an admin filed - it cannot be required */
        if ( $this->adminField || !( $this->editable ) || !( $this->enabled ) ) {
            $this->required = false;
        }
    }

    private function cgf( $key )
    {
        if ( SPRequest::task() != 'field.edit' && strstr( $key, 'cfg:' ) ) {
            preg_match_all( '/\[cfg:([^\]]*)\]/', $key, $matches );
            if ( !( isset( $matches[ 1 ] ) ) || !( count( $matches[ 1 ] ) ) ) {
                preg_match_all( '/\{cfg:([^}]*)\}/', $key, $matches );
            }
            if ( count( $matches[ 1 ] ) ) {
                foreach ( $matches[ 1 ] as $i => $replacement ) {
                    $key = str_replace( $matches[ 0 ][ $i ], Sobi::Cfg( $replacement ), $key );
                }
            }
        }
        return $key;
    }

    /**
     * @param string $var
     * @param mixed $val
     */
    protected function _set( $var, $val )
    {
        if ( $this->has( $var ) ) {
            if ( is_array( $this->$var ) && is_string( $val ) ) {
                try {
                    $val = SPConfig::unserialize( $val, $var );
                }
                catch ( SPException $x ) {
                    Sobi::Error( $this->name(), sprintf( 'Cannot unserialize: %s.', $x->getMessage() ), SPC::NOTICE, 0, __LINE__, __FILE__ );
                }
            }
            if ( is_string( $val ) ) {
                $val = $this->cgf( $val );
            }
            $this->$var = $val;
        }
    }

    /**
     * Returns attributes of this class
     *
     * @return array
     */
    public function getAttributes()
    {
        $attr = get_class_vars( __CLASS__ );
        $ret = array();
        foreach ( $attr as $k => $v ) {
            if ( !( strstr( $k, '_' ) && strpos( $k, '_' ) == 0 ) ) {
                $ret[ ] = $k;
            }
        }
        return $ret;
    }

    /**
     * @param int $sid
     * @return void
     */
    public function loadData( $sid )
    {
        if ( $this->_off ) {
            return null;
        }
        $this->sid = $sid;
        $fdata = Sobi::Reg( 'fields_data_' . $sid, array() );
        if ( $sid && count( $fdata ) && isset( $fdata[ $this->id ] ) ) {
            $this->_fData = $fdata[ $this->id ];
            $this->lang = $this->_fData->lang;
            $this->_rawData = $this->_fData->baseData;
            $this->_data = $this->_fData->baseData;

            // if the field has own method we have to re-init
            $this->checkMethod( 'loadData' );
            if ( $this->editLimit > 0 && is_numeric( $this->_fData->editLimit ) ) {
                $this->editLimit = $this->_fData->editLimit;
            }
            elseif ( $this->editLimit < 0 ) {
                $this->editLimit = 2;
            }
            else {
                $this->editLimit = 2;
            }
            // if the limit has been reached - this field cannot be required
            if ( !( Sobi::Can( 'entry.manage.*' ) ) && $this->editLimit < 1 && in_array( SPRequest::task(), array( 'entry.save', 'entry.edit', 'entry.submit' ) ) ) {
                $this->required = false;
                $this->enabled = false;
                $this->_off = true;
            }
        }
        else {
            $fdata = Sobi::Reg( 'editcache' );
            if ( is_array( $fdata ) && isset( $fdata[ $this->nid ] ) ) {
                $this->_data = $fdata[ $this->nid ];
                $this->_rawData = $fdata[ $this->nid ];
            }
            else {
                $this->checkMethod( 'loadData' );
            }
        }
        if ( !( $this->isFree ) && SPRequest::task() == 'entry.edit' ) {
            /* in case we are editing - check if this field wasn't paid already */
            SPLoader::loadClass( 'services.payment' );
            if ( SPPayment::check( $sid, $this->id ) ) {
                $this->fee = 0;
                $this->isFree = true;
            }
        }
    }

    /**
     * @return void
     */
    public function field()
    {
        if ( $this->_off ) {
            return null;
        }
        $this->checkMethod( 'field' );
        if ( $this->_type && method_exists( $this->_type, 'field' ) ) {
            $args = func_get_args();
            $r = $this->_type->field( $args );
            Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$r ) );
            return $r;
        }
    }

    /**
     * @return void
     */
    public function searchForm()
    {
        if ( $this->_off ) {
            return null;
        }
        $this->checkMethod( 'searchForm' );
        if ( $this->_type && method_exists( $this->_type, 'searchForm' ) ) {
            $args = func_get_args();
            $r = $this->_type->searchForm( $args );
            Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$r ) );
            return $r;
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
        if ( isset( $this->$attr ) ) {
            return $this->$attr;
        }
        if ( !( $this->_type ) && !( $this->_off ) ) {
            $this->fullInit();
        }
        if ( $this->_type && $this->_type->has( $attr ) && $this->_type->get( $attr ) ) {
            return $this->_type->get( $attr );
        }
        else {
            return $default;
        }
    }

    /**
     * @param string $attr
     * @return bool
     */
    public function has( $attr )
    {
        return parent::has( $attr ) || ( $this->_type && $this->_type->get( $attr ) );
    }

    /**
     * Checks if the field should be displayed or not
     * @param string $view
     * @return bool
     */
    public function enabled( $view, $new = false )
    {
        if ( $view == 'form' ) {
            // while editing an entry we have to get the real data
            $this->fullInit( true );
            if ( $this->get( 'isOutputOnly' ) ) {
                return false;
            }
            if ( !( Sobi::Can( 'entry.adm_fields.edit' ) ) ) {
                if ( $this->adminField ) {
                    return false;
                }
                /*
                 * When the user is adding the entry very first time this should not affect because
                 * the field is not editable but the user has to be able to add data for the first time
                 */
                if ( !( $this->editable ) && SPRequest::task() != 'entry.add' && !( $new && in_array( SPRequest::task(), array( 'entry.submit', 'entry.save' ) ) ) ) {
                //if ( !( $this->editable ) && !( $new && in_array( SPRequest::task(), array( 'entry.add', 'entry.submit', 'entry.save' ) ) ) ) {
                    return false;
                }
                if ( !( $this->editLimit ) ) {
                    return false;
                }
            }
        }
        else {
            if ( $this->get( 'isInputOnly' ) ) {
                return false;
            }
        }
        $this->currentView = $view;
        if ( !( $this->enabled ) /*&& !( Sobi::Can( 'field', 'see_disabled', 'all' ) )*/ ) {
            return false;
        }
        if ( ( $view != 'form' ) && !( $this->showIn == $view || $this->showIn == 'both' ) ) {
            return false;
        }
        /*
         * not every field has the same raw data
         */
        if ( isset( $this->_fData->publishDown ) ) {
            if ( count( $this->_fData ) && ( !( strtotime( $this->_fData->publishUp ) < time() ) || ( ( ( strtotime( $this->_fData->publishDown ) > 0 ) && strtotime( $this->_fData->publishDown ) > time() ) ) ) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Proxy pattern
     *
     * @param string $method
     * @param array $args
     */
    public function __call( $method, $args )
    {
        if ( $this->_off ) {
            return null;
        }
        $this->checkMethod( $method );
        if ( $this->_type && method_exists( $this->_type, $method ) ) {
            $Args = array();
            // http://www.php.net/manual/en/function.call-user-func-array.php#91503
            foreach ( $args as $k => &$arg ) {
                $Args[ $k ] = &$arg;
            }
            Sobi::Trigger( 'Field', ucfirst( $method ), array( &$Args ) );
            return call_user_func_array( array( $this->_type, $method ), $Args );
        }
        else {
            if ( $this->_off ) {
                Sobi::Error( 'Field', SPLang::e( 'CALL_TO_UNDEFINED_CLASS_METHOD', $this->fieldType, $method ), SPC::WARNING );
            }
            else {
                throw new SPException( SPLang::e( 'CALL_TO_UNDEFINED_CLASS_METHOD', get_class( $this->_type ), $method ) );
            }
        }
    }
}
