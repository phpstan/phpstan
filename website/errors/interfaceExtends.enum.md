---
title: "interfaceExtends.enum"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Status
{
	case Active;
	case Inactive;
}

interface StatusInterface extends Status // ERROR: Interface StatusInterface extends enum Status.
{
}
```

## Why is it reported?

An interface cannot extend an enum in PHP. Interfaces can only extend other interfaces. Enums are a distinct type in PHP and cannot be used as a parent type for interfaces. This is a language-level constraint that will cause a fatal error at runtime.

## How to fix it

If the enum implements an interface, extend that interface instead:

```diff-php
 <?php declare(strict_types = 1);

+interface HasLabelInterface
+{
+	public function label(): string;
+}
+
 enum Status implements HasLabelInterface
 {
 	case Active;
 	case Inactive;

 	public function label(): string
 	{
 		return $this->name;
 	}
 }

-interface StatusInterface extends Status
+interface StatusInterface extends HasLabelInterface
 {
 }
```

If the intent is to type-hint against the enum, use the enum name directly as a type:

```php
<?php declare(strict_types = 1);

function processStatus(Status $status): void
{
	// ...
}
```
