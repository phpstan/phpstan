---
title: "interface.nameCase"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Loggable
{
	public function log(): void;
}

class Foo implements loggable
{
	public function log(): void
	{
	}
}
```

## Why is it reported?

An interface is referenced with a different letter casing than its declaration. While PHP class and interface names are case-insensitive and the code will work at runtime, using incorrect casing is considered poor practice. It makes code harder to read and can cause confusion.

In the example above, the interface is declared as `Loggable` with an uppercase `L`, but referenced as `loggable` with a lowercase `l`.

## How to fix it

Match the casing of the interface reference to its declaration:

```diff-php
 <?php declare(strict_types = 1);

-class Foo implements loggable
+class Foo implements Loggable
 {
 	public function log(): void
 	{
 	}
 }
```
