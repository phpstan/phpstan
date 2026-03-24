---
title: "global.this"
shortDescription: "Using $this in a global statement."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function foo(): void {
	global $this;
}
```

## Why is it reported?

PHP does not allow `$this` to be used as a global variable. The `$this` pseudo-variable is reserved for referring to the current object instance inside class methods, and it cannot be imported from the global scope with the `global` statement. This is a compile-time error in PHP.

## How to fix it

Use a different variable name:

```diff-php
 function foo(): void {
-	global $this;
+	global $instance;
 }
```
