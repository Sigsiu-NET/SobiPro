<?php
/**
 * @package: SobiPro Component for Joomla!
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
/*

/**
 * @param $query
 * @return array
 */
function SobiProBuildRoute( &$query )
{
	$cfg = SobiProRouterCfg();
	$segments = [];
	$reserved = $cfg[ 'skip' ];
	if ( isset( $query[ 'task' ] ) ) {
		if ( isset( $reserved[ $query[ 'task' ] ] ) && $reserved[ $query[ 'task' ] ] ) {
			return [];
		}
		if ( isset( $query[ 'sid' ] ) && isset( $query[ 'Itemid' ] ) ) {
			if ( !( SobiProIsLinked( $query[ 'Itemid' ], $query[ 'sid' ], $query[ 'task' ] ) ) ) {
				if ( isset( $cfg[ 'tasks' ][ strtolower( $query[ 'task' ] ) ] ) ) {
					$task = str_replace( $query[ 'task' ], $cfg[ 'tasks' ][ strtolower( $query[ 'task' ] ) ], $query[ 'task' ] );
				}
				else {
					$task = str_replace( '.', '/', $query[ 'task' ] );
				}
				if ( isset( $cfg[ 'task_id' ][ $query[ 'task' ] ] ) ) {
					$task = $query[ 'sid' ] . '.' . $task;
				}
				$segments[] = $task;
			}
			unset( $query[ 'task' ] );
			unset( $query[ 'sid' ] );
		}
	}
	elseif ( isset( $query[ 'Itemid' ] ) ) {
		if ( isset( $query[ 'sid' ] ) ) {
			if ( !( SobiProIsLinked( $query[ 'Itemid' ], $query[ 'sid' ] ) ) ) {
				$segments[] = $query[ 'sid' ];
			}
			unset( $query[ 'sid' ] );
		}
	}
	if ( isset( $query[ 'pid' ] ) ) {
		unset( $query[ 'pid' ] );
	}
	if ( count( $query ) ) {
		SobiProRemoveVars( $cfg, $query );
	}
	if ( count( $query ) ) {
		foreach ( $query as $k => $v ) {
			if ( isset( $cfg[ 'transform_vars' ][ $k ] ) ) {
				$segments[] = $cfg[ 'transform_vars' ][ $k ] . $cfg[ 'config' ][ 'transform_separator' ] . SobiProTransformValue( $v, $cfg, false );
				unset( $query[ $k ] );
				continue;
			}
			if ( isset( $cfg[ 'ignore_vars' ][ $k ] ) ) {
				if ( $v == $cfg[ 'ignore_vars' ][ $k ] || $cfg[ 'ignore_vars' ][ $k ] == '*' ) {
					continue;
				}
			}
			if ( isset( $cfg[ 'vars' ][ $k ] ) ) {
				$segments[] = $cfg[ 'vars' ][ $k ];
				$segments[] = SobiProTransformValue( $v, $cfg, false );
				unset( $query[ $k ] );
				continue;
			}
		}
	}
	if ( $cfg[ 'config' ][ 'to_lower' ] ) {
		foreach ( $segments as $i => $segment ) {
			$segments[ $i ] = function_exists( 'mb_strtolower' ) ? mb_strtolower( $segment ) : strtolower( $segment );
		}
	}
	if ( count( $segments ) && strstr( $segments[ count( $segments ) - 1 ], '.' ) ) {
		$segments[] = '/.';
	}
	if ( isset( $segments[ count( $segments ) - 1 ] ) && $segments[ count( $segments ) - 1 ] == '' ) {
		unset( $segments[ count( $segments ) - 1 ] );
	}
	return $segments;
}

function SobiProTransformValue( $val, $cfg, $back )
{
	$replacement = $cfg[ 'values' ];
	if ( $back ) {
		$replacement = array_flip( $replacement );
	}
	return isset( $replacement[ $val ] ) ? $replacement[ $val ] : $val;
}

function SobiProRemoveVars( $cfg, &$query )
{
	if ( count( $cfg[ 'remove_vars' ] ) ) {
		foreach ( $cfg[ 'remove_vars' ] as $var => $removal ) {
			$var = explode( ':', $var );
			$var = $var[ 0 ];
			if ( isset( $query[ $var ] ) && $query[ $var ] == $removal[ 'condition' ] ) {
				if ( count( $removal[ 'query' ] ) ) {
					foreach ( $removal[ 'query' ] as $k => $v ) {
						if ( isset( $query[ $k ] ) && $query[ $k ] == $v ) {
							unset( $query[ $k ] );
						}
					}
				}
			}
		}
	}
}

function SobiProRouterCfg()
{
	static $config = [];
	if ( !( count( $config ) ) ) {
		$config = parse_ini_file( 'etc/router.ini', true );
		if ( file_exists( dirname( __FILE__ ) . '/etc/router_override.ini' ) ) {
			$custom = parse_ini_file( 'etc/router_override.ini', true );
			$config = array_merge( $config, $custom );
		}
	}
	if ( isset( $config[ 'remove_query' ] ) && count( $config[ 'remove_query' ] ) ) {
		foreach ( $config[ 'remove_query' ] as $condition => $remove ) {
			$cond = explode( ':', $condition );
			$query = [];
			$remove = explode( ',', $remove );
			foreach ( $remove as $q ) {
				$q = explode( ':', $q );
				$query[ $q[ 0 ] ] = $q[ 1 ];
			}
			$config[ 'remove_vars' ][ $condition ] = [ 'condition' => $cond[ 1 ], 'query' => $query ];
		}
	}
	return $config;
}

function SobiProTaskEnhancement( $value, $task, $values )
{
	foreach ( $values as $enhancement => $current ) {
		if ( strstr( $task, $current ) && strstr( $value, $enhancement ) ) {
			return true;
		}
	}
	return false;
}

function SobiProParseRoute( $segments )
{
	/** because screw logic. That's why .... #1783 */
	if ( $segments[ 0 ] == 'results' ) {
		$segments[ 0 ] = 'search';
		$segments[ 1 ] = 'results';
	}
	$cfg = SobiProRouterCfg();
	$vars = [];
	$return = JFactory::getApplication()->getMenu()->getActive()->query;
	$tasks = array_flip( $cfg[ 'tasks' ] );
	$taskEnhancement = array_flip( $cfg[ 'segments_to_task' ] );
	$key = false;
	$afterTask = false;
	if ( count( $segments ) ) {
		foreach ( $segments as $i => $segment ) {
			if ( strstr( $segment, ':' ) ) {
				$segment = str_replace( ':', '-', $segment );
			}
			if ( strstr( $segment, $cfg[ 'config' ][ 'transform_separator' ] ) ) {
				$segment = explode( $cfg[ 'config' ][ 'transform_separator' ], $segment );
				$vars[ $segment[ 0 ] ] = $segment[ 1 ];
				unset( $segments[ $i ] );
				$afterTask = false;
			}
			elseif ( isset( $tasks[ $segment ] ) ) {
				$vars[ 'task' ] = $tasks[ $segment ];
				unset( $segments[ $i ] );
				$afterTask = true;
			}
			elseif ( $afterTask && SobiProTaskEnhancement( $segment, $vars[ 'task' ], $taskEnhancement ) ) {
				$vars[ 'task' ] .= '.' . $segment;
				unset( $segments[ $i ] );
			}
			else {
				if ( $key ) {
					$return[ $key ] = $segment;
					$key = false;
				}
				else {
					$key = $segment;
				}
				$afterTask = false;
			}
		}
	}
	$vars[ 'sid' ] = isset( $segments[ 0 ] ) ? $segments[ 0 ] : 0;
	$sid = $vars[ 'sid' ];
	if ( strstr( $sid, '.' ) ) {
		$sid = explode( '.', $vars[ 'sid' ] );
		$vars[ 'task' ] = $sid[ 1 ];
		$sid = $sid[ 0 ];
	}
	$sid = explode( ':', $sid );
	$sid = $sid[ 0 ];
	if ( !( ( int )$sid ) ) {
		$vars[ 'sid' ] = JFactory::getApplication()->getMenu()->getActive()->query[ 'sid' ];
		if ( count( $segments ) && !( isset( $vars[ 'task' ] ) ) ) {
			$vars[ 'task' ] = implode( '.', $segments );
		}
	}
	$replacement = array_flip( array_merge( $cfg[ 'vars' ], $cfg[ 'transform_vars' ] ) );
	$pre = [];
	foreach ( $vars as $k => $v ) {
		if ( isset( $replacement[ $k ] ) ) {
			$k = $replacement[ $k ];
		}
		$pre[ $k ] = SobiProTransformValue( $v, $cfg, true );
	}
	$addVars = $cfg[ 'remove_query' ];
	foreach ( $pre as $k => $v ) {
		if ( isset( $addVars[ $k ] ) ) {
			SobiProExtendQuery( $addVars[ $k ], $return );
		}
		elseif ( isset( $addVars[ $k . ':' . $v ] ) ) {
			SobiProExtendQuery( $addVars[ $k . ':' . $v ], $return );
		}
		$return[ $k ] = $v;
	}
	return $return;
}

function SobiProExtendQuery( $arr, &$return )
{
	$q = explode( ',', $arr );
	foreach ( $q as $v ) {
		$v = explode( ':', $v );
		$return[ $v[ 0 ] ] = $v[ 1 ];
	}
}

function SobiProIsLinked( $id, $sid, $task = null )
{
	static $menu = null;
	if ( !( $menu ) ) {
		$menu = JFactory::getApplication()->getMenu();
	}
	$sid = explode( ':', $sid );
	$sid = $sid[ 0 ];
	if ( !( $task ) ) {
		return $menu->getItem( $id )->query[ 'sid' ] == $sid;
	}
	else {
		$item = $menu->getItem( $id );
		if ( isset( $item->query[ 'task' ] ) ) {
			if ( $item->query[ 'sid' ] == $sid && $item->query[ 'task' ] == $task ) {
				return true;
			}
		}
	}
	return false;
}
