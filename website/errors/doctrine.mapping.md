---
title: "doctrine.mapping"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FooWithoutPK
{
	#[ORM\Column]
	private string $name;
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-doctrine`.

Doctrine ORM detected a mapping configuration error in the entity class. The specific error message comes directly from Doctrine's metadata validation. Common causes include:

- Missing identifier/primary key definition (every entity must have an `@Id` or `#[ORM\Id]` column)
- Invalid column type mappings
- Incorrect association mappings (e.g. missing `inversedBy` or `mappedBy`)
- Annotation or attribute syntax errors in the mapping configuration

These errors would cause Doctrine to throw an exception at runtime when it tries to load the entity metadata.

## How to fix it

The fix depends on the specific Doctrine mapping error reported. For example, if a primary key is missing, add one:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;

 #[ORM\Entity]
 class FooWithoutPK
 {
+	#[ORM\Id]
+	#[ORM\GeneratedValue]
+	#[ORM\Column]
+	private ?int $id = null;
+
 	#[ORM\Column]
 	private string $name;
 }
```

Consult the [Doctrine ORM mapping documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/basic-mapping.html) for details on the correct mapping configuration.
