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

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

defined( 'SP_TBL_HEAD_RAW' ) || define( 'SP_TBL_HEAD_RAW', 0 );
defined( 'SP_TBL_HEAD_SORTABLE' ) || define( 'SP_TBL_HEAD_SORTABLE', 1 );
defined( 'SP_TBL_HEAD_SELECTION_BOX' ) || define( 'SP_TBL_HEAD_SELECTION_BOX', 2 );
defined( 'SP_TBL_HEAD_STATE' ) || define( 'SP_TBL_HEAD_STATE', 3 );
defined( 'SP_TBL_HEAD_APPROVAL' ) || define( 'SP_TBL_HEAD_APPROVAL', 4 );
defined( 'SP_TBL_HEAD_ORDER' ) || define( 'SP_TBL_HEAD_ORDER', 5 );
defined( 'SP_TBL_HEAD_SORTABLE_FIELD' ) || define( 'SP_TBL_HEAD_SORTABLE_FIELD', 6 );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:41:10 PM
 */
abstract class SPLists
{
	/**
	 * Creates published/unpublished/expired/pending symbol
	 *
	 * @param SPDataModel $row
	 * @param int|string $id - id ident label of the object
	 * @param string $type - object type e.g. field, entry, category
	 * @param string $stateKey - name of the property where the state is stored
	 * @param array $states - label for states description. By default array( 'on' => 'publish', 'off' => 'hide' )
	 * @param int $sid
	 * @return string
	 */
	public static function state( $row, $id = 'sid', $type = null, $stateKey = 'state', $states = null, $sid = 0 )
	{
		/* check state */
		$state = $row->get( $stateKey ) ? 1 : 0;
		SPLoader::loadClass( 'html.tooltip' );

		$type 	= $type ? $type : $row->get( 'oType' );

		/* get icons */
		if( !$states ) {
			$states	= [ 'on' => 'publish', 'off' => 'hide' ];
			$up = Sobi::Cfg( 'list_icons.unpublished' );
			$pu = Sobi::Cfg( 'list_icons.published' );
			$ex = Sobi::Cfg( 'list_icons.expired' );
			$pe = Sobi::Cfg( 'list_icons.pending' );
			if( $state ) {
				if( strtotime( $row->get( 'validSince' ) ) > time() ) {
					$img = $pe;
				}
				elseif ( strtotime( $row->get( 'validUntil' ) ) && strtotime( $row->get( 'validUntil' ) ) < time() ) {
					$img = $ex;
				}
				else {
					$img = $pu;
				}
			}
			else {
				$img = $up;
			}
		}
		else {
			$img = Sobi::Cfg( 'list_icons.'.$type.'_'.$stateKey.'_'.$state );
		}

		/* translate alternative text */
		$s = Sobi::Txt( $type.'.'.$stateKey.'_head' );
		$a = Sobi::Txt( $stateKey.'_'.( $state ? 'on' : 'off' ) );
		$img = SPTooltip::toolTip( $a, $s, $img );

		$action = $state ? $type.'.'.$states[ 'off' ] : $type.'.'.$states[ 'on' ];

		/* if user has permission for this action */
		if( SPFactory::user()->can( $action ) ) {
			$url = [ 'task' => $action, $id => $row->get( 'id' ) ];
			if( $sid ) {
				$url[ 'sid' ] = $sid;
			}
			$url = Sobi::Url( $url );
			$img = "<a href=\"{$url}\">{$img}</a>";
		}
		return $img;
	}

	/**
	 * Creates the "position" table row with order up/order down and position inputbox
	 *
	 * @param $row - SPDataModel
	 * @param $count - number of entries/cats
	 * @param $id - id ident for the box
	 * @param $type - object type
	 * @param $parent - id ident of parent object
	 * @param $lsid - id of the object for the link
	 * @return unknown_type
	 */
	public static function position( $row, $count, $id = 'sid', $type = null, $parent = 'pid', $lsid = 'sid' )
	{
		SPLoader::loadClass( 'html.tooltip' );
		$position = $row->get( 'position' );
		/** @todo check what the hell the $up should be */
		$up = null;
		if( !$type ) {
			$type = $row->type();
		}
		if( $position > 1 ) {
			$up = /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.position_up' );
			$url = Sobi::Url( [ 'task' => $type.'.up', $parent => SPRequest::sid(), $lsid => $row->get( 'id' ) ] );
			/* translate alternative text */
			$s = Sobi::Txt( $type.'.order_up' );
			$a = Sobi::Txt( $type.'.order_up_expl' );
			$img = SPTooltip::toolTip( $a, $s, $up );
			$up = "<a href=\"{$url}\">{$img}</a>";
		}
		$down = /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.position_down' );
		$url = Sobi::Url( [ 'task' => $type.'.down', $parent => SPRequest::sid(), $lsid => $row->get( 'id' ) ] );
		/* translate alternative text */
		$s = Sobi::Txt( $type.'.order_down' );
		if( $position != $count ) {
			$a = Sobi::Txt( $type.'.order_down_expl' );
			$img = SPTooltip::toolTip( $a, $s, $down );
			$down = "<a href=\"{$url}\">{$img}</a>";
		}
		else {
			$down = null;
		}
		$sid = $row->get( 'id' );
		$box = SPHtml_Input::text( "{$id}[{$sid}]", $position, [ 'style' => 'text-align:center; width: 40px;' ] );
		return "<div style=\"width:30%;float:left;\">{$up}&nbsp;{$down}</div>&nbsp;{$box}";
	}

	/**
	 * Creates the approved/unapproved symbol
	 *
	 * @param SPDataModel $row
	 * @return string
	 */
	public static function approval( $row )
	{
		/* check state */
		$state = $row->get( 'approved' ) ? 1 : 0;
		SPLoader::loadClass( 'html.tooltip' );
		/* get icons */
		$up = /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.unapproved' );
		$pu = /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.approved' );
		$img = $state == 1 ? $pu : $up;
		$action = $state ? $row->get( 'oType' ).'.unapprove' : $row->get( 'oType' ).'.approve';

		/* translate alternative text */
		$s = Sobi::Txt( $row->get( 'oType' ).'.approval_head' );
		$a = Sobi::Txt( 'approval_'.( $state ? 'on' : 'off' ) );
		$img = SPTooltip::toolTip( $a, $s, $img );

		/* if user has permission for this action */
		if( SPFactory::user()->can( $action ) ) {
			$url = SPFactory::mainframe()->url( [ 'task' => $action, 'sid' => $row->get( 'id' ) ] );
			$img = "<a href=\"{$url}\">{$img}</a>";
		}
		return $img;
	}

	/**
	 * @todo Enter description here...
	 *
	 * @param array $ordering
	 * @param string $type
	 * @param string $id
	 * @param string $fname
	 * @param string $def
	 * @return string
	 */
	public static function tableHeader( $ordering, $type, $id = 'sid', $fname = 'order', $def = 'position.asc' )
	{
		$header = [];
		$current = SPFactory::user()->getUserState( $type.'.order', $fname, $def );
		if( strstr( $current, '.' ) ) {
			$current 	= explode( '.', $current );
			$newDirect 	= ( trim( $current[1] ) == 'asc' ) ? 'desc' : 'asc';
			$current 	= $current[0];
		}
		$sid = SPRequest::sid() ? SPRequest::sid() : SPRequest::int( 'pid' );
		if( is_array( $ordering ) && count( $ordering ) ) {
			foreach ( $ordering as $order => $active ) {
				$class = null;
				$params = [];
				if( is_array( $active ) ) {
					$params = $active;
					$active = $active[ 'type' ];
				}
				switch ( $active ) {
					case SP_TBL_HEAD_RAW:
						$header[ $order ] = Sobi::Txt( $type.'.header_'.$order );
						break;
					case 1:
					case 3:
					case 4:
					case 5:
					case 6:
						$direction = 'asc';
						$ico = null;
						$aico = null;
						if( isset( $params[ 'order' ] ) ) {
							$sortBy = $params[ 'order' ];
						}
						else {
							$sortBy = $order;
						}
						$label = Sobi::Txt( $type.'.header_'.$order );
						$title = Sobi::Txt( $type.'.header_order_by_'.$order );
						if( $sortBy == $current ) {
							$class = "class=\"selected\"";
							$ico = /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.sort_direction_'. ( ( $newDirect == 'asc' ) ? 'desc' : 'asc' ) );
							$ico = "&nbsp;<img src=\"{$ico}\"/>&nbsp;";
							$direction = $newDirect;
						}
						if( $active == SP_TBL_HEAD_SORTABLE_FIELD ) {
							$label = $params[ 'label' ];
							$title = Sobi::Txt( 'LIST.ORDER_BY_FIELD', [ 'field' => $label ] );
						}
						if( $active == SP_TBL_HEAD_STATE ) {
							SPLoader::loadClass( 'html.tooltip' );
							$msg	= Sobi::Txt( 'LIST.MAKE_SELECTION' );
							$onclk	= " onclick=\"if( document.adminForm.boxchecked.value == 0 ) { alert( '{$msg}' ); } else { submitbutton( '{$type}.publish' ); }\"";
							$url	= "#";
							$ai 	= /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.enable' );
							$s 		= Sobi::Txt( 'LIST.ENABLE_S', [ 'type' => Sobi::Txt( strtoupper( $type ) ) ] );
							$a 		= Sobi::Txt( $type.'.enable_expl' );
							$aico 	= SPTooltip::toolTip( $a, $s, $ai );
							$aico 	= "&nbsp;<span class=\"headerStateIcons\"><a href=\"{$url}\"{$onclk}>{$aico}</a></span>";
							$ui 	= /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.disable' );
							$s 		= Sobi::Txt( 'LIST.DISABLE_S', [ 'type' => Sobi::Txt( strtoupper( $type ) ) ] );
							$a 		= Sobi::Txt( $type.'.disable_expl' );
							$uico 	= SPTooltip::toolTip( $a, $s, $ui );
							$onclk	= " onclick=\"if( document.adminForm.boxchecked.value == 0 ) { alert( '{$msg}' ); } else { submitbutton( '{$type}.hide' ); }\"";
							$uico 	= "&nbsp;<span class=\"headerStateIcons\"><a href=\"{$url}\"{$onclk}>{$uico}</a></span>";
							$aico .= $uico;
						}
						if( $active == SP_TBL_HEAD_APPROVAL ) {
							SPLoader::loadClass( 'html.tooltip' );
							$msg	= Sobi::Txt( 'LIST.MAKE_SELECTION' );
							$onclk	= " onclick=\"if( document.adminForm.boxchecked.value == 0 ) { alert( '{$msg}' ); } else { submitbutton( '{$type}.approve' ); }\"";
							$url	= "#";
							$ai 	= /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.approve' );
							$s 		= Sobi::Txt( $type.'.approve' );
							$a 		= Sobi::Txt( $type.'.approve_expl' );
							$aico 	= SPTooltip::toolTip( $a, $s, $ai );
							$aico 	= "&nbsp;<span class=\"headerAppIcons\"><a href=\"{$url}\"{$onclk}>{$aico}</a></span>";
							$ui 	= /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.disable' );
							$s 		= Sobi::Txt( $type.'.unapprove' );
							$a 		= Sobi::Txt( $type.'.unapprove_expl' );
							$uico 	= SPTooltip::toolTip( $a, $s, $ui );
							$onclk	= " onclick=\"if( document.adminForm.boxchecked.value == 0 ) { alert( '{$msg}' ); } else { submitbutton( '{$type}.unapprove' ); }\"";
							$uico 	= "&nbsp;<span class=\"headerAppIcons\"><a href=\"{$url}\"{$onclk}>{$uico}</a></span>";
							$aico .= $uico;
						}
						if( $active == SP_TBL_HEAD_ORDER ) {
							SPLoader::loadClass( 'html.tooltip' );
							$url	= "#";
							$onclk	= " onclick=\"SPReorder( '{$type}', {$sid} );\" ";
							$aico 	= "&nbsp;<span class=\"headerStateIcons\"><a href=\"{$url}\" title=\"{$a}\">{$aico}</a></span>";
							$ui 	= /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.order' );
							$s 		= Sobi::Txt( $type.'.save_order' );
							$a 		= Sobi::Txt( $type.'.save_order_expl' );
							$uico 	= SPTooltip::toolTip( $a, $s, $ui );
							$uico 	= "&nbsp;<span class=\"headerOrderIcon\"><a href=\"{$url}\"{$onclk}>{$uico}</a></span>";
							$aico .= $uico;
						}
						$header[ $order ] = "<a {$class} href=\"javascript:SPOrdering( '{$sortBy}','{$direction}', '{$fname}', {$sid} );\" title=\"{$title}\">{$label}</a>&nbsp;{$ico}{$aico}";
						break;
					case SP_TBL_HEAD_SELECTION_BOX:
						$name = Sobi::Txt( $type.'.header_toggle' );
						$header[ $order ] = "<input type=\"checkbox\" name=\"toggle\" id=\"toggel_{$id}\" title=\"{$name}\" value=\"1\" onclick=\"SPCheckListElements('{$id}', this );\"/>";
						break;
				}
			}
		}
		return $header;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $row
	 * @param string|unknown_type $id
	 * @return unknown
	 */
	public static function checkedOut( $row, $id = 'sid' )
	{
		$state = $row->get( 'cout' );
		if(
		/* if checked out ... */
		$state &&
		/* ... by an other user ... */
		$state != Sobi::My( 'id' ) &&
		/* ... and the time isn't expired */
		strtotime( $row->get( 'coutTime' ) ) > time()
		) {
			/* translate alternative text */
			$user = SPUser::getInstance( $state );
			$uname = $user->get( 'name' );
			$img = /*Sobi::Cfg( 'live_site' ).*/Sobi::Cfg( 'list_icons.checked_out' );
			$s = Sobi::Txt( $row->get( 'oType' ).'.checked_out' );
			$a = Sobi::Txt( $row->get( 'oType' ).'.checked_out_by', [ 'user' => $uname, 'time' => $row->get( 'coutTime' ) ] );
			$r = SPTooltip::toolTip( $a, $s, $img );
		}
		else {
			$sid = $row->get( 'id' );
			$r = "<input type=\"checkbox\" name=\"{$id}[]\" value=\"{$sid}\" onclick=\"SPCheckListElement( this )\" />";
		}
		return $r;
	}
}
