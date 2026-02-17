---
title: "variable.dynamicName"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
    $name = 'foo';
    echo $$name;
}
```

## Why is it reported?

Variable variables (`$$name`) use the value of one variable as the name of another variable. While this is valid PHP syntax, it makes code harder to understand, maintain, and analyse statically. PHPStan cannot reliably track the types of variable variables because their names are determined at runtime. Variable variables also make it easy to introduce bugs through typos in string values.

Another common form of variable variables is assignment:

```php
<?php declare(strict_types = 1);

function doBar(): void
{
    $name = 'foo';
    $$name = 'bar'; // equivalent to $foo = 'bar'
}
```

## How to fix it

Replace the variable variable with an associative array:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-    $name = 'foo';
-    echo $$name;
+    $data = ['foo' => 'some value'];
+    $name = 'foo';
+    echo $data[$name];
 }
```

Or use the variable directly if the name is known:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-    $name = 'foo';
-    echo $$name;
+    $foo = 'some value';
+    echo $foo;
 }
```

This error is reported by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) extension.
