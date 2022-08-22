Fuseboxy ORM
============

Keep your SQL to a minimum


----------------------------------------------------------------------------------------------------


## Installation

#### By Composer


#### Manually


----------------------------------------------------------------------------------------------------


## ORM Libraries

#### RedBeanPHP (v5.7.x)

* Third-party library (which has limitation of underscore on table name)
* https://redbeanphp.com/


#### Generic

* Own library (which do not have limitation on table name)
* Basic CRUD operations only


----------------------------------------------------------------------------------------------------


## Examples

#### Load and multiple records
````
<?php
$data = ORM::get('foo', 'disabled = 0 AND category = ? ORDER BY datetime DESC', array('bar'));
F::error(ORM::error(), $data === false);
foreach ( $data as $id => $item ) var_dump($item);
````

#### Load specific record
````
<?php
$bean = ORM::get('foo', $_GET['id']);
F::error(ORM::error(), $bean === false);
var_dump($bean);
````

#### Create new record
````
<?php
// create new object and then save
$bean_1 = ORM::new('foo', [ 'category' => 'aaaaa', 'seq' => 10 ]);
$id = ORM::save($bean_1);
F::error(ORM::error(), $id === false);
var_dump($id);

// save new record right away
$bean_2 = ORM::saveNew('foo', [ 'category' => 'bbbbb', 'seq' => 999 ]);
F::error(ORM::error(), $bean_2 === false);
var_dump($bean_2);
````

#### Update specific record
````
<?php
$bean = ORM::get('foo', $_GET['id']);
F::error(ORM::error(), $bean === false);

$bean->category = 'bar';
$saved = ORM::save($bean);
F::error(ORM::error(), $saved === false);

echo 'Record updated successfully';
````

#### Update multiple records
````
<?php
$updated = ORM::query('UPDATE foo SET category = ? WHERE category IS NULL ', array('bar'));
F::error(ORM::error(), $updated === false);
echo 'Records updated successfully';
````

#### Delete specific record
````
<?php
$bean = ORM::get('foo', $_GET['id']);
F::error(ORM::error(), $bean === false);

$deleted = ORM::delete($bean);
F::error(ORM::error(), $deleted === false);

echo 'Record deleted successfully';
````


----------------------------------------------------------------------------------------------------


## Methods

#### ORM::all ( $beanType, $order="ORDER BY id" )
````
<fusedoc>
	<description>
		get all records (default sort by id)
	</description>
	<io>
		<in>
			<string name="$beanType" />
			<string name="$order" default="ORDER BY id" />
		</in>
		<out>
			<structure name="~return~">
				<object name="~id~" />
			</structure>
		</out>
	</io>
</fusedoc>
````

#### ORM::columns ( $beanType )
````
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
````

#### ORM::count ( $beanType, $filter="", $param=[] )
````
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
````

#### ORM::delete ( $bean )
````
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
````

#### ORM::first ( $beanType, $filter="", $param=[] )
````
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
````

#### ORM::one ( $beanType, $filter="", $param=[] )

* (alias of `ORM::first` method)


#### ORM::get ( $beanType, $filterOrID="", $param=[] )
````
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
````

#### ORM::new ( $beanType, $data=[] )
````
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
````

#### ORM::query ( $sql, $param=[], $return="all" )
````
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
````

#### ORM::run ( $sql, $param=[], $return="all" )

*  Alias of `ORM::query` method


#### ORM::save ( $bean )
````
<fusedoc>
	<description>
		save object into database
	</description>
	<io>
		<in>
			<!-- property -->
			<boolean name="$saveEmptyStringAsNull" scope="self" />
			<!-- parameter -->
			<object name="$bean" />
		</in>
		<out>
			<number name="~return~" comments="id of record inserted/updated" />
		</out>
	</io>
</fusedoc>
````

#### ORM::tables ( )
````
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
````

#### Bean::diff ( $bean1, $bean2 )
````
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
````

#### Bean::export ( $bean )
````
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
````

#### Bean::getColumns ( $bean )
````
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
````

#### Bean::groupBy ( $groupColumn, $beans )
````
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
````

#### Bean::toString ( $bean )
````
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
````

#### Bean::type ( $bean )
````
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
````