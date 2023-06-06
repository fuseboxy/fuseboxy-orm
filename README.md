FUSEBOXY ORM
============

Keep your SQL to a minimum


----------------------------------------------------------------------------------------------------


## Installation

#### Install By Composer

* Specify followings in `composer.json` and run `composer u`
```
{
    "require": {
        "fuseboxy/fuseboxy-orm" : "*"
    },
    "repositories": [
        { "type": "git", "url": "https://www.bitbucket.org/henrygotmojo/fuseboxy-installer" },
        { "type": "git", "url": "https://www.bitbucket.org/henrygotmojo/fuseboxy-orm" }
    ],
    "minimum-stability": "dev",
    "prefer-stable": false,
    "config": {
        "platform": { "php": "7.4" },
        "allow-plugins": { "fuseboxy/fuseboxy-installer": true }
    },
}
```

* Include `vendor/autoload.php` which generated by Composer
```
<?php
require_once 'vendor/autoload.php';
define('FUSEBOXY_ORM_DB', [ ... ]);
...

```


#### Install It Manually

* Extract package to whatever directory (e.g. `fuseboxy-orm`)
```
+ my_application
  + fuseboxy-orm
    + app/*
    + lib/*
```

* Include the component
```
<?php
require_once 'fuseboxy-orm/app/model/ORM.php';
define('FUSEBOXY_ORM_DB', [ ... ]);
...

```

----------------------------------------------------------------------------------------------------


## Configuration

#### For Fuseboxy Framework
Specify framework config `db` at `app/config/fusebox_config.php`

```
<?php
return array(
	...

	'db' => array(
		'host'     => 'localhost',
		'name'     => 'my_database',
		'username' => 'root',
		'password' => 'password',
	),

	...
);
```


#### For Others
Define `FUSEBOXY_ORM_DB` constant

```
<?php
define('FUSEBOXY_ORM_DB', [
	'host'     => 'localhost',
	'name'     => 'my_database',
	'username' => 'root',
	'password' => 'password',
]);
```


----------------------------------------------------------------------------------------------------


## ORM Libraries

#### RedBeanPHP (5.7.x)

* Third-party library (which has limitation of underscore on table name)
* https://redbeanphp.com/


#### Generic

* Own library (which do not have limitation on table name)
* Basic CRUD operations only


----------------------------------------------------------------------------------------------------


## Quick Start

#### SELECT
```
<?php
// load library
require_once '/path/to/ORM.php';
define('FUSEBOXY_ORM_DB', [ ... ]);

// load multiple records
$beans = ORM::get('foo', 'disabled = 0 AND category = ? ORDER BY datetime DESC', array('bar'));
if ( $beans === false ) die(ORM::error());
foreach ( $beans as $id => $item ) var_dump($item);

// load single specific record
$bean = ORM::get('foo', $_GET['id']);
if ( $bean === false ) die(ORM::error());
var_dump($bean);
```

#### INSERT
```
<?php
// load library
require_once '/path/to/ORM.php';
define('FUSEBOXY_ORM_DB', [ ... ]);

// create new object and then save
$bean1 = ORM::new('foo', [ 'category' => 'aaaaa', 'seq' => 10 ]);
$id = ORM::save($bean1);
if ( $id === false ) die(ORM::error());
var_dump($id);

// save new record right away
$bean2 = ORM::saveNew('foo', [ 'category' => 'bbbbb', 'seq' => 999 ]);
if ( $bean2 === false ) die(ORM::error());
var_dump($bean2);
```

#### UPDATE
```
<?php
// load library
require_once '/path/to/ORM.php';
define('FUSEBOXY_ORM_DB', [ ... ]);

// update single specific record
$bean = ORM::get('foo', $_GET['id']);
if ( $bean === false ) die(ORM::error());
$bean->category = 'bar';
$updated = ORM::save($bean);
if ( $updated === false ) die(ORM::error());
echo 'Record updated successfully';

// update multiple records by criteria
$updated = ORM::query('UPDATE foo SET category = ? WHERE category IS NULL ', array('bar'));
if ( $updated === false ) die(ORM::error());
echo 'Records updated successfully';
```

#### DELETE
```
<?php
// load library
require_once '/path/to/ORM.php';
define('FUSEBOXY_ORM_DB', [ ... ]);

// delete single specific record
$bean = ORM::get('foo', $_GET['id']);
if ( $bean === false ) die(ORM::error());
$deleted = ORM::delete($bean);
if ( $deleted === false ) die(ORM::error());
echo 'Record deleted successfully';

// delete multiple records by criteria
$deleted = ORM::query('DELETE foo WHERE disabled = ? ', array(0));
if ( $deleted === false ) die(ORM::error());
echo 'Records deleted successfully';
```


----------------------------------------------------------------------------------------------------


## Methods

#### ORM::all ( $beanType, $order="ORDER BY id" )
Get all records of a table (default sort by id)

##### Parameters
```
<fusedoc>
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
```

##### Example
```
<?php
// load data
$beans = ORM::all('foo', 'ORDER BY datetime DESC');
if ( $beans === false ) die(ORM::error());

// display result
foreach ( $beans as $id => $item ) var_dump($item);
```


#### ORM::columns ( $beanType )
Get columns of specific table


##### Parameters
```
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
```

##### Example
```
<?php
// load schema
$columns = ORM::columns('foo');
if ( $columns === false ) die(ORM::error());

// display result
var_dump($columns);
```


#### ORM::count ( $beanType, $filter="", $param=[] )
Count number of records according to criteria specified (if any)

##### Parameters
```
<fusedoc>
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
```

##### Example
```
<?php
// get number of records
$count = ORM::count('foo', 'disabled = 0 AND category = ?', array('bar'));
if ( $count === false ) die(ORM::error());

// display result
var_dump($count);
```


#### ORM::delete ( $bean )
Delete single specific record

##### Parameters
```
<fusedoc>
	<io>
		<in>
			<object name="$bean" />
		</in>
		<out>
			<boolean name="~return~" />
		</out>
	</io>
</fusedoc>
```

##### Example
```
<?php
// load specific record
$bean = ORM::get('foo', $_GET['id']);
if ( $bean === false ) die(ORM::error());

// delete record
$deleted = ORM::delete($bean);
if ( $deleted === false ) die(ORM::error());
var_dump($deleted);
```


#### ORM::first ( $beanType, $filter="", $param=[] )
Obtain first record according to the criteria specified (if any)

##### Parameters
```
<fusedoc>
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
```

##### Example
```
<?php
// load data
$firstBean = ORM::get('foo', 'disabled = 0 AND category = ? ORDER BY created_on DESC', array('bar'));
if ( $firstBean === false ) die(ORM::error());

// display result
var_dump($firstBean);
```


#### ORM::get ( $beanType, $filterOrID="", $param=[] )
Obtain single specific record by ID; or
Obtain multiple records according to criteria specified (if any)


##### Parameters
```
<fusedoc>
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
```

##### Example
```
<?php
// load single record
$bean = ORM::get('foo', $_GET['id']);
if ( $bean === false ) die(ORM::error());
var_dump($bean);

// load multiple records
$data = ORM::get('foo', 'disabled = 0 ORDER BY created_on DESC');
if ( $data === false ) die(ORM::error());
foreach ( $data as $id => $bean ) var_dump($bean);
```


#### ORM::init ( $vendor )
Detemrine the ORM library to use (at the very start)
Default using RedBeanPHP

##### Parameters
```
<fusedoc>
	<io>
		<in>
			<string name="$vendor" />
		</in>
		<out>
			<boolean name="~return~" />
		</out>
	</io>
</fusedoc>
```

##### Example
```
<?php
// using generic ORM library
$ready = ORM::init('generic');
if ( $ready === false ) die(ORM::error());

// load single record
$bean = ORM::first('foo');
if ( $bean === false ) die(ORM::error());

// see the difference of data structure
var_dump($bean);
```


#### ORM::new ( $beanType, $data=[] )
Create new object for specific table (but not save to database yet)

##### Parameters
```
<fusedoc>
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
```

##### Example
```
<?php
// create container with data pre-loaded
$bean = ORM::new('student', [
	'name' => 'FOO, BAR',
	'gender' => 'M',
	'hkid' => 'A1234567',
	'dob' => '1999-12-31',
]);
if ( $bean === false ) die(ORM::error());

// display result (ID should be empty)
var_dump($bean);
var_dump($bean->id);
```


#### ORM::one ( $beanType, $filter="", $param=[] )
Alias of `ORM::first` method


#### ORM::query ( $sql, $param=[], $return="all" )
Run SQL statement

##### Parameters
```
<fusedoc>
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
```

##### Example
```
<?php
// prepare complicated statement
$sql = '
	SELECT DISTINCT c.name
	FROM product p
	INNER JOIN category c ON (p.category_id = c.id)
	WHERE p.price > ?
';
$param = array(9999);

// run statement (and obtain single column of data only)
$categories = ORM::query($sql, $param, 'column');
if ( $categories === false ) die(ORM::error());

// display result
var_dump($categories);
```


#### ORM::run ( $sql, $param=[], $return="all" )
Alias of `ORM::query` method


#### ORM::save ( $bean )
Save object into database

##### Parameters
```
<fusedoc>
	<io>
		<in>
			<object name="$bean" />
		</in>
		<out>
			<number name="~return~" comments="id of record inserted/updated" />
		</out>
	</io>
</fusedoc>
```

##### Example
```
<?php
// update existing record
$bean = ORM::first('foo');
if ( $bean === false ) die(ORM::error());
$bean->category = 'XXXXX';
$id = ORM::save($bean);
if ( $id === false ) die(ORM::error());

// create new record
$newBean = ORM::new('foo');
if ( $newBean === false ) die(ORM::error());
$newBean->category = 'BAR';
$newID = ORM::save($newBean);
if ( $newID === false ) die(ORM::error());
```


#### ORM::saveNew ( $beanType, $data )
Create new object and save to database in one-go

##### Parameters
```
<?php
// insert new record to database
$bean = ORM::saveNew('foo', [
	'category' => 'BAR',
	'disabled' = 0,
]);
if ( $bean === false ) die(ORM::error());

// display result
var_dump($bean);
```

##### Example
```

```


#### ORM::tables ( )
Obtain name of all tables in the database

##### Parameters
```
<fusedoc>
	<io>
		<in />
		<out>
			<array name="~return~">
				<string name="+" />
			</array>
		</out>
	</io>
</fusedoc>
```

##### Example
```
<?php
// load data
$tables = ORM::tables();
if ( $tables === false ) die(ORM::error());

// display result
var_dump($tables);
```


#### Bean::diff ( $bean1, $bean2 )
Compare two objects and return string which showing the difference

##### Parameters
```
<fusedoc>
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
```

##### Example
```
<?php
// load record
$bean = ORM::get('foo', $_GET['id']);
if ( $bean === false ) die(ORM::error());

// clone object (to avoid pass-by-reference)
$beanBeforeSave = unserialize(serialize($bean));

// update record
$bean->name = 'FOO BAR';
$bean->category = 'BAR';
$updated = ORM::save($bean);
if ( $updated === false ) die(ORM::error());

// display diff
$diff = Bean::diff($beanBeforeSave, $bean);
if ( $diff === false ) die(Bean::error());
var_dump($diff);
```


#### Bean::export ( $bean )
Export bean from object to associative-array

##### Parameters
```
<fusedoc>
	<io>
		<in>
			<object name="$bean" />
		</in>
		<out>
			<structure name="~return~" />
		</out>
	</io>
</fusedoc>
```

##### Example
```
<?php
// load record
$bean = ORM::first('foo');
if ( $bean === false ) die(ORM::error());

// display record as array
var_dump($bean->export());

// display record as object
var_dump($bean);
```


#### Bean::getColumns ( $bean )
Obtain columns of specific bean

##### Parameters
```
<fusedoc>
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
```

##### Example
```
<?php
// load specific record
$bean = ORM::get('foo', $_GET['id']);
if ( $bean === false ) die(ORM::error());

// obtain columns of this bean
$beanColumns = Bean::getColumns($bean);
if ( $beanColumns === false ) die(Bean::error());

// display result
var_dump($beanColumns);
```


#### Bean::groupBy ( $groupColumn, $beans )
Convert array-of-beans into multi-dimensional-array which groups beans by column specified

##### Parameters
```
<fusedoc>
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
```

##### Example
```
<?php
// load data
$data = ORM::all('foo');
if ( $data === false ) die(ORM::error());
var_dump($data);

// group records by category
$dataGroupByCategory = Bean::groupBy('category', $data);
if ( $dataGroupByCategory === false ) die(Bean::error());
var_dump($dataGroupByCategory);
```


#### Bean::toString ( $bean )
Convert data of bean to human-readable string

##### Parameters
```
<fusedoc>
	<io>
		<in>
			<object name="$bean" />
		</in>
		<out>
			<string name="~return~" />
		</out>
	</io>
</fusedoc>
```

##### Example
```
<?php
// load record
$bean = ORM::first('foo');
if ( $bean === false ) die(ORM::error());

// convert bean to string (for log writing)
$beanString = Bean::toString($bean);
if ( $beanString === false ) die(Bean::error());

// write log
$logResult = Log::write([ 'action' => 'FOOBAR', 'remark' => $beanString ]);
if ( $logResult === false ) die(Log::error());
```


#### Bean::type ( $bean )
Determine bean type (which is table name)

##### Parameters
```
<fusedoc>
	<io>
		<in>
			<object name="$bean" />
		</in>
		<out>
			<string name="~return~" />
		</out>
	</io>
</fusedoc>
```

##### Example
```
<?php
// load record
$bean = ORM::get('foo', $_GET['id']);
if ( $bean === false ) die(ORM::error());

// get table name
$beanType = Bean::type($bean);
if ( $beanType === false ) die(Bean::error());

// display result
var_dump($beanType);
```