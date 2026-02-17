---
title: "generics.deprecatedClassDefault"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewFormatter instead */
class OldFormatter
{
}

/**
 * @template T = OldFormatter
 */
class Pipeline
{
}
```

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

## Why is it reported?

A `@template` tag uses a deprecated class as its default type value. The `@template T = DefaultClass` syntax specifies a default type for a generic parameter, but when `DefaultClass` is deprecated, the generic class creates a dependency on a class planned for removal. Any usage of `Pipeline` without an explicit type argument would implicitly use the deprecated `OldFormatter`.

In the example above, the template parameter `T` defaults to `OldFormatter`, which is marked as `@deprecated`.

## How to fix it

Replace the deprecated default type with the recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T = OldFormatter
+ * @template T = NewFormatter
  */
 class Pipeline
 {
 }
```

Alternatively, remove the default if there is no suitable non-deprecated replacement:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T = OldFormatter
+ * @template T
  */
 class Pipeline
 {
 }
```
