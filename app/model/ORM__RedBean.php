<?php
require_once 'iORM.php';
require_once dirname(dirname(__DIR__)).'/lib/redbeanphp/5.3.1/rb.php';
class ORM__RedBean implements iORM {


	// properties
	private static $isReady = false;


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
				<!-- config -->
				<structure name="$fusebox->config|FUSEBOXY_DB">
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
		// load config (from framework or constant)
		if ( class_exists('F') ) $dbConfig = F::config('db');
		if ( empty($dbConfig) and defined('FUSEBOXY_DB') ) $dbConfig = FUSEBOXY_DB;
		// default config
		if ( empty($dbConfig['provider']) ) $dbConfig['provider'] = 'mysql';
		// check config
		if ( empty($dbConfig) ) {
			self::$error = '[ORM__RedBean::init] Database config is missing';
			return false;
		} elseif ( empty($dbConfig['host']) ) {
			self::$error = '[ORM__RedBean::init] Database config [host] is required';
			return false;
		} elseif ( empty($dbConfig['name']) ) {
			self::$error = '[ORM__RedBean::init] Database config [name] is required';
			return false;
		} elseif ( empty($dbConfig['username']) ) {
			self::$error = '[ORM__RedBean::init] Database config [username] is required';
			return false;
		// allow empty password but must be defined
		} elseif ( !isset($dbConfig['password']) ) {
			self::$error = '[ORM__RedBean::init] Database config [password] is required';
			return false;
		}
		// connect to database
		try {
			R::setup($dbConfig['provider'].':host='.$dbConfig['host'].';dbname='.$dbConfig['name'], $dbConfig['username'], $dbConfig['password']);
			if ( isset($dbConfig['freeze']) ) R::freeze($dbConfig['freeze']);
		} catch (Exception $e) {
			self::$error = '[ORM__RedBean::init] '.$e->getMessage();
			return false;
		}
		// mark status
		self::$isReady = true;
		// done!
		return true;
	}


	// close connection
	public static function destroy() {
		try {
			R::close();
		} catch (Exception $e) {
			self::$error = '[ORM__RedBean::destroy] '.$e->getMessage();
			return false;
		}
		self::$isReady = false;
		return true;
	}


	// get all records
	public static function all($beanType, $order) {
		if ( self::init() === false ) return false;
		return R::findAll($beanType, $order);
	}


	// get columns of specific table
	public static function columns($beanType) {
		if ( self::init() === false ) return false;
		// proceed
		try {
			$result = R::getColumns($beanType);
		} catch (Exception $e) {
			self::$error = '[ORM__RedBean::columns] '.$e->getMessage();
			return false;
		}
		// done!
		return $result;
	}


	// count number of records accorrding to criteria
	public static function count($beanType, $filter, $param) {
		if ( self::init() === false ) return false;
		return R::count($beanType, $filter, $param);
	}


	// delete specific record
	public static function delete($bean) {
		if ( self::init() === false ) return false;
		// proceed
		try {
			R::trash($bean);
		} catch (Exception $e) {
			self::$error = '[ORM__RedBean::delete] '.$e->getMessage();
			return false;
		}
		// done!
		return true;
	}


	// obtain first record according to the criteria
	public static function first($beanType, $filter, $param) {
		if ( self::init() === false ) return false;
		return R::findOne($beanType, $filter, $param);
	}


	// obtain specific record according to ID, or...
	// obtain multiple records according to criteria
	public static function get($beanType, $filterOrID, $param) {
		if ( self::init() === false ) return false;
		// get multiple records, or...
		if ( !is_numeric($filterOrID) ) return R::find($beanType, $filterOrID, $param);
		// get specific record
		$result = R::load($beanType, $filterOrID);
		if ( empty($result->id) ) {
			self::$error = "[ORM__RedBean::get] Record not found (id={$filterOrID})";
			return false;
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
			// validation
			if ( !empty($data) ) foreach ( $data as $key => $val ) {
				// check key
				if ( is_numeric($key) ) {
					self::$error = '[ORM__RedBean::new] Import data must be associative array';
					return false;
				// check simple value
				} elseif ( is_array($val) or is_object($val) ) {
					self::$error = "[ORM__RedBean::new] Import data [{$key}] must be simple value";
					return false;
				}
			}
			// import data
			if ( !empty($data) ) $bean->import($data);
		} catch (Exception $e) {
			self::$error = '[ORM__RedBean::new] '.$e->getMessage();
			return false;
		}
		// done!
		return $bean;
	}


	// run sql statement
	public static function query($sql, $param, $return) {
		if ( self::init() === false ) return false;
		// fix arguments
		$sql = trim($sql);
		$return = strtolower($return);
		// determine operation
		$arr = explode(' ', $sql);
		$operation = strtoupper(array_shift($arr));
		// run method according to nature of query
		try {
			$sql = trim($sql);
			if ( in_array($operation, ['INSERT','UPDATE','DELETE']) ) $result = R::exec($sql, $param);
			elseif ( $return == 'row' ) $result = R::getRow($sql, $param);
			elseif ( $return == 'cell' ) $result = R::getCell($sql, $param);
			elseif ( in_array($return, ['col','column']) ) $result = R::getCol($sql, $param);
			else $result = R::getAll($sql, $param);
		} catch (Exception $e) {
			self::$error = '[ORM__RedBean::query] '.$e->getMessage();
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
			self::$error = '[ORM__RedBean::save] '.$e->getMessage();
			return false;
		}
		// validation
		if ( empty($id) ) {
			self::$error = empty($bean->id) ? '[ORM__RedBean::save] Error occurred while creating record' : "[ORM__RedBean::save] Error occurred while updating record (id={$bean->id})";
			return false;
		}
		// done!
		return $id;
	}


	// get name of tables
	public static function tables() {
		return R::inspect();
	}


} // class