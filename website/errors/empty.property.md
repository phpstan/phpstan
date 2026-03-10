---
title: "empty.property"
shortDescription: "Using empty() on a property with a known type is redundant."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Bar {}

class Foo
{
	public Bar $bar;

	public function __construct()
	{
		$this->bar = new Bar();
	}
}

function test(): void
{
	$foo = new Foo();
	$foo->bar = new Bar();
	if (empty($foo->bar)) {
		echo 'empty';
	}
}
```

## Why is it reported?

The property used inside `empty()` has a type that PHPStan can fully evaluate for emptiness. When the property type is always falsy or never falsy, the `empty()` check is either always `true` or always `false`, indicating a logic error or a check that should be written more explicitly.

In the example above, `$foo->bar` is typed as `Bar`, which is an object and can never be falsy. The `empty()` call on it always returns `false`.

## How to fix it

Remove the redundant `empty()` check since an object value is never falsy:

```diff-php
 function test(): void
 {
 	$foo = new Foo();
 	$foo->bar = new Bar();
-	if (empty($foo->bar)) {
-		echo 'empty';
-	}
+	echo $foo->bar::class;
 }
```

If the property can legitimately be nullable, adjust the type to reflect that:

```diff-php
 class Foo
 {
-	public Bar $bar;
+	public ?Bar $bar;

 	public function __construct()
 	{
-		$this->bar = new Bar();
+		$this->bar = null;
 	}
 }
```
