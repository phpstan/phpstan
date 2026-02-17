---
title: "class.allowDynamicPropertiesReadonly"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

#[\AllowDynamicProperties]
readonly class Foo
{
	public function __construct(
		public string $name,
	)
	{
	}
}
```

## Why is it reported?

The `#[\AllowDynamicProperties]` attribute cannot be used with readonly classes. Readonly classes have all their properties implicitly declared as `readonly`, and dynamic properties are inherently incompatible with readonly semantics because dynamic properties cannot be declared as readonly. PHP will emit a fatal error at runtime.

In the example above, `Foo` is a `readonly` class with the `#[\AllowDynamicProperties]` attribute, which is not allowed.

## How to fix it

Remove the `#[\AllowDynamicProperties]` attribute from the readonly class:

```diff-php
 <?php declare(strict_types = 1);

-#[\AllowDynamicProperties]
 readonly class Foo
 {
 	public function __construct(
 		public string $name,
 	)
 	{
 	}
 }
```

If you need dynamic properties, remove the `readonly` modifier from the class and declare specific properties as `readonly` individually instead:

```diff-php
 <?php declare(strict_types = 1);

 #[\AllowDynamicProperties]
-readonly class Foo
+class Foo
 {
 	public function __construct(
-		public string $name,
+		public readonly string $name,
 	)
 	{
 	}
 }
```
