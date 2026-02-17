---
title: "constructor.call"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function __construct(private string $name)
	{
	}

	public function reinitialize(string $name): void
	{
		$this->__construct($name);
	}
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

Calling `__construct()` on an already-constructed object is not a standard practice in PHP. The constructor is intended to be called once during object creation via `new`. Re-calling it can lead to unexpected behavior because it bypasses the normal object lifecycle.

Similarly, static calls to `__construct()` (e.g. `SomeClass::__construct()`) are only appropriate as `parent::__construct()` calls within a child class constructor. Calling another class's constructor statically from outside that context is a code smell.

## How to fix it

Use a dedicated method or a factory pattern instead of re-calling the constructor:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function __construct(private string $name)
 	{
 	}

-	public function reinitialize(string $name): void
+	public function withName(string $name): self
 	{
-		$this->__construct($name);
+		return new self($name);
 	}
 }
```
