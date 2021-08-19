<?php
require_once 'iORM.php';
require_once dirname(dirname(__DIR__)).'/lib/medoo/2.1.2/Medoo.php';
class ORM__Medoo implements iORM {


	// properties
	private static $conn;


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }


}