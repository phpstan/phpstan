---
title: "parameter.this"
shortDescription: "Using $this as a function or method parameter name."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo($this)
{
}
```

## Why is it reported?

`$this` is a reserved variable in PHP that refers to the current object instance inside a class. Using it as a parameter name is not allowed — PHP will produce a fatal error if `$this` is used as a parameter in a method, and while it may not always error in a standalone function, it creates misleading code that suggests an object context where there is none.

## How to fix it

Rename the parameter:

```diff-php
-function doFoo($this)
+function doFoo($value)
 {
 }
```
