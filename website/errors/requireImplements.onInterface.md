---
title: "requireImplements.onInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasName
{
}

/**
 * @phpstan-require-implements HasName
 */
interface Nameable
{
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag is only valid on traits. It restricts which classes can use a trait by requiring them to implement a specific interface. Using this tag on an interface or a class has no meaning and is not supported by PHPStan.

## How to fix it

If the intent is to constrain trait usage, move the tag to a trait:

```diff-php
 <?php declare(strict_types = 1);

 interface HasName
 {
 }

-/**
- * @phpstan-require-implements HasName
- */
-interface Nameable
-{
-}
+/**
+ * @phpstan-require-implements HasName
+ */
+trait Nameable
+{
+}
```

If the intent is to make one interface extend another, use standard interface inheritance:

```diff-php
 <?php declare(strict_types = 1);

 interface HasName
 {
 }

-/**
- * @phpstan-require-implements HasName
- */
-interface Nameable
-{
-}
+interface Nameable extends HasName
+{
+}
```
