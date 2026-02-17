---
title: "property.abstractFinal"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class Foo
{
	abstract final public string $name { get; } // ERROR: Property cannot be both abstract and final.
}
```

## Why is it reported?

A property (or its hook) cannot be both `abstract` and `final`. The `abstract` modifier requires subclasses to provide an implementation, while `final` prevents subclasses from overriding it. These two modifiers are mutually exclusive and combining them is a contradiction that PHP does not allow.

This also applies when an abstract property has a final hook without a body.

## How to fix it

Remove one of the conflicting modifiers. If the property should be overridden by subclasses, keep `abstract`:

```diff-php
 <?php declare(strict_types = 1);

 abstract class Foo
 {
-	abstract final public string $name { get; }
+	abstract public string $name { get; }
 }
```

If the property should not be overridden, provide an implementation and use `final`:

```diff-php
 <?php declare(strict_types = 1);

-abstract class Foo
+class Foo
 {
-	abstract final public string $name { get; }
+	final public string $name {
+		get => 'default';
+	}
 }
```
