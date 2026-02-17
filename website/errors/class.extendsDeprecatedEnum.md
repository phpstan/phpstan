---
title: "class.extendsDeprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus
{
	case Active;
}

class MyClass extends OldStatus
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A class extends a type that has been marked as `@deprecated` and whose type description is an enum. Deprecated types are planned for removal in a future version, and code should not depend on them.

## How to fix it

Replace the deprecated type with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-class MyClass extends OldStatus
+class MyClass extends NewStatus
 {
 }
```
