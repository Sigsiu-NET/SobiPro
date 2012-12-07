<?php
/**
 * @version: $Id: template.php 898 2011-03-01 18:29:11Z Radek Suski $
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
 * $Date: 2011-03-01 19:29:11 +0100 (Tue, 01 Mar 2011) $
 * $Revision: 898 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/views/adm/template.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'view', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jun-2010 17:09:48
 */
class SPAdmTemplateView extends SPAdmView
{
	public function display()
	{
		switch ( $this->get( 'task' ) ) {
			case 'edit':
				$this->edit();
				break;
		}
		parent::display();
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function setTitle( $title )
	{
		$name = $this->get( 'template_name' );
		if( !( strlen( $name ) ) ) {
			$name = $this->get( 'file_name' );
			$title = Sobi::Txt( $title, array( 'path' => $name ) );
		}
		else {
			$title = Sobi::Txt( $title, array( 'template' => $name ) );
		}
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		SPFactory::header()->setTitle( $title );
		$this->set( $title, 'site_title');
	}

	private function edit()
	{
		SPFactory::header()->addJsFile( 'codemirror.codemirror' );
		$ext = $this->get( 'file_ext' );
		$jpath = Sobi::Cfg( 'live_site' ).SOBI_LIVE_PATH.'/lib/js/codemirror/';
		$spath = SOBI_MEDIA_LIVE.'/css/codemirror/';
		switch ( strtolower( $ext ) ) {
			case 'xsl':
			case 'xml':
				$jf = '"parsexml.js"';
				$sf = '"'.$spath.'xmlcolors.css"';
				break;
			case 'css':
				$jf = '"parsecss.js"';
				$sf = '"'.$spath.'csscolors.css"';
				break;
			case 'js':
				$jf = '["tokenizejavascript.js", "parsejavascript.js"]';
				$sf = '"'.$spath.'jscolors.css"';
				break;
			case 'php':
				$jf = '["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js", "tokenizephp.js", "parsephp.js", "parsephphtmlmixed.js" ]';
				$sf = '["'.$spath.'xmlcolors.css", "'.$spath.'jscolors.css", "'.$spath.'csscolors.css", "'.$spath.'phpcolors.css"] ';
				break;
			case '':
				break;
		}
		if( isset( $jf ) && isset( $sf ) ) {
			SPFactory::header()->addJsCode( '
				window.addEvent( "domready", function() {
					  var editor = CodeMirror.fromTextArea( "file_content", {
					    	height: "1200px",
					    	parserfile: '.$jf.',
					    	stylesheet: '.$sf.',
					   	 	path: "'.$jpath.'",
					    	continuousScanning: 500,
					    	lineNumbers: true
					  });
					  var spSave = $$( "#toolbar-save a" )[ 0 ];
					  spSaveFn = spSave.onclick;
					  spSave.onclick = null;
					  $( "toolbar-save" ).addEvent( "click", function() {
					  		$( "file_content" ).value = editor.getCode();
							spSaveFn();
					  } );
				  });
			' );
			SPFactory::header()->addCSSCode( '
			      .CodeMirror-line-numbers {
			        width: 2.2em;
			        color: #aaa;
			        background-color: #eee;
			        text-align: right;
			        padding-right: .3em;
			        font-size: 10pt;
			        font-family: monospace;
			        padding-top: .4em;
			      }
			' );
		}
	}
}
