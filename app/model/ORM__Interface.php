<?php
interface ORM__Interface {

	public static function error();
	public static function init();
	public static function all($beanType, $sql);
	public static function columns($beanType);
	public static function count($beanType, $sql, $param);
	public static function delete($bean);
	public static function first($beanType, $sql, $param);
	public static function get($beanType, $sqlOrID, $param);
	public static function new($beanType, $data);
	public static function query($sql, $param);
	public static function save($bean);

}