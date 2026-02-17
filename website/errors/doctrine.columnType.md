---
title: "doctrine.columnType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
	/**
	 * @ORM\Column(type="string")
	 */
	private int $name;
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-doctrine`.

The PHP property type does not match the Doctrine column type mapping. In this example, the Doctrine column type `string` maps to `string` in PHP, but the property is declared as `int`. This mismatch means that:

- The database can contain a value (string) that the property type (int) does not expect, or
- The property can contain a value (int) that the database column (string) does not expect.

This mismatch can lead to type errors at runtime when Doctrine hydrates entities from the database or persists them.

## How to fix it

Align the PHP property type with the Doctrine column type:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class User
 {
 	/**
 	 * @ORM\Column(type="string")
 	 */
-	private int $name;
+	private string $name;
 }
```

Or change the Doctrine column type to match the property type:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class User
 {
 	/**
-	 * @ORM\Column(type="string")
+	 * @ORM\Column(type="integer")
 	 */
 	private int $name;
 }
```

For nullable columns, make sure the property type includes `null`:

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private ?string $name = null;
}
```
