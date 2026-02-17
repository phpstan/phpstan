---
title: "interface.extendsDeprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus
{
	case Active;
	case Inactive;
}

interface StatusInterface extends OldStatus // ERROR: Interface StatusInterface extends deprecated enum OldStatus.
{
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

The interface extends an enum that has been marked with the `@deprecated` PHPDoc tag. Extending a deprecated type ties your interface to an implementation that is scheduled for removal or replacement. When the deprecated enum is eventually removed, the interface declaration will break.

## How to fix it

Replace the deprecated enum with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-interface StatusInterface extends OldStatus
+interface StatusInterface extends NewStatus
 {
 }
```

If the enum should not be extended at all, define the required contract directly on the interface:

```diff-php
 <?php declare(strict_types = 1);

-interface StatusInterface extends OldStatus
+interface StatusInterface
 {
+	public function label(): string;
 }
```
