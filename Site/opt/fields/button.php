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
	protected $allowedProtocols = array( 'http', 'https', 'relative' );
	/** @var string */
	protected $dType = 'special';
	/** @var bool */
	static private $CAT_FIELD = true;

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		return array( 'ownLabel', 'labelWidth', 'labelMaxLength', 'labelsLabel', 'validateUrl', 'allowedProtocols', 'newWindow', 'maxLength', 'width', 'countClicks', 'counterToLabel', 'itemprop', 'cssClassView', 'cssClassEdit', 'noFollow', 'showEditLabel', 'labelAsPlaceholder', 'defaultValue', 'bsWidth', 'deleteClicks', 'useIcon', 'cssIconClass', 'cssButtonClass' );
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

			$attributes = array( 'href' => $url, 'class' => $this->cssClass );
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
					$data[ 'label' ] = Sobi::Txt( 'FM.URL.COUNTER_WITH_LABEL2', array( 'label' => $data[ 'label' ], 'counter' => $counter ) );
				}
			}
			$this->cleanCss();
			if ( strlen( $url ) ) {
				if ( $this->newWindow ) {
					$attributes[ 'target' ] = '_blank';
				}
				if ( $this->noFollow ) {
					$attributes[ 'rel' ] = 'nofollow';
				}
				if ( $this->useIcon ) {
					$f[ 'i' ] = array(
						'_complex'    => 1,
						'_data'       => ' ',
						'_attributes' => array( 'class' => $this->cssIconClass )
					);
				}
				$f[ 'span' ] = SPLang::clean( $data[ 'label' ] );
				$data        = array(
					'_complex'    => 1,
					'_data'       => $f,
					'_attributes' => $attributes
				);

				return array(
					'_complex'    => 1,
					'_data'       => array( 'a' => $data ),
					'_attributes' => array( 'lang' => Sobi::Lang( false ), 'class' => $this->cssClass, 'counter' => $counter )
				);
			}
		}
	}
}