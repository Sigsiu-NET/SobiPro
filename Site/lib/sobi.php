<?php
/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

use Sobi\Framework;

defined( 'SOBIPRO' ) || defined( '_JEXEC' ) || exit( 'Restricted access' );

/**
 * Factory alike shortcut class for simple access frequently used methods.
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:35:35 PM
 */
abstract class Sobi
{
	/**
	 * Creating an URL
	 *
	 * @param array $var - array with parameters array( 'sid' => 5, 'task' => 'entry.edit' ).
	 * If not given, returns base URL to Sobi Pro.
	 * Can be also an URL string, in this case replacing all & with &amp;
	 * If string is not an URL - it can be single task: Sobi::Url( 'entry.add' );
	 * Special case is Sobi::Url( 'current' ); - in this case return currently requestet URL
	 * @param bool $js
	 * @param bool $sef
	 * @param bool $live
	 * @param bool $forceItemId
	 * @return string
	 */
	public static function Url( $var = null, $js = false, $sef = true, $live = false, $forceItemId = false )
	{
		return SPFactory::mainframe()->url( $var, $js, $sef, $live, $forceItemId );
	}

	/**
	 * @param string $section - error section. I.e. Entry controller
	 * @param string $msg - main message
	 * @param int $type - error type
	 * @param int $code - error code
	 * @param int $line - file line
	 * @param string $file - file name
	 * @param null $sMsg - additional message
	 * @return null
	 */
	public static function Error( $section, $msg, $type = SPC::NOTICE, $code = 0, $line = null, $file = null, $sMsg = null )
	{
		if ( $type == 0 ) {
			$type = SPC::NOTICE;
		}
		/*
		* Mi., Jul 4, 2012
		* So now could someone explain me what was the sense of the code below and why trigger_error was commented out??!!
		*
		* Mi., Jul 4, 2012
		* Ok, it doesn't make much sense.
		* This is what actually should be removed.
		* 		if( Sobi::Cfg( 'debug.level', 0 ) < $type ) { return true; }
		* It was the problem with the ACL when error reporting was disabled.
		* But why the hell I removed the damn trigger_error from it?!!!
		* Being sloppy again?!!!!
		* Frack me - it means that since 20.07.2011 the whole error reporting went in nirvana??
		*/
		if ( $type == E_USER_ERROR ) {
			$rType = E_ERROR;
			$code = $code ? $code : 500;
		}
		elseif ( $type == E_USER_WARNING ) {
			$rType = E_WARNING;
		}
		else {
			$rType = $type;
		}
		if ( Sobi::Cfg( 'debug.level', 0 ) >= $rType ) {
			if ( $file ) {
				$sMsg .= sprintf( 'In file %s at line %d', $file, $line );
			}
			if ( SPRequest::task() ) {
				$sMsg .= ' [ ' . SPRequest::task() . ' ]';
			}
			$error = [
					'section' => $section,
					'message' => $msg,
					'code' => $code,
					'file' => $file,
					'line' => $line,
					'content' => $sMsg
			];
			trigger_error( 'json://' . json_encode( $error ), $type );
		}
		if ( $code ) {
			SPLoader::loadClass( 'base.mainframe' );
			SPLoader::loadClass( 'cms.base.mainframe' );
			SPFactory::mainframe()->runAway( $msg, $code, SPConfig::getBacktrace() );
		}
		return null;
	}

	/**
	 * Saves return URL - the back point to redirect to after several actions like add new object etc
	 *
	 */
	public static function ReturnPoint()
	{
		if ( !isset( $_POST[ 'option' ] ) || $_POST[ 'option' ] != 'com_sobipro'
				// wtf joobi??!!!!
				|| ( isset( $_GET[ 'hidemainmenu' ] ) && isset( $_POST[ 'hidemainmenu' ] ) )
		) {
			Sobi::SetUserState( 'back_url', Sobi::Url( 'current' ) );
		}
		else {
			$current = 'index.php?';
			foreach ( $_POST as $k => $v ) {
				$current .= $k . '=' . ( ( string )$v ) . '&amp;';
			}
			Sobi::SetUserState( 'back_url', $current );
		}
	}

	/**
	 * Returns formatted date
	 *
	 * @param string $time - time or date
	 * @param string $format - section and key in the config
	 * @return string
	 */
	public static function Date( $time = null, $format = 'date.db_format' )
	{
		return SPFactory::config()->date( $time, $format );
	}

	/**
	 * Set a redirect
	 *
	 * @param array $address - @see #Url
	 * @param string $msg - message for user
	 * @param string $msgtype - 'message' or 'error'
	 * @param bool $now - if true, redirecting immediately
	 */
	public static function Redirect( $address, $msg = null, $msgtype = 'message', $now = false )
	{
		SPFactory::mainframe()->setRedirect( $address, $msg, $msgtype );
		if ( $now ) {
			SPFactory::mainframe()->redirect();
		}
	}

	/**
	 * Returns translation of a selected language dependend string (case 1)
	 * or translates language dependend properties (case 2)
	 *
	 * case 1)
	 * @return string case 2)
	 *
	 * case 2)
	 * @internal param string $txt - string to translate
	 * @internal param array $vars - variables included in the string.
	 *             array( 'username' => $username, 'userid' => $uid ).
	 *         The language label has to be defined like this my_label = " My name is var:[username] and my id is var:[userid]"
	 * @internal param array $sids - array with ids of objects to translate
	 * @internal param array $fields - (optional) array (or string) with properties names to translate. If not given, translates all
	 * @internal param string $type - (optional) type of object (section, category, entry). If not given, translates all
	 * @internal param string $lang - (optional) specific language. If not given, use currently set language
	 */
	public static function Txt()
	{
		$args = func_get_args();
		SPLoader::loadClass( 'types.array' );
		if ( is_array( $args[ 0 ] ) && SPData_Array::is_int( $args[ 0 ] ) ) {
			return call_user_func_array( [ 'SPLang', 'translateObject' ], $args );
		}
		else {
			return call_user_func_array( [ 'SPLang', '_' ], $args );
		}
	}

	/**
	 * Cleaning string for the output
	 *
	 * @param string $txt
	 * @return string
	 */
	public static function Clean( $txt )
	{
		return SPFactory::lang()->clean( $txt );
	}

	/**
	 * Loading language file
	 *
	 * @param string $name - file name to load (without extension - has to be ini )
	 * @param bool $sections - parse sections
	 * @param bool $adm - if true, admin file will be loaded
	 * @param string $defSection - default section to load, only if not parsing sections
	 * @param string $lang - language folder. Default the currently selected language will be loaded
	 * @deprecated
	 * @return bool
	 */
	public static function LoadLangFile( $name, $sections = true, $adm = false, $defSection = 'general', $lang = null )
	{
		//		return SPFactory::lang()->loadFile( $name, $sections, $adm, $defSection, $lang );
	}

	/**
	 * Trigger plugin action: Sobi::Trigger( 'LoadField', 'Search', array( &$fields ) );
	 * @param string $action - action to trigger
	 * @param string $subject - subject of this action: e.g. entry, category, search etc
	 * @param array $params - parameters to pass to the plugin
	 * @return bool
	 */
	public static function Trigger( $action, $subject = null, $params = [] )
	{
		SPFactory::plugins()->trigger( $action, $subject, $params );
	}

	public static function RegisterHandler( $action, &$object )
	{
		SPFactory::plugins()->registerHandler( $action, $object );
	}

	public static function AttachHandler( &$object )
	{
		SPFactory::plugins()->registerHandler( null, $object );
	}

	/**
	 * check permission for an action.
	 * Can be also used like this:
	 *         Sobi::Can( 'subject.action.ownership' )
	 *         Sobi::Can( 'entry.see_unpublished.own' )
	 * @param $subject
	 * @param string $action - e.g. edit
	 * @param string $ownership - e.g. own, all or valid
	 * @param int $section - section. If not given, the current section will be used
	 * @return bool - true if authorized
	 */
	public static function Can( $subject, $action = 'access', $ownership = 'valid', $section = null )
	{
		return SPFactory::user()->can( $subject, $action, $ownership, $section );
	}

	/**
	 * Sets the value of a user state variable.
	 *
	 * @param string $key - The path of the state.
	 * @param string $value - The value of the variable.
	 * @return mixed The previous state, if one existed.
	 */
	public static function SetUserState( $key, $value )
	{
		return SPFactory::user()->setUserState( $key, $value );
	}

	/**
	 * Gets the value of a user state variable.
	 *
	 * @param string $key - The key of the user state variable.
	 * @param string $request - The name of the variable passed in a request.
	 * @param string $default - The default value for the variable if not found. Optional.
	 * @param string $type - Filter for the variable.
	 * @return mixed
	 */
	public static function GetUserState( $key, $request, $default = null, $type = 'none' )
	{
		return SPFactory::user()->getUserState( $key, $request, $default, $type );
	}

	/**
	 * Sets the value of a user data.
	 * @param    string $key - The path of the state.
	 * @param    string $value - The value of the variable.
	 * @return    mixed    The previous state, if one existed.
	 */
	public static function SetUserData( $key, $value )
	{
		return SPFactory::user()->setUserState( $key, $value );
	}

	/**
	 * Gets the value of a user data stored in session
	 * @param    string $key - The key of the user state variable.
	 * @param    string $default - The default value for the variable if not found. Optional.
	 * @return    mixed
	 */
	public static function GetUserData( $key, $default = null )
	{
		return SPFactory::user()->getUserData( $key, $default );
	}

	public static function Back()
	{
		return SPFactory::mainframe()->getBack();
	}

	/**
	 * Triggering plugin action
	 *
	 * @param string $action
	 * @param string $subject
	 * @param mixed $params
	 * @return bool
	 */
	public static function TriggerPlugin( $action, $subject = null, $params = [] )
	{
		return Sobi::Trigger( $action, $subject, $params );
	}

	/**
	 * Returns copy of stored config key
	 * Can be also used like this: Sobi::Cfg( 'config_section.config_key', 'default_value' );
	 *
	 * @param string $key - the config key
	 * @param mixed $def - default value
	 * @param string $section - config section (not the SobiPro section)
	 * @return string
	 */
	public static function Cfg( $key, $def = null, $section = 'general' )
	{
		return SPFactory::config()->key( $key, $def, $section );
	}


	public static function Ico( $icon, $def = null, $section = 'general' )
	{
		return SPFactory::config()->icon( $icon, $def, $section );
	}

	/**
	 * Returns copy of stored registry value key
	 *
	 * @param string $key - stored key
	 * @param mixed $def - default value
	 * @return mixed
	 */
	public static function Reg( $key, $def = null )
	{
		return SPFactory::registry()->get( $key, $def );
	}

	/**
	 * Returns current section id
	 *
	 * @param bool $name
	 * @return int
	 */
	public static function Section( $name = false )
	{
		static $section = null;
		if ( !( $name ) ) {
			return SPFactory::registry()->get( 'current_section' );
		}
		elseif ( ( string )$name == 'nid' ) {
			if ( !( $section ) ) {
				$section = SPFactory::Section( SPFactory::registry()->get( 'current_section' ) );
			}
			return $section->get( 'nid' );
		}
		else {
			return SPFactory::registry()->get( 'current_section_name' );
		}
	}

	/**
	 * Returns currently used language
	 *
	 * @param $path
	 * @return string
	 * @deprecated use \Sobi\FileSystem\FileSystem::FixPath
	 */
	public static function FixPath( $path )
	{
		return SPFs::clean( $path );
	}

	/**
	 * Returns currently used language
	 * @param bool $storage - force lang for storage.
	 * If the $_POST array contain "sp_language" index and the $storage param is set, this language will be returned.
	 * In other cases it is recommended to call this function with $storage = false. However because this happen only while recieving data from POST ///
	 * @param bool $allowEmpty
	 * @return string
	 */
	public static function Lang( $storage = true, $allowEmpty = false )
	{
		/* when storing lang depend values and there was lang in request */
		static $langPost = -1;
		static $langGet = -1;
		if ( $langPost == -1 || $langGet == -1 ) {
			$langPost = SPRequest::cmd( 'sp-language', false, 'post' );
			$langGet = SPRequest::cmd( 'sp-language', false, 'get' );
		}
		if ( $storage && $langPost ) {
			$lang = SPRequest::cmd( 'sp-language', false, 'post' );
		}
		/* Otherwise we maybe translating now */
		elseif ( $langGet && self::Cfg( 'lang.multimode', false ) ) {
			$lang = SPRequest::cmd( 'sp-language', false, 'get' );
		}
		else {
			static $lang = null;
			if ( !( strlen( $lang ) ) ) {
				/**
				 * Fri, Jul 7, 2017 13:42:02
				 * It doesn't makes any sense. It should always get the currently set language
				 * */
//				if ( self::Cfg( 'lang.multimode', false ) ) {
//					$lang = SPFactory::config()->key( 'language' );
//				}
//				else {
//					$lang = self::DefLang();
//				}
				$lang = SPFactory::config()->key( 'language' );
				self::Trigger( 'Language', 'Determine', [ &$lang ] );
			}
		}
		$lang = strlen( $lang ) ? $lang : ( $allowEmpty ? self::DefLang() : self::Lang( false, true ) );
		return $lang;
	}

	public static function DefLang()
	{
		if ( self::Cfg( 'lang.ignore_default', false ) ) {
			return self::Lang( false );
		}
		return strlen( self::Cfg( 'lang.default_lang', null ) ) ? self::Cfg( 'lang.default_lang' ) : SOBI_DEFLANG;
	}

	/**
	 * Returns selected property of the currently visiting user
	 * e.g Sobi::My( 'id' ); Sobi::My( 'name' );
	 *
	 * @param string $property
	 * @return mixed
	 */
	public static function My( $property )
	{
		static $user = null;
		if ( !( $user ) ) {
			$user =& SPFactory::user();
		}
		return $user->get( $property );
	}

	/**
	 * Method to initialise SobiPro from outside
	 * @param int $sid - section id
	 * @param null $root - root of Joomla!
	 * @param null $lang - language
	 * @return null
	 */
	public static function Initialise( $sid = 0, $root = null, $lang = null )
	{
		if ( !( $root ) ) {
			$root = JPATH_ROOT;
		}
		if ( !( $lang ) ) {
			$lang = JComponentHelper::getParams( 'com_languages' )->get( 'site', 'en-GB' );
		}
		self::Init( $root, $lang, $sid );
	}

	/**
	 * @deprecated since 1.1 replaced by {@link #Initialise()}
	 * @param null $root - root of Joomla!
	 * @param null $lang - language
	 * @param int $sid - section id
	 * @return null
	 * @throws Exception
	 */
	public static function Init( $root = null, $lang = null, $sid = 0 )
	{
		static $loaded = false;
		if ( !( $loaded ) ) {
			if ( !( defined( 'SOBI_CMS' ) ) ) {
				//define( 'SOBI_CMS', version_compare( JVERSION, '3.0.0', 'ge' ) ? 'joomla3' : ( version_compare( JVERSION, '1.6.0', 'ge' ) ? 'joomla16' : 'joomla15' ) );
				define( 'SOBI_CMS', version_compare( JVERSION, '3.0.0', 'ge' ) ? 'joomla3' : 'joomla16' );
			}
			defined( 'SOBI_ROOT' ) || define( 'SOBI_ROOT', $root );
			defined( 'SOBIPRO' ) || define( 'SOBIPRO', true );
			defined( 'SOBI_TASK' ) || define( 'SOBI_TASK', 'task' );
			defined( 'SOBI_DEFLANG' ) || define( 'SOBI_DEFLANG', $lang );
			defined( 'SOBI_ACL' ) || define( 'SOBI_ACL', 'front' );
			defined( 'SOBI_MEDIA' ) || define( 'SOBI_MEDIA', implode( '/', [ $root, 'media', 'sobipro' ] ) );
			defined( 'SOBI_PATH' ) || define( 'SOBI_PATH', SOBI_ROOT . '/components/com_sobipro' );
			defined( 'SOBI_LIVE_PATH' ) || define( 'SOBI_LIVE_PATH', 'components/com_sobipro' );
			require_once( SOBI_PATH . '/lib/base/fs/loader.php' );

			SPLoader::loadController( 'sobipro' );
			SPLoader::loadController( 'interface' );
			SPLoader::loadClass( 'base.exception' );
			SPLoader::loadClass( 'base.const' );
			SPLoader::loadClass( 'base.object' );
			SPLoader::loadClass( 'base.filter' );
			SPLoader::loadClass( 'base.request' );
			SPLoader::loadClass( 'cms.base.lang' );
			SPLoader::loadClass( 'models.dbobject' );
			SPLoader::loadClass( 'base.factory' );
			SPLoader::loadClass( 'base.config' );
			SPLoader::loadClass( 'cms.base.fs' );
			// in case it is a CLI call
			if ( isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
				SPFactory::config()->set( 'live_site', JURI::root() );
			}
			Framework::SetTranslator( [ 'SPlang', '_txt' ] );
			Framework::setConfig( [ 'Sobi', 'Cfg' ] );
			$loaded = true;
		}
		if ( $sid ) {
			$section = null;
			if ( $sid ) {
				$path = [];
				$id = $sid;
				$path[] = ( int )$id;
				while ( $id > 0 ) {
					try {
						$id = SPFactory::db()
								->select( 'pid', 'spdb_relations', [ 'id' => $id ] )
								->loadResult();
						if ( $id ) {
							$path[] = ( int )$id;
						}
					} catch ( SPException $x ) {
						Sobi::Error( 'ExtCoreCtrl', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
					}
				}
				$path = array_reverse( $path );
				$section = SPFactory::object( $path[ 0 ] );
			}
			/* set current section in the registry */
			SPFactory::registry()->set( 'current_section', $section->id );
		}
		$_config = &SPFactory::config();

		/* load basic configuration settings */
		$_config->addIniFile( 'etc.config', true );
		$_config->addIniFile( 'etc.base', true );

		$_config->addTable( 'spdb_config', $sid );
		/* initialise interface config setting */
		SPFactory::mainframe()->getBasicCfg();
		/* initialise config */
		$_config->init();
	}
}
