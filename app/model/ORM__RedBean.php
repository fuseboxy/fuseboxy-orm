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




	/**
	<fusedoc>
		<description>
			get all records (sort by id)
		</description>
		<io>
			<in>
				<string name="$beanType" />
				<string name="$sql" />
			</in>
			<out>
				<structure name="~return~">
					<object name="~id~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function all($beanType, $sql) {
		if ( self::init() === false ) return false;
		return R::findAll($beanType, $sql);
	}




	/**
	<fusedoc>
		<description>
			get columns of specific table
		</description>
		<io>
			<in>
				<string name="$beanType" />
			</in>
			<out>
				<array name="~return~">
					<string name="+" />
				</array>
			</out>
		</io>
	</fusedoc>
	*/
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




	/**
	<fusedoc>
		<description>
			count number of records accorrding to criteria (if any)
		</description>
		<io>
			<in>
				<string name="$beanType" />
				<string name="$sql" />
				<string name="$param" />
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function count($beanType, $sql, $param) {
		if ( self::init() === false ) return false;
		return R::count($beanType, $sql, $param);
	}




	/**
	<fusedoc>
		<description>
			delete specific record
		</description>
		<io>
			<in>
				<object name="$bean" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
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




	/**
	<fusedoc>
		<description>
			obtain first record according to the criteria
		</description>
		<io>
			<in>
				<string name="$beanType" />
				<string name="$sql" />
				<array name="$param" />
			</in>
			<out>
				<object name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function first($beanType, $sql, $param) {
		if ( self::init() === false ) return false;
		return R::findOne($beanType, $sql, $param);
	}




	/**
	<fusedoc>
		<description>
			obtain specific record according to ID, or...
			obtain multiple records according to criteria
		</description>
		<io>
			<in>
				<string name="$beanType" />
				<string_or_number name="$sqlOrID" />
				<array name="$param" />
			</in>
			<out>
				<!-- single record -->
				<object name="~return~" optional="yes" />
				<!-- multiple records -->
				<structure name="~return~" optional="yes">
					<object name="~id~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
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




	/**
	<fusedoc>
		<description>
			create empty new container (preload data when specified)
		</description>
		<io>
			<in>
				<string name="$beanType" />
			</in>
			<out>
				<object name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
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




	/**
	<fusedoc>
		<description>
			run sql statement
		</description>
		<io>
			<in>
				<string name="$sql" />
				<array name="$param" optional="yes" />
			</in>
			<out>
				<!-- select -->
				<array name="~return~">
					<structure name="+" />
				</array>
				<!-- insert / update / delete -->
				<number name="~return~" comments="number of affected records; zero affected row does not mean error" />
			</out>
		</io>
	</fusedoc>
	*/
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




	/**
	<fusedoc>
		<description>
			save object into database
		</description>
		<io>
			<in>
				<object name="$bean" />
			</in>
			<out>
				<number name="~return~" comments="id of record inserted/updated" />
			</out>
		</io>
	</fusedoc>
	*/
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