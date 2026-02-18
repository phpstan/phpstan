---
title: "varTag.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewLogger instead */
interface OldLogger
{
}

class Foo
{
	/** @var OldLogger */
	public $logger;
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A `@var` PHPDoc tag references a deprecated interface. Deprecated interfaces are scheduled for removal or replacement. Referencing them in type annotations propagates the dependency on a deprecated API.

## How to fix it

Update the `@var` tag to reference the replacement interface:

```diff-php
 class Foo
 {
-	/** @var OldLogger */
+	/** @var NewLogger */
 	public $logger;
 }
```

If possible, use a native type declaration instead of a `@var` tag:

```diff-php
 class Foo
 {
-	/** @var OldLogger */
-	public $logger;
+	public NewLogger $logger;
 }
```

If the calling code is itself deprecated, the error will not be reported. Mark the class as deprecated if it is part of a deprecation migration:

```diff-php
+/** @deprecated */
 class Foo
 {
 	/** @var OldLogger */
 	public $logger;
 }
```
