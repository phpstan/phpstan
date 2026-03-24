---
title: "static.this"
shortDescription: "Using $this as a static variable."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function foo(): void {
	static $this;
}
```

## Why is it reported?

PHP does not allow `$this` to be declared as a static variable. The `$this` pseudo-variable is reserved for referring to the current object instance inside class methods, and it cannot be used as a static local variable. This is a compile-time error in PHP.

## How to fix it

Use a different variable name:

```diff-php
 function foo(): void {
-	static $this;
+	static $instance;
 }
```
