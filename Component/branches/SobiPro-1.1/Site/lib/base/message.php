<?php
/**
 * @version: $Id$
 * @package: SobiPro
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
 * $Date:$
 * $Revision:$
 * $Author:$
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
	}

	/**
	 * @return array
	 */
	public function getSystemMessages()
	{
		return $this->store;
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
