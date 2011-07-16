TreasureChest
==================================

A simple key/value store with namespace support and backends for memcached, apc, filesystem and more.

TreasureChest's biggest feature is namespaces. Most existing key/value stores place your data in a single global environment, this can lead to clashing key names in large datasets. They also don't support wildcards when deleting keys. It's impossible to do something like `store('user1_username', 'bob')` and then `delete('user1_*');`. This makes tracking and invalidating large sets of related keys difficult. TreasureChest provides a wrapper around your favourite key/value store (memcached, apc, xcache etc) making this possible.

Internally, TreasureChest uses a pointer which keeps track of the version number of each namespace. This version number is prefixed to all keys which get passed into the class. When a namespace is `invalidate`ed the pointer is incremented by 1, thereby changing the key which gets passed to the datastore.

Requirements
-----------------------------------
- PHP 5.3 or higher
- APC 3.1.1 or higher (if using APC as backend)
- Memcached 1.2.0 and PECL memcached 0.1.0 or higher (if using Memcached as backend)

Usage
-----------------------------------

Include and register the autoloader, this takes care of loading all other classes.

	include('../lib/TreasureChest/Autoloader.php');
	\TreasureChest\Autoloader::register();
	
Create an instance of the TreasureChest class, passing in an instance of the datastore you wish to use.

	$bounty = new \TreasureChest\Instance(new \TreasureChest\Cache\APC);


Use the `add`, `store`, `fetch`, `replace`, `exists`, `inc`, `dec` and `delete` methods to store, retrieve and manipulate your data.
e.g

	$bounty->store('email', 'bob@example.org');
	$bounty->store('age', 45);
	$bounty->fetch('email'); // returns bob@example.org
	$bounty->inc('age', 5); // returns 50
	$bounty->dec('age', 10); // returns 40
	$bounty->delete('email');
	$bounty->fetch('email'); // returns boolean FALSE
	
Namespaces can be used to logically group sets of key/value pairs. Simply append the key with the desired namespace, separated by a colon (this delimiter character can be changed) 
e.g

	$bounty->add('user123:username', 'bob');
	$bounty->add('user123:email', 'bob@example.org');
	$bounty->add('user123:age', 21);
	
	// Clear the entire user123 namespace
	$cache->invalidate('user123');
	
	$cache->fetch('user123:username'); // returns boolean FALSE
	$cache->fetch('user123:email'); // returns boolean FALSE
	$cache->fetch('user123:age'); // returns boolean FALSE


Known issues
-----------------------------------
There is currently a concurrency issue which can lead to TreasureChest returning data which should have been invalidated. This happens if another PHP process calls `invalidate` whilst the first process is still running.  This can be fixed by checking the namespace version number before each call to `fetch`, `store` etc.  This has a performance impact so I'll be making it a user enabled option.

To do
-----------------------------------
- Introduce option to check namespace version before every APC call.
- Improve testing suite. Introduce automated unit tests using PHPUnit.
- Add support to automatically serialize/unserialize objects/arrays stored in the cache
- Improve README file
