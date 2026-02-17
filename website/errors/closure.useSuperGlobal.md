---
title: "closure.useSuperGlobal"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$fn = function () use ($_GET) {
    return $_GET['key'];
};
```

## Why is it reported?

PHP does not allow using superglobal variables (`$_GET`, `$_POST`, `$_SERVER`, `$_COOKIE`, `$_FILES`, `$_SESSION`, `$_REQUEST`, `$_ENV`, `$GLOBALS`) as lexical variables in a closure `use` clause. Superglobals are already available in every scope, so importing them is unnecessary and produces a fatal error.

## How to fix it

Remove the superglobal from the `use` clause and access it directly inside the closure:

```diff-php
 <?php declare(strict_types = 1);

-$fn = function () use ($_GET) {
+$fn = function () {
     return $_GET['key'];
 };
```
