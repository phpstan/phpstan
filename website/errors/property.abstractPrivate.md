---
title: "property.abstractPrivate"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class Foo
{
	abstract private int $value { get; }
}
```

## Why is it reported?

PHP does not allow a property to be both `abstract` and `private`. An abstract property requires subclasses to provide an implementation, but `private` properties are not visible to subclasses. Combining the two modifiers is contradictory and results in a compile-time error in PHP 8.4+.

## How to fix it

Change the visibility to `protected` or `public` so that subclasses can implement the abstract hook:

```diff-php
 <?php declare(strict_types = 1);

 abstract class Foo
 {
-	abstract private int $value { get; }
+	abstract protected int $value { get; }
 }
```
