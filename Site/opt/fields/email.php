<?php
/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.url' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 15-Jan-2009 14:33:15
 */
class SPField_Email extends SPField_Url implements SPFieldInterface
{
	/*** @var string */
	protected $labelsLabel = "Contact us by Email";
	/*** @var bool */
	protected $botProtection = true;
	/*** @var string */
	protected $dType = 'special';
	/** @var string */
	protected $cssClass = 'spClassEmail';
	/** * @var string */
	protected $cssClassView = 'spClassViewEmail';
	/** * @var string */
	protected $cssClassEdit = 'spClassEditEmail';
	/** @var bool */
	static private $CAT_FIELD = true;
	/*** @var bool */
	protected $suggesting = false;

	/**
	 * Shows the field in the edit entry or add entry form
	 *
	 * @param bool $return return or display directly
	 *
	 * @return string
	 */
	public function field( $return = false )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}
		$field = null;

		$fdata = Sobi::Reg( 'editcache' );
		if ( $fdata && is_array( $fdata ) ) {
			$raw = $this->fromCache( $fdata );
		}
		else {
			$raw = SPConfig::unserialize( $this->getRaw() );
		}

		if ( $this->ownLabel ) {
			$fieldTitle = null;

			$class = $this->cssClass . 'Title';
			if ( defined( 'SOBIPRO_ADM' ) ) {
				if ( $this->bsWidth ) {
					$width = SPHtml_Input::_translateWidth( $this->bsWidth );
					$class .= ' ' . $width;
				}
			}
			$params = [ 'id' => $this->nid, 'size' => $this->labelWidth, 'class' => $class ];
			if ( $this->labelMaxLength ) {
				$params[ 'maxlength' ] = $this->labelMaxLength;
			}
			if ( $this->labelWidth ) {
				$params[ 'style' ] = "width: {$this->labelWidth}px;";
			}
			if ( strlen( $this->labelsLabel ) ) {
				$this->labelsLabel = SPLang::clean( $this->labelsLabel );
				//$fieldTitle .= "<label for=\"{$this->nid}\" class=\"{$this->cssClass}Title\">{$this->labelsLabel}</label>\n";
				$params[ 'placeholder' ] = $this->labelsLabel;
			}
			$fieldTitle .= SPHtml_Input::text( $this->nid, ( ( is_array( $raw ) && isset( $raw[ 'label' ] ) ) ? SPLang::clean( $raw[ 'label' ] ) : null ), $params );
		}
		$class = $this->required ? $this->cssClass . ' required' : $this->cssClass;
		if ( defined( 'SOBIPRO_ADM' ) ) {
			if ( $this->bsWidth ) {
				$width = SPHtml_Input::_translateWidth( $this->bsWidth );
				$class .= ' ' . $width;
			}
		}
		$this->nid .= '_url';
		$params = [ 'id' => $this->nid, 'size' => $this->width, 'class' => $class ];
		if ( $this->maxLength ) {
			$params[ 'maxlength' ] = $this->maxLength;
		}
		if ( $this->width ) {
			$params[ 'style' ] = "width: {$this->width}px;";
		}
		$label = Sobi::Txt( 'FD.MAIL_EMAIL_ADDRESS' );
		if ( ( !$this->ownLabel ) && ( $this->labelAsPlaceholder ) ) { // the field label will be shown only if labelAsPlaceholder is true and no own label for the email is selected
			$label = $this->__get( 'name' );  //get the field's label from the model
		}
		$params[ 'placeholder' ] = $label;
		$value                   = ( is_array( $raw ) && isset( $raw[ 'url' ] ) ) ? $raw[ 'url' ] : null;
		if ( $value == null ) {
			if ( $this->defaultValue ) {
				$value = $this->defaultValue;
			}
		}

		$field .= SPHtml_Input::text( $this->nid, $value, $params );

		if ( $this->ownLabel ) {
			$field = "\n<div class=\"spFieldEmailLabel\">{$fieldTitle}</div><div class=\"spFieldEmail\">{$field}</div>";
		}
		if ( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	/**
	 * @return array
	 */
	public function struct()
	{
		$data = SPConfig::unserialize( $this->getRaw() );
		if ( isset( $data[ 'url' ] ) && strlen( $data[ 'url' ] ) ) {
			$show = true;
			if ( !( isset( $data[ 'label' ] ) && strlen( $data[ 'label' ] ) ) ) {
				$data[ 'label' ] = ( $this->labelsLabel == '' ) ? $data[ 'url' ] : $this->labelsLabel;
			}
			/* @TODO: add second step */
			if ( $this->botProtection ) {
				SPLoader::loadClass( 'env.browser' );
				$humanity = SPBrowser::getInstance()
					->get( 'humanity' );
				$display  = Sobi::Cfg( 'mail_protection.show' );
				$show     = ( $humanity >= $display ) ? true : false;
			}
			if ( $show && strlen( $data[ 'url' ] ) ) {
				$this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData';
				$this->cssClass = $this->cssClass . ' ' . $this->nid;
				$this->cleanCss();
				$attributes = [ 'href' => "mailto:{$data['url']}", 'class' => $this->cssClass ];
				if ( $this->newWindow ) {
					$attributes[ 'target' ] = '_blank';
				}
				$data = [
					'_complex'    => 1,
					'_data'       => SPLang::clean( $data[ 'label' ] ),
//						'_data' => SPLang::clean( $this->ownLabel ? $data[ 'label' ] : $data[ 'url' ] ),
					'_attributes' => $attributes
				];

				return [
					'_complex'    => 1,
					'_validate'   => [ 'class' => str_replace( str_replace( '\\', '/', SOBI_PATH ), null, str_replace( '\\', '/', __FILE__ ) ), 'method' => 'validateVisibility' ],
					'_data'       => [ 'a' => $data ],
					'_attributes' => [ 'lang' => Sobi::Lang( false ), 'class' => $this->cssClass ]
				];
			}
		}
	}

	public function __construct( &$field )
	{
		parent::__construct( $field );
		$this->getLabelsLabel();
	}

		public static function validateVisibility( &$data )
	{
		SPLoader::loadClass( 'env.browser' );
		$humanity = SPBrowser::getInstance()
			->get( 'humanity' );
		$display  = Sobi::Cfg( 'mail_protection.show' );
		if ( !( $humanity >= $display ) ) {
			$data[ '_data' ] = [];
		}
	}

	public function cleanData( $html )
	{
		$data = SPConfig::unserialize( $this->getRaw() );

		return $data[ 'url' ];
	}

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 *
	 * @param SPEntry $entry
	 * @param string $tsId
	 * @param string $request
	 *
	 * @return array
	 */
	public function submit( &$entry, $tsId = null, $request = 'POST' )
	{
		if ( count( $this->verifyEmail( $entry, $request ) ) ) {
			return SPRequest::search( $this->nid, $request );
		}
		else {
			return [];
		}
	}

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		$attr   = parent::getAttr();
		$attr[] = 'botProtection';
		$attr[] = 'itemprop';

		return $attr;
	}

	private function fromCache( $cache )
	{
		$data = [];
		if ( isset( $cache[ $this->nid ] ) ) {
			$data[ 'label' ] = $cache[ $this->nid ];
		}
		if ( isset( $cache[ $this->nid . '_url' ] ) ) {
			$data[ 'url' ] = $cache[ $this->nid . '_url' ];
		}

		return $data;
	}

	/**
	 * Gets the data for a field and save it in the database
	 *
	 * @param SPEntry $entry
	 * @param string $request
	 *
	 * @return bool
	 */
	public function saveData( &$entry, $request = 'POST' )
	{
		if ( !( $this->enabled ) ) {
			return false;
		}
		/* @var SPdb $db */
		$db   = SPFactory::db();
		$save = $this->verifyEmail( $entry, $request );
		$time = SPRequest::now();
		$IP   = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$uid  = Sobi::My( 'id' );

		/* collect the needed params */
		$params                  = [];
		$params[ 'publishUp' ]   = $entry->get( 'publishUp' );
		$params[ 'publishDown' ] = $entry->get( 'publishDown' );
		$params[ 'fid' ]         = $this->fid;
		$params[ 'sid' ]         = $entry->get( 'id' );
		$params[ 'section' ]     = Sobi::Reg( 'current_section' );
		$params[ 'lang' ]        = Sobi::Lang();
		$params[ 'enabled' ]     = $entry->get( 'state' );
		$params[ 'baseData' ]    = $db->escape( SPConfig::serialize( $save ) );
		$params[ 'approved' ]    = $entry->get( 'approved' );
		$params[ 'confirmed' ]   = $entry->get( 'confirmed' );
		/* if it is the first version, it is new entry */
		if ( $entry->get( 'version' ) == 1 ) {
			$params[ 'createdTime' ] = $time;
			$params[ 'createdBy' ]   = $uid;
			$params[ 'createdIP' ]   = $IP;
		}
		$params[ 'updatedTime' ] = $time;
		$params[ 'updatedBy' ]   = $uid;
		$params[ 'updatedIP' ]   = $IP;
		$params[ 'copy' ]        = !( $entry->get( 'approved' ) );
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
		}
		catch ( SPException $x ) {
			Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}

		/* if it wasn't edited in the default language, we have to try to insert it also for def lang */
		if ( Sobi::Lang() != Sobi::DefLang() ) {
			$params[ 'lang' ] = Sobi::DefLang();
			try {
				$db->insert( 'spdb_field_data', $params, true, true );
			}
			catch ( SPException $x ) {
				Sobi::Error( __CLASS__, SPLang::e( 'CANNOT_SAVE_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 *
	 * @throws SPException
	 * @return array
	 */
	protected function verifyEmail( $entry, $request )
	{
		$save = [];
		$data = SPRequest::raw( $this->nid . '_url', null, $request );
		$dexs = strlen( $data );
		$data = SPFactory::db()->escape( $data );

		if ( $this->ownLabel ) {
			$save[ 'label' ] = SPRequest::raw( $this->nid, null, $request );
			/* check if there was a filter */
			if ( $this->filter && strlen( $save[ 'label' ] ) ) {
				$registry =& SPFactory::registry();
				$registry->loadDBSection( 'fields_filter' );
				$filters = $registry->get( 'fields_filter' );
				$filter  = isset( $filters[ $this->filter ] ) ? $filters[ $this->filter ] : null;
				if ( !( count( $filter ) ) ) {
					throw new SPException( SPLang::e( 'FIELD_FILTER_ERR', $this->filter ) );
				}
				else {
					if ( !( preg_match( base64_decode( $filter[ 'params' ] ), $save[ 'label' ] ) ) ) {
						throw new SPException( str_replace( '$field', $this->name, SPLang::e( $filter[ 'description' ] ) ) );
					}
				}
			}
		}

		/* check if it was required */
		if ( $this->required && !( $dexs ) ) {
			throw new SPException( SPLang::e( 'FIELD_REQUIRED_ERR', $this->name ) );
		}

		/* check if there was an adminField */
		if ( $this->adminField && $dexs ) {
			if ( !( Sobi:: Can( 'adm_fields.edit' ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_AUTH', $this->name ) );
			}
		}

		/* check if it was free */
		if ( !( $this->isFree ) && $this->fee && $dexs ) {
			SPFactory::payment()->add( $this->fee, $this->name, $entry->get( 'id' ), $this->fid );
		}

		/* check if it should contains unique data */
		if ( $this->uniqueData && $dexs ) {
			$matches = $this->searchData( $data, Sobi::Reg( 'current_section' ) );
			if ( count( $matches ) ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_UNIQUE', $this->name ) );
			}
		}

		/* check if it was editLimit */
		if ( $this->editLimit == 0 && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_EXP', $this->name ) );
		}

		/* check if it was editable */
		if ( !( $this->editable ) && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs && $entry->get( 'version' ) > 1 ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_NOT_ED', $this->name ) );
		}

		/* check the response code */
		if ( $dexs && $this->validateUrl ) {
			if ( preg_match( '/[a-z0-9]@[a-z0-9].[a-z]/i', $data ) ) {
				$domain = explode( '@', $data, 2 );
				$domain = $domain[ 1 ];
				if ( !( checkdnsrr( $domain, 'MX' ) ) ) {
					throw new SPException( SPLang::e( 'FIELD_MAIL_NO_MX', $data ) );
				}
			}
			else {
				throw new SPException( SPLang::e( 'FIELD_MAIL_WRONG_FORM', $data ) );
			}
		}
		if ( $dexs ) {
			/* if we are here, we can save these data */
			$save[ 'url' ] = $data;
		}
		else {
			$save = null;
		}
		$this->setData( $save );

		return $save;
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 *
	 * @return string
	 */
	public function validate( $entry, $request )
	{
		return $this->verifyEmail( $entry, $request );
	}

	protected function getLabelsLabel()
	{
		$data = SPLang::getValue( $this->nid . '-labels-label', 'field_email', Sobi::Section(), null, null, $this->fid );
		if ( $data ) {
			$this->labelsLabel = $data;
		}
	}

	/**
	 * @param $attr
	 */
	protected function saveLabelsLabel( &$attr )
	{
		$data = [
			'key' => $this->nid . '-labels-label',
			'value' => $attr[ 'labelsLabel' ],
			'type' => 'field_email',
			'fid' => $this->fid,
			'id' => Sobi::Section(),
			'section' => Sobi::Section()
		];
		SPLang::saveValues( $data );
	}
}
