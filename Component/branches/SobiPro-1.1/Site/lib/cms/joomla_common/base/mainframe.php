<?php
/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * Interface between SobiPro and the used CMS
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:50:43 PM
 */
class SPJoomlaMainFrame
{
	/**
	 * @var bool
	 */
	static $cs = false;
	/**
	 */
	const baseUrl = "index.php?option=com_sobipro";

	public function __construct()
	{
		if ( self::$cs ) {
			Sobi::Error( 'mainframe', SPLang::e( 'CRITICAL_SECTION' ), SPC::ERROR, 500, __LINE__, __CLASS__ );
		}
		else {
			self::$cs = true;
			self::$cs = false;
		}
	}

	public function path( $path )
	{
		$path = explode( '.', $path );
		$sp = explode( ':', $path[ 0 ] );
		$type = $sp[ 0 ];
		unset( $sp[ 0 ] );
		$path[ 0 ] = implode( '', $sp );
		switch ( $type ) {
			case 'templates':
				$path = $type . implode( '.', $path );
				break;
		}
		return $path;
	}

	/**
	 * Gets basic data from the CMS (e.g Joomla) and stores in the #SPConfig instance
	 */
	public function getBasicCfg()
	{
		$cfg = SPFactory::config();
		$cfg->set( 'live_site', JURI::root() );
		$cfg->set( 'tmp_path',	$this->JConfigValue( 'config.tmp_path' ) );
		$cfg->set( 'from', $this->JConfigValue( 'config.mailfrom' ), 'mail' );
		$cfg->set( 'mailer', $this->JConfigValue( 'config.mailer' ), 'mail' );
		$cfg->set( 'fromname', $this->JConfigValue( 'config.fromname' ), 'mail' );
		$cfg->set( 'smtpauth', $this->JConfigValue( 'config.smtpauth' ), 'mail' );
		$cfg->set( 'smtphost', $this->JConfigValue( 'config.smtphost' ), 'mail' );
		$cfg->set( 'smtpuser', $this->JConfigValue( 'config.smtpuser' ), 'mail' );
		$cfg->set( 'smtppass', $this->JConfigValue( 'config.smtppass' ), 'mail' );
		$cfg->set( 'smtpsecure', $this->JConfigValue( 'config.smtpsecure' ), 'mail' );
		$cfg->set( 'smtpport', $this->JConfigValue( 'config.smtpport' ), 'mail' );

		$cfg->set( 'language', $this->JConfigValue( 'language' ) );
		$cfg->set( 'secret', $this->JConfigValue( 'secret' ) );
		$cfg->set( 'site_name', $this->JConfigValue( 'config.sitename' ) );
		$cfg->set( 'images_folder', SOBI_ROOT . DS . 'media/sobipro/' );
		$cfg->set( 'img_folder_live', JURI::root() . '/media/sobipro' );
		$cfg->set( 'ftp_mode', $this->JConfigValue( 'config.ftp_enable' ) );

		$cfg->set( 'root_path', SOBI_PATH );
		$cfg->set( 'cms_root_path', SOBI_ROOT );
		$cfg->set( 'live_path', SOBI_LIVE_PATH );
		if ( defined( 'SOBIPRO_ADM' ) ) {
			$cfg->set( 'adm_img_folder_live', Sobi::FixPath( JURI::root() . '/' . SOBI_ADM_FOLDER . '/images' ) );
		}
		$cfg->set( 'img_folder_path', SOBI_ROOT . DS . 'media' . DS . 'sobipro' );

		if ( $this->JConfigValue( 'config.ftp_enable' ) ) {
			if ( !( file_exists( $this->JConfigValue( 'config.tmp_path' ) . DS . 'SobiPro' ) ) ) {
				if ( !( @mkdir( $this->JConfigValue( 'config.tmp_path' ) . DS . 'SobiPro' ) ) ) {
					JFolder::create( $this->JConfigValue( 'config.tmp_path' ) . DS . 'SobiPro', 0775 );
				}
			}
			$cfg->set( 'temp', $this->JConfigValue( 'config.tmp_path' ) . DS . 'SobiPro', 'fs' );
		}
		else {
			$cfg->set( 'temp', SOBI_PATH . DS . 'tmp', 'fs' );
		}
		// try mkdir because it's always used by apache
		if ( !( Sobi::Cfg( 'cache.store', false ) ) ) {
			if ( $this->JConfigValue( 'config.ftp_enable' ) ) {
				if ( !( file_exists( $this->JConfigValue( 'config.tmp_path' ) . '/SobiPro/Cache' ) ) ) {
					if ( !( mkdir( $this->JConfigValue( 'config.tmp_path' ) . '/SobiPro/Cache' ) ) ) {
						// really ;)
						JFolder::create( $this->JConfigValue( 'config.tmp_path' ) . DS . 'SobiPro' . DS . 'Cache', 0775 );
					}
				}
				$cfg->set( 'store', $this->JConfigValue( 'config.tmp_path' ) . DS . 'SobiPro' . DS . 'Cache' . DS, 'cache' );
			}
		}
	}

	protected function JConfigValue( $value )
	{
		return JFactory::getConfig()->getValue( $value );
	}

	/**
	 * @return SPJoomlaMainFrame
	 */
	public static function & getInstance()
	{
		static $mf = false;
		if ( !$mf || !( $mf instanceof self ) ) {
			$mf = new self();
		}
		return $mf;
	}

	/**
	 * @static
	 * @param string    $msg    The error message, which may also be shown the user if need be.
	 * @param int $code The application-internal error code for this error
	 * @param mixed    $info    Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	 * @param bool $translate
	 * @return object    $error    The configured JError object
	 */
	public function runAway( $msg, $code = 500, $info = null, $translate = false )
	{
		$msg = $translate ? JText::_( $msg ) : $msg;
		$msg = str_replace( SOBI_PATH, null, $msg );
		$msg = str_replace( SOBI_ROOT, null, $msg );
		return JError::raiseError( $code, $msg, $info );
	}

	/**
	 * @return string
	 */
	public function getBack()
	{
		$r = Sobi::GetUserState( 'back_url', Sobi::Url() );
		if ( !( $r ) ) {
			$r = SPRequest::string( 'HTTP_REFERER', self::url(), false, 'SERVER' );
		}
		return $r;
	}

	/**
	 * @static
	 * @param string $msg - The message, which may also be shown the user if need be.
	 */
	public function setRedirect( $add, $msg = null, $msgtype = 'message', $now = false )
	{
		if ( is_array( $msg ) && !( is_string( $msg ) ) ) {
			$msgtype = isset( $msg[ 'msgtype' ] ) ? $msg[ 'msgtype' ] : null;
			$msg = $msg[ 'msg' ];
		}
		$a = array( 'address' => $add, 'msg' => $msg, 'msgtype' => $msgtype );
		SPFactory::registry()->set( 'redirect', $a );
		if ( $now ) {
			self::redirect();
		}
	}

	/**
	 * @static
	 * @param string $msg The message, which may also be shown the user if need be.
	 * @param null $type
	 */
	public function msg( $msg, $type = null )
	{
		if ( is_array( $msg ) ) {
			$type = isset( $msg[ 'msgtype' ] ) && strlen( $msg[ 'msgtype' ] ) ? ( $msg[ 'msgtype' ] ) : null;
			$msg = isset( $msg[ 'msg' ] ) && strlen( $msg[ 'msg' ] ) ? ( $msg[ 'msg' ] ) : null;
		}
		JFactory::getApplication()->enqueueMessage( Sobi::Txt( $msg ), $type );
	}

	public function proceedMessageQueue()
	{
		JFactory::getSession()
				->set( 'application.queue', JFactory::getApplication()->getMessageQueue() );
	}

	/**
	 * @static
	 */
	public function redirect()
	{
		$r = SPFactory::registry()->get( 'redirect' );
		if ( $r && isset( $r[ 'address' ] ) ) {
			$r[ 'address' ] = str_replace( '&amp;', '&', $r[ 'address' ] );
			$msg = isset( $r[ 'msg' ] ) && strlen( $r[ 'msg' ] ) ? Sobi::Txt( $r[ 'msg' ] ) : null;
			if ( $msg ) {
				$type = $r[ 'msgtype' ];
			}
			else {
				$type = null;
			}
			JFactory::getApplication()
					->redirect( $r[ 'address' ], $msg, $type );
		}
	}

	public function addToPathway( $name, $url )
	{
		if ( defined( 'SOBI_ADM_PATH' ) ) {
			return true;
		}
		$menu = isset( JSite::getMenu()->getActive()->link ) ? JSite::getMenu()->getActive()->link : null;
		$a = preg_replace( '/&Itemid=\d+/', null, str_replace( '/', null, $url ) );
		if ( $menu != $a ) {
			JFactory::getApplication()
					->getPathway()
					->addItem( $name, $url );
		}
	}

	public function trigger( $action, &$params )
	{
		switch ( $action ) {
			case 'ParseContent':
				if ( !( defined( 'SOBIPRO_ADM' ) ) ) {
					$params[ 0 ] =& JHTML::_( 'content.prepare', $params[ 0 ] );
				}
				break;
			default:
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger( $action, $params );
				break;
		}
	}

	/**
	 * Adds objectto the pathway
	 * @param SPDBObject $obj
	 * @return void
	 */
	public function addObjToPathway( $obj )
	{
		if ( defined( 'SOBI_ADM_PATH' ) ) {
			return true;
		}
		$menu =& JSite::getMenu()->getActive()->query;
		$sid = isset( $menu[ 'sid' ] ) ? $menu[ 'sid' ] : 0;
		$pathway = & JFactory::getApplication()->getPathway();
		if ( $obj->get( 'oType' ) == 'entry' ) {
			$id = SPRequest::int( 'pid' );
			/* if we didn't enetered this entry via category */
			if ( !$id || $id == Sobi::Section() || Sobi::Cfg( 'entry.primary_path_always' ) ) {
				$id = $obj->get( 'parent' );
			}
		}
		else {
			$id = $obj->get( 'id' );
		}
		$path = SPFactory::cache()->getVar( 'parent_path', $id );
		if ( !( $path ) ) {
			$path = SPFactory::config()->getParentPath( $id, true, false, true );
			SPFactory::cache()->addVar( $path, 'parent_path', $id );
		}
		if ( count( $path ) ) {
			/* skip everything above the linked sid */
			$rpath = array_reverse( $path );
			$path = array();
			foreach ( $rpath as $part ) {
				if ( $part[ 'id' ] == $sid ) {
					break;
				}
				$path[ ] = $part;
			}
			$path = array_reverse( $path );
			/* ^^ skip everything above the linked sid */
		}
		$title = array();
		/**
		 * Mon, Jul 16, 2012
		 * I would relay like to know why I've added the "htmlentities" call here.
		 * The second param of the 'addItem' method is URL so there should be definitely no such thing
		 * Related to Bug #692
		 */
		if ( count( $path ) ) {
			foreach ( $path as $data ) {
				$title[ ] = $data[ 'name' ];
				$pathway->addItem( $data[ 'name' ], /*htmlentities*/
					( self::url( array( 'title' => $data[ 'name' ], 'sid' => $data[ 'id' ] ) ) ) );
			}
		}
		if ( $obj->get( 'oType' ) == 'entry' ) {
			$pathway->addItem( $obj->get( 'name' ), /*htmlentities*/
				( self::url( array( 'task' => 'entry.details', 'title' => $obj->get( 'name' ), 'sid' => $obj->get( 'id' ) ) ) ) );
			$title[ ] = $obj->get( 'name' );
		}
		$this->setTitle( $title );
	}

	/**
	 * @param array $head
	 */
	public function addHead( $head )
	{
		if ( SPRequest::cmd( 'format' ) == 'raw' ) {
			return true;
		}
		$document = JFactory::getDocument();
		$c = 0;
		if ( count( $head ) ) {
			$document->addCustomTag( "\n\t<!--  SobiPro Head Tags Output  -->\n" );
			$document->addCustomTag( "\n\t<script type=\"text/javascript\">\n/*<![CDATA[*/ \n\tvar SobiProUrl = '" . Sobi::FixPath( Sobi::Cfg( 'live_site' ) . self::Url( array( 'task' => '%task%' ), true, false ) ) . "'; \n\tvar SobiProSection = " . ( Sobi::Section() ? Sobi::Section() : 0 ) . "; \n\tvar SPLiveSite = '" . Sobi::Cfg( 'live_site' ) . "'; \n/*]]>*/\n</script>\n" );
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
						if ( !( defined( 'SOBI_ADM_PATH' ) ) ) {
							$document->setMetadata( $type, implode( ', ', $code ) );
						}
						break;
					}
					case 'keywords':
						if ( !( defined( 'SOBI_ADM_PATH' ) ) ) {
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
						}
						break;
					case 'description':
					{
						$metaDesc = implode( Sobi::Cfg( 'string.meta_desc_separator', ' ' ), $code );
						if ( strlen( $metaDesc ) && !( defined( 'SOBI_ADM_PATH' ) ) ) {
							if ( Sobi::Cfg( 'meta.desc_append', true ) ) {
								$metaDesc .= ' ' . $document->get( 'description' );
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
			$jsUrl = Sobi::FixPath( Sobi::Cfg( 'live_site' ) . ( defined( 'SOBI_ADM_FOLDER' ) ? SOBI_ADM_FOLDER . '/' : '' ) . self::Url( array( 'task' => 'txt.js', 'tmpl' => 'component' ), true, false ) );
			$document->addCustomTag( "\n\t<script type=\"text/javascript\" src=\"" . str_replace( '&', '&amp;', $jsUrl ) . "\"></script>\n" );
			$c++;
			$document->addCustomTag( "\n\t<!--  SobiPro ({$c}) Head Tags Output -->\n" );
		}
	}

	/**
	 * Creating array of additional variables depend on the CMS
	 * @internal param array $var
	 * @return string
	 */
	public function form()
	{
		return array( 'option' => 'com_sobipro', 'Itemid' => SPRequest::int( 'Itemid' ) );
	}

	/**
	 * Creating URL from a array for the current CMS
	 * @param array $var
	 * @return string
	 */
	public static function url( $var = null, $js = false, $sef = true, $live = false, $forceItemId = false )
	{
		$url = self::baseUrl;
		if ( $var == 'current' ) {
			return SPRequest::raw( 'REQUEST_URI', self::baseUrl, 'SERVER' );
		}
		$sef = Sobi::Cfg( 'disable_sef_globally', false ) ? false : $sef;
		Sobi::Trigger( 'Create', 'Url', array( &$var, $js ) );
		if ( is_array( $var ) && !empty( $var ) ) {
			if (
				( isset( $var[ 'sid' ] ) && !( defined( 'SOBIPRO_ADM' ) || $forceItemId ) ) ||
				( defined( 'SOBIPRO_ADM' ) && $sef && $live )
			) {
				SPFactory::mainframe()->getItemid( $var );
			}
			if ( isset( $var[ 'title' ] ) ) {
				if ( Sobi::Cfg( 'url.title', true ) ) {
					$var[ 'title' ] = trim( SPLang::urlSafe( $var[ 'title' ] ) );
					$var[ 'sid' ] = $var[ 'sid' ] . ':' . $var[ 'title' ];
				}
				unset( $var[ 'title' ] );
			}
			foreach ( $var as $k => $v ) {
				if ( $k == 'out' ) {
					switch ( $v ) {
						case 'html':
							$var[ 'tmpl' ] = 'component';
							unset( $var[ 'out' ] );
							break;
						case 'xml':
							$var[ 'tmpl' ] = 'component';
							$var[ 'format' ] = 'raw';
						case 'raw':
							$var[ 'tmpl' ] = 'component';
							$var[ 'format' ] = 'raw';
							break;
						case 'json':
							$var[ 'out' ] = 'json';
							$var[ 'format' ] = 'raw';
							$var[ 'tmpl' ] = 'component';
							break;
					}
				}
			}
			foreach ( $var as $k => $v ) {
				$url .= "&amp;{$k}={$v}";
			}
		}
		elseif ( is_string( $var ) ) {
			if ( strstr( $var, 'index.php?' ) ) {
				$url = null;
			}
			else {
				$url .= '&amp;';
			}
			if ( strstr( $var, '=' ) ) {
				$var = str_replace( '&amp;', '&', $var );
				$var = str_replace( '&', '&amp;', $var );
				$url .= $var;
			}
			else {
				$url .= SOBI_TASK . '=';
				$url .= $var;
			}
		}
		if ( $sef && !( $live ) ) {
			$url = JRoute::_( $url, false );
		}
		else {
			$url = preg_replace( '/&(?![#]?[a-z0-9]+;)/i', '&amp;', $url );
		}
		if ( $live ) {
			/*
			 * SubDir Issues:
			 * when using SEF Joomla! router returns also the subdir
			 * and JURI::base returns the subdir too
			 * So if the URL should be SEF we have to remove the subdirectory once
			 * Otherwise it doesn't pass the JRoute::_ method so there is no subdir included
			* */
			if ( $sef ) {
				$base = JURI::base( true );
				$root = str_replace( $base, null, Sobi::Cfg( 'live_site' ) );
				$url = explode( '/', $url );
				$url = $url[ count( $url ) - 1 ];
				//                if ( defined( 'SOBIPRO_ADM' ) ) {
				//                    $router = JApplication::getInstance( 'site' )->getRouter();
				//                    $a = $router->build( $url );
				//                    $url = $router->build( $url )->toString();
				//                }
				if ( !defined( 'SOBIPRO_ADM' ) ) {
					$url = JRoute::_( $url, false );
				}
				$url = Sobi::FixPath( "{$root}{$url}" );
			}
			else {
				$adm = defined( 'SOBIPRO_ADM' ) ? SOBI_ADM_FOLDER : null;
				$url = Sobi::FixPath( Sobi::Cfg( 'live_site' ) . $adm . '/' . $url );
			}
		}
		$url = str_replace( '%3A', ':', $url );
		// all urls in front are passed to the XML/XSL template are going to be encoded anyway
		$o = SPRequest::cmd( 'format', SPRequest::cmd( 'out' ) );
		if ( !( in_array( $o, array( 'raw', 'xml' ) ) ) && !( defined( 'SOBI_ADM_PATH' ) ) ) {
			$url = html_entity_decode( $url );
		}
		$url = urldecode( $url );
		return $js ? str_replace( 'amp;', null, $url ) : $url;
	}

	protected function getItemid( &$url )
	{
		$sid = isset( $url[ 'pid' ] ) && $url[ 'pid' ] ? $url[ 'pid' ] : $url[ 'sid' ];
		if ( !( ( int )$sid ) ) {
			return false;
		}
		$url[ 'Itemid' ] = 0;
		$menu =& JApplication::getMenu( 'site' );
		if ( isset( $url[ 'task' ] ) ) {
			$task = ( $url[ 'task' ] == 'search.results' ) ? 'search' : $url[ 'task' ];
			$link = 'index.php?option=com_sobipro&task=' . $task . '&sid=' . $sid;
		}
		else {
			$link = 'index.php?option=com_sobipro&sid=' . $sid;
		}
		$item = $menu->getItems( 'link', $link, true );
		if ( $item && count( $item ) ) {
			$url[ 'Itemid' ] = $item->id;
		}
		else {
			$path = SPFactory::config()->getParentPath( $sid );
			if ( count( $path ) ) {
				foreach ( $path as $sid ) {
					$item = $menu->getItems( 'link', 'index.php?option=com_sobipro&sid=' . $sid, true );
					if ( $item && count( $item ) ) {
						$url[ 'Itemid' ] = $item->id;
					}
				}
			}
		}
	}

	public function endOut()
	{
		if ( SPRequest::cmd( 'format' ) != 'raw' ) {
			/* something like 'onDomReady' but it should be bit faster */
			echo '<script type="text/javascript">SobiPro.Ready();</script>';
		}
	}

	public function getDb()
	{
		return SPFactory::db();
	}

	/**
	 *
	 * @param id
	 */
	public function & getUser( $id = 0 )
	{
		return SPFactory::user()->getInstance( $id );
	}

	/**
	 * Switchin error reporting and displaying of errors compl. off
	 * For e.g JavaScript, or XML output where the document structure is very sensible
	 *
	 */
	public function cleanBuffer()
	{
		error_reporting( 0 );
		ini_set( 'display_errors', 'off' );
		SPRequest::set( 'tmpl', 'component' );
		JResponse::setBody( null );
		while ( ob_get_length() )
			ob_end_clean();
	}

	/**
	 * Method to determine a hash for anti-spoofing variable names
	 * @return string
	 */
	public function token()
	{
		return JUtility::getToken();
	}

	/**
	 * Checks for a form token in the request.
	 * @param string $method
	 * @return boolean
	 */
	public function checkToken( $method = 'post' )
	{
		if ( Sobi::Cfg( 'security.token', true ) ) {
			return JRequest::checkToken( $method );
		}
		else {
			return true;
		}
	}

	/**
	 * @param string $title
	 */
	public function setTitle( $title, $forceAdd = false )
	{
		$document = JFactory::getDocument();
		if ( !( is_array( $title ) ) && ( Sobi::Cfg( 'browser.add_title', true ) || $forceAdd ) ) {
			$title = array( $title );
		}
		if ( is_array( $title ) ) {
			if ( Sobi::Cfg( 'browser.add_title', true ) || $forceAdd ) {
				array_unshift( $title, $document->getTitle() );
			}
			if ( Sobi::Cfg( 'browser.full_title', true ) ) {
				if ( Sobi::Cfg( 'browser.reverse_title', false ) ) {
					$title = array_reverse( $title );
				}
				$title = implode( Sobi::Cfg( 'browser.title_separator', ' > ' ), $title );
			}
			else {
				if ( count( $title ) ) {
					$title = isset( $title[ count( $title ) - 1 ] ) ? $title[ count( $title ) - 1 ] : $title[ 0 ];
				}
				else {
					$title = null;
				}
			}
		}
		if ( strlen( $title ) ) {
			$document->setTitle( SPLang::clean( html_entity_decode( $title ) ) );
		}
	}
}

