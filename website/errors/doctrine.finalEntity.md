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

If the project uses Doctrine ORM 2.11+ or 3.0+, the entity can stay `final`. These versions use lazy ghost objects instead of proxy subclasses for lazy loading, so extending the entity class is no longer required. Mark the entity with the `#[AllowFinalEntity]` attribute from phpstan-doctrine to suppress this error:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;
+use PHPStan\Doctrine\ORM\AllowFinalEntity;

 #[ORM\Entity]
+#[AllowFinalEntity]
 final class User
 {
 	#[ORM\Id]
 	#[ORM\Column]
 	private int $id;
 }
```
