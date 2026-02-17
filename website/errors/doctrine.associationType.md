---
title: "doctrine.associationType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Article
{
	/** @var string */
	#[ORM\ManyToOne(targetEntity: Author::class)]
	private $author;
}

#[ORM\Entity]
class Author
{
	#[ORM\Id]
	#[ORM\Column]
	private int $id;
}
```

## Why is it reported?

This error is reported by the [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine) extension.

The property type does not match what Doctrine expects based on the association mapping. For a `ManyToOne` or `OneToOne` association, the property type should be the target entity type (or nullable if the association is not required). For `OneToMany` or `ManyToMany` associations, the property type should be `Collection<int, TargetEntity>`.

In the example above, `$author` is typed as `string` but the `ManyToOne` mapping expects it to be `Author|null`.

## How to fix it

Change the property type to match the association mapping:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;

 #[ORM\Entity]
 class Article
 {
-	/** @var string */
+	/** @var Author|null */
 	#[ORM\ManyToOne(targetEntity: Author::class)]
-	private $author;
+	private ?Author $author = null;
 }
```
