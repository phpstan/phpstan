---
title: "property.abstractFinal"
shortDescription: "Property cannot be both abstract and final."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class Foo
{
	abstract public string $name {
		final get;
	}
}
```

## Why is it reported?

A property (or its hook) cannot be both `abstract` and `final`. The `abstract` modifier requires subclasses to provide an implementation, while `final` prevents subclasses from overriding it. These two modifiers are mutually exclusive and combining them is a contradiction that PHP does not allow.

In the example above, the property is declared `abstract` and the `get` hook is declared `final` without a body. A `final` hook without a body is still abstract (it has no implementation), so this creates a conflict -- the hook would need to be implemented by subclasses but the `final` modifier prevents that.

## How to fix it

Remove the `final` modifier from the abstract hook:

```diff-php
 abstract class Foo
 {
 	abstract public string $name {
-		final get;
+		get;
 	}
 }
```

Or provide an implementation and use `final` to prevent further overriding:

```diff-php
-abstract class Foo
+class Foo
 {
-	abstract public string $name {
-		final get;
+	public string $name {
+		final get => 'default';
 	}
 }
```
