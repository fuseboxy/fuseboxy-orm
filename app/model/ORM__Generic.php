<?php
class ORM__Generic {


	// properties
	private static $isReady = false;
	private static $conn = null;


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }


} // class