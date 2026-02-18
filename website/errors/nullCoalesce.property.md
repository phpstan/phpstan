---
title: "nullCoalesce.property"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public string $name = '';

	public function doFoo(): void
	{
		echo $this->name ?? 'default'; // error: Property Foo::$name (string) on left side of ?? is not nullable.
	}
}
```

## Why is it reported?

The null coalescing operator (`??`) is designed to provide a fallback value when the left-hand side is `null`. When the property being checked is typed as non-nullable (e.g., `string`), it can never be `null`, so the fallback value on the right side of `??` will never be used. This indicates either dead code or a missing nullable type on the property.

## How to fix it

If the property should be nullable, update its type declaration.

```diff-php
 class Foo
 {
-	public string $name = '';
+	public ?string $name = null;

 	public function doFoo(): void
 	{
 		echo $this->name ?? 'default';
 	}
 }
```

If the property is intentionally non-nullable, remove the unnecessary null coalescing operator.

```diff-php
 class Foo
 {
 	public string $name = '';

 	public function doFoo(): void
 	{
-		echo $this->name ?? 'default';
+		echo $this->name;
 	}
 }
```
