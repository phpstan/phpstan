---
title: "property.readOnlyAssignOutOfClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public readonly int $bar;

	public function __construct(int $bar)
	{
		$this->bar = $bar;
	}
}

class Bar extends Foo
{
	public function __construct(int $bar)
	{
		parent::__construct(1);
		$this->bar = $bar;
	}
}
```

## Why is it reported?

PHP's `readonly` properties can only be assigned from within the class that declares them. Assigning a readonly property from a child class, an unrelated class, or a standalone function is not allowed, even if the property's visibility (`public` or `protected`) would otherwise permit access.

In the example above, `Bar` attempts to assign `$this->bar` inside its constructor, but `$bar` is declared as `readonly` in `Foo`. Since `Bar` is not the declaring class, this assignment is forbidden.

## How to fix it

Pass the value to the parent constructor so the declaring class performs the initialization:

```diff-php
 <?php declare(strict_types = 1);

 class Bar extends Foo
 {
 	public function __construct(int $bar)
 	{
-		parent::__construct(1);
-		$this->bar = $bar;
+		parent::__construct($bar);
 	}
 }
```

Or move the readonly property to the class that needs to assign it:

```diff-php
 <?php declare(strict_types = 1);

 class Bar extends Foo
 {
+	public readonly int $baz;
+
 	public function __construct(int $bar)
 	{
 		parent::__construct(1);
-		$this->bar = $bar;
+		$this->baz = $bar;
 	}
 }
```
