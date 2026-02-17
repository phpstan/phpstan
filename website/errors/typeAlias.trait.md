---
title: "typeAlias.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait Timestampable
{
	public \DateTimeInterface $createdAt;
}

/**
 * @phpstan-type EntityType Timestampable
 */
class Config
{
}
```

## Why is it reported?

A type alias defined via `@phpstan-type` references a trait, but traits are not valid types in PHP. Traits cannot be used as type declarations, type hints, or in `instanceof` checks. They are a mechanism for code reuse, not a type in the type system.

## How to fix it

Replace the trait reference with an interface or a class that represents the intended type.

```diff-php
 <?php declare(strict_types = 1);

+interface Timestampable
+{
+}
-trait Timestampable
-{
-	public \DateTimeInterface $createdAt;
-}

 /**
  * @phpstan-type EntityType Timestampable
  */
 class Config
 {
 }
```
