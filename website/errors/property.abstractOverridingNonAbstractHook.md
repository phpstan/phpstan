---
title: "property.abstractOverridingNonAbstractHook"
shortDescription: "An abstract property hook overrides a non-abstract property hook from a parent class."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class ParentClass
{
	public int $bar { get => 42; }
}

abstract class ChildClass extends ParentClass
{
	abstract public int $bar { get; }
}
```

## Why is it reported?

PHP does not allow redeclaring a concrete (non-abstract) property hook as abstract in a child class. The parent class already provides an implementation of the `get` (or `set`) hook, and making it abstract in a subclass would remove that implementation.

This applies to both `get` and `set` hooks independently. If a parent class provides concrete implementations of both hooks and the child class declares both as abstract, each one is reported separately.

Property hooks are a PHP 8.4 feature.

## How to fix it

If the child class needs a different hook implementation, override it with a concrete body:

```diff-php
 abstract class ChildClass extends ParentClass
 {
-	abstract public int $bar { get; }
+	public int $bar { get => 0; }
 }
```

If the intent is to keep the parent's hook implementation, remove the abstract property declaration from the child class:

```diff-php
 abstract class ChildClass extends ParentClass
 {
-	abstract public int $bar { get; }
 }
```
