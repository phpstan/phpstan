---
title: "parameter.defaultValue"
shortDescription: "Default value of a parameter is incompatible with its declared type."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function greet(int $count = "hello"): void
{
	echo $count;
}
```

## Why is it reported?

The default value of a parameter is incompatible with the parameter's declared type. In the example above, the parameter `$count` is declared as `int`, but its default value is `"hello"`, which is a `string`.

## How to fix it

Change the default value to match the declared type:

```diff-php
-function greet(int $count = "hello"): void
+function greet(int $count = 0): void
 {
 	echo $count;
 }
```

Or change the parameter type to match the default value:

```diff-php
-function greet(int $count = "hello"): void
+function greet(string $count = "hello"): void
 {
 	echo $count;
 }
```
