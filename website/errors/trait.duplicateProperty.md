---
title: "trait.duplicateProperty"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
	public string $name;
	public int $name;
}
```

## Why is it reported?

A property is declared more than once in the same trait. PHP does not allow redeclaring properties within a single trait definition. This also applies when a property is declared both as a regular property and as a constructor-promoted property.

## How to fix it

Remove the duplicate property declaration:

```diff-php
 trait MyTrait
 {
 	public string $name;
-	public int $name;
 }
```
