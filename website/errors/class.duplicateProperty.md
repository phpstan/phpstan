---
title: "class.duplicateProperty"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public string $name;
	public int $name;
}
```

## Why is it reported?

A property is declared more than once in the same class. PHP does not allow redeclaring properties within a single class definition. This also applies when a property is declared both as a regular property and as a constructor-promoted property.

In the example above, `$name` is declared twice in the `Foo` class.

## How to fix it

Remove the duplicate property declaration:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public string $name;
-	public int $name;
 }
```

If using constructor promotion, remove the non-promoted property declaration:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public string $name;
-
 	public function __construct(
 		public string $name,
 	) {
 	}
 }
```
