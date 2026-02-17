---
title: "classImplements.class"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Logger
{
}

class FileLogger implements Logger
{
}
```

## Why is it reported?

In PHP, a class can only implement interfaces. The `implements` keyword is reserved exclusively for interface types. Using a class in the `implements` clause is not valid.

In the example above, `FileLogger` attempts to implement `Logger`, which is a class, not an interface.

## How to fix it

Extract an interface from the class, then implement the interface:

```diff-php
 <?php declare(strict_types = 1);

-class Logger
+interface LoggerInterface
 {
+	public function log(string $message): void;
 }

-class FileLogger implements Logger
+class FileLogger implements LoggerInterface
 {
+	public function log(string $message): void
+	{
+		file_put_contents('app.log', $message . PHP_EOL, FILE_APPEND);
+	}
 }
```

Alternatively, if the intent is to reuse functionality from the parent class, use `extends` instead of `implements`:

```diff-php
 <?php declare(strict_types = 1);

-class FileLogger implements Logger
+class FileLogger extends Logger
 {
 }
```
