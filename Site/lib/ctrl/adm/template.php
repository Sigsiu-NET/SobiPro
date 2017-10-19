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

use Sobi\C;
use Sobi\FileSystem\DirectoryIterator;
use Sobi\FileSystem\File;
use Sobi\FileSystem\FileSystem;
use Sobi\Input\Input;

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
		if ( Sobi::Section() ) {
			if ( !( Sobi::Can( 'section.configure' ) ) ) {
				Sobi::Error( $this->name(), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', Input::Task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
				exit();
			}
		}
		elseif ( !( Sobi::Can( 'cms.apps' ) ) ) {
			Sobi::Error( $this->name(), SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', Input::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
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
			case 'compileSave':
				$this->save( $this->_task == 'saveAs', $this->_task == 'compileSave' );
				break;
			case 'info':
				$this->info();
				break;
//			case 'deleteFile':
//				$this->deleteFile();
//				break;
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
			case 'settings':
				$this->templateSettings();
				break;
			case 'saveConfig':
				$this->saveConfig();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if ( !( parent::execute() ) ) {
					Sobi::Error( $this->name(), SPLang::e( 'SUCH_TASK_NOT_FOUND', Input::Task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
				}
				break;
		}
	}

	protected function saveConfig()
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', Input::Task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$config = Input::Arr( 'settings' );
		$templateName = Input::Cmd( 'templateName' );
		if ( !( strlen( $templateName ) ) ) {
			$templateName = SPC::DEFAULT_TEMPLATE;
		}
		foreach ( $config as $configFile => $settings ) {
			$store = json_encode( $settings );
			if ( isset( $settings[ 'less' ] ) && count( $settings[ 'less' ] ) ) {
				foreach ( $settings[ 'less' ] as $file => $variables ) {
					$lessFile = FileSystem::FixPath( $this->dir( $templateName ) . '/css/' . $file . '.less' );
					if ( FileSystem::exists( $lessFile ) ) {
						$lessContent = SPFs::read( $lessFile );
						foreach ( $variables as $variable => $value ) {
							// @colour-set: sobipro;
							$lessContent = preg_replace( "/@{$variable}:[^\n]*\;/", "@{$variable}: {$value};", $lessContent );
						}
						try {
							SPFs::write( $lessFile, $lessContent );
							$this->compileLessFile( $lessFile, str_replace( 'less', 'css', $lessFile ), Sobi::Url( 'template.settings' ), true );
						} catch ( SPException $x ) {
							$this->response( Sobi::Url( 'template.settings' ), Sobi::Txt( 'TP.SETTINGS_NOT_SAVED', $x->getMessage() ), false, SPC::ERROR_MSG );
						}
					}
				}
			}
			try {
				FileSystem::Write( FileSystem::FixPath( $this->dir( $templateName ) . str_replace( '.', '/', $configFile ) . '.json' ), $store );
			} catch ( SPException $x ) {
				$this->response( Sobi::Url( 'template.settings' ), Sobi::Txt( 'TP.SETTINGS_NOT_SAVED', $x->getMessage() ), false, SPC::ERROR_MSG );
			}
		}
		SPFactory::cache()
				->cleanSectionXML( Sobi::Section() );
		$this->response( Sobi::Url( 'template.settings' ), Sobi::Txt( 'TP.SETTINGS_SAVED' ), false, SPC::SUCCESS_MSG );
	}

	protected function templateSettings()
	{
		$templateName = Input::Cmd( 'template' );
		$templateSettings = [];
		$file = null;
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
			$file = $this->getTemplateData( $dir, $view, $templateName );
		}
		else {
			SPFactory::message()
					->warning( Sobi::Txt( 'TP.MISSING_DEFINITION_FILE' ), false )
					->setSystemMessage();
		}
		/** search for all json files */
		$directory = new DirectoryIterator( $dir );
		$configs = array_keys( $directory->searchFile( '.json', false ) );
		if ( count( $configs ) ) {
			foreach ( $configs as $file ) {
				$prefix = null;
				if ( basename( dirname( $file ) ) != $templateName ) {
					$prefix = basename( dirname( $file ) ) . '-';
				};
				$templateSettings[ $prefix . basename( $file, '.json' ) ] = json_decode( SPFs::read( $file ), true );
			}
		}
		$menu = $this->createMenu();
		$plugins = SPFactory::db()
				->select( 'pid', 'spdb_plugins' )
				->loadAssocList( 'pid' );
		if ( Sobi::Section() ) {
			$menu->setOpen( 'AMN.APPS_SECTION_TPL' );
		}
		else {
			$menu->setOpen( 'GB.CFG.GLOBAL_TEMPLATES' );
		}
		$view->setCtrl( $this );
		$entriesOrdering = $view->namesFields( null, true );
		$sid = Sobi::Section();
		$view->assign( $menu, 'menu' )
				->assign( $this->_task, 'task' )
				->assign( $sid, 'sid' )
				->assign( $templateSettings, 'settings' )
				->assign( $entriesOrdering, 'entriesOrdering' )
				->assign( $plugins, 'apps' )
				->addHidden( $templateName, 'templateName' )
				->determineTemplate( 'template', 'config', $dir );
		Sobi::Trigger( 'Settings', $this->name(), [ &$file, &$view ] );
		$view->display();
	}

	protected function compile( $outputMessage = true )
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', Input::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$file = $this->file( Input::Cmd( 'fileName' ) );
		$output = str_replace( 'less', 'css', $file );
		Sobi::Trigger( 'BeforeCompileLess', $this->name(), [ &$file ] );
		$u = [ 'task' => 'template.edit', 'file' => Input::Cmd( 'fileName' ) ];
		if ( Sobi::Section() ) {
			$u[ 'sid' ] = Sobi::Section();
		}
		if ( !( $file ) ) {
			$this->response( Sobi::Url( $u ), SPLang::e( 'Missing file to compile %s', Input::cmd( 'fileName' ) ), false, SPC::ERROR_MSG );
		}
		$this->compileLessFile( $file, $output, $u );
		if ( $outputMessage ) {
			$this->response( Sobi::Url( $u ), Sobi::Txt( 'TP.LESS_FILE_COMPILED', str_replace( SOBI_PATH, null, $output ) ), false, SPC::SUCCESS_MSG );
		}
		else {
			return Sobi::Txt( 'TP.LESS_FILE_COMPILED', str_replace( SOBI_PATH, null, $output ) );
		}
	}

	protected function getTemplateFiles()
	{
		$type = Input::Cmd( 'type', 'post' );
		if ( strstr( $type, '.' ) ) {
			$type = explode( '.', $type );
			$type = $type[ 0 ];
		}
		$directory = $this->dir( Sobi::Cfg( 'section.template' ) );
		$directory = FileSystem::FixPath( $directory . '/' . $type );
		$arr = [];
		if ( file_exists( $directory ) ) {
			$files = scandir( $directory );
			if ( count( $files ) ) {
				foreach ( $files as $file ) {
					$stack = explode( '.', $file );
					if ( array_pop( $stack ) == 'xsl' ) {
						$arr[] = [ 'name' => $stack[ 0 ], 'filename' => $file ];
					}
				}
			}
		}
		Sobi::Trigger( 'List', 'Templates', [ &$arr ] );
		SPFactory::mainframe()->cleanBuffer();
		echo json_encode( $arr );
		exit;
	}

//	protected function deleteFile()
//	{
//		if( !( SPFactory::mainframe()->checkToken() ) ) {
//			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', Input::Task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
//		}
//		$file = $this->file( Input::Cmd( 'sp_fedit' ) );
//		Sobi::Trigger( 'Delete', $this->name(), array( &$content, &$file ) );
//		if( !$file ) {
//			throw new SPException( SPLang::e( 'Missing  file to delete %s', Input::Cmd( 'sp_fedit' ) ) );
//		}
//		$fClass = SPLoader::loadClass( 'base.fs.file' );
//		$File = new $fClass( $file );
//		if( $File->delete() ) {
//			$u = array( 'task' => 'template.edit', 'file' => 'template.xml' );
//			if( Input::Sid() ) {
//				$u[ 'sid' ] = Input::Sid();
//			}
//			Sobi::Redirect( Sobi::Url( $u ), 'File has been deleted' );
//		}
//		else {
//			Sobi::Redirect( SPMainFrame::getBack(), 'Cannot delete the file', SPC::ERROR_MSG );
//		}
//	}

	private function delete()
	{
		$dir = $this->dir( Input::Cmd( 'templateName' ) );
		if ( Input::Cmd( 'templateName' ) == SPC::DEFAULT_TEMPLATE ) {
			$this->response( Sobi::Url( 'template.info' ), Sobi::Txt( 'TP.DO_NOT_REMOVE' ), true, 'error' );
		}
		if ( $dir && FileSystem::Delete( $dir ) ) {
			$this->response( Sobi::Url( [ 'task' => 'config.general' ] ), Sobi::Txt( 'TP.REMOVED' ), false, 'success' );
		}
		else {
			$this->response( Sobi::Back(), Sobi::Txt( 'TP.CANNOT_REMOVE' ), false, 'error' );
		}
	}

	private function cloneTpl()
	{
		$dir = $this->dir( Input::Cmd( 'templateName' ) );
		$newName = Input::String( 'templateNewName', 'post', 'Duplicated Template' );
		$newName = str_replace( [ '-', '_' ], ' ', $newName );
		$newName = explode( ' ', $newName );
		foreach ( $newName as $i => $part ) {
			$newName[ $i ] = ucfirst( $part );
		}
		$newName = implode( $newName );
		$dirName = ( $newName );
		$dirNameOrg = $dirName;
		$c = 1;
		while ( FileSystem::Exists( SPLoader::dirPath( 'usr.templates.' . $dirName, 'front', false ) ) ) {
			$dirName = $dirNameOrg . '-' . $c++;
		}
		$newPath = SPLoader::dirPath( 'usr.templates.' . $dirName, 'front', false );
		if ( !( FileSystem::Copy( $dir, $newPath ) ) ) {
			throw new SPException( SPLang::e( 'COULD_NOT_COPY_DIRECTORY', $dir, $newPath ) );
		}
		$defFile = SPLoader::path( $newPath . '.template', 'absolute', true, 'xml' );
		if ( $defFile ) {
			$def = new DOMDocument();
			$def->load( $defFile );
			$xdef = new DOMXPath( $def );
			$oldName = $xdef->query( '/template/name' )->item( 0 )->nodeValue;
			$oldDesc = $xdef->query( '/template/description' )->item( 0 )->nodeValue;
			$date = SPFactory::config()->date( time(), null, 'Y-m-d' );
			$xdef->query( '/template/name' )->item( 0 )->nodeValue = Input::String( 'templateNewName', 'post', 'Duplicated Template' );
			$xdef->query( '/template/creationDate' )->item( 0 )->nodeValue = $date;
			$xdef->query( '/template/id' )->item( 0 )->nodeValue = $dirName;
			$newDesc = Sobi::Txt( 'TP.CLONE_NOTE', [ 'name' => $oldName, 'date' => $date ] );
			$xdef->query( '/template/description' )->item( 0 )->nodeValue = "{$newDesc}\n{$oldDesc}";
			$file = new File( $defFile );
			$file->content( $def->saveXML() );
			$file->save();
		}
		/** Replace template's prefixes  */
		$newDir = $this->dir( $dirName );
		if ( FileSystem::Exists( $newDir . '/template.php' ) ) {
			$content = FileSystem::Read( $newDir . '/template.php' );
			$class = [];
			preg_match( '/\s*(class)\s+(\w+)/', $content, $class );
			$className = $class[ 2 ];
			$oldTplName = Input::Cmd( 'templateName' );
			// if for example bs3-default
			if ( strstr( $oldTplName, '-' ) ) {
				$oldTplName = explode( '-', $oldTplName );
				// take the longer part - it's most likely the right one
				$oldTplName = strlen( $oldTplName[ 0 ] > $oldTplName[ 1 ] ) ? $oldTplName[ 0 ] : $oldTplName[ 1 ];
			}
			if ( stristr( $className, $oldTplName ) ) {
				$newClassName = str_ireplace( $oldTplName, ucfirst( $newName ), $className );
			}
			else {
				if ( $className == 'TplFunctions' ) {
					$newClassName = 'Tpl' . ucfirst( $newName ) . 'Functions';
				}
				else {
					$newClassName = $className . ucfirst( $newName );
				}
			}
			$newClassName = ucfirst( $newClassName );
			$content = str_replace( 'class ' . $className, 'class ' . $newClassName, $content );
			FileSystem::Write( $newDir . '/template.php', $content );
			// now go through all XSL files
			/** @var SPDirectory $directory */
			$directory = SPFactory::Instance( 'base.fs.directory', $newDir );
			$files = $directory->searchFile( '.xsl', false, 2 );
			if ( count( $files ) ) {
				$files = array_keys( $files );
				foreach ( $files as $file ) {
					$c = FileSystem::Read( $file );
					if ( strstr( $c, "'{$className}::" ) ) {
						$c = str_replace( "'{$className}::", "'{$newClassName}::", $c );
						FileSystem::Write( $file, $c );
					}
				}
			}
		}
		// now the namespace
		/** @var SPDirectory $directory */
		$directory = SPFactory::Instance( 'base.fs.directory', $newDir );
		$files = $directory->searchFile( [ '.less', '.css' ], false, 2 );
		if ( count( $files ) ) {
			$oldTplName = Input::Cmd( 'templateName' );
			$files = array_keys( $files );
			foreach ( $files as $file ) {
				$c = FileSystem::Read( $file );
				if ( strstr( $c, $oldTplName ) ) {
					$c = str_replace( $oldTplName, $newName, $c );
					FileSystem::Write( $file, $c );
				}
			}
		}
		$this->response( Sobi::Url( [ 'task' => 'template.info', 'template' => str_replace( SOBI_PATH . '/usr/templates/', null, $dirName ) ] ), Sobi::Txt( 'TP.DUPLICATED' ), false, 'success' );
	}

	private function info()
	{
		$templateName = Input::Cmd( 'template' );
		$file = null;
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

		if ( FileSystem::Exists( $dir . '/template.xml' ) ) {
			$file = $this->getTemplateData( $dir, $view, $templateName );
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
		$sid = Sobi::Section();
		$view->assign( $menu, 'menu' )
				->assign( $this->_task, 'task' )
				->assign( $sid, 'sid' )
				->addHidden( $templateName, 'templateName' )
				->determineTemplate( 'template', 'info' );
		Sobi::Trigger( 'Info', $this->name(), [ &$file, &$view ] );
		$view->display();
	}

	public function getTemplateTree( $template )
	{
		if ( FileSystem::Exists( SPLoader::dirPath( 'usr.templates.' ) . $template ) ) {
			return $this->listTemplates( SPLoader::dirPath( 'usr.templates.' ) . $template, false );
		}
		else {
			SPFactory::message()
					->error( Sobi::Txt( 'TP.TEMPLATE_MISSING', Sobi::Cfg( 'section.template' ) ), false );
			return null;
		}
	}

	protected function save( $new = false, $compile = false )
	{
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', Input::Task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$content = SPRequest::raw( 'file_content', null, 'post' );
		$file = $this->file( Input::Cmd( 'fileName' ), !( $new ) );
		Sobi::Trigger( 'Save', $this->name(), [ &$content, &$file ] );
		if ( !( $file ) ) {
			throw new SPException( SPLang::e( 'Missing  file to save %s', Input::Cmd( 'fileName' ) ) );
		}
		$File = new File( $file );
		$File->content( stripslashes( $content ) );
		try {
			$File->save();
			$message = Sobi::Txt( 'TP.FILE_SAVED' );
			if ( $compile ) {
				$message .= "\n" . $this->compile( false );
			}

			$u = [ 'task' => 'template.edit', 'file' => Input::Cmd( 'fileName' ) ];
			if ( Sobi::Section() ) {
				$u[ 'sid' ] = Sobi::Section();
			}
			$this->response( Sobi::Url( $u ), $message, $new, 'success' );
		} catch ( SPException $x ) {
			$this->response( Sobi::Back(), $x->getMessage(), false, 'error' );
		}
	}

	private function file( $file, $exits = true )
	{
		$ext = FileSystem::GetExt( $file );
		$file = explode( '.', $file );
		unset( $file[ count( $file ) - 1 ] );
		if ( strstr( $file[ 0 ], 'cms:' ) ) {
			$file[ 0 ] = str_replace( 'cms:', null, $file[ 0 ] );
			$file = SPFactory::mainframe()->path( implode( '.', $file ) );
			$file = FileSystem::FixPath( SPLoader::path( $file, 'root', $exits, $ext ) );
		}
		else {
			$file = FileSystem::FixPath( SPLoader::path( 'usr.templates.' . implode( '.', $file ), 'front', $exits, $ext ) );
		}
		if ( !$file ) {
			$file = SPLoader::path( 'usr.templates.' . implode( '.', $file ), 'front', false, $ext );
			Sobi::Error( $this->name(), SPLang::e( 'FILE_NOT_FOUND', $file ), SPC::WARNING, 404, __LINE__, __FILE__ );
		}
		return $file;
	}

	/**
	 * @param array $file
	 *
	 * @return array|string
	 *
	 * @since version
	 */
	private function dir( $file )
	{
		$file = explode( '.', $file );
		if ( strstr( $file[ 0 ], 'cms:' ) ) {
			$file[ 0 ] = str_replace( 'cms:', null, $file[ 0 ] );
			$file = SPFactory::mainframe()->path( implode( '.', $file ) );
			$file = SPLoader::dirPath( $file, 'root', true );
		}
		else {
			/** @var array $file */
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
		$file = Input::Cmd( 'file' );
		$file = $this->file( $file );
		$ext = FileSystem::GetExt( $file );
		$fileContent = FileSystem::read( $file );
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
		$sid = Sobi::Section();
		$view = SPFactory::View( 'template', true )
				->assign( $fileContent, 'file_content' )
				->assign( $filename, 'file_name' )
				->assign( $ext, 'file_ext' )
				->assign( $menu, 'menu' )
				->assign( $this->_task, 'task' )
				->assign( $sid, 'sid' )
				->addHidden( Input::Cmd( 'file' ), 'fileName' )
				->addHidden( $filename, 'filePath' )
				->determineTemplate( 'template', 'edit' );
		Sobi::Trigger( 'Edit', $this->name(), [ &$file, &$view ] );
		$view->display();
	}

	/**
	 * @param $dir
	 * @param $view
	 * @param $templateName
	 * @return mixed
	 */
	protected function getTemplateData( $dir, $view, $templateName )
	{
		$info = new DOMDocument();
		$info->load( $dir . '/template.xml' );
		$xinfo = new DOMXPath( $info );
		$template = [];
		$template[ 'name' ] = $xinfo->query( '/template/name' )->item( 0 )->nodeValue;
		$view->assign( $template[ 'name' ], 'template_name' );
		$template[ 'author' ] = [
				'name' => $xinfo->query( '/template/authorName' )->item( 0 )->nodeValue,
				'email' => $xinfo->query( '/template/authorEmail' )->item( 0 )->nodeValue,
				'url' => $xinfo->query( '/template/authorUrl' )->item( 0 )->nodeValue ? $xinfo->query( '/template/authorUrl' )->item( 0 )->nodeValue : null,
		];
		$template[ 'copyright' ] = $xinfo->query( '/template/copyright' )->item( 0 )->nodeValue;
		$template[ 'license' ] = $xinfo->query( '/template/license' )->item( 0 )->nodeValue;
		$template[ 'date' ] = $xinfo->query( '/template/creationDate' )->item( 0 )->nodeValue;
		$template[ 'version' ] = $xinfo->query( '/template/version' )->item( 0 )->nodeValue;
		$template[ 'description' ] = $xinfo->query( '/template/description' )->item( 0 )->nodeValue;
		$template[ 'id' ] = $xinfo->query( '/template/id' )->item( 0 )->nodeValue;
		if ( $xinfo->query( '/template/previewImage' )->length && $xinfo->query( '/template/previewImage' )->item( 0 )->nodeValue ) {
			$template[ 'preview' ] = FileSystem::FixPath( Sobi::Cfg( 'live_site' ) . str_replace( '\\', '/', str_replace( SOBI_ROOT . C::DS, null, $dir ) ) . '/' . $xinfo->query( '/template/previewImage' )->item( 0 )->nodeValue );
		}
		$file = '';
		if ( $xinfo->query( '/template/files/file' )->length ) {
			$files = [];
			foreach ( $xinfo->query( '/template/files/file' ) as $file ) {
				$filePath = $dir . '/' . $file->attributes->getNamedItem( 'path' )->nodeValue;
				if ( $filePath && is_file( $filePath ) ) {
					$filePath = $templateName . '.' . str_replace( '/', '.', $file->attributes->getNamedItem( 'path' )->nodeValue );
				}
				else {
					$filePath = null;
				}
				$files[] = [
						'file' => $file->attributes->getNamedItem( 'path' )->nodeValue,
						'description' => $file->nodeValue,
						'filepath' => $filePath
				];
			}
			$template[ 'files' ] = $files;
			$view->assign( $files, 'files' );
		}
		$view->assign( $template, 'template' );
		return $file;
	}

	/**
	 * @param $file
	 * @param $output
	 * @param $u
	 * @param bool $compress
	 */
	protected function compileLessFile( $file, $output, $u, $compress = false )
	{
		try {
			include_once( 'phar://' . SOBI_PATH . '/lib/services/third-party/less/less.phar.tar.gz/Autoloader.php' );
			Less_Autoloader::register();

			if ( $compress ) {
				$options = [
						'compress' => true,
						'strictMath' => true
				];
			}
			else {
				$options = [];
			}
			$parser = new Less_Parser( $options );
			$parser->parseFile( $file );
			$css = $parser->getCss();
			if ( FileSystem::Exists( $output ) ) {
				FileSystem::Delete( $output );
			}
			FileSystem::Write( $output, $css );
		} catch ( Exception $x ) {
			$this->response( Sobi::Url( $u ), SPLang::e( 'TP.LESS_FILE_NOT_COMPILED', $x->getMessage() ), false, SPC::ERROR_MSG );
		}
	}
}
