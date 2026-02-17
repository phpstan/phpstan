---
title: "doctrine.findOneByArgument"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
	#[ORM\Id]
	#[ORM\Column]
	private int $id;

	#[ORM\Column]
	private string $email;
}
```

```php
<?php declare(strict_types = 1);

/** @var \Doctrine\ORM\EntityRepository<User> $repository */
$user = $repository->findOneBy(['username' => 'john']);
```

## Why is it reported?

This error is reported by the [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine) extension.

The criteria array passed to `findOneBy()` contains a key (`username`) that does not correspond to any field or association on the entity (`User`). This likely indicates a typo or a reference to a field that does not exist.

## How to fix it

Use a field name that exists on the entity:

```diff-php
 <?php declare(strict_types = 1);

 /** @var \Doctrine\ORM\EntityRepository<User> $repository */
-$user = $repository->findOneBy(['username' => 'john']);
+$user = $repository->findOneBy(['email' => 'john@example.com']);
```
