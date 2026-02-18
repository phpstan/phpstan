---
title: "traitUse.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewFeature instead */
class OldFeature
{
}

class Foo
{
	use OldFeature;
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A `use` statement in a class body references a deprecated class. The class has been marked with `@deprecated` and is scheduled for removal or replacement. Any usage of it should be migrated to the recommended alternative.

## How to fix it

Replace the deprecated class with the recommended replacement:

```diff-php
 class Foo
 {
-	use OldFeature;
+	use NewFeature;
 }
```

If the calling code is itself deprecated, the error will not be reported. Mark the class as deprecated if it is part of a deprecation migration:

```diff-php
+/** @deprecated */
 class Foo
 {
 	use OldFeature;
 }
```
