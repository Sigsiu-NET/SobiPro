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
SPLoader::loadClass( 'opt.fields.inbox' );

/**
 * @author Radek Suski
 * @version 1.1
 * @created Tue, Feb 12, 2013 10:43:18
 */
class SPField_Url extends SPField_Inbox implements SPFieldInterface
{
	/** @var bool */
	protected $ownLabel = true;
	/** @var int */
	protected $labelWidth = 350;
	/**  @var string */
	protected $labelsLabel = "Visit our Site";
	/** @var int */
	protected $labelMaxLength = 150;
	/** @var int */
	protected $maxLength = 150;
	/** @var int */
	protected $width = 350;
	/** @var int */
	protected $bsWidth = 4;
	/** @var string */
	protected $cssClass = 'spClassUrl';
	/** * @var string */
	protected $cssClassView = 'spClassViewUrl';
	/** * @var string */
	protected $cssClassEdit = 'spClassEditUrl';
	/** @var bool */
	protected $validateUrl = false;
	/** @var array */
	protected $allowedProtocols = [ 'http', 'https', 'ftp' ];
	/** @var bool */
	protected $newWindow = false;
	/** @var bool */
	protected $noFollow = false;
	/** @var string */
	protected $dType = 'special';
	/** @var bool */
	protected $countClicks = false;
	/** @var bool */
	protected $deleteClicks = true;
	/** @var bool */
	protected $counterToLabel = false;
	/** @var bool */
	protected $labelAsPlaceholder = false;
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
			$raw = $this->getRaw();
			if ( !( is_array( $raw ) ) ) {
				try {
					$raw = SPConfig::unserialize( $raw );
				}
				catch ( SPException $x ) {
					$raw = null;
				}
			}
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
			$params = [ 'id' => $this->nid, 'class' => $class ];
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
			$value = ( ( is_array( $raw ) && isset( $raw[ 'label' ] ) ) ? SPLang::clean( $raw[ 'label' ] ) : null );
			$fieldTitle .= SPHtml_Input::text( $this->nid, $value, $params );
		}
		$protocols = [];
		if ( count( $this->allowedProtocols ) ) {
			foreach ( $this->allowedProtocols as $protocol ) {
				$protocols[ $protocol ] = $protocol . '://';
			}
		}
		else {
			$protocols = [ 'http' => 'http://', 'https' => 'https://' ];
		}
		$params = [ 'id' => $this->nid . '_protocol', 'size' => 1, 'class' => $this->cssClass . 'Protocol' ];

		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) ) {
			$protofield = '<div class="input-group"><div class="input-group-btn">';
		}
		else {
			$protofield = '<div class="input-prepend"><div class="btn-group">';
		}
		$fliped_protocols = array_flip( $protocols );
		$fliped_protocols = array_values( $fliped_protocols );
		$selected = ( is_array( $raw ) && isset( $raw[ 'protocol' ] ) ) ? $raw[ 'protocol' ] : $fliped_protocols[ 0 ];
		$protofield .= SPHtml_Input::select( $this->nid . '_protocol', $protocols, $selected, false, $params );
		$protofield .= '</div>';

		//$field .= '<span class="spFieldUrlProtocol">://</span>';
		$class = $this->required ? $this->cssClass . ' required' : $this->cssClass;
		if ( defined( 'SOBIPRO_ADM' ) ) {
			if ( $this->bsWidth ) {
				$width = SPHtml_Input::_translateWidth( $this->bsWidth );
				$class .= ' ' . $width;
			}
		}

		$params = [ 'id' => $this->nid . '_url', 'class' => $class ];
		if ( $this->maxLength ) {
			$params[ 'maxlength' ] = $this->maxLength;
		}

		//for compatibility reason still there
		if ( $this->width ) {
			$params[ 'style' ] = "width: {$this->width}px;";
		}

		$label = Sobi::Txt( 'FD.URL_ADDRESS' );
		if ( ( !$this->ownLabel ) && ( $this->labelAsPlaceholder ) ) { // the field label will be shown only if labelAsPlaceholder is true and no own label for the URL is selected
			$label = $this->__get( 'name' );
		}
		$params[ 'placeholder' ] = $label;
		$value = ( is_array( $raw ) && isset( $raw[ 'url' ] ) ) ? $raw[ 'url' ] : null;
		if ( $value == null ) {
			if ( $this->defaultValue ) {
				$value = $this->defaultValue;
			}
		}

		$field .= $protofield;
		$field .= SPHtml_Input::text( $this->nid . '_url', $value, $params );
		$field .= '</div>';

		if ( $this->ownLabel ) {
			$field = "\n<div class=\"spFieldUrlLabel\">{$fieldTitle}</div>\n<div class=\"spFieldUrl\">{$field}</div>";
		}
		else {
			$field = "\n<div class=\"spFieldUrl\">{$field}</div>";
		}

		if ( $this->countClicks && $this->sid && ( $this->deleteClicks || SPFactory::user()->isAdmin() ) ) {
			$counter = $this->getCounter();
			if ( $counter ) {
				SPFactory::header()->addJsFile( 'opt.field_url_edit' );
			}
			$classes = 'btn btn-default spCountableReset';
			$attr = [];
			if ( !( $counter ) ) {
				$attr[ 'disabled' ] = 'disabled';
			}
			$field .= SPHtml_Input::button( $this->nid . '_reset', Sobi::Txt( 'FM.URL.EDIT_CLICKS', $counter ), null, $classes );
		}
		if ( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	public function compareRevisions( $revision, $current )
	{
		return [ 'current' => $this->paresUrl( $current ), 'revision' => $this->paresUrl( $revision ) ];
	}

	protected function paresUrl( $url )
	{
		if ( $url[ 'protocol' ] == 'relative' ) {
			$dUrl = $url[ 'url' ];
		}
		else {
			$dUrl = $url[ 'protocol' ] . '://' . $url[ 'url' ];
		}
		if ( isset( $url[ 'label' ] ) ) {
			return "{$url['label']}\n{$dUrl}";
		}
		else {
			return $dUrl;
		}
	}


	private
	function fromCache( $cache )
	{
		$data = [];
		if ( isset( $cache ) && isset( $cache[ $this->nid ] ) ) {
			$data[ 'label' ] = $cache[ $this->nid ];
		}
		if ( isset( $cache ) && isset( $cache[ $this->nid . '_url' ] ) ) {
			$data[ 'url' ] = $cache[ $this->nid . '_url' ];
		}
		if ( isset( $cache ) && isset( $cache[ $this->nid . '_protocol' ] ) ) {
			$data[ 'protocol' ] = $cache[ $this->nid . '_protocol' ];
		}

		return $data;
	}


	/**
	 * @param $real
	 * @param $sid
	 * @param $nid
	 *
	 * @return string
	 */
	protected
	static function getHits( $real, $sid, $nid )
	{
		$query = [ 'sid' => $sid, 'fid' => $nid, 'section' => Sobi::Section() ];
		if ( $real ) {
			$query[ 'humanity>' ] = Sobi::Cfg( 'field_url.humanity', 90 );
		}
		$counter = SPFactory::db()
			->select( 'count(*)', 'spdb_field_url_clicks', $query )
			->loadResult();

		return $counter;
	}

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		return [ 'ownLabel', 'labelWidth', 'labelMaxLength', 'labelsLabel', 'validateUrl', 'allowedProtocols', 'newWindow', 'maxLength', 'width', 'countClicks', 'counterToLabel', 'itemprop', 'cssClassView', 'cssClassEdit', 'noFollow', 'showEditLabel', 'labelAsPlaceholder', 'defaultValue', 'bsWidth', 'deleteClicks' ];
	}

	/**
	 * @return array
	 */
	public function struct()
	{
		$data = SPConfig::unserialize( $this->getRaw() );
		if ( isset( $data[ 'url' ] ) && strlen( $data[ 'url' ] ) ) {
			$counter = -1;
			if ( $data[ 'protocol' ] == 'relative' ) {
				$url = $data[ 'url' ];
			}
			else {
				$url = $data[ 'protocol' ] . '://' . $data[ 'url' ];
			}
			if ( !( isset( $data[ 'label' ] ) && strlen( $data[ 'label' ] ) ) ) {
				$data[ 'label' ] = ( $this->labelsLabel == '' ) ? $url : $this->labelsLabel;
			}
			$this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData';
			$this->cssClass = $this->cssClass . ' ' . $this->nid;
			$attributes = [ 'href' => $url, 'class' => $this->cssClass ];
			if ( $this->countClicks ) {
				SPFactory::header()->addJsFile( 'opt.field_url' );
				$this->cssClass = $this->cssClass . ' ctrl-visit-countable';
				$counter = $this->getCounter();
				$attributes[ 'data-sid' ] = $this->sid;
				if ( Sobi::Cfg( 'cache.xml_enabled' ) ) {
					$attributes[ 'data-counter' ] = $counter;
					$attributes[ 'data-refresh' ] = 'true';
				}
				$attributes[ 'class' ] = $this->cssClass;
				if ( $this->counterToLabel ) {
					$data[ 'label' ] = Sobi::Txt( 'FM.URL.COUNTER_WITH_LABEL', [ 'label' => $data[ 'label' ], 'counter' => $counter ] );
				}
			}
			$this->cleanCss();
			if ( strlen( $url ) ) {
				if ( $this->newWindow ) {
					$attributes[ 'target' ] = '_blank';
					$attributes[ 'rel' ] = 'noopener noreferrer';
				}
				if ( $this->noFollow ) {
					if ( $this->newWindow ) {
						$attributes[ 'rel' ] = 'nofollow noopener noreferrer';
					}
					else {
						$attributes[ 'rel' ] = 'nofollow';
					}
				}
				$data = [
					'_complex'    => 1,
					'_data'       => SPLang::clean( $data[ 'label' ] ),
//						'_data' => SPLang::clean( $this->ownLabel ? $data[ 'label' ] : $data[ 'url' ] ),
					'_attributes' => $attributes
				];

				return [
					'_complex'    => 1,
					'_data'       => [ 'a' => $data ],
					'_attributes' => [ 'lang' => Sobi::Lang( false ), 'class' => $this->cssClass, 'counter' => $counter ]
				];
			}
		}
	}

	public function __construct( &$field )
	{
		parent::__construct( $field );
		$this->getLabelsLabel();
	}


	protected function getCounter( $real = true )
	{
		$counter = self::getHits( $real, $this->sid, $this->nid );

		return $counter;
	}

	public function cleanData( $html )
	{
		$data = SPConfig::unserialize( $this->getRaw() );
		$url = null;
		if ( isset( $data[ 'url' ] ) && strlen( $data[ 'url' ] ) ) {
			if ( $data[ 'protocol' ] == 'relative' ) {
				$url = Sobi::Cfg( 'live_site' ) . $data[ 'url' ];
			}
			else {
				$url = $data[ 'protocol' ] . '://' . $data[ 'url' ];
			}
		}

		return $url;
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
		if ( count( $this->verify( $entry, SPFactory::db(), $request ) ) ) {
			return SPRequest::search( $this->nid, $request );
		}
		else {
			return [];
		}
	}


	/**
	 * @param SPEntry $entry
	 * @param string $request
	 *
	 * @return string
	 */
	public function validate( $entry, $request )
	{
		return $this->verify( $entry, SPFactory::db(), $request );
	}

	/**
	 * @param SPEntry $entry
	 * @param SPdb $db
	 * @param string $request
	 *
	 * @throws SPException
	 * @return array
	 */
	private function verify( $entry, &$db, $request )
	{
		$save = [];
		if ( $this->ownLabel ) {
			$save[ 'label' ] = SPRequest::raw( $this->nid, null, $request );
			/* check if there was a filter */
			if ( $this->filter && strlen( $save[ 'label' ] ) ) {
				$registry =& SPFactory::registry();
				$registry->loadDBSection( 'fields_filter' );
				$filters = $registry->get( 'fields_filter' );
				$filter = isset( $filters[ $this->filter ] ) ? $filters[ $this->filter ] : null;
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
		$data = SPRequest::raw( $this->nid . '_url', null, $request );
		$save[ 'protocol' ] = $db->escape( SPRequest::word( $this->nid . '_protocol', null, $request ) );
		$dexs = strlen( $data );
		$data = $db->escape( $data );
		$data = preg_replace( '/([a-z]{1,5}\:\/\/)/i', null, $data );
		$save[ 'url' ] = $data;
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
			$rclass = SPLoader::loadClass( 'services.remote' );
			$err = 0;
			$response = 0;
			try {
				$connection = new $rclass();
				$connection->setOptions(
					[
						'url'            => $save[ 'protocol' ] . '://' . $data,
						'connecttimeout' => 10,
						'header'         => false,
						'returntransfer' => true
					]
				);
				$connection->exec();
				$response = $connection->info( 'response_code' );
				$err = $connection->error( false );
				$errTxt = $connection->error();
				$connection->close();
				if ( $err ) {
					Sobi::Error( $this->name(), SPLang::e( 'FIELD_URL_CANNOT_VALIDATE', $errTxt ), SPC::WARNING, 0, __LINE__, __FILE__ );
				}
			}
			catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'FIELD_URL_CANNOT_VALIDATE', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			if ( $err || ( $response != 200 ) ) {
				$response = $err ? $errTxt : $response;
				Sobi::Error( $this->name(), SPLang::e( 'FIELD_URL_ERR', $save[ 'protocol' ] . '://' . $data, $response ), SPC::WARNING, 0, __LINE__, __FILE__ );
				throw new SPException( SPLang::e( 'FIELD_URL_ERR', $save[ 'protocol' ] . '://' . $data, $response ) );
			}
		}
		if ( !( $dexs ) ) {
			$save = null;
		}

		return $save;
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
		$db =& SPFactory::db();
		$save = $this->verify( $entry, $db, $request );
		$this->setRawData( $save );

		$time = SPRequest::now();
		$IP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$uid = Sobi::My( 'id' );

		/* if we are here, we can save these data */
		/* collect the needed params */
		$params = [];
		$params[ 'publishUp' ] = $entry->get( 'publishUp' );
		$params[ 'publishDown' ] = $entry->get( 'publishDown' );
		$params[ 'fid' ] = $this->fid;
		$params[ 'sid' ] = $entry->get( 'id' );
		$params[ 'section' ] = Sobi::Reg( 'current_section' );
		$params[ 'lang' ] = Sobi::Lang();
		$params[ 'enabled' ] = $entry->get( 'state' );
		$params[ 'baseData' ] = $db->escape( SPConfig::serialize( $save ) );
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

	public function ProxyReset()
	{
		$eid = SPRequest::int( 'eid' );
		// let's allow it for admins only right now
		// later we can extend it a bit
//		$entry = SPFactory::Entry( $eid );
		if ( Sobi::Can( 'entry.manage.any' ) ) {
			SPFactory::db()
				->delete( 'spdb_field_url_clicks', [ 'section' => Sobi::Section(), 'sid' => $eid, 'fid' => $this->nid ] );
		}
		echo 1;
	}

	public function ProxyHits()
	{
		SPFactory::mainframe()->cleanBuffer()->customHeader();
		$r = ( int ) self::getHits( true, SPRequest::int( 'eid' ), $this->nid );
		echo $r;
		exit;
	}

	public function ProxyCount()
	{
		SPLoader::loadClass( 'env.browser' );
		SPLoader::loadClass( 'env.cookie' );
		$browser = SPBrowser::getInstance();
		$this->nid = str_replace( [ '.count', '.' ], [ null, '_' ], SPRequest::task() );
		$ident = $this->nid . '_' . SPRequest::int( 'eid' );
		$check = SPRequest::cmd( 'count_' . $ident, null, 'cookie' );
		if ( !( $check ) ) {
			$data = [
				'date'        => 'FUNCTION:NOW()',
				'uid'         => Sobi::My( 'id' ),
				'sid'         => SPRequest::int( 'eid' ),
				'fid'         => $this->nid,
				'ip'          => SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' ),
				'section'     => Sobi::Section(),
				'browserData' => $browser->get( 'browser' ),
				'osData'      => $browser->get( 'system' ),
				'humanity'    => $browser->get( 'humanity' )
			];
			SPCookie::set( 'count_' . $ident, 1, SPCookie::hours( 2 ) );
			SPFactory::db()->insert( 'spdb_field_url_clicks', $data );
		}
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
			'key'     => $this->nid . '-labels-label',
			'value'   => $attr[ 'labelsLabel' ],
			'type'    => 'field_email',
			'fid'     => $this->fid,
			'id'      => Sobi::Section(),
			'section' => Sobi::Section()
		];
		SPLang::saveValues( $data );
	}
}
