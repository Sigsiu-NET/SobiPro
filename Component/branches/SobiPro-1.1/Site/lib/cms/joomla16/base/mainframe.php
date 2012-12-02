<?php
/**
 * @version: $Id: mainframe.php 1508 2011-06-21 19:48:12Z Radek Suski $
 * @package: SobiPro J!Bridge
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2011-06-21 21:48:12 +0200 (Tue, 21 Jun 2011) $
 * $Revision: 1508 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla16/base/mainframe.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
require_once dirname( __FILE__ ) . '/../../joomla_common/base/mainframe.php';
/**
 * Interface between SobiPro and the used CMS
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:50:43 PM
 */
final class SPMainFrame extends SPJoomlaMainFrame implements SPMainfrmaInterface
{
	/**
	 * Gets basic data from the CMS (e.g Joomla) and stores in the #SPConfig instance
	 */
	public function getBasicCfg()
	{
		parent::getBasicCfg();
		$cfg =& SPFactory::config();
		if ( defined( 'SOBIPRO_ADM' ) ) {
			$cfg->change( 'adm_img_folder_live',
				Sobi::FixPath(
					JURI::root() . DS . SOBI_ADM_FOLDER . DS . 'templates' . DS . JFactory::getApplication()->getTemplate() . '/images/admin'
				), 'general'
			);
		}
	}


	/**
	 * @param array $head
	 */
	public function addHead( $head )
	{
		if ( SPRequest::cmd( 'format' ) == 'raw' ) {
			return true;
		}
		/** @var JDocument $document */
		$document = JFactory::getDocument();
		$c = 0;
		if ( count( $head ) ) {
			$document->addCustomTag( "\n\t<!--  SobiPro Head Tags Output  -->\n" );
			$document->addCustomTag( "\n\t<script type=\"text/javascript\">/*\n<![CDATA[*/ \n\tvar SobiProUrl = '" . Sobi::FixPath( self::Url( array( 'task' => '%task%' ), true, false, true ) ) . "'; \n\tvar SobiProSection = " . ( Sobi::Section() ? Sobi::Section() : 0 ) . "; \n\tvar SPLiveSite = '" . Sobi::Cfg( 'live_site' ) . "'; \n/*]]>*/\n</script>\n" );
			if ( defined( 'SOBI_ADM_PATH' ) ) {
				$document->addCustomTag( "\n\t<script type=\"text/javascript\">/* <![CDATA[ */ \n\tvar SobiProAdmUrl = '" . Sobi::FixPath( Sobi::Cfg( 'live_site' ) . SOBI_ADM_FOLDER . '/' . self::Url( array( 'task' => '%task%' ), true, false ) ) . "'; \n/* ]]> */</script>\n" );
			}
			foreach ( $head as $type => $code ) {
				switch ( $type ) {
					default:
						{
						if ( count( $code ) ) {
							foreach ( $code as $html ) {
								++$c;
								$document->addCustomTag( $html );
							}
						}
						break;
						}
					case 'robots' :
					case 'author':
					{
						$document->setMetaData( $type, implode( ', ', $code ) );
//						$document->setHeadData( array( $type => implode( ', ', $code ) ) );
						break;
					}
					case 'keywords':
					{
						$metaKeys = trim( implode( ', ', $code ) );
						if ( Sobi::Cfg( 'meta.keys_append', true ) ) {
							$metaKeys .= Sobi::Cfg( 'string.meta_keys_separator', ',' ) . $document->getMetaData( 'keywords' );
						}
						$metaKeys = explode( Sobi::Cfg( 'string.meta_keys_separator', ',' ), $metaKeys );
						if ( count( $metaKeys ) ) {
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
					case 'description':
					{
						$metaDesc = implode( '. ', $code );
						if ( strlen( $metaDesc ) ) {
							if ( Sobi::Cfg( 'meta.desc_append', true ) ) {
								$metaDesc .= '. ' . $document->get( 'description' );
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
			$jsUrl = Sobi::FixPath( self::Url( array( 'task' => 'txt.js', 'tmpl' => 'component' ), true, false, false ) );
			$document->addCustomTag( "\n\t<script type=\"text/javascript\" src=\"" . str_replace( '&', '&amp;', $jsUrl ) . "\"></script>\n" );
			$c++;
			$document->addCustomTag( "\n\t<!--  SobiPro ({$c}) Head Tags Output -->\n" );
		}
	}
}

?>
