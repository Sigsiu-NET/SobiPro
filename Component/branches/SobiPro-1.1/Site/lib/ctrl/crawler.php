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
SPLoader::loadController( 'txt' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 15-Jul-2010 18:17:28
 */
class SPCrawler extends SPController
{
	const TIME_LIMIT = 2;
	const USER_AGENT = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1');";
	const DB_TABLE = 'spdb_crawler';
	protected $start = 0;

	public function execute()
	{
		$this->start = microtime( true );
		$sites = $this->getSites();
		$responses = array();
		$status = 'working';
		$message = null;
		$task = SPRequest::task();
		if ( in_array( $task, array( 'crawler.init', 'crawler.restart' ) ) ) {
			if ( $task == 'crawler.restart' ) {
				SPFactory::cache()->cleanSection( Sobi::Section() );
			}
			SPFactory::db()->truncate( self::DB_TABLE );
			$responses[ ] = $this->getResponse( Sobi::Cfg( 'live_site' ) . 'index.php?option=com_sobipro&sid=' . Sobi::Section() );
			$sites = $this->getSites();
		}
		if ( !( count( $sites ) ) && !( in_array( $task, array( 'crawler.init', 'crawler.restart' ) ) ) ) {
			$message = Sobi::Txt( 'CRAWL_ULR_PARSED_DONE', SPFactory::db()->select( 'count(*)', self::DB_TABLE )->loadResult() );
			SPFactory::db()->truncate( self::DB_TABLE );
			$this->response( array( 'status' => 'done', 'data' => array(), 'message' => $message ) );
		}
		if ( count( $sites ) ) {
			$i = 0;
			foreach ( $sites as $site ) {
				if ( !( strlen( $site ) ) ) {
					continue;
				}
				$responses[ ] = $this->getResponse( $site );
				$i++;
				if ( microtime( true ) - $this->start > self::TIME_LIMIT ) {
					break;
				}
			}
			$message = Sobi::Txt( 'CRAWL_ULR_PARSED_WORKING', $i, count( $sites ) );
		}
		$this->response( array( 'status' => $status, 'data' => $responses, 'message' => $message ) );
	}

	protected function response( $status )
	{
		SPFactory::mainframe()
				->cleanBuffer()
				->customHeader();
		echo json_encode( $status );
		exit;
	}

	protected function getResponse( $url )
	{
		$request = $url;
		if ( !( strstr( $request, '?' ) ) ) {
			$request .= '?';
		}
		else {
			$request .= '&';
		}
		$request .= 'format=raw&crawl=1';
		/** @var $connection SPRemote */
		$connection = SPFactory::Instance( 'services.remote' );
		$connection->setOptions(
			array(
				'url' => $request,
				'connecttimeout' => 10,
				'returntransfer' => true,
				'useragent' => self::USER_AGENT,
				'header' => true,
				'verbose' => true
			)
		);
		$content = $connection->exec();
		$response = $connection->info();
		$urls = array();
		if ( $response[ 'http_code' ] == 200 ) {
			$urls = $this->parseResponse( $content );
			$this->removeUrl( $url );
			if ( !( is_array( $urls ) ) && is_numeric( $urls ) ) {
				$response[ 'http_code' ] = $urls;
			}
		}
		if ( count( $urls ) && $response[ 'http_code' ] == 200 ) {
			$this->insertUrls( $urls );
		}
		return array(
			'url' => "<a href=\"{$url}\" target=\"_blank\">{$url}</a>",
			'count' => count( $urls ),
			'code' => $response[ 'http_code' ],
			'time' => $response[ 'total_time' ]
		);
	}

	protected function removeUrl( $url )
	{
		if ( !( $url ) ) {
			print_r( $url );
			exit;
		}
		SPFactory::db()->update( self::DB_TABLE, array( 'state' => 1 ), array( 'url' => $url ) );
	}

	protected function insertUrls( $urls )
	{
		$rows = array();
		foreach ( $urls as $url ) {
			if ( !( strlen( $url ) ) ) {
				continue;
			}
			$rows[ ] = array( 'crid' => 'NULL', 'url' => $url, 'state' => 0 );
		}
		if ( count( $rows ) ) {
			SPFactory::db()->insertArray( self::DB_TABLE, $rows, false, true );
		}
	}

	protected function getSites()
	{
		return SPFactory::db()
				->select( 'url', self::DB_TABLE, array( 'state' => 0 ) )
				->loadResultArray();
	}

	protected function parseResponse( $response )
	{
		if ( !( strlen( $response ) ) ) {
			return 204;
		}
		$links = array();
		if ( strlen( $response ) && strstr( $response, 'SobiPro' ) ) {
			list( $header, $response ) = explode( "\r\n\r\n", $response );
			$header = explode( "\n", $header );
			foreach ( $header as $line ) {
				if ( strstr( $line, 'SobiPro' ) ) {
					$line = explode( ':', $line );
					$sid = trim( $line[ 1 ] );
					if ( $sid != Sobi::Section() ) {
						return 412;
					}
				}
			}
			preg_match_all( '/href=[\'"]?([^\'" >]+)/', $response, $links, PREG_PATTERN_ORDER );
			if ( isset( $links[ 1 ] ) && $links[ 1 ] ) {
				$liveSite = Sobi::Cfg( 'live_site' );
				$host = Sobi::Cfg( 'live_site_root' );
				$links = array_unique( $links[ 1 ] );
				foreach ( $links as $index => $link ) {
					$link = trim( $link );
					$http = preg_match( '/http[s]?:\/\/.*/i', $link );
					if ( !( strlen( $link ) ) ) {
						unset( $links[ $index ] );
					}
					elseif ( strstr( $link, '#' ) ) {
						unset( $links[ $index ] );
					}
					elseif ( $http && !( strstr( $link, $liveSite ) ) ) {
						unset( $links[ $index ] );
					}
					elseif ( !( $http ) ) {
						$links[ $index ] = Sobi::FixPath( $host . '/' . $link );
					}
				}
			}
			return $links;
		}
		else {
			return 501;
		}
	}
}
