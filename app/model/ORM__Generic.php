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
		return self::query("SELECT * FROM `{$beanType}` {$order}");
	}


	// get columns of specific table
	public static function columns($beanType) {
	}


	// count number of records accorrding to criteria
	public static function count($beanType, $filter, $param) {
		$filter = trim($filter);
		// prepare statement
		$sql = "SELECT COUNT(*) AS recordcount FROM `{$beanType}` ";
		if ( stripos($filter, 'ORDER') !== false ) $sql .= " WHERE ";
		$sql .= $filter;
		// get data
		$data = self::query($sql, $param);
		if ( $data === false ) return false;
		// done!
		return array_shift($data)['recordcount'];
	}


	// delete specific record
	public static function delete($bean) {
	}


	// obtain first record according to the criteria
	public static function first($beanType, $filter, $param) {
		$filter = trim($filter);
		// prepare statement
		$sql = "SELECT COUNT(*) AS recordcount FROM `{$beanType}` ";
		if ( stripos($filter, 'ORDER') !== false ) $sql .= " WHERE ";
		$sql .= $filter;
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
		$filterOrID = trim($filterOrID);
	}


	// create new container (preloaded with data)
	public static function new($beanType, $data) {
	}


	// run sql statement
	public static function query($sql, $param) {
	}


	// save object into database
	public static function save($bean) {
	}


} // class