<?php
/**
 * @version: $Id: inbox.php 1898 2011-09-22 14:13:00Z Radek Suski $
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
 * $Date: 2011-09-22 16:13:00 +0200 (Thu, 22 Sep 2011) $
 * $Revision: 1898 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/opt/fields/inbox.php $
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
	protected $method = 'mselect';
	/** @var bool */
	protected $modal = false;
	/** @var int */
	protected $catsMaxLimit = 10;
	/** @var bool */
	protected $childs = true;
	/** @var int */
	protected $width = 100;
	/** @var int */
	protected $height = 100;
	/** @var string */
	protected $fixedCid = '';
	/** @var array */
	protected $_selectedCats = array();
	/** @var array */
	protected $_cats = array();
	/** @var bool */
	protected $isPrimary = false;
	/** @var string */
	protected $searchMethod = 'select';
	/** @var int */
	protected $searchWidth = 100;
	/** @var int */
	protected $searchHeight = 100;

	/**
	 * Shows the field in the edit entry or add entry form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function field( $return = false )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}

		$this->_selectedCats = $this->getRaw();
		$this->loadCategories();
		if ( !( $this->_selectedCats ) && $this->sid ) {
			$entry = SPFactory::Entry( $this->sid );
			$this->_selectedCats = array_keys( $entry->get( 'categories' ) );
		}
		else {
			$this->_selectedCats = SPConfig::unserialize( $this->_selectedCats );
		}
		switch ( $this->method ) {
			case 'tree':
				$field = $this->tree();
				break;
			case 'select':
				$field = $this->select();
				break;
			case 'mselect':
				$this->showLabel = false;
				$field = $this->mSelect();
				break;
			case 'fixed':
				$this->showLabel = false;
				return true;
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

	}

	protected function select()
	{
		if ( count( $this->_cats ) ) {
			$values = array();
			$params = array(
				'id' => $this->nid,
				'class' => 'required ' . $this->cssClass
			);
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

	protected function mSelect()
	{
		if ( count( $this->_cats ) ) {
			$values = array();
			$params = array(
				'id' => $this->nid,
				'class' => 'required ' . $this->cssClass
			);
			if ( $this->width && $this->height ) {
				$params[ 'style' ] = "width: {$this->width}px; height: {$this->height}px";
			}
			$this->createValues( $this->_cats, $values, Sobi::Cfg( 'category_chooser.margin_sign', '-' ) );
			$selected = $this->_selectedCats;
			if ( count( $selected ) ) {
				foreach ( $selected as $i => $v ) {
					$selected[ $i ] = (string)$v;
				}
			}
			$field = SPHtml_Input::select( $this->nid, $values, $selected, true, $params );
			$opt = json_encode( array( 'id' => $this->nid, 'limit' => $this->catsMaxLimit ) );
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
			$params = array();
			if ( $selector || $cat[ 'type' ] == 'section' ) {
				if ( $cat[ 'type' ] == 'section' || ( count( ( $cat[ 'childs' ] ) ) && !( $this->childs ) ) ) {
					$params[ 'disabled' ] = 'disabled';
				}
			}
			$result[ ] = array(
				'label' => $margin . ' ' . $cat[ 'name' ],
				'value' => $cat[ 'sid' ],
				'params' => $params
			);
			if ( count( ( $cat[ 'childs' ] ) ) ) {
				$this->createValues( $cat[ 'childs' ], $result, Sobi::Cfg( 'category_chooser.margin_sign', '-' ) . $margin, $selector );
			}
		}
	}

	protected function loadCategories()
	{
		if ( !( $this->_cats ) || !( count( $this->_cats ) ) ) {
			$this->_cats = SPFactory::cache()
					->getVar( 'categories_tree', Sobi::Section() );
			if ( !( $this->_cats ) || !( count( $this->_cats ) ) ) {
				$this->travelCats( Sobi::Section(), $this->_cats, true );
				SPFactory::cache()
						->addVar( $this->_cats, 'categories_tree', Sobi::Section() );
			}
		}
	}

	private function travelCats( $sid, &$cats, $init = false )
	{
		$category = SPFactory::Model( $init == true ? 'section' : 'category' );
		$category->init( $sid );
		$cats[ $sid ] = array(
			'sid' => $sid,
			'state' => $category->get( 'state' ),
			'name' => $category->get( 'name' ),
			'type' => $category->get( 'oType' ),
			'childs' => array(),
		);
		$childs = $category->getChilds( 'category', true );
		if ( count( $childs ) ) {
			foreach ( $childs as $id => $name ) {
				$this->travelCats( $id, $cats[ $sid ][ 'childs' ] );
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
		$this->loadCategories();
		if ( count( $this->_cats ) ) {
			if ( $this->searchMethod == 'select' ) {
				$values = array( '' => Sobi::Txt( 'FMN.CC_SEARCH_SELECT_CAT' ) );
			}
			else {
				$values = array();
			}
			$this->createValues( $this->_cats, $values, Sobi::Cfg( 'category_chooser.margin_sign', '-' ), false );
			$selected = $this->_selected;
			if ( $selected ) {
				if ( is_numeric( $selected ) ) {
					$selected = array( $selected );
				}
				foreach ( $selected as $i => $v ) {
					$selected[ $i ] = (string)$v;
				}
			}
		}
		if ( $this->searchMethod == 'select' ) {
			$params = array(
				'id' => $this->nid,
				'class' => $this->cssClass
			);
			if ( $this->searchWidth ) {
				$params[ 'style' ] = "width: {$this->searchWidth}px;";
			}
			$field = SPHtml_Input::select( $this->nid, $values, $selected, false, $params );
		}
		elseif ( $this->searchMethod == 'mselect' ) {
			$params = array(
				'id' => $this->nid,
				'class' => $this->cssClass
			);
			if ( $this->searchWidth && $this->searchHeight ) {
				$params[ 'style' ] = "width: {$this->searchWidth}px; height: {$this->searchHeight}px";
			}
			$field = SPHtml_Input::select( $this->nid, $values, $selected, true, $params );
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
	 * @param string $tsid
	 * @param string $request
	 * @return void
	 */
	public function submit( &$entry, $tsid = null, $request = 'POST' )
	{
		$data = $this->verify( $entry, $request );
		if ( strlen( $data ) ) {
			return SPRequest::search( $this->nid, $request );
		}
		else {
			return array();
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
		$data = SPRequest::arr( $this->nid, array(), $request );
		if ( !( $data ) ) {
			$dataString = SPRequest::int( $this->nid, 0, $request );
			if ( $dataString ) {
				$data = array( $dataString );
			}
		}
		else {
			if ( count( $data ) > $this->catsMaxLimit ) {
				$data = array_slice( $data, 0, $this->catsMaxLimit );
			}
		}
		$dexs = count( $data );
		/* check if it was required */
		if ( $this->required && !( $dexs ) ) {
			throw new SPException( SPLang::e( 'FIELD_REQUIRED_ERR', $this->name ) );
		}
		/* check if there was an adminField */
		if ( $this->adminField && $dexs ) {
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
		if ( !( $dexs ) ) {
			$data = array();
		}
		$this->setData( $data );
		return $data;
	}


	/**
	 * Gets the data for a field and save it in the database
	 * @param SPEntry $entry
	 * @param string $request
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
			$data = array();
			if ( count( $fixed ) ) {
				foreach ( $fixed as $cid ) {
					$data[ ] = trim( $cid );
				}
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
		$db =& SPFactory::db();

		/* collect the needed params */
		$params = array();
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
		$cats = SPFactory::registry()->get( 'request_categories', array() );
		$cats = array_unique( array_merge( $cats, $data ) );
		SPFactory::registry()->set( 'request_categories', $cats );
		if ( $this->method == 'select' && $this->isPrimary ) {
			$entry->set( $data[ 0 ], 'parent' );
		}
	}


	/**
	 * @param string $data
	 * @param int $section
	 * @return array
	 */
	public function searchNarrowResults( $data, &$results )
	{
		if ( count( $data ) && count( $results ) ) {
			if ( is_numeric( $data ) ) {
				$data = array( $data );
			}
			$this->loadCategories();
			if ( count( $this->_cats ) ) {
				$categories = array();
				foreach ( $data as $cid ) {
					$this->getChildCategories( $this->_cats, $cid, $categories );
				}
			}
			if ( count( $categories ) ) {
				$db = SPFactory::db();
				foreach ( $results as $index => $sid ) {
					$relation = $db
							->select( 'id', 'spdb_relations', array( 'id' => $sid, 'oType' => 'entry', 'pid' => $categories ) )
							->loadResultArray();
					if ( !( count( $relation ) ) ) {
						unset( $results[ $index ] );
					}
				}
			}
		}
		return $results;
	}

	private function getChildCategories( $categories, $cid, &$results )
	{
		foreach ( $categories as $id => $category ) {
			if ( $cid == $id ) {
				$results[ ] = $id;
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
		foreach ( $categories as $cid => $category ) {
			$results[ ] = $cid;
			if ( count( $category[ 'childs' ] ) ) {
				$this->categoryChilds( $results, $category[ 'childs' ] );
			}
		}
	}
}
