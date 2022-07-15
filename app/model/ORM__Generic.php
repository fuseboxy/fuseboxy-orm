<?php
require_once 'iORM.php';
class ORM__Generic implements iORM {


	// properties
	private static $conn;


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }


	/**
	<fusedoc>
		<description>
			prepare database connection
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$fusebox->config|FUSEBOXY_ORM_DB">
					<structure name="db">
						<string name="host" />
						<string name="name" />
						<string name="username" />
						<string name="password" />
					</structure>
				</structure>
				<!-- cache -->
				<object name="$conn" scope="self" optional="yes" />
			</in>
			<out>
				<!-- cache -->
				<object name="$conn" scope="self" optional="yes" />
				<!-- return value -->
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function init() {
		// check status
		if ( self::$conn ) return true;
		// load config (from framework or constant)
		if ( class_exists('F') ) $dbConfig = F::config('db');
		if ( empty($dbConfig) and defined('FUSEBOXY_ORM_DB') ) $dbConfig = FUSEBOXY_ORM_DB;
		// check config
		if ( empty($dbConfig) ) {
			self::$error = '[ORM__Generic::init] Database config is missing';
			return false;
		} elseif ( empty($dbConfig['host']) ) {
			self::$error = '[ORM__Generic::init] Database config [host] is required';
			return false;
		} elseif ( empty($dbConfig['name']) ) {
			self::$error = '[ORM__Generic::init] Database config [name] is required';
			return false;
		} elseif ( empty($dbConfig['username']) ) {
			self::$error = '[ORM__Generic::init] Database config [username] is required';
			return false;
		// allow empty password but must be defined
		} elseif ( !isset($dbConfig['password']) ) {
			self::$error = '[ORM__Generic::init] Database config [password] is required';
			return false;
		}
		// keep connection opened for this HTTP request
		self::$conn = @mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['name']);
		if ( !self::$conn ) {
			self::$error = '[ORM__Generic::init] Error occurred while connecting to MySQL : '.mysqli_connect_error().' ('.mysqli_connect_errno().')';
			return false;
		}
		// done!
		return true;
	}


	// close connection
	public static function destroy() {
		if ( self::$conn ) mysqli_close(self::$conn);
		self::$conn = null;
		return true;
	}


	// get all records
	public static function all($beanType, $order) {
		$order = trim($order);
		// fix order clause
		if ( empty($order) ) $order = 'ORDER BY id ASC';
		// validation
		$firstWord = strtoupper( explode(' ', $order, 2)[0] );
		if ( !empty($order) and $firstWord != 'ORDER' ) {
			self::$error = '[ORM__Generic::all] Only [ORDER BY] clause is allowed';
			return false;
		}
		// get data
		return self::get($beanType, $order, []);
	}


	// get columns of specific table
	public static function columns($beanType) {
		$result = array();
		// get column info
		$data = self::query("SHOW COLUMNS FROM `{$beanType}` ");
		if ( $data === false ) return false;
		// move to result container
		foreach ( $data as $item ) $result[$item['Field']] = $item['Type'];
		// done!
		return $result;
	}


	// count number of records accorrding to criteria
	public static function count($beanType, $filter, $param) {
		$filter = trim($filter);
		$firstWord = strtoupper( explode(' ', $filter, 2)[0] );
		if ( !empty($filter) and !in_array($firstWord, ['WHERE','ORDER','LIMIT']) ) $filter = 'WHERE '.$filter;
		$sql = "SELECT COUNT(*) FROM `{$beanType}` {$filter} ";
		return self::query($sql, $param, 'cell');
	}


	// delete specific record
	public static function delete($bean) {
		// validation
		if ( empty($bean->__type__) ) {
			self::$error = '[ORM__Generic::delete] Bean type is unknown';
			return false;
		} elseif ( empty($bean->id) ) {
			self::$error = '[ORM__Generic::delete] ID is empty';
			return false;
		}
		// prepare statement
		$sql = "DELETE FROM `{$bean->__type__}` WHERE id = ? ";
		$param = array($bean->id);
		// done!
		return self::query($sql, $param, null);
	}


	// obtain first record according to the criteria
	public static function first($beanType, $filter, $param) {
		$filter = trim($filter);
		$firstWord = strtoupper( explode(' ', $filter, 2)[0] );
		if ( !empty($filter) and !in_array($firstWord, ['WHERE','ORDER']) ) $filter = 'WHERE '.$filter;
		// get data
		$sql = "SELECT * FROM `{$beanType}` {$filter} LIMIT 1 ";
		$data = self::query($sql, $param, 'row');
		if ( $data === false ) return false;
		// turn into bean
		return self::new($beanType, $data);
	}


	// obtain specific record according to ID, or...
	// obtain multiple records according to criteria
	public static function get($beanType, $filterOrID, $param) {
		$result = array();
		// get single record (when necessary)
		if ( is_numeric($filterOrID) ) return self::getByID($beanType, $filterOrID);
		// adjust filter
		$filter = trim($filterOrID);
		$firstWord = strtoupper( explode(' ', $filter, 2)[0] );
		if ( !empty($filter) and !in_array($firstWord, ['WHERE','ORDER']) ) $filter = 'WHERE '.$filter;
		// get multiple records
		$sql = "SELECT * FROM `{$beanType}` {$filter} ";
		$data = self::query($sql, $param, 'all');
		if ( $data === false ) return false;
		// turn into bean
		foreach ( $data as $row ) {
			$result[ $row['id'] ] = self::new($beanType, $row);
			if ( $result[ $row['id'] ] === false ) return false;
		}
		// done!
		return $result;
	}


	// obtain specific record by ID
	public static function getByID($beanType, $id) {
		$bean = self::first($beanType, 'id = ?', [ $id ]);
		if ( $bean === false ) return false;
		return $bean;
	}


	// create new container (preload with data)
	public static function new($beanType, $data) {
		$bean = new stdClass();
		$bean->__type__ = $beanType;
		// import & validation
		foreach ( $data as $key => $val ) {
			// check key
			if ( is_numeric($key) ) {
				self::$error = '[ORM__Generic::new] Data must be associative array';
				return false;
			// check simple value
			} elseif ( is_array($val) or is_object($val) ) {
				self::$error = "[ORM__Generic::new] Field [{$key}] must be simple value";
				return false;
			// import
			} else {
				$bean->{$key} = is_bool($val) ? (int)$val : $val;
			}
		}
		// done!
		return $bean;
	}


	// run sql statement
	public static function query($sql, $param, $return) {
		if ( self::init() === false ) return false;
		// container
		$result = array();
		// fix arguments
		$sql = trim($sql);
		$return = strtolower($return);
		// determine operation
		$arr = array_map('trim', explode(' ', str_replace("\n", ' ', $sql)));
		$operation = strtoupper(array_shift($arr));
		// determine param types
		$paramTypes = '';
		foreach ( $param as $key => $val ) {
			// fix value
			if ( is_bool($val) ) $param[$key] = (int)$val;
			// determine type
			if ( is_int($val) ) $paramTypes .= 'i';
			elseif ( is_numeric($val) ) $paramTypes .= 'd';
			else $paramTypes .= 's';
		}
		// proceed to execute statement
		try {
			$stmt = self::$conn->prepare($sql);
			if ( !empty($param) ) $stmt->bind_param($paramTypes, ...$param);
			$stmt->execute();
			// obtain query result object (when necessary)
			if ( !in_array($operation, ['INSERT','UPDATE','DELETE']) ) $qryResult = $stmt->get_result();
			// obtain result according to operation
			if ( $operation == 'INSERT' ) $result = $stmt->insert_id;
			elseif ( in_array($operation, ['UPDATE','DELETE']) ) $result = $stmt->affected_rows;
			elseif ( $return == 'row' ) $result = $qryResult->fetch_array(MYSQLI_ASSOC);
			elseif ( $return == 'cell' ) { $row = $qryResult->fetch_array(MYSQLI_ASSOC); $result = empty($row) ? '' : array_shift($row); }
			elseif ( in_array($return, ['col','column']) ) while ( $row = $qryResult->fetch_array(MYSQLI_ASSOC) ) $result[] = array_shift($row);
			else while ( $row = $qryResult->fetch_array(MYSQLI_ASSOC) ) $result[] = $row;
		// if any error...
		} catch (Exception $e) {
			self::$error = '[ORM__Generic::query] '.$e->getMessage();
			return false;
		}
		// done!
		return $result;
	}


	// save object into database
	public static function save($bean) {
		// validation
		if ( empty($bean->__type__) ) {
			self::$error = '[ORM__Generic::save] Bean type is unknown';
			return false;
		}
		// obtain data fields
		$data = get_object_vars($bean);
		unset($data['__type__']);
		// prepare statement
		if ( empty($bean->id) ) {
			if ( isset($data['id']) ) unset($data['id']);  // remove ID when empty string
			$cols = array_keys($data);
			foreach ( $cols as $key => $val ) $cols[$key] = "`{$val}`";
			$sql = "INSERT INTO `{$bean->__type__}` (".implode(',', $cols).") VALUES (".ORM::slots($data).")";
			$param = array_values($data);
		} else {
			$arr = array();
			foreach ( $data as $key => $val ) $arr[] = "`{$key}` = ?";
			$sql = "UPDATE `{$bean->__type__}` SET ".implode(',', $arr)." WHERE id = ? ";
			$param = array_values($data);
			$param[] = $bean->id;
		}
		// get result
		$operation = explode(' ', $sql, 2)[0];
		$result = self::query($sql, $param, null);
		// done!
		return ( $operation == 'INSERT' ) ? $result : $bean->id;
	}


	// get name of all tables
	public static function tables() {
		return self::query('SHOW TABLES', [], 'col');
	}


} // class