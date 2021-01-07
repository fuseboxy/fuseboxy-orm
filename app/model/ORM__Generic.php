<?php
require_once 'ORM__Interface.php';
class ORM__Generic implements ORM__Interface {


	// properties
	private static $conn = null;


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	/**
	<fusedoc>
		<description>
			setup and prepare connection to database
		</description>
		<io>
			<in>
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
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function init() {
	}




	// get all records
	public static function all($beanType, $sql) {
	}




	// get columns of specific table
	public static function columns($beanType) {
	}




	// count number of records accorrding to criteria
	public static function count($beanType, $sql, $param) {
	}




	// delete specific record
	public static function delete($bean) {
	}




	// obtain first record according to the criteria
	public static function first($beanType, $sql, $param) {
	}




	// obtain specific record according to ID, or...
	// obtain multiple records according to criteria
	public static function get($beanType, $sqlOrID, $param) {
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