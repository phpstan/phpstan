---
title: "property.hookBodyInInterface"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasName
{
	public string $firstName {
		get {
			return 'Foo';
		}
	}
}
```

## Why is it reported?

Interface property hooks cannot have bodies. Properties declared in interfaces are implicitly abstract, meaning their hooks must be declared without a body. The implementing class is responsible for providing the hook implementation.

This is a PHP language constraint introduced with property hooks in PHP 8.4.

## How to fix it

Remove the hook body and declare only the hook signature:

```diff-php
 <?php declare(strict_types = 1);

 interface HasName
 {
-	public string $firstName {
-		get {
-			return 'Foo';
-		}
-	}
+	public string $firstName { get; }
 }
```

Then provide the implementation in a class:

```php
<?php declare(strict_types = 1);

class User implements HasName
{
	public string $firstName {
		get {
			return 'Foo';
		}
	}
}
```
