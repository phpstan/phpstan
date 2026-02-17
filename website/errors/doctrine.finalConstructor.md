---
title: "doctrine.finalConstructor"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class MyEntity
{
	final public function __construct(private string $name)
	{
	}
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-doctrine`.

Doctrine ORM uses proxy objects for lazy loading. These proxies extend the entity class and override the constructor to prevent premature initialization. If the constructor is declared `final`, Doctrine cannot create a proxy class for this entity, which breaks lazy loading functionality.

This applies to all Doctrine ORM entities (but not embeddables, which are not proxied).

## How to fix it

Remove the `final` keyword from the constructor:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class MyEntity
 {
-	final public function __construct(private string $name)
+	public function __construct(private string $name)
 	{
 	}
 }
```
