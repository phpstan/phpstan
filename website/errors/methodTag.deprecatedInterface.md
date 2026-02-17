---
title: "methodTag.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{
}

/**
 * @method OldInterface getInterface()
 */
class Foo
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A `@method` PHPDoc tag references a deprecated interface in its type declaration. Deprecated types are planned for removal in a future version, and PHPDoc annotations should not rely on them.

## How to fix it

Update the `@method` tag to use the replacement type:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @method OldInterface getInterface()
+ * @method NewInterface getInterface()
  */
 class Foo
 {
 }
```
