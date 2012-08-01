<?php
/**
 * @version: $Id: requirements.php 1797 2011-08-09 09:49:05Z Radek Suski $
 * @package: SobiPro Template
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2011-08-09 11:49:05 +0200 (Tue, 09 Aug 2011) $
 * $Revision: 1797 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Admin/config/requirements.php $
 */
if( SPRequest::int( 'init' ) ) {
	$b = Sobi::Url( null, true );
}
else {
	$b = Sobi::Url( 'config', true );
}
$d = Sobi::Url( 'requirements.download', true );
SPFactory::header()->addJsCode( 'var spReq ="'.Sobi::Url( array( 'task' => 'requirements' ), true ).'"; var spHome ="'.$b.'"; var spDownl ="'.$d.'";' );
SPFactory::header()->addJsFile( 'advajax' );
SPFactory::header()->addJsFile( 'requirements', true );
SPFactory::header()->addCSSCode('table.adminlist tbody tr td {height:20px!important;}');
SPRequest::set( 'hidemainmenu', 1 );
$row = 0;
?>
<div style="min-width:100px; margin: 15px;" id="progrStat">
	<div style="min-width: 100px; height: 35px; margin-left: 10px;">
		<h1 style="float:left;">
			<?php $this->txt( 'REQ.CHECKING_REQUIREMENTS' ); ?>
			<span id="StatMsg"></span><span id="StatSp" style="margin-left: 5px;"></span>
		</h1>
		<div style="padding: 2px; margin-left: 5px; display:none; float: right;" id="spReqBtCont">
			<?php
				if( !( SPRequest::int( 'init' ) ) ) {
					$this->field( 'submit', 'end', 'translate:[REQ.NEXT_BT]', array( 'class' => 'button', 'onclick' => 'SP_ReqEnd()', 'disabled' => 'disabled', 'id' => 'spReqBt' ) );
				}
			?>
			<?php $this->field( 'submit', 'refresh', 'translate:[REQ.CHECK_AGAIN_BT]', array( 'class' => 'button', 'onclick' => 'SP_ReqAgain()', 'disabled' => 'disabled', 'id' => 'spRReqBt' ) ); ?>
			<?php $this->field( 'submit', 'download', 'translate:[REQ.DOWNLOAD_BT]', array( 'class' => 'button', 'onclick' => 'SP_Download()', 'disabled' => 'disabled', 'id' => 'spRDownBt' ) ); ?>
		</div>
	</div>
	<div style="min-width: 100px; height: 35px; margin-left: 10px;clear: both;">
		<?php $this->show( 'icons.ok' ); ?>&nbsp;<?php $this->txt( 'REQ.COMPLY_REQ' ); ?>&nbsp;|&nbsp;
		<?php $this->show( 'icons.warning' ); ?>&nbsp;<?php $this->txt( 'REQ.NOT_RECOMMENDED' ); ?>&nbsp;|&nbsp;
		<?php $this->show( 'icons.error' ); ?>&nbsp;<?php $this->txt( 'REQ.DOES_NOT_COMPLY' ); ?>
	</div>
	<table class="adminlist" cellspacing="1">
		<colgroup>
			<col width="3%" align="center">
			<col width="20%" align="center">
			<col width="45%" align="center">
		</colgroup>
		<thead>
			<tr>
				<th colspan="3" style="text-align: left;"><?php $this->txt( 'REQ.CMS' ); ?></th>
			</tr>
		</thead>
		<tr class="row0">
			<td><div style="text-align:center;" id="icms"></div></td>
			<td><?php $this->txt( 'REQ.CMS_VERSION' ); ?></td>
			<td>
				<div class="statInner" id="cms"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="icmsFtp"></div></td>
			<td><?php $this->txt( 'REQ.FTP_LAYER' ); ?></td>
			<td>
				<div class="statInner" id="cmsFtp"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="icmsEncoding"></div></td>
			<td><?php $this->txt( 'REQ.ENCODING' ); ?></td>
			<td>
				<div class="statInner" id="cmsEncoding"></div>
			</td>
		</tr>
	</table>

	<table class="adminlist" cellspacing="1">
		<colgroup>
			<col width="3%" align="center">
			<col width="20%" align="center">
			<col width="45%" align="center">
		</colgroup>
		<thead>
			<tr>
				<th colspan="3" style="text-align: left;"><?php $this->txt( 'REQ.WEB_SERVER' ); ?></th>
			</tr>
		</thead>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iwebServer"></div></td>
			<td><?php $this->txt( 'REQ.WEB_SERVER_VERSION_NAME' ); ?></td>
			<td>
				<div class="statInner" id="webServer"></div>
			</td>
		</tr>
	</table>

	<table class="adminlist" cellspacing="1">
		<colgroup>
			<col width="3%" align="center">
			<col width="20%" align="center">
			<col width="45%" align="center">
		</colgroup>
		<thead>
			<tr>
				<th colspan="3" style="text-align: left;"><?php $this->txt( 'REQ.PHP_SETTINGS' ); ?></th>
			</tr>
		</thead>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iphpVersion"></div></td>
			<td><?php $this->txt( 'REQ.PHP_VERSION' ); ?></td>
			<td>
				<div class="statInner" id="phpVersion"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="isafeMode"></div></td>
			<td><?php $this->txt( 'REQ.SAFE_MODE' ); ?></td>
			<td>
				<div class="statInner" id="safeMode"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iregisterGlobals"></div></td>
			<td><?php $this->txt( 'REQ.REGISTER_GLOBALS' ); ?></td>
			<td>
				<div class="statInner" id="registerGlobals"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iiniParse"></div></td>
			<td><?php $this->txt( 'REQ.PARSE_INI' ); ?></td>
			<td>
				<div class="statInner" id="iniParse"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="igDlib"></div></td>
			<td><?php $this->txt( 'REQ.GD_LIBRARY' ); ?></td>
			<td>
				<div class="statInner" id="gDlib"></div>
			</td>
		</tr>
		<!--
		<tr class="row<?php //echo ++$row; ?>">
			<td><div style="text-align:center;" id="iexif"></div></td>
			<td><?php $this->txt( 'REQ.EXIF' ); ?></td>
			<td>
				<div class="_statInner" id="exif"></div>
			</td>
		</tr>
		-->
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iDOM"></div></td>
			<td><?php $this->txt( 'REQ.DOM' ); ?></td>
			<td>
				<div class="statInner" id="DOM"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iXSL"></div></td>
			<td><?php $this->txt( 'REQ.XSL' ); ?></td>
			<td>
				<div class="statInner" id="XSL"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iXPath"></div></td>
			<td><?php $this->txt( 'REQ.XPATH' ); ?></td>
			<td>
				<div class="statInner" id="XPath"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="itidy"></div></td>
			<td><?php $this->txt( 'REQ.TIDY' ); ?></td>
			<td>
				<div class="statInner" id="tidy"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iSPL"></div></td>
			<td><?php $this->txt( 'REQ.SPL' ); ?></td>
			<td>
				<div class="statInner" id="SPL"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="ifilter"></div></td>
			<td><?php $this->txt( 'REQ.FILTER' ); ?></td>
			<td>
				<div class="statInner" id="filter"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iPCRE"></div></td>
			<td><?php $this->txt( 'REQ.REPC' ); ?></td>
			<td>
				<div class="statInner" id="PCRE"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iCURL"></div></td>
			<td><?php $this->txt( 'REQ.CURL' ); ?></td>
			<td>
				<div class="statInner" id="CURL"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iSQLite"></div></td>
			<td><?php $this->txt( 'REQ.SQLITE' ); ?></td>
			<td>
				<div class="statInner" id="SQLite"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iSOAP"></div></td>
			<td><?php $this->txt( 'REQ.SOAP' ); ?></td>
			<td>
				<div class="statInner" id="SOAP"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iOpenSSL"></div></td>
			<td><?php $this->txt( 'REQ.OPENSSL' ); ?></td>
			<td>
				<div class="statInner" id="OpenSSL"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="imaxExecutionTime"></div></td>
			<td><?php $this->txt( 'REQ.MAX_EXE_TIME' ); ?></td>
			<td>
				<div class="statInner" id="maxExecutionTime"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="imemoryLimit"></div></td>
			<td><?php $this->txt( 'REQ.MEMORY_LIMIT' ); ?></td>
			<td>
				<div class="statInner" id="memoryLimit"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="ijson"></div></td>
			<td><?php $this->txt( 'REQ.JSON' ); ?></td>
			<td>
				<div class="statInner" id="json"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iZipArchive"></div></td>
			<td><?php $this->txt( 'REQ.ZIPARCHIVE' ); ?></td>
			<td>
				<div class="statInner" id="ZipArchive"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="ireflection"></div></td>
			<td><?php $this->txt( 'REQ.REFLECTION' ); ?></td>
			<td>
				<div class="statInner" id="reflection"></div>
			</td>
		</tr>
		<!--
		<tr class="row<?php //echo ++$row; ?>">
			<td><div style="text-align:center;" id="iCalendar"></div></td>
			<td><?php $this->txt( 'REQ.CALENDAR' ); ?></td>
			<td>
				<div class="_statInner" id="Calendar"></div>
			</td>
		</tr>
		<tr class="row<?php //echo ++$row; ?>">
			<td><div style="text-align:center;" id="iPSpell"></div></td>
			<td><?php $this->txt( 'REQ.PSPELL' ); ?></td>
			<td>
				<div class="_statInner" id="PSpell"></div>
			</td>
		</tr>
		-->
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iexec"></div></td>
			<td><?php $this->txt( 'REQ.SYSTEM' ); ?></td>
			<td>
				<div class="statInner" id="exec"></div>
			</td>
		</tr>
		<!--
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="iPEAR"></div></td>
			<td><?php $this->txt( 'REQ.PEAR' ); ?></td>
			<td>
				<div class="_statInner" id="PEAR"></div>
			</td>
		</tr>
		-->
	</table>

	<table class="adminlist" width="100%">
		<colgroup>
			<col width="3%" align="center">
			<col width="20%" align="center">
			<col width="45%" align="center">
		</colgroup>
		<thead>
			<tr>
				<th colspan="3" style="text-align: left;"><?php $this->txt( 'REQ.MYSQL_SETTINGS' ); ?></th>
			</tr>
		</thead>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="imySQLversion"></div></td>
			<td><?php $this->txt( 'REQ.MYSQL_VERSION' ); ?></td>
			<td>
				<div class="statInner" id="mySQLversion"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="imySQLcharset"></div></td>
			<td><?php $this->txt( 'REQ.MYSQL_CHARSET' ); ?></td>
			<td>
				<div class="statInner" id="mySQLcharset"></div>
			</td>
		</tr>
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="icreateProcedure"></div></td>
			<td><?php $this->txt( 'REQ.MYSQL_PROCEDURE' ); ?></td>
			<td>
				<div class="statInner" id="createProcedure"></div>
			</td>
		</tr>
		<!--
		<tr class="row<?php //echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="icreateFunction"></div></td>
			<td><?php $this->txt( 'REQ.MYSQL_FUNCTION' ); ?></td>
			<td>
				<div class="_statInner" id="createFunction"></div>
			</td>
		</tr>
		-->
		<tr class="row<?php echo ++$row%2; ?>">
			<td><div style="text-align:center;" id="icreateView"></div></td>
			<td><?php $this->txt( 'REQ.MYSQL_VIEW' ); ?></td>
			<td>
				<div class="statInner" id="createView"></div>
			</td>
		</tr>
	</table>
</div>
