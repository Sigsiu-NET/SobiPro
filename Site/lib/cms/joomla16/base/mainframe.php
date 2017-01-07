<?php
/**
 * @version: $Id$
 * @package: SobiPro Library
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
require_once dirname( __FILE__ ) . '/../../joomla_common/base/mainframe.php';

/**
 * Interface between SobiPro and the used CMS
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:50:43 PM
 */
class SPJ16MainFrame extends SPJoomlaMainFrame implements SPMainframeInterface
{
	/**
	 * Gets basic data from the CMS (e.g Joomla) and stores in the #SPConfig instance
	 */
	public function getBasicCfg()
	{
		parent::getBasicCfg();
		if ( defined( 'SOBIPRO_ADM' ) ) {
			SPFactory::config()->change( 'adm_img_folder_live', Sobi::FixPath( JURI::root() . '/' . SOBI_ADM_FOLDER . '/templates/' . JFactory::getApplication()->getTemplate() . '/images/admin' ), 'general' );
		}
	}

	/**
	 * @param string $title
	 * @param bool $forceAdd
	 * @return void
	 */
	public function setTitle( $title, $forceAdd = false )
	{
		if ( defined( 'SOBIPRO_ADM' ) ) {
			if ( is_array( $title ) ) {
				$title = implode( ' ', $title );
			}
			static $section = null;
			if ( !( $section ) ) {
				$section = Sobi::Section( true );
			}
			if ( strlen( $section ) && !( strstr( $title, $section ) ) ) {
				if ( strlen( $title ) ) {
					$title = "SobiPro [{$section}] - {$title}";
				}
				else {
					$title = "SobiPro [{$section}]";
				}
			}
			else {
				$title = 'SobiPro - ' . $title;
			}
			JToolbarHelper::title( $title, 'sobipro' );
		}
		else {
			parent::setTitle( $title, $forceAdd );
		}
	}

	/**
	 * @param array $head
	 * @return bool
	 */
	public function addHead( $head )
	{
		if ( strlen( SPRequest::cmd( 'format' ) ) && SPRequest::cmd( 'format' ) != 'html' ) {
			return true;
		}
		/** @var JDocument $document */
		$document = JFactory::getDocument();
		$c = 0;
		if ( count( $head ) ) {
			$document->addCustomTag( "\n\t<!--  SobiPro Head Tags Output  -->\n" );
			$document->addCustomTag( "\n\t<script type=\"text/javascript\">/*\n<![CDATA[*/ \n\tvar SobiProUrl = '" . Sobi::FixPath( self::Url( [ 'task' => '%task%' ], true, false, true ) ) . "'; \n\tvar SobiProSection = " . ( Sobi::Section() ? Sobi::Section() : 0 ) . "; \n\tvar SPLiveSite = '" . Sobi::Cfg( 'live_site' ) . "'; \n/*]]>*/\n</script>\n" );
			if ( defined( 'SOBI_ADM_PATH' ) ) {
				$document->addCustomTag( "\n\t<script type=\"text/javascript\">/* <![CDATA[ */ \n\tvar SobiProAdmUrl = '" . Sobi::FixPath( Sobi::Cfg( 'live_site' ) . SOBI_ADM_FOLDER . '/' . self::Url( [ 'task' => '%task%' ], true, false ) ) . "'; \n/* ]]> */</script>\n" );
			}
			foreach ( $head as $type => $code ) {
				switch ( $type ) {
					default: {
					if ( count( $code ) ) {
						foreach ( $code as $html ) {
							++$c;
							$document->addCustomTag( $html );
						}
					}
					break;
					}
					case 'robots' :
					case 'author': {
						$document->setMetaData( $type, implode( ', ', $code ) );
//						$document->setHeadData( array( $type => implode( ', ', $code ) ) );
						break;
					}
					case 'keywords': {
						$metaKeys = trim( implode( ', ', $code ) );
						if ( Sobi::Cfg( 'meta.keys_append', true ) ) {
							$metaKeys .= Sobi::Cfg( 'string.meta_keys_separator', ',' ) . $document->getMetaData( 'keywords' );
						}
						$metaKeys = explode( Sobi::Cfg( 'string.meta_keys_separator', ',' ), $metaKeys );
						if ( count( $metaKeys ) ) {
							$metaKeys = array_unique( $metaKeys );
							foreach ( $metaKeys as $i => $p ) {
								if ( strlen( trim( $p ) ) ) {
									$metaKeys[ $i ] = trim( $p );
								}
								else {
									unset( $metaKeys[ $i ] );
								}
							}
							$metaKeys = implode( ', ', $metaKeys );
						}
						else {
							$metaKeys = null;
						}
						$document->setMetadata( 'keywords', $metaKeys );
						break;
					}
					case 'description': {
						$metaDesc = implode( Sobi::Cfg( 'string.meta_desc_separator', ' ' ), $code );
						if ( strlen( $metaDesc ) ) {
							if ( Sobi::Cfg( 'meta.desc_append', true ) ) {
								$metaDesc .= $this->getMetaDescription( $document );
							}
							$metaDesc = explode( ' ', $metaDesc );
							if ( count( $metaDesc ) ) {
								foreach ( $metaDesc as $i => $p ) {
									if ( strlen( trim( $p ) ) ) {
										$metaDesc[ $i ] = trim( $p );
									}
									else {
										unset( $metaDesc[ $i ] );
									}
								}
								$metaDesc = implode( ' ', $metaDesc );
							}
							else {
								$metaDesc = null;
							}
							$document->setDescription( $metaDesc );
						}
						break;
					}
				}
			}
			$jsUrl = Sobi::FixPath( self::Url( [ 'task' => 'txt.js', 'format' => 'json' ], true, false, false ) );
			$document->addCustomTag( "\n\t<script type=\"text/javascript\" src=\"" . str_replace( '&', '&amp;', $jsUrl ) . "\"></script>\n" );
			$c++;
			$document->addCustomTag( "\n\t<!--  SobiPro ({$c}) Head Tags Output -->\n" );
			// we would like to set our own canonical please :P
			// https://groups.google.com/forum/?fromgroups=#!topic/joomla-dev-cms/sF3-JBQspQU
			if ( count( $document->_links ) ) {
				foreach ( $document->_links as $index => $link ) {
					if ( $link[ 'relation' ] == 'canonical' ) {
						unset( $document->_links[ $index ] );
					}
				}
			}
		}
	}

	protected function getMetaDescription( $document )
	{
		return $document->get( 'description' );
	}

	protected function JConfigValue( $value )
	{
		$value = str_replace( 'config.', null, $value );
		return JFactory::getConfig()->get( $value );
	}

	/**
	 * @return SPJoomlaMainFrame
	 */
	public static function & getInstance()
	{
		static $mf = false;
		if ( !( $mf ) || !( $mf instanceof self ) ) {
			$mf = new self();
		}
		return $mf;
	}

	/**
	 * Method to determine a hash for anti-spoofing variable names
	 * @return string
	 */
	public function token()
	{
		return JSession::getFormToken();
	}

	/**
	 * Checks for a form token in the request.
	 * @param string $method
	 * @return boolean
	 */
	public function checkToken( $method = 'post' )
	{
		if ( Sobi::Cfg( 'security.token', true ) ) {
			return JSession::checkToken( $method );
		}
		else {
			return true;
		}
	}

	public function setCookie( $name, $value, $expire = 0, $httponly = false, $secure = false, $path = '/', $domain = null )
	{
		return JFactory::getApplication()->input->cookie->set( $name, $value, $expire, $path, $domain, $secure, $httponly );
	}
}
