---
title: "doctrine.finalEntity"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class User
{
	#[ORM\Id]
	#[ORM\Column]
	private int $id;
}
```

## Why is it reported?

This error is reported by the [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine) extension.

A Doctrine entity class is declared as `final`. Doctrine uses proxy objects for lazy loading of entity relationships. Proxies are generated as subclasses of the entity class, but a `final` class cannot be extended. This means Doctrine cannot create proxy objects for this entity, which can cause problems with lazy loading.

## How to fix it

Remove the `final` keyword from the entity class:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;

 #[ORM\Entity]
-final class User
+class User
 {
 	#[ORM\Id]
 	#[ORM\Column]
 	private int $id;
 }
```

Starting with [Doctrine ORM 3.4](https://www.doctrine-project.org/2025/06/28/orm-3.4.0-released.html), lazy ghost objects are used by default for lazy loading. Lazy ghost objects do not require extending the entity class, so entities can be `final`. On older Doctrine ORM 3.x versions, lazy ghost objects can be enabled explicitly through the `Configuration` object:

```php
$configuration->setLazyGhostObjectEnabled(true);
```
