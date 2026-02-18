---
title: "sealed.nonObject"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-sealed int
 */
class Foo
{
}
```

## Why is it reported?

The `@phpstan-sealed` PHPDoc tag contains a non-object type such as `int`, `string`, or `array`. This tag is used to declare that a class or interface can only be extended or implemented by the listed types. Only class and interface names are valid in `@phpstan-sealed` because only objects can extend or implement other types.

## How to fix it

Replace the non-object type with a valid class or interface name:

```diff-php
 /**
- * @phpstan-sealed int
+ * @phpstan-sealed ChildClass
  */
 class Foo
 {
 }
```

The `@phpstan-sealed` tag accepts one or more class/interface names, separated by `|`:

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-sealed ChildA|ChildB
 */
class Foo
{
}
```
