---
title: "generics.unresolvable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template TSuccess
 * @template TError
 */
class Result
{
}

/**
 * @extends Result<void, SomeResult::*>
 */
class SomeResult extends Result
{
}
```

## Why is it reported?

The generic type argument in the `@extends`, `@implements`, or `@use` PHPDoc tag contains a type that cannot be resolved. In this example, `SomeResult::*` is used as a template argument for the parent class `Result`, but `SomeResult` is the class being defined, and the `::*` syntax creates a circular reference that cannot be resolved at the point of the class definition.

Common causes of unresolvable types include:
- Self-referencing types that create circular dependencies
- References to class constants or types that do not exist at the point of use
- Invalid type syntax in generic arguments

## How to fix it

Replace the unresolvable type with a concrete, resolvable type:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template TSuccess
  * @template TError
  */
 class Result
 {
 }

 /**
- * @extends Result<void, SomeResult::*>
+ * @extends Result<void, string>
  */
 class SomeResult extends Result
 {
 }
```

Or use a template type from the class itself if the type needs to remain generic:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template TSuccess
  * @template TError
  */
 class Result
 {
 }

 /**
- * @extends Result<void, SomeResult::*>
+ * @template TError
+ * @extends Result<void, TError>
  */
 class SomeResult extends Result
 {
 }
```
