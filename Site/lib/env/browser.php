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
 * Browser and OS ident
 * @author Radek Suski
 * @version 1.0
 * @created 21-Dec-2009 11:56:18
 */
final class SPBrowser
{
	private $client = [];

	private function __construct()
	{
		$this->client = $this->parse_user_agent( SPRequest::raw( 'HTTP_USER_AGENT', null, 'server' ) );
	}

	public function get( $property = null )
	{
		return ( $property && isset( $this->client[ $property ] ) ) ? $this->client[ $property ] : $this->client;
	}

	public static function & getInstance()
	{
		static $b = null;
		if ( !$b || !( $b instanceof self ) ) {
			$b = new self();
		}
		return $b;
	}

	/**
	 * Based on Web Browser Identifier v0.8
	 * @author Marcin Krol <hawk@limanowa.net>
	 * @license GPL
	 * @param $user_agent
	 * @return array
	 */
	private function parse_user_agent( $user_agent )
	{
		$client_data = [
				'system' => '',
				'system_icon' => '',
				'browser' => '',
				'browser_icon' => '',
				'type' => '',
				'humanity' => 100
		];
		$tmp_array = [];
		//
		// Check browsers
		//
		// Camino
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*camino\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Camino" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'camino';
			$client_data[ 'type' ] = 'normal';
		}

		// Netscape
		if ( preg_match( '/mozilla.*netscape[0-9]?\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Netscape" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'netscape';
			$client_data[ 'type' ] = 'normal';
		}

		if ( preg_match( '/mozilla.*navigator[0-9]?\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Netscape" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'netscape';
			$client_data[ 'type' ] = 'normal';
		}

		// Epiphany
		if ( preg_match( '/mozilla.*gecko\/[0-9]+.*epiphany\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Epiphany" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'epiphany';
			$client_data[ 'type' ] = 'normal';
		}

		// Galeon
		if ( preg_match( '/mozilla.*gecko\/[0-9]+.*galeon\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Galeon" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'galeon';
			$client_data[ 'type' ] = 'normal';
		}

		// Flock
		if ( preg_match( '/mozilla.*gecko\/[0-9]+.*flock\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Flock" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'flock';
			$client_data[ 'type' ] = 'normal';
		}

		// Minimo
		if ( preg_match( '/mozilla.*gecko\/[0-9]+.*minimo\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Minimo" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'mozilla';
			$client_data[ 'type' ] = 'normal';
		}

		// K-Meleon
		if ( preg_match( '/mozilla.*gecko\/[0-9]+.*k\-meleon\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "K-Meleon" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'k-meleon';
			$client_data[ 'type' ] = 'normal';
		}

		// K-Ninja
		if ( preg_match( '/mozilla.*gecko\/[0-9]+.*k-ninja\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "K-Ninja" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'k-ninja';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 85;
		}

		// Kazehakase
		if ( preg_match( '/mozilla.*gecko\/[0-9]+.*kazehakase\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Kazehakase" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'kazehakase';
			$client_data[ 'type' ] = 'normal';
		}

		// SeaMonkey
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*seamonkey\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "SeaMonkey" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'seamonkey';
			$client_data[ 'type' ] = 'normal';
		}

		// Iceape
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*iceape\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Iceape" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'iceape';
			$client_data[ 'type' ] = 'normal';
		}

		// Firefox
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*firefox\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Firefox" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'firefox';
			$client_data[ 'type' ] = 'normal';
		}

		// Iceweasel
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*iceweasel\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Iceweasel" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'iceweasel';
			$client_data[ 'type' ] = 'normal';
		}

		// Bon Echo
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*BonEcho\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Bon Echo" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'deerpark';
			$client_data[ 'type' ] = 'normal';
		}

		// Gran Paradiso
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*GranParadiso\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Gran Paradiso" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'deerpark';
			$client_data[ 'type' ] = 'normal';
		}

		// Shiretoko
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*Shiretoko\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Shiretoko" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'deerpark';
			$client_data[ 'type' ] = 'normal';
		}

		// Minefield
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*Minefield\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Minefield" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'minefield';
			$client_data[ 'type' ] = 'normal';
		}

		// Thunderbird
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*thunderbird\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Thunderbird" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'thunderbird';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 85;
		}

		// Icedove
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*icedove\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Icedove" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'icedove';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 85;
		}

		// Firebird
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*firebird\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Firebird" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'phoenix';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 85;
		}

		// Phoenix
		if ( preg_match( '/mozilla.*rv:[0-9\.]+.*gecko\/[0-9]+.*phoenix\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Phoenix" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'phoenix';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 85;
		}

		// Mozilla Suite
		if ( preg_match( '/mozilla.*rv:([0-9\.]+).*gecko\/[0-9]+.*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Mozilla";
			$client_data[ 'browser_icon' ] = 'mozilla';
			// Last official version was 1.7.13, drop all versions where second number > 7
			if ( (int)substr( $tmp_array[ 1 ], 2, 1 ) <= 7 ) {
				$client_data[ 'browser' ] .= ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
			else {
				$client_data[ 'browser' ] .= " compatible";
				$client_data[ 'humanity' ] = 85;
			}
			$client_data[ 'type' ] = 'normal';
		}

		// Konqueror
		if ( preg_match( '/mozilla.*konqueror\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Konqueror" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'konqueror';
			$client_data[ 'type' ] = 'normal';
			if ( preg_match( '/khtml\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) ) {
				$client_data[ 'browser' ] = "Konqueror" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// Opera
		if ( ( preg_match( '/mozilla.*opera ([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) || preg_match( '/^opera\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Opera" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'opera';
			$client_data[ 'type' ] = 'normal';
			if ( preg_match( '/opera mini/si', $user_agent ) ) {
				preg_match( '/opera mini\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array );
				$client_data[ 'browser' ] .= " (Opera Mini" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" ) . ")";
			}
		}

		// OmniWeb
		if ( preg_match( '/mozilla.*applewebkit\/[0-9]+.*omniweb\/v[0-9\.]+/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "OmniWeb";
			$client_data[ 'browser_icon' ] = 'omniweb';
			$client_data[ 'type' ] = 'normal';
		}

		// SunriseBrowser
		if ( preg_match( '/mozilla.*applewebkit\/[0-9]+.*sunrisebrowser\/([0-9a-z\+\-\.]+)/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "SunriseBrowser" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'sunrise';
			$client_data[ 'type' ] = 'normal';
		}

		// DeskBrowse
		if ( preg_match( '/mozilla.*applewebkit\/[0-9]+.*deskbrowse\/([0-9a-z\+\-\.]+)/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "DeskBrowse" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'deskbrowse';
			$client_data[ 'type' ] = 'normal';
		}

		// Shiira
		if ( preg_match( '/mozilla.*applewebkit.*shiira\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Shiira" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'shiira';
			$client_data[ 'type' ] = 'normal';
		}

		// Chrome
		if ( preg_match( '/mozilla.*applewebkit.*chrome\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Chrome" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'chrome';
			$client_data[ 'type' ] = 'normal';
		}

		// Safari (use version string if available)
		if ( preg_match( '/mozilla.*applewebkit.*version\/([0-9\.]+).*safari\/[0-9a-z\+\-\.]+/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Safari" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'safari';
			$client_data[ 'type' ] = 'normal';
		}

		// Safari (detect version using Safari build number)
		if ( preg_match( '/mozilla.*applewebkit.*safari\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Safari";
			$client_data[ 'browser_icon' ] = 'safari';
			$client_data[ 'type' ] = 'normal';

			// Translate Safari build into version number
			if ( $tmp_array[ 1 ] == "525.17" || $tmp_array[ 1 ] == "525.18" || $tmp_array[ 1 ] == "525.20" ) {
				$client_data[ 'browser' ] .= " 3.1.1";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 6 ) == "525.13" ) {
				$client_data[ 'browser' ] .= " 3.1";
			}
			elseif ( $tmp_array[ 1 ] == "523.10" || $tmp_array[ 1 ] == "523.12" || $tmp_array[ 1 ] == "523.12.9" || $tmp_array[ 1 ] == "523.15" ) {
				$client_data[ 'browser' ] .= " 3.0.4";
			}
			elseif ( $tmp_array[ 1 ] == "522.12.1" || $tmp_array[ 1 ] == "522.15.5" ) {
				$client_data[ 'browser' ] .= " 3.0.3";
			}
			elseif ( $tmp_array[ 1 ] == "522.12" || $tmp_array[ 1 ] == "522.13.1" ) {
				$client_data[ 'browser' ] .= " 3.0";
			}
			elseif ( $tmp_array[ 1 ] == "522.11.3" ) {
				$client_data[ 'browser' ] .= " 3.0 beta";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "419" ) {
				$client_data[ 'browser' ] .= " 2.0.4";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "417" ) {
				$client_data[ 'browser' ] .= " 2.0.3";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "416" ) {
				$client_data[ 'browser' ] .= " 2.0.2";
			}
			elseif ( $tmp_array[ 1 ] == "412.5" ) {
				$client_data[ 'browser' ] .= " 2.0.1";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "412" ) {
				$client_data[ 'browser' ] .= " 2.0";
			}
			elseif ( $tmp_array[ 1 ] == "312.6" || $tmp_array[ 1 ] == "312.5" ) {
				$client_data[ 'browser' ] .= " 1.3.2";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 5 ) == "312.3" ) {
				$client_data[ 'browser' ] .= " 1.3.1";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "312" ) {
				$client_data[ 'browser' ] .= " 1.3";
			}
			elseif ( $tmp_array[ 1 ] == "125.12" || $tmp_array[ 1 ] == "125.11" ) {
				$client_data[ 'browser' ] .= " 1.2.4";
			}
			elseif ( $tmp_array[ 1 ] == "125.9" ) {
				$client_data[ 'browser' ] .= " 1.2.3";
			}
			elseif ( $tmp_array[ 1 ] == "125.8" || $tmp_array[ 1 ] == "125.7" ) {
				$client_data[ 'browser' ] .= " 1.2.2";
			}
			elseif ( $tmp_array[ 1 ] == "125.1" ) {
				$client_data[ 'browser' ] .= " 1.2.1";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "125" ) {
				$client_data[ 'browser' ] .= " 1.2";
			}
			elseif ( $tmp_array[ 1 ] == "101.1" ) {
				$client_data[ 'browser' ] .= " 1.1.1";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "100" ) {
				$client_data[ 'browser' ] .= " 1.1";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 4 ) == "85.8" ) {
				$client_data[ 'browser' ] .= " 1.0.3";
			}
			elseif ( $tmp_array[ 1 ] == "85.7" ) {
				$client_data[ 'browser' ] .= " 1.0.2";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 2 ) == "85" ) {
				$client_data[ 'browser' ] .= " 1.0";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 2 ) == "74" ) {
				$client_data[ 'browser' ] .= " 1.0b2";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 2 ) == "73" ) {
				$client_data[ 'browser' ] .= " 0.9";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 2 ) == "60" ) {
				$client_data[ 'browser' ] .= " 0.8.2";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 2 ) == "51" ) {
				$client_data[ 'browser' ] .= " 0.8.1";
			}
			elseif ( substr( $tmp_array[ 1 ], 0, 2 ) == "48" ) {
				$client_data[ 'browser' ] .= " 0.8";
			}
		}

		// Dillo
		if ( preg_match( '/dillo\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Dillo" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'dillo';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 60;
		}

		// iCab
		if ( preg_match( '/icab\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "iCab" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'icab';
			$client_data[ 'type' ] = 'normal';
		}

		// Lynx
		if ( preg_match( '/^lynx\/([0-9a-z\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Lynx" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'lynx';
			$client_data[ 'type' ] = 'text';
			$client_data[ 'humanity' ] = 5;
		}

		// Links
		if ( preg_match( '/^links \(([0-9a-z\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Links" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'links';
			$client_data[ 'type' ] = 'text';
			$client_data[ 'humanity' ] = 5;
		}

		// Elinks
		if ( preg_match( '/^elinks \(([0-9a-z\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "ELinks" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'links';
			$client_data[ 'type' ] = 'text';
			$client_data[ 'humanity' ] = 5;
		}
		if ( preg_match( '/^elinks\/([0-9a-z\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "ELinks" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'links';
			$client_data[ 'type' ] = 'text';
			$client_data[ 'humanity' ] = 5;
		}
		if ( preg_match( '/^elinks$/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "ELinks";
			$client_data[ 'browser_icon' ] = 'links';
			$client_data[ 'type' ] = 'text';
			$client_data[ 'humanity' ] = 5;
		}

		// Wget
		if ( preg_match( '/^Wget\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Wget" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'wget';
			$client_data[ 'type' ] = 'text';
			$client_data[ 'humanity' ] = 5;
		}

		// Amiga Aweb
		if ( preg_match( '/Amiga\-Aweb\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Amiga Aweb" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'aweb';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 50;
		}

		// Amiga Voyager
		if ( preg_match( '/AmigaVoyager\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Amiga Voyager" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'voyager';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 50;
		}

		// QNX Voyager
		if ( preg_match( '/QNX Voyager ([0-9a-z.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "QNX Voyager" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'qnx';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 50;
		}

		// IBrowse
		if ( preg_match( '/IBrowse\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "IBrowse" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'ibrowse';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 50;
		}

		// Openwave
		if ( preg_match( '/UP\.Browser\/([0-9a-zA-Z\.]+).*/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Openwave" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'openwave';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 50;
		}
		if ( preg_match( '/UP\/([0-9a-zA-Z\.]+).*/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Openwave" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'openwave';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 50;
		}

		// NetFront
		if ( preg_match( '/NetFront\/([0-9a-z\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "NetFront" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'netfront';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 50;
		}

		// PlayStation Portable
		if ( preg_match( '/psp.*playstation.*portable[^0-9]*([0-9a-z\.]+)\)/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "PSP" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'playstation';
			$client_data[ 'type' ] = 'normal';
		}

		// Various robots...

		// Googlebot
		if ( preg_match( '/Googlebot\/([0-9a-z\+\-\.]+).*/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Googlebot" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// Googlebot Image
		if ( preg_match( '/Googlebot\-Image\/([0-9a-z\+\-\.]+).*/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Googlebot Image " . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// Gigabot
		if ( preg_match( '/Gigabot\/([0-9a-z\+\-\.]+).*/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Gigabot" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// W3C Validator
		if ( preg_match( '/^W3C_Validator\/([0-9a-z\+\-\.]+)$/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "W3C Validator" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// W3C CSS Validator
		if ( preg_match( '/W3C_CSS_Validator_[a-z]+\/([0-9a-z\+\-\.]+)$/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "W3C CSS Validator" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// MSN Bot
		if ( preg_match( '/msnbot(-media|)\/([0-9a-z\+\-\.]+).*/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "MSNBot" . ( $tmp_array[ 2 ] ? " " . $tmp_array[ 2 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// Psbot
		if ( preg_match( '/psbot\/([0-9a-z\+\-\.]+).*/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Psbot" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// IRL crawler
		if ( preg_match( '/IRLbot\/([0-9a-z\+\-\.]+).*/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "IRL crawler" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// Seekbot
		if ( preg_match( '/Seekbot\/([0-9a-z\+\-\.]+).*/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Seekport Robot" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// Microsoft Research Bot
		if ( preg_match( '/^MSRBOT /s', $user_agent ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "MSRBot";
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// cfetch/voyager
		if ( preg_match( '/^(cfetch|voyager)\/([0-9a-z\+\-\.]+)$/s', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "voyager" . ( $tmp_array[ 2 ] ? " " . $tmp_array[ 2 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// BecomeBot
		if ( preg_match( '/BecomeBot\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "BecomeBot" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// SnapBot
		if ( preg_match( '/SnapBot\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "SnapBot" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// Yeti
		if ( preg_match( '/Yeti\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Yeti" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// Twiceler
		if ( preg_match( '/Twiceler-([0-9\.]+) http:\/\/www.cuill.com/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Twiceler" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// Alexa
		if ( preg_match( '/^ia_archiver$/s', $user_agent ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Alexa";
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// Inktomi Slurp
		if ( preg_match( '/Slurp.*inktomi/s', $user_agent ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Inktomi Slurp";
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// Yahoo Slurp
		if ( preg_match( '/Yahoo!.*Slurp/s', $user_agent ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Yahoo! Slurp";
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// Ask.com
		if ( preg_match( '/Ask Jeeves\/Teoma/s', $user_agent ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Ask.com";
			$client_data[ 'browser_icon' ] = 'robot';
			$client_data[ 'type' ] = 'bot';
			$client_data[ 'humanity' ] = 0;
		}

		// end of robots

		// MSIE
		// Sun, Jan 18, 2015 21:09:32
		if ( preg_match( '/microsoft.*internet.*explorer/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Microsoft Internet Explorer 1.0";
			$client_data[ 'browser_icon' ] = 'msie';
			$client_data[ 'type' ] = 'normal';
		}

		if ( preg_match( '/Trident/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Microsoft Internet Explorer 11";
			$client_data[ 'browser_icon' ] = 'msie';
			$client_data[ 'type' ] = 'normal';
			$client_data[ 'humanity' ] = 130;
		}

		if ( preg_match( '/mozilla.*MSIE ([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Microsoft Internet Explorer" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'msie';
			$client_data[ 'type' ] = 'normal';
		}

		// Netscape Navigator
		if ( preg_match( '/Mozilla\/([0-4][0-9\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Netscape Navigator" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'browser_icon' ] = 'netscape_old';
			$client_data[ 'humanity' ] = 30;
		}

		// Catchall for other Mozilla compatible browsers
		if ( preg_match( '/mozilla/si', $user_agent, $tmp_array ) && !$client_data[ 'browser' ] ) {
			$client_data[ 'browser' ] = "Mozilla compatible";
			$client_data[ 'browser_icon' ] = 'mozilla';
			$client_data[ 'humanity' ] = 75;
		}

		//
		// Check system
		//

		// Linux
		if ( preg_match( '/linux/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Linux";
			$client_data[ 'system_icon' ] = "linux";
			if ( preg_match( '/mdk/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " (Mandrake)";
				$client_data[ 'system_icon' ] = "mandrake";
			}
			elseif ( preg_match( '/kanotix/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " (Kanotix)";
				$client_data[ 'system_icon' ] = "kanotix";
			}
			elseif ( preg_match( '/lycoris/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " (Lycoris)";
				$client_data[ 'system_icon' ] = "lycoris";
			}
			elseif ( preg_match( '/knoppix/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " (Knoppix)";
				$client_data[ 'system_icon' ] = "knoppix";
			}
			elseif ( preg_match( '/centos/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " (CentOS)";
				$client_data[ 'system_icon' ] = "centos";
			}
			elseif ( preg_match( '/gentoo/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " (Gentoo)";
				$client_data[ 'system_icon' ] = "gentoo";
			}
			elseif ( preg_match( '/fedora/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " (Fedora)";
				$client_data[ 'system_icon' ] = "fedora";
			}
			elseif ( preg_match( '/ubuntu/si', $user_agent ) ) {
				// Which *ubuntu do we have?
				if ( preg_match( '/kubuntu/si', $user_agent ) ) {
					$client_data[ 'system' ] .= " (Kubuntu";
					$client_data[ 'system_icon' ] = "kubuntu";
				}
				elseif ( preg_match( '/xubuntu/si', $user_agent ) ) {
					$client_data[ 'system' ] .= " (Xubuntu";
					$client_data[ 'system_icon' ] = "xubuntu";
				}
				else {
					$client_data[ 'system' ] .= " (Ubuntu";
					$client_data[ 'system_icon' ] = "ubuntu";
				}
				// Try to detect version
				if ( preg_match( '/intrepid/si', $user_agent ) ) {
					$client_data[ 'system' ] .= " 8.10 Intrepid)";
				}
				elseif ( preg_match( '/hardy/si', $user_agent ) ) {
					$client_data[ 'system' ] .= " 8.04 LTS Hardy Heron)";
				}
				elseif ( preg_match( '/gutsy/si', $user_agent ) ) {
					$client_data[ 'system' ] .= " 7.10 Gutsy Gibbon)";
				}
				elseif ( preg_match( '/ubuntu.feist/si', $user_agent ) ) {
					$client_data[ 'system' ] .= " 7.04 Feisty Fawn)";
				}
				elseif ( preg_match( '/ubuntu.edgy/si', $user_agent ) ) {
					$client_data[ 'system' ] .= " 6.10 Edgy Eft)";
				}
				elseif ( preg_match( '/ubuntu.dapper/si', $user_agent ) ) {
					$client_data[ 'system' ] .= " 6.06 LTS Dapper Drake)";
				}
				elseif ( preg_match( '/ubuntu.breezy/si', $user_agent ) ) {
					$client_data[ 'system' ] .= " 5.10 Breezy Badger)";
				}
				else {
					$client_data[ 'system' ] .= ")";
				}
			}
			elseif ( preg_match( '/slackware/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " (Slackware)";
				$client_data[ 'system_icon' ] = "slackware";
			}
			elseif ( preg_match( '/suse/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " (Suse)";
				$client_data[ 'system_icon' ] = "suse";
			}
			elseif ( preg_match( '/redhat/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " (Redhat)";
				$client_data[ 'system_icon' ] = "redhat";
			}
			elseif ( preg_match( '/debian/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " (Debian)";
				$client_data[ 'system_icon' ] = "debian";
			}
			elseif ( preg_match( '/PLD\/([0-9.]*) \(([a-z]{2})\)/si', $user_agent, $tmp_array ) ) {
				$client_data[ 'system' ] .= " (PLD" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" ) . ( $tmp_array[ 2 ] ? " " . $tmp_array[ 2 ] : "" ) . ")";
				$client_data[ 'system_icon' ] = "pld";
			}
			elseif ( preg_match( '/PLD\/([a-zA-Z.]*)/si', $user_agent, $tmp_array ) ) {
				$client_data[ 'system' ] .= " (PLD" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" ) . ")";
				$client_data[ 'system_icon' ] = "pld";
			}
		}

		// BSD
		if ( preg_match( '/bsd/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "BSD";
			$client_data[ 'system_icon' ] = "bsd";
			if ( preg_match( '/freebsd/si', $user_agent ) ) {
				$client_data[ 'system' ] = "FreeBSD";
			}
			elseif ( preg_match( '/openbsd/si', $user_agent ) ) {
				$client_data[ 'system' ] = "OpenBSD";
			}
			elseif ( preg_match( '/netbsd/si', $user_agent ) ) {
				$client_data[ 'system' ] = "NetBSD";
			}
		}

		// Mac OS (X)
		if ( ( preg_match( '/mac_/si', $user_agent ) || preg_match( '/macos/si', $user_agent ) || preg_match( '/powerpc/si', $user_agent ) || preg_match( '/mac os/si', $user_agent ) || preg_match( '/68k/si', $user_agent ) || preg_match( '/macintosh/si', $user_agent ) ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Mac OS";
			$client_data[ 'system_icon' ] = "macos";
			if ( preg_match( '/mac os x/si', $user_agent ) ) {
				$client_data[ 'system' ] .= " X";

				// use version string if available
				if ( preg_match( '/mac os x ([0-9\._]+)/si', $user_agent, $tmp_array ) ) {
					$client_data[ 'system' ] .= ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
					$client_data[ 'system' ] = preg_replace( '/_/', '.', $client_data[ 'system' ] );
				}
				// if browser == safari try to detect Mac OS X version using WebKit and Safari build numbers
				elseif ( preg_match( '/applewebkit\/([0-9\.]+).*safari\/([0-9\.]+)/si', $user_agent, $tmp_array ) ) {
					if ( $tmp_array[ 1 ] == "523.10.3" ) {
						$client_data[ 'system' ] .= " 10.5/10.5.1";
					}
					elseif ( $tmp_array[ 1 ] == "419.3" ) {
						$client_data[ 'system' ] .= " 10.4.10";
					}
					elseif ( $tmp_array[ 1 ] == "419.2.1" ) {
						$client_data[ 'system' ] .= " 10.4.9/10.4.10";
					}
					elseif ( $tmp_array[ 1 ] == "419" ) {
						$client_data[ 'system' ] .= " 10.4.9";
					}
					elseif ( $tmp_array[ 1 ] == "418.9.1" ) {
						$client_data[ 'system' ] .= " 10.4.8";
					}
					elseif ( $tmp_array[ 1 ] == "418.9" ) {
						$client_data[ 'system' ] .= " 10.4.8";
					}
					elseif ( $tmp_array[ 1 ] == "418.8" ) {
						$client_data[ 'system' ] .= " 10.4.7";
					}
					elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "418" ) {
						$client_data[ 'system' ] .= " 10.4.6";
					}
					elseif ( $tmp_array[ 1 ] == "417.9" ) {
						$client_data[ 'system' ] .= " 10.4.4/10.4.5";
					}
					elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "416" ) {
						$client_data[ 'system' ] .= " 10.4.3";
					}
					elseif ( substr( $tmp_array[ 1 ], 0, 4 ) == "412." ) {
						$client_data[ 'system' ] .= " 10.4.2";
					}
					elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "412" ) {
						$client_data[ 'system' ] .= " 10.4/10.4.1";
					}
					elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "312" ) {
						$client_data[ 'system' ] .= " 10.3.9";
					}
					elseif ( $tmp_array[ 1 ] == "125.5.7" ) {
						$client_data[ 'system' ] .= " 10.3.8";
					}
					elseif ( $tmp_array[ 1 ] == "125.5.5" && $tmp_array[ 2 ] == "125.11" ) {
						$client_data[ 'system' ] .= " 10.3.6";
					}
					elseif ( ( $tmp_array[ 1 ] == "125.5.6" || $tmp_array[ 1 ] == "125.5.5" ) && substr( $tmp_array[ 2 ], 0, 5 ) == "125.1" ) {
						$client_data[ 'system' ] .= " 10.3.6/10.3.7/10.3.8";
					}
					elseif ( $tmp_array[ 1 ] == "125.5" || $tmp_array[ 1 ] == "125.4" ) {
						$client_data[ 'system' ] .= " 10.3.5";
					}
					elseif ( $tmp_array[ 1 ] == "125.2" ) {
						$client_data[ 'system' ] .= " 10.3.4";
					}
					elseif ( $tmp_array[ 1 ] == "100" && $tmp_array[ 2 ] == "100.1" ) {
						$client_data[ 'system' ] .= " 10.3.2";
					}
					elseif ( substr( $tmp_array[ 1 ], 0, 3 ) == "100" ) {
						$client_data[ 'system' ] .= " 10.3";
					}
					elseif ( substr( $tmp_array[ 1 ], 0, 2 ) == "85" ) {
						$client_data[ 'system' ] .= " 10.2.8";
					}
				}
			}
		}

		// ReactOS
		if ( preg_match( '/ReactOS ([0-9a-zA-Z\+\-\. ]+).*/s', $user_agent, $tmp_array ) ) {
			$client_data[ 'system' ] = "ReactOS" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'system_icon' ] = "reactos";
			$client_data[ 'humanity' ] -= 10;
		}

		// SunOs
		if ( preg_match( '/sunos/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Solaris";
			$client_data[ 'system_icon' ] = "solaris";
			$client_data[ 'humanity' ] -= 10;
		}

		// Amiga
		if ( preg_match( '/amiga/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Amiga";
			$client_data[ 'system_icon' ] = "amiga";
			$client_data[ 'humanity' ] -= 10;
		}

		// Irix
		if ( preg_match( '/irix/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "IRIX";
			$client_data[ 'system_icon' ] = "irix";
			$client_data[ 'humanity' ] -= 10;
		}

		// OpenVMS
		if ( preg_match( '/open.*vms/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "OpenVMS";
			$client_data[ 'system_icon' ] = "openvms";
		}

		// BeOs
		if ( preg_match( '/beos/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "BeOS";
			$client_data[ 'system_icon' ] = "beos";
		}

		// QNX
		if ( preg_match( '/QNX/si', $user_agent ) && preg_match( '/Photon/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "QNX";
			$client_data[ 'system_icon' ] = "qnx";
		}

		// OS/2 Warp
		if ( preg_match( '/OS\/2.*Warp ([0-9.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "OS/2 Warp" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'system_icon' ] = "os2";
			$client_data[ 'humanity' ] -= 10;
		}

		// Java on mobile
		if ( preg_match( '/j2me/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Java Platform Micro Edition";
			$client_data[ 'system_icon' ] = "java";
		}

		// Symbian Os
		if ( preg_match( '/symbian/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Symbian OS";
			$client_data[ 'system_icon' ] = "symbian";
			// try to get version
			if ( preg_match( '/SymbianOS\/([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) ) {
				$client_data[ 'system' ] .= ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// Palm Os
		if ( preg_match( '/palmos/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Palm OS";
			$client_data[ 'system_icon' ] = "palmos";
			// try to get version
			if ( preg_match( '/PalmOS ([0-9a-z\+\-\.]+).*/si', $user_agent, $tmp_array ) ) {
				$client_data[ 'system' ] .= ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// PlayStation Portable
		if ( preg_match( '/psp.*playstation.*portable/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "PlayStation Portable";
			$client_data[ 'system_icon' ] = 'playstation';
		}

		// Nintentdo Wii
		if ( preg_match( '/Nintendo Wii/si', $user_agent ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Nintendo Wii";
			$client_data[ 'system_icon' ] = 'wii';
		}

		// Try to detect some mobile devices...

		// Nokia
		if ( preg_match( '/Nokia[ ]{0,1}([0-9a-zA-Z\+\-\.]+){0,1}.*/s', $user_agent, $tmp_array ) ) {
			if ( !$client_data[ 'system' ] ) {
				$client_data[ 'system' ] = "Nokia" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "mobile";
			}
			else {
				$client_data[ 'system' ] .= " / Nokia" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// Motorola
		if ( preg_match( '/mot\-([0-9a-zA-Z\+\-\.]+){0,1}\//si', $user_agent, $tmp_array ) ) {
			if ( !$client_data[ 'system' ] ) {
				$client_data[ 'system' ] = "Motorola" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "mobile";
			}
			else {
				$client_data[ 'system' ] .= " / Motorola" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// Siemens
		if ( preg_match( '/sie\-([0-9a-zA-Z\+\-\.]+){0,1}\//si', $user_agent, $tmp_array ) ) {
			if ( !$client_data[ 'system' ] ) {
				$client_data[ 'system' ] = "Siemens" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "mobile";
			}
			else {
				$client_data[ 'system' ] .= " / Siemens" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// Samsung
		if ( preg_match( '/samsung\-([0-9a-zA-Z\+\-\.]+){0,1}\//si', $user_agent, $tmp_array ) ) {
			if ( !$client_data[ 'system' ] ) {
				$client_data[ 'system' ] = "Samsung" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "mobile";
			}
			else {
				$client_data[ 'system' ] .= " / Samsung" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// SonyEricsson & Ericsson
		if ( preg_match( '/SonyEricsson[ ]{0,1}([0-9a-zA-Z\+\-\.]+){0,1}.*/s', $user_agent, $tmp_array ) ) {
			if ( !$client_data[ 'system' ] ) {
				$client_data[ 'system' ] = "Sony Ericsson" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "mobile";
			}
			else {
				$client_data[ 'system' ] .= " / Sony Ericsson" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}
		elseif ( preg_match( '/Ericsson[ ]{0,1}([0-9a-zA-Z\+\-\.]+){0,1}.*/s', $user_agent, $tmp_array ) ) {
			if ( !$client_data[ 'system' ] ) {
				$client_data[ 'system' ] = "Ericsson" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "mobile";
			}
			else {
				$client_data[ 'system' ] .= " / Ericsson" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// Alcatel
		if ( preg_match( '/Alcatel\-([0-9a-zA-Z\+\-\.]+){0,1}.*/s', $user_agent, $tmp_array ) ) {
			if ( !$client_data[ 'system' ] ) {
				$client_data[ 'system' ] = "Alcatel" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "mobile";
			}
			else {
				$client_data[ 'system' ] .= " / Alcatel" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// Panasonic
		if ( preg_match( '/Panasonic\-{0,1}([0-9a-zA-Z\+\-\.]+){0,1}.*/s', $user_agent, $tmp_array ) ) {
			if ( !$client_data[ 'system' ] ) {
				$client_data[ 'system' ] = "Panasonic" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "mobile";
			}
			else {
				$client_data[ 'system' ] .= " / Panasonic" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// Philips
		if ( preg_match( '/Philips\-([0-9a-z\+\-\@\.]+){0,1}.*/si', $user_agent, $tmp_array ) ) {
			if ( !$client_data[ 'system' ] ) {
				$client_data[ 'system' ] = "Philips" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "mobile";
			}
			else {
				$client_data[ 'system' ] .= " / Philips" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// Acer
		if ( preg_match( '/Acer\-([0-9a-z\+\-\.]+){0,1}.*/si', $user_agent, $tmp_array ) ) {
			if ( !$client_data[ 'system' ] ) {
				$client_data[ 'system' ] = "Acer" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "mobile";
			}
			else {
				$client_data[ 'system' ] .= " / Acer" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// BlackBerry
		if ( preg_match( '/BlackBerry([0-9a-zA-Z\+\-\.]+){0,1}\//s', $user_agent, $tmp_array ) ) {
			if ( !$client_data[ 'system' ] ) {
				$client_data[ 'system' ] = "BlackBerry" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "mobile";
			}
			else {
				$client_data[ 'system' ] .= " / BlackBerry" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			}
		}

		// Windows 3.x, 95, 98 and other numerical
		if ( preg_match( '/windows ([0-9\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Windows" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
			$client_data[ 'system_icon' ] = "win_old";
			$client_data[ 'humanity' ] -= 20;
		}

		if ( preg_match( '/[ \(]win([0-9\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'system' ] ) {
			if ( $tmp_array[ 1 ] == "16" ) {
				$client_data[ 'system' ] = "Windows 3.x";
				$client_data[ 'system_icon' ] = "win_old";
				$client_data[ 'humanity' ] -= 40;
			}
			elseif ( $tmp_array[ 1 ] == "32" ) {
				$client_data[ 'system' ] = "Windows";
				$client_data[ 'system_icon' ] = "win_old";
				$client_data[ 'humanity' ] -= 20;
			}
			else {
				$client_data[ 'system' ] = "Windows" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "win_old";
				$client_data[ 'humanity' ] -= 20;
			}
		}

		// Windows ME
		if ( preg_match( '/windows me/si', $user_agent, $tmp_array ) || preg_match( '/win 9x 4\.90/si', $user_agent, $tmp_array ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Windows Millenium";
			$client_data[ 'system_icon' ] = "win_old";
			$client_data[ 'humanity' ] -= 20;
		}

		// Windows CE
		if ( preg_match( '/windows ce/si', $user_agent, $tmp_array ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Windows CE";
			$client_data[ 'system_icon' ] = "win_old";
		}

		// Windows XP
		if ( preg_match( '/windows xp/si', $user_agent, $tmp_array ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Windows XP";
			$client_data[ 'system_icon' ] = "win_new";
		}

		// Windows NT, no version, to be sure it will catch
		if ( preg_match( '/windows nt/si', $user_agent, $tmp_array ) || preg_match( '/winnt/si', $user_agent, $tmp_array ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Windows NT";
			$client_data[ 'system_icon' ] = "win_old";
			$client_data[ 'humanity' ] -= 40;
		}

		// Windows NT with version
		if ( preg_match( '/windows nt ([0-9\.]+).*/si', $user_agent, $tmp_array ) || preg_match( '/winnt([0-9\.]+).*/si', $user_agent, $tmp_array ) && !$client_data[ 'system' ] ) {
			if ( $tmp_array[ 1 ] == "6.1" ) {
				$client_data[ 'system' ] = "Windows 7/Server 2008 R2";
				$client_data[ 'system_icon' ] = "win_new";
				$client_data[ 'humanity' ] += 40;
			}
			elseif ( $tmp_array[ 1 ] == "6.0" ) {
				$client_data[ 'system' ] = "Windows Vista/Server 2008";
				$client_data[ 'system_icon' ] = "win_new";
				$client_data[ 'humanity' ] += 40;
			}
			elseif ( $tmp_array[ 1 ] == "10.0" ) {
				$client_data[ 'system' ] = "Windows 10";
				$client_data[ 'system_icon' ] = "win_new";
				$client_data[ 'humanity' ] += 40;
			}
			elseif ( $tmp_array[ 1 ] == "5.2" ) {
				$client_data[ 'system' ] = "Windows Server Home/Server 2003";
				$client_data[ 'system_icon' ] = "win_new";
				$client_data[ 'humanity' ] += 40;
			}
			elseif ( $tmp_array[ 1 ] == "5.1" ) {
				$client_data[ 'system' ] = "Windows XP";
				$client_data[ 'system_icon' ] = "win_new";
				$client_data[ 'humanity' ] += 40;
			}
			elseif ( $tmp_array[ 1 ] == "5.0" || $tmp_array[ 1 ] == "5.01" ) {
				$client_data[ 'system' ] = "Windows 2000";
				$client_data[ 'system_icon' ] = "win_old";
				$client_data[ 'humanity' ] += 40;
			}
			else {
				$client_data[ 'system' ] = "Windows NT" . ( $tmp_array[ 1 ] ? " " . $tmp_array[ 1 ] : "" );
				$client_data[ 'system_icon' ] = "win_old";
			}
		}
		// Catchall for all other windozez
		if ( preg_match( '/windows/si', $user_agent, $tmp_array ) && !$client_data[ 'system' ] ) {
			$client_data[ 'system' ] = "Windows";
			$client_data[ 'system_icon' ] = "win_old";
			$client_data[ 'humanity' ] -= 10;
		}
		return $client_data;
	}
}
