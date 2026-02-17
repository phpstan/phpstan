---
title: "classConstant.finalPrivate"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	final private const BAR = 'baz';
}
```

## Why is it reported?

A class constant is declared as both `final` and `private`. The `final` modifier prevents child classes from overriding the constant, but `private` constants are not visible to child classes and therefore cannot be overridden anyway. Combining `final` with `private` is redundant and misleading.

## How to fix it

Remove the `final` modifier from the private constant:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	final private const BAR = 'baz';
+	private const BAR = 'baz';
 }
```

Alternatively, if the constant should be accessible to child classes but not overridable, change the visibility to `protected` or `public`:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	final private const BAR = 'baz';
+	final protected const BAR = 'baz';
 }
```
