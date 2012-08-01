<?php
/**
 * @version: $Id: bank_transfer.php 2614 2012-07-20 13:50:40Z Radek Suski $
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
 * $Date: 2012-07-20 15:50:40 +0200 (Fri, 20 Jul 2012) $
 * $Revision: 2614 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/ctrl/adm/bank_transfer.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'config', true );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 06-Aug-2010 15:38:15
 */
class SPPaymentBt extends SPConfigAdmCtrl
{
    /**
     * @var string
     */
    protected $_defTask = 'config';

    public function execute()
    {
        $this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
        switch ( $this->_task ) {
            case 'config':
                $this->screen();
                Sobi::ReturnPoint();
                break;
            case 'save':
                $this->save();
                break;
            default:
                Sobi::Error( 'SPPaymentBt', 'Task not found', SPC::NOTICE, 404, __LINE__, __FILE__ );
                break;
        }
    }

    protected function save()
    {
        if ( !( SPFactory::mainframe()->checkToken() ) ) {
            Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
        }
        $data = SPRequest::string( 'bankdata', null, true );
        $data = array(
            'key' => 'bankdata',
            'value' => $data,
            'type' => 'application',
            'id' => Sobi::Section(),
            'section' => Sobi::Section()
        );
        try {
            SPLang::saveValues( $data );
        } catch ( SPException $x ) {
            $msg = SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() );
            Sobi::Error( 'SPPaymentBt', $msg, SPC::WARNING, 0, __LINE__, __FILE__ );
            Sobi::Redirect( SPMainFrame::getBack(), $msg, SPC::ERROR_MSG, true );
        }
        Sobi::Redirect( SPMainFrame::getBack(), Sobi::Txt( 'MSG.ALL_CHANGES_SAVED' ) );
    }

    private function screen()
    {
        $bankdata = SPLang::getValue( 'bankdata', 'application', Sobi::Section() );
        if ( !( strlen( $bankdata ) ) ) {
            SPLang::getValue( 'bankdata', 'application' );
        }
        $view = $this->getView( 'bank_transfer' );
        $tile = Sobi::Txt( 'APP.BANK_TRANSFER_NAME' );
        $view->assign( $tile, 'title' );
        $view->assign( $bankdata, 'bankdata' );
        $view->loadConfig( 'extensions.bank_transfer' );
        $view->setTemplate( 'extensions.bank_transfer' );
        $view->display();
    }
}

?>
