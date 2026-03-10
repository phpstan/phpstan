---
title: "class.duplicate"
shortDescription: "Same class name is declared in multiple files."
ignorable: true
---

## Code example

This error is reported when the same class name is declared in multiple files within the analysed codebase:

```php
// file1.php
<?php declare(strict_types = 1);

class UserService
{
	public function find(): void
	{
	}
}
```

```php
// file2.php
<?php declare(strict_types = 1);

class UserService
{
	public function find(): void
	{
	}
}
```

## Why is it reported?

The same class name is declared in multiple files within the registered [stub files](/user-guide/stub-files).

## How to fix it

Remove the duplicate declaration, or rename one of the classes:

```diff-php
 <?php declare(strict_types = 1);

 // file2.php
-class UserService
+class AdminUserService
 {
 	public function find(): void
 	{
 	}
 }
```

Or place the classes in different namespaces:

```diff-php
 <?php declare(strict_types = 1);

 // file2.php
+namespace Admin;
+
 class UserService
 {
 	public function find(): void
 	{
 	}
 }
```
