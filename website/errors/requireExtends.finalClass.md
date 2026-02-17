---
title: "requireExtends.finalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class BaseService
{
}

/**
 * @phpstan-require-extends BaseService
 */
interface ServiceAware
{
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references a class that is declared as `final`. A final class cannot be extended, so requiring that implementing classes extend it is impossible to satisfy. Any class that would `implements ServiceAware` could never also `extends BaseService` because PHP forbids extending final classes.

## How to fix it

If the class should be extendable, remove the `final` modifier:

```diff-php
 <?php declare(strict_types = 1);

-final class BaseService
+class BaseService
 {
 }

 /**
  * @phpstan-require-extends BaseService
  */
 interface ServiceAware
 {
 }
```

If the class must remain final, remove the `@phpstan-require-extends` tag and consider using `@phpstan-require-implements` with an interface instead:

```diff-php
 <?php declare(strict_types = 1);

+interface BaseServiceInterface
+{
+}
+
 /**
- * @phpstan-require-extends BaseService
+ * @phpstan-require-implements BaseServiceInterface
  */
 interface ServiceAware
 {
 }
```
