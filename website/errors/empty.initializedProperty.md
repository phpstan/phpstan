---
title: "empty.initializedProperty"
shortDescription: "Using empty() on a non-nullable initialized property is redundant."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Bar {}

class Foo
{
	private Bar $bar;

	public function __construct(Bar $bar)
	{
		$this->bar = $bar;
	}

	public function check(): void
	{
		if (empty($this->bar)) {
			echo 'empty';
		}
	}
}
```

## Why is it reported?

The property used inside `empty()` has a native type that is not nullable and PHPStan can determine the property has been initialized. Since the property is always assigned a value and cannot be `null`, `empty()` cannot be checking for an uninitialized or null state. In addition, the type is never falsy, so `empty()` is always `false`.

In the example above, `$this->bar` is typed as `Bar` and is always initialized in the constructor. An object value is never falsy, so `empty()` on it is redundant.

## How to fix it

Remove the redundant `empty()` check since the property is always initialized and never falsy:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private Bar $bar;

 	public function __construct(Bar $bar)
 	{
 		$this->bar = $bar;
 	}

 	public function check(): void
 	{
-		if (empty($this->bar)) {
-			echo 'empty';
-		}
+		echo $this->bar::class;
 	}
 }
```
