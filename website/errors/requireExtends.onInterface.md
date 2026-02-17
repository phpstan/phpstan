---
title: "requireExtends.onInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-require-extends Base
 */
class MyClass
{

}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag is only valid on traits and interfaces. It is used to constrain which classes can use a trait or implement an interface by requiring them to extend a specific base class. Using it on a regular class or enum has no meaning because classes and enums do not impose extension requirements on their users.

## How to fix it

If you intended to use this on a trait, change the declaration:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-require-extends Base
  */
-class MyClass
+trait MyTrait
 {

 }
```

If you intended to use this on an interface, change the declaration:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-require-extends Base
  */
-class MyClass
+interface MyInterface
 {

 }
```

If neither is appropriate, remove the tag:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-require-extends Base
- */
 class MyClass
 {

 }
```
