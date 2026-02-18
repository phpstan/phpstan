---
title: "property.neverRead"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private ?string $name {
		get => $this->items[$this->index] ?? null;
	}

	/** @var string[] */
	public array $items = [];

	private int $index = 0;

	public function setIndex(int $i): void
	{
		$this->index = $i;
	}
}
```

## Why is it reported?

The property is readable (has a `get` hook) but is never actually read anywhere in the code. This indicates dead code -- a property that provides a read interface but has no consumers. Since the property is private, it can only be accessed within the class, and PHPStan has verified it is never read there.

## How to fix it

Remove the property if it is no longer needed:

```diff-php
 class Foo
 {
-	private ?string $name {
-		get => $this->items[$this->index] ?? null;
-	}
-
 	/** @var string[] */
 	public array $items = [];

 	private int $index = 0;
 }
```

Alternatively, if the property should be read, add the missing read access in the class.
