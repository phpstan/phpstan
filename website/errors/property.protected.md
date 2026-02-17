---
title: "property.protected"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	protected string $bar = 'hello';
}

function doFoo(Foo $foo): void
{
	echo $foo->bar;
}
```

## Why is it reported?

The property `$bar` is declared as `protected`, which means it can only be accessed from within the class itself or from its subclasses. Accessing it from outside the class hierarchy -- such as from a standalone function or from an unrelated class -- violates the visibility constraint and would cause a fatal error at runtime.

## How to fix it

Add a public getter method to expose the property value:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	protected string $bar = 'hello';
+
+	public function getBar(): string
+	{
+		return $this->bar;
+	}
 }

 function doFoo(Foo $foo): void
 {
-	echo $foo->bar;
+	echo $foo->getBar();
 }
```

Or change the property's visibility to `public` if it is safe to do so:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	protected string $bar = 'hello';
+	public string $bar = 'hello';
 }
```

Or move the accessing code into the class or a subclass where the `protected` property is accessible.
