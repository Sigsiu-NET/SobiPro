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
 * @created 17-Jun-2010 12:35:23
 */

class SPInstaller extends SPObject
{
	/**
	 * @var string
	 */
	protected $type = null;
	/**
	 * @var string
	 */
	protected $xmlFile = null;
	/**
	 * @var string
	 */
	protected $root = null;
	/**
	 * @var DOMDocument
	 */
	protected $definition = null;
	/**
	 * @var DOMXPath
	 */
	protected $xdef = null;

	/**
	 * @param string $definition
	 * @param string $type
	 * @return SPInstaller
	 */
	public function __construct( $definition, $type = null )
	{
		$this->type = $type;
		$this->xmlFile = $definition;
		$this->definition = new DOMDocument( Sobi::Cfg( 'xml.version', '1.0' ), Sobi::Cfg( 'xml.encoding', 'UTF-8' ) );
		$this->definition->load( $this->xmlFile );
		$this->xdef = new DOMXPath( $this->definition );
		$this->root = dirname( $this->xmlFile );
	}

	protected function xGetString( $key )
	{
		$node = $this->xGetChilds( $key )->item( 0 );
		return isset( $node ) ? $node->nodeValue : null;
	}

	/**
	 * @param $key
	 * @return DOMNodeList
	 */
	protected function xGetChilds( $key )
	{
		return $this->xdef->query( "/{$this->type}/{$key}" );
	}

	public function validate()
	{
		$type = ( $this->type == 'SobiProApp' ) ? 'application' : $this->type;
		$schemaDef = SPLoader::path( 'lib.services.installers.schemas.' . $type, 'front', false, 'xsd' );
		$def = "https://xml.sigsiu.net/SobiPro/{$type}.xsd";
		if ( !( SPFs::exists( $schemaDef ) ) || ( time() - filemtime( $schemaDef ) > ( 60 * 60 * 24 * 7 ) ) ) {
			$connection = SPFactory::Instance( 'services.remote' );
			$connection->setOptions(
				[
					'url' => $def,
					'connecttimeout' => 10,
					'header' => false,
					'returntransfer' => true,
					'ssl_verifypeer' => false,
					'ssl_verifyhost' => 2,
				]
			);
			$schema =& SPFactory::Instance( 'base.fs.file', SPLoader::path( 'lib.services.installers.schemas.' . $type, 'front', false, 'xsd' ) );
			$file = $connection->exec();
			if ( !( strlen( $file ) ) ) {
				throw new SPException( SPLang::e( 'CANNOT_ACCESS_SCHEMA_DEF', $def ) );
			}
			$schema->content( $file );
			$schema->save();
			$schemaDef = $schema->filename();
		}
		if ( !( $this->definition->schemaValidate( $schemaDef ) ) ) {
			throw new SPException( SPLang::e( 'CANNOT_VALIDATE_SCHEMA_DEF_AT', str_replace( SOBI_ROOT , null, $this->xmlFile ), $def ) );
		}
	}
}

