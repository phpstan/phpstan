---
title: "magicConstant.outOfTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function getTraitName(): string
	{
		return __TRAIT__;
	}
}
```

## Why is it reported?

The magic constant `__TRAIT__` returns the name of the trait where it is used. When used outside of a trait, it always returns an empty string. This is almost certainly a mistake, as the developer likely intended to use this constant inside a trait, or meant to use a different magic constant like `__CLASS__`.

## How to fix it

Move the usage of `__TRAIT__` inside a trait, or use a different magic constant that is appropriate for the context.

```diff-php
 <?php declare(strict_types = 1);

-class Foo
+trait Foo
 {
 	public function getTraitName(): string
 	{
 		return __TRAIT__;
 	}
 }
```

Alternatively, if you meant to get the class name:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public function getTraitName(): string
+	public function getClassName(): string
 	{
-		return __TRAIT__;
+		return __CLASS__;
 	}
 }
```
