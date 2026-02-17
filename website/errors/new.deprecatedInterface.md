---
title: "new.deprecatedInterface"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewCacheInterface instead */
interface OldCacheInterface
{
}

class FileCache implements OldCacheInterface
{
}

$cache = new FileCache();
```

## Why is it reported?

An object is being instantiated from a class that implements a deprecated interface, or the class itself is a deprecated interface-like structure being referenced in a `new` expression context. The `@deprecated` tag on the interface signals that it should no longer be used, and all code depending on it should migrate to the recommended replacement.

## How to fix it

Update the code to use the replacement class or interface as indicated in the deprecation message.

```diff-php
 <?php declare(strict_types = 1);

-class FileCache implements OldCacheInterface
+class FileCache implements NewCacheInterface
 {
 }

 $cache = new FileCache();
```
