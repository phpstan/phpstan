---
title: "interface.disallowedSubtype"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-sealed
 * @phpstan-require-extends AllowedImplementation
 */
interface Contract
{
}

class AllowedImplementation implements Contract
{
}

class DisallowedImplementation implements Contract
{
}
```

## Why is it reported?

The interface or class restricts which types are allowed to extend or implement it using `AllowedSubTypesClassReflectionExtension`. The reported class is not in the list of allowed subtypes. This mechanism enforces closed hierarchies where only specific implementations are permitted, similar to sealed classes in other languages.

## How to fix it

If the class should be an allowed subtype, register it with the appropriate extension. Otherwise, use a different approach that does not require implementing the restricted interface:

```diff-php
-class DisallowedImplementation implements Contract
+class DisallowedImplementation
 {
 }
```

Or restructure the code to extend one of the allowed implementations:

```diff-php
-class DisallowedImplementation implements Contract
+class DisallowedImplementation extends AllowedImplementation
 {
 }
```
