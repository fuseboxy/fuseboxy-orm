<?php
class ORM__RedBean {


	// properties
	private static $isReady = false;
	private static $libPath = __DIR__.'/../../lib/redbeanphp/5.3.1/rb.php';




	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	/**
	<fusedoc>
		<description>
			setup and connect to database
		</description>
		<io>
			<in>
				<structure name="config" scope="$fusebox">
					<structure name="db">
						<string name="provider" optional="yes" default="mysql" />
						<string name="host" />
						<string name="name" />
						<string name="username" />
						<string name="password" />
						<boolean name="freeze" optional="yes" />
					</structure>
				</structure>
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function init() {
		// check status
		if ( self::$isReady ) return true;
		// load library
		$path = self::$libPath;
		if ( !is_file($path) ) {
			self::$error = "RedBeanPHP library is missing ({$path})";
			return false;
		}
		require_once($path);
		// load config
		$dbConfig = F::config('db');
		// default config
		if ( empty($dbConfig['provider']) ) $dbConfig['provider'] = 'mysql';
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
		// connect to database
		try {
			R::setup($dbConfig['provider'].':host='.$dbConfig['host'].';dbname='.$dbConfig['name'], $dbConfig['username'], $dbConfig['password']);
			if ( isset($dbConfig['freeze']) ) R::freeze($dbConfig['freeze']);
		} catch (Exception $e) {
			self::$error = $e->getMessage();
			return false;
		}
		// mark status
		self::$isReady = true;
		// done!
		return true;
	}




	// get all records
	public static function all($beanType, $sql) {
		if ( self::init() === false ) return false;
		return R::findAll($beanType, $sql);
	}




	// get columns of specific table
	public static function columns($beanType) {
		if ( self::init() === false ) return false;
		// proceed
		try {
			$result = R::getColumns($beanType);
		} catch (Exception $e) {
			self::$error = $e->getMessage();
			return false;
		}
		// done!
		return $result;
	}




	// count number of records accorrding to criteria
	public static function count($beanType, $sql, $param) {
		if ( self::init() === false ) return false;
		return R::count($beanType, $sql, $param);
	}




	// delete specific record
	public static function delete($bean) {
		if ( self::init() === false ) return false;
		// proceed
		try {
			R::trash($bean);
		} catch (Exception $e) {
			self::$error = $e->getMessage();
			return false;
		}
		// done!
		return true;
	}




	// obtain first record according to the criteria
	public static function first($beanType, $sql, $param) {
		if ( self::init() === false ) return false;
		return R::findOne($beanType, $sql, $param);
	}




	// obtain specific record according to ID, or...
	// obtain multiple records according to criteria
	public static function get($beanType, $sqlOrID, $param) {
		if ( self::init() === false ) return false;
		// get multiple records
		if ( !is_numeric($sqlOrID) ) {
			$result = R::find($beanType, $sqlOrID, $param);
		// get single record
		} else {
			$result = R::load($beanType, $sqlOrID);
			if ( empty($result->id) ) {
				self::$error = "Record not found (id={$sqlOrID})";
				return false;
			}
		}
		// done!
		return $result;
	}




	// create new container (preloaded with data)
	public static function new($beanType, $data) {
		if ( self::init() === false ) return false;
		// create container & import data
		try {
			$bean = R::dispense($beanType);
			if ( !empty($data) ) $bean->import($data);
		} catch (Exception $e) {
			self::$error = $e->getMessage();
			return false;
		}
		// done!
		return $bean;
	}




	// run sql statement
	public static function query($sql, $param) {
		if ( self::init() === false ) return false;
		// run method according to nature of query
		try {
			$sql = trim($sql);
			$result = ( stripos($sql, 'SELECT') == 0 ) ? R::getAll($sql, $param) : R::exec($sql, $param);
		} catch (Exception $e) {
			self::$error = $e->getMessage();
			return false;
		}
		// done!
		return $result;
	}




	// save object into database
	public static function save($bean) {
		if ( self::init() === false ) return false;
		// save record
		try {
			$id = R::store($bean);
		} catch (Exception $e) {
			self::$error = $e->getMessage();
			return false;
		}
		// validation
		if ( empty($id) ) {
			self::$error = empty($bean->id) ? 'Error occurred while creating record' : "Error occurred while updating record (id={$bean->id})";
			return false;
		}
		// done!
		return $id;
	}


} // class