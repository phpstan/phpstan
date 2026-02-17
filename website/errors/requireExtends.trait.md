---
title: "requireExtends.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait Logging
{

}

/**
 * @phpstan-require-extends Logging
 */
interface HasLogging
{

}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references a trait, but it expects a class. A class can only extend another class, not a trait. Traits are used via the `use` keyword, not through inheritance.

## How to fix it

If you want to require that a class uses a specific trait, there is no built-in PHPDoc tag for that. Instead, reference a class in `@phpstan-require-extends`:

```diff-php
 <?php declare(strict_types = 1);

+class BaseWithLogging
+{
+	use Logging;
+}
+
 /**
- * @phpstan-require-extends Logging
+ * @phpstan-require-extends BaseWithLogging
  */
 interface HasLogging
 {

 }
```

Alternatively, if the trait should be an interface, use `@phpstan-require-implements` instead:

```diff-php
 <?php declare(strict_types = 1);

-trait Logging
+interface Logging
 {

 }

 /**
- * @phpstan-require-extends Logging
+ * @phpstan-require-implements Logging
  */
 interface HasLogging
 {

 }
```
