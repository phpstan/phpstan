---
title: "method.private"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private function doFoo(): void
	{
	}
}

class Bar extends Foo
{
	public function doBar(): void
	{
		$this->doFoo();
	}
}
```

## Why is it reported?

This error is reported in two situations:

- A call is made to a private method of a parent class. Private methods are not accessible from child classes. In the example above, `Bar` tries to call `doFoo()` which is a private method of `Foo`.
- A call is made to a private method on an object, and the calling scope does not have access to it.

Private visibility means the method can only be called from within the class where it is declared.

## How to fix it

If the method should be accessible to child classes, change the visibility to `protected`:

```diff-php
 class Foo
 {
-	private function doFoo(): void
+	protected function doFoo(): void
 	{
 	}
 }
```

If the method should be accessible from anywhere, change it to `public`:

```diff-php
 class Foo
 {
-	private function doFoo(): void
+	public function doFoo(): void
 	{
 	}
 }
```
