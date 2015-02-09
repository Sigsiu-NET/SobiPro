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
define( '_JEXEC', 1 );

require dirname( __FILE__ ) . '/../../../../libraries/import.php';
if ( !( defined( 'JPATH_BASE' ) ) ) {
	define( 'JPATH_BASE', realpath( dirname( __FILE__ ) . '/../../../../' ) );
}
require_once JPATH_BASE . '/includes/defines.php';
if ( file_exists( JPATH_LIBRARIES . '/import.legacy.php' ) ) {
	require_once JPATH_LIBRARIES . '/import.legacy.php';
}
require_once JPATH_LIBRARIES . '/cms/version/version.php';
if ( file_exists( JPATH_LIBRARIES . '/cms.php' ) ) {
	require_once JPATH_LIBRARIES . '/cms.php';
}

if ( !( defined( 'JVERSION' ) ) ) {
	$jVersion = new JVersion;
	define( 'JVERSION', $jVersion->getShortVersion() );
}
require_once( JPATH_ROOT . '/components/com_sobipro/lib/sobi.php' );

class SobiProCrawler extends JApplicationCli
{
	const USER_AGENT = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1');";
	protected $silent = true;
	protected $section = 0;
	protected $sections = array();
	protected $timeLimit = 3600;
	protected $start = 0;
	protected $cleanCache = true;
	protected $liveURL = '';
	protected $loopTimeLimit = 15;

	public function execute( $argv )
	{
		Sobi::Initialise();
		$continue = $this->parseParameters( $argv );
		if ( $continue ) {
			if ( !( $this->section ) ) {
				$this->sections = SPFactory::db()
						->select( 'id', 'spdb_object', array( 'oType' => 'section', 'state' => '1', '@VALID' => SPFactory::db()->valid( 'validUntil', 'validSince' ) ) )
						->loadResultArray();
			}
			else {
				$this->sections = SPFactory::db()
						->select( 'id', 'spdb_object', array( 'id' => $this->section, 'oType' => 'section', 'state' => '1', '@VALID' => SPFactory::db()->valid( 'validUntil', 'validSince' ) ) )
						->loadResultArray();
			}
			if ( !( $this->liveURL ) || !( preg_match( '/http[s]?:\/\/.*/i', $this->liveURL ) ) ) {
				$this->out( '[ERROR] A valid live URL address is required' );
			}
			if ( count( $this->sections ) ) {
				$this->start = time();
				foreach ( $this->sections as $sid ) {
					$this->crawlSobiSection( $sid );
				}
			}
			else {
				$this->out( '[ERROR] No valid sections found' );
			}
		}
	}

	protected function crawlSobiSection( $sid )
	{
		$done = false;
		$task = $this->cleanCache ? 'crawler.restart' : 'crawler.init';
		$connection = SPFactory::Instance( 'services.remote' );
		while ( !( $done ) && ( time() - $this->start ) < $this->timeLimit ) {
			$url = $this->liveURL . "/index.php?option=com_sobipro&task={$task}&sid={$sid}&format=raw&tmpl=component&timeLimit={$this->loopTimeLimit}&fullFormat=1";
			list( $content, $response ) = $this->SpConnect( $connection, $url );
			$task = 'crawler';
			if ( $response[ 'http_code' ] == 303 ) {
				preg_match( '/Location: (http.*)/', $content, $newUrl );
				list( $content, $response ) = $this->SpConnect( $connection, $newUrl[ 1 ] );
			}
			if ( $response[ 'http_code' ] == 200 ) {
				$content = substr( $content, $response[ 'header_size' ] );
				$data = json_decode( $content );
				$done = $data->status == 'done';
				$this->SpOut( '' );
				$this->SpOut( '============' );
				$this->SpOut( "[ " . date( DATE_RFC2822 ) . " ] {$data->message}" );
				$this->SpOut( '============' );
				foreach ( $data->data as $row ) {
					$u = strip_tags( $row->url );
					$this->SpOut( "{$u}\t{$row->count}\t{$row->code}\t{$row->time}" );
				}
			}
			else {
				$done = true;
				$this->out( '[ERROR] Invalid return code: ' . $response[ 'http_code' ] );
			}
		}
	}

	protected function parseParameters( $args )
	{
		if ( count( $args ) ) {
			foreach ( $args as $param ) {
				if ( $param == '--help' || $param == '-h' ) {
					$this->SobiCrawlerHelpScreen();
					return false;
				}
				if ( strstr( $param, '=' ) ) {
					$param = explode( '=', $param );
					$name = trim( $param[ 0 ] );
					$set = trim( $param[ 1 ] );
					if ( $set == 'yes' ) {
						$set = true;
					}
					elseif ( $set == 'no' ) {
						$set = false;
					}
					$this->$name = $set;
				}
			}
		}
		return true;
	}

	protected function SobiCrawlerHelpScreen()
	{
		$this->out( '============' );
		$this->out( 'SobiPro crawler v 1.0' );
		$this->out( 'Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.' );
		$this->out( 'License GNU/GPL Version 3' );
		$this->out( '============' );
		$this->out( '' );
		$this->out( 'The SobiPro crawler allows you to set up a cronjob to check all SobiPro links and build up the SobiPro caches which are useful to speed up your site significantly' );
		$this->out( '' );
		$this->out( 'Parameter list:' );
		$this->out( '' );
		$this->out( "\t liveURL - The URL address of your website/Joomla! root. Defines like liveURL=http://demo.sobi.pro This is a required parameter!" );
		$this->out( "\t silent - yes/no. In case set to 'yes' only error messages are going to be displayed. Default set to 'yes'" );
		$this->out( "\t section - section id of the section you want to crawl. If not given all valid sections are going to be crawled" );
		$this->out( "\t timeLimit - time limit (in seconds - default 3600). If the limit has been reached while crawling the crawler will stop any actions" );
		$this->out( "\t cleanCache - yes/no If set to 'yes' the cache will be invalidated first. Default set to 'yes'" );
		$this->out( "\t loopTimeLimit - time limit (in seconds - default 15) for a loop. When reached a new loop is going to be started" );
		$this->out( '' );
		$this->out( "All settings are defined like name=value (timeLimit=100). The order doesn't matter" );
		$this->out( '' );
		$this->out( '' );
	}

	protected function SpOut( $txt )
	{
		if ( !( $this->silent ) ) {
			$this->out( $txt );
		}
	}

	/**
	 * @param SPRemote $connection
	 * @param string $url
	 * @return array
	 */
	protected function SpConnect( $connection, $url )
	{
		$connection->setOptions( array( 'url' => $url, 'connecttimeout' => 10, 'returntransfer' => true, 'useragent' => self::USER_AGENT, 'header' => true, 'verbose' => false ) );
		$content = $connection->exec();
		$response = $connection->info();
		return array( $content, $response );
	}
}

JApplicationCli::getInstance( 'SobiProCrawler' )->execute( $argv );
