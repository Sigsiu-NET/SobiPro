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
 * @author Radek Suski
 * @version 1.0
 * @updated 13-Jan-2009 13:02:11
 */
final class SPPlugins
{
	/*** @var array */
	private $_actions;
	/*** @var array */
	private $_plugins;

	private function __construct()
	{
		SPLoader::loadClass( 'plugins.plugin' );
	}

	public static function & getInstance()
	{
		static $plugins = false;
		if ( !$plugins || !( $plugins instanceof SPPlugins ) ) {
			$plugins = new self();
		}
		return $plugins;
	}

	private function load( $task )
	{
		$db =& SPFactory::db();
		$adm = defined( 'SOBIPRO_ADM' ) ? 'adm.' : null;
		$enabled = $db->select( 'pid', 'spdb_plugins', [ 'enabled' => 1 ] )
				->loadResultArray();
		$condition = [ $adm . '*', $adm . $task ];
		if ( strstr( $task, '.' ) ) {
			$t = explode( '.', $task );
			$condition[ ] = $adm . $t[ 0 ] . '.*';
			$task = $t[ 0 ] . '.' . $t[ 1 ];
		}
		$this->_actions[ $task ] = [];
		try {
			$pids = $db->select( 'pid', 'spdb_plugin_task', [ 'onAction' => $condition, 'pid' => $enabled ] )
					->loadResultArray();
		} catch ( SPException $x ) {
			Sobi::Error( 'Plugins', $x->getMessage(), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		if ( !( count( $pids ) ) ) {
			$this->_actions[ $task ] = [];
		}
		// get section depend apps
		if ( Sobi::Section() && count( $pids ) ) {
			try {
				$this->_actions[ $task ] = $db->select( 'pid', 'spdb_plugin_section', [ 'section' => Sobi::Section(), 'enabled' => 1, 'pid' => $pids ] )->loadResultArray();
			} catch ( SPException $x ) {
				Sobi::Error( 'Plugins', $x->getMessage(), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
		}
		/* if we didn't get section it can be also because it wasn't initialized yet
		   * but then we have at lease on of these id in request - if so; just do nothing
		   * it will be initialized later anyway */
		elseif ( !( SPRequest::sid() || SPRequest::int( 'pid' ) ) ) {
			$this->_actions[ $task ] = $pids;
		}
		// here is a special exception for the custom listings
		// it can be l.alpha or list.alpha or listing.alpha
		if ( preg_match( '/^list\..*/', $task ) || preg_match( '/^l\..*/', $task ) ) {
			$this->_actions[ 'listing' . '.' . $t[ 1 ] ] = $pids;
		}
	}

	public function registerHandler( $action, &$object )
	{
		static $count = 0;
		$count++;
		$this->_plugins[ 'dynamic_' . $count ] = $object;
		$task = Sobi::Reg( 'task', SPRequest::task() );
		$this->_actions[ $task ][ ] = 'dynamic_' . $count;
	}

	private function initPlugin( $plugin )
	{
		if ( SPLoader::translatePath( 'opt.plugins.' . $plugin . '.init' ) ) {
			$pc = SPLoader::loadClass( $plugin . '.init', false, 'plugin' );
			$this->_plugins[ $plugin ] = new $pc( $plugin );
		}
		else {
			Sobi::Error( 'Class Load', sprintf( 'Cannot load Application file at %s. File does not exist or is not readable.', $plugin ), SPC::WARNING, 0 );
		}
	}

	/**
	 * @param string $action
	 * @param string $subject
	 * @param mixed $params
	 * @return bool
	 */
	public function trigger( $action, $subject = null, $params = [] )
	{
		static $actions = [];
		static $count = 0;
		$action = ucfirst( $action ) . ucfirst( $subject );
		$action = str_replace( 'SP', null, $action );
		$task = Sobi::Reg( 'task', SPRequest::task() );
		$task = strlen( $task ) ? $task : '*';
		if ( strstr( $task, '.' ) ) {
			$t = explode( '.', $task );
			$task = $t[ 0 ] . '.' . $t[ 1 ];
		}
		/**
		 * Joomla! -> Unable to load renderer class
		 */
		if ( $action == 'ParseContent' && SPRequest::cmd( 'format' ) == 'raw' ) {
			return;
		}
		$actions[ $count++ ] = $action;
		// this always
		SPFactory::mainframe()->trigger( $action, $params );
//	    SPConfig::debOut( $action, false, false, true );
		/**
		 * An Application should not trigger other applications
		 * Apps are running non parallel
		 * Exception, if an app will an action to be
		 * triggered this action has to begin with App
		 */
		/*
		 * it's important to write comments in own code ..
		 * It may be also helpful to read own comments sometimes
		 * How the hell "has to begin with App" == substr( $action, 0, 3 ) != 'App' ) ???
		 * ========================================================================================
		 * Note for intelligent people: it caused for example that the payment method wasn't delivered to the notification App
		 */
		if ( $count < 2 || substr( $action, 0, 3 ) == 'App' ) {

			/* load all plugins having method for this action */
			if ( !( isset( $this->_actions[ $task ] ) ) || !( count( $this->_actions[ $task ] ) ) ) {
				$this->load( $task );
			}

			/* if there were any plugin for this action, check if these are loaded */
			if ( is_array( $this->_actions[ $task ] ) && count( $this->_actions[ $task ] ) ) {
				foreach ( $this->_actions[ $task ] as $plugin ) {
					/* in case this plugin wasn't initialised */
					if ( !( isset( $this->_plugins[ $plugin ] ) ) ) {
						$this->initPlugin( $plugin );
					}
					/* call the method */
					if ( isset( $this->_plugins[ $plugin ] ) && $this->_plugins[ $plugin ]->provide( $action ) ) {
						call_user_func_array( [ $this->_plugins[ $plugin ], $action ], $params );
					}
				}
			}
		}
		unset( $actions[ $count ] );
		$count--;
		return true;
	}
}
