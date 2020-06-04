<?php
class ORM {


	// properties
	private static $libPath = array('connectDB' => __DIR__.'/../../lib/redbeanphp/5.3.1/rb.php');
	private static $status = 0;


	// define constant
	const DB_CONNECTED = 1;


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	/**
	<fusedoc>
		<description>
			setup redbean and connect to database
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
						<boolean name="freeze" />
					</structure>
				</structure>
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	private static function connectDB() {
		// check status
		if ( self::$status != self::DB_CONNECTED ) {
			// load library
			$path = self::$libPath['connectDB'];
			if ( !is_file($path) ) {
				self::$error = "RedBeanPHP library is missing ({$path})";
				return false;
			}
			require_once($path);
			// check config
			$dbConfig = F::config('db');
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
			// default config
			if ( empty($dbConfig['provider']) ) $dbConfig['provider'] = 'mysql';
			// connect to database
			try {
				R::setup($dbConfig['provider'].':host='.$dbConfig['host'].';dbname='.$dbConfig['name'], $dbConfig['username'], $dbConfig['password']);
				if ( isset($dbConfig['freeze']) ) R::freeze($dbConfig['freeze']);
			} catch (Exception $e) {
				self::$error = $e->getMessage();
				return false;
			}
		}
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
			</in>
			<out>
				<structure name="~return~">
					<object name="~id~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function all($beanType) {
		if ( self::connectDB() === false ) return false;
		// done!
		return R::findAll($beanType);
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
		if ( self::connectDB() === false ) return false;
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
	public static function count($beanType, $sql=null, $param=null) {
		if ( self::connectDB() === false ) return false;
		// done!
		return R::count($beanType, $sql, $param);
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
	public static function get($beanType, $sqlOrID=null, $param=null) {
		if ( self::connectDB() === false ) return false;
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
	public static function first($beanType, $sql=null, $param=null) {
		if ( self::connectDB() === false ) return false;
		// done!
		return R::findOne($beanType, $sql, $param);
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
	public static function new($beanType, $data=null) {
		if ( self::connectDB() === false ) return false;
		// create container
		$bean = R::dispense($beanType);
		if ( !empty($data) ) $bean->import($data);
		// done!
		return $bean;
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
		if ( self::connectDB() === false ) return false;
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
		if ( self::connectDB() === false ) return false;
		// save record
		$id = R::store($bean);
		// validation
		if ( empty($id) ) {
			self::$error = empty($bean->id) ? 'Error occurred while creating record' : "Error occurred while updating record (id={$bean->id})";
			return false;
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
				<string name="$sql" />
				<array name="$param" optional="yes" />
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function runSQL($sql, $param=null) {
		if ( self::connectDB() === false ) return false;

	}




	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getCell() {
		if ( self::connectDB() === false ) return false;

	}




	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getRow() {
		if ( self::connectDB() === false ) return false;

	}


} // class