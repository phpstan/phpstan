---
title: "constructor.unusedParameter"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function __construct(int $value, string $unused)
	{
		echo $value;
	}
}
```

## Why is it reported?

The constructor has a parameter that is never used within the constructor body. In the example above, the parameter `$unused` is declared but never referenced.

This only applies to non-promoted parameters. Constructor-promoted parameters (those with visibility keywords like `public`, `protected`, or `private`) are excluded because they serve the purpose of initializing properties.

## How to fix it

Remove the unused parameter if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public function __construct(int $value, string $unused)
+	public function __construct(int $value)
 	{
 		echo $value;
 	}
 }
```

Or use the parameter in the constructor body. Alternatively, promote it to a property:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public function __construct(int $value, string $unused)
+	public function __construct(int $value, private string $unused)
 	{
 		echo $value;
 	}
 }
```
