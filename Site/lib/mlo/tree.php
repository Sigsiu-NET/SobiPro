<?php
/**
 * @package: SobiPro Library
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 *
 * Url: https://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
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
	private $_images = [
		'root'        => 'base.gif',
		'join'        => 'empty.gif',
		'joinBottom'  => 'empty.gif',
		'plus'        => 'arrow_close.gif',
		'plusBottom'  => 'arrow_close.gif',
		'minus'       => 'arrow_open.gif',
		'minusBottom' => 'arrow_open.gif',
		'folder'      => 'folder.gif',
		'disabled'    => 'disabled.gif',
		'folderOpen'  => 'folderopen.gif',
		'line'        => 'empty.gif',
		'empty'       => 'empty.gif'
	];
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
	private $_disabled = [];

	/**
	 * Set category, or set of categories id which should not be selectable in the tree
	 *
	 * @param int $cid
	 */
	public function disable( $cid )
	{
		if ( is_array( $cid ) ) {
			$this->_disabled = array_merge( $this->_disabled, $cid );
		}
		else {
			$this->_disabled[] = $cid;
		}
	}

	/**
	 * Return created Tree
	 * @return string
	 */
	public function getTree()
	{
		Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), [ &$this->tree ] );

		return $this->tree;
	}

	/**
	 * @param bool $return
	 *
	 * @return string
	 */
	public function display( $return = false )
	{
		Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), [ &$this->tree ] );
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
		Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), [ &$images ] );
		if ( $images && is_array( $images ) ) {
			foreach ( $images as $img => $loc ) {
				if ( file_exists( SOBI_ROOT . DS . $loc ) ) {
					$this->_images[ $img ] = $loc;
				}
			}
			foreach ( $this->_images as $img => $loc ) {
				$this->_images[ $img ] = Sobi::FixPath( Sobi::Cfg( 'tree_images', Sobi::Cfg( 'media_folder_live' ) . 'tree/' . $loc ) );
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
	 *
	 * @param string $ordering
	 * @param array $opts
	 *
	 * @return SigsiuTree
	 */
	public function __construct( $ordering = 'position', $opts = [] )
	{
		$this->_ordering = $ordering;
		foreach ( $this->_images as $img => $loc ) {
			$this->_images[ $img ] = Sobi::FixPath( Sobi::Cfg( 'tree_images', Sobi::Cfg( 'media_folder_live' ) . '/tree/' . $loc ) );
		}
		if ( count( $opts ) ) {
			foreach ( $opts as $a => $v ) {
				$this->$a = $v;
			}
		}
	}

	/**
	 * init tree
	 *
	 * @param int $sid - sobi id / section id
	 * @param int $current - actually displayed category
	 *
	 * @return string $tree
	 */
	public function init( $sid, $current = 0 )
	{
		$head =& SPFactory::header();
		if ( defined( 'SOBIPRO_ADM' ) ) {
//            $head->addCssFile( 'tree', true );
		}
		else {
			if ( Sobi::Reg( 'current_template_path', null ) && SPFs::exists( Sobi::Reg( 'current_template_path' ) . 'css/tree.css' ) ) {
				$head->addCssFile( 'absolute.' . Sobi::Reg( 'current_template_path' ) . 'css/tree.css' );
			}
			else {
				$head->addCssFile( 'tree' );
			}
		}
		$tree       = null;
		$matrix     = null;
		$this->_sid = $sid;
		if ( $current ) {
			$this->startScript( $current );
		}

		$section     = $this->getSection( $sid );
		$sectionLink = $this->parseLink( $section );
		$sectionName = $section->get( 'name' );
		$childs      = $this->getChilds( $sid );
		//		if( count( $cats ) ) {
		//			foreach ( $cats as $i => $cat ) {
		//				$childs[ $cat->get( 'name' ).$i ] = $cat;
		//			}
		//		}
		//		ksort( $childs );
		$countNodes = count( $childs, 0 );
		$lastNode   = 0;

		if ( $this->_id == "sobiCats" ) {   //categories tree called from iframe, SobiPro scope needed
			$tree .= "<{$this->_tag} class=\"SobiPro\">";
		}
		$tree .= "<{$this->_tag} class=\"sigsiuTree {$this->_id}SigsiuTree\">";
		$tree .= "<{$this->_tag} class=\"sigsiuTreeNode\" id=\"{$this->_id}stNode0\">";
		if ( !( in_array( $sid, $this->_disabled ) ) ) {
			$tree .= "<a href=\"{$sectionLink}\" id=\"{$this->_id}_imgFolderUrl0\"><img id=\"{$this->_id}0\" src=\"{$this->_images['root']}\" alt=\"{$sectionName}\"/></a>";
		}
		else {
			$tree .= "<img id=\"{$this->_id}0\" src=\"{$this->_images['root']}\" alt=\"{$sectionName}\"/>";
		}
		if ( !( in_array( $sid, $this->_disabled ) ) ) {
			$tree .= "<a href=\"{$sectionLink}\"  rel=\"{$sid}\" data-sid=\"{$sid}\" class=\"treeNode\" id=\"{$this->_id}_CatUrl0\">{$sectionName}</a>";
		}
		else {
			$tree .= $sectionName;
		}
		$tree .= "</{$this->_tag}>";
		$tree .= "<{$this->_tag} id=\"{$this->_id}\" class=\"clip\" style=\"display: block;\">";

		if ( count( $childs ) ) {
			foreach ( $childs as $cat ) {
				$countNodes--;
				// clean string produces htmlents and these are invalid in XML
				$catName   = /*$this->cleanString*/
					( $cat->get( 'name' ) );
				$hasChilds = count( $cat->getChilds( 'category' ) );
				$cid       = $cat->get( 'id' );
				$url       = $this->parseLink( $cat );
				$disabled  = ( in_array( $cid, $this->_disabled ) ) ? true : false;

				$tree .= "<{$this->_tag} class=\"sigsiuTreeNode\" id=\"{$this->_id}stNode{$cid}\">";

				if ( $hasChilds ) {
					if ( $countNodes == 0 && !$disabled ) {
						$lastNode = $cid;
						$tree .= "<a href=\"javascript:{$this->_id}_stmExpand( {$cid}, 0, {$this->_pid} );\" id=\"{$this->_id}_imgUrlExpand{$cid}\"><img src=\"{$this->_images[ 'plusBottom' ]}\" id=\"{$this->_id}_imgExpand{$cid}\" style=\"border-style:none;\" alt=\"expand\"/></a>";
						$matrix .= "{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'plusBottom' );";
					}
					elseif ( !$disabled ) {
						$tree .= "<a href=\"javascript:{$this->_id}_stmExpand( {$cid}, 0, {$this->_pid} );\" id=\"{$this->_id}_imgUrlExpand{$cid}\"><img src=\"{$this->_images[ 'plus' ]}\" id=\"{$this->_id}_imgExpand{$cid}\" style=\"border-style:none;\" alt=\"expand\"/></a>";
						$matrix .= "{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'plus' );";
					}
					else {
						$tree .= "<img src=\"{$this->_images[ 'join' ]}\" id=\"{$this->_id}_imgExpand{$cid}\" style=\"border-style:none;\" alt=\"expand\"/>";
						$matrix .= "{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'plus' );";
					}
				}
				else {
					if ( $countNodes == 0 && !$disabled ) {
						$lastNode = $cid;
						$tree .= "<img src=\"{$this->_images[ 'joinBottom' ]}\" style=\"border-style:none;\" id=\"{$this->_id}_imgJoin{$cid}\" alt=\"\"/>";
						$matrix .= "{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'join' );";
					}
					elseif ( !$disabled ) {
						$tree .= "<img src=\"{$this->_images[ 'join' ]}\" style=\"border-style:none;\" id=\"{$this->_id}_imgJoin{$cid}\" alt=\"\"/>";
						$matrix .= "{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'joinBottom' );";
					}
					else {
						$tree .= "<img src=\"{$this->_images[ 'joinBottom' ]}\" id=\"{$this->_id}_imgExpand{$cid}\" style=\"border-style:none;\" alt=\"expand\"/>";
						$matrix .= "{$this->_id}_stmImgMatrix[ {$cid} ] = new Array( 'plus' );";
					}

				}
				if ( !$disabled ) {
					$tree .= "<a href=\"{$url}\" id=\"{$this->_id}_imgFolderUrl{$cid}\"><img src=\"{$this->_images[ 'folder' ]}\" style=\"border-style:none;\" id=\"{$this->_id}_imgFolder{$cid}\" alt=\"\"/></a><a href=\"{$url}\" rel=\"{$cid}\" data-sid=\"{$cid}\" class=\"treeNode\" id=\"{$this->_id}_CatUrl{$cid}\">{$catName}</a>";
				}
				else {
					$tree .= "<img src=\"{$this->_images[ 'disabled' ]}\" style=\"border-style:none;\" id=\"{$this->_id}_imgFolder{$cid}\" alt=\"\"/>{$catName}</a>";
				}

				$tree .= "</{$this->_tag}>";
				if ( $hasChilds && !$disabled ) {
					$tree .= "<{$this->_tag} id=\"{$this->_id}_childsContainer{$cid}\" class=\"clip\" style=\"display: block; display:none;\"></{$this->_tag}>";
				}
			}
		}
		$tree .= "</{$this->_tag}>";
		$tree .= "</{$this->_tag}>";
		if ( $this->_id == "sobiCats" ) {
			$tree .= "</{$this->_tag}>";
		}
		$this->createScript( $lastNode, $childs, $matrix, $head );
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
		Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), [ &$childs ] );
		SPFactory::mainframe()->cleanBuffer();
		header( 'Content-type: application/xml' );
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		echo "\n<root>";
		if ( count( $childs ) ) {
			foreach ( $childs as $cat ) {
				$catName   = $this->cleanString( $cat->get( 'name' ) );
				$hasChilds = count( $cat->getChilds( 'category' ) );
				$cid       = $cat->get( 'id' );
				$pid       = $cat->get( 'parent' );
				$url       = preg_replace( '/&(?![#]?[a-z0-9]+;)/i', '&amp;', $this->parseLink( $cat ) );
				$disabled  = ( in_array( $cid, $this->_disabled ) ) ? true : false;
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
		$func   = $this->_id . '_stmExpand';
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
	 * @param $lastNode
	 * @param $childs
	 * @param $matrix
	 * @param $head
	 */
	private function createScript( $lastNode, $childs, $matrix, $head )
	{
		$params                    = [];
		$params[ 'ID' ]            = $this->_id;
		$params[ 'LAST_NODE' ]     = ( string ) $lastNode;
		$params[ 'IMAGES_ARR' ]    = null;
		$params[ 'IMAGES_MATRIX' ] = $matrix;
		foreach ( $this->_images as $img => $loc ) {
			$params[ 'IMAGES_ARR' ] .= "{$this->_id}_stmImgs[ '{$img}' ] = '{$loc}';";
		}
		$params[ 'URL' ]      = Sobi::Url(
			[
				'task'   => $this->_task,
				'sid'    => $this->_sid,
				'out'    => 'xml',
				'expand' => '__JS__',
				'pid'    => '__JS2__'
			],
			true, false
		);
		$params[ 'URL' ]      = str_replace( '__JS__', '" + ' . $this->_id . '_stmcid + "', $params[ 'URL' ] );
		$params[ 'URL' ]      = str_replace( '__JS2__', '" + ' . $this->_id . '_stmPid + "', $params[ 'URL' ] );
		$params[ 'FAIL_MSG' ] = Sobi::Txt( 'AJAX_FAIL' );
		$params[ 'TAG' ]      = $this->_tag;
		$params[ 'SPINNER' ]  = Sobi::FixPath( Sobi::Cfg( 'media_folder_live' ) . '/adm/spinner.gif' );
		Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), [ &$params ] );
		$head->addJsVarFile( 'tree', md5( count( $childs, COUNT_RECURSIVE ) . $this->_id . $this->_sid . $this->_task . serialize( $params ) ), $params );
	}

	/**
	 * @param int $sid
	 * @param bool $count
	 *
	 * @return SPSection
	 */
	private function getChilds( $sid, $count = false )
	{
		$childs = [];
		/* @var SPdb $db */
		try {
			$ids = SPFactory::db()
				->select( 'id', 'spdb_relations', [ 'pid' => $sid, 'oType' => 'category' ] )
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
					$childs[] = $child;
				}
			}
		}
		uasort( $childs, [ $this, 'sortChilds' ] );

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
	 *
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
	 *
	 * @param SPObject $cat
	 *
	 * @return string
	 */
	private function parseLink( $cat )
	{
		static $placeHolders = [
			'{sid}',
			'{name}',
			'{introtext}',
		];
		$replacement = [
			$cat->get( 'id' ),
			$cat->get( 'name' ),
			$cat->get( 'introtext' ),
		];
		$link        = str_replace( $placeHolders, $replacement, $this->_url );
		Sobi::Trigger( 'SigsiuTree', ucfirst( __FUNCTION__ ), [ &$link ] );

		return $link;
	}

	/**
	 * cleaning string for javascript
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	private function cleanString( $string )
	{
		$string = htmlspecialchars( $string, ENT_COMPAT, 'UTF-8' );
		$string = str_replace( '&amp;', '&#38;', $string );

		return $string;
	}
}
