---
title: "class.extendsDeprecatedTrait"
shortDescription: "Class extends a deprecated trait."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldTrait
{
}

class Foo extends OldTrait
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A class extends a trait that has been marked as `@deprecated`. Deprecated traits are planned for removal in a future version.

Note: triggering this identifier requires a class to extend a trait, which PHP itself forbids. PHPStan therefore always also reports a non-ignorable `class.extendsTrait` error alongside this one.

## How to fix it

Use `use` to include the trait, or extend a proper class instead:

```diff-php
-class Foo extends OldTrait
+class Foo
 {
+	use NewHelper;
 }
```
