---
title: "doctrine.findByArgument"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
	#[ORM\Id]
	#[ORM\Column]
	public int $id;

	#[ORM\Column]
	public string $name;
}

function doFoo(EntityManager $em): void
{
	$repository = $em->getRepository(User::class);
	$repository->findBy(['nonexistent' => 'test']);
}
```

## Why is it reported?

The `findBy()` and `findOneBy()` methods on a Doctrine entity repository accept an array of field names as criteria. The field `nonexistent` does not exist on the `User` entity, so the call will fail at runtime with a Doctrine exception.

This rule is provided by the [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine) extension.

## How to fix it

Use a field name that actually exists on the entity:

```diff-php
- $repository->findBy(['nonexistent' => 'test']);
+ $repository->findBy(['name' => 'test']);
```
