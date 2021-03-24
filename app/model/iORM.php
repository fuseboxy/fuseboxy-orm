<?php
interface iORM {

	public static function error();
	public static function init();
	public static function destroy();
	public static function all($beanType, $order);
	public static function columns($beanType);
	public static function count($beanType, $filter, $param);
	public static function delete($bean);
	public static function first($beanType, $filter, $param);
	public static function get($beanType, $filterOrID, $param);
	public static function new($beanType, $data);
	public static function query($sql, $param, $return);
	public static function save($bean);
	public static function tables();

}