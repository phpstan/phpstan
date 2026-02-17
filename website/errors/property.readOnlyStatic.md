---
title: "property.readOnlyStatic"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public static readonly int $count;
}
```

## Why is it reported?

PHP does not allow a property to be both `readonly` and `static`. The `readonly` modifier is designed for instance properties to ensure they are assigned only once during object construction. Static properties belong to the class itself rather than to individual instances, and PHP does not support readonly semantics for them. Combining the two modifiers results in a compile-time error.

## How to fix it

If the property should be a readonly instance property, remove the `static` modifier:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public static readonly int $count;
+	public readonly int $count;
 }
```

If the property should be a static class property, remove the `readonly` modifier:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public static readonly int $count;
+	public static int $count;
 }
```

If the goal is to have a class-level constant that cannot be changed, use a class constant instead:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public static readonly int $count;
+	public const int COUNT = 0;
 }
```
