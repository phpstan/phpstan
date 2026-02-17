---
title: "argument.invalidPregQuote"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$delimiter = '/';
$pattern = '/' . preg_quote($input) . '/';
```

## Why is it reported?

The call to `preg_quote()` is missing the delimiter parameter or uses an incorrect delimiter. `preg_quote()` escapes special regex characters, but it needs to know the delimiter character to escape it as well. Without the correct delimiter parameter, the quoted string may still contain unescaped delimiter characters, which will break the regular expression.

In the example above, the pattern uses `/` as the delimiter, but `preg_quote()` is called without specifying this delimiter as the second argument.

## How to fix it

Pass the correct delimiter as the second argument to `preg_quote()`:

```diff-php
 <?php declare(strict_types = 1);

 $delimiter = '/';
-$pattern = '/' . preg_quote($input) . '/';
+$pattern = '/' . preg_quote($input, '/') . '/';
```
