<?php
/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.fieldtype' );

/**
 * @author Radek Suski
 * @version 1.1
 * @created Sat, Oct 20, 2012 11:52:12
 */
class SPField_Category extends SPFieldType implements SPFieldInterface
{
	/** @var string */
	protected $cssClass = 'spClassCategory';
	/** * @var string */
	protected $cssClassEdit = 'spClassEditCategory';
	/** * @var string */
	protected $cssClassSearch = 'spClassSearchCategory';
	/** @var string */
	protected $method = 'mselect';
	/** @var bool */
	protected $modal = false;
	/** @var int */
	protected $catsMaxLimit = 10;
	/** @var bool */
	protected $catsWithChilds = true;
	/** @var int */
	protected $width = 200;
	/** * @var int */
	protected $bsWidth = 4;
	/** @var int */
	protected $height = 150;
	/** @var string */
	protected $fixedCid = '';
	/** @var array */
	protected $_selectedCats = [];
	/** @var array */
	protected $_cats = [];
	/** @var bool */
	protected $isPrimary = false;
	/** @var string */
	protected $searchMethod = 'select';
	/** @var int */
	protected $searchWidth = 200;
	/** * @var int */
	protected $bsSearchWidth = 4;
	/** @var int */
	protected $searchHeight = 100;
	/** @var string */
	protected $orderCatsBy = 'name.asc';
	/** @var string */
	protected $searchOrderCatsBy = 'name.asc';
	/** @var string */
	protected static $_filter = '';
	/*** @var bool */
	protected $suggesting = false;

	public function __construct( &$field )
	{
		parent::__construct( $field );
		$this->orderCatsBy = $this->orderCatsBy ? $this->orderCatsBy : 'position.asc';
		$this->searchOrderCatsBy = $this->searchOrderCatsBy ? $this->searchOrderCatsBy : 'position.asc';
		if ( $this->method == 'fixed' ) {
			$this->editable = true;
			$this->editLimit = 5;
		}
		if ( $this->method == 'fixed' && in_array( SPRequest::task(), [ 'entry.add', 'entry.edit' ] ) ) {
			$this->isOutputOnly = true;
		}
		if ( $this->method == 'fixed' ) {
			$this->editable = true;
		}
	}

	public function loadData()
	{
		if ( $this->method == 'fixed' ) {
			$this->editable = true;
			// meeeh ;)
			$this->__call( 'set', [ 'editable', true ] );
		}
	}

	public function cleanData()
	{
		$this->_selectedCats = $this->getRaw();
		if ( !( is_numeric( $this->_selectedCats ) || is_array( $this->_selectedCats ) ) ) {
			if ( is_string( $this->_selectedCats ) && strstr( $this->_selectedCats, '://' ) ) {
				$this->_selectedCats = SPFactory::config()->structuralData( $this->_selectedCats );
			}
			elseif ( is_string( $this->_selectedCats ) && strstr( $this->_selectedCats, ',' ) ) {
				$this->_selectedCats = explode( ',', $this->_selectedCats );
			}
			else {
				$this->_selectedCats = SPConfig::unserialize( $this->_selectedCats );
			}
		}
		if ( !( $this->_selectedCats ) ) {
			if ( SPRequest::task() == 'entry.add' && SPRequest::sid() != Sobi::Section() ) {
				$this->_selectedCats = [ SPRequest::sid() ];
			}
		}
		return $this->_selectedCats;
	}

	/**
	 * Shows the field in the edit entry or add entry form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function field( $return = false )
	{
		self::$_filter = explode( '.', $this->orderCatsBy );
		if ( !( $this->enabled ) ) {
			return false;
		}
		if ( !( $this->sid ) ) {
			$this->sid = SPRequest::sid();
		}
		$this->_selectedCats = $this->cleanData();
		$this->loadCategories();
		if ( !( $this->_selectedCats ) && $this->sid ) {
			$entry = SPFactory::Entry( $this->sid );
			$this->_selectedCats = array_keys( $entry->get( 'categories' ) );
		}
		else {
			$this->cleanData();
		}
		if ( !( $this->_selectedCats ) || !( count( $this->_selectedCats ) ) ) {
			$sid = SPRequest::sid();
			if ( $sid != Sobi::Section() && $sid != $this->sid ) {
				$this->_selectedCats = [ SPRequest::sid() ];
			}
		}
		$this->showLabel = true;
		if ( !( ( int )$this->catsMaxLimit ) ) {
			$this->catsMaxLimit = 10;   //set to standard value
		}
		if ( count( $this->_selectedCats ) > $this->catsMaxLimit ) {
			$this->_selectedCats = array_slice( $this->_selectedCats, 0, $this->catsMaxLimit );
		}
		switch ( $this->method ) {
			case 'fixed':
				$this->showLabel = false;
				$this->isOutputOnly = true;
				return null;
				break;
			case 'tree':
				$field = $this->tree();
				break;
			case 'select':
				$field = $this->select();
				break;
			case 'pselect':
				$field = $this->pselect();
				break;
			case 'mselect':
				$field = $this->mSelect();
				break;
		}
		if ( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	protected function tree()
	{
		$selector = null;
		$selectedCategories = [];
		$tree = SPFactory::Instance( 'mlo.tree', Sobi::Cfg( 'list.categories_ordering' ), [ 'preventParents' => !( $this->catsWithChilds ) ] );
		$tree->setHref( '#' );
		$tree->setTask( 'category.chooser' );
		$tree->setId( $this->nid );
		$tree->disable( Sobi::Section() );
		$tree->init( Sobi::Section() );
		$params = [];
		$params[ 'maxcats' ] = $this->catsMaxLimit;
		$params[ 'field' ] = $this->nid;
		$params[ 'preventParents' ] = !( $this->catsWithChilds );

		$setheight = '';
		if ( strlen( $this->height ) ) {
			$setheight = " style=\"max-height: {$this->height}px;\"";
		}
		$addBtParams = [ 'class' => 'btn btn-sm btn-small btn-default' ];
		$delBtParams = [ 'class' => 'btn btn-sm btn-small btn-default' ];
		$selectParams = [];
		SPFactory::header()
				->addJsFile( 'opt.field_category_tree' )
				->addJsCode( 'SobiPro.jQuery( document ).ready( function () { new SigsiuTreeEdit( ' . json_encode( $params ) . '); } );' );

		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			$selector = $selector . '<div class="row"><div class="tree col-sm-6"' . $setheight . '>' . $tree->display( true ) . '</div>';
		}
		else {
			$selector = $selector . '<div class="row-fluid"><div class="tree span6"' . $setheight . '>' . $tree->display( true ) . '</div>';
		}
		if ( count( $this->_selectedCats ) ) {
			$selected = SPLang::translateObject( $this->_selectedCats, 'name', 'category' );
			if ( count( $selected ) ) {
				$count = 0;
				foreach ( $selected as $category ) {
					if ( $category[ 'id' ] == $this->sid && SPRequest::task() != 'entry.add' ) {
						continue;
					}
					$selectedCategories[ $category[ 'id' ] ] = $category[ 'value' ];
					$count++;
					if ( $count == $this->catsMaxLimit ) {
						break;
					}
				}
			}
		}
		if ( count( $selectedCategories ) >= $this->catsMaxLimit ) {
			$addBtParams[ 'disabled' ] = 'disabled';
			$selectParams[ 'readonly' ] = 'readonly';
		}
		elseif ( !( count( $selectedCategories ) ) ) {
			$delBtParams[ 'disabled' ] = 'disabled';
		}
		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			$selector .= '<div class="selected col-sm-6">';
			$treeclass = 'SigsiuTree container-fluid';
		}
		else {
			$selector .= '<div class="selected span6">';
			$treeclass = 'SigsiuTree';
			if ( defined( 'SOBIPRO_ADM' ) ) {
				$treeclass .= ' spAdminEntry';
			}
		}
		if ( $this->height > 100 ) {
			$selectParams[ 'style' ] = "min-height: {$this->height}px";
		}
		$selector .= SPHtml_Input::select( $this->nid . '_list', $selectedCategories, null, true, $selectParams );
		$selector .= SPHtml_Input::hidden( $this->nid, 'json://' . json_encode( array_keys( $selectedCategories ) ) );
		$selector .= '</div></div>';

		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			$selector .= '<div class="row"><div class="spCatSelectBtns buttons col-sm-12">';
		}
		else {
			$selector .= '<div class="row-fluid"><div class="spCatSelectBtns buttons span12">';
		}
		$selector .= SPHtml_Input::button( 'addCategory', '<i class="icon-plus" ></i>' . Sobi::Txt( 'CC.ADD_BT' ), $addBtParams );
		$selector .= SPHtml_Input::button( 'removeCategory', '<i class="icon-minus-sign" ></i>' . Sobi::Txt( 'CC.DEL_BT' ), $delBtParams );
		$selector .= '</div></div>';
		$selector = '<div class="' . $treeclass . '" id="' . $this->nid . '_canvas">' . $selector . '</div>';

		if ( $this->modal ) {
			$modalclass = 'modaltree modal hide';
			if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
				$modalclass = 'modaltree modal fade';
			}

			$selector = SPHtml_Input::modalWindow( Sobi::Txt( 'EN.SELECT_CAT_PATH' ), $this->nid . '_modal', $selector, $modalclass, 'CLOSE', null );
			$field = SPHtml_Input::button( 'select-category', Sobi::Txt( 'EN.SELECT_CAT_PATH' ), [ 'class' => 'btn btn-primary btn-sigsiu', 'href' => '#' . $this->nid . '_modal', 'data-toggle' => 'modal', 'id' => $this->nid . '_modal_fire' ] );
			return $field . $selector;
		}
		else {
			return $selector;
		}
	}

	protected function select()
	{
		if ( count( $this->_cats ) ) {
			$values = [];
			$class = $this->cssClass;
			if ( defined( 'SOBIPRO_ADM' ) ) {
				if ( $this->bsWidth ) {
					$width = SPHtml_Input::_translateWidth( $this->bsWidth );
					$class .= ' ' . $width;
				}
			}
			$params = [
					'id' => $this->nid,
					'class' => 'required ' . $class
			];
			//still there for compatibility reason
			if ( $this->width ) {
				$params[ 'style' ] = "width: {$this->width}px;";
			}
			$this->createValues( $this->_cats, $values, Sobi::Cfg( 'category_chooser.margin_sign', '-' ) );
			$selected = $this->_selectedCats;
			if ( count( $selected ) ) {
				foreach ( $selected as $i => $v ) {
					$selected[ $i ] = (string)$v;
				}
			}
			$field = SPHtml_Input::select( $this->nid, $values, $selected, false, $params );
			return $field;
		}
	}

	public function ProxyLoadCategories()
	{
		if ( !( count( $this->_cats ) ) ) {
			self::$_filter = explode( '.', SPRequest::cmd( 'method' ) == 'search' ? $this->searchOrderCatsBy : $this->orderCatsBy );
			$this->loadCategories();
			SPFactory::mainframe()
					->cleanBuffer()
					->customHeader();
			echo json_encode( [ 'categories' => $this->_cats[ Sobi::Section() ][ 'childs' ] ] );
			exit;
		}
	}

	protected function pselect()
	{
		SPFactory::header()
				->addJsFile( 'opt.field_category_pselect' );
		$class = $this->cssClass;
		if ( defined( 'SOBIPRO_ADM' ) ) {
			if ( $this->bsWidth ) {
				$width = SPHtml_Input::_translateWidth( $this->bsWidth );
				$class .= ' ' . $width;
			}
		}
		$params = [
				'id' => $this->nid,
				'class' => 'ctrl-field-category required ' . $class
		];
		$selected = $this->_selectedCats;
		$params[ 'data' ][ 'task' ] = str_replace( '_', '.', $this->nid ) . '.loadCategories';
		$params[ 'data' ][ 'selected' ] = isset( $selected[ 0 ] ) ? $selected[ 0 ] : 0;
		if ( $this->width ) {
			$params[ 'style' ] = "width: {$this->width}px;";
		}
		$field = SPHtml_Input::select( $this->nid, null, null, false, $params );
		return $field;
	}

	protected function mSelect()
	{
		if ( count( $this->_cats ) ) {
			$values = [];
			$class = $this->cssClass;
			if ( defined( 'SOBIPRO_ADM' ) ) {
				if ( $this->bsWidth ) {
					$width = SPHtml_Input::_translateWidth( $this->bsWidth );
					$class .= ' ' . $width;
				}
			}
			$params = [
					'id' => $this->nid,
					'class' => 'required ' . $class
			];
			if ( $this->height ) {
				$params[ 'style' ] = "height: {$this->height}px";
			}
			$this->createValues( $this->_cats, $values, Sobi::Cfg( 'category_chooser.margin_sign', '-' ) );
			$selected = $this->_selectedCats;
			if ( count( $selected ) ) {
				foreach ( $selected as $i => $v ) {
					$selected[ $i ] = (string)$v;
				}
			}
			$field = SPHtml_Input::select( $this->nid, $values, $selected, true, $params );
			$opt = json_encode( [ 'id' => $this->nid, 'limit' => $this->catsMaxLimit ] );
			SPFactory::header()
					->addJsFile( 'opt.field_category' )
					->addJsCode( "SPCategoryChooser( {$opt} )" );
			return $field;
		}
	}

	private function createValues( $cats, &$result, $margin, $selector = true )
	{
		foreach ( $cats as $cat ) {
			if ( !( $cat[ 'state' ] ) && !( Sobi::Can( 'category', 'access', 'unpublished_any' ) ) ) {
				continue;
			}
			$params = [];
			if ( $selector || $cat[ 'type' ] == 'section' ) {
				if ( $cat[ 'type' ] == 'section' || ( count( ( $cat[ 'childs' ] ) ) && !( $this->catsWithChilds ) ) ) {
					$params[ 'disabled' ] = 'disabled';
				}
			}
			$result[] = [
					'label' => $margin . ' ' . $cat[ 'name' ],
					'value' => $cat[ 'sid' ],
					'params' => $params
			];
			if ( count( ( $cat[ 'childs' ] ) ) ) {
				$this->createValues( $cat[ 'childs' ], $result, Sobi::Cfg( 'category_chooser.margin_sign', '-' ) . $margin, $selector );
			}
		}
	}

	protected function loadCategories()
	{
		if ( ( !( $this->_cats ) || !( count( $this->_cats ) ) ) && SPLoader::path( 'etc.categories.' . Sobi::Lang() . '-' . Sobi::Section(), 'front', true, 'json' ) ) {
			$this->_cats = json_decode( SPFs::read( SPLoader::path( 'etc.categories.' . Sobi::Lang() . '-' . Sobi::Section(), 'front', true, 'json' ) ), true );
		}
		if ( !( $this->_cats ) || !( count( $this->_cats ) ) ) {
			$this->_cats = SPFactory::cache()
					->getVar( 'categories_tree', Sobi::Section() );
			if ( !( $this->_cats ) || !( count( $this->_cats ) ) ) {
				$this->travelCats( Sobi::Section(), $this->_cats, true );
				SPFactory::cache()
						->addVar( $this->_cats, 'categories_tree', Sobi::Section() );
			}
			$cache = json_encode( $this->_cats );
			if ( !( defined( 'SOBIPRO_ADM' ) ) ) {
				SPFs::write( SPLoader::path( 'etc.categories.' . Sobi::Lang() . '-' . Sobi::Section(), 'front', false, 'json' ), $cache );
			}

		}
		$this->sort( $this->_cats );
	}

	protected function sort( &$childs )
	{
		foreach ( $childs as &$array ) {
			if ( count( $array[ 'childs' ] ) ) {
				$this->sort( $array[ 'childs' ] );
				usort( $array[ 'childs' ], [ 'self', 'reorder' ] );
			}
		}
	}

	protected static function reorder( &$from, &$to )
	{
		if ( self::$_filter[ 1 ] == 'asc' ) {
			if ( !( is_numeric( $from[ self::$_filter[ 0 ] ] ) ) ) {
				return strcmp( $from[ self::$_filter[ 0 ] ], $to[ self::$_filter[ 0 ] ] ) > 0;
			}
			else {
				return $from[ self::$_filter[ 0 ] ] > $to[ self::$_filter[ 0 ] ];
			}
		}
		else {
			if ( !( is_numeric( $from[ self::$_filter[ 0 ] ] ) ) ) {
				return ( strcmp( $from[ self::$_filter[ 0 ] ], $to[ self::$_filter[ 0 ] ] ) ) < 0;
			}
			else {
				return $from[ self::$_filter[ 0 ] ] < $to[ self::$_filter[ 0 ] ];
			}
		}
	}

	private function travelCats( $sid, &$cats, $init = false )
	{
		$category = $init == true ? SPFactory::Section( $sid ) : SPFactory::Category( $sid );
		if ( $category->get( 'state' ) ) {
			$cats[ $sid ] = [
					'sid' => $sid,
					'state' => $category->get( 'state' ),
					'name' => $category->get( 'name' ),
					'type' => $category->get( 'oType' ),
					'position' => $category->get( 'position' ),
					'url' => Sobi::Url( [ 'title' => Sobi::Cfg( 'sef.alias', true ) ? $category->get( 'nid' ) : $category->get( 'name' ), 'sid' => $category->get( 'id' ) ] ),
					'childs' => [],
			];
			$childs = $category->getChilds( 'category' );
			if ( count( $childs ) ) {
				foreach ( $childs as $id => $name ) {
					$this->travelCats( $id, $cats[ $sid ][ 'childs' ] );
				}
			}
		}
	}

	/**
	 * Shows the field in the search form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function searchForm( $return = true )
	{
		self::$_filter = explode( '.', $this->orderCatsBy );
		$this->loadCategories( true );
		if ( count( $this->_cats ) ) {
			if ( $this->searchMethod == 'select' ) {
				$values = [ '' => Sobi::Txt( 'FMN.SEARCH_SELECT_CATEGORY' ) ];
			}
			else {
				$values = [];
			}
			$this->createValues( $this->_cats, $values, Sobi::Cfg( 'category_chooser.margin_sign', '-' ), false );
			$selected = $this->_selected;
			if ( $selected ) {
				if ( is_numeric( $selected ) ) {
					$selected = [ $selected ];
				}
				foreach ( $selected as $i => $v ) {
					$selected[ $i ] = (string)$v;
				}
			}
		}
		if ( $this->searchMethod == 'select' ) {
			$params = [
					'id' => $this->nid,
					'class' => $this->cssClass
			];
			//still there for compatibility reason
			if ( $this->searchWidth ) {
				$params[ 'style' ] = "width: {$this->searchWidth}px;";
			}
			$field = SPHtml_Input::select( $this->nid, $values, $selected, false, $params );
		}
		elseif ( $this->searchMethod == 'mselect' ) {
			$params = [
					'id' => $this->nid,
					'class' => $this->cssClass
			];
			if ( $this->searchHeight ) {
				$params[ 'style' ] = "height: {$this->searchHeight}px";
			}
			$field = SPHtml_Input::select( $this->nid, $values, $selected, true, $params );
		}
		elseif ( $this->searchMethod == 'pselect' ) {
			SPFactory::header()
					->addJsFile( 'opt.field_category_pselect' );
			$class = $this->cssClass;
			$params = [
					'id' => $this->nid,
					'class' => 'ctrl-field-category ' . $class
			];
			$params[ 'data' ][ 'task' ] = str_replace( '_', '.', $this->nid ) . '.loadCategories';
			$params[ 'data' ][ 'method' ] = 'search';
			$params[ 'data' ][ 'selected' ] = $selected[ 0 ];
			if ( $this->width ) {
				$params[ 'style' ] = "width: {$this->width}px;";
			}
			$field = SPHtml_Input::select( $this->nid, null, null, false, $params );
		}
		return $field;
	}

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		$attr = get_class_vars( __CLASS__ );
		return array_keys( $attr );
	}

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 * @param SPEntry $entry
	 * @param string $tsId
	 * @param string $request
	 * @return void
	 */
	public function submit( &$entry, $tsId = null, $request = 'POST' )
	{
		$data = $this->verify( $entry, $request );
		if ( is_string( $data ) && strlen( $data ) ) {
			return SPRequest::search( $this->nid, $request );
		}
		else {
			return [];
		}
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @throws SPException
	 * @return string
	 * @throw SPException
	 */
	private function verify( $entry, $request )
	{
		$data = SPRequest::arr( $this->nid, [], $request );
		if ( !( $data ) ) {
			$dataString = SPRequest::string( $this->nid, null, false, $request );
			if ( strstr( $dataString, '://' ) ) {
				$data = SPFactory::config()->structuralData( $dataString );
			}
			else {
				$dataString = SPRequest::int( $this->nid, 0, $request );
				if ( $dataString ) {
					$data = [ $dataString ];
				}
			}
		}
		else {
			if ( !( ( int )$this->catsMaxLimit ) ) {
				$this->catsMaxLimit = 10;   //set to standard value
			}
			if ( count( $data ) > $this->catsMaxLimit && count( $data ) > 1 ) {
				$data = array_slice( $data, 0, $this->catsMaxLimit );
			}
		}
		$dexs = count( $data );
		/* check if it was required */
		if ( $this->required && !( $dexs ) && $this->method != 'fixed' ) {
			throw new SPException( SPLang::e( 'FIELD_REQUIRED_ERR', $this->name ) );
		}
		/* check if there was an adminField */
		if ( $this->adminField && $dexs && $this->method != 'fixed' ) {
			if ( !( Sobi:: Can( 'entry.adm_fields.edit' ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_AUTH', $this->name ) );
			}
		}
		/* check if it was free */
		if ( !( $this->isFree ) && $this->fee && $dexs ) {
			SPFactory::payment()->add( $this->fee, $this->name, $entry->get( 'id' ), $this->fid );
		}
		/* check if it was editLimit */
		if ( $this->editLimit == 0 && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_EXP', $this->name ) );
		}
		/* check if it was editable */
		if ( !( $this->editable ) && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs && $entry->get( 'version' ) > 1 ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_NOT_ED', $this->name ) );
		}
		if ( !( $this->catsWithChilds ) && $dexs ) {
			foreach ( $data as $cid ) {
				$cat = SPFactory::Category( $cid );
				if ( count( $cat->getChilds( 'category' ) ) ) {
					throw new SPException( SPLang::e( 'CAT_FIELD_SELECT_CAT_WITH_NO_CHILDS', $this->name ) );
				}
			}
		}
		if ( !( $dexs ) ) {
			$data = [];
		}
		$this->setData( $data );
		return $data;
	}


	/**
	 * This function is used for the case that a field wasn't used for some reason while saving an entry
	 * But it has to perform some operation
	 * E.g. Category field is set to be administrative and isn't used
	 * but it needs to pass the previously selected categories to the entry model
	 * @param SPEntry $entry
	 * @param string $request
	 * @return bool
	 * */
	public function finaliseSave( $entry, $request = 'post' )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}
		$cats = SPFactory::registry()->get( 'request_categories', [] );
		$cats = array_unique( array_merge( $cats, $this->cleanData() ) );
		SPFactory::registry()->set( 'request_categories', $cats );
		return true;
	}

	/**
	 * Gets the data for a field and save it in the database
	 * @param SPEntry $entry
	 * @param string $request
	 * @throws SPException
	 * @return bool
	 */
	public function saveData( &$entry, $request = 'POST' )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}
		if ( $this->method == 'fixed' ) {
			$fixed = $this->fixedCid;
			$fixed = explode( ',', $fixed );
			$data = [];
			if ( count( $fixed ) ) {
				foreach ( $fixed as $cid ) {
					$data[] = trim( $cid );
				}
			}
			if ( !( count( $data ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_CC_FIXED_CID_NOT_SELECTED', $this->name ) );
			}

		}
		else {
			$data = $this->verify( $entry, $request );
		}
		$time = SPRequest::now();
		$IP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$uid = Sobi::My( 'id' );

		/* if we are here, we can save these data */
		/* @var SPdb $db */
		$db = SPFactory::db();

		/* collect the needed params */
		$params = [];
		$params[ 'publishUp' ] = $entry->get( 'publishUp' );
		$params[ 'publishDown' ] = $entry->get( 'publishDown' );
		$params[ 'fid' ] = $this->fid;
		$params[ 'sid' ] = $entry->get( 'id' );
		$params[ 'section' ] = Sobi::Reg( 'current_section' );
		$params[ 'lang' ] = Sobi::Lang();
		$params[ 'enabled' ] = $entry->get( 'state' );
		$params[ 'params' ] = null;
		$params[ 'options' ] = null;
		$params[ 'baseData' ] = SPConfig::serialize( $data );
		$params[ 'approved' ] = $entry->get( 'approved' );
		$params[ 'confirmed' ] = $entry->get( 'confirmed' );
		/* if it is the first version, it is new entry */
		if ( $entry->get( 'version' ) == 1 ) {
			$params[ 'createdTime' ] = $time;
			$params[ 'createdBy' ] = $uid;
			$params[ 'createdIP' ] = $IP;
		}
		$params[ 'updatedTime' ] = $time;
		$params[ 'updatedBy' ] = $uid;
		$params[ 'updatedIP' ] = $IP;
		$params[ 'copy' ] = !( $entry->get( 'approved' ) );
		if ( Sobi::My( 'id' ) == $entry->get( 'owner' ) ) {
			--$this->editLimit;
		}
		$params[ 'editLimit' ] = $this->editLimit;

		/* save it */
		try {
			/* Notices:
				* If it was new entry - insert
				* If it was an edit and the field wasn't filled before - insert
				* If it was an edit and the field was filled before - update
				*     " ... " and changes are not autopublish it should be insert of the copy .... but
				* " ... " if a copy already exist it is update again
				* */
			$db->insertUpdate( 'spdb_field_data', $params );
		} catch ( SPException $x ) {
			Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

		/* if it wasn't edited in the default language, we have to try to insert it also for def lang */
		if ( Sobi::Lang() != Sobi::DefLang() ) {
			$params[ 'lang' ] = Sobi::DefLang();
			try {
				$db->insert( 'spdb_field_data', $params, true, true );
			} catch ( SPException $x ) {
				Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		/** Last important thing - join selected categories  */
		$cats = SPFactory::registry()->get( 'request_categories', [] );
		$cats = array_unique( array_merge( $cats, $data ) );
		SPFactory::registry()->set( 'request_categories', $cats );
		if ( $this->method == 'select' && $this->isPrimary ) {
			$db->update( 'spdb_object', [ 'parent' => $data[ 0 ] ], [ 'id' => $params[ 'sid' ] ] );
		}
	}


	/**
	 * @param string $data
	 * @param $results
	 * @param $priorities
	 * @return array
	 */
	public function searchNarrowResults( $data, &$results, &$priorities )
	{
		if ( is_numeric( $data ) ) {
			$data = [ $data ];
		}
		if ( count( $data ) ) {
			$this->loadCategories();
			if ( count( $this->_cats ) ) {
				$categories = [];
				foreach ( $data as $cid ) {
					$this->getChildCategories( $this->_cats, $cid, $categories );
				}
			}
			if ( count( $categories ) ) {
				// narrowing down - it's a special method instead the regular search because we would have to handle too much data in the search
				if ( count( $results ) ) {
					foreach ( $results as $index => $sid ) {
						$relation = SPFactory::db()
								->dselect( 'id', 'spdb_relations', [ 'id' => $sid, 'oType' => 'entry', 'pid' => $categories ] )
								->loadResultArray();
						if ( !( count( $relation ) ) ) {
							unset( $results[ $index ] );
						}
					}

				} // it's a real search now - in case we hadn't nothing to filter out
				else {
					$results = SPFactory::db()
							->dselect( 'id', 'spdb_relations', [ 'oType' => 'entry', 'pid' => $categories ] )
							->loadResultArray();
					$priorities[ $this->priority ] = $results;
				}
			}
		}
		return $results;
	}

	private function getChildCategories( $categories, $cid, &$results )
	{
		foreach ( $categories as $category ) {
			if ( $cid == $category[ 'sid' ] ) {
				$results[] = $category[ 'sid' ];
				$this->categoryChilds( $results, $category[ 'childs' ] );
				break;
			}
			if ( count( $category[ 'childs' ] ) ) {
				$this->getChildCategories( $category[ 'childs' ], $cid, $results );
			}
		}
	}

	private function categoryChilds( &$results, $categories )
	{
		foreach ( $categories as $category ) {
			$results[] = $category[ 'sid' ];
			if ( count( $category[ 'childs' ] ) ) {
				$this->categoryChilds( $results, $category[ 'childs' ] );
			}
		}
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @return string
	 */
	public function validate( $entry, $request )
	{
		return $this->verify( $entry, $request );
	}
}
