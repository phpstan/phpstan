---
title: "property.parentPropertyFinal"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	final public string $name = 'base';
}

class Child extends Base
{
	public string $name = 'child';
}
```

## Why is it reported?

The child class overrides a property that is declared as `final` in the parent class. Final properties cannot be overridden by child classes. This is a PHP language-level restriction enforced since PHP 8.4.

Properties declared with `private(set)` visibility are also implicitly final, because the set operation cannot be overridden.

## How to fix it

Remove the overriding property declaration from the child class:

```diff-php
 class Child extends Base
 {
-	public string $name = 'child';
 }
```

If the child class needs different behavior, ask the parent class maintainer to remove the `final` keyword, or use a different property name:

```diff-php
 class Child extends Base
 {
-	public string $name = 'child';
+	public string $displayName = 'child';
 }
```
