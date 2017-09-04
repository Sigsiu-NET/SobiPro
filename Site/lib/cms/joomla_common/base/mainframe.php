<?php
/**
 * @package: SobiPro Library
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
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
 * Interface between SobiPro and the used CMS
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:50:43 PM
 */
class SPJoomlaMainFrame /*implements SPMainframeInterface*/
{
	/** @var bool */
	static $cs = false;
	/** @var string */
	const baseUrl = "index.php?option=com_sobipro";
	/** @var array */
	protected $pathway = [];

	/**
	 *
	 */
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

	/**
	 * @param $path
	 * @return array|string
	 */
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
		$cfg->set( 'live_site_root', JURI::getInstance()->toString( [ 'scheme', 'host', 'port' ] ) );
		$cfg->set( 'tmp_path', $this->JConfigValue( 'config.tmp_path' ) );
		$cfg->set( 'from', $this->JConfigValue( 'config.mailfrom' ), 'mail' );
		$cfg->set( 'mailer', $this->JConfigValue( 'config.mailer' ), 'mail' );
		$cfg->set( 'fromname', $this->JConfigValue( 'config.fromname' ), 'mail' );
		$cfg->set( 'smtpauth', $this->JConfigValue( 'config.smtpauth' ), 'mail' );
		$cfg->set( 'smtphost', $this->JConfigValue( 'config.smtphost' ), 'mail' );
		$cfg->set( 'smtpuser', $this->JConfigValue( 'config.smtpuser' ), 'mail' );
		$cfg->set( 'smtppass', $this->JConfigValue( 'config.smtppass' ), 'mail' );
		$cfg->set( 'smtpsecure', $this->JConfigValue( 'config.smtpsecure' ), 'mail' );
		$cfg->set( 'smtpport', $this->JConfigValue( 'config.smtpport' ), 'mail' );

		$cfg->set( 'unicode', $this->JConfigValue( 'unicodeslugs' ), 'sef' );

		$lang = $this->JConfigValue( 'language' );
		if ( !( $lang ) ) {
			$lang = SPRequest::cmd( 'language' );
		}
		$cfg->set( 'language', $lang );
		$cfg->set( 'secret', $this->JConfigValue( 'secret' ) );
		$cfg->set( 'site_name', $this->JConfigValue( 'config.sitename' ) );

		$cfg->set( 'media_folder', SOBI_ROOT . '/media/sobipro/' );
		$cfg->set( 'media_folder_live', JURI::root() . '/media/sobipro' );

		$cfg->set( 'images_folder', SOBI_ROOT . '/images/sobipro/' );
		$cfg->set( 'images_folder_live', JURI::root() . '/images/sobipro' );

		$cfg->set( 'ftp_mode', $this->JConfigValue( 'config.ftp_enable' ) );
		$cfg->set( 'time_offset', $this->JConfigValue( 'offset' ) );

		$cfg->set( 'root_path', SOBI_PATH );
		$cfg->set( 'cms_root_path', SOBI_ROOT );
		$cfg->set( 'live_path', SOBI_LIVE_PATH );

		if ( defined( 'SOBIPRO_ADM' ) ) {
			$cfg->set( 'adm_img_folder_live', Sobi::FixPath( JURI::root() . '/' . SOBI_ADM_FOLDER . '/images' ) );
		}
		///same as media_folder
		/// $cfg->set( 'img_folder_path', SOBI_ROOT . '/media/sobipro' );

		if ( $this->JConfigValue( 'config.ftp_enable' ) ) {
			if ( !( file_exists( $this->JConfigValue( 'config.tmp_path' ) . '/SobiPro' ) ) ) {
				if ( !( @mkdir( $this->JConfigValue( 'config.tmp_path' ) . '/SobiPro' ) ) ) {
					JFolder::create( $this->JConfigValue( 'config.tmp_path' ) . '/SobiPro', 0775 );
				}
			}
			$cfg->set( 'temp', $this->JConfigValue( 'config.tmp_path' ) . '/SobiPro', 'fs' );
		}
		else {
			$cfg->set( 'temp', SOBI_PATH . '/tmp', 'fs' );
		}

		// try mkdir because it's always used by apache
		if ( !( Sobi::Cfg( 'cache.store', false ) ) ) {
			if ( $this->JConfigValue( 'config.ftp_enable' ) ) {
				if ( !( file_exists( $this->JConfigValue( 'config.tmp_path' ) . '/SobiPro/Cache' ) ) ) {
					if ( !( mkdir( $this->JConfigValue( 'config.tmp_path' ) . '/SobiPro/Cache' ) ) ) {
						// really ;)
						if ( !( JFolder::create( $this->JConfigValue( 'config.tmp_path' ) . '/SobiPro/Cache', 0775 ) ) ) {
							SPFactory::message()
									->setSilentSystemMessage( Sobi::e( 'CANNOT_CREATE_CACHE_DIRECTORY' ), SPC::ERROR_MSG );
						}

					}
				}
				$cfg->set( 'store', $this->JConfigValue( 'config.tmp_path' ) . '/SobiPro/Cache/' );
			}
		}
		// Mon, Jun 29, 2015 10:52:09 - compat mode
		if ( $cfg->get( 'template.icon_fonts_arr', -1 ) == -1 ) {
			$cfg->set( 'icon_fonts_arr', [ 'font-awesome-3-local' ], 'template' );
		}
		if ( !( is_array( $cfg->get( 'template.icon_fonts_arr', -1 ) ) ) ) {
			$cfg->change( 'icon_fonts_arr', [ $cfg->get( 'template.icon_fonts_arr', -1 ) ], 'template' );
		}
	}

	/**
	 * @param $value
	 * @return mixed
	 */
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
	 * @param string $msg The error message, which may also be shown the user if need be.
	 * @param int $code The application-internal error code for this error
	 * @param mixed $info Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	 * @param bool $translate
	 * @throws Exception
	 * @return object    $error    The configured JError object
	 */
	public function runAway( $msg, $code = 500, $info = null, $translate = false )
	{
		$msg = $translate ? JText::_( $msg ) : $msg;
		$msg = str_replace( SOBI_PATH, null, $msg );
		$msg = str_replace( SOBI_ROOT, null, $msg );
		throw new Exception( $msg, $code );
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
	 * @param $add
	 * @param string $msg - The message, which may also be shown the user if need be.
	 * @param string $msgtype
	 * @param bool $now
	 */
	public function setRedirect( $add, $msg = null, $msgtype = 'message', $now = false )
	{
		if ( is_array( $msg ) && !( is_string( $msg ) ) ) {
			$msgtype = isset( $msg[ 'msgtype' ] ) ? $msg[ 'msgtype' ] : null;
			$msg = $msg[ 'msg' ];
		}
		$a = [ 'address' => $add, 'msg' => $msg, 'msgtype' => $msgtype ];
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
//		JFactory::getApplication()->enqueueMessage( Sobi::Txt( $msg ), $type );
		SPFactory::message()->setMessage( $msg, true, $type );
	}

	/**
	 *
	 */
	public function proceedMessageQueue()
	{
		JFactory::getSession()
				->set( 'application.queue', JFactory::getApplication()->getMessageQueue() );
	}

	/**
	 * @static
	 * @param int $code HTTP response code
	 */
	public function redirect( $code = 302 )
	{
		$r = SPFactory::registry()->get( 'redirect' );
		if ( $r && isset( $r[ 'address' ] ) ) {
			$r[ 'address' ] = str_replace( '&amp;', '&', $r[ 'address' ] );
			/** Sat, Oct 4, 2014 15:22:44
			 * Here is something wrong. The method redirect do not get the message and type params
			 * Instead we're sending a "moved" param which results with a 303 redirect code */
//			JFactory::getApplication()
//					->redirect( $r[ 'address' ], $msg, $type );
			$msg = isset( $r[ 'msg' ] ) && strlen( $r[ 'msg' ] ) ? Sobi::Txt( $r[ 'msg' ] ) : null;
			if ( $msg ) {
				$type = $r[ 'msgtype' ];
			}
			else {
				$type = 'message';
			}
			JFactory::getApplication()
					->enqueueMessage( $msg, $type );
			JFactory::getApplication()
					->redirect( $r[ 'address' ], $code );

		}
	}

	/**
	 * @param $name
	 * @param $url
	 * @return SPJoomlaMainFrame
	 */
	public function & addToPathway( $name, $url )
	{
		if ( defined( 'SOBI_ADM_PATH' ) ) {
			return true;
		}
		$query = parse_url( $url );
		if ( is_array( $query ) && count( $query ) && isset( $query[ 'query' ] ) && strstr( $query[ 'query' ], 'crawl' ) ) {
			parse_str( $query[ 'query' ], $vars );
			unset( $vars[ 'format' ] );
			unset( $vars[ 'crawl' ] );
			$query[ 'query' ] = count( $vars ) ? http_build_query( $vars ) : null;
			if ( $query[ 'query' ] ) {
				$url = $query[ 'path' ] . '?' . $query[ 'query' ];
			}
			else {
				$url = $query[ 'path' ];
			}
		}
		$menu = isset( JFactory::getApplication()->getMenu()->getActive()->link ) ? JFactory::getApplication()->getMenu()->getActive()->link : null;
		$a = preg_replace( '/&Itemid=\d+/', null, str_replace( '/', null, $url ) );
		if ( $menu != $a ) {
			JFactory::getApplication()
					->getPathway()
					->addItem( $name, $url );
			$this->pathway[] = [ 'name' => $name, 'url' => $url ];
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function getPathway()
	{
		return $this->pathway;
	}

	/**
	 * @param $action
	 * @param $params
	 */
	public function trigger( $action, &$params )
	{
		switch ( $action ) {
			case 'ParseContent':
				if ( !( defined( 'SOBIPRO_ADM' ) ) ) {
					$params[ 0 ] = JHTML::_( 'content.prepare', $params[ 0 ] );
				}
				break;
			default:
				$dispatcher = JEventDispatcher::getInstance();
				$dispatcher->trigger( $action, $params );
				break;
		}
	}

	/**
	 * Adds object to the pathway
	 * @param SPDBObject $obj
	 * @param array $site
	 * @return mixed
	 */
	public function & addObjToPathway( $obj, $site = [] )
	{
		if ( defined( 'SOBI_ADM_PATH' ) ) {
			return true;
		}
		$active = JFactory::getApplication()->getMenu()->getActive();
		$menu = isset( $active->query ) ? $active->query : [];
		$sid = isset( $menu[ 'sid' ] ) ? $menu[ 'sid' ] : 0;
		$resetPathway = false;
		if ( $obj->get( 'oType' ) == 'entry' ) {
			$id = SPRequest::int( 'pid' );
			/** if the entry isn't linked directly in the menu */
			if ( !( $obj->get( 'id' ) == $sid ) ) {
				/* if we didn't entered this entry via category */
				if ( !( $id ) || $id == Sobi::Section() || Sobi::Cfg( 'entry.primary_path_always' ) ) {
					$id = $obj->get( 'parent' );
					$resetPathway = true;
				}
			}
			/** if it is linked in the Joomla! menu we have nothing to do */
			else {
				/** ok - here is the weird thing:
				 * When it is accessed via menu we have to force cache to create another version
				 * because the pathway is stored in the cache
				 * @todo find better solution for it
				 */
				$mid = true;
				SPFactory::registry()
						->set( 'cache_view_recreate_request', $mid )
						->set( 'cache_view_add_itemid', JFactory::getApplication()->getMenu()->getActive()->id );
				return $this;

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
			$path = [];
			foreach ( $rpath as $part ) {
				if ( isset( $part[ 'id' ] ) && $part[ 'id' ] == $sid ) {
					break;
				}
				$path[] = $part;
			}
			$path = array_reverse( $path );
			/* ^^ skip everything above the linked sid */
		}
		$title = [];
		// if there was an active menu - add its title to the browser title as well
		if ( $sid ) {
			$title[] = JFactory::getDocument()->getTitle();
		}
		/**
		 * Mon, Jul 16, 2012
		 * I would relay like to know why I've added the "htmlentities" call here.
		 * The second param of the 'addItem' method is URL so there should be definitely no such thing
		 * Related to Bug #692
		 */
		if ( count( $path ) ) {
			if ( $resetPathway ) {
				/** we have to reset the J! pathway in case:
				 *  - we are entering an entry and we want to show the pathway corresponding to the main parent if of the entry
				 *    but we have also an Itemid and Joomla! set already the pathway partialy so we need to override it
				 *    It wouldn't be normally a problem but when SEF is enabled we do not have the pid so we don't know how it has been enetered
				 */
				JFactory::getApplication()
						->getPathway()
						->setPathway( [] );
			}
			foreach ( $path as $data ) {
				if ( !( isset( $data[ 'name' ] ) || isset( $data[ 'id' ] ) ) || !( $data[ 'id' ] ) ) {
					continue;
				}
				$title[] = $data[ 'name' ];
				$this->addToPathway( $data[ 'name' ], ( self::url( [ 'title' => Sobi::Cfg( 'sef.alias', true ) ? $data[ 'alias' ] : $data[ 'name' ], 'sid' => $data[ 'id' ] ] ) ) );
			}
		}
		if ( $obj->get( 'oType' ) == 'entry' ) {
			$this->addToPathway( $obj->get( 'name' ), ( self::url( [ 'task' => 'entry.details', 'title' => Sobi::Cfg( 'sef.alias', true ) ? $obj->get( 'nid' ) : $obj->get( 'name' ), 'sid' => $obj->get( 'id' ) ] ) ) );
			$title[] = $obj->get( 'name' );
		}
//		if ( count( $site ) && $site[ 0 ] ) {
//			$title[ ] = Sobi::Txt( 'SITES_COUNTER', $site[ 1 ], $site[ 0 ] );
//		}
		SPFactory::header()->addTitle( $title, $site );

		return $this;
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
					default:
						if ( count( $code ) ) {
							foreach ( $code as $html ) {
								++$c;
								$document->addCustomTag( $html );
							}
						}
						break;
					case 'robots' :
					case 'author':
						if ( !( defined( 'SOBI_ADM_PATH' ) ) ) {
							$document->setMetadata( $type, implode( ', ', $code ) );
						}
						break;
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
			$jsUrl = Sobi::FixPath( Sobi::Cfg( 'live_site' ) . ( defined( 'SOBI_ADM_FOLDER' ) ? SOBI_ADM_FOLDER . '/' : '' ) . self::Url( [ 'task' => 'txt.js', 'format' => 'json' ], true, false ) );
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
		return [ 'option' => 'com_sobipro', 'Itemid' => SPRequest::int( 'Itemid' ) ];
	}

	/**
	 * Creating URL from a array for the current CMS
	 * @param array $var
	 * @param bool $js
	 * @param bool $sef
	 * @param bool $live
	 * @param bool $forceItemId
	 * @return string
	 */
	public static function url( $var = null, $js = false, $sef = true, $live = false, $forceItemId = false )
	{
		$url = self::baseUrl;
		if ( $var == 'current' ) {
			return SPRequest::raw( 'REQUEST_URI', self::baseUrl, 'SERVER' );
		}
		// don't remember why :(
		// Nevertheless it is generating &amp; in URL fro ImEx
//		$sef = Sobi::Cfg( 'disable_sef_globally', false ) ? false : ( defined( 'SOBIPRO_ADM' ) && !( $forceItemId ) ? false : $sef );
		$sef = Sobi::Cfg( 'disable_sef_globally', false ) ? false : $sef;
		Sobi::Trigger( 'Create', 'Url', [ &$var, $js ] );
		if ( is_array( $var ) && !empty( $var ) ) {
			if ( isset( $var[ 'option' ] ) ) {
				$url = str_replace( 'com_sobipro', $var[ 'option' ], $url );
				unset( $var[ 'option' ] );
			}
			if ( ( isset( $var[ 'sid' ] ) && ( !defined( 'SOBIPRO_ADM' ) || $forceItemId ) ) || ( defined( 'SOBIPRO_ADM' ) && $sef && $live ) ) {
				if ( !( isset( $var[ 'Itemid' ] ) ) || !( $var[ 'Itemid' ] ) ) {
					SPFactory::mainframe()->getItemid( $var );
				}
			}
			if ( isset( $var[ 'title' ] ) ) {
				if ( Sobi::Cfg( 'url.title', true ) ) {
					$var[ 'title' ] = trim( SPLang::urlSafe( $var[ 'title' ] ) );
					$var[ 'sid' ] = $var[ 'sid' ] . ':' . $var[ 'title' ];
				}
				unset( $var[ 'title' ] );
			}
			if ( isset( $var[ 'format' ] ) && $var[ 'format' ] == 'raw' && $sef ) {
				unset( $var[ 'format' ] );
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
		if ( !( in_array( $o, [ 'raw', 'xml' ] ) ) && !( defined( 'SOBI_ADM_PATH' ) ) ) {
			$url = html_entity_decode( $url );
		}
		$url = str_replace( ' ', '%20', urldecode( $url ) );
		return $js ? str_replace( 'amp;', null, $url ) : $url;
	}

	/**
	 * @param $url
	 * @return bool
	 */
	protected function getItemid( &$url )
	{
		$sid = isset( $url[ 'pid' ] ) && $url[ 'pid' ] ? $url[ 'pid' ] : $url[ 'sid' ];
		if ( !( ( int )$sid ) ) {
			return false;
		}
		$url[ 'Itemid' ] = 0;
		// Thu, Feb 27, 2014 16:28:22 - iy is probably the right solution
		// but NTaRS
		$menu = JFactory::getApplication( 'site' )->getMenu( 'site' );
		// Thu, Mar 6, 2014 12:42:01  - let's check
//		$menu = JApplication::getMenu( 'site' );
		if ( isset( $url[ 'task' ] ) ) {
			$task = ( $url[ 'task' ] == 'search.results' ) ? 'search' : $url[ 'task' ];
			$link = 'index.php?option=com_sobipro&task=' . $task . '&sid=' . $sid;
		}
		else {
			if ( isset( $url[ 'sptpl' ] ) ) {
				$link = 'index.php?option=com_sobipro&sptpl=' . $url[ 'sptpl' ] . '&sid=' . $sid;
			}
			else {
				$link = 'index.php?option=com_sobipro&sid=' . $sid;
			}
		}
		/** Fri, Feb 17, 2017 10:46:34 - check a direct link first - for e.g. linked entries */
		if ( isset( $url[ 'sid' ] ) ) {
			$item = $menu->getItems( 'link', 'index.php?option=com_sobipro&sid=' . $url[ 'sid' ], true );
			if ( isset( $item ) && isset( $item->id ) ) {
				$url[ 'Itemid' ] = $item->id;
			}
		}
		if ( !( $url[ 'Itemid' ] ) ) {
			$item = $menu->getItems( 'link', $link, true );
			if ( $item && count( $item ) ) {
				if ( isset( $url[ 'sptpl' ] ) ) {
					unset( $url[ 'sptpl' ] );
				}
				$url[ 'Itemid' ] = $item->id;
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
		if ( !( $url[ 'Itemid' ] ) && !( defined( 'SOBIPRO_ADM' ) ) ) {
			$url[ 'Itemid' ] = Sobi::Cfg( 'itemid.' . Sobi::Section( 'nid' ), 0 );
		}
		// if we still don't have an Itemid it means that there is no link to SobiPro section
		if ( !( $url[ 'Itemid' ] ) && !( defined( 'SOBIPRO_ADM' ) ) ) {
			SPFactory::message()
					->warning( Sobi::Txt( 'ITEMID_MISSING_WARN', 'https://www.sigsiu.net/help_screen/joomla.menu', $sid ), false, false )
					->setSystemMessage( 'SEF-URL' );
		}
	}

	/**
	 *
	 */
	public function endOut()
	{
		if ( ( !strlen( SPRequest::cmd( 'format' ) ) || SPRequest::cmd( 'format' ) == 'html' ) ) {
			/* something like 'onDomReady' but it should be bit faster */
			echo '<script type="text/javascript">SobiPro.Ready();</script>';
		}
	}

	/**
	 * @return SPDb
	 */
	public function getDb()
	{
		return SPFactory::db();
	}

	/**
	 *
	 * @param int $id
	 * @return \JUser
	 * @internal param $id
	 */
	public function & getUser( $id = 0 )
	{
		return SPFactory::user()->getInstance( $id );
	}

	/**
	 * Switching error reporting and displaying of errors compl. off
	 * For e.g JavaScript, or XML output where the document structure is very sensible
	 *
	 */
	public function & cleanBuffer()
	{
		error_reporting( 0 );
		ini_set( 'display_errors', 'off' );
		SPRequest::set( 'tmpl', 'component' );
		JResponse::setBody( null );
		while ( ob_get_length() )
			ob_end_clean();
		return $this;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function & customHeader( $type = 'application/json' )
	{
		header( 'Content-type: ' . $type );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
		return $this;
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
//			return JRequest::checkToken( $method );
			return JSession::checkToken( $method );
		}
		else {
			return true;
		}
	}

	/**
	 * @param string $title
	 * @param bool $forceAdd
	 */
	public function setTitle( $title, $forceAdd = false )
	{
		$document = JFactory::getDocument();
		if ( !( is_array( $title ) ) && ( Sobi::Cfg( 'browser.add_title', true ) || $forceAdd ) ) {
			$title = [ $title ];
		}
		if ( is_array( $title ) ) {
			//browser.add_title = true: adds the Joomla part (this is normally the menu item) in front of it (works only if full_title is also set to true)
			$jTitle = $document->getTitle(); //get the title Joomla has set
			if ( Sobi::Cfg( 'browser.add_title', true ) || $forceAdd ) {
				if ( $title[ 0 ] != $jTitle ) {
					array_unshift( $title, $jTitle );
				}
			}
			else {
				if ( $title[ 0 ] == $jTitle ) {
					array_shift( $title );
				}
			}
			//if ( Sobi::Cfg( 'browser.full_title', true ) || true ) {
			//browser.full_title = true: if title is array, use only the last. That's e.g. the entry name without categories for SobiPro standard title
			if ( count( $title ) ) {
				if ( is_array( $title ) ) {
					if ( Sobi::Cfg( 'browser.reverse_title', false ) ) {
						$title = array_reverse( $title );
					}
					$title = implode( Sobi::Cfg( 'browser.title_separator', ' - ' ), $title );
				}
				else {
					$title = isset( $title[ count( $title ) - 1 ] ) ? $title[ count( $title ) - 1 ] : $title[ 0 ];
				}
			}
			else {
				$title = null;
			}

		}
		if ( strlen( $title ) ) {
			if ( !( defined( 'SOBIPRO_ADM' ) ) ) {
				if ( JFactory::getApplication()->getCfg( 'sitename_pagetitles', 0 ) == 1 ) {
					$title = JText::sprintf( 'JPAGETITLE', JFactory::getApplication()->getCfg( 'sitename' ), $title );
				}
				elseif ( JFactory::getApplication()->getCfg( 'sitename_pagetitles', 0 ) == 2 ) {
					$title = JText::sprintf( 'JPAGETITLE', $title, JFactory::getApplication()->getCfg( 'sitename' ) );
				}
			}
			$document->setTitle( SPLang::clean( html_entity_decode( $title ) ) );
		}
	}

	public function setCookie( $name, $value, $expire = 0, $httponly = false, $secure = false, $path = '/', $domain = null )
	{
		setcookie( $name, $value, $expire, $path, $domain, $secure, $httponly );
	}
}

/** Legacy class - @deprecated */
class SPMainFrame
{
	/**
	 * @deprecated
	 * @param $msg
	 * @param int $code
	 * @param null $info
	 * @param bool $translate
	 * @return object
	 */
	public static function runAway( $msg, $code = 500, $info = null, $translate = false )
	{
		return SPFactory::mainframe()->runAway( $msg, $code, $info, $translate );
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public static function getBack()
	{
		return SPFactory::mainframe()->getBack();
	}

	/**
	 * @deprecated
	 * @param $add
	 * @param null $msg
	 * @param string $msgtype
	 * @param bool $now
	 */
	public static function setRedirect( $add, $msg = null, $msgtype = 'message', $now = false )
	{
		return SPFactory::mainframe()->setRedirect( $add, $msg, $msgtype, $now );
	}

	/**
	 * @deprecated
	 * @param $msg
	 * @param null $type
	 * @return string
	 */
	public static function msg( $msg, $type = null )
	{
		return SPFactory::mainframe()->getBack( $msg, $type );
	}

	/**
	 * @deprecated
	 */
	public static function redirect()
	{
		return SPFactory::mainframe()->redirect();
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public static function form()
	{
		return SPFactory::mainframe()->form();
	}

	/**
	 * @deprecated
	 * @param null $var
	 * @param bool $js
	 * @param bool $sef
	 * @param bool $live
	 * @param bool $forceItemId
	 * @return string
	 */
	public static function url( $var = null, $js = false, $sef = true, $live = false, $forceItemId = false )
	{
		return SPFactory::mainframe()->url( $var, $js, $sef, $live, $forceItemId );
	}

	/**
	 * @deprecated
	 */
	public static function endOut()
	{
		return SPFactory::mainframe()->endOut();
	}
}
