---
title: "method.protected"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	protected function doSomething(): void
	{
		echo 'hello';
	}
}

function doFoo(Foo $foo): void
{
	$foo->doSomething(); // ERROR: Call to protected method doSomething() of class Foo.
}
```

## Why is it reported?

The code attempts to call a `protected` method from a context where it is not accessible. In PHP, `protected` methods can only be called from within the class that defines them or from a subclass. Calling a protected method from outside the class hierarchy is a language-level error.

## How to fix it

If the method should be accessible from outside the class, change its visibility to `public`:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	protected function doSomething(): void
+	public function doSomething(): void
 	{
 		echo 'hello';
 	}
 }
```

If the method should remain protected, call it from within the class or a subclass:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	protected function doSomething(): void
 	{
 		echo 'hello';
 	}

+	public function run(): void
+	{
+		$this->doSomething();
+	}
 }

 function doFoo(Foo $foo): void
 {
-	$foo->doSomething();
+	$foo->run();
 }
```
