---
title: "interface.duplicateMethod"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface Foo
{
	public function doSomething(): void;

	public function doSomething(): int;
}
```

## Why is it reported?

The interface declares the same method name more than once. PHP does not allow redeclaring methods within a single interface (or class/enum) definition, and this will cause a fatal error. Method names are compared case-insensitively, so `doSomething()` and `DoSomething()` are considered the same method.

## How to fix it

Remove the duplicate method declaration:

```diff-php
 interface Foo
 {
 	public function doSomething(): void;
-
-	public function doSomething(): int;
 }
```

If two different methods are intended, give them distinct names:

```diff-php
 interface Foo
 {
 	public function doSomething(): void;

-	public function doSomething(): int;
+	public function doSomethingElse(): int;
 }
```
