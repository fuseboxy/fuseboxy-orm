<?php
require_once 'ORM__Interface.php';
class ORM implements ORM__Interface {


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }


	// get vendor of ORM
	private static $vendor = 'redbean';
	public static function vendor() { return self::$vendor; }


	/**
	<fusedoc>
		<description>
			setup ORM of corresponding vendor
		</description>
		<io>
			<in />
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function init($vendor=null) {
		if ( !empty($vendor) ) self::$vendor = $vendor;
		return call_user_func(__CLASS__.'__'.self::$vendor.'::'.__FUNCTION__);
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
	public static function all($beanType, $sql='ORDER BY id') {
		return call_user_func(__CLASS__.'__'.self::$vendor.'::'.__FUNCTION__, $beanType, $sql);
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
		return call_user_func(__CLASS__.'__'.self::$vendor.'::'.__FUNCTION__, $beanType);
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
	public static function count($beanType, $sql='', $param=[]) {
		return call_user_func(__CLASS__.'__'.self::$vendor.'::'.__FUNCTION__, $beanType, $sql, $param);
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
		return call_user_func(__CLASS__.'__'.self::$vendor.'::'.__FUNCTION__, $bean);
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
	public static function first($beanType, $sql='', $param=[]) {
		return call_user_func(__CLASS__.'__'.self::$vendor.'::'.__FUNCTION__, $beanType, $sql, $param);
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
	public static function get($beanType, $sqlOrID='', $param=[]) {
		return call_user_func(__CLASS__.'__'.self::$vendor.'::'.__FUNCTION__, $beanType, $sqlOrID, $param);
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
	public static function new($beanType, $data=[]) {
		return call_user_func(__CLASS__.'__'.self::$vendor.'::'.__FUNCTION__, $beanType, $data);
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
				<string name="$return" optional="yes" default="all" comments="all|row|col|column|cell" />
			</in>
			<out>
				<array name="~return~" optional="yes" oncondition="SELECT & [return=all]">
					<structure name="+">
						<mixed name="~column~" />
					</structure>
				</array>
				<structure name="~return~" optional="yes" oncondition="SELECT & [return=row]" comments="return value of first row">
					<mixed name="~column~" />
				</structure>
				<array name="~return~" optional="yes" oncondition="SELECT & [return=col|column]" comments="return value of first column">
					<mixed name="+" />
				</array>
				<mixed name="~return~" optional="yes" oncondition="SELECT & [return=cell]" comments="return value of first cell" />
				<number name="~return~" optional="yes" oncondition="INSERT|UPDATE|DELETE" comments="number of affected records; zero affected row does not mean error" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function query($sql, $param=[], $return='all') {
		return call_user_func(__CLASS__.'__'.self::$vendor.'::'.__FUNCTION__, $sql, $param);
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
		return call_user_func(__CLASS__.'__'.self::$vendor.'::'.__FUNCTION__, $bean);
	}


	/**
	<fusedoc>
		<description>
			get name of all tables
		</description>
		<io>
			<in />
			<out>
				<array name="~return~">
					<string name="+" />
				</array>
			</out>
		</io>
	</fusedoc>
	*/
	public static function tables() {
		return call_user_func(__CLASS__.'__'.self::$vendor.'::'.__FUNCTION__);
	}


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
	public static function slots($param) {
		return implode(',', array_fill(0, count($param), '?'));
	}


	// alias methods
	public static function one() { return call_user_func(__CLASS__.'::first', func_get_args()); }
	public static function run() { return call_user_func(__CLASS__.'::query', func_get_args()); }


} // class


// define alias
class_alias('ORM', 'O');