---
title: "doctrine.internalError"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\EntityManagerInterface;

function getResults(EntityManagerInterface $em): mixed
{
	$qb = $em->createQueryBuilder()
		->select('a')
		->from('App\Entity\Article', 'a');

	return $qb->getQuery()->getResult();
}
```

## Why is it reported?

This error is reported by the [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine) extension.

An internal error occurred while PHPStan was trying to analyse a Doctrine `QueryBuilder` query. This typically means an exception was thrown during DQL analysis when calling `getQuery()` on a `QueryBuilder` instance.

This can happen when the QueryBuilder is constructed in a way that leads to an unexpected error during static analysis of the resulting DQL.

## How to fix it

Verify that the QueryBuilder query is valid and can be executed at runtime. If the query works correctly at runtime but PHPStan reports this error, it may be a bug in the phpstan-doctrine extension. Consider [reporting it](https://github.com/phpstan/phpstan-doctrine/issues).
