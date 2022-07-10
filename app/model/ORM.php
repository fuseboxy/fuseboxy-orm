<?php
require_once 'Bean.php';
require_once 'iORM.php';
require_once 'ORM__Generic.php';
require_once 'ORM__RedBean.php';
class ORM implements iORM {


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }


	// get vendor of ORM
	private static $vendor = 'redbean';
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
					<string name="vendor" optional="yes" />
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
		if ( !isset($options['class_alias']) ) $options['class_alias'] = 'O';
		// update corresponding property (when necessary)
		if ( !empty($options['vendor']) ) self::$vendor = $options['vendor'];
		// define class alias (when necessary)
		if ( !empty($options['class_alias']) and !class_exists($options['class_alias']) ) class_alias(__CLASS__, $options['class_alias']);
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
				<string name="$order" />
			</in>
			<out>
				<structure name="~return~">
					<object name="~id~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function all($beanType, $order='ORDER BY id') { return self::invoke(__FUNCTION__, [$beanType, $order]); }




	/**
	<fusedoc>
		<description>
			get columns of specific table
		</description>
		<io>
			<in>
				<string_or_object name="$beanType" />
			</in>
			<out>
				<structure name="~return~">
					<string name="~columnName~" value="~columnType~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function columns($beanType) {
		// when bean as param
		if ( is_object($beanType) ) {
			$beanType = Bean::type($beanType);
			if ( $beanType === false ) {
				self::$error = Bean::error();
				return false;
			}
		}
		// done!
		return self::invoke(__FUNCTION__, [$beanType]);
	}




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
				<string name="$filter" />
				<string name="$param" />
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function count($beanType, $filter='', $param=[]) { return self::invoke(__FUNCTION__, [$beanType, $filter, $param]); }




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
	public static function delete($bean) { return self::invoke(__FUNCTION__, [$bean]); }




	/**
	<fusedoc>
		<description>
			obtain first record according to the criteria
		</description>
		<io>
			<in>
				<string name="$beanType" />
				<string name="$filter" />
				<array name="$param" />
			</in>
			<out>
				<object name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function first($beanType, $filter='', $param=[]) { return self::invoke(__FUNCTION__, [$beanType, $filter, $param]); }
	public static function one($beanType, $filter='', $param=[]) { return self::first($beanType, $filter, $param); }




	/**
	<fusedoc>
		<description>
			obtain specific record according to ID, or...
			obtain multiple records according to criteria
		</description>
		<io>
			<in>
				<string name="$beanType" />
				<string_or_number name="$filterOrID" />
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
	public static function get($beanType, $filterOrID='', $param=[]) { return self::invoke(__FUNCTION__, [$beanType, $filterOrID, $param]); }




	/**
	<fusedoc>
		<description>
			create empty new item (preload data when specified)
		</description>
		<io>
			<in>
				<string name="$beanType" />
				<structure name="$data" optional="yes">
					<mixed name="~columnName~" />
				</structure>
			</in>
			<out>
				<object name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function new($beanType, $data=[]) { return self::invoke(__FUNCTION__, [$beanType, $data]); }




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
	public static function query($sql, $param=[], $return='all') { return self::invoke(__FUNCTION__, [$sql, $param, $return]); }
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
	public static function save($bean) {
		return self::invoke(__FUNCTION__, [$bean]);
	}




	/**
	<fusedoc>
		<description>
			create empty new item & save
		</description>
		<io>
			<in>
				<string name="$beanType" />
				<structure name="$data" optional="yes">
					<mixed name="~columnName~" />
				</structure>
			</in>
			<out>
				<object name="~return~" comments="last insert record" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function saveNew($beanType, $data=[]) {
		$item = self::new($beanType, $data);
		if ( $item === false ) return false;
		$id = self::save($item);
		if ( $id === false ) return false;
		return self::get($beanType, $id);
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
	public static function tables() { return self::invoke(__FUNCTION__); }




	/**
	<fusedoc>
		<description>
			generate slots for query paramters
		</description>
		<io>
			<in>
				<array_or_number name="$param" />
			</in>
			<out>
				<list name="~return~" delim=",">
					<string value="?" />
				</list>
			</out>
		</io>
	</fusedoc>
	*/
	public static function slots($param) { return implode(',', array_fill(0, is_numeric($param) ? $param : count($param), '?')); }


} // class