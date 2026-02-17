---
title: "property.callableType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public callable $callback;
}
```

## Why is it reported?

PHP does not allow `callable` as a property type declaration. While `callable` can be used as a parameter type or return type, it is not valid for properties. This is a PHP language-level restriction.

## How to fix it

Use `\Closure` instead of `callable` for the property type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public callable $callback;
+	public \Closure $callback;
 }
```
