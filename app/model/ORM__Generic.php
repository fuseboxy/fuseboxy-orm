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
			self::$error = 'Unable to connect to MySQL : '.mysqli_connect_error().' ('.mysqli_connect_errno().')';
			return false;
		}
		// done!
		return true;
	}


	// get all records
	public static function all($beanType, $order) {
		return self::query("SELECT * FROM `{$beanType}` {$order} ");
	}


	// get columns of specific table
	public static function columns($beanType) {
		$result = array();
		// get column info
		$sql = "SHOW COLUMNS FROM `{$beanType}` ";
		$data = self::query($sql);
		if ( $data === false ) return false;
		// put into result
		foreach ( $data as $item ) $result[] = $item['field'];
		// done!
		return $result;
	}


	// count number of records accorrding to criteria
	public static function count($beanType, $filter, $param) {
		// prepare statement
		$sql  = "SELECT COUNT(*) AS recordcount FROM `{$beanType}` ";
		$sql .= ( stripos(trim($filter), 'ORDER') === 0 ) ? $filter : " WHERE {$filter} ";
		// get data
		$data = self::query($sql, $param);
		if ( $data === false ) return false;
		// done!
		return array_shift($data)['recordcount'];
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
		// prepare statement
		$sql  = "SELECT * FROM `{$beanType}` ";
		$sql .= ( stripos(trim($filter), 'ORDER') === 0 ) ? $filter : " WHERE {$filter} ";
		$sql .= " LIMIT 1 ";
		// get data
		$data = self::query($sql, $param);
		if ( $data === false ) return false;
		// done!
		return array_shift($data);
	}


	// obtain specific record according to ID, or...
	// obtain multiple records according to criteria
	public static function get($beanType, $filterOrID, $param) {
		// get multiple records, or...
		if ( !is_numeric($filterOrID) ) {
			$sql  = "SELECT * FROM `{$beanType}` ";
			$sql .= ( stripos(trim($filterOrID), 'ORDER') === 0 ) ? $filterOrID : " WHERE {$filterOrID} ";
			return self::query($sql, $param);
		}
		// get specific record
		$data = self::first($beanType, 'id = ?', [$filterOrID]);
		// validation (when specific record)
		if ( empty($data) ) {
			self::$error = "Record not found (id={$filterOrID})";
			return false;
		}
		// done!
		return $data;
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
			} elseif ( !is_string($val) or !is_numeric($val) or !is_boolean($val) ) {
				self::$error = "Field [{$key}] must be simple value";
				return false;
			// import
			} else {
				$bean->{$key} = is_boolean($val) ? (int)$val : $val;
			}
		}
		// done!
		return $bean;
	}


	// run sql statement
	public static function query($sql, $param) {
	}


	// save object into database
	public static function save($bean) {{
		// validation
		if ( empty($bean->__type__) ) {
			self::$error = 'Bean type is unknown';
			return false;
		}
		// prepare statement
		if ( empty($bean->id) ) {
			self::$error = 'ID is empty';
			return false;
		}
		// done!
		return self::query($sql, $param);
	}


} // class