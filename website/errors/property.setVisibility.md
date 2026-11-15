---
title: "property.setVisibility"
shortDescription: "Overriding property narrows the set visibility of the parent property."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
	public string $name;
}

class ChildClass extends ParentClass
{
	public private(set) string $name;
}
```

## Why is it reported?

PHP 8.4 introduced asymmetric visibility, which allows a property to have a different visibility for writing than for reading, for example `public private(set)` or `public protected(set)`. A property overriding a parent property, or implementing an interface property, cannot have a more restrictive set visibility than the original property. Code that writes to the parent property would no longer be allowed to write to the child property, which breaks the [Liskov substitution principle](https://en.wikipedia.org/wiki/Liskov_substitution_principle). PHP reports a fatal error when the class is declared.

The set visibility order from widest to narrowest is `public(set)`, `protected(set)`, `private(set)`. A property without explicit set visibility has the same set visibility as its read visibility, and a `readonly` property is implicitly `protected(set)`.

## How to fix it

Use the same or a wider set visibility as the parent property:

```diff-php
 <?php declare(strict_types = 1);

 class ChildClass extends ParentClass
 {
-	public private(set) string $name;
+	public string $name;
 }
```

If the parent property is `protected(set)`, the child property can be `protected(set)` or `public(set)`:

```diff-php
 <?php declare(strict_types = 1);

 class ParentClass
 {
 	public protected(set) string $name;
 }

 class ChildClass extends ParentClass
 {
-	public private(set) string $name;
+	public protected(set) string $name;
 }
```

If writes should really be restricted, change the set visibility of the parent property as well.
