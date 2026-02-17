---
title: "property.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{
	public function doSomething(): void;
}

class Foo
{
	public OldInterface $service;
}
```

## Why is it reported?

A property's type declaration references an interface that has been marked as `@deprecated`. Using deprecated interfaces in property types ties the code to APIs that are scheduled for removal. The deprecation notice indicates that a replacement exists and should be used instead.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated interface with its recommended replacement in the property type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public OldInterface $service;
+	public NewInterface $service;
 }
```

If the replacement interface does not exist yet or the migration is ongoing, the error can be ignored on a per-line basis using a PHPStan ignore comment while the migration is being planned.
