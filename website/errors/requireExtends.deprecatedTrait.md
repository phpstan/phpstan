---
title: "requireExtends.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
	public function help(): void {}
}

/**
 * @phpstan-require-extends OldHelper
 */
trait MyTrait // ERROR: PHPDoc tag @phpstan-require-extends references deprecated trait OldHelper.
{
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references a trait that has been marked as `@deprecated`. Using deprecated symbols in new code is discouraged because they may be removed in a future version, which would break the requirement.

This rule is provided by the [phpstan/phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension package.

## How to fix it

Replace the deprecated trait with its suggested replacement:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-require-extends OldHelper
+ * @phpstan-require-extends NewHelper
  */
 trait MyTrait
 {
 }
```

If there is no replacement and the deprecated trait must still be used, the error can be ignored. If the code using the `@phpstan-require-extends` tag is itself deprecated, the error will not be reported.
