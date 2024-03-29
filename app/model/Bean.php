<?php
// generic bean helper
class Bean {


	// get latest error message
	private static $error;
	public static function error() { return self::$error; }




	/**
	<fusedoc>
		<description>
			compare two objects and return string showing the differences
		</description>
		<io>
			<in>
				<object name="$bean1" />
				<object name="$bean2" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function diff($bean1, $bean2) {
		$result = '';
		// convert format
		if ( !is_array($bean1) ) $bean1 = self::export($bean1);
		if ( !is_array($bean2) ) $bean2 = self::export($bean2);
		// compare each properties of beans
		$bean1_columns = self::getColumns($bean1);
		$bean2_columns = self::getColumns($bean2);
		$columns = array_merge($bean1_columns, $bean2_columns);
		$columns = array_unique($columns);
		foreach ( $columns as $col ) {
			$bean1_col = $bean1[$col] ?? '';
			$bean2_col = $bean2[$col] ?? '';
			if ( $bean1_col != $bean2_col ) {
				$result .= "[{$col}] ";
				$result .= strlen($bean1_col) ? $bean1_col : '(empty)';
				$result .= ' ===> ';
				$result .= strlen($bean2_col) ? $bean2_col : '(empty)';
				$result .= "\n";
			}
		}
		// result
		return trim($result);
	}




	/**
	<fusedoc>
		<description>
			export bean from object to associative-array
		</description>
		<io>
			<in>
				<object name="$bean" />
			</in>
			<out>
				<structure name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function export($bean) {
		// export right away (when redbean)
		if ( method_exists($bean, 'export') ) return $bean->export();
		// get object values & remove meta data
		$result = is_array($bean) ? $bean : get_object_vars($bean);
		if ( isset($result['__type__']) ) unset($result['__type__']);
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			get columns of bean
		</description>
		<io>
			<in>
				<object name="$bean" />
			</in>
			<out>
				<array name="~return~">
					<string name="+" />
				</array>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getColumns($bean) {
		$result = array();
		// simple value properties only
		$beanData = self::export($bean);
		foreach ( $beanData as $key => $val ) if ( !is_array($val) ) $result[] = $key;
		// return result
		return $result;
	}




	/**
	<fusedoc>
		<description>
			transform records into multi-level array
		</description>
		<io>
			<in>
				<string name="$groupColumn" />
				<structure name="$beans">
					<object name="~id~" />
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<structure name="~groupColumnValue~">
						<object name="~id~" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function groupBy($groupColumn, $beans) {
		// empty result container
		$result = array();
		// go through each item and check group
		foreach ( $beans as $bean ) {
			// create empty container for this group
			if ( !isset($result[$bean->{$groupColumn}]) ) {
				$result[$bean->{$groupColumn}] = array();
			}
			// put item into group
			$result[$bean->{$groupColumn}][$bean->id] = $bean;
		}
		// result
		return $result;
	}




	/**
	<fusedoc>
		<description>
			convert bean to string
		</description>
		<io>
			<in>
				<object name="$bean" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function toString($bean) {
		$result = '';
		$beanData = self::export($bean);
		foreach ( $beanData as $key => $val ) {
			$result .= "[{$key}] ";
			$result .= strlen($val) ? $val : '(empty)';
			$result .= "\n";
		}
		return trim($result);
	}




	/**
	<fusedoc>
		<description>
			obtain type of bean
		</description>
		<io>
			<in>
				<object name="$bean">
					<string name="getMeta('type')" optional="yes" oncondition="redbean" />
					<string name="__type__" optional="yes" oncondition="generic" />
				</object>
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function type($bean) {
		return method_exists($bean, 'getMeta') ? $bean->getMeta('type') : ( $bean->__type__ ?? null );
	}


} // class