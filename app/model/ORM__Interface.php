<?php
interface ORM__Interface {

	public static function error();
	public static function init();
	public static function all($beanType, $order);
	public static function columns($beanType);
	public static function count($beanType, $filter, $param);
	public static function delete($bean);
	public static function first($beanType, $filter, $param);
	public static function get($beanType, $filterOrID, $param);
	public static function new($beanType, $data);
	public static function query($sql, $param);
	public static function save($bean);

}