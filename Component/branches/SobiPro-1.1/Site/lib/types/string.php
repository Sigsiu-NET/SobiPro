<?php
/**
 * @version: $Id: string.php 2347 2012-04-09 15:36:07Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-04-09 17:36:07 +0200 (Mon, 09 Apr 2012) $
 * $Revision: 2347 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/types/string.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 21-Jan-2009 1:35:29 PM
 */

class SPData_String
{
    protected $string = "";

    public function isEmpty()
    {

    }

    public function __construct( $string )
    {
        $this->string = $string;
    }

    public function & toLower()
    {
        if ( $this->isUnicode() ) {
            if ( function_exists( 'mb_strtolower' ) ) {
                self::_setUnicode();
                $this->string = mb_strtolower( $this->string );
            }
            else {
                Sobi::Error( 'String', 'String is a non-latin but we don\'t have unicode handler' );
            }
        }
        return $this;
    }

    private static function _setUnicode()
    {
        static $set = false;
        if ( !( $set ) ) {
            mb_internal_encoding( 'UTF-8' );
            $set = true;
        }
    }

    public function & toUpper()
    {
        if ( $this->isUnicode() ) {
            if ( function_exists( 'mb_strtoupper' ) ) {
                self::_setUnicode();
                $this->string = mb_strtoupper( $this->string );
            }
            else {
                Sobi::Error( 'String', 'String is a non-latin but we don\'t have unicode handler' );
            }
        }
        return $this;
    }

    public function __call( $method, $params )
    {
        if ( function_exists( $method ) ) {
            $this->string = $method( $this->string );
        }
        else {
            Sobi::Error( 'String', "Function {$method} does not exists", SPC::WARNING );
        }
        return $this;
    }

    protected function isUnicode()
    {
        return preg_match( '/^[\x20-\x7f]*$/D', $this->string ) ? false : true;
    }

    public function get()
    {
        return $this->string;
    }
}
