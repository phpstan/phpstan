---
title: "method.callToAbstract"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class Foo
{
	abstract public function doSomething(): void;
}

class Bar extends Foo
{
	public function doSomething(): void
	{
		parent::doSomething(); // error: Cannot call abstract method Foo::doSomething().
	}
}
```

## Why is it reported?

Abstract methods have no implementation in the declaring class. Calling an abstract method via `parent::` will result in a fatal error at runtime because there is no method body to execute. This typically happens when a child class overrides an abstract method and mistakenly calls `parent::` on it.

## How to fix it

Remove the call to the abstract parent method. If shared logic is needed, move it to a non-abstract method in the parent class.

```diff-php
 abstract class Foo
 {
 	abstract public function doSomething(): void;
+
+	protected function sharedLogic(): void
+	{
+		// shared implementation
+	}
 }

 class Bar extends Foo
 {
 	public function doSomething(): void
 	{
-		parent::doSomething();
+		$this->sharedLogic();
 		// ...
 	}
 }
```
