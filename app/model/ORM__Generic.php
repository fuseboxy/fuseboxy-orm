<?php
require_once 'ORM__Interface.php';
class ORM__Generic implements ORM__Interface {


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
				<object name="$conn" scope="self" optional="yes" />
				<structure name="config" scope="$fusebox">
					<structure name="db">
						<string name="host" />
						<string name="name" />
						<string name="username" />
						<string name="password" />
					</structure>
				</structure>
			</in>
			<out>
				<object name="$conn" scope="self" optional="yes" />
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function init() {
		// check status
		if ( self::$conn ) return true;
		// load config
		$dbConfig = F::config('db');
		// check config
		if ( empty($dbConfig) ) {
			self::$error = 'Database config is missing';
			return false;
		} elseif ( empty($dbConfig['host']) ) {
			self::$error = 'Database config [host] is required';
			return false;
		} elseif ( empty($dbConfig['name']) ) {
			self::$error = 'Database config [name] is required';
			return false;
		} elseif ( empty($dbConfig['username']) ) {
			self::$error = 'Database config [username] is required';
			return false;
		// allow empty password but must be defined
		} elseif ( !isset($dbConfig['password']) ) {
			self::$error = 'Database config [password] is required';
			return false;
		}
		// keep connection opened for this HTTP request
		self::$conn = @mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['name']);
		if ( !self::$conn ) {
			self::$error = 'Error occurred while connecting to MySQL : '.mysqli_connect_error().' ('.mysqli_connect_errno().')';
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
		return self::query("SELECT * FROM `{$beanType}` {$order} ");
	}


	// get columns of specific table
	public static function columns($beanType) {
		return self::query("SHOW COLUMNS FROM `{$beanType}` ", [], 'col');
	}


	// count number of records accorrding to criteria
	public static function count($beanType, $filter, $param) {
		$sql  = "SELECT COUNT(*) FROM `{$beanType}` ";
		$sql .= ( stripos(trim($filter), 'ORDER') === 0 ) ? $filter : " WHERE {$filter} ";
		return self::query($sql, $param, 'cell');
	}


	// delete specific record
	public static function delete($bean) {
		// validation
		if ( empty($bean->__type__) ) {
			self::$error = 'Bean type is unknown';
			return false;
		} elseif ( empty($bean->id) ) {
			self::$error = 'ID is empty';
			return false;
		}
		// prepare statement
		$sql = "DELETE FROM `{$bean->__type__}` WHERE id = ? ";
		$param = array($bean->id);
		// done!
		return self::query($sql, $param);
	}


	// obtain first record according to the criteria
	public static function first($beanType, $filter, $param) {
		$sql  = "SELECT * FROM `{$beanType}` ";
		$sql .= ( stripos(trim($filter), 'ORDER') === 0 ) ? $filter : " WHERE {$filter} ";
		$sql .= " LIMIT 1 ";
		return self::query($sql, $param, 'row');
	}


	// obtain specific record according to ID, or...
	// obtain multiple records according to criteria
	public static function get($beanType, $filterOrID, $param) {
		$result = array();
		// get multiple records, or...
		if ( !is_numeric($filterOrID) ) {
			$sql  = "SELECT * FROM `{$beanType}` ";
			$sql .= ( stripos(trim($filterOrID), 'ORDER') === 0 ) ? $filterOrID : " WHERE {$filterOrID} ";
			$data = self::query($sql, $param);
			if ( $data === false ) return false;
			foreach ( $data as $row ) $result[ $row['id'] ] = self::new($beanType, $row);
		}
		// get specific record
		$result = self::first($beanType, 'id = ?', [$filterOrID]);
		// validation (when specific record)
		if ( empty($result) ) {
			self::$error = "Record not found (id={$filterOrID})";
			return false;
		}
		// done!
		return $result;
	}


	// create new container (preload with data)
	public static function new($beanType, $data) {
		$bean = new stdClass();
		$bean->__type__ = $beanType;
		// import & validation
		foreach ( $data as $key => $val ) {
			// check key
			if ( is_numeric($key) ) {
				self::$error = 'Data must be associative array';
				return false;
			// check simple value
			} elseif ( !is_string($val) and !is_numeric($val) and !is_bool($val) ) {
				self::$error = "Field [{$key}] must be simple value";
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
	public static function query($sql, $param=[], $return='all') {
		if ( self::init() === false ) return false;
		// container
		$result = array();
		// fix arguments
		$sql = trim($sql);
		$return = strtolower($return);
		// determine operation
		$arr = explode(' ', $sql);
		$operation = strtoupper(array_shift($arr));
		// determine param types
		$paramTypes = '';
		foreach ( $param as $item ) {
			if ( is_int($item) ) $paramTypes .= 'i';
			elseif ( is_numeric($item) ) $paramTypes .= 'd';
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
			self::$error = $e->getMessage();
			return false;
		}
		// done!
		return $result;
	}


	// save object into database
	public static function save($bean) {
		// validation
		if ( empty($bean->__type__) ) {
			self::$error = 'Bean type is unknown';
			return false;
		}
		// obtain data fields
		$data = get_object_vars($bean);
		unset($data['__type__']);
		// prepare statement
		if ( empty($bean->id) ) {
			if ( isset($data['id']) ) unset($data['id']);
			$sql = "INSERT INTO `{$bean->__type__}` (".implode(',', array_keys($data)).") VALUES (".ORM::slots($data).")";
			$param = array_values($data);
		} else {
			$arr = array();
			foreach ( $data as $key => $val ) $arr[] = "`{$key}` = ?";
			$sql = "UPDATE `{$bean->__type__}` SET ".implode(',', $arr)." WHERE id = ? ";
			$param = array_values($data);
			$param[] = $bean->id;
		}
		// done!
		return self::query($sql, $param);
	}


	// get name of all tables
	public static function tables() {
		return self::query('SHOW TABLES');
	}


} // class