---
title: "doctrine.queryBuilderDynamic"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class UserRepository
{
	public function __construct(private EntityManagerInterface $em)
	{
	}

	public function findUsers(QueryBuilder $qb): array
	{
		return $qb->getQuery()->getResult();
	}
}
```

This error is reported by [`phpstan/phpstan-doctrine`](https://github.com/phpstan/phpstan-doctrine).

## Why is it reported?

PHPStan's Doctrine extension validates DQL queries built with `QueryBuilder` at analysis time. To do this, it needs to trace the query builder from its creation (e.g. `$em->createQueryBuilder()`) through the chain of method calls to `getQuery()`. When the query builder is received as a parameter or its origin cannot be determined, PHPStan cannot reconstruct the DQL being built and reports this error.

This error is only reported when the `reportDynamicQueryBuilders` option is enabled in the `phpstan-doctrine` configuration.

## How to fix it

Build the query builder in the same method scope so PHPStan can trace it from creation to execution:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\EntityManagerInterface;

 class UserRepository
 {
 	public function __construct(private EntityManagerInterface $em)
 	{
 	}

-	public function findUsers(QueryBuilder $qb): array
+	public function findUsers(): array
 	{
-		return $qb->getQuery()->getResult();
+		return $this->em->createQueryBuilder()
+			->select('u')
+			->from(User::class, 'u')
+			->getQuery()
+			->getResult();
 	}
 }
```

If the dynamic query builder is intentional and cannot be avoided, the error can be ignored with a PHPStan ignore comment referencing this identifier.
