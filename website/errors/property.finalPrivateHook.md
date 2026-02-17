---
title: "property.finalPrivateHook"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private string $name {
		final get => $this->name;
	}
}
```

## Why is it reported?

PHP does not allow a private property to have a `final` hook. The `final` modifier on a hook prevents child classes from overriding that hook, but `private` properties and their hooks are already invisible to child classes and cannot be overridden. Combining `private` visibility with a `final` hook is contradictory and results in a compile-time error in PHP 8.4+.

## How to fix it

Remove the `final` modifier from the hook, since private property hooks cannot be overridden anyway:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private string $name {
-		final get => $this->name;
+		get => $this->name;
 	}
 }
```

If the hook needs to be `final` to prevent overriding, change the property visibility to `protected` or `public`:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	private string $name {
+	protected string $name {
 		final get => $this->name;
 	}
 }
```
