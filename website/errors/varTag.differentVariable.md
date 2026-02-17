---
title: "varTag.differentVariable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
    /** @var int $foo */
    $test = someFunction();
}
```

## Why is it reported?

The variable name specified in the `@var` PHPDoc tag does not match the variable being assigned on the next line. In this example, the `@var` tag references `$foo`, but the actual assignment is to `$test`. This is almost always a copy-paste mistake or a typo.

PHPStan also reports this when the `@var` tag references a variable that does not match any variable in a `foreach` loop, `static` declaration, or `global` statement:

```php
<?php declare(strict_types = 1);

function doBar(array $list): void
{
    /** @var int $foo */
    foreach ($list as $key => $val) {
        // $foo does not match $list, $key, or $val
    }
}
```

## How to fix it

Correct the variable name in the `@var` tag to match the actual variable:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-    /** @var int $foo */
+    /** @var int $test */
     $test = someFunction();
 }
```

Or use a `@var` tag without a variable name when there is only one variable being assigned:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-    /** @var int $foo */
+    /** @var int */
     $test = someFunction();
 }
```
