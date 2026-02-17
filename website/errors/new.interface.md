---
title: "new.interface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface LoggerInterface
{
	public function log(string $message): void;
}

$logger = new LoggerInterface();
```

## Why is it reported?

PHP interfaces cannot be instantiated. An interface defines a contract that classes must implement, but it does not provide concrete implementations of its methods. Attempting to use `new` with an interface name will result in a fatal error at runtime.

## How to fix it

Instantiate a concrete class that implements the interface instead.

```diff-php
 <?php declare(strict_types = 1);

-$logger = new LoggerInterface();
+$logger = new FileLogger();
```
