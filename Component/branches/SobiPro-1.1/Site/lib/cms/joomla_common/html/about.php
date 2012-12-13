<?php
/**
 * @version: $Id: about.php 2334 2012-03-28 09:39:05Z Sigrid Suski $
 * @package: SobiPro
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2012-03-28 11:39:05 +0200 (Wed, 28 Mar 2012) $
 * $Revision: 2334 $
 * $Author: Sigrid Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla_common/html/about.php $
 */
jimport( 'joomla.html.pane' );
/**
 * @author neo
 *
 */
class SPJoomlaCredits
{
	public function __construct() {}

	public function update( $str )
	{
		/*
		 * This function should only check whether the "copyright" notice from the administrator's area has been removed,
		 * which would be a clear violation of the GPL license.
		 * It is, I think it clear that we do not want that this functionality is easy to find using a plain text search.
		 * Unfortunately some people piss them off because of that. Especially people who apparently are not even using SobiPro.
		 * Greetings to Ken at this point (Is that some kind of sick revenge?).
		 * What bugs me in the whole story is the fact that it is not even illegal according to ToS of JED
		 */
//		if( stristr( $str, 'Fatal' ) ) {
//			return $str;
//		}
//		$arr = SPConfig::unserialize( 'YToxOntzOjI6ImNw'.'IjtzOjM2OiIvJmN'.'vcHk7IFxkezR9'.'IFNpZ3NpdS5OR'.'VQgR21iSCZuYnNwOy8iO30=' );
//		if( !( preg_match( $arr[ 'cp' ], $str  ) ) ) {
//			$arr = SPConfig::unserialize( 'YToxOntzOjE6ImUiO3M6MjE6IkdQTCBMaWNlbnNlIFZpb2xhdGlvbiI7fQ==' );
//			JFactory::getApplication()->redirect( 'index.php', $arr[ 'e' ] , 'error' );
//		}
		return $str;
	}

	/**
	 * @param SPAdmView $view
	 * @return void
	 */
	public function add( SPAdmView &$view )
	{
//		SPFactory::header()->addJsFile( 'front', true );
		JFactory::getLanguage()->load( 'com_sobipro.about', JPATH_ADMINISTRATOR, 'en-GB' );
		if( JFactory::getConfig()->getValue( 'language' ) != 'en-GB' ) {
			JFactory::getLanguage()->load( 'com_sobipro.about' );
		}
		$view->assign( $this->about(), 'about' );
	}

	private function about()
	{
		$tabs = array();
		$tabs[ 'about' ] = $this->aboutPane();
		$tabs[ 'updates' ] = $this->updPane();
		$tabs[ 'news' ] = $this->newsPane();
		$tabs[ 'credits' ] = $this->creditsPane();
		$tabs[ 'license' ] = $this->licensePane();
		$out = null;
		$pane = new SPPane();
		$out .= $pane->startPane( 'content-pane' );
		foreach ( $tabs as $pid => $p ) {
			$out .= $pane->startPanel( Sobi::Txt( 'ABOUT.'.strtoupper( $pid ).'_PANE' ), 'sp-panel-'.$pid );
			$out .= $p;
			$out .= $pane->endPanel();
		}
		$out .= $pane->endPane();
		return $out;
	}

	private function licensePane()
	{
		$out = null;
		$lang = Sobi::Lang( false );
		$file = SOBI_ADM_PATH.DS.'about'.DS.$lang.DS.'license.html';
		if( !( SPFs::exists( $file ) ) ) {
			$file = SOBI_ADM_PATH.DS.'about'.DS.'en-GB'.DS.'license.html';
		}
		$out .= '<div style="padding: 3px 10px 0 10px; font-size: 12px;" class="SPCpTabs">';
		$out .= SPFs::read( $file );
		$out .= '&nbsp;</div>';
		return $out;
	}

	private function aboutPane()
	{
		$out = null;
		$out .= '<div style="padding:10px; font-size:12px;" class="SPCpTabs">';
		$out .= '<div style="float:right; margin: 5px; text-align:center;">';
		$out .= '<a href="http://www.Sigsiu.NET" target="_blank" title="Sigsiu.NET Software Development"><img src="../media/sobipro/sobipro.png" alt="Sigsiu.NET Software Development" style="border-style:none;" /></a>';
		$out .= '<br/>';
		$out .= '<br/>';
		$out .= '<a href="http://www.Sigsiu.NET" target="_blank" title="Sigsiu.NET Software Development"><img src="../media/sobipro/sobipro-main.png" alt="Sigsiu.NET Software Development" style="border-style:none;" /></a>';
		$out .= '</div>';
		$out .= '<strong>'.Sobi::Txt( 'ABOUT.SP_DESC' ).'</strong>';
		$out .= '<br/>';
		$out .= '<br/>';
		$out .= Sobi::Txt( 'ABOUT.DEVELOPED_AND_DESIGNED', '<a href="http://sigrid.suski.eu" target="_blank" title="Sigrid Suski">Sigrid Suski</a>', '<a href="http://radek.suski.eu" target="_blank" title="Radek Suski">Radek Suski</a>' );
		$out .= '<br/>';
		$out .= '<br/>';
		$out .= '<div style="margin-bottom: 2px;">';
		$out .= '<strong>'.Sobi::Txt( 'ABOUT.SP_CLUB' ).'</strong>';
		$out .= '<p>'.Sobi::Txt( 'ABOUT.SP_CLUB_DESC', '<a href="http://SobiPro.Sigsiu.NET" target="_blank" >SobiPro Club</a>' ).'</p>';
		$out .= '<ul>';
		$out .= '<li>'.Sobi::Txt( 'ABOUT.SP_CLUB_WEB', '<a href="http://SobiPro.Sigsiu.NET" target="_blank" >SobiPro Club</a>' ).'</li>';
		$out .= '<li><a href="https://forum.Sigsiu.NET" target="_blank" >Sigsiu.NET Forum</a></li>';
		$out .= '</ul>';
		$out .= '</div>';
		$out .= '<a style="float:left;" href="http://SobiPro.Sigsiu.NET" target="_blank" title="The SobiPro Club - Support, Documentation, Applications and much more .... "><img src="../media/sobipro/join-the-club.png" alt="The SobiPro Club - Support, Documentation, Applications and much more .... " style="border-style:none;" /></a>';
		$out .= '<div style="text-align: right; margin-bottom: 2px;">';
		$out .= '<br/>';
		$out .= '</div>';
		$out .= '<div style="clear:both;"></div>';
		$out .= '<div style="float:right; margin: 1px; text-align:center;">';
		$out .= '&copy; 2012 Sigsiu.NET GmbH&nbsp;</div>';
		$out .= '</div>';
		return $out;
	}

	private function newsPane()
	{
		$out = null;
		$out .= '<div style="padding-left:10px; padding-top:3px;" class="SPCpTabs"><div class="SPCpTabNews">';
		$out .= $this->getNews();
		$out .= '&nbsp;</div></div>';
		return $out;
	}

	private function getNews()
	{
		$out = null;
		$path = SPLoader::path( 'etc.news', 'front', false, 'xml' );
		if( SPFs::exists( $path  ) && ( time() - filemtime( $path  ) < ( 60 * 60 * 12 ) ) ) {
			$content = SPFs::read( SPLoader::path( 'etc.news', 'front', false, 'xml' ) );
		}
		else {
			$connection = SPFactory::Instance( 'services.remote' );
			$news = 'http://www.sigsiu.net/news.rss';
			$connection->setOptions(
				array(
					'url' => $news,
					'connecttimeout' => 10,
					'header' => false,
					'returntransfer' => true,
				)
			);
			$file = SPFactory::Instance( 'base.fs.file', $path );
			$content = $connection->exec();
			$cinf = $connection->info();
			if( isset( $cinf[ 'http_code' ] ) && $cinf[ 'http_code' ] != 200 ) {
				return Sobi::Error( 'about', sprintf( 'CANNOT_GET_NEWS', $news, $cinf[ 'http_code' ] ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			$file->content( $content );
			$file->save();
		}
		try {
			$news = new DOMXPath( DOMDocument::loadXML( $content ) ) ;
			$title = $news->query( '/rss/channel/title' )->item( 0 )->nodeValue;
			$out .= '<h4>'.$title.'</h4>';
			$items = $news->query( '/rss/channel/item[*]' );
			$c = 3;
			$open = false;
			foreach ( $items as $item ) {
				$date = $item->getElementsByTagName( 'pubDate' )->item( 0 )->nodeValue;
				if( !( $open ) && time() - strtotime( $date ) < ( 60 * 60 * 24 ) ) {
					$open = true;
				}
				$out .= '<h1>';
				$out .= '<a target="_blank" href="' . $item->getElementsByTagName( 'link' )->item( 0 )->nodeValue .'">';
				$out .= $item->getElementsByTagName( 'title' )->item( 0 )->nodeValue;
				$out .= '</a>';
				$out .= '</h1>';
				$out .= '<p style="margin: 0 6px 0 8px; float: left;">';
				$out .= $item->getElementsByTagName( 'description' )->item( 0 )->nodeValue;
				$out .= '</p>';
				$out .= '<p style="clear:both;">';
				$c--;
				if( !( $c ) ) {
					break;
				}
			}
			if( $open ) {
				SPFactory::header()->addJsCode( 'window.addEvent( "domready", function() { window.setTimeout( function() { SPAcc.display( 2 ); }, 150 ); } );' );
			}
		}
		catch ( DOMException $x ) {
			return Sobi::Error( 'about', sprintf( 'CANNOT_LOAD_NEWS',  $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
		return $out;
	}

	private function updPane()
	{
		$out = null;
		$out .= '<div style="padding-left:10px; padding-top:10px;" class="SPCpTabs">';
		$out .= '<span class="SPCPCurrentVer">'.Sobi::Txt( 'ABOUT.UD.YOU_ARE_USING', SPFactory::CmsHelper()->myVersion( true ) ).'</span>';
		$out .= '<div id="SPVerUpd"></div>';
		$out .= '<br/>';
		$out .= '&nbsp;</div>&nbsp;';
		return $out;
	}

	private function creditsPane()
	{
		$out = null;
		$lang = Sobi::Lang( false );
		$file = SOBI_ADM_PATH.DS.'about'.DS.$lang.DS.'credits.html';
		if( !( SPFs::exists( $file ) ) ) {
			$file = SOBI_ADM_PATH.DS.'about'.DS.'en-GB'.DS.'credits.html';
		}
		$out .= '<div style="padding-left:10px; padding-top:3px; font-size: 12px;" class="SPCpTabs">';
		$out .= SPFs::read( $file );
		$out .= '&nbsp;</div>';
		return $out;
	}
}

/* redeclared because we need access to the Accordion */
final class SPPane extends JPane
{

	/**
	 * Constructor
	 *
	 * @param int useCookies, if set to 1 cookie will hold last used tab between page refreshes
	 */
	function __construct( $params = array() )
	{
		static $loaded = false;
		parent::__construct($params);
		if(!$loaded) {
			$this->_loadBehavior($params);
			$loaded = true;
		}
	}
	/**
	 * Creates a pane and creates the javascript object for it
	 *
	 * @param string The pane identifier
	 */
	function startPane( $id )
	{
		return '<div id="'.$id.'" class="pane-sliders">';
	}

	/**
	 * Ends the pane
	 */
	function endPane()
	{
		return '</div>';
	}

	/**
	 * Creates a tab panel with title text and starts that panel
	 *
	 * @param       string  $text - The name of the tab
	 * @param       string  $id - The tab identifier
	 */
	function startPanel( $text, $id )
	{
		return '<div class="panel">'
		.'<h3 class="jpane-toggler title" id="'.$id.'"><span>'.$text.'</span></h3>'
		.'<div class="jpane-slider content">';
	}

	/**
	 * Ends a tab page
	 */
	function endPanel()
	{
		return '</div></div>';
	}

	/**
	* Load the javascript behavior and attach it to the document
	*
	* @param       array   $params Associative array of values
	*/
	function _loadBehavior( $params = array() )
	{
		// Include mootools framework
		JHTML::_( 'behavior.mootools' );
		$options = '{';
		$opt['onActive'] = 'function(toggler, i) { toggler.addClass(\'jpane-toggler-down\'); toggler.removeClass(\'jpane-toggler\'); }';
		$opt['onBackground'] = 'function(toggler, i) { toggler.addClass(\'jpane-toggler\'); toggler.removeClass(\'jpane-toggler-down\'); }';
		$opt['duration'] = (isset($params['duration'])) ? (int)$params['duration'] : 300;
		$opt['display']  = (isset($params['startOffset']) && ($params['startTransition'])) ? (int)$params['startOffset'] : null ;
		$opt['show']     = (isset($params['startOffset']) && (!$params['startTransition'])) ? (int)$params['startOffset'] : null ;
		$opt['opacity']  = (isset($params['opacityTransition']) && ($params['opacityTransition'])) ? 'true' : 'false' ;
		$opt['alwaysHide']       = (isset($params['allowAllClose']) && ($params['allowAllClose'])) ? 'true' : null ;
		foreach ($opt as $k => $v) {
			if ($v) {
				$options .= $k.': '.$v.',';
			}
		}
		if (substr($options, -1) == ',') {
			$options = substr($options, 0, -1);
		}
		$options .= '}';
		$switch = SOBI_CMS == 'joomla15' ? 'var SPResize = 1;' : 'var SPResize = 0;';
		SPFactory::header()->addJsCode( $switch.' var SPAcc = null; window.addEvent(\'domready\', function(){ SPAcc = new Accordion($$(\'.panel h3.jpane-toggler\'), $$(\'.panel div.jpane-slider\'), '.$options.'); });' );
	}
}
