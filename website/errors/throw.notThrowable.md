---
title: "throw.notThrowable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

throw 123;
```

## Why is it reported?

PHP only allows throwing objects that implement the `Throwable` interface. Attempting to throw a value of any other type (such as `int`, `string`, or an object that does not implement `Throwable`) will result in a fatal error at runtime.

In the example above, an integer is used in a `throw` statement, which is not a valid throwable value.

## How to fix it

Throw an instance of a class that implements `Throwable`, such as `Exception` or `RuntimeException`:

```diff-php
-throw 123;
+throw new \RuntimeException('Something went wrong');
```

If using a custom class, make sure it extends `Exception` or implements `Throwable`:

```diff-php
-class MyError
+class MyError extends \RuntimeException
 {
 }

 throw new MyError();
```
