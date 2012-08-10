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
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
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
        $dir = $this->dir( SPRequest::cmd( 'sp_fedit' ) );
        if ( SPRequest::cmd( 'sp_fedit' ) == 'default' ) {
            Sobi::Redirect( SPMainFrame::getBack(), Sobi::Txt( 'TP.DO_NOT_REMOVE' ), SPC::ERROR_MSG, true );
            exit();
        }
        if ( SPFs::delete( $dir ) ) {
            Sobi::Redirect( Sobi::Url( array( 'task' => 'config.general' ) ), Sobi::Txt( 'TP.REMOVED' ) );
        }
        else {
            Sobi::Redirect( SPMainFrame::getBack(), Sobi::Txt( 'TP.CANNOT_REMOVE' ), SPC::ERROR_MSG );
        }

    }

    private function cloneTpl()
    {
        $dir = $this->dir( SPRequest::cmd( 'sp_fedit' ) );
        $newName = SPRequest::word( 'sptplname', 'Duplicated Template', 'post' );
        $dirName = SPLang::nid( $newName );
        $dirNameOrg = $dirName;
        $c = 1;
        while ( SPFs::exists( SPLoader::dirPath( 'usr.templates.' . $dirName, 'front', false ) ) ) {
            $dirName = $dirNameOrg . '_' . $c;
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
        Sobi::Redirect( Sobi::Url( array( 'task' => 'template.info', 'template' => str_replace( SOBI_PATH . DS . 'usr' . DS . 'templates' . DS, null, $dirName ) ) ), Sobi::Txt( 'TP.DUPLICATED' ) );
    }

    private function info()
    {
        $dir = $this->dir( SPRequest::cmd( 'template' ) );
        $class = SPLoader::loadView( 'template', true );
        $view = new $class();
        if ( Sobi::Section() && Sobi::Cfg( 'section.template' ) == 'default' ) {
            SPMainFrame::msg( array( 'msg' => Sobi::Txt( 'TP.DEFAULT_WARN', 'http://sobipro.sigsiu.net/help_screen/template.info' ), 'msgtype' => SPC::ERROR_MSG ) );
        }

        if ( SPFs::exists( $dir . DS . 'template.xml' ) ) {
            $info = new DOMDocument();
            $info->load( $dir . DS . 'template.xml' );
            $xinfo = new DOMXPath( $info );
            $view->assign( $xinfo->query( '/template/name' )->item( 0 )->nodeValue, 'template_name' );
            $view->assign( $xinfo->query( '/template/authorName' )->item( 0 )->nodeValue, 'template_author' );
            $view->assign( $xinfo->query( '/template/authorEmail' )->item( 0 )->nodeValue, 'template_author_email' );
            if ( $xinfo->query( '/template/authorUrl' )->item( 0 )->nodeValue ) {
                $view->assign( $xinfo->query( '/template/authorUrl' )->item( 0 )->nodeValue, 'template_author_url' );
            }
            $view->assign( $xinfo->query( '/template/copyright' )->item( 0 )->nodeValue, 'template_copyright' );
            $view->assign( $xinfo->query( '/template/license' )->item( 0 )->nodeValue, 'template_license' );
            $view->assign( $xinfo->query( '/template/creationDate' )->item( 0 )->nodeValue, 'template_date' );
            $view->assign( $xinfo->query( '/template/version' )->item( 0 )->nodeValue, 'template_version' );
            if ( $xinfo->query( '/template/description' )->length && $xinfo->query( '/template/description' )->item( 0 )->nodeValue ) {
                $view->assign( $xinfo->query( '/template/description' )->item( 0 )->nodeValue, 'template_description' );
            }
            if ( $xinfo->query( '/template/previewImage' )->length && $xinfo->query( '/template/previewImage' )->item( 0 )->nodeValue ) {
                $img = Sobi::Cfg( 'live_site' ) . str_replace( '\\', '/', str_replace( SOBI_ROOT . DS, null, $dir ) ) . '/' . $xinfo->query( '/template/previewImage' )->item( 0 )->nodeValue;
                $view->assign( $img, 'template_preview_image' );
            }
            if ( $xinfo->query( '/template/files/file' )->length ) {
                $files = array();
                foreach ( $xinfo->query( '/template/files/file' ) as $file ) {
                    $files[ ] = array( 'file' => $file->attributes->getNamedItem( 'path' )->nodeValue, 'description' => $file->nodeValue );
                }
                $view->assign( $files, 'template_files' );
            }
        }
        else {
            SPMainFrame::msg( array( 'msg' => Sobi::Txt( 'TP.MISSING_DEFINITION_FILE' ), 'msgtype' => SPC::ERROR_MSG ) );
        }
        $menu = $this->createMenu();
        if ( Sobi::Section() ) {
            $menu->setOpen( 'AMN.APPS_SECTION_TPL' );
        }
        else {
            $menu->setOpen( 'GB.CFG.GLOBAL_TEMPLATES' );
        }
        $view->loadConfig( 'template.info' );
        $view->setTemplate( 'template.info' );
        $view->assign( $menu, 'menu' );
        $view->assign( $this->_task, 'task' );
        $view->addHidden( SPRequest::cmd( 'template' ), 'sp_fedit' );
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
        SPConfig::debOut( SPRequest::cmd( 'sp_fedit' ) );

        $file = $this->file( SPRequest::cmd( 'sp_fedit' ), !( $new ) );
        Sobi::Trigger( 'Save', $this->name(), array( &$content, &$file ) );
        if ( !$file ) {
            throw new SPException( SPLang::e( 'Missing  file to save %s', SPRequest::cmd( 'sp_fedit' ) ) );
        }
        $fClass = SPLoader::loadClass( 'base.fs.file' );
        $File = new $fClass( $file );
        $File->content( stripslashes( $content ) );
        if ( $File->save() ) {
            $u = array( 'task' => 'template.edit', 'file' => SPRequest::cmd( 'sp_fedit' ) );
            if ( SPRequest::sid() ) {
                $u[ 'sid' ] = SPRequest::sid();
            }
            Sobi::Redirect( Sobi::Url( $u ), Sobi::Txt( 'TP.FILE_SAVED' ) );
        }
        else {
            Sobi::Redirect( SPMainFrame::getBack(), Sobi::Txt( 'TP.CANNOT_SAVE' ), SPC::ERROR_MSG );
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
            SPMainFrame::msg( array( 'msg' => Sobi::Txt( 'TP.DEFAULT_WARN', 'http://sobipro.sigsiu.net/help_screen/template.info' ), 'msgtype' => SPC::ERROR_MSG ) );
        }
        $file = SPRequest::cmd( 'file' );
        $file = $this->file( $file );
        $ext = SPFs::getExt( $file );
        $fcontent = SPFs::read( $file );
        if ( strstr( $file, SOBI_PATH ) ) {
            $fname = str_replace( SOBI_PATH . DS . 'usr' . DS . 'templates' . DS, null, $file );
        }
        else {
            $fname = str_replace( SOBI_ROOT, null, $file );
        }
        $menu = $this->createMenu();
        if ( Sobi::Section() ) {
            $menu->setOpen( 'AMN.APPS_SECTION_TPL' );
        }
        else {
            $menu->setOpen( 'GB.CFG.GLOBAL_TEMPLATES' );
        }
        $class = SPLoader::loadView( 'template', true );
        $view = new $class();
        $view->assign( $fcontent, 'file_content' );
        $view->assign( $fname, 'file_name' );
        $view->assign( $ext, 'file_ext' );
        $view->assign( $menu, 'menu' );
        $view->assign( $this->_task, 'task' );
        $view->addHidden( SPRequest::cmd( 'file' ), 'sp_fedit' );
        $view->loadConfig( 'template.edit' );
        $view->setTemplate( 'template.edit' );
        Sobi::Trigger( 'Edit', $this->name(), array( &$file, &$view ) );
        $view->display();
    }
}
