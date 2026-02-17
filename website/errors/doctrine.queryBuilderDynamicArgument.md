---
title: "doctrine.queryBuilderDynamicArgument"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\EntityManagerInterface;

function buildQuery(EntityManagerInterface $em, string $field): void
{
	$qb = $em->createQueryBuilder()
		->select('u')
		->from('App\Entity\User', 'u')
		->where("u.$field = :value")
		->setParameter('value', 'test');

	$qb->getQuery();
}
```

## Why is it reported?

This error is reported by the [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine) extension.

PHPStan could not statically analyse the QueryBuilder because it contains dynamic (non-constant) arguments. In the example above, the `$field` variable makes the DQL string dynamic, preventing PHPStan from validating the query at analysis time.

## How to fix it

Use constant string values in QueryBuilder method calls so that PHPStan can statically determine the resulting DQL:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\EntityManagerInterface;

-function buildQuery(EntityManagerInterface $em, string $field): void
+function buildQuery(EntityManagerInterface $em): void
 {
 	$qb = $em->createQueryBuilder()
 		->select('u')
 		->from('App\Entity\User', 'u')
-		->where("u.$field = :value")
+		->where('u.email = :value')
 		->setParameter('value', 'test');

 	$qb->getQuery();
 }
```
