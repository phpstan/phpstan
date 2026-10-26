---
title: "doctrine.columnType"
shortDescription: "Property type does not match the Doctrine column type mapping."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
	#[ORM\Column(type: 'string')]
	private int $name;
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-doctrine`.

The PHP property type does not match the Doctrine column type mapping. In this example, the Doctrine column type `string` maps to `string` in PHP, but the property is declared as `int`. This mismatch means that:

- The database can contain a value (string) that the property type (int) does not expect, or
- The property can contain a value (int) that the database column (string) does not expect.

This mismatch can lead to type errors at runtime when Doctrine hydrates entities from the database or persists them.

The check also takes value ranges into account. For example, a `smallint` column can contain values in the range `int<-32768, 32767>`, or `int<0, 65535>` when the column is declared with `options: ['unsigned' => true]`. A property typed with a range that does not cover the column's range is reported:

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Product
{
	/** @var int<0, 65535> */
	#[ORM\Column(type: 'smallint')]
	private int $quantity;
}
```

Here the database can contain `int<-32768, 32767>` but the property expects `int<0, 65535>`.

## How to fix it

Align the PHP property type with the Doctrine column type:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;

 #[ORM\Entity]
 class User
 {
 	#[ORM\Column(type: 'string')]
-	private int $name;
+	private string $name;
 }
```

Or change the Doctrine column type to match the property type:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;

 #[ORM\Entity]
 class User
 {
-	#[ORM\Column(type: 'string')]
+	#[ORM\Column(type: 'integer')]
 	private int $name;
 }
```

For `smallint` columns, make the property's integer range match the column's signedness:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;

 #[ORM\Entity]
 class Product
 {
 	/** @var int<0, 65535> */
-	#[ORM\Column(type: 'smallint')]
+	#[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
 	private int $quantity;
 }
```

For nullable columns, make sure the property type includes `null`:

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
	#[ORM\Column(type: 'string', nullable: true)]
	private ?string $name = null;
}
```
