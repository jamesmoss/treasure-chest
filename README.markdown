NamespaceCache
==================================

A wrapper around the apc_* functions which make it possible to use namespaces with keys when manipulating data in the user cache. NamespaceCache makes it possible to logically group a set of keys and invalidate them all at once making cache management much easier.  This also lets you get around the problem of not being able to use wildcards when wanting to delete a whole series of related keys.

It's impossible to do `apc_store('user1_username', 'bob')` and then `apc_delete('user1_*');`. Using this class you can simply do `NamespaceCache::store('user1', 'username', 'bob')` and then `NamespaceCache::invalidate('user1')`.

Internally, NamespaceCache uses a pointer (also stored in the user cache) that keeps track of the version number of each namespace. This version number is prefixed to all keys which get passed into the class. When a namespace is `invalidate`'ed the pointer is incremented by one, thereby changing the key which gets passed to APC.

Requirements
-----------------------------------
- PHP 5.3 or higher
- APC 3.1.4 or higher

Usage
-----------------------------------
All the NamespaceCache methods are static and have (almost) identical function signatures to their apc_ equivilents. The only change is an additional first parameter, a string, which is the namespace to be used for that key.

e.g

	apc_fetch('key123');

becomes

	NamespaceCache::fetch('namespace123', 'key123');

To clear all the keys associated to a namespace use the `NamespaceCache::invalidate` method.


Known issues
-----------------------------------
There is currently a concurrency issue which can lead to a NamespaceCache returning data which should have been invalidated if another PHP process calls `invalidate` whilst the first process is still running.  This can be fixed by checking the namespace version number before each call to `fetch`, `store` etc.  This has a performance impact so I'll be making it a user enabled option.

To do
-----------------------------------
- Introduce option to check namespace version before every APC call.
- Improve testing suite. Introduce automated unit tests using PHPUnit.
- Improve README file
