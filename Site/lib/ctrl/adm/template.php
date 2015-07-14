<?php
/**
 * @version: $Id$
 * @package: SobiPro Library
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
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

SPLoader::loadController( 'config', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jun-2010 15:16:47
 */
class SPTemplateCtrl extends SPConfigAdmCtrl
{
	/**
	 * @var string
	 */
	protected $_type = 'template';
	/**
	 * @var string
	 */
	protected $_defTask = 'edit';

	/**
	 */
	public function __construct()
	{
		if ( !Sobi::Can( 'template.manage' ) ) {
			Sobi::Error( $this->name(), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
			exit();
		}
	}

	/**
	 */
	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'edit':
				$this->editFile();
				Sobi::ReturnPoint();
				break;
			case 'save':
			case 'saveAs':
				$this->save( $this->_task == 'saveAs' );
				break;
			case 'info':
				$this->info();
				break;
			case 'deleteFile':
				$this->deleteFile();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'compile':
				$this->compile();
				break;
			case 'clone':
				$this->cloneTpl();
				break;
			case 'list':
				$this->getTemplateFiles();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !( parent::execute() ) ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				else {
					$r = true;
				}
				break;
		}
	}

	protected function compile()
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$file = $this->file( SPRequest::cmd( 'fileName' ) );
		$output = str_replace( 'less', 'css', $file );
		Sobi::Trigger( 'BeforeCompileLess', $this->name(), array( &$file ) );
		$u = array( 'task' => 'template.edit', 'file' => SPRequest::cmd( 'fileName' ) );
		if ( Sobi::Section() ) {
			$u[ 'sid' ] = Sobi::Section();
		}
		if ( !( $file ) ) {
			$this->response( Sobi::Url( $u ), SPLang::e( 'Missing file to compile %s', SPRequest::cmd( 'fileName' ) ), false, SPC::ERROR_MSG );
		}
		include( 'phar://' . SOBI_PATH . '/lib/services/third-party/less/less.phar.tar.gz/Autoloader.php' );
		try {
			Less_Autoloader::register();
			$parser = new Less_Parser();
			$parser->parseFile( $file, SPFactory::config()->get( 'live_site' ) );
			$css = $parser->getCss();
			if ( SPFs::exists( $output ) ) {
				SPFs::delete( $output );
			}
			SPFs::write( $output, $css );
		} catch ( Exception $x ) {
			$this->response( Sobi::Url( $u ), SPLang::e( 'TP.LESS_FILE_NOT_COMPILED', $x->getMessage() ), false, SPC::ERROR_MSG );
		}
		$this->response( Sobi::Url( $u ), Sobi::Txt( 'TP.LESS_FILE_COMPILED', str_replace( SOBI_PATH, null, $output ) ), false, SPC::SUCCESS_MSG );
	}

	protected function getTemplateFiles()
	{
		$type = SPRequest::cmd( 'type', null, 'post' );
		if ( strstr( $type, '.' ) ) {
			$type = explode( '.', $type );
			$type = $type[ 0 ];
		}
		$directory = $this->dir( Sobi::Cfg( 'section.template' ) );
		$directory = Sobi::FixPath( $directory . '/' . $type );
		if ( file_exists( $directory ) ) {
			$files = scandir( $directory );
			if ( count( $files ) ) {
				foreach ( $files as $file ) {
					$stack = explode( '.', $file );
					if ( array_pop( $stack ) == 'xsl' ) {
						$arr[ ] = array( 'name' => $stack[ 0 ], 'filename' => $file );
					}
				}
			}
		}
		Sobi::Trigger( 'List', 'Templates', array( &$arr ) );
		SPFactory::mainframe()->cleanBuffer();
		echo json_encode( $arr );
		exit;
	}

//	protected function deleteFile()
//	{
//		if( !( SPFactory::mainframe()->checkToken() ) ) {
//			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
//		}
//		$file = $this->file( SPRequest::cmd( 'sp_fedit' ) );
//		Sobi::Trigger( 'Delete', $this->name(), array( &$content, &$file ) );
//		if( !$file ) {
//			throw new SPException( SPLang::e( 'Missing  file to delete %s', SPRequest::cmd( 'sp_fedit' ) ) );
//		}
//		$fClass = SPLoader::loadClass( 'base.fs.file' );
//		$File = new $fClass( $file );
//		if( $File->delete() ) {
//			$u = array( 'task' => 'template.edit', 'file' => 'template.xml' );
//			if( SPRequest::sid() ) {
//				$u[ 'sid' ] = SPRequest::sid();
//			}
//			Sobi::Redirect( Sobi::Url( $u ), 'File has been deleted' );
//		}
//		else {
//			Sobi::Redirect( SPMainFrame::getBack(), 'Cannot delete the file', SPC::ERROR_MSG );
//		}
//	}

	private function delete()
	{
		$dir = $this->dir( SPRequest::cmd( 'templateName' ) );
		if ( SPRequest::cmd( 'templateName' ) == SPC::DEFAULT_TEMPLATE ) {
			$this->response( Sobi::Url( 'template.info' ), Sobi::Txt( 'TP.DO_NOT_REMOVE' ), true, 'error' );
		}
		if ( $dir && SPFs::delete( $dir ) ) {
			$this->response( Sobi::Url( array( 'task' => 'config.general' ) ), Sobi::Txt( 'TP.REMOVED' ), false, 'success' );
		}
		else {
			$this->response( Sobi::Back(), Sobi::Txt( 'TP.CANNOT_REMOVE' ), false, 'error' );
		}
	}

	private function cloneTpl()
	{
		$dir = $this->dir( SPRequest::cmd( 'templateName' ) );
		$newName = SPRequest::word( 'templateNewName', 'Duplicated Template', 'post' );
		$dirName = SPLang::nid( $newName );
		$dirNameOrg = $dirName;
		$c = 1;
		while ( SPFs::exists( SPLoader::dirPath( 'usr.templates.' . $dirName, 'front', false ) ) ) {
			$dirName = $dirNameOrg . '-' . $c++;
		}
		$newPath = SPLoader::dirPath( 'usr.templates.' . $dirName, 'front', false );
		if ( !( SPFs::copy( $dir, $newPath ) ) ) {
			throw new SPException( SPLang::e( 'COULD_NOT_COPY_DIRECTORY', $dir, $newPath ) );
		}
		$defFile = SPLoader::path( $newPath . '.template', 'absolute', true, 'xml' );
		if ( $defFile ) {
			$fc = SPLoader::loadClass( 'base.fs.file' );
			$def = new DOMDocument();
			$def->load( $defFile );
			$xdef = new DOMXPath( $def );
			$oldName = $xdef->query( '/template/name' )->item( 0 )->nodeValue;
			$oldDesc = $xdef->query( '/template/description' )->item( 0 )->nodeValue;
			$date = SPFactory::config()->date( time() );
			$xdef->query( '/template/name' )->item( 0 )->nodeValue = $newName;
			$xdef->query( '/template/creationDate' )->item( 0 )->nodeValue = $date;
			$xdef->query( '/template/id' )->item( 0 )->nodeValue = $dirName;
			$newDesc = Sobi::Txt( 'TP.CLONE_NOTE', array( 'name' => $oldName, 'date' => $date ) );
			$xdef->query( '/template/description' )->item( 0 )->nodeValue = "{$newDesc}\n{$oldDesc}";
			$file = new $fc( $defFile );
			$file->content( $def->saveXML() );
			$file->save();
		}
		$this->response( Sobi::Url( array( 'task' => 'template.info', 'template' => str_replace( SOBI_PATH . DS . 'usr' . DS . 'templates' . DS, null, $dirName ) ) ), Sobi::Txt( 'TP.DUPLICATED' ), false, 'success' );
	}

	private function info()
	{
		$templateName = SPRequest::cmd( 'template' );
		if ( !( strlen( $templateName ) ) ) {
			$templateName = SPC::DEFAULT_TEMPLATE;
		}

		$dir = $this->dir( $templateName );
		/** @var $view SPAdmTemplateView */
		$view = SPFactory::View( 'template', true );
		if ( Sobi::Section() && Sobi::Cfg( 'section.template' ) == SPC::DEFAULT_TEMPLATE ) {
			SPFactory::message()
					->warning( Sobi::Txt( 'TP.DEFAULT_WARN', 'https://www.sigsiu.net/help_screen/template.info' ), false )
					->setSystemMessage();
		}

		if ( SPFs::exists( $dir . '/template.xml' ) ) {
			$info = new DOMDocument();
			$info->load( $dir . '/template.xml' );
			$xinfo = new DOMXPath( $info );
			$template = array();
			$template[ 'name' ] = $xinfo->query( '/template/name' )->item( 0 )->nodeValue;
			$view->assign( $template[ 'name' ], 'template_name' );
			$template[ 'author' ] = array(
					'name' => $xinfo->query( '/template/authorName' )->item( 0 )->nodeValue,
					'email' => $xinfo->query( '/template/authorEmail' )->item( 0 )->nodeValue,
					'url' => $xinfo->query( '/template/authorUrl' )->item( 0 )->nodeValue ? $xinfo->query( '/template/authorUrl' )->item( 0 )->nodeValue : null,
			);
			$template[ 'copyright' ] = $xinfo->query( '/template/copyright' )->item( 0 )->nodeValue;
			$template[ 'license' ] = $xinfo->query( '/template/license' )->item( 0 )->nodeValue;
			$template[ 'date' ] = $xinfo->query( '/template/creationDate' )->item( 0 )->nodeValue;
			$template[ 'version' ] = $xinfo->query( '/template/version' )->item( 0 )->nodeValue;
			$template[ 'description' ] = $xinfo->query( '/template/description' )->item( 0 )->nodeValue;
			$template[ 'id' ] = $xinfo->query( '/template/id' )->item( 0 )->nodeValue;
			if ( $xinfo->query( '/template/previewImage' )->length && $xinfo->query( '/template/previewImage' )->item( 0 )->nodeValue ) {
				$template[ 'preview' ] = Sobi::FixPath( Sobi::Cfg( 'live_site' ) . str_replace( '\\', '/', str_replace( SOBI_ROOT . DS, null, $dir ) ) . '/' . $xinfo->query( '/template/previewImage' )->item( 0 )->nodeValue );
			}
			if ( $xinfo->query( '/template/files/file' )->length ) {
				$files = array();
				foreach ( $xinfo->query( '/template/files/file' ) as $file ) {
					$filePath = $dir . '/' . $file->attributes->getNamedItem( 'path' )->nodeValue;
					if ( $filePath && is_file( $filePath ) ) {
						$filePath = $templateName . '.' . str_replace( '/', '.', $file->attributes->getNamedItem( 'path' )->nodeValue );
					}
					else {
						$filePath = null;
					}
					$files[ ] = array(
							'file' => $file->attributes->getNamedItem( 'path' )->nodeValue,
							'description' => $file->nodeValue,
							'filepath' => $filePath
					);
				}
				$template[ 'files' ] = $files;
				$view->assign( $files, 'files' );
			}
			$view->assign( $template, 'template' );
		}
		else {
			SPFactory::message()
					->warning( Sobi::Txt( 'TP.MISSING_DEFINITION_FILE' ), false )
					->setSystemMessage();
		}
		$menu = $this->createMenu();
		if ( Sobi::Section() ) {
			$menu->setOpen( 'AMN.APPS_SECTION_TPL' );
		}
		else {
			$menu->setOpen( 'GB.CFG.GLOBAL_TEMPLATES' );
		}
		$view->assign( $menu, 'menu' )
				->assign( $this->_task, 'task' )
				->assign( Sobi::Section(), 'sid' )
				->addHidden( $templateName, 'templateName' )
				->determineTemplate( 'template', 'info' );
		Sobi::Trigger( 'Info', $this->name(), array( &$file, &$view ) );
		$view->display();
	}

	public function getTemplateTree( $template )
	{
		return $this->listTemplates( SPLoader::dirPath( 'usr.templates.' ) . $template, false );
	}

	protected function save( $new = false )
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$content = SPRequest::raw( 'file_content', null, 'post' );
		$file = $this->file( SPRequest::cmd( 'fileName' ), !( $new ) );
		Sobi::Trigger( 'Save', $this->name(), array( &$content, &$file ) );
		if ( !( $file ) ) {
			throw new SPException( SPLang::e( 'Missing  file to save %s', SPRequest::cmd( 'fileName' ) ) );
		}
		$File = SPFactory::Instance( 'base.fs.file', $file );
		$File->content( stripslashes( $content ) );
		try {
			$File->save();
			$u = array( 'task' => 'template.edit', 'file' => SPRequest::cmd( 'fileName' ) );
			if ( Sobi::Section() ) {
				$u[ 'sid' ] = Sobi::Section();
			}
			$this->response( Sobi::Url( $u ), Sobi::Txt( 'TP.FILE_SAVED' ), $new, 'success' );
		} catch ( SPException $x ) {
			$this->response( Sobi::Back(), $x->getMessage(), false, 'error' );
		}
	}

	private function file( $file, $exits = true )
	{
		$ext = SPFs::getExt( $file );
		$file = explode( '.', $file );
		unset( $file[ count( $file ) - 1 ] );
		if ( strstr( $file[ 0 ], 'cms:' ) ) {
			$file[ 0 ] = str_replace( 'cms:', null, $file[ 0 ] );
			$file = SPFactory::mainframe()->path( implode( '.', $file ) );
			$file = Sobi::FixPath( SPLoader::path( $file, 'root', $exits, $ext ) );
		}
		else {
			$file = Sobi::FixPath( SPLoader::path( 'usr.templates.' . implode( '.', $file ), 'front', $exits, $ext ) );
		}
		if ( !$file ) {
			$file = SPLoader::path( 'usr.templates.' . implode( '.', $file ), 'front', false, $ext );
			Sobi::Error( $this->name(), SPLang::e( 'FILE_NOT_FOUND', $file ), SPC::WARNING, 404, __LINE__, __FILE__ );
		}
		return $file;
	}

	private function dir( $file )
	{
		$file = explode( '.', $file );
		if ( strstr( $file[ 0 ], 'cms:' ) ) {
			$file[ 0 ] = str_replace( 'cms:', null, $file[ 0 ] );
			$file = SPFactory::mainframe()->path( implode( '.', $file ) );
			$file = SPLoader::dirPath( $file, 'root', true );
		}
		else {
			$file = SPLoader::dirPath( 'usr.templates.' . implode( '.', $file ), 'front', true );
		}
		if ( !$file ) {
			$file = SPLoader::path( 'usr.templates.' . implode( '.', $file ), 'front', false );
			Sobi::Error( $this->name(), SPLang::e( 'FILE_NOT_FOUND', $file ), SPC::WARNING, 404, __LINE__, __FILE__ );
		}
		return $file;
	}

	private function editFile()
	{
		if ( Sobi::Section() && Sobi::Cfg( 'section.template' ) == SPC::DEFAULT_TEMPLATE ) {
			SPFactory::message()
					->warning( Sobi::Txt( 'TP.DEFAULT_WARN', 'https://www.sigsiu.net/help_screen/template.info' ), false )
					->setSystemMessage();
		}
		$file = SPRequest::cmd( 'file' );
		$file = $this->file( $file );
		$ext = SPFs::getExt( $file );
		$fileContent = SPFs::read( $file );
		$path = str_replace( '\\', '/', SOBI_PATH );
		if ( strstr( $file, $path ) ) {
			$filename = str_replace( $path . '/usr/templates/', null, $file );
		}
		else {
			$filename = str_replace( SOBI_ROOT, null, $file );
		}
		$menu = $this->createMenu();
		if ( Sobi::Section() ) {
			$menu->setOpen( 'AMN.APPS_SECTION_TPL' );
		}
		else {
			$menu->setOpen( 'GB.CFG.GLOBAL_TEMPLATES' );
		}
		/** @var $view SPAdmTemplateView */
		$view = SPFactory::View( 'template', true )
				->assign( $fileContent, 'file_content' )
				->assign( $filename, 'file_name' )
				->assign( $ext, 'file_ext' )
				->assign( $menu, 'menu' )
				->assign( $this->_task, 'task' )
				->assign( Sobi::Section(), 'sid' )
				->addHidden( SPRequest::cmd( 'file' ), 'fileName' )
				->addHidden( $filename, 'filePath' )
				->determineTemplate( 'template', 'edit' );
		Sobi::Trigger( 'Edit', $this->name(), array( &$file, &$view ) );
		$view->display();
	}
}
