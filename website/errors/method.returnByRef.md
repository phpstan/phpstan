---
title: "method.returnByRef"
shortDescription: "Method or property hook overrides a parent method or hook that returns by reference, but does not return by reference."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @var array<int> */
	private array $items = [];

	/** @return array<int> */
	public function &getItems(): array
	{
		return $this->items;
	}
}

class Bar extends Foo
{
	/** @return array<int> */
	public function getItems(): array
	{
		return [];
	}
}
```

## Why is it reported?

The parent method is declared with `&` and returns by reference, but the overriding method returns by value. PHP requires the child method to keep returning by reference, because callers of the parent method may rely on getting a reference (for example `$items = &$foo->getItems();`). PHP reports a fatal error when the class is declared.

The same rule applies to property hooks: a `get` hook overriding a `&get` hook from a parent class or an interface has to return by reference too:

```php
<?php declare(strict_types = 1);

interface HasValue
{
	public int $value { &get; }
}

class Foo implements HasValue
{
	public int $value {
		get => 1;
	}
}
```

## How to fix it

Make the overriding method return by reference:

```diff-php
 <?php declare(strict_types = 1);

 class Bar extends Foo
 {
+	/** @var array<int> */
+	private array $barItems = [];
+
 	/** @return array<int> */
-	public function getItems(): array
+	public function &getItems(): array
 	{
-		return [];
+		return $this->barItems;
 	}
 }
```

For a property hook, add `&` to the `get` hook:

```diff-php
 <?php declare(strict_types = 1);

 class Foo implements HasValue
 {
+	private int $storage = 1;
+
 	public int $value {
-		get => 1;
+		&get => $this->storage;
 	}
 }
```

If returning by reference is not needed, remove the `&` from the parent method or hook.
