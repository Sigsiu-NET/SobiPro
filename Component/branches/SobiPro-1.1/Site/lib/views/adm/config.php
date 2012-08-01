<?php
/**
 * @version: $Id: config.php 2326 2012-03-27 15:16:01Z Radek Suski $
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
 * $Date: 2012-03-27 17:16:01 +0200 (Tue, 27 Mar 2012) $
 * $Revision: 2326 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/config.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'view', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:41:10 PM
 */
class SPConfigAdmView extends SPAdmView implements SPView
{
    /**
     * @var bool
     */
    protected $_fout = true;
    /**
     * @var SPConfigAdmCtrl
     */
    private $_ctrl = true;

    public function setCtrl( &$ctrl )
    {
        $this->_ctrl =& $ctrl;
    }

    /**
     * @param string $title
     */
    public function setTitle( $title )
    {
        $title = Sobi::Txt( $title, array( 'section' => $this->get( 'section.name' ) ) );
        Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
        SPFactory::header()->setTitle( $title );
        $this->set( $title, 'site_title' );
    }

    /**
     * Enter description here...
     *
     */
    protected function fields()
    {
        SPLoader::loadClass( 'html.tabs' );
        SPFactory::header()->addCssFile( 'tabs', true );
        $t = new SPHtml_Tabs( true, null );
        $tabs = $this->get( 'fields' );
        if ( count( $tabs ) ) {
            $t->startPane( 'fields_' . $this->get( 'task' ) );
            foreach ( $tabs as $tab => $keys ) {
                $t->startTab( Sobi::Txt( $tab ), str_replace( ' ', '_', $tab ) );
                echo '<table  class="admintable" style="width: 100%;">';
                $c = 0;
                foreach ( $keys as $key => $params ) {
                    $class = $c % 2;
                    $c++;
                    $params = explode( '|', $params );
                    $p = array();
                    /* at first we need the label */
                    $label = Sobi::Txt( array_shift( $params ) );
                    $label2 = null;
                    if ( strstr( $label, ':' ) ) {
                        $label = explode( ':', $label );
                        $label2 = $label[ 1 ];
                        $label = $label[ 0 ];
                    }
                    /* get the field type */
                    $p[ 0 ] = array_shift( $params );

                    if ( preg_match( '/^section.*/', $key ) ) {
                        /* put the field name */
                        $p[ 1 ] = $key;
                        /* get the current value */
                        $p[ 2 ] = $this->get( $key );
                    }
                    elseif ( !( strstr( $key, 'spacer' ) ) ) {
                        /* put the field name */
                        $p[ 1 ] = 'spcfg_' . $key;
                        /* get the current value */
                        $p[ 2 ] = Sobi::Cfg( $key, '' );
                    }
                    if ( ( strstr( $key, 'spacer' ) ) ) {
                        if ( $key == 'spacer_pby' ) {
                            $this->pby();
                        }
                        else {
                            echo "<tr class=\"row{$class}\">";
                            echo '<th colspan="2" class="spConfigTableHeader">';
                            $this->txt( $label );
                            echo '</th>';
                            echo '</tr>';
                        }
                    }
                    else {
                        if ( strstr( $key, '_array' ) && count( $p[ 2 ] ) && $p[ 2 ] ) {
                            $p[ 2 ] = implode( '|', $p[ 2 ] );
                        }
                        /* and all other parameters */
                        if ( count( $params ) ) {
                            foreach ( $params as $param ) {
                                $p[ ] = $param;
                            }
                        }
                        echo "<tr class=\"row{$class}\">";
                        echo '<td class="key" style="min-width:200px;">';
                        $this->txt( $label );
                        echo '</td>';
                        echo '<td>';
                        $this->parseField( $p );
                        if ( $label2 ) {
                            $this->txt( $label2 );
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                echo '</table>';
                $t->endTab();
            }
            $t->endPane();
        }
    }

    private function parseField( $params )
    {
        if ( strstr( $params[ 0 ], 'function:' ) ) {
            $params[ 0 ] = str_replace( 'function:', null, $params[ 0 ] );
            switch ( $params[ 0 ] ) {
                case 'name_fields_list':
                    $params = $this->namesFields( $params );
                    break;
                case 'entries_ordering':
                    $params = $this->namesFields( $params, true );
                    break;
                case 'templates_list':
                    $params = $this->templatesList( $params, true );
                    break;
                case 'alpha_field_list':
                    $params = $this->alphaFieldList( $params );
                    break;
                case 'alpha_fields_list':
                    $params = $this->alphaFieldList( $params, true );
                    break;
            }
        }
        else {
            /* bei der methode is das value array, ein array mit allen moeglichen values und das was ausgewaehlt ist, ist das selected. Muss umdrehen */
            if ( $params[ 0 ] == 'select' ) {
                $selected = $params[ 2 ];
                $params[ 2 ] = $params[ 3 ];
                $params[ 3 ] = $selected;
            }
        }
        call_user_func_array( array( $this, 'field' ), $params );
    }

    private function alphaFieldList( $params, $add = false )
    {
        $fields = $this->_ctrl->getNameFields( true, Sobi::Cfg( 'alphamenu.field_types' ) );
        if ( count( $fields ) ) {
            foreach ( $fields as $fid => $field ) {
                $fData[ $fid ] = $field->get( 'name' );
            }
        }
        if ( $add ) {
            $selected = Sobi::Cfg( 'alphamenu.extra_fields_array' );
            $p = array( 'select', $params[ 1 ], $fData, $selected, $add, $params[ 3 ] );
        }
        else {
            $selected = Sobi::Cfg( 'alphamenu.primary_field', SPFactory::config()->nameField()->get( 'id' ) );
            $p = array( 'select', $params[ 1 ], $fData, $selected, $add, $params[ 3 ] );
        }
        return $p;
    }

    private function pby()
    {
        echo "<tr>";
        echo '<td colspan="2"><div style="margin-left: 150px;">';
        echo Sobi::Txt( 'GB.CFG.PBY_EXPL', array( 'image' => Sobi::Cfg( 'img_folder_live' ) . '/donate.png' ) );
        echo '</div></td>';
        echo '</tr>';
    }

    private function templatesList( $params, $ordering = false )
    {
        $cms = SPFactory::CmsHelper()->templatesPath();
        $dir = new SPDirectoryIterator( SPLoader::dirPath( 'usr.templates' ) );
        $templates = array();
        foreach ( $dir as $file ) {
            if ( $file->isDir() ) {
                if ( $file->isDot() || in_array( $file->getFilename(), array( 'common', 'front' ) ) ) {
                    continue;
                }
                if ( SPFs::exists( $file->getPathname() . DS . 'template.xml' ) && ( $tname = $this->templateName( $file->getPathname() . DS . 'template.xml' ) ) ) {
                    $templates[ $file->getFilename() ] = $tname;
                }
                else {
                    $templates[ $file->getFilename() ] = $file->getFilename();
                }
            }
        }
        if ( is_array( $cms ) && isset( $cms[ 'name' ] ) && isset( $cms[ 'data' ] ) && is_array( $cms[ 'data' ] ) && count( $cms[ 'data' ] ) ) {
            $templates[ $cms[ 'name' ] ] = array();
            foreach ( $cms[ 'data' ] as $name => $path ) {
                $templates[ $cms[ 'name' ] ][ $name ] = array();
                $dir = new SPDirectoryIterator( $path );
                foreach ( $dir as $file ) {
                    if ( $file->isDot() ) {
                        continue;
                    }
                    $fpath = 'cms:' . str_replace( SOBI_ROOT . DS, null, $file->getPathname() );
                    $fpath = str_replace( DS, '.', $fpath );
                    if ( SPFs::exists( $file->getPathname() . DS . 'template.xml' ) && ( $tname = $this->templateName( $file->getPathname() . DS . 'template.xml' ) ) ) {
                        $templates[ $cms[ 'name' ] ][ $name ][ $fpath ] = $tname;
                    }
                    else {
                        $templates[ $cms[ 'name' ] ][ $name ][ $fpath ] = $file->getFilename();
                    }
                }
            }
        }
        $p = array( 'select', 'spcfg_' . $params[ 1 ], $templates, Sobi::Cfg( 'section.template', 'default' ), false, $params[ 3 ] );
        return $p;
    }

    private function templateName( $file )
    {
        $def = new DOMDocument();
        $def->load( $file );
        $xdef = new DOMXPath( $def );
        $name = $xdef->query( '/template/name' )->item( 0 )->nodeValue;
        return strlen( $name ) ? $name : false;
    }

    private function namesFields( $params, $ordering = false )
    {
        $fields = $this->_ctrl->getNameFields( $ordering );
        $fData = array( 0 => Sobi::Txt( 'SEC.CFG.ENTRY_TITLE_FIELD_SELECT' ) );
        if ( count( $fields ) ) {
            foreach ( $fields as $fid => $field ) {
                if ( $ordering ) {
                    $fData[ $field->get( 'nid' ) ] = $field->get( 'name' );
                }
                else {
                    $fData[ $fid ] = $field->get( 'name' );
                }
            }
        }
        if ( $ordering ) {
            unset( $fData[ 0 ] );
            $fData = array(
                'position.asc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_POSITION_ASCENDING' ),
                'position.desc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_POSITION_DESCENDING' ),
                'counter.asc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_POPULARITY_ASCENDING' ),
                'counter.desc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_POPULARITY_DESCENDING' ),
                'createdTime.asc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_CREATION_DATE_ASC' ),
                'createdTime.desc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_CREATION_DATE_DESC' ),
                'updatedTime.asc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_UPDATE_DATE_ASC' ),
                'updatedTime.desc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_UPDATE_DATE_DESC' ),
                'validUntil.asc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_EXPIRATION_DATE_ASC' ),
                'validUntil.desc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_EXPIRATION_DATE_DESC' ),
                Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_FIELDS' ) => $fData
            );
        }
        $p = array( 'select', $params[ 1 ], $fData, $params[ 2 ], false );
        if ( isset( $params[ 3 ] ) ) {
            $p[ ] = $params[ 3 ];
        }
        if ( isset( $params[ 4 ] ) ) {
            $p[ ] = $params[ 4 ];
        }
        if ( isset( $params[ 5 ] ) ) {
            $p[ ] = $params[ 5 ];
        }
        return $p;
    }

    /**
     *
     * @param mixed $attr
     * @param int $index
     * @return mixed
     */
    public function & get( $attr, $index = -1 )
    {
        $config =& SPFactory::config();
        if ( !( $config->key( $attr, false ) ) ) {
            return parent::get( $attr, $index );
        }
        else {
            $attr = $config->key( $attr );
            if ( is_array( $attr ) ) {
                $attr = implode( '|', $attr );
            }
            return $attr;
        }
    }
}
