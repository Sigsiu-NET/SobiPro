<?php
/**
 * @version: $Id: sobipro.php 2354 2012-04-17 06:45:16Z Radek Suski $
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
 * $Date: 2012-04-17 08:45:16 +0200 (Tue, 17 Apr 2012) $
 * $Revision: 2354 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/ctrl/sobipro.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:35:35 PM
 */
final class SobiProCtrl
{
    /**
     * @var SPMainFrame
     */
    private $_mainframe = null;
    /**
     * @var SPConfig
     */
    private $_config = null;
    /**
     * @var int
     */
    private $_mem = 0;
    /**
     * @var int
     */
    private $_time = 0;
    /**
     * @var int
     */
    private $_section = 0;
    /**
     * @var string
     */
    private $_task = 0;
    /**
     * @var int
     */
    private $_sid = 0;
    /**
     * @var SPUser
     */
    private $_user = null;
    /**
     * @var SPController - can be also array of
     */
    private $_ctrl = null;
    /**
     * @var mixed
     */
    private $_model = null;
    /**
     * @var string
     */
    private $_type = 'component';
    /**
     * @var int
     */
    private $_err = 0;
    /**
     * @var int
     */
    private $_deb = 0;

    /**
     * @param string $task
     * @return SobiPro
     */
    function __construct( $task )
    {
        $this->_mem = memory_get_usage();
        $this->_time = microtime( true );
        SPLoader::loadClass( 'base.exception' );
        set_error_handler( 'SPExceptionHandler' );
        $this->_err = ini_set( 'display_errors', 'on' );
        $this->_task = $task;

        /* load all needed classes */
        SPLoader::loadClass( 'base.const' );
        SPLoader::loadClass( 'base.factory' );
        SPLoader::loadClass( 'base.object' );
        SPLoader::loadClass( 'base.filter' );
        SPLoader::loadClass( 'base.request' );
        SPLoader::loadClass( 'sobi' );
        SPLoader::loadClass( 'base.config' );
        SPLoader::loadClass( 'cms.base.lang' );

        /* get sid if any */
        $this->_sid = SPRequest::sid();

        /* determine section */
        $access = $this->getSection();

        /* initialise mainframe interface to CMS */
        $this->_mainframe = & SPFactory::mainframe();

        /* initialise config */
        $this->createConfig();

        ini_set( 'display_errors', Sobi::Cfg( 'debug.display_errors', false ) );
        $this->_deb = error_reporting( Sobi::Cfg( 'debug.level', 0 ) );

        /* trigger plugin */
        Sobi::Trigger( 'Start' );
        /* initialise translator and load language files */
        SPLang::setLang( Sobi::Lang( false ) );
        try {
            SPLang::registerDomain( 'site' );
        }
        catch ( SPException $x ) {
            Sobi::Error( 'CoreCtrl', SPLang::e( 'Cannot register language domain: %s.', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
        }

        if ( !( $access ) ) {
            if ( Sobi::Cfg( 'redirects.section_enabled', false ) ) {
                $redirect = Sobi::Cfg( 'redirects.section_url', null );
                $msg = Sobi::Cfg( 'redirects.section_msg', SPLang::e( 'UNAUTHORIZED_ACCESS', SPRequest::task() ) );
                $msgtype = Sobi::Cfg( 'redirects.section_msgtype', 'message' );
                Sobi::Redirect( Sobi::Url( $redirect ), Sobi::Txt( $msg ), $msgtype, true );
            }
            else {
                SPFactory::mainframe()->runAway( 'You have no permission to access this site', 403, null, true );
            }
        }

        /* load css and js files */
        SPFactory::header()
                ->addCssFile( 'sobipro' )
                ->addJsFile( 'sobipro' );

        $sectionName = SPLang::translateObject( $this->_section, 'name', 'section' );
        SPFactory::registry()->set( 'current_section_name', $sectionName[ $this->_section ][ 'value' ] );

        $start = array( $this->_mem, $this->_time );
        SPFactory::registry()->set( 'start', $start );
        /* check if it wasn't plugin custom task */
        if ( !( Sobi::Trigger( 'custom', 'task', array( &$this, SPRequest::task() ) ) ) ) {
            /* if not, start to route */
            try {
                $this->route();
            }
            catch ( SPException $x ) {
                Sobi::Error( 'CoreCtrl', SPLang::e( 'Cannot route: %s.', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
            }
        }
        return true;
    }

    /**
     * initialise config object
     * @return void
     */
    private function createConfig()
    {
        $this->_config = & SPFactory::config();
        /* load basic configuration settings */
        $this->_config->addIniFile( 'etc.config', true );
        $this->_config->addTable( 'spdb_config', $this->_section );
        /* initialise interface config setting */
        $this->_mainframe->getBasicCfg();
        /* initialise config */
        $this->_config->init();
    }

    /**
     * get the right section
     * @return void
     */
    private function getSection()
    {
        $sid = SPRequest::int( 'pid' ) ? SPRequest::int( 'pid' ) : $this->_sid;
        $db =& SPFactory::db();
        $section = null;
        if ( $sid ) {
            $section = & SPFactory::object( $sid );
            if ( $section && $section->oType == 'section' ) {
                $this->_section = $section->id;
                $state = $section->state;
            }
            else {
                $path = array();
                $id = $sid;
                while ( $id > 0 ) {
                    try {
                        $db->select( 'pid', 'spdb_relations', array( 'id' => $id ) );
                        $id = $db->loadResult();
                        if ( $id ) {
                            $path[ ] = ( int )$id;
                        }
                    }
                    catch ( SPException $x ) {
                        Sobi::Error( 'CoreCtrl', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
                    }
                }
                if ( count( $path ) ) {
                    $path = array_reverse( $path );
                    $this->_section = $path[ 0 ];
                    $section = & SPFactory::object( $this->_section );
                    $state = $section->state;
                }
                else {
                    Sobi::Error( 'CoreCtrl', SPLang::e( 'PAGE_NOT_FOUND' ), 0, 404 );
                    exit;
                }
            }
        }
        else {
            $this->_section = '0';
        }
        /* set current section in the registry */
        SPFactory::registry()->set( 'current_section', $this->_section );

        if ( !( isset( $state ) ) ) {
            $section = SPFactory::object( $sid );
            if ( isset( $section->state ) ) {
                $state = $section->state;
            }
            else {
                $state = false;
            }
        }
        /* if this section isn't published */
        if ( !$state ) {
            if ( !( Sobi::Can( 'section.access.any' ) ) ) {
                return false;
            }
        }
        if ( $section && $section->id == SPRequest::sid() ) {
            $this->_model = $section;
        }
        elseif ( SPRequest::sid() ) {
            $this->_model = & SPFactory::object( SPRequest::sid() );
        }
        return true;
    }


    /**
     * Try to find out what we have to do
     *     - If we have a task - parse task
     *  - If we don't have a task, but sid, we are going via default object task
     *  - Otherwise it could be only the frontpage
     * @return void
     */
    private function route()
    {
        /* if we have a task */
        if ( $this->_task && $this->_task != 'panel' ) {
            if ( !( $this->routeTask() ) ) {
                throw new SPException( SPLang::e( 'Cannot interpret task "%s"', $this->_task ) );
            }
        }
        /* if there is no task - execute default task for object */
        elseif ( $this->_sid ) {
            if ( !( $this->routeObj() ) ) {
                throw new SPException( SPLang::e( 'Cannot route object with id "%d"', $this->_sid ) );
            }
        }
        /* otherwise show the frontpage */
        else {
            $this->frontpage();
        }
    }

    /**
     * Route by task
     * @return bool
     */
    private function routeTask()
    {
        $r = true;
        if ( strstr( $this->_task, '.' ) ) {
            /* task consist of the real task and the object type */
            $task = explode( '.', $this->_task );
            $obj = trim( $task[ 0 ] );
            $task = trim( $task[ 1 ] );
            if ( $obj == 'list' || $obj == 'ls' ) {
                $obj = 'listing';
            }
            /* load the controller class definition and get the class name */
            $ctrl = SPLoader::loadController( $obj );

            /* route task for multiple objects - e.g removing or publishing elements from a list */
            /* and there was some of multiple sids */
            if ( count( SPRequest::arr( 'sid' ) ) || count( SPRequest::arr( 'c_sid' ) ) || count( SPRequest::arr( 'e_sid' ) ) ) {
                $sid = key_exists( 'sid', $_REQUEST ) ? 'sid' : ( key_exists( 'c_sid', $_REQUEST ) ? 'c_sid' : 'e_sid' );
                if ( count( SPRequest::arr( $sid ) ) ) {
                    /* @var SPdb $db */
                    $db =& SPFactory::db();
                    try {
                        $db->select( '*', 'spdb_object', array( 'id' => SPRequest::arr( $sid ) ) );
                        $objects = $db->loadObjectList();
                    }
                    catch ( SPException $x ) {
                        Sobi::Error( 'CoreCtrl', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
                        $r = false;
                    }
                    if ( count( $objects ) ) {
                        $this->_ctrl = array();
                        foreach ( $objects as $object ) {
                            $this->_ctrl[ ] = $this->extendObj( $object, $obj, $ctrl, $task );
                        }
                    }
                }
                else {
                    Sobi::Error( 'CoreCtrl', SPLang::e( 'IDENTIFIER_EXPECTED' ), SPC::ERROR, 500, __LINE__, __FILE__ );
                    $r = false;
                }
            }
            else {
                /* set controller and model */
                try {
                    $ctrl = new $ctrl();
                    $this->setController( $ctrl );
                    if ( $ctrl instanceof SPController ) {
                        $model = SPLoader::loadModel( $obj, false, false );
                        if ( $model ) {
                            $this->_ctrl->setModel( $model );
                        }
                    }
                }
                catch ( SPException $x ) {
                    Sobi::Error( 'CoreCtrl', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
                }
                if ( $this->_sid ) {
                    $this->_model = & SPFactory::object( $this->_sid );
                }

                /* if the basic object we got from the #getSection method is the same one ... */
                if ( ( $this->_model instanceof stdClass ) && ( $this->_model->oType == $obj ) ) {
                    /*... extend the empty model of these data we've allredy got */
                    $this->_ctrl->extend( $this->_model );
                }
                /* ... and so on... */
                $this->_ctrl->setTask( $task );
            }
        }
        else {
            /** Special controllers not inherited from object and without model */
            $task = $this->_task;
            try {
                $ctrl = SPLoader::loadController( $task );
            }
            catch( SPException $x ) {
                Sobi::Error( 'CoreCtrl', SPLang::e( 'PAGE_NOT_FOUND' ), 0, 404 );
            }
            try {
                $this->setController( new $ctrl() );
                $this->_ctrl->setTask( null );
            }
            catch ( SPException $x ) {
                Sobi::Error( 'CoreCtrl', SPLang::e( 'Cannot set controller. %s.', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
            }
        }
        return $r;
    }

    /**
     * @return bool
     */
    private function routeObj()
    {
        try {
            $ctrl = SPLoader::loadController( $this->_model->oType );
            $ctrl = new $ctrl();
            if ( $ctrl instanceof SPController ) {
                $this->setController( $ctrl );
                if ( $this->_model->id == SPRequest::sid() ) {
                    // just to use the pre-fetched entry
                    if ( $this->_model->oType == 'entry' ) {
                        $e = SPFactory::Entry( $this->_model->id );
                        // object has been stored anyway in the SPFactory::object method
                        // and also already initialized
                        // therefore it will be now rewritten and because the init flag is set to true
                        // the name is getting lost
                        // $e->extend( $this->_model );
                        $this->_ctrl->setModel( $e );
                    }
                    else {
                        $this->_ctrl->setModel( SPLoader::loadModel( $this->_model->oType ) );
                        $this->_ctrl->extend( $this->_model );
                    }
                }
                else {
                    $this->_ctrl->extend( SPFactory::object( SPRequest::sid() ) );
                }
                $this->_ctrl->setTask( $this->_task );
            }
        }
        catch ( SPException $x ) {
            Sobi::Error( 'CoreCtrl', SPLang::e( 'Cannot set controller. %s.', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
        }
        return true;
    }

    /**
     * @param stdClass $obj
     * @param string $ctrlClass
     * @param string $objType
     * @param string $task
     * @return SPControl
     */
    private function & extendObj( $obj, $objType, $ctrlClass, $task = null )
    {
        if ( $objType == $obj->oType ) {
            if ( $ctrlClass ) {
                /* create controler */
                $ctrl = new $ctrlClass();
                /* set model */
                $ctrl->setModel( SPLoader::loadModel( $objType ) );
                /* extend model of basic data */
                $ctrl->extend( $obj );
                /* set task */
                if ( strlen( $task ) ) {
                    $ctrl->setTask( $task );
                }
            }
            else {
                Sobi::Error( 'CoreCtrl', SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
            }
        }
        return $ctrl;
    }

    /**
     * @return void
     */
    private function frontpage()
    {
        $ctrl = SPLoader::loadController( 'front' );
        $this->setController( new $ctrl() );
        $this->_ctrl->setTask( SPRequest::task() );
    }

    /**
     * Executes the controller task
     * @return void
     */
    public function execute()
    {
        try {
            if ( is_array( $this->_ctrl ) ) {
                foreach ( $this->_ctrl as &$c ) {
                    $c->execute();
                }
            }
            else {
                if ( $this->_ctrl instanceof SPControl ) {
                    $this->_ctrl->execute();
                }
                else {
                    Sobi::Error( 'CoreCtrl', SPLang::e( 'No controller to execute' ), SPC::ERROR, 500, __LINE__, __FILE__ );
                }
            }
        }
        catch ( SPException $x ) {
            Sobi::Error( 'CoreCtrl', SPLang::e( '%s', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
        }
        /* send header data etc ...*/
        SPFactory::header()->send();
        SPMainFrame::endOut();
        Sobi::Trigger( 'End' );
        /* redirect if any redyriect has been set */
        SPMainFrame::redirect();
        ini_set( 'display_errors', $this->_err );
        error_reporting( $this->_deb );
        restore_error_handler();
    }

    /**
     * @return void
     */
    public function setController( &$ctrl )
    {
        $this->_ctrl = & $ctrl;
    }
}
