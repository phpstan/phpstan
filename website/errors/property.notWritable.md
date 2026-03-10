---
title: "property.notWritable"
shortDescription: "Overriding property removes writability required by the parent."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasValue
{
	public int $value { get; set; }
}

class Foo implements HasValue
{
	public int $value {
		get => 42;
	}
}
```

## Why is it reported?

A child class overrides a writable property from a parent class or interface but makes it non-writable. In the example above, the interface `HasValue` declares `$value` as both readable and writable, but `Foo` only defines a `get` hook. Since the `get` hook does not reference the backing store, the property becomes virtual and has no `set` capability. This violates the writability contract established by the interface.

This rule applies to PHP 8.4+ property hooks.

## How to fix it

Ensure the overriding property also supports writes:

```diff-php
 class Foo implements HasValue
 {
 	public int $value {
 		get => 42;
+		set => $value;
 	}
 }
```
