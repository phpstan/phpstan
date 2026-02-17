---
title: "property.onlyRead"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private int $counter;

	public function getCounter(): int
	{
		return $this->counter;
	}
}
```

## Why is it reported?

The private property is read but never written to anywhere in the class. Since it is `private`, no code outside the class can assign a value to it either. This means the property will always hold its default value (or be uninitialized), which usually indicates dead code or a missing assignment.

## How to fix it

Write to the property where appropriate, for example in the constructor:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private int $counter;

+	public function __construct(int $initial)
+	{
+		$this->counter = $initial;
+	}
+
 	public function getCounter(): int
 	{
 		return $this->counter;
 	}
 }
```

If the property is no longer needed, remove it and its usages.
