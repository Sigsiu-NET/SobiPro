<?php
/**
 * @version: $Id: template.php 1979 2011-11-08 18:25:45Z Radek Suski $
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
 * $Date: 2011-11-08 19:25:45 +0100 (Tue, 08 Nov 2011) $
 * $Revision: 1979 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/adm/template.php $
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
		if ( SPRequest::cmd( 'templateName' ) == 'default' ) {
			$this->response( Sobi::Back(), Sobi::Txt( 'TP.DO_NOT_REMOVE' ), false, 'error' );
		}
		if ( SPFs::delete( $dir ) ) {
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
			$date = SPFactory::config()->date();
			$xdef->query( '/template/name' )->item( 0 )->nodeValue = $newName;
			$xdef->query( '/template/id' )->item( 0 )->nodeValue = $dirName;
			$newDesc = Sobi::Txt( 'TP.CLONE_NOTE', array( 'name' => $oldName, 'date' => $date ) );
			$xdef->query( '/template/description' )->item( 0 )->nodeValue = "{$newDesc}\n<br/>\n{$oldDesc}";
			$file = new $fc( $defFile );
			$file->content( $def->saveXML() );
			$file->save();
		}
		$this->response( Sobi::Url( array( 'task' => 'template.info', 'template' => str_replace( SOBI_PATH . DS . 'usr' . DS . 'templates' . DS, null, $dirName ) ) ), Sobi::Txt( 'TP.DUPLICATED' ), false, 'success' );
	}

	private function info()
	{
		$template = SPRequest::cmd( 'template' );
		if ( !( $template ) ) {
			$template = 'default';
		}
		$dir = $this->dir( $template );
		/** @var $view SPAdmTemplateView */
		$view = SPFactory::View( 'template', true );
		if ( Sobi::Section() && Sobi::Cfg( 'section.template' ) == 'default' ) {
			SPFactory::message()
					->warning( Sobi::Txt( 'TP.DEFAULT_WARN', 'http://sobipro.sigsiu.net/help_screen/template.info' ), false )
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
					$files[ ] = array(
						'file' => $file->attributes->getNamedItem( 'path' )->nodeValue,
						'description' => $file->nodeValue,
						'filepath' => SPRequest::cmd( 'template' ) . '.' . str_replace( '/', '.', $file->attributes->getNamedItem( 'path' )->nodeValue )
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
				->addHidden( SPRequest::cmd( 'template' ), 'templateName' )
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
		if ( Sobi::Section() && Sobi::Cfg( 'section.template' ) == 'default' ) {
			SPFactory::message()
					->warning( Sobi::Txt( 'TP.DEFAULT_WARN', 'http://sobipro.sigsiu.net/help_screen/template.info' ), false )
					->setSystemMessage();
		}
		$file = SPRequest::cmd( 'file' );
		$file = $this->file( $file );
		$ext = SPFs::getExt( $file );
		$fileContent = SPFs::read( $file );
		if ( strstr( $file, SOBI_PATH ) ) {
			$filename = str_replace( SOBI_PATH . DS . 'usr' . DS . 'templates' . DS, null, $file );
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
