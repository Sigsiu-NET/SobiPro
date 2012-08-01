<?php
/**
 * @version: $Id: template.php 2306 2012-03-16 16:17:10Z Radek Suski $
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
 * $Date: 2012-03-16 17:17:10 +0100 (Fri, 16 Mar 2012) $
 * $Revision: 2306 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/services/installers/template.php $
 */
defined( 'SOBIPRO' ) || ( trigger_error( 'Restricted access ' . __FILE__, E_USER_ERROR ) && exit( 'Restricted access' ) );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 17-Jun-2010 12:36:18
 */

SPLoader::loadClass( 'services.installers.installer' );

class SPTemplateInstaller extends SPInstaller
{
    public function install()
    {
        $id = $this->xGetString( 'id' );
        if ( SPLoader::dirPath( 'usr.templates.' . $id ) ) {
            throw new SPException( SPLang::e( 'TEMPLATE_INST_DUPLICATE', $id ) );
        }
        $path = SPLoader::dirPath( 'usr.templates.' . $id, 'front', false );
        if ( !( SPFs::move( $this->root, $path ) ) ) {
            throw new SPException( SPLang::e( 'CANNOT_MOVE_DIRECTORY', $this->root, $path ) );
        }

        $dir =& SPFactory::Instance( 'base.fs.directory', $path );
        $zip = array_keys( $dir->searchFile( '.zip', false ) );
        if( count( $zip ) ) {
            foreach( $zip as $file ) {
                SPFs::delete( $file );
            }
        }

        $requirements = $this->xGetChilds( 'requirements/*' );
        if ( $requirements && ( $requirements instanceof DOMNodeList ) ) {
            $reqCheck =& SPFactory::Instance( 'services.installers.requirements' );
            $reqCheck->check( $requirements );
        }

        $section = $this->xGetChilds( 'install' );
        if ( ( $section instanceof DOMNodeList ) && $section->length ) {
            $this->section( $id );
        }

        $exec = $this->xGetString( 'exec' );
        if ( $exec && SPFs::exists( $this->root . DS . $exec ) ) {
            include_once $this->root . DS . $exec;
        }

        Sobi::Trigger( 'After', 'InstallTemplate', array( $id ) );
        $dir =& SPFactory::Instance( 'base.fs.directory', SPLoader::dirPath( 'tmp.install' ) );
        $dir->deleteFiles();
        return Sobi::Txt( 'TP.TEMPLATE_HAS_BEEN_INSTALLED', array( 'template' => $id ) );
    }

    /**
     * @return void
     */
    private function section( $tpl )
    {
        $path = 'install/section/';
        /* get base section data */
        $name = $this->xGetString( $path . 'name' );
        $description = $this->xGetString( $path . 'description' );

        /* create new section */
        $section =& SPFactory::Instance( 'models.section' );
        $section->set( 'description', $description );
        $section->set( 'name', $name );
        $section->set( 'nid', SPLang::nid( $name ) );

        $fields =& $this->xGetChilds( $path . 'fields/*' );
        if ( ( $fields instanceof DOMNodeList ) && $fields->length ) {
            $section->save( false, false );
        }
        else {
            $section->save();
        }
        $sid = $section->get( 'id' );

        $settings = array();
        $options = $this->xGetChilds( $path . 'options/*' );
        if ( ( $options instanceof DOMNodeList ) && $options->length ) {
            foreach ( $options as $option ) {
                $v = $option->nodeValue;
                if ( in_array( $option->nodeValue, array( 'true', 'false' ) ) ) {
                    $v = $option->nodeValue == 'true' ? true : false;
                }
                $key = explode( '.', $option->getAttribute( 'attribute' ) );
                $settings[ trim( $key[ 0 ] ) ][ trim( $key[ 1 ] ) ] = $v;
            }
        }

        /* if there are fields to create */
        if ( ( $fields instanceof DOMNodeList ) && $fields->length ) {
            $fids = $this->fields( $fields, $sid );
            $settings[ 'entry' ][ 'name_field' ] = $fids[ $this->xGetString( $path . 'nameField' ) ];
            $settings[ 'list' ][ 'entries_ordering' ] = $this->xGetString( $path . 'nameField' );
        }
        else {
            $settings[ 'list' ][ 'entries_ordering' ] = $this->xGetString( $path . 'nameField' );
        }
        $settings[ 'section' ][ 'template' ] = $tpl;
        $settings[ 'general' ][ 'top_menu' ] = $this->xGetString( $path . 'showTopMenu' ) == 'true' ? true : false;
        $settings[ 'list' ][ 'categories_in_line' ] = ( int )$this->xGetString( $path . 'catsInLine' );
        $settings[ 'list' ][ 'cat_desc' ] = $this->xGetString( $path . 'showCategoryDesc' ) == 'true' ? true : false;
        $settings[ 'list' ][ 'cat_meta' ] = $this->xGetString( $path . 'showCategoryMeta' ) == 'true' ? true : false;
        $settings[ 'list' ][ 'subcats' ] = $this->xGetString( $path . 'showCategorySubcats' ) == 'true' ? true : false;
        $settings[ 'list' ][ 'categories_in_line' ] = ( int )$this->xGetString( $path . 'catsInLine' );
        $settings[ 'list' ][ 'entries_in_line' ] = ( int )$this->xGetString( $path . 'entriesInLine' );
        $settings[ 'list' ][ 'entries_limit' ] = ( int )$this->xGetString( $path . 'entriesOnPage' );
        $settings[ 'list' ][ 'entry_meta' ] = $this->xGetString( $path . 'showEntryMeta' ) == 'true' ? true : false;
        $settings[ 'list' ][ 'entry_cats' ] = $this->xGetString( $path . 'showEntryCategories' ) == 'true' ? true : false;

        $values = array();
        foreach ( $settings as $section => $setting ) {
            foreach ( $setting as $k => $v ) {
                $values[ ] = array( 'sKey' => $k, 'sValue' => $v, 'section' => $sid, 'critical' => 0, 'cSection' => $section );
            }
        }
        try {
            SPFactory::db()->insertArray( 'spdb_config', $values, true );
        }
        catch ( SPException $x ) {
            Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
        }
        /* create default permission */
        SPFactory::Controller( 'acl', true )
                ->addNewRule( $name, array( $sid ), array( 'section.access.valid', 'category.access.valid', 'entry.access.valid', 'entry.add.own' ), array( 'visitor', 'registered' ), "Default permissions for the section {$name}" );

        $categories = $this->xGetChilds( $path . 'categories/*' );
        if ( ( $categories instanceof DOMNodeList ) && $categories->length ) {
            $this->categories( $categories, $sid );
        }
        Sobi::Trigger( 'After', 'SaveConfig', array( &$values ) );
    }

    /**
     * @param DOMNodeList $fields
     * @return void
     */
    private function categories( $categories, $sid )
    {
        for ( $i = 0; $i < $categories->length; $i++ ) {
            $category = $categories->item( $i );
            if ( $category->nodeName == 'category' ) {
                $name = $this->txt( $category, 'name' );
                $introtext = $this->txt( $category, 'introtext' );
                $description = $this->txt( $category, 'description' );
                $icon = $this->txt( $category, 'icon' );
                $cat = SPFactory::Model( 'category' );
                $cat->set( 'state', 1 );
                /* Additional data */
                $options = $category->getElementsByTagName( 'option' );
                if ( ( $options instanceof DOMNodeList ) && $options->length ) {
                    foreach ( $options as $option ) {
                        $v = $option->nodeValue;
                        if ( in_array( $option->nodeValue, array( 'true', 'false' ) ) ) {
                            $v = $option->nodeValue == 'true' ? true : false;
                        }
                        $cat->set( $option->getAttribute( 'attribute' ), $v );
                    }
                }
                /* Base category data */
                $cat->set( 'description', $description );
                $cat->set( 'name', $name );
                $cat->set( 'nid', SPLang::nid( $name ) );
                $cat->set( 'introtext', $introtext );
                $cat->set( 'parent', $sid );
                $cat->set( 'icon', $icon );
                /* save the category */
                $cat->save();
                /* Handle subcats */
                $childs = $this->xdef->query( "childs/category", $category );
                if ( $childs && $childs->length ) {
                    if ( ( $childs instanceof DOMNodeList ) && $childs->length ) {
                        $this->categories( $childs, $cat->get( 'id' ) );
                    }
                }
            }
        }
    }

    /**
     * @param DOMNodeList $fields
     * @param int $sid
     * @return array
     */
    private function fields( $fields, $sid )
    {
        $c = 0;
        $fids = array();
        foreach ( $fields as $field ) {
            if ( $field->nodeName == 'field' ) {
                $c++;
                $attr = array();
                $attr[ 'adminField' ] = false;
                $attr[ 'editLimit' ] = -1;
                $ftype = $this->txt( $field, 'type' );
                $options = $field->getElementsByTagName( 'option' );
                if ( ( $options instanceof DOMNodeList ) && $options->length ) {
                    foreach ( $options as $option ) {
                        $v = $option->nodeValue;
                        if ( in_array( $option->nodeValue, array( 'true', 'false' ) ) ) {
                            $v = $option->nodeValue == 'true' ? true : false;
                        }
                        $attr[ $option->getAttribute( 'attribute' ) ] = $v;
                    }
                }
                $options = $field->getElementsByTagName( 'value' );
                $addOptions = array();
                if ( ( $options instanceof DOMNodeList ) && $options->length ) {
                    $values = array();
                    foreach ( $options as $option ) {
                        $id = strlen( $option->getAttribute( 'name' ) ) ? $option->getAttribute( 'name' ) : 0;
                        if ( strlen( $option->getAttribute( 'group' ) ) && $option->getAttribute( 'group' ) != 'root' ) {
                            if ( !( isset( $values[ $option->getAttribute( 'group' ) ] ) ) ) {
                                $values[ $option->getAttribute( 'group' ) ] = array( 'gid' => $option->getAttribute( 'group' ), 'name' => $option->getAttribute( 'group' ) );
                            }
                            $values[ $option->getAttribute( 'group' ) ][ ] = array( 'id' => $id, 'name' => $option->nodeValue );
                        }
                        elseif ( $id ) {
                            $values[ ] = array( 'id' => $id, 'name' => $option->nodeValue );
                        }
                        else {
                            $addOptions[ $option->parentNode->getAttribute( 'attribute' ) ][ ] = $option->nodeValue;
                        }
                    }
                    if ( count( $addOptions ) ) {
                        foreach ( $addOptions as $name => $options ) {
                            $values[ $name ] = $options;
                        }
                    }
                    /* we need the exact array format as the field expects, so we have to have numeric index */
                    if ( count( $values ) ) {
                        foreach ( $values as $v ) {
                            $attr[ 'options' ][] = $v;
                        }
                    }
                }
                $attr[ 'nid' ] = $this->txt( $field, 'name' );
                $attr[ 'name' ] = $this->txt( $field, 'label' );
                $attr[ 'required' ] = $this->txt( $field, 'required' ) == 'true' ? true : false;
                $attr[ 'showIn' ] = $this->txt( $field, 'showIn' );
                $attr[ 'type' ] = $ftype;
                $attr[ 'section' ] = $sid;
                $attr[ 'position' ] = isset( $attr[ 'position' ] ) ? $attr[ 'position' ] : $c;
                $attr[ 'enabled' ] = isset( $attr[ 'enabled' ] ) ? $attr[ 'enabled' ] : true;
                $attr[ 'editable' ] = isset( $attr[ 'editable' ] ) ? $attr[ 'editable' ] : true;

                /* let's create the field */
                $f =& SPFactory::Instance( 'models.adm.field' );
                $f->saveNew( $attr );
                $f->loadType( $ftype );
                $f->save( $attr );
                $fids[ $attr[ 'nid' ] ] = $f->get( 'id' );
            }
        }
        return $fids;
    }

    private function txt( $el, $node )
    {
        if ( $el->getElementsByTagName( $node )->length ) {
            return $el->getElementsByTagName( $node )->item( 0 )->nodeValue;
        }
        else {
            return null;
        }
    }
}

?>
