---
title: "class.extendsDeprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewBaseClass instead */
class OldBaseClass
{
}

class Foo extends OldBaseClass
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A class extends a class that has been marked as `@deprecated`. Extending deprecated classes ties your code to implementations that are planned for removal.

In the example above, class `Foo` extends `OldBaseClass`, which is deprecated.

## How to fix it

Replace the deprecated base class with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-class Foo extends OldBaseClass
+class Foo extends NewBaseClass
 {
 }
```
