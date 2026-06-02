---
title: "doctrine.findOneByArgument"
shortDescription: "Field name passed to repository findOneBy() does not exist on the entity."
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

When [bleeding edge](/blog/what-is-bleeding-edge) is enabled, the field names in the second argument (the order-by array) are checked the same way:

```php
$repository->findOneBy(['email' => 'john@example.com'], ['username' => 'ASC']);
```

## How to fix it

Use a field name that exists on the entity:

```diff-php
 <?php declare(strict_types = 1);

 /** @var \Doctrine\ORM\EntityRepository<User> $repository */
-$user = $repository->findOneBy(['username' => 'john']);
+$user = $repository->findOneBy(['email' => 'john@example.com']);
```

The same applies to the order-by argument:

```diff-php
-$user = $repository->findOneBy(['email' => 'john@example.com'], ['username' => 'ASC']);
+$user = $repository->findOneBy(['email' => 'john@example.com'], ['email' => 'ASC']);
```
