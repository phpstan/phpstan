---
title: "assign.propertyReadOnly"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function __construct(
		public readonly string $name,
	)
	{
	}
}

$foo = new Foo('bar');
$foo->name = 'baz';
```

## Why is it reported?

A value is being assigned to a property that is not writable. This typically happens when writing to a `readonly` property outside its initialization context, or to a property that only defines a `get` hook without a `set` hook.

In the example above, the property `$name` is declared as `readonly` and has already been initialized in the constructor. Attempting to assign a new value to it outside the constructor is not allowed.

## How to fix it

Avoid assigning to the property after it has been initialized. If you need to change the value, create a new instance instead:

```diff-php
 <?php declare(strict_types = 1);

 $foo = new Foo('bar');
-$foo->name = 'baz';
+$foo = new Foo('baz');
```
