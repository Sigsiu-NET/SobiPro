<?php
/**
 * @version: $Id$
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3 as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and http://sobipro.sigsiu.net/licenses.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 * $Date$
 * $Revision$
 * $Author$
 * $HeadURL$
 */

/**
 * @author Radek Suski
 * @version 1.0
 * @since 1.0
 * @created 15-Jan-2009 2:04:36 PM
 */
abstract class SPJoomlaAdmMenu
{
	/**
	 * @param string $name
	 * @param string $link
	 * @param bool $active
	 */
	public static function addSubMenuEntry( $name, $link = null, $active = false )
	{
		return null;
//		preg_match( '/task\=([a-zA-Z0-9\.\_\-]*).*/', $link, $matches );
//		if( isset( $matches[ 1 ] ) ) {
//			if( SPFactory::user()->can( $matches[ 1 ] ) ) {
//				JSubMenuHelper::addEntry( $name, $link, false /*( SPRequest::task() == $matches[ 1 ] )*/ );
//			}
//		}
//		else {
//			JSubMenuHelper::addEntry( $name, $link, $active );
//		}
	}

	/**
	 * @param string The task to perform (picked up by the switch ($task) blocks
	 * @param string The image to display
	 * @param string The image to display when moused over
	 * @param string The alt text for the icon image
	 * @param boolean True if required to check that a standard list item is checked
	 * @param boolean True if required to include callinh hideMainMenu()
	 */
	public static function custom( $task, $icon = null, $iconOver = null, $alt = null, $listSelect = true, $x = false )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::custom( $task, $icon, $iconOver, $alt, $listSelect, $x );
		}
	}

	/**
	 * @param string The $task to perform (picked up by the switch($task) blocks
	 * @param string The $txt text for the icon image
	 */
	public static function forward( $task, $txt )
	{
		self::custom( $task, 'forward', 'forward', $txt, false );
	}

	/**
	 * @param string The $task to perform (picked up by the switch($task) blocks
	 * @param string The $txt text for the icon image
	 */
	public static function back( $task, $txt )
	{
		self::custom( $task, 'back', 'back', $txt, false );
	}

	/**
	 * @param string The task to perform (picked up by the switch( $task) blocks
	 * @param string The image to display
	 * @param string The image to display when moused over
	 * @param string The alt text for the icon image
	 * @param boolean True if required to check that a standard list item is checked
	 * @param boolean True if required to include callinh hideMainMenu()
	 */
	public static function customX( $task, $icon = null, $iconOver = null, $alt = null, $listSelect = true )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::custom( $task, $icon, $iconOver, $alt, $listSelect, true );
		}
	}

	/**
	 * @param string An override for the task
	 * @param string An override for the alt text
	 */
	public static function addNew( $task = 'add', $alt = 'New' )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::addNew( $task, $alt );
		}
	}

	/**
	 * @param string An override for the task
	 * @param string An override for the alt text
	 */
	public static function addSection( $alt = 'New' )
	{
		self::addNew( 'section.add', $alt );
	}

	/**
	 * @param string An override for the task
	 * @param string An override for the alt text
	 */
	public static function addCategory( $alt = 'New' )
	{
		self::addNew( 'category.add', $alt );
	}

	/**
	 * @param string An override for the task
	 * @param string An override for the alt text
	 */
	public static function addEntry( $alt = 'New' )
	{
		self::addNew( 'entry.add', $alt );
	}

	/**
	 * Writes a common 'publish' button
	 * @param string An override for the task
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function publish( $task = 'publish', $alt = 'Publish' )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::publishList( $task, $alt );
		}
	}


	/**
	 * Writes a common 'default' button for a record
	 * @param string An override for the task
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function makeDefault( $task = 'default', $alt = 'Default' )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::makeDefault( $task, $alt );
		}
	}

	/**
	 * Writes a common 'assign' button for a record
	 * @param string An override for the task
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function assign( $task = 'assign', $alt = 'Assign' )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::assign( $task, $alt );
		}
	}

	/**
	 * Writes a common 'unpublish' button
	 * @param string An override for the task
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function hide( $task = 'entry.hide', $alt = 'hide' )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::unpublishList( $task, $alt );
		}
	}

	/**
	 * Writes a common 'unpublish' button
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function hideEntry( $alt = 'hide_entry' )
	{
		self::hide( 'entry.hide', $alt );
	}

	/**
	 * Writes a common 'unpublish' button
	 * @param string $alt An override for the alt text
	 * @since 1.0
	 */
	public static function hideCategory( $alt = 'hide_category' )
	{
		self::hide( 'category.hide', $alt );
	}

	/**
	 * Writes a common 'unpublish' button
	 * @param string $alt An override for the alt text
	 * @since 1.0
	 */
	public static function hideSection( $alt = 'hide_section' )
	{
		self::hide( 'section.hide', $alt );
	}

	/**
	 * Title cell
	 * @param string $title The title
	 * @param string $icon The name of the image
	 * @since 1.5
	 */
	public static function title( $title, $icon = 'generic.png' )
	{
		if( !( strstr( Sobi::Reg( 'task' ), 'section' ) ) ) {
			if( strlen( Sobi::Section( true ) ) ) {
				$title = Sobi::Section( true ).' - '.$title;
			}
		}
		$title = 'SobiPro - '.$title;
		JToolBarHelper::title( $title, $icon );
	}

	/**
	 * Writes a spacer cell
	 * @param string The width for the cell
	 */
	public static function spacer( $width = null )
	{
		JToolBarHelper::spacer( $width );
	}

	/**
	 * Write a divider between menu buttons
	 */
	public static function divider()
	{
		JToolBarHelper::divider();
	}

	/**
	 * Writes a common 'publish' button for a list of records
	 * @param string An override for the task
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function publishList( $task = 'entry.publish', $alt = 'Publish' )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::publishList( $task, $alt );
		}
	}

	/**
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function publishSection( $alt = 'Publish' )
	{
		self::publishList( 'section.publish', $alt );
	}

	/**
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function publishEntry( $alt = 'Publish' )
	{
		self::publishList( 'entry.publish', $alt );
	}

	/**
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function publishCategory( $alt = 'Publish' )
	{
		self::publishList( 'category.publish', $alt );
	}

	/**
	 * Writes a common 'delete' button for a list of records
	 * @param string  Postscript for the 'are you sure' message
	 * @param string An override for the task
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function deleteList( $msg = null, $task = 'entry.delete', $alt = 'Delete' )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::deleteList( $msg, $task, $alt );
		}
	}

	/**
	 * Writes a common 'delete' button for a list of records
	 * @param string  Postscript for the 'are you sure' message
	 * @param string An override for the task
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function delete( $task = 'entry.delete', $alt = 'Delete', $msg = null )
	{
		self::deleteList( $msg, $task, $alt );
	}

	/**
	 * Writes a common 'delete' button for a list of records
	 * @param string  Postscript for the 'are you sure' message
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function deleteSection( $alt = 'Delete', $msg = null )
	{
		self::deleteList( $msg, 'section.delete', $alt );
	}

	/**
	 * Writes a common 'delete' button for a list of records
	 * @param string  Postscript for the 'are you sure' message
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function deleteCategory( $alt = 'Delete', $msg = null )
	{
		self::deleteList( $msg, 'category.delete', $alt );
	}

	/**
	 * Writes a common 'delete' button for a list of records
	 * @param string  Postscript for the 'are you sure' message
	 * @param string An override for the alt text
	 * @since 1.0
	 */
	public static function deleteEntry( $alt = 'Delete', $msg = null )
	{
		self::deleteList( $msg, 'entry.delete', $alt );
	}

	/**
	 * Writes a apply button for a given option
	 * Apply operation leads to a save action only (does not leave edit mode)
	 * @param string An override for the task
	 * @param string An override for the alt text
	 */
	public static function apply( $task = 'apply', $alt = 'Apply' )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::apply( $task, $alt );
		}
	}

	/**
	 * Writes a save button for a given option
	 * Save operation leads to a save and then close action
	 * @param string An override for the task
	 * @param string An override for the alt text
	 */
	public static function save( $task = 'save', $alt = 'Save' )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::save( $task, $alt );
		}
	}

	/**
	 * Writes a save button for a given option
	 * Save operation leads to a save and then close action
	 * @param string An override for the task
	 * @param string An override for the alt text
	 */
	public static function duplicate( $task = 'clone', $alt = 'Duplicate' )
	{
		if( SPFactory::user()->can( $task ) ) {
			JToolBarHelper::custom( $task, 'document-save-all', 'document-save-all', $alt, false );
		}
	}

	/**
	 * Writes a cancel button and invokes a cancel operation (eg a checkin)
	 * @param string An override for the task
	 * @param string An override for the alt text
	 */
	public static function cancel( $task = 'cancel', $alt = 'Cancel' )
	{
		JToolBarHelper::cancel( $task, $alt );
	}

	/**
	 * Writes a cancel button and invokes a cancel operation (eg a checkin)
	 * @param string An override for the task
	 * @param string An override for the alt text
	 */
	public static function help( $alt )
	{
		$bar =& JToolBar::getInstance( 'toolbar' );
//		$bar->appendButton( 'Popup', 'help', $alt, SPMainFrame::url( array( 'task' => 'help.message', 'mid' => Sobi::Reg( 'task', SPRequest::task() ), 'out' => 'html' ) ) );
		$bar->appendButton( 'link', 'help', $alt, 'http://sobipro.sigsiu.net/help_screen/'.Sobi::Reg( 'help_task', Sobi::Reg( 'task', SPRequest::task() ) ) );
		SPFactory::header()->addJsCode( '
			window.addEvent(  "domready",
				function() {
					var spHelpLink = $$( "#toolbar-link a" )[ 0 ];
					spHelpLink.target = "_blank";
				}
			);'
		);
	}
}
?>
