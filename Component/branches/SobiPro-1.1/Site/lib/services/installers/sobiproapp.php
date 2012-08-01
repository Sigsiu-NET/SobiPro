<?php
/**
 * @version: $Id: sobiproapp.php 2302 2012-03-15 17:00:28Z Radek Suski $
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
 * $Date: 2012-03-15 18:00:28 +0100 (Thu, 15 Mar 2012) $
 * $Revision: 2302 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/services/installers/plugin.php $
 */
defined( 'SOBIPRO' ) || ( trigger_error( 'Restricted access ' . __FILE__, E_USER_ERROR ) && exit( 'Restricted access' ) );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 02-Jul-2010 16:49:58
 */

SPLoader::loadClass( 'services.installers.installer' );

class SPAppInstaller extends SPInstaller
{
    public function install()
    {
        $log = array();
        $type = strlen( $this->xGetString( 'type' ) ) ? $this->xGetString( 'type' ) : ( $this->xGetString( 'fieldType' ) ? 'field' : null );
        $id = $this->xGetString( 'id' );

        if ( $this->installed( $id, $type ) ) {
            SPFactory::db()->delete( 'spdb_plugins', array( 'pid' => $id, 'type' => $type ) );
        }
        Sobi::Trigger( 'Before', 'InstallPlugin', array( $id ) );
        $requirements = $this->xGetChilds( 'requirements/*' );
        if ( $requirements && ( $requirements instanceof DOMNodeList ) ) {
            $reqCheck =& SPFactory::Instance( 'services.installers.requirements' );
            $reqCheck->check( $requirements );
        }

        $permissions = $this->xGetChilds( 'permissions/*' );
        if ( $permissions && ( $permissions instanceof DOMNodeList ) ) {
            $permsCtrl =& SPFactory::Instance( 'ctrl.adm.acl' );
            for ( $i = 0; $i < $permissions->length; $i++ ) {
                $perm = explode( '.', $permissions->item( $i )->nodeValue );
                $permsCtrl->addPermission( $perm[ 0 ], $perm[ 1 ], $perm[ 2 ] );
                $log[ 'permissions' ][ ] = $permissions->item( $i )->nodeValue;
            }
        }

        $actions = $this->xGetChilds( 'actions/*' );
        if ( $actions && ( $actions instanceof DOMNodeList ) ) {
            $log[ 'actions' ] = $this->actions( $actions, $id );
        }

        $dir = SPLoader::dirPath( 'etc.installed.' . SPLang::nid( $type . 's' ), 'front', false );
        if ( !( SPFs::exists( $dir ) ) ) {
            SPFs::mkdir( $dir );
            SPFs::mkdir( "{$dir}/{$id}/backup" );
        }

        $files = $this->xGetChilds( 'files' );
        if ( $files && ( $files instanceof DOMNodeList ) && $files->length ) {
            $log[ 'files' ] = $this->files( $files, $id, "{$dir}/{$id}/backup" );
        }

        $language = $this->xGetChilds( 'language/file' );
        $folder = $this->xGetChilds( 'language/@folder' )->item( 0 )->nodeValue;
        if ( $language && ( $language instanceof DOMNodeList ) && $language->length ) {
            $langFiles = array();
            foreach ( $language as $file ) {
                if ( $file->attributes->getNamedItem( 'admin' ) ) {
                    $adm = $file->attributes->getNamedItem( 'admin' )->nodeValue;
                }
                else {
                    $adm = false;
                }
                $langFiles[ $file->attributes->getNamedItem( 'lang' )->nodeValue ][ ] =
                        array(
                            'path' => Sobi::FixPath( "{$this->root}/{$folder}/" . trim( $file->nodeValue ) ),
                            'name' => $file->nodeValue,
                            'adm' => $adm
                        );
            }
            $log[ 'files' ][ 'created' ] = array_merge( $log[ 'files' ][ 'created' ], SPFactory::CmsHelper()->installLang( $langFiles, false ) );
        }

        $sql = $this->xGetString( 'sql' );
        if ( $sql && SPFs::exists( "{$this->root}/{$sql}" ) ) {
            try {
                $log[ 'sql' ] = SPFactory::db()->loadFile( "{$this->root}/{$sql}" );
            }
            catch ( SPException $x ) {
                Sobi::Error( 'installer', SPLang::e( 'CANNOT_EXECUTE_QUERIES', $x->getMessage() ), SPC::WARNING, 500, __LINE__, __FILE__ );
                return false;
            }
        }
        if ( !strlen( $this->xGetString( 'type' ) ) && strlen( $this->xGetString( 'fieldType' ) ) ) {
            $log[ 'field' ] = $this->field();
        }

        $exec = $this->xGetString( 'exec' );
        if ( $exec && SPFs::exists( "{$this->root}/{$exec}" ) ) {
            include_once "{$this->root}/{$exec}";
        }

        $this->plugin( $id, $type );
        $this->log( $log );
        $this->definition->formatOutput = true;
        $this->definition->preserveWhiteSpace = false;
        $this->definition->normalizeDocument();
        $path = "{$dir}/{$id}.xml";
        $file = SPFactory::Instance( 'base.fs.file', $path );
        //		$file->content( $this->definition->saveXML() );
        // why the hell DOM cannot format it right. Try this
        $outXML = $this->definition->saveXML();
        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $xml->loadXML( $outXML );
        $file->content( $xml->saveXML() );
        $file->save();

        switch ( $type ) {
            case 'SobiProApp':
                $type = 'plugin';
            default:
            case 'update':
            case 'plugin':
            case 'field':
                $t = Sobi::Txt( 'EX.' . strtoupper( $type ) . '_TYPE' );
                break;
            case 'payment':
                $t = Sobi::Txt( 'EX.PAYMENT_METHOD_TYPE' );
                break;
        }
        Sobi::Trigger( 'After', 'InstallPlugin', array( $id ) );
        $dir =& SPFactory::Instance( 'base.fs.directory', SPLoader::dirPath( 'tmp.install' ) );
        $dir->deleteFiles();
        return Sobi::Txt( 'EX.EXTENSION_HAS_BEEN_INSTALLED', array( 'type' => $t, 'name' => $this->xGetString( 'name' ) ) );
    }

    private function _d( $msg )
    {
        //		SPConfig::debOut( $msg, false, false, true );
    }

    private function log( $log )
    {
        if ( count( $log ) ) {
            $install = $this->definition->createElement( 'installLog' );
            foreach ( $log as $section => $values ) {
                switch ( $section ) {
                    case 'files':
                        {
                        if ( isset( $values[ 'modified' ] ) ) {
                            $mfiles = $this->definition->createElement( 'modified' );
                            foreach ( $values[ 'modified' ] as $i => $file ) {
                                $f = $this->definition->createElement( 'file' );
                                $f->appendChild( $this->definition->createElement( 'path', Sobi::FixPath( $file[ 'file' ] ) ) );
                                $f->appendChild( $this->definition->createElement( 'size', $file[ 'size' ] ) );
                                $f->appendChild( $this->definition->createElement( 'checksum', $file[ 'checksum' ] ) );
                                $f->appendChild( $this->definition->createElement( 'backup', Sobi::FixPath( $file[ 'backup' ] ) ) );
                                $mfiles->appendChild( $f );
                            }
                            $install->appendChild( $mfiles );
                            if ( isset( $values[ 'created' ] ) && count( $values[ 'created' ] ) ) {
                                $mfiles = $this->definition->createElement( 'files' );
                                foreach ( $values[ 'created' ] as $i => $file ) {
                                    $mfiles->appendChild( $this->definition->createElement( 'file', Sobi::FixPath( $file ) ) );
                                }
                                $install->appendChild( $mfiles );
                            }
                        }
                        else {
                            if ( count( $values ) ) {
                                $files = $this->definition->createElement( 'files' );
                                foreach ( $values as $i => $file ) {
                                    $files->appendChild( $this->definition->createElement( 'file',  Sobi::FixPath( $file ) ) );
                                }
                                $install->appendChild( $files );
                            }
                        }
                        break;
                        }
                    case 'actions':
                        {
                        $actions = $this->definition->createElement( 'actions' );
                        if ( count( $values ) ) {
                            foreach ( $values as $i => $action ) {
                                $actions->appendChild( $this->definition->createElement( 'action', $action ) );
                            }
                            $install->appendChild( $actions );
                        }
                        break;
                        }
                    case 'field':
                        {
                        $install->appendChild( $this->definition->createElement( 'field', $values ) );
                        break;
                        }
                    case 'permissions':
                        {
                        $permission = $this->definition->createElement( 'permissions' );
                        if ( count( $values ) ) {
                            foreach ( $values as $i => $action ) {
                                $permission->appendChild( $this->definition->createElement( 'permission', $action ) );
                            }
                            $install->appendChild( $permission );
                        }
                        break;
                        }
                    case 'sql':
                        {
                        if ( count( $values ) ) {
                            $queries = $this->definition->createElement( 'sql' );
                            /* first find all created tables */
                            $tables = array();
                            foreach ( $values as $i => $query ) {
                                if ( stristr( $query, 'create table' ) ) {
                                    preg_match( '/spdb_[a-z0-9\-_]*/i', $query, $table );
                                    $tables[ ] = $table[ 0 ];
                                }
                            }
                            if ( count( $tables ) ) {
                                $tbls = $this->definition->createElement( 'tables' );
                                foreach ( $tables as $table ) {
                                    $tbls->appendChild( $this->definition->createElement( 'table', $table ) );
                                }
                                $queries->appendChild( $tbls );
                            }
                            $inserts = array();
                            /* second loop to find all inserts */
                            foreach ( $values as $i => $query ) {
                                if ( stristr( $query, 'insert into' ) ) {
                                    preg_match( '/spdb_[a-z0-9\-_]*/i', $query, $match );
                                    $table = isset( $match[ 0 ] ) ? $match[ 0 ] : null;
                                    /* will be dropped anyway */
                                    if ( in_array( $table, $tables ) || !( $table ) ) {
                                        continue;
                                    }
                                    preg_match( '/\([^\)]*\)/i', $query, $match );
                                    $cols = isset( $match[ 0 ] ) ? str_ireplace( array( '`', '(', ')' ), array( null, null, null ), $match[ 0 ] ) : null;
                                    $cols = explode( ',', $cols );
                                    preg_match( '/VALUES.*\)/i', $query, $match );
                                    $values = isset( $match[ 0 ] ) ? str_ireplace( array( 'VALUES', '(', ')' ), array( null, null, null ), $match[ 0 ] ) : null;
                                    $values = explode( ',', $values );
                                    $cc = count( $cols );
                                    $v = array();
                                    if ( $cc ) {
                                        for ( $i = 0; $i < $cc; $i++ ) {
                                            $v[ trim( str_replace( "'", null, $cols[ $i ] ) ) ] = trim( str_ireplace( "NULL", null, trim( trim( $values[ $i ] ), "\x22\x27" ) ) );
                                        }
                                    }
                                    else {
                                        foreach ( $values as $value ) {
                                            $v[ ] = trim( str_ireplace( "NULL", null, trim( trim( $value ), "\x22\x27" ) ) );
                                        }
                                    }
                                    $inserts[ $table ][ ] = $v;
                                }
                            }
                            if ( count( $inserts ) ) {
                                $ins = $this->definition->createElement( 'queries' );
                                foreach ( $inserts as $table => $cols ) {
                                    foreach ( $cols as $values ) {
                                        $query = $this->definition->createElement( 'insert' );
                                        $attr = $this->definition->createAttribute( 'table' );
                                        $attr->appendChild( $this->definition->createTextNode( $table ) );
                                        $query->appendChild( $attr );
                                        foreach ( $values as $col => $value ) {
                                            if ( ( is_numeric( $col ) ) ) {
                                                $col = 'value';
                                            }
                                            if ( strlen( $value ) > 50 ) {
                                                $node = $query->appendChild( $this->definition->createElement( $col ) );
                                                $node->appendChild( $this->definition->createCDATASection( $value ) );
                                            }
                                            else {
                                                $query->appendChild( $this->definition->createElement( $col, $value ) );
                                            }
                                        }
                                        $ins->appendChild( $query );
                                    }
                                }
                                $queries->appendChild( $ins );
                            }
                            $install->appendChild( $queries );
                        }
                        break;
                        }
                }
            }
            $root = $this->definition->getElementsByTagName( 'SobiProApp' )->item( 0 );
            $root->appendChild( $install );
            $root->normalize();
            $this->definition->appendChild( $root );
        }
    }

    /**
     * @param DOMNodeList $folders
     * @return void
     */
    private function actions( $action, $id )
    {
        $adds = array();
        $actions = array();
        for ( $i = 0; $i < $action->length; $i++ ) {
            $actions[ ] = $action->item( $i )->nodeValue;
            $adds[ $i ] = array( 'pid' => $id, 'onAction' => $action->item( $i )->nodeValue );
        }
        if ( count( $adds ) ) {
            try {
                SPFactory::db()->insertArray( 'spdb_plugin_task', $adds, false, true );
            }
            catch ( SPException $x ) {
                throw new SPException( SPLang::e( 'CANNOT_INSTALL_PLUGIN_DB_ERR', $x->getMessage() ) );
            }
        }
        return $actions;
    }

    private function installed( $id, $type )
    {
        $res = 0;
        try {
            SPFactory::db()->select( 'COUNT( pid )', 'spdb_plugins', array( 'pid' => $id, 'type' => $type ) );
            $res = SPFactory::db()->loadResult();
        }
        catch ( SPException $x ) {
        }
        return $res > 0 ? true : false;
    }

    private function plugin( $id, $type )
    {
        if ( $this->xGetString( 'type' ) != 'update' ) {
            try {
                SPFactory::db()->insert( 'spdb_plugins', array( $id, $this->xGetString( 'name' ), $this->xGetString( 'version' ), $this->xGetString( 'description' ), $this->xGetString( 'authorName' ), $this->xGetString( 'authorUrl' ), $this->xGetString( 'authorEmail' ), 1, $type, null ) );
                if ( $this->xGetString( 'type' ) == 'payment' ) {
                    SPFactory::db()->insert( 'spdb_plugin_task', array( $id, 'PaymentMethodView' ) );
                }
            }
            catch ( SPException $x ) {
                throw new SPException( SPLang::e( 'CANNOT_INSTALL_PLUGIN_DB_ERR', $x->getMessage() ) );
            }
        }
    }

    private function field()
    {
        $node = $this->xGetChilds( 'fieldType' )->item( 0 );
        $tid = $node->attributes->getNamedItem( 'typeId' )->nodeValue;
        $tGroup = $node->attributes->getNamedItem( 'fieldGroup' )->nodeValue;
        $fType = $node->nodeValue;
        try {
            SPFactory::db()->insert( 'spdb_field_types', array( $tid, $fType, $tGroup, null ), true );
        }
        catch ( SPException $x ) {
            throw new SPException( SPLang::e( 'CANNOT_INSTALL_FIELD_DB_ERR', $x->getMessage() ) );
        }
        return $tid;
    }

    /**
     * @param DOMNodeList $folders
     * @return void
     */
    private function files( $folders, $eid, $backup )
    {
        $log = array( 'created' => array(), 'modified' => array() );
        foreach ( $folders as $folder ) {
            $target = $folder->attributes->getNamedItem( 'path' )->nodeValue;
            $basePath = explode( ':', $target );
            $basePath = $basePath[ 0 ];
            $target = str_replace( $basePath . ':', null, $target );
            $target = $this->joinPath( $basePath, $target, $eid );
            if ( !( SPFs::exists( $target ) ) ) {
                if ( SPFs::mkdir( $target ) ) {
                    $log[ 'created' ][ ] = $target;
                }
            }
            foreach ( $folder->childNodes as $child ) {
                // the path within the node value is the "from" path and has to be removed from the target path
                $remove = null;
                if ( strstr( $child->nodeValue, '/' ) ) {
                    $remove = explode( '/', $child->nodeValue );
                    array_pop( $remove );
                    if ( !( is_string( $remove ) ) && count( $remove ) ) {
                        $remove = implode( '/', $remove );
                    }
                    else {
                        $remove = null;
                    }
                }
                switch ( $child->nodeName ) {
                    case 'folder':
                        {
                        /*
                               * the directory iterator is a nice thing but it need lot of time and memory
                               *  - so let's simplify it
                               */
                        // $dir = SPFactory::Instance( 'base.fs.directory', str_replace( DS . DS, DS, $this->root.DS.$child->nodeValue ) );
                        $files = array();
                        $this->travelDir( $this->root . '/' . $child->nodeValue, $files );
                        if ( count( $files ) ) {
                            $this->_d( sprintf( 'List %s', print_r( $files, true ) ) );
                            foreach ( $files as $file ) {
                                $this->_d( sprintf( 'Parsing %s', $files ) );
                                $tfile = Sobi::FixPath( str_replace( $this->root, null, $file ) );
                                $bPath = Sobi::FixPath( $backup . $tfile );
                                if ( $remove && strstr( $tfile, $remove ) && strpos( $tfile, $remove ) < 2 ) {
                                    $tfile = Sobi::FixPath( str_replace( $remove, null, $tfile ) );
                                }
                                $t = Sobi::FixPath( $target . $tfile );
                                if ( SPFs::exists( $t ) ) {
                                    SPFs::copy( $t, $bPath );
                                }
                                if ( SPFs::copy( $file, $t ) ) {
                                    // if this file existed already, do not add it to the log
                                    if ( !( SPFs::exists( $bPath ) ) ) {
                                        $log[ 'created' ][ ] = $t;
                                        $this->_d( sprintf( 'File %s doesn\'t exist', $t ) );
                                    }
                                    else {
                                        $this->_d( sprintf( 'File %s exist and will be backed up', $t ) );
                                        $log[ 'modified' ][ ] = array( 'file' => $t, 'size' => filesize( $t ), 'checksum' => md5_file( $t ), 'backup' => $bPath );
                                    }
                                }
                                else {
                                    $this->_d( sprintf( 'Cannot copy %s to %s', $file, $t ) );
                                }
                            }
                        }
                        break;
                        }
                    case 'file':
                        {
                        $bPath = null;
                        $tfile = $child->nodeValue;
                        // remove the install path
                        if ( $remove && strstr( $child->nodeValue, $remove ) && strpos( $child->nodeValue, $remove ) < 2 ) {
                            $tfile = Sobi::FixPath( str_replace( $remove, null, $child->nodeValue ) );
                        }
                        $t = Sobi::FixPath( $target . $tfile );
                        // if modifying file - backup file
                        if ( SPFs::exists( $t ) ) {
                            $bPath = Sobi::FixPath( "{$backup}/{$child->nodeValue}" );
                            SPFs::copy( $t, $bPath );
                        }
                        if ( SPFs::copy( "{$this->root}/{$child->nodeValue}", $t ) ) {
                            // if this file existed already, do not add it to the log
                            if ( !( SPFs::exists( $bPath ) ) ) {
                                $log[ 'created' ][ ] = $t;
                                $this->_d( sprintf( 'File %s doesn\'t exist', $t ) );
                            }
                            else {
                                // if modifying file - store the current data so when we are going to restore it, we can be sure we are not overwriting some file
                                $log[ 'modified' ][ ] = array( 'file' => $t, 'size' => filesize( $t ), 'checksum' => md5_file( $t ), 'backup' => $bPath );
                                $this->_d( sprintf( 'File %s exist and will be backed up', $t ) );
                            }
                        }
                        break;
                        }
                }
            }
        }
        return $log;
    }

    private function travelDir( $dir, &$files )
    {
        // How stupid I am, stupid, stupid, stupid, stupid, stupid, stupid, ;(
        //$files = array();
        $scan = scandir( $dir );
        $this->_d( sprintf( 'Parsing %s directory', $dir ) );
        if ( count( $scan ) ) {
            foreach ( $scan as $value ) {
                $this->_d( sprintf( 'Child %s in directory %s', $value, $dir ) );
                if ( $value != '.' && $value != '..' ) {
                    if ( is_dir( "{$dir}/{$value}" ) ) {
                        $this->travelDir( "{$dir}/{$value}", $files );
                    }
                    else {
                        $this->_d( sprintf( 'Adding file %s', "{$dir}/{$value}" ) );
                        $files[ ] = "{$dir}/{$value}";
                    }
                }
            }
        }
    }

    private function joinPath( $base, $path, $eid )
    {
        switch ( $base ) {
            case 'home':
                $path = Sobi::FixPath( SPLoader::newDir( "opt.plugins.{$eid}.{$path}" ) . '/' );
                break;
            case 'fields':
                $path = Sobi::FixPath( SPLoader::newDir( "opt.fields.{$path}" ) . '/' );
                break;
            case 'templates':
                $path = Sobi::FixPath( SPLoader::newDir( "usr.templates.{$path}" ) . '/' );
                break;
            case 'config':
                $path = Sobi::FixPath( SPLoader::newDir( "etc.{$path}" ) . '/' );
                break;
            case 'lib':
            case 'ctrl':
            case 'models':
            case 'views':
            case 'js':
            case 'adm':
            case 'front':
            case 'css':
                $path = Sobi::FixPath( SPLoader::newDir( Sobi::FixPath( $path ), $base ) . '/' );
                break;
            case 'img':
                $path = Sobi::FixPath( SPLoader::newDir( Sobi::Cfg( 'images_folder' ) . '.' . $path, 'root' ) );
                break;
        }
        return $path;
    }

    /**
     * @param DOMNodeList $changes
     * @return void
     */
    private function revert( $changes )
    {
        $files = array();
        foreach ( $changes as $file ) {
            if ( $file->hasChildNodes() ) {
                $f = array();
                foreach ( $file->childNodes as $node ) {
                    if ( $node->nodeName != '#text' ) {
                        $f[ $node->nodeName ] = $node->nodeValue;
                    }
                }
                $files[ ] = $f;
            }
        }
        if ( count( $files ) ) {
            foreach ( $files as $file ) {
                if ( strstr( $file[ 'path' ], '/opt/' ) || strstr( $file[ 'path' ], '/field/' ) ) {
                    SPFs::delete( SOBI_ROOT . $file[ 'path' ] );
                    continue;
                }
                if ( SPFs::exists( SOBI_ROOT . $file[ 'path' ] ) ) {
                    if ( md5_file( SOBI_ROOT . $file[ 'path' ] ) ) {
                        if ( SPFs::exists( SOBI_ROOT . $file[ 'backup' ] ) ) {
                            if ( !( SPFs::copy( SOBI_ROOT . $file[ 'backup' ], SOBI_ROOT . $file[ 'path' ] ) ) ) {
                                Sobi::Error( 'installer', SPLang::e( 'Cannot restore file. Cannot copy from "%s" to "%s"', $file[ 'path' ], $file[ 'backup' ] ), SPC::WARNING, 0 );
                            }
                        }
                        else {
                            Sobi::Error( 'installer', SPLang::e( 'Cannot restore file "%s". Backup file does not exist', $file[ 'path' ] ), SPC::WARNING, 0 );
                        }
                    }
                    else {
                        Sobi::Error( 'installer', SPLang::e( 'Cannot restore file "%s". File has been modified since the installation', $file[ 'path' ] ), SPC::WARNING, 0 );
                    }
                }
                else {
                    Sobi::Error( 'installer', SPLang::e( 'Cannot restore file "%s". File does not exist', $file[ 'path' ] ), SPC::WARNING, 0 );
                }
            }
        }
    }

    public function remove()
    {
        $pid = $this->xGetString( 'id' );
        $function = $this->xGetString( 'uninstall' );
        if ( $function ) {
            $obj = explode( ':', $function );
            $function = $obj[ 1 ];
            $obj = $obj[ 0 ];
            return SPFactory::Instance( $obj )->$function( $this->definition );
        }
        $permissions = $this->xGetChilds( 'installLog/permissions/*' );
        if ( $permissions && ( $permissions instanceof DOMNodeList ) ) {
            $permsCtrl =& SPFactory::Instance( 'ctrl.adm.acl' );
            for ( $i = 0; $i < $permissions->length; $i++ ) {
                $perm = explode( '.', $permissions->item( $i )->nodeValue );
                $permsCtrl->removePermission( $perm[ 0 ], $perm[ 1 ], $perm[ 2 ] );
            }
        }

        $mods = $this->xGetChilds( 'installLog/modified/*' );
        if ( $mods && ( $mods instanceof DOMNodeList ) ) {
            $this->revert( $mods );
        }

        $files = $this->xGetChilds( 'installLog/files/*' );
        if ( $files && ( $files instanceof DOMNodeList ) ) {
            for ( $i = 0; $i < $files->length; $i++ ) {
                if ( SPFs::exists( SOBI_ROOT . $files->item( $i )->nodeValue ) ) {
                    SPFs::delete( SOBI_ROOT . $files->item( $i )->nodeValue );
                }
            }
        }

        $actions = $this->xGetChilds( 'installLog/actions/*' );
        if ( $actions && ( $actions instanceof DOMNodeList ) ) {
            for ( $i = 0; $i < $actions->length; $i++ ) {
                try {
                    SPFactory::db()->delete( 'spdb_plugin_task', array( 'pid' => $pid, 'onAction' => $actions->item( $i )->nodeValue ) );
                }
                catch ( SPException $x ) {
                    Sobi::Error( 'installer', SPLang::e( 'Cannot remove plugin task "%s". Db query failed. Error: %s', $actions->item( $i )->nodeValue, $x->getMessage() ), SPC::WARNING, 0 );
                }
            }
            if ( $this->xGetString( 'type' ) == 'payment' ) {
                try {
                    SPFactory::db()->delete( 'spdb_plugin_task', array( 'pid' => $pid, 'onAction' => 'PaymentMethodView' ) );
                }
                catch ( SPException $x ) {
                    Sobi::Error( 'installer', SPLang::e( 'Cannot remove plugin task "PaymentMethodView". Db query failed. Error: %s', $x->getMessage() ), SPC::WARNING, 0 );
                }
            }
        }

        $field = $this->xdef->query( "/{$this->type}/fieldType[@typeId]" );
        if ( $field && $field->length ) {
            try {
                SPFactory::db()->delete( 'spdb_field_types', array( 'tid' => $field->item( 0 )->getAttribute( 'typeId' ) ) );
            }
            catch ( SPException $x ) {
                Sobi::Error( 'installer', SPLang::e( 'CANNOT_REMOVE_FIELD_DB_ERR', $field->item( 0 )->getAttribute( 'typeId' ), $x->getMessage() ), SPC::WARNING, 0 );
            }
        }

        $tables = $this->xGetChilds( 'installLog/sql/tables/*' );
        if ( $tables && ( $tables instanceof DOMNodeList ) ) {
            for ( $i = 0; $i < $tables->length; $i++ ) {
                try {
                    SPFactory::db()->drop( $tables->item( $i )->nodeValue );
                }
                catch ( SPException $x ) {
                    Sobi::Error( 'installer', SPLang::e( 'CANNOT_DROP_TABLE', $tables->item( $i )->nodeValue, $x->getMessage() ), SPC::WARNING, 0 );
                }
            }
        }

        $inserts = $this->xGetChilds( 'installLog/sql/queries/*' );
        if ( $inserts && ( $inserts instanceof DOMNodeList ) ) {
            for ( $i = 0; $i < $inserts->length; $i++ ) {
                $table = $inserts->item( $i )->attributes->getNamedItem( 'table' )->nodeValue;
                $where = array();
                $cols = $inserts->item( $i )->childNodes;
                if ( $cols->length ) {
                    for ( $j = 0; $j < $cols->length; $j++ ) {
                        $where[ $cols->item( $j )->nodeName ] = $cols->item( $j )->nodeValue;
                    }
                }
                try {
                    SPFactory::db()->delete( $table, $where, 1 );
                }
                catch ( SPException $x ) {
                    Sobi::Error( 'installer', SPLang::e( 'CANNOT_DELETE_DB_ENTRIES', $table, $x->getMessage() ), SPC::WARNING, 0 );
                }
            }
        }
        $type = strlen( $this->xGetString( 'type' ) ) ? $this->xGetString( 'type' ) : ( $this->xGetString( 'fieldType' ) ? 'field' : null );
        switch ( $type ) {
            default:
            case 'SobiProApp':
            case 'plugin':
                $t = Sobi::Txt( 'EX.PLUGIN_TYPE' );
                break;
            case 'field':
                $t = Sobi::Txt( 'EX.FIELD_TYPE' );
                break;
            case 'payment':
                $t = Sobi::Txt( 'EX.PAYMENT_METHOD_TYPE' );
                break;
            case 'language':
                $t = Sobi::Txt( 'EX.LANGUAGE_TYPE' );
                break;
            case 'module':
                $t = Sobi::Txt( 'EX.MODULE_TYPE' );
                break;
        }
        try {
            SPFactory::db()->delete( 'spdb_plugins', array( 'pid' => $pid, 'type' => $type ), 1 );
        }
        catch ( SPException $x ) {
            Sobi::Error( 'installer', SPLang::e( 'CANNOT_DELETE_PLUGIN_DB_ERR', $pid, $x->getMessage() ), SPC::ERROR, 0 );
        }

        try {
            SPFactory::db()->delete( 'spdb_plugin_section', array( 'pid' => $pid ) );
        }
        catch ( SPException $x ) {
            Sobi::Error( 'installer', SPLang::e( 'CANNOT_DELETE_PLUGIN_SECTION_DB_ERR', $pid, $x->getMessage() ), SPC::WARNING, 0 );
        }
        SPFs::delete( $this->xmlFile );
        return ucfirst( Sobi::Txt( 'EX.EXTENSION_HAS_BEEN_REMOVED', array( 'type' => $t, 'name' => $this->xGetString( 'name' ) ) ) );
    }
}
