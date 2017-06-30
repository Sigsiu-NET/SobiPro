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

/**
 * @author Radek Suski
 * @version 1.0
 * @created 08-Jul-2008 9:43:25 AM
 */
class SPJoomlaDb
{
	/**
	 * Joomla Database object
	 *
	 * @var JDatabaseMySQLi
	 */
	protected $db = null;
	/**
	 * @var string
	 */
	protected $prefix = '#__';
	/**
	 * @var int
	 */
	protected $count = 0;

	/**
	 * @return \SPJoomlaDb
	 */
	public function __construct()
	{
		$this->db = JFactory::getDBO();
	}

	/**
	 * @return SPDb
	 */
	public static function & getInstance()
	{
		static $db = null;
		if ( !$db || !( $db instanceof SPDb ) ) {
			$db = new SPDb();
		}
		return $db;
	}

	/**
	 * Returns the error number
	 * @deprecated
	 * @return int
	 */
	public function getErrorNum()
	{
		return $this->db->getErrorNum();
	}

	/**
	 * Returns the error message
	 * @deprecated
	 * @return string
	 */
	public function getErrorMsg()
	{
		return $this->db->getErrorMsg();
	}

	/**
	 * Proxy pattern
	 *
	 * @param string $method
	 * @param array $args
	 * @throws SPException
	 * @return mixed
	 */
	public function __call( $method, $args )
	{
		if ( $this->db && method_exists( $this->db, $method ) ) {
			$Args = [];
			// http://www.php.net/manual/en/function.call-user-func-array.php#91503
			foreach ( $args as $k => &$arg ) {
				$Args[ $k ] = &$arg;
			}
			return call_user_func_array( [ $this->db, $method ], $Args );
		}
		else {
			throw new SPException( SPLang::e( 'CALL_TO_UNDEFINED_CLASS_METHOD', get_class( $this->_type ), $method ) );
		}
	}

	/**
	 * Returns a database escaped string
	 *
	 * @param string $text string to be escaped
	 * @param bool $esc extra escaping
	 * @return string
	 */
	public function getEscaped( $text, $esc = false )
	{
		return $this->db->getEscaped( ( class_exists( 'SPLang' ) ? SPLang::clean( $text ) : $text ), $esc );
	}

	/**
	 * Returns a database escaped string
	 *
	 * @param string $text string to be escaped
	 * @param bool $esc extra escaping
	 * @return string
	 */
	public function escape( $text, $esc = false )
	{
		return $this->db->getEscaped( $text, $esc );
	}

	/**
	 * Returns database null date format
	 *
	 * @return string Quoted null date string
	 */
	public function getNullDate()
	{
		return $this->db->getNullDate();
	}

	/**
	 * Sets the SQL query string for later execution.
	 *
	 * @param string $sql
	 * @return $this
	 */
	public function setQuery( $sql )
	{
		$sql = str_replace( 'spdb', $this->prefix . 'sobipro', $sql );
		$sql = str_replace( 'NOW()', '\'' . gmdate( Sobi::Cfg( 'date.db_format', 'Y-m-d H:i:s' ) ) . '\'', $sql );
		return $this->db->setQuery( $sql );
	}

	/* (non-PHPdoc)
	  * @see Site/lib/base/SPDatabase#loadFile($file)
	  */
	public function loadFile( $file )
	{
		$sql = SPFs::read( $file );
		$sql = explode( "\n", $sql );
		$log = [];
		if ( count( $sql ) ) {
			foreach ( $sql as $query ) {
				if ( strlen( $query ) ) {
					$this->exec( str_replace( 'spdb_', $this->prefix . 'sobipro_', $query ) );
					$log[ ] = $query;
				}
			}
		}
		return $log;
	}

	/**
	 * Alias for select where $distinct is true
	 *
	 * @param string $toSelect
	 * @param string $tables
	 * @param string $where
	 * @param null $order
	 * @param int $limit
	 * @param int $limitStart
	 * @param null $group
	 * @return \SPDb
	 * param string $groupBy - column to group by
	 */
	public function dselect( $toSelect, $tables, $where = null, $order = null, $limit = 0, $limitStart = 0, $group = null )
	{
		return $this->select( $toSelect, $tables, $where, $order, $limit, $limitStart, true, $group );
	}

	/**
	 * Creates a "select" SQL query.
	 *
	 * @param string $toSelect - table rows to select
	 * @param string $tables - from which table(s)
	 * @param string $where - SQL select condition
	 * @param null $order
	 * @param int $limit - maximal number of rows
	 * @param int $limitStart - start position
	 * @param bool $distinct - clear??
	 * @param string $groupBy - column to group by
	 * @return SPDb
	 */
	public function & select( $toSelect, $tables, $where = null, $order = null, $limit = 0, $limitStart = 0, $distinct = false, $groupBy = null )
	{
		$limits = null;
		$ordering = null;
		$where = $this->where( $where );
		$where = $where ? "WHERE {$where}" : null;
		$distinct = $distinct ? ' DISTINCT ' : null;
		$tables = is_array( $tables ) ? implode( ', ', $tables ) : $tables;
		$groupBy = $groupBy ? "GROUP BY {$groupBy}" : null;
		$limitStart = $limitStart < 0 ? 0 : $limitStart;
		if ( $limit ) {
			$limits = "LIMIT {$limitStart}, {$limit}";
		}
		if ( is_array( $toSelect ) ) {
			$toSelect = implode( ',', $toSelect );
		}
		if ( $order ) {
			$n = false;
			if ( strstr( $order, '.num' ) ) {
				$order = str_replace( '.num', null, $order );
				$n = true;
			}
			if ( strstr( $order, ',' ) ) {
				$o = explode( ',', $order );
				$order = [];
				foreach ( $o as $p ) {
					if ( strstr( $p, '.' ) ) {
						$p = explode( '.', $p );
						$order[ ] = $p[ 0 ] . ' ' . strtoupper( $p[ 1 ] );
					}
					else {
						$order[ ] = $p;
					}
				}
				$order = implode( ', ', $order );
			}
			elseif ( strstr( $order, '.' ) && ( stristr( $order, 'asc' ) || stristr( $order, 'desc' ) ) ) {
				$order = explode( '.', $order );
				$e = array_pop( $order );
				if ( $n ) {
					$order = implode( '.', $order ) . '+0 ' . $e;
				}
				else {
					$order = implode( '.', $order ) . ' ' . $e;
				}
			}
			else {
				if ( $n ) {
					$order .= '+0';
				}
			}
			$ordering = "ORDER BY {$order}";
		}
		$this->setQuery( "SELECT {$distinct}{$toSelect} FROM {$tables} {$where} {$groupBy} {$ordering} {$limits}" );
		return $this;
	}

	/**
	 * Creates a "delete" SQL query
	 *
	 * @param string $table - in which table
	 * @param array|string $where - SQL delete condition
	 * @param int $limit - maximal number of rows to delete
	 * @throws SPException
	 * @return \SPJoomlaDb
	 */
	public function delete( $table, $where, $limit = 0 )
	{
		$where = $this->where( $where );
		$limit = $limit ? "LIMIT $limit" : null;
		try {
			$this->exec( "DELETE FROM {$table} WHERE {$where} {$limit}" );
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $this;
	}

	/**
	 * Creates a "drop table" SQL query
	 *
	 * @param string $table - in which table
	 * @param bool|string $ifExists
	 * @throws SPException
	 * @return \SPJoomlaDb
	 */
	public function drop( $table, $ifExists = true )
	{
		$ifExists = $ifExists ? 'IF EXISTS' : null;
		try {
			$this->exec( "DROP TABLE {$ifExists} {$table}" );
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $this;
	}

	/**
	 * Creates a "truncate table" SQL query
	 *
	 * @param string $table - in which table
	 * @throws SPException
	 * @return $this
	 */
	public function truncate( $table )
	{
		try {
			$this->exec( "TRUNCATE TABLE {$table}" );
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $this;
	}

	/**
	 * Creates where condition from a given array
	 *
	 * @param array $where - array with values. array( 'id' => 5, 'published' => 1 ) OR array( 'id' => array( 5, 3, 4 ), 'published' => 1 )
	 * @param string $andor - joind conditions through AND or OR
	 * @return string
	 */
	public function where( $where, $andor = 'AND' )
	{
		if ( is_array( $where ) ) {
			$w = [];
			foreach ( $where as $col => $val ) {
				$equal = '=';
				$not = false;
				// sort of workaround for incompatibility between RC3 and RC4
				if ( $col == 'language' && !( count( $val ) ) ) {
					$val = 'en-GB';
				}
				/* like:
					 * 	array( '!key' => 'value' )
					 * 	produces sql query with
					 * 	key NOT 'value'
					 */
				if ( strpos( $col, '!' ) !== false && strpos( $col, '!' ) == 0 ) {
					$col = trim( str_replace( '!', null, $col ) );
					$not = true;
				}
				/* current means get previous query */
				if ( is_string( $val ) && ( string )$val == '@CURRENT' ) {
					$n = $not ? 'NOT' : null;
					$val = $this->db->getQuery();
					$w[ ] = " ( {$col} {$n} IN ( {$val} ) ) ";
				}
				/* see SPDb#valid() */
				elseif ( $col == '@VALID' ) {
//					$col = '';
					$w[ ] = $val;
				}
				elseif ( is_numeric( $col ) ) {
					$w[ ] = $this->escape( $val );
				}
				/* like:
					 * 	array( 'key' => array( 'from' => 1, 'to' => 10 ) )
					 * 	produces sql query with
					 * 	key BETWEEN 1 AND 10
					 */
				elseif ( is_array( $val ) && ( isset( $val[ 'from' ] ) || isset( $val[ 'to' ] ) ) ) {
					if ( ( isset( $val[ 'from' ] ) && isset( $val[ 'to' ] ) ) && $val[ 'from' ] != SPC::NO_VALUE && $val[ 'to' ] != SPC::NO_VALUE ) {
						$val[ 'to' ] = $this->escape( $val[ 'to' ] );
						$val[ 'from' ] = $this->escape( $val[ 'from' ] );
						$w[ ] = " ( {$col} * 1.0 BETWEEN {$val['from']} AND {$val['to']} ) ";
					}
					elseif ( $val[ 'from' ] != SPC::NO_VALUE && $val[ 'to' ] == SPC::NO_VALUE ) {
						$val[ 'from' ] = $this->escape( $val[ 'from' ] );
						$w[ ] = " ( {$col} * 1.0 > {$val['from']} ) ";
					}
					elseif ( $val[ 'from' ] == SPC::NO_VALUE && $val[ 'to' ] != SPC::NO_VALUE ) {
						$val[ 'to' ] = $this->escape( $val[ 'to' ] );
						$w[ ] = " ( {$col} * 1.0 < {$val['to']} ) ";
					}

				}
				/* like:
					 * 	array( 'key' => array( 1,2,3,4 ) )
					 * 	produces sql query with
					 * 	key IN ( 1,2,3,4 )
					 */
				elseif ( is_array( $val ) ) {
					$v = [];
					foreach ( $val as $k ) {
						if ( strlen( $k ) || $k == SPC::NO_VALUE ) {
							$k = $k == SPC::NO_VALUE ? null : $k;
							$k = $this->escape( $k );
							$v[ ] = "'{$k}'";
						}
					}
					$val = implode( ',', $v );
					$n = $not ? 'NOT' : null;
					$w[ ] = " ( {$col} {$n} IN ( {$val} ) ) ";
				}
				else {
					/* changes the equal sign */
					$n = $not ? '!' : null;
					/* is lower */
					if ( strpos( $col, '<' ) ) {
						$equal = '<';
						$col = trim( str_replace( '<', null, $col ) );
					}
					/* is greater */
					elseif ( strpos( $col, '>' ) ) {
						$equal = '>';
						$col = trim( str_replace( '>', null, $col ) );
					}
					/* is like */
					elseif ( strpos( $val, '%' ) !== false ) {
						if ( $n == '!' ) {
							$n = null;
							$equal = 'NOT LIKE';
						}
						else {
							$equal = 'LIKE';
						}
					}
					/* regular expressions handling
						  * array( 'key' => 'REGEXP:^search$' )
						  */
					elseif ( strpos( $val, 'REGEXP:' ) !== false ) {
						$equal = 'REGEXP';
						$val = str_replace( 'REGEXP:', null, $val );
					}
					elseif ( strpos( $val, 'RLIKE:' ) !== false ) {
						$equal = 'RLIKE';
						$val = str_replace( 'RLIKE:', null, $val );
					}
					/* ^^ regular expressions handling ^^ */

					/* SQL functions within the query
						  * array( 'created' => 'FUNCTION:NOW()' )
						  */
					if ( strstr( $val, 'FUNCTION:' ) ) {
						$val = str_replace( 'FUNCTION:', null, $val );
					}
					else {
						$val = $this->escape( $val );
						$val = "'{$val}'";
					}
					$w[ ] = " ( {$col} {$n}{$equal}{$val} ) ";
				}
			}
			$where = implode( " {$andor} ", $w );
		}
		return $where;
	}


	/**
	 * Sample usage
	 *        $fields = array(
	 *            'url' => 'VARCHAR(255) NOT NULL',
	 *            'crid' => 'INT(11) NOT NULL AUTO_INCREMENT',
	 *            'state' => 'TINYINT(1) NOT NULL'
	 *        );
	 *        $keys = array(
	 *            'crid' => 'primary',
	 *            'url' => 'unique'
	 *        );
	 *        SPFactory::db()->createTable( 'crawler', $fields, $keys, true, 'MyISAM' );
	 *
	 * Would create query like:
	 *
	 *         CREATE TABLE IF NOT EXISTS `#__sobipro_crawler` (
	 *            `url`   VARCHAR(255) NOT NULL,
	 *            `crid`  INT(11)      NOT NULL AUTO_INCREMENT,
	 *            `state` TINYINT(1)   NOT NULL,
	 *            PRIMARY KEY (`crid`),
	 *            UNIQUE KEY `url` (`url`)
	 *         ) ENGINE = MyISAM DEFAULT CHARSET = utf8;
	 *
	 *
	 * @param string $name - table name without any prefix
	 * @param array $fields - array with fields definition like: $fields[ 'url' ] = 'VARCHAR(255) NOT NULL';
	 * @param array $keys - optional array with keys defined like: $keys[ 'url' ] = 'unique'; || $keys[ 'url, crid' ] = 'primary';
	 * @param bool $notExists - adds "CREATE TABLE IF NOT EXISTS"
	 * @param string $engine - optional engine type
	 * @param string $charset
	 * @return $this
	 */
	public function createTable( $name, $fields, $keys = [], $notExists = false, $engine = null, $charset = 'utf8' )
	{
		$name = "#__sobipro_{$name}";
		$query = $notExists ? "CREATE TABLE IF NOT EXISTS `{$name}` " : "CREATE TABLE `{$name}` ";
		$subQuery = null;
		$count = count( $fields );
		$i = 0;
		foreach ( $fields as $name => $definition ) {
			$i++;
			$subQuery .= "`{$name}` {$definition}";
			if ( $i < $count || count( $keys ) ) {
				$subQuery .= ', ';
			}
			else {
				$subQuery .= ' ';
			}
		}
		if ( count( $keys ) ) {
			$count = count( $keys );
			$i = 0;
			foreach ( $keys as $key => $type ) {
				$type = strtoupper( $type );
				if ( strstr( $key, ',' ) ) {
					$_keys = explode( ',', $key );
					$key = null;
					foreach ( $_keys as $i => $subkey ) {
						$_keys[ $i ] = "`{$subkey}`";
					}
					$key = implode( ',', $_keys );
				}
				else {
					$key = "`{$key}`";
				}
				$subQuery = "{$type} KEY ( {$key} )";
				if ( $i < $count ) {
					$subQuery .= ', ';
				}
				else {
					$subQuery .= ' ';
				}

			}
		}
		$query .= "( {$subQuery} ) ";
		if ( $engine ) {
			$query .= " ENGINE = {$engine} ";
		}
		$query .= "DEFAULT CHARSET = {$charset};";
		$this->exec( $query );
		return $this;
	}

	/* Arguments and or or
	  * (non-PHPdoc)
	  * @see Site/lib/base/SPDatabase#argsOr($val)
	  */
	public function argsOr( $val )
	{
		$cond = [];
		foreach ( $val as $i => $k ) {
			$equal = ' = ';
			if ( strpos( $i, '<' ) ) {
				$equal = '<';
				$i = trim( str_replace( '<', null, $i ) );
			}
			/* is greater */
			elseif ( strpos( $i, '>' ) ) {
				$equal = '>';
				$i = trim( str_replace( '>', null, $i ) );
			}
			$cond[ ] .= $i . $equal . $k;
		}
		$cond = implode( ' OR ', $cond );
		return '( ' . $cond . ' )';
	}

	/**
	 * Creates a "update" SQL query
	 *
	 * @param string $table - table to update
	 * @param array $set - two-dimensional array with table row name to update => new value
	 * @param array|string $where - SQL update condition
	 * @param int $limit
	 */
	public function update( $table, $set, $where, $limit = 0 )
	{
		$change = [];
		$where = $this->where( $where );
		foreach ( $set as $var => $state ) {
			if ( is_array( $state ) || is_object( $state ) ) {
				$state = SPConfig::serialize( $state );
			}
			$var = $this->escape( $var );
			$state = $this->escape( $state );
			if ( strstr( $state, 'FUNCTION:' ) ) {
				$state = str_replace( 'FUNCTION:', null, $state );
			}
			elseif ( strlen( $state ) == 2 && $state == '++' ) {
				$state = "{$var} + 1";
			}
			else {
				$state = "'{$state}'";
			}
			$change[ ] = "{$var} = {$state}";
		}
		$change = implode( ',', $change );
		$l = $limit ? " LIMIT {$limit} " : null;
		$this->exec( "UPDATE {$table} SET {$change} WHERE {$where}{$l}" );
	}

	/**
	 * Creates a "replace" SQL query
	 *
	 * @param string $table - table name
	 * @param array $values - two-dimensional array with table row name => value
	 * @throws SPException
	 */
	public function replace( $table, $values )
	{
		$v = [];
		foreach ( $values as $var => $val ) {
			if ( is_array( $val ) || is_object( $val ) ) {
				$val = SPConfig::serialize( $val );
			}
			$val = $this->escape( $val );
			if ( strstr( $val, 'FUNCTION:' ) ) {
				$v[ ] = str_replace( 'FUNCTION:', null, $val );
			}
			else {
				$v[ ] = "'{$val}'";
			}
		}
		$v = implode( ',', $v );
		try {
			$this->exec( "REPLACE INTO {$table} VALUES ({$v})" );
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
	}

	/**
	 * Creates a "insert" SQL query
	 *
	 * @param string $table - table name
	 * @param array $values - two-dimensional array with table row name => value
	 * @param bool $ignore - adds "IGNORE" after "INSERT" command
	 * @param bool $normalize - if the $values is a two-dimm, array and it's not complete - fit to the columns
	 * @throws SPException
	 * @return \SPJoomlaDb
	 */
	public function insert( $table, $values, $ignore = false, $normalize = false )
	{
		$ignore = $ignore ? 'IGNORE ' : null;
		$v = [];
		if ( $normalize ) {
			$this->normalize( $table, $values );
		}
		foreach ( $values as $val ) {
			if ( is_array( $val ) || is_object( $val ) ) {
				$val = SPConfig::serialize( $val );
			}
			$val = $this->escape( $val );
			if ( strstr( $val, 'FUNCTION:' ) ) {
				$v[ ] = str_replace( 'FUNCTION:', null, $val );
			}
			else {
				$v[ ] = "'{$val}'";
			}
		}
		$v = implode( ',', $v );
		try {
			$this->exec( "INSERT {$ignore} INTO {$table} VALUES ({$v})" );
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $this;
	}

	/**
	 * Fits a two dimensional array to the necessary columns of the given table
	 * @param string $table - table name
	 * @param array $values
	 */
	public function normalize( $table, &$values )
	{
		$normalised = [];
		$cols = $this->getColumns( $table );
		/* sort the properties in the same order */
		foreach ( $cols as $col ) {
			$normalised[ $col ] = isset( $values[ $col ] ) ? $values[ $col ] : '';
		}
		$values = $normalised;
	}

	/**
	 * Creates a "insert" SQL query with multiple values
	 *
	 * @param string $table - table name
	 * @param array $values - one-dimensional array with two-dimensional array with table row name => value
	 * @param bool $update - update existing row if cannot insert it because of duplicate primary key
	 * @param bool $ignore - adds "IGNORE" after "INSERT" command
	 * @throws SPException
	 * @return \SPJoomlaDb
	 */
	public function insertArray( $table, $values, $update = false, $ignore = false )
	{
		$ignore = $ignore ? 'IGNORE ' : null;
		$rows = [];
		foreach ( $values as $arr ) {
			$v = [];
			$vars = [];
			$k = [];
			foreach ( $arr as $var => $val ) {
				if ( is_array( $val ) || is_object( $val ) ) {
					$val = SPConfig::serialize( $val );
				}
				$vars[ ] = "{$var} = VALUES( {$var} )";
				$k[ ] = $var;
				$val = $this->escape( $val );
				if ( strstr( $val, 'FUNCTION:' ) ) {
					$v[ ] = str_replace( 'FUNCTION:', null, $val );
				}
				else {
					$v[ ] = "'{$val}'";
				}
			}
			$rows[ ] = implode( ',', $v );
		}
		$vars = implode( ', ', $vars );
		$rows = implode( " ), \n ( ", $rows );
		$k = implode( '`,`', $k );
		$update = $update ? "ON DUPLICATE KEY UPDATE {$vars}" : null;
		try {
			$this->exec( "INSERT {$ignore} INTO {$table} ( `{$k}` ) VALUES ({$rows}) {$update}" );
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $this;
	}

	/**
	 * Creates a "insert" SQL query with update if cannot insert it because of duplicate primary key
	 *
	 * @param string $table - table name
	 * @param array $values - two-dimensional array with table row name => value
	 * @throws SPException
	 * @return \SPJoomlaDb
	 */
	public function insertUpdate( $table, $values )
	{
		$v = [];
		$c = [];
		$k = [];
		foreach ( $values as $var => $val ) {
			if ( is_array( $val ) || is_object( $val ) ) {
				$val = SPConfig::serialize( $val );
			}
			$val = $this->escape( $val );
			if ( strstr( $val, 'FUNCTION:' ) ) {
				$f = str_replace( 'FUNCTION:', null, $val );
				$v[ ] = $f;
				$c[ ] = "{$var} = {$f}";
			}
			else {
				$v[ ] = "'{$val}'";
				$c[ ] = "{$var} = '{$val}'";
			}
			$k[ ] = "`{$var}`";

		}
		$v = implode( ',', $v );
		$c = implode( ',', $c );
		$k = implode( ',', $k );
		try {
			$this->exec( "INSERT INTO {$table} ({$k}) VALUES ({$v}) ON DUPLICATE KEY UPDATE {$c}" );
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $this;
	}

	/**
	 * Returns current query
	 *
	 * @return string
	 */
	public function getQuery()
	{
		return str_replace( $this->prefix, $this->db->getPrefix(), $this->db->getQuery() );
	}

	/**
	 * Returns queries counter
	 *
	 * @return int
	 */
	public function getCount()
	{
		return $this->count;
	}

	/**
	 * Execute the query
	 *
	 * @return mixed database resource or <var>false</var>.
	 */
	public function query()
	{
		$this->count++;
		return $this->db->execute();
	}

	/**
	 * Loads the first field of the first row returned by the query.
	 *
	 * @throws SPException
	 * @return string
	 */
	public function loadResult()
	{
		try {
			$r = $this->db->loadResult();
			$this->count++;
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $r;
	}

	/**
	 * Load an array of single field results into an array
	 *
	 * @throws SPException
	 * @return array
	 */
	public function loadResultArray()
	{
		try {
			$r = $this->db->loadResultArray();
			$this->count++;
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $r;
	}

	/**
	 * Load a assoc list of database rows
	 *
	 * @param string $key field name of a primary key
	 * @throws SPException
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 */
	public function loadAssocList( $key = null )
	{
		try {
			$r = $this->db->loadAssocList( $key );
			$this->count++;
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $r;
	}

	/**
	 * Loads the first row of a query into an object
	 *
	 * @throws SPException
	 * @return stdObject
	 */
	public function loadObject()
	{
		try {
			$r = $this->db->loadObject();
			$this->count++;
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		if ( $r && is_object( $r ) ) {
			$attr = get_object_vars( $r );
			foreach ( $attr as $property => $value ) {
				if ( is_string( $value ) && strstr( $value, '"' ) ) {
					$r->$property = class_exists( 'SPLang' ) ? SPLang::clean( $value ) : $value;
				}
			}
			return $r;
		}
	}

	/**
	 * Load a list of database objects
	 *
	 * @param string $key
	 * @throws SPException
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 */
	public function loadObjectList( $key = null )
	{
		try {
			$r = $this->db->loadObjectList( $key );
			$this->count++;
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $r;
	}

	/**
	 * Load the first row of the query.
	 *
	 * @throws SPException
	 * @return array
	 */
	public function loadRow()
	{
		try {
			$r = $this->db->loadRow();
			$this->count++;
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $r;
	}

	/**
	 * Load a list of database rows (numeric column indexing)
	 *
	 * @param string $key field name of a primary key
	 * @throws SPException
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 */
	public function loadRowList( $key = null )
	{
		try {
			$r = $this->db->loadRowList( $key );
			$this->count++;
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $r;
	}

	/**
	 * Returns an error statement
	 * @deprecated
	 * @return string
	 */
	public function stderr()
	{
		return $this->db->stderr();
	}

	/**
	 * Returns the ID generated from the previous insert operation
	 *
	 * @return int
	 */
	public function insertid()
	{
		return $this->db->insertid();
	}

	/**
	 * executing query (update/insert etc)
	 *
	 * @param string $query - query to execute
	 * @throws SPException
	 * @return mixed
	 */
	public function exec( $query )
	{
		$this->setQuery( $query );
		try {
			$r = $this->query();
		} catch ( Exception $e ) {
			throw new SPException( $e->getMessage() );
		}
		return $r;
	}

	/**
	 * Returns all rows of given table
	 * @param string $table
	 * @throws SPException
	 * @return array
	 */
	public function getColumns( $table )
	{
		static $cache = [];
		if ( !( isset( $cache[ $table ] ) ) ) {
			$this->setQuery( "SHOW COLUMNS FROM {$table}" );
			try {
				$cache[ $table ] = $this->loadResultArray();
			} catch ( Exception $e ) {
				throw new SPException( $e->getMessage() );
			}
		}
		return $cache[ $table ];
	}

	/**
	 * rolls back the current transaction, canceling its changes
	 *
	 * @return bool
	 */
	public function rollback()
	{
		return $this->exec( 'ROLLBACK;' ) !== false ? true : false;
	}

	/**
	 * begin a new transaction
	 *
	 * @return bool
	 */
	public function transaction()
	{
		return $this->exec( 'START TRANSACTION;' ) !== false ? true : false;
	}

	/**
	 * ommits the current transaction, making its changes permanent
	 *
	 * @return bool
	 */
	public function commit()
	{
		return $this->exec( 'COMMIT;' ) !== false ? true : false;
	}

	/**
	 * Returns current datetime in database acceptable format
	 * @return string
	 */
	public function now()
	{
		return date( SPFactory::config()->key( 'date.db_format', 'Y-m-d H:i:s' ) );
	}

	/**
	 * Creates yntax for joins two tables
	 *
	 * @param array $params - two cells array with table name <var>table</var>, alias name <var>as</var> and common key <var>key</var>
	 * @param string $through - join direction (left/right)
	 * @return string
	 */
	public function join( $params, $through = 'left' )
	{
		$through = strtoupper( $through );
		$join = null;
		if ( count( $params ) > 2 ) {
			$joins = [];
			$c = 0;
			foreach ( $params as $table ) {
				if ( isset( $table[ 'table' ] ) ) {
					$join = "\n {$table['table']} AS {$table['as']} ";
					if ( $c > 0 ) {
						if ( isset( $table[ 'key' ] ) ) {
							if ( is_array( $table[ 'key' ] ) ) {
								$join .= " ON {$table['key'][0]} =  {$table['key'][1]} ";
							}
							else {
								$join .= " ON {$params[0]['as']}.{$table['key']} =  {$table['as']}.{$table['key']} ";
							}
						}
					}
					$joins[ ] = $join;
				}
				$c++;
			}
			$join = implode( " {$through} JOIN ", $joins );
		}
		else {
			if (
					( isset( $params[ 0 ][ 'table' ] ) && isset( $params[ 0 ][ 'as' ] ) && isset( $params[ 0 ][ 'key' ] ) ) &&
					( isset( $params[ 1 ][ 'table' ] ) && isset( $params[ 1 ][ 'as' ] ) && isset( $params[ 1 ][ 'key' ] ) )
			) {
				$join = " {$params[0]['table']} AS {$params[0]['as']} {$through} JOIN {$params[1]['table']} AS {$params[1]['as']} ON {$params[0]['as']}.{$params[0]['key']} =  {$params[1]['as']}.{$params[1]['key']}";
			}
		}
		return $join;
	}

	/**
	 * Creates syntax to check the expiration date, state, and start publishing date off an row
	 * @param string $until - row name where the expiration date is stored
	 * @param string $since - row name where the start date is stored
	 * @param string $pub - row name where the state is stored (e.g. 'published')
	 * @param array $exception
	 * @return string
	 */
	public function valid( $until, $since = null, $pub = null, $exception = null )
	{
		$null = $this->getNullDate();
		$pub = $pub ? " AND {$pub} = 1 " : null;
		$stamp = date( SPFactory::config()->key( 'date.db_format', 'Y-m-d H:i:s' ), 0 );
		if ( $since ) {
			//			$since = "AND ( {$since} < '{$now}' OR {$since} IN( '{$null}', '{$stamp}' ) ) ";
			$since = "AND ( {$since} < NOW() OR {$since} IN( '{$null}', '{$stamp}' ) ) ";
		}
		if ( $exception && is_array( $exception ) ) {
			$ex = [];
			foreach ( $exception as $subject => $value ) {
				$ex[ ] = "{$subject} = '{$value}'";
			}
			$exception = implode( 'OR', $ex );
			$exception = 'OR ' . $exception;
		}
		//		return " ( ( {$until} > '{$now}' OR {$until} IN ( '{$null}', '{$stamp}' ) ) {$since} {$pub} ) ";
		return "( ( {$until} > NOW() OR {$until} IN ( '{$null}', '{$stamp}' ) ) {$since} {$pub} ) {$exception} ";
	}
}
