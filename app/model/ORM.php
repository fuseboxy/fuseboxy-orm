<?php
require_once 'ORM__Interface.php';
class ORM implements ORM__Interface {


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	// get or set vendor
	private static $vendor = 'redbean';
	public static function vendor($name=null) {
		if ( empty($name) ) return self::$vendor;
		self::$vendor = strtolower($name);
		return true;
	}




	// invoke method of corresponding vendor class
	// ===> with dynamic number of arguments
	public static function invoke() {
		$args = func_get_args();
		// validate class
		$class = __CLASS__.'__'.self::$vendor;
		if ( !class_exists($class) ) {
			self::$error = "Class [{$class}] not exists";
			return false;
		}
		// validate method
		$method = array_shift($args);
		if ( empty($method) ) {
			self::$error = 'Method name is required';
			return false;
		} elseif ( !method_exists($class, $method) ) {
			self::$error = "Method [{$class}::{$method}] not exists";
			return false;
		}
		// call method with arguments
		switch ( count($args) ) {
			case  0: $result = $class::$method(); break;
			case  1: $result = $class::$method($args[0]); break;
			case  2: $result = $class::$method($args[0], $args[1]); break;
			case  3: $result = $class::$method($args[0], $args[1], $args[2]); break;
			case  4: $result = $class::$method($args[0], $args[1], $args[2], $args[3]); break;
			case  5: $result = $class::$method($args[0], $args[1], $args[2], $args[3], $args[4]); break;
			case  6: $result = $class::$method($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]); break;
			case  7: $result = $class::$method($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]); break;
			case  8: $result = $class::$method($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]); break;
			case  9: $result = $class::$method($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]); break;
			default: self::$error = 'Please enhance [ORM::invoke] method to allow more arguments to pass through'; return false;
		}
		// validation
		if ( $result === false ) {
			self::$error = $class::error();
			return false;
		}
		// done!
		return $result;
	}



	/**
	<fusedoc>
		<description>
			get all records (default sort by id)
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
	public static function all($beanType, $sql='ORDER BY id') { return self::invoke(__FUNCTION__, $beanType, $sql); }




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
	public static function columns($beanType) { return self::invoke(__FUNCTION__, $beanType); }




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
	public static function count($beanType, $sql='', $param=[]) { return self::invoke(__FUNCTION__, $beanType, $sql, $param); }




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
	public static function delete($bean) { return self::invoke(__FUNCTION__, $bean); }




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
	public static function first($beanType, $sql='', $param=[]) { return self::invoke(__FUNCTION__, $beanType, $sql, $param); }
	public static function one($beanType, $sql='', $param=[]) { return self::first($beanType, $sql, $param); }




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
	public static function get($beanType, $sqlOrID='', $param=[]) { return self::invoke(__FUNCTION__, $beanType, $sqlOrID, $param); }




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
	public static function new($beanType, $data=[]) { return self::invoke(__FUNCTION__, $beanType, $data); }




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
	public static function query($sql, $param=[]) { return self::invoke(__FUNCTION__, $sql, $param); }
	public static function run($sql, $param=[]) { return self::query($sql, $param); }




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
	public static function save($bean) { return self::invoke(__FUNCTION__, $bean); }




	/**
	<fusedoc>
		<description>
			generate slots for query paramters
		</description>
		<io>
			<in>
				<array name="$param" />
			</in>
			<out>
				<list name="~return~" delim=",">
					<string value="?" />
				</list>
			</out>
		</io>
	</fusedoc>
	*/
	public static function slots($param) { return implode(',', array_fill(0, count($param), '?')); }


} // class




// alias class
class O extends ORM {}