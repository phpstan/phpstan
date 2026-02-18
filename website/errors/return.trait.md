---
title: "return.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
}

function createTrait(): MyTrait
{
	// ...
}
```

## Why is it reported?

A trait cannot be used as a type in PHP. Traits are not types -- they are a mechanism for code reuse. Using a trait name as a return type declaration is not valid and causes a fatal error at runtime.

## How to fix it

Use an interface or class instead of the trait:

```diff-php
+interface HasTrait
+{
+}

-function createTrait(): MyTrait
+function createTrait(): HasTrait
 {
 	// ...
 }
```

Or use a class that uses the trait:

```diff-php
 class MyClass
 {
 	use MyTrait;
 }

-function createTrait(): MyTrait
+function createTrait(): MyClass
 {
 	// ...
 }
```
