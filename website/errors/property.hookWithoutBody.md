---
title: "property.hookWithoutBody"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Person
{
	public string $name { get; set; }
}
```

## Why is it reported?

In PHP 8.4+, property hooks (`get` and `set`) must have a body unless the property is declared `abstract` in an abstract class. A hook without a body (e.g., `get;` instead of `get { ... }`) is only valid for abstract properties, where the implementation is deferred to child classes.

In the example above, `Person` is not abstract and the property `$name` is not abstract, so the hooks must provide their implementation bodies.

## How to fix it

Add bodies to the property hooks:

```diff-php
 <?php declare(strict_types = 1);

 class Person
 {
-	public string $name { get; set; }
+	public string $name {
+		get => $this->name;
+		set => $this->name = $value;
+	}
 }
```

Or if the intention is to defer the implementation, declare both the class and the property as abstract:

```diff-php
 <?php declare(strict_types = 1);

-class Person
+abstract class Person
 {
-	public string $name { get; set; }
+	abstract public string $name { get; set; }
 }
```

Or remove the hooks entirely if the property does not need custom get/set logic:

```diff-php
 <?php declare(strict_types = 1);

 class Person
 {
-	public string $name { get; set; }
+	public string $name;
 }
```
