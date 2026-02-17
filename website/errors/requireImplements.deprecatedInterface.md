---
title: "requireImplements.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated */
interface OldInterface {}

/**
 * @phpstan-require-implements OldInterface
 */
trait MyTrait {}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag references an interface that has been marked as `@deprecated`. Using deprecated interfaces in new constraints means your code is relying on functionality that is planned for removal in a future version.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated interface with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-require-implements OldInterface
+ * @phpstan-require-implements NewInterface
  */
 trait MyTrait {}
```

If no replacement exists, remove the `@phpstan-require-implements` tag if the constraint is no longer needed:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-require-implements OldInterface
- */
 trait MyTrait {}
```
