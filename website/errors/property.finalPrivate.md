---
title: "property.finalPrivate"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	final private int $bar;
}
```

## Why is it reported?

PHP does not allow a property to be both `final` and `private`. The `final` modifier prevents child classes from overriding a property, but `private` properties are already invisible to child classes and cannot be overridden. Combining the two modifiers is contradictory and results in a compile-time error in PHP 8.4+.

## How to fix it

Remove one of the conflicting modifiers. If the property should not be visible to child classes, use `private` without `final`:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	final private int $bar;
+	private int $bar;
 }
```

If the property should be visible to child classes but not overridable, use `final protected` or `final public`:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	final private int $bar;
+	final protected int $bar;
 }
```
