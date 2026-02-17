---
title: "requireExtends.interface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface SomeInterface {}

/**
 * @phpstan-require-extends SomeInterface
 */
interface MyInterface {}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag is used to require that a class using a trait or implementing an interface must extend a specific class. However, it only accepts class names, not interfaces. The tag refers to `extends` in the class inheritance sense, which in PHP applies only to classes.

If you want to require that a class implements a specific interface, the `@phpstan-require-implements` tag should be used instead.

## How to fix it

If you meant to require that the implementing class also implements a specific interface, use `@phpstan-require-implements`:

```diff-php
 <?php declare(strict_types = 1);

 interface SomeInterface {}

 /**
- * @phpstan-require-extends SomeInterface
+ * @phpstan-require-implements SomeInterface
  */
 interface MyInterface {}
```

If you meant to require extending a specific class, provide a class name instead of an interface:

```diff-php
 <?php declare(strict_types = 1);

 class SomeBaseClass {}

 /**
- * @phpstan-require-extends SomeInterface
+ * @phpstan-require-extends SomeBaseClass
  */
 interface MyInterface {}
```
