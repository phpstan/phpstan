---
title: "mixin.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewEnum instead */
enum OldEnum
{
	case A;
}

/**
 * @mixin OldEnum
 */
class Foo
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A `@mixin` PHPDoc tag references an enum that has been marked as `@deprecated`. Deprecated types are planned for removal in a future version, and PHPDoc annotations should not rely on them.

## How to fix it

Update the `@mixin` tag to reference the replacement type:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @mixin OldEnum
+ * @mixin NewEnum
  */
 class Foo
 {
 }
```
