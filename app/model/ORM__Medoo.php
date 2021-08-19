<?php
require_once 'iORM.php';
class ORM__Medoo implements iORM {


	// properties
	private static $conn;


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }


}