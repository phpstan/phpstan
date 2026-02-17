---
title: "interface.duplicateProperty"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface Foo
{
	public string $name { get; }
	public string $name { get; }
}
```

## Why is it reported?

The interface declares the same property name more than once. Since PHP 8.4, interfaces can have property declarations with hooks. PHP does not allow redeclaring a property within the same interface body. This will cause a fatal error at runtime.

## How to fix it

Remove the duplicate property declaration or rename it:

```diff-php
 <?php declare(strict_types = 1);

 interface Foo
 {
 	public string $name { get; }
-	public string $name { get; }
+	public string $label { get; }
 }
```
