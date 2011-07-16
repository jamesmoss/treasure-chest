<?php

// This file will be updated to use PHPUnit in the near future.


include('../lib/TreasureChest/Autoloader.php');
\TreasureChest\Autoloader::register();


$cache = new \TreasureChest\Instance(new TreasureChest\Cache\Filesystem('/tmp'));

// Add a few dummy items to the cache
$cache->store('user123:username', 'bob');
$cache->store('user123:email', 'bob@example.org');
$cache->store('user123:age', 21);


// prints bob, bob@example.org, 21
var_dump($cache->fetch('user123:username'));
var_dump($cache->fetch('user123:email'));
var_dump($cache->fetch('user123:age'));

// Clear the namespace
$cache->invalidate('user123');

// prints FALSE, FALSE, FALSE
var_dump($cache->fetch('user123:username'));
var_dump($cache->fetch('user123:email'));
var_dump($cache->fetch('user123:age'));

