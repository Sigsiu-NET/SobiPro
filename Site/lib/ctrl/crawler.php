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
SPLoader::loadController( 'controller' );

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
	const FORMAT = 'tmpl=component&crawl=1';
	const FORMAT_FULL = 'crawl=1';
	private $format = null;
	private $skipTasks = [ 'entry.edit', 'entry.disable', 'entry.save', 'entry.clone', 'entry.payment', 'entry.submit', 'entry.approve', 'entry.publish', 'entry.delete' ];

	public function execute()
	{
		$this->start = microtime( true );
		$sites = $this->getSites();
		$responses = [];
		$status = 'working';
		$message = null;
		$this->format = SPRequest::bool( 'fullFormat' ) ? self::FORMAT_FULL : self::FORMAT;
//		$this->format = SPRequest::bool( 'fullFormat' ) ? self::FORMAT_FULL : self::FORMAT_FULL;
		$task = SPRequest::task();
		if ( in_array( $task, [ 'crawler.init', 'crawler.restart' ] ) ) {
			if ( $task == 'crawler.restart' ) {
				SPFactory::cache()->cleanSection( Sobi::Section() );
			}
			SPFactory::db()->truncate( self::DB_TABLE );
			$multiLang = Sobi::Cfg( 'lang.multimode', false );
			if ( $multiLang ) {
				$langs = SPFactory::CmsHelper()->getLanguages();
				if ( $multiLang && $langs ) {
					foreach ( $langs as $lang ) {
						$responses[ ] = $this->getResponse( Sobi::Cfg( 'live_site' ) . 'index.php?option=com_sobipro&sid=' . Sobi::Section() . '&lang=' . $lang );
					}
				}
			}
			$responses[ ] = $this->getResponse( Sobi::Cfg( 'live_site' ) . 'index.php?option=com_sobipro&sid=' . Sobi::Section() );
			$sites = $this->getSites();
		}
		if ( !( count( $sites ) ) && !( in_array( $task, [ 'crawler.init', 'crawler.restart' ] ) ) ) {
			$message = Sobi::Txt( 'CRAWL_URL_PARSED_DONE', SPFactory::db()->select( 'count(*)', self::DB_TABLE )->loadResult() );
			SPFactory::db()->truncate( self::DB_TABLE );
			$this->out( [ 'status' => 'done', 'data' => [], 'message' => $message ] );
		}
		if ( count( $sites ) ) {
			$i = 0;
			$timeLimit = SPRequest::int( 'timeLimit', self::TIME_LIMIT, 'get', true );
			foreach ( $sites as $site ) {
				if ( !( strlen( $site ) ) ) {
					continue;
				}
				$responses[ ] = $this->getResponse( $site );
				$i++;
				if ( microtime( true ) - $this->start > $timeLimit ) {
					break;
				}
			}
			$message = Sobi::Txt( 'CRAWL_URL_PARSED_WORKING', $i, count( $sites ) );
		}
		$this->out( [ 'status' => $status, 'data' => $responses, 'message' => $message ] );
	}

	protected function out( $status )
	{
		SPFactory::mainframe()
				->cleanBuffer()
				->customHeader();
		echo json_encode( $status );
		exit;
	}

	protected function getResponse( $url )
	{
		$request = str_replace( 'amp;', null, $url );
//		$request = parse_url( $url );
//		$request[ 'query' ] =  urlencode( $request[ 'query' ] );
//		$request = $request[ 'scheme' ].'://'.$request[ 'host' ].$request[ 'path' ].'?'.$request[ 'query' ];
		if ( !( strstr( $request, '?' ) ) ) {
			$request .= '?';
		}
		else {
			$request .= '&';
		}
		$request .= $this->format;
		/** @var $connection SPRemote */
		$connection = SPFactory::Instance( 'services.remote' );
		$connection->setOptions(
			[
				'url' => $request,
				'connecttimeout' => 10,
				'returntransfer' => true,
				'useragent' => self::USER_AGENT,
				'header' => true,
				'verbose' => true
			]
		);
		$content = $connection->exec();
		$response = $connection->info();
		$urls = [];
		if ( $response[ 'http_code' ] == 200 ) {
			$urls = $this->parseResponse( $content );
			if ( !( is_array( $urls ) ) && is_numeric( $urls ) ) {
				$response[ 'http_code' ] = $urls;
			}
		}
		if ( $response[ 'http_code' ] == 303 ) {
			preg_match( '/Location: (http.*)/', $content, $newUrl );
			$urls[ ] = str_replace( [ '?' . $this->format, '&' . $this->format ], null, trim( $newUrl[ 1 ] ) );
		}
		if ( count( $urls ) ) {
			$this->insertUrls( $urls );
		}
		$this->removeUrl( $url );
		return [
			'url' => "<a href=\"{$url}\" target=\"_blank\">{$url}</a>",
			'count' => count( $urls ),
			'code' => $response[ 'http_code' ],
			'time' => $response[ 'total_time' ]
		];
	}

	protected function removeUrl( $url )
	{
		SPFactory::db()->update( self::DB_TABLE, [ 'state' => 1 ], [ 'url' => $url ] );
	}

	protected function insertUrls( $urls )
	{
		$rows = [];
//		$multiLang = Sobi::Cfg( 'lang.multimode', false );
//		$langs = SPFactory::CmsHelper()->getLanguages();
//		$language = Sobi::Lang();
		foreach ( $urls as $url ) {
			$url = str_replace( '&amp;', '&', $url );
			if ( !( strlen( $url ) ) ) {
				continue;
			}
			$break = false;
			foreach( $this->skipTasks as $task ) {
				if( strstr( $url, $task ) ) {
					$break = true;
					break;
				}
			}
			if( $break ) {
				return $break;
			}
			$schema = parse_url( $url );
			if ( isset( $schema[ 'query' ] ) ) {
				parse_str( $schema[ 'query' ], $query );
				if ( isset( $query[ 'format' ] ) ) {
					continue;
				}
				if ( isset( $query[ 'date' ] ) ) {
					$query[ 'date' ] = explode( '.', $query[ 'date' ] );
					$year = $query[ 'date' ][ 0 ];
					if ( $year > ( date( 'Y' ) + 2 ) || $year < ( date( 'Y' ) - 2  ) ) {
						continue;
					}
				}
				if ( isset( $query[ 'task' ] ) && in_array( $query[ 'task' ], $this->skipTasks ) ) {
					continue;
				}
			}
			if ( preg_match( '/(\d{4}\.\d{1,2})/', $url, $matches ) ) {
				if ( isset( $matches[ 0 ] ) ) {
					if ( $matches[ 0 ] > ( date( 'Y' ) + 2 ) || $matches[ 0 ] < ( date( 'Y' ) - 2 ) ) {
						continue;
					}
				}
			}
			if ( strstr( $url, 'favicon.ico' ) ) {
				continue;
			}
			if ( strstr( $url, '.css' ) ) {
				continue;
			}
			$rows[ ] = [ 'crid' => 'NULL', 'url' => $url, 'state' => 0 ];
//			if ( $multiLang && $langs ) {
//				foreach ( $langs as $lang ) {
//					if ( $lang != $language ) {
//						$url = preg_replace( '|(?<!:/)/' . $langs[ $language ] . '(/)?|', '/' . $lang . '\1', $url );
//						$url = str_replace( 'lang=' . $langs[ $language ], 'lang=' . $lang, $url );
//						$rows[ ] = array( 'crid' => 'NULL', 'url' => $url, 'state' => 0 );
//					}
//				}
//			}
		}
		if ( count( $rows ) ) {
			SPFactory::db()->insertArray( self::DB_TABLE, $rows, false, true );
		}
	}

	protected function getSites()
	{
		return SPFactory::db()
				->select( 'url', self::DB_TABLE, [ 'state' => 0 ] )
				->loadResultArray();
	}

	protected function parseResponse( $response )
	{
		if ( !( strlen( $response ) ) ) {
			return 204;
		}
		$links = [];
		if ( strlen( $response ) && strstr( $response, 'SobiPro' ) ) {
			// we need to limit the "explode" to two pieces only because otherwise
			// if the separator is used somewhere in the <body> it will be split into more pieces
			list( $header, $response ) = explode( "\r\n\r\n", $response, 2 );
			$header = explode( "\n", $header );
			$SobiPro = false;
			foreach ( $header as $line ) {
				if ( strstr( $line, 'SobiPro' ) ) {
					$line = explode( ':', $line );
					if ( trim( $line[ 0 ] ) == 'SobiPro' ) {
						$sid = trim( $line[ 1 ] );
						if ( $sid != Sobi::Section() ) {
							return 412;
						}
						else {
							$SobiPro = true;
						}
					}
				}
			}
			if ( !( $SobiPro ) ) {
				return 412;
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
						$link = explode( '#', $link );
						if ( strlen( $link[ 0 ] ) ) {
							$links[ $index ] = Sobi::FixPath( $host . '/' . $link[ 0 ] );
						}
						else {
							unset( $links[ $index ] );
						}
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
