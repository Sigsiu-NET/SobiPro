<?php
/**
 * @version: $Id: tree.php 4350 2014-10-28 16:44:50Z Sigrid Suski $
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date: 2014-10-28 17:44:50 +0100 (Tue, 28 Oct 2014) $
 * $Revision: 4350 $
 * $Author: Radek Suski , Marcos A. Rodríguez Roldán $
 * I modified a couple of files and created a new one

Files are:
components/com_sobipro/lib/js/opt/field_category_tree.js
components/com_sobipro/lib/js/tree.js (I have the original file renamed to "tree-ori.js" , to work in the same way as before in the administrator)
components/com_sobipro/lib/mlo/tree.php
 * $HeadURL: file:///opt/svn/SobiPro/Component/branches/SobiPro-1.1/Site/lib/mlo/tree.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:08:52 PM
 */
final class SigsiuTree extends SPObject
{
    /**
     * array with all needed images
     *
     * @var array
     */
    private $_images = array(
        'root' => 'base.gif',
        'join' => 'join.gif',
        'joinBottom' => 'joinbottom.gif',
        'plus' => 'plus.gif',
        'plusBottom' => 'plusbottom.gif',
        'minus' => 'minus.gif',
        'minusBottom' => 'minusbottom.gif',
        'folder' => 'folder.gif',
        'disabled' => 'disabled.gif',
        'folderOpen' => 'folderopen.gif',
        'line' => 'line.gif',
        'empty' => 'empty.gif'
    );
    /**
     * @var string
     */
    private $tree = null;
    /**
     * @var string
     */
    private $_task = 'tree.node';
    /**
     * @var string
     */
    private $_url = null;
    /**
     * @var string
     */
    private $_tag = 'div';
    /**
     * @var string
     */
    private $_id = 'sobiCats';
    /**
     * @var string
     */
    private $_ordering = 'position';
    /**
     * @var int
     */
    private $_sid = 0;
    /**
     * @var int
     */
    private $_pid = 0;
    /**
     * @var int
     */
    private $_disabled = array();

    /**
     * Set category, or set of categories id which should not be selectable in the tree
     * @param int $cid
     */
    public function disable( $cid )
    {
        if ( is_array( $cid ) ) {
            $this->_disabled = array_merge( $this->_disabled, $cid );
        }
        else {
            $this->_disabled[ ] = $cid;
        }
    }

    /**
     * Return created Tree
     * @return string
     */
    public function getTree()
    {
        Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), array( &$this->tree ) );
        return $this->tree;
    }

	/**
	 * @param bool $return
	 * @return string
	 */
    public function display( $return = false )
    {
        Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), array( &$this->tree ) );
        if ( $return ) {
            return $this->tree;
        }
        else {
            echo $this->tree;
        }
    }

	/**
	 * @param $id
	 */
    public function setId( $id )
    {
        $this->_id = $id;
    }

    /**
     * @param int $id
     */
    public function setPid( $id )
    {
        $this->_pid = $id;
    }

    /**
     * @param array $images
     */
    public function setImages( $images )
    {
        Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), array( &$images ) );
        if ( $images && is_array( $images ) ) {
            foreach ( $images as $img => $loc ) {
                if ( file_exists( SOBI_ROOT . DS . $loc ) ) {
                    $this->_images[ $img ] = $loc;
                }
            }
            foreach ( $this->_images as $img => $loc ) {
                $this->_images[ $img ] = Sobi::FixPath( Sobi::Cfg( 'tree_images', Sobi::Cfg( 'images_folder_live' ) . 'tree/' . $loc ) );
            }
        }
    }

    /**
     * @param string $_tag
     */
    public function setTag( $_tag )
    {
        $this->_tag = $_tag;
    }

    /**
     * @param string $_task
     */
    public function setTask( $_task )
    {
        $this->_task = $_task;
    }

    /**
     * @param string $href
     */
    public function setHref( $href )
    {
        $this->_url = $href;
    }

	/**
	 * constructor
	 * @param string $ordering
	 * @param array $opts
	 * @return SigsiuTree
	 */
    public function __construct( $ordering = 'position', $opts = array() )
    {
        $this->_ordering = $ordering;
        foreach ( $this->_images as $img => $loc ) {
            $this->_images[ $img ] = Sobi::FixPath( Sobi::Cfg( 'tree_images', Sobi::Cfg( 'img_folder_live' ) . '/tree/' . $loc ) );
        }
	    if( count($opts)) {
		    foreach( $opts as $a => $v ) {
			    $this->$a = $v;
		    }
	    }
    }

    /**
     * init tree
     * @param int $sid - sobi id / section id
     * @param int $current - actually displayed category
     * @return string $tree
     */
    public function init( $sid, $current = 0 )
    {
        $head =& SPFactory::header();
        if ( defined( 'SOBIPRO_ADM' ) ) {
            $head->addCssFile( 'tree', true );
			$treeadmin = 'tree-ori';     
			$div = ""; }
        else {
			$treeadmin = 'tree';
			$div = "<div style=\" width: 14%; float: left;\">" ; 
            if ( Sobi::Reg( 'current_template_path', null ) && SPFs::exists( Sobi::Reg( 'current_template_path' ) . 'css' . DS . 'tree.css' ) ) {
                $head->addCssFile( 'absolute.' . Sobi::Reg( 'current_template_path' ) . 'css' . DS . 'tree.css' );
            }
            else {
                $head->addCssFile( 'tree' );
            }
        }
        $tree = null;
        $matrix = null;
        $this->_sid = $sid;
        if ( $current ) {
            $this->startScript( $current );
        }

        $section = $this->getSection( $sid );
        $sectionLink = $this->parseLink( $section );
        $sectionName = $section->get( 'name' );
        $childs = $this->getChilds( $sid );
        //		if( count( $cats ) ) {
        //			foreach ( $cats as $i => $cat ) {
        //				$childs[ $cat->get( 'name' ).$i ] = $cat;
        //			}
        //		}
        //		ksort( $childs );
        $countNodes = count( $childs, 0 );
        $lastNode = 0;

        $tree .= "\n\t<{$this->_tag} class=\"sigsiuTree {$this->_id}SigsiuTree\">";
        $tree .= "\n\t\t<div class=\"childsContainer\" id=\"{$this->_id}stNode0\">";
        if ( !( in_array( $sid, $this->_disabled ) ) ) {
         
        }
        else {
            $tree .= "<img id=\"{$this->_id}0\" src=\"{$this->_images['root']}\" alt=\"{$sectionName}\"/>";
        }
        if ( !( in_array( $sid, $this->_disabled ) ) ) {
            $tree .= "<a href=\"{$sectionLink}\"  value=\"{$sid}\" data-sid=\"{$sid}\" class=\"treeNode\" id=\"{$this->_id}_CatUrl0\">{$sectionName}</a>";
        }
        else {
            $tree .= $sectionName;
        }
        $tree .= "</{$this->_tag}>";
        $tree .= "\n\t\t<{$this->_tag} id=\"{$this->_id}\" class=\"clip\" style=\"width: 100%; display: block;\">";

        if ( count( $childs ) ) {
            foreach ( $childs as $cat ) {
                $countNodes--;
                // clean string produces htmlents and these are invalid in XML
                $catName = /*$this->cleanString*/
                        ( $cat->get( 'name' ) );
                $hasChilds = count( $cat->getChilds( 'category' ) );
                $cid = $cat->get( 'id' );
                $url = $this->parseLink( $cat );
                $disabled = ( in_array( $cid, $this->_disabled ) ) ? true : false;
  				if ( defined( 'SOBIPRO_ADM' ) ) {
              $tree .= "\n\t\t\t<{$this->_tag} class=\"sigsiuTreeNode\" id=\"{$this->_id}stNode{$cid}\">";
				}else{$tree .= ""; }
                if ( $hasChilds ) {
                    if ( $countNodes == 0 && !$disabled ) {
                        $lastNode = $cid;
                        $tree .= "\n\t\t\t\t\t".$div."<a href=\"javascript:{$this->_id}_stmExpand( {$cid}, 0, {$this->_pid} );\" id=\"{$this->_id}_imgUrlExpand{$cid}\">\n\t\t\t\t\t\t<img src=\"{$this->_images[ 'plusBottom' ]}\" id=\"{$this->_id}_imgExpand{$cid}\"  style=\"border-style:none;\" alt=\"expand\"/>\n\t\t\t\t\t</a>";
                        $matrix .= "\n{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'plusBottom' );";
                    }
                    elseif ( !$disabled ) {
                        $tree .= "\n\t\t\t\t\t".$div."<a href=\"javascript:{$this->_id}_stmExpand( {$cid}, 0, {$this->_pid} );\" id=\"{$this->_id}_imgUrlExpand{$cid}\">\n\t\t\t\t\t\t<img src=\"{$this->_images[ 'plus' ]}\" id=\"{$this->_id}_imgExpand{$cid}\"  style=\"border-style:none;\" alt=\"expand\"/>\n\t\t\t\t\t</a>";
                        $matrix .= "\n{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'plus' );";
                    }
                    else {
                        $tree .= "\n\t\t\t\t\t<img src=\"{$this->_images[ 'join' ]}\" id=\"{$this->_id}_imgExpand{$cid}\" style=\"border-style:none;\" alt=\"expand\"/>";
                        $matrix .= "\n{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'plus' );";
                    }
                }
                else {
                    if ( $countNodes == 0 && !$disabled ) {
                        $lastNode = $cid;
                        $tree .= "\n\t\t\t\t\t<img src=\"{$this->_images[ 'joinBottom' ]}\" style=\"border-style:none;\" id=\"{$this->_id}_imgJoin{$cid}\" alt=\"\"/>";
                        $matrix .= "\n{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'join' );";
                    }
                    elseif ( !$disabled ) {
                        $tree .= "\n\t\t\t\t\t<img src=\"{$this->_images[ 'join' ]}\" style=\"border-style:none;\" id=\"{$this->_id}_imgJoin{$cid}\" alt=\"\"/>";
                        $matrix .= "\n{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'joinBottom' );";
                    }
                    else {
                        $tree .= "\n\t\t\t\t\t<img src=\"{$this->_images[ 'joinBottom' ]}\" id=\"{$this->_id}_imgExpand{$cid}\" style=\"border-style:none;\" alt=\"expand\"/>";
                        $matrix .= "\n{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'plus' );";
                    }

                }
                if ( !$disabled ) {
                    $tree .= "\n\t\t\t\t\t<a href=\"{$url}\" id=\"{$this->_id}_imgFolderUrl{$cid}\">\n\t\t\t\t\t\t<img src=\"{$this->_images[ 'folder' ]}\" style=\"border-style:none;\" id=\"{$this->_id}_imgFolder{$cid}\" alt=\"\"/>\n\t\t\t\t\t</a>\n\t\t\t\t\t\n\t\t\t";
					if ( defined( 'SOBIPRO_ADM' )){$tree .= "<a href=\"{$url}\" rel=\"{$cid}\" data-sid=\"{$cid}\" class=\"treeNode\" id=\"{$this->_id}_CatUrl{$cid}\">\n\t\t\t\t\t\t{$catName}\n\t\t\t\t\t</a>";}
					else{$tree .= "</div><select multiple=\"multiple\" class=\"sigsiuTreeNode childsContainer\" style=\"display: block; height: 20px; margin: 2px 0px 0px 5px; overflow-y: hidden; width: 83%; float: left;\" id=\"{$this->_id}stNode{$cid}\"><option href=\"{$url}\" value=\"{$cid}\" data-sid=\"{$cid}\" class=\"treeNode\" id=\"{$this->_id}_CatUrl{$cid}\">\n\t\t\t\t\t\t{$catName}\n\t\t\t\t\t</option>";}
                }
                else {
                    $tree .= "\n\t\t\t\t\t<img src=\"{$this->_images[ 'disabled' ]}\" style=\"border-style:none;\" id=\"{$this->_id}_imgFolder{$cid}\" alt=\"\"/>\n\t\t\t\t\t{$catName}\n\t\t\t\t\t</a>";
                }

                $tree .= "\n\t\t\t";
				if ( defined( 'SOBIPRO_ADM' )){$tree .= "</{$this->_tag}>";}else{$tree .= "</select>";}
                if ( $hasChilds && !$disabled ) {
                    $tree .= "\n\t\t\t<{$this->_tag} id=\"{$this->_id}_childsContainer{$cid}\" class=\"clip\" style=\"width: 100%; display: block; display:none;\"></{$this->_tag}>";
                }
            }
        }
        $tree .= "\n\t\t</{$this->_tag}>";
        $tree .= "\n\t</{$this->_tag}>\n\n";
        $this->createScript( $lastNode, $childs, $matrix, $head, $treeadmin );
        $this->tree = $tree;
    }

    /**
     * returning information about subcats in XML format
     *
     * @param int $sid
     */
    public function extend( $sid )
    {
        $childs = $this->getChilds( $sid );
        Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), array( &$childs ) );
        SPFactory::mainframe()->cleanBuffer();
        header( 'Content-type: application/xml' );
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        echo "\n<root>";
        if ( count( $childs ) ) {
            foreach ( $childs as $cat ) {
                $catName = $this->cleanString( $cat->get( 'name' ) );
                $hasChilds = count( $cat->getChilds( 'category' ) );
                $cid = $cat->get( 'id' );
                $pid = $cat->get( 'parent' );
                $url = preg_replace( '/&(?![#]?[a-z0-9]+;)/i', '&amp;', $this->parseLink( $cat ) );
                $disabled = ( in_array( $cid, $this->_disabled ) ) ? true : false;
                if ( $disabled ) {
                    continue;
                }
                echo "\n\t <category>";
                echo "\n\t\t <catid>{$cid}</catid>";
                echo "\n\t\t <name>{$catName}</name>";
                echo "\n\t\t <introtext>.</introtext>";
                echo "\n\t\t <parentid>{$pid}</parentid>";
                echo "\n\t\t <childs>{$hasChilds}</childs>";
                echo "\n\t\t <url>{$url}</url>";
                echo "\n\t </category>";
            }
        }
        echo "\n</root>";
        /* we don't need any others information so we can go out */
        exit();
    }

    private function startScript( $current )
    {
        $path = SPFactory::config()->getParentPath( $current );

        if ( !( $this->getChilds( $path[ count( $path ) - 1 ] ) ) ) {
            unset( $path[ count( $path ) - 1 ] );
        }
        unset( $path[ 0 ] );
        $func = $this->_id . '_stmExpand';
        $script = null;
        if ( count( $path ) ) {
            foreach ( $path as $i => $cid ) {
                $retard = $i * 150;
                $script .= "\t\twindow.setTimeout( '{$func}( {$cid}, {$i}, 0 )', {$retard} );\n";
            }
            SPFactory::header()->addJsCode( "\tSobiPro.onReady( function () { \n{$script}\n \t} );" );
        }
    }

    /**
     */
    private function createScript( $lastNode, $childs, $matrix, $head, $treeadmin )
    {
        $params = array();
        $params[ 'ID' ] = $this->_id;
        $params[ 'LAST_NODE' ] = ( string )$lastNode;
        $params[ 'IMAGES_ARR' ] = null;
        $params[ 'IMAGES_MATRIX' ] = $matrix;
        foreach ( $this->_images as $img => $loc ) {
            $params[ 'IMAGES_ARR' ] .= "\n{$this->_id}_stmImgs[ '{$img}' ] = '{$loc}';";
        }
        $params[ 'URL' ] = Sobi::Url(
            array(
                'task' => $this->_task,
                'sid' => $this->_sid,
                'out' => 'xml',
                'expand' => '__JS__',
                'pid' => '__JS2__'
            ),
            true, false
        );
        $params[ 'URL' ] = str_replace( '__JS__', '" + ' . $this->_id . '_stmcid + "', $params[ 'URL' ] );
        $params[ 'URL' ] = str_replace( '__JS2__', '" + ' . $this->_id . '_stmPid + "', $params[ 'URL' ] );
        $params[ 'FAIL_MSG' ] = Sobi::Txt( 'AJAX_FAIL' );
        $params[ 'TAG' ] = $this->_tag;
        $params[ 'SPINNER' ] = Sobi::FixPath( Sobi::Cfg( 'img_folder_live' ) . '/adm/spinner.gif' );
        Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), array( &$params ) );
        $head->addJsVarFile( $treeadmin, md5( count( $childs, COUNT_RECURSIVE ) . $this->_id . $this->_sid . $this->_task . serialize( $params ) ), $params );
		
    }

    /**
     * @param int $sid
     * @return SPSection
     */
    private function getChilds( $sid, $count = false )
    {
        $childs = array();
        /* @var SPdb $db */
        try {
            $ids = SPFactory::db()
                    ->select( 'id', 'spdb_relations', array( 'pid' => $sid, 'oType' => 'category' ) )
                    ->loadResultArray();
        }
        catch ( SPException $x ) {
            Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_CHILDS_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
        }
        if ( $count ) {
            return count( $ids );
        }
        if ( count( $ids ) ) {
            foreach ( $ids as $id ) {
                $child = SPFactory::Category( $id );
                if ( $child->get( 'state' ) || defined( 'SOBIPRO_ADM' ) ) {
                    $childs[ ] = $child;
                }
            }
        }
        uasort( $childs, array( $this, 'sortChilds' ) );
        return $childs;
    }

    public function sortChilds( $from, $to )
    {
        switch ( $this->_ordering ) {
            case 'name':
            case 'name.asc':
            case 'name.desc':
                $r = strcasecmp( $from->get( 'name' ), $to->get( 'name' ) );
                if ( $this->_ordering == 'name.desc' ) {
                    $r = $r * -1;
                }
                break;
            default:
            case 'position':
            case 'position.asc':
            case 'position.desc':
                $r = ( $from->get( 'position' ) > $to->get( 'position' ) ) ? 1 : -1;
                if ( $this->_ordering == 'position.desc' ) {
                    $r = $r * -1;
                }
                break;
        }
        return $r;
    }

    /**
     * @param int $sid
     * @return SPSection
     */
    private function getSection( $sid )
    {
        SPLoader::loadModel( 'section' );
        $section = new SPSection();
        $section->init( $sid );
        return $section;
    }

    /**
     * parse link (replace placeholders)
     * @param SPObject $cat
     * @return string
     */
    private function parseLink( $cat )
    {
        static $placeHolders = array(
            '{sid}',
            '{name}',
            '{introtext}',
        );
        $replacement = array(
            $cat->get( 'id' ),
            $cat->get( 'name' ),
            $cat->get( 'introtext' ),
        );
        $link = str_replace( $placeHolders, $replacement, $this->_url );
        Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), array( &$link ) );
        return $link;
    }

    /**
     * cleaning string for javascript
     *
     * @param string $string
     * @return string
     */
    private function cleanString( $string )
    {
        $string = htmlspecialchars( $string, ENT_COMPAT, 'UTF-8' );
        $string = str_replace( '&amp;', '&#38;', $string );
        return $string;
    }
}
