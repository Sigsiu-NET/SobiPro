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

class SPMessage
{
	/** @var array */
	private $messages = array();
	/** @var bool */
	private $reset = false;
	/** @var bool */
	private $langLoaded = false;
	/** @var array */
	private $store = array();
	/** @var array */
	private $reports = array();
	/** @var array */
	private $current = array();

	/**
	 * @return SPMessage
	 */
	private function __construct()
	{
		$this->messages = Sobi::GetUserData( 'messages-queue', $this->messages );
		$registry = SPFactory::registry()
				->loadDBSection( 'messages' )
				->get( 'messages.queue.params' );
		if ( $registry ) {
			$this->store = SPConfig::unserialize( $registry );
		}
		$reports = SPFactory::registry()
				->loadDBSection( 'reports' )
				->get( 'reports.queue.params' );
		if ( $reports ) {
			$this->reports = SPConfig::unserialize( $reports );
		}
	}

	/**
	 * @param $action
	 * @param int $sid
	 * @param array $changes
	 * @param $message
	 * @return SPMessage
	 */
	public function & logAction( $action, $sid = 0, $changes = array(), $message = null )
	{
		if ( Sobi::Cfg( 'entry.versioning', true ) ) {
			$log = array(
				'revision' => microtime( true ) . '.' . $sid . '.' . Sobi::My( 'id' ),
				'changedAt' => 'FUNCTION:NOW()',
				'uid' => Sobi::My( 'id' ),
				'userName' => Sobi::My( 'name' ),
				'userEmail' => Sobi::My( 'mail' ),
				'change' => $action,
				'site' => defined( 'SOBIPRO_ADM' ) ? 'adm' : 'site',
				'sid' => $sid,
				'changes' => SPConfig::serialize( $changes ),
				'params' => null,
				'reason' => $message,
				'language' => Sobi::Lang()
			);
			SPFactory::db()->insert( 'spdb_history', $log );
		}
		return $this;
	}

	public function getHistory( $sid )
	{
		if ( Sobi::Cfg( 'entry.versioning', true ) ) {
			$log = ( array )SPFactory::db()
					->select( '*', 'spdb_history', array( 'sid' => $sid ), 'changedAt.desc', 100 )
					->loadAssocList( 'revision' );
			if ( count( $log ) ) {
				foreach ( $log as $revision => $data ) {
					try {
						$log[ $revision ][ 'changes' ] = SPConfig::unserialize( $data[ 'changes' ] );
					} catch ( Exception $x ) {
						$this->warning( sprintf( "Can't restore revision from %s. Error was '%s'", $data[ 'changedAt' ], $x->getMessage() ), false );
					}
				}
			}
			return $log;
		}
		else {
			return array();
		}
	}

	public function getRevision( $rev )
	{
		if ( Sobi::Cfg( 'entry.versioning', true ) ) {
			$log = ( array )SPFactory::db()
					->select( '*', 'spdb_history', array( 'revision' => $rev ) )
					->loadObject( 'revision' );
			if ( count( $log ) ) {
				$log[ 'changes' ] = SPConfig::unserialize( $log[ 'changes' ] );
			}
			return $log;
		}
		else {
			return array();
		}
	}

	/**
	 * @return void
	 */
	public function storeMessages()
	{
		Sobi::SetUserData( 'messages-queue', $this->messages );
	}

	/**
	 * @param bool $reset
	 * @return array
	 */
	public function getMessages( $reset = true )
	{
		$r = $this->messages;
		if ( $reset ) {
			$this->reset();
		}
		return $r;
	}

	/**
	 * @return SPMessage
	 */
	public function resetSystemMessages()
	{
		$this->store = array();
		$this->storeMessages();
		$store = array(
			'params' => array(),
			'key' => 'queue',
			'value' => date( DATE_RFC822 ),
			'description' => null,
			'options' => null
		);
		SPFactory::registry()->saveDBSection( array( 'messages' => $store ), 'messages' );
		return $this;
	}

	/**
	 * @return SPMessage
	 */
	public function reset()
	{
		$this->messages = array();
		$this->reset = true;
		$this->storeMessages();
		return $this;
	}

	/**
	 * @return SPMessage
	 */
	public static function & getInstance()
	{
		static $message = null;
		if ( !$message || !( $message instanceof SPMessage ) ) {
			$message = new self();
		}
		return $message;
	}

	/**
	 * @param string $message
	 * @param bool $translate
	 * @param string $type
	 * @return SPMessage
	 */
	public function & setMessage( $message, $translate = true, $type = 'warning' )
	{
		if ( $translate && !( $this->langLoaded ) ) {
			SPLang::load( 'com_sobipro.messages' );
			$this->langLoaded = true;
		}
		if ( $type == 'message' ) {
			$type = 'info';
		}
		if ( is_array( $message ) && !( is_string( $message ) ) ) {
			foreach ( $message as $msg ) {
				$this->setMessage( $msg[ 'text' ], $translate, $msg[ 'type' ] );
			}
		}
		$this->messages[ $type ][ $message ] = $translate ? Sobi::Txt( strtoupper( $type ) . '.' . $message ) : $message;
		$this->current = array( 'message' => $this->messages[ $type ][ $message ], 'type' => $type, 'section' => array( 'id' => Sobi::Section(), 'name' => Sobi::Section( true ) ) );
		$this->storeMessages();
		return $this;
	}

	/**
	 * @param $section string
	 * @return SPMessage
	 */
	public function & setSystemMessage( $section = 'configuration' )
	{
		$change = count( $this->store );
		$this->current[ 'issue-type' ] = $section;
		$this->store[ md5( serialize( $this->current ) ) ] = $this->current;
		if ( count( $this->store ) > $change ) {
			$messages = SPConfig::serialize( $this->store );
			$store = array(
				'params' => $messages,
				'key' => 'queue',
				'value' => date( DATE_RFC822 ),
				'description' => null,
				'options' => null
			);
			SPFactory::registry()->saveDBSection( array( 'messages' => $store ), 'messages' );
			SPFactory::cache()->cleanSection( -1, false );
		}
		return $this;
	}

	/**
	 * @param $message
	 * @param $type -
	 * @param $section string
	 * @return SPMessage
	 */
	public function & setSilentSystemMessage( $message, $type = SPC::NOTICE_MSG, $section = 'configuration' )
	{
		$this->current = array( 'message' => $message, 'type' => $type, 'section' => array( 'id' => Sobi::Section(), 'name' => Sobi::Section( true ) ) );
		$this->current[ 'issue-type' ] = $section;
		$this->store[ md5( serialize( $this->current ) ) ] = $this->current;
		if ( count( $this->store ) ) {
			$messages = SPConfig::serialize( $this->store );
			$store = array(
				'params' => $messages,
				'key' => 'queue',
				'value' => date( DATE_RFC822 ),
				'description' => null,
				'options' => null
			);
			SPFactory::registry()->saveDBSection( array( 'messages' => $store ), 'messages' );
			SPFactory::cache()->cleanSection( -1, false );
		}
		return $this;
	}

	/**
	 * @param $message
	 * @param $spsid string
	 * @param string $type
	 * @return SPMessage
	 */
	public function & setReport( $message, $spsid, $type = SPC::INFO_MSG )
	{
		$this->reports[ $spsid ][ $type ][ ] = $message;
		if ( count( $this->reports ) ) {
			$messages = SPConfig::serialize( $this->reports );
			$store = array(
				'params' => $messages,
				'key' => 'queue',
				'value' => date( DATE_RFC822 ),
				'description' => null,
				'options' => null
			);
			SPFactory::registry()->saveDBSection( array( 'reports' => $store ), 'reports' );
		}
		return $this;
	}

	/**
	 * @param null $spsid
	 * @return array
	 */
	public function getReports( $spsid )
	{
		$reports = array();
		if ( $this->reports[ $spsid ] ) {
			$messages = SPConfig::serialize( $this->reports );
			$reports = $this->reports[ $spsid ];
			unset( $this->reports[ $spsid ] );
			$store = array(
				'params' => $messages,
				'key' => 'queue',
				'value' => date( DATE_RFC822 ),
				'description' => null,
				'options' => null
			);
			SPFactory::registry()->saveDBSection( array( 'reports' => $store ), 'reports' );
		}
		return $reports;
	}

	/**
	 * @param null $spsid
	 * @return array
	 */
	public function getSystemMessages( $spsid = null )
	{
		return $spsid ? ( isset( $this->reports[ $spsid ] ) ? $this->reports[ $spsid ] : array() ) : $this->store;
	}

//	/**
//	 * @param $id string
//	 * @return SPMessage
//	 */
//	public function & addSystemMessage( $id )
//	{
//		$change = count( $this->store );
//		$this->current[ 'issue-type' ] = $id;
//		$this->store[ md5( serialize( $this->current ) ) ] = $this->current;
//		if ( count( $this->store ) > $change ) {
//			$messages = SPConfig::serialize( $this->store );
//			$store = array(
//				'params' => $messages,
//				'key' => 'queue',
//				'value' => date( DATE_RFC822 ),
//				'description' => null,
//				'options' => null
//			);
//			SPFactory::registry()->saveDBSection( array( 'messages' => $store ), 'messages' );
//			SPFactory::cache()->cleanSection( -1, false );
//		}
//		return $this;
//	}

	/**
	 * @param string $message
	 * @param bool $translate
	 * @return SPMessage
	 */
	public function & info( $message, $translate = true )
	{
		return $this->setMessage( $message, $translate, 'info' );
	}

	/**
	 * @param string $message
	 * @param bool $translate
	 * @return SPMessage
	 */
	public function & warning( $message, $translate = true )
	{
		return $this->setMessage( $message, $translate );
	}

	/**
	 * @param string $message
	 * @param bool $translate
	 * @return SPMessage
	 */
	public function & error( $message, $translate = true )
	{
		return $this->setMessage( $message, $translate, 'error' );
	}

	/**
	 * @param string $message
	 * @param bool $translate
	 * @return SPMessage
	 */
	public function & success( $message, $translate = true )
	{
		return $this->setMessage( $message, $translate, 'success' );
	}
}
