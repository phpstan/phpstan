---
title: "requireExtends.duplicate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-require-extends Base
 * @phpstan-require-extends AnotherBase
 */
interface Constrainable
{

}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag is used more than once on the same interface or trait. Since a class can only extend one parent class, there can only be one `@phpstan-require-extends` constraint. Having multiple tags is contradictory because a class cannot extend two different classes at the same time.

## How to fix it

Keep only one `@phpstan-require-extends` tag:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-require-extends Base
- * @phpstan-require-extends AnotherBase
  */
 interface Constrainable
 {

 }
```
