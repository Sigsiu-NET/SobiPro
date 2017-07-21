<?php
/**
 * @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 *  This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 *  as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 *  See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.url' );

/**
 * @author Radek Suski
 * @version 1.1
 * @created Tue, Feb 12, 2013 10:43:18
 */
class SPField_Button extends SPField_Url implements SPFieldInterface
{
	/** @var bool */
	protected $ownLabel = true;
	/** @var int */
	protected $labelWidth = 350;
	/**  @var string */
	protected $labelsLabel = "Download";
	/** @var int */
	protected $labelMaxLength = 150;
	/** @var int */
	protected $maxLength = 150;
	/** @var int */
	protected $width = 350;
	/** @var int */
	protected $bsWidth = 8;
	/** @var string */
	protected $cssClass = 'spClassButton';
	/** * @var string */
	protected $cssClassView = 'spClassViewButton';
	/** * @var string */
	protected $cssClassEdit = 'spClassEditButton';
	/** * @var string */
	protected $cssButtonClass = 'btn btn-default';
	/** * @var string */
	protected $cssIconClass = 'icon icon-download';
	/** @var bool */
	protected $useIcon = true;
	/** @var array */
	protected $allowedProtocols = [ 'http', 'https', 'relative' ];
	/** @var string */
	protected $dType = 'special';
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
			$class      = $this->cssClass . 'Title';
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
			$fieldTitle .= SPHtml_Input::text( $this->nid, ( ( is_array( $raw ) && isset( $raw[ 'label' ] ) ) ? SPLang::clean( $raw[ 'label' ] ) : null ), $params );
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

		$fliped_protocols = array_flip($protocols);
		$protofield .= SPHtml_Input::select( $this->nid . '_protocol', $protocols, ( ( is_array( $raw ) && isset( $raw[ 'protocol' ] ) ) ? $raw[ 'protocol' ] : $fliped_protocols[0] ), false, $params );
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
		$value                   = ( is_array( $raw ) && isset( $raw[ 'url' ] ) ) ? $raw[ 'url' ] : null;
		if ( $value == null ) {
			if ( $this->defaultValue ) {
				$value = $this->defaultValue;
			}
		}

		$field .= $protofield;
		$field .= SPHtml_Input::text( $this->nid . '_url', $value, $params );
		$field .= '</div>';

		if ( $this->ownLabel ) {
			$field = "\n<div class=\"spFieldButtonLabel\">{$fieldTitle}</div>\n<div class=\"spFieldButton\">{$field}</div>";
		}
		else {
			$field = "\n<div class=\"spFieldButton\">{$field}</div>";
		}

		if ( $this->countClicks && $this->sid && ( $this->deleteClicks || SPFactory::user()->isAdmin() ) ) {
			$counter = $this->getCounter();
			if ( $counter ) {
				SPFactory::header()->addJsFile( 'opt.field_url_edit' );
			}
			$classes = 'btn btn-default spCountableReset';
			$attr    = [];
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

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		return [ 'ownLabel', 'labelWidth', 'labelMaxLength', 'labelsLabel', 'validateUrl', 'allowedProtocols', 'newWindow', 'maxLength', 'width', 'countClicks', 'counterToLabel', 'itemprop', 'cssClassView', 'cssClassEdit', 'noFollow', 'showEditLabel', 'labelAsPlaceholder', 'defaultValue', 'bsWidth', 'deleteClicks', 'useIcon', 'cssIconClass', 'cssButtonClass' ];
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
				$data[ 'label' ] = ( $this->labelsLabel == '' && !$this->useIcon ) ? $url : $this->labelsLabel;
			}
			$this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData';
			$this->cssClass = $this->cssClass . ' ' . $this->cssButtonClass;
			$this->cssClass = $this->cssClass . ' ' . $this->nid;

			$attributes = [ 'href' => $url, 'class' => $this->cssClass ];
			if ( $this->countClicks ) {
				SPFactory::header()->addJsFile( 'opt.field_url' );
				$this->cssClass           = $this->cssClass . ' ctrl-visit-countable';
				$counter                  = $this->getCounter();
				$attributes[ 'data-sid' ] = $this->sid;
				if ( Sobi::Cfg( 'cache.xml_enabled' ) ) {
					$attributes[ 'data-counter' ] = $counter;
					$attributes[ 'data-refresh' ] = 'true';
				}
				$attributes[ 'class' ] = $this->cssClass;
				if ( $this->counterToLabel ) {
					$data[ 'label' ] = Sobi::Txt( 'FM.URL.COUNTER_WITH_LABEL2', [ 'label' => $data[ 'label' ], 'counter' => $counter ] );
				}
			}
			$this->cleanCss();
			if ( strlen( $url ) ) {
				if ( $this->newWindow ) {
					$attributes[ 'target' ] = '_blank';
					$attributes['rel'] = 'noopener noreferrer';
				}
				if ( $this->noFollow ) {
					if ( $this->newWindow ) {
						$attributes[ 'rel' ] = 'nofollow noopener noreferrer';
					}
					else {
						$attributes[ 'rel' ] = 'nofollow';
					}
				}
				if ( $this->useIcon ) {
					$f[ 'i' ] = [
						'_complex'    => 1,
						'_data'       => ' ',
						'_attributes' => [ 'class' => $this->cssIconClass ]
					];
				}
				$f[ 'span' ] = SPLang::clean( $data[ 'label' ] );
				$data        = [
					'_complex'    => 1,
					'_data'       => $f,
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
}
