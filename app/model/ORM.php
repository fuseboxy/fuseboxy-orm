<?php
require_once 'ORM__Interface.php';
class ORM implements ORM__Interface {


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }


	// get vendor of ORM
	private static $vendor;
	public static function vendor() { return self::$vendor; }




	/**
	<fusedoc>
	    <description>
	        invoke method of corresponding vendor class (with dynamic number of arguments)
	    </description>
	    <io>
	        <in>
	        	<string name="$method" />
	        	<array name="$args" optional="yes">
	        		<mixed name="+" />
	        	</array>
	        </in>
	        <out>
	            <boolean name="~return~" />
	        </out>
	    </io>
	</fusedoc>
	*/
	public static function invoke($method, $args=[]) {
		$class = __CLASS__.'__'.self::vendor();
		// validation
		if ( !class_exists($class) ) {
			self::$error = "Class [{$class}] not exists";
			return false;
		} elseif ( !method_exists($class, $method) ) {
			self::$error = "Method [{$class}::{$method}] not exists";
			return false;
		}
		// call method with arguments
		$result = $class::$method(...$args);
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
			setup ORM of corresponding vendor
		</description>
		<io>
			<in>
				<string name="$options" optional="yes" comments="vendor" />
				<structure name="$options" optional="yes">
					<string name="vendor" optional="yes" default="redbean" />
					<string name="class_alias" optional="yes" default="O" />
				</structure>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function init($options=[]) {
		// fix param
		if ( is_string($options) ) $options = array('vendor' => $options);
		// default options
		if ( empty($options['vendor']) ) $options['vendor'] = 'redbean';
		if ( !isset($options['class_alias']) ) $options['class_alias'] = 'O';
		// update corresponding property
		self::$vendor = $options['vendor'];
		// define class alias (when necessary)
		if ( !empty($options['class_alias']) ) class_alias(__CLASS__, $options['class_alias']);
		// validation
		$className = __CLASS__.'__'.self::$vendor;
		if ( !class_exists($className) ) {
			self::$error = "Class [{$className}] is missing";
			return false;
		}
		// done!
		return self::invoke(__FUNCTION__);
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
	public static function all($beanType, $sql='ORDER BY id') { return self::invoke(__FUNCTION__, func_get_args()); }




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
	public static function columns($beanType) { return self::invoke(__FUNCTION__, func_get_args()); }




	/**
	<fusedoc>
		<description>
			close connection
		</description>
		<io>
			<in />
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function destroy() { return self::invoke(__FUNCTION__); }



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
	public static function count($beanType, $sql='', $param=[]) { return self::invoke(__FUNCTION__, func_get_args()); }




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
	public static function delete($bean) { return self::invoke(__FUNCTION__, func_get_args()); }




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
	public static function first($beanType, $sql='', $param=[]) { return self::invoke(__FUNCTION__, func_get_args()); }
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
	public static function get($beanType, $sqlOrID='', $param=[]) { return self::invoke(__FUNCTION__, func_get_args()); }




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
	public static function new($beanType, $data=[]) { return self::invoke(__FUNCTION__, func_get_args()); }




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
	public static function query($sql, $param=[], $return='all') { return self::invoke(__FUNCTION__, func_get_args()); }
	public static function run($sql, $param=[], $return='all') { return self::query($sql, $param, $return); }




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
	public static function save($bean) { return self::invoke(__FUNCTION__, func_get_args()); }




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
	public static function tables() { return self::invoke(__FUNCTION__); }




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