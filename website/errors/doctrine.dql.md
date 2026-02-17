---
title: "doctrine.dql"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\EntityManagerInterface;

function getUsers(EntityManagerInterface $em): void
{
    $query = $em->createQuery('SELCT u FROM App\Entity\User u');
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-doctrine`.

The DQL (Doctrine Query Language) string passed to `EntityManager::createQuery()` or built via `QueryBuilder` contains a syntax error or references an unknown entity, field, or association. Doctrine parses the DQL at runtime, and invalid DQL will cause a `QueryException`. PHPStan validates the DQL statically to catch these errors before runtime.

In this example, `SELCT` is a typo for `SELECT`.

## How to fix it

Fix the DQL syntax or entity/field references:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\EntityManagerInterface;

 function getUsers(EntityManagerInterface $em): void
 {
-    $query = $em->createQuery('SELCT u FROM App\Entity\User u');
+    $query = $em->createQuery('SELECT u FROM App\Entity\User u');
 }
```

For complex queries, consider using the QueryBuilder API which provides IDE autocompletion and helps prevent syntax errors:

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\EntityManagerInterface;

function getUsers(EntityManagerInterface $em): void
{
    $query = $em->createQueryBuilder()
        ->select('u')
        ->from('App\Entity\User', 'u')
        ->getQuery();
}
```
