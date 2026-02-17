---
title: "regexp.pattern"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$result = preg_match('/[unclosed/', 'test');
```

## Why is it reported?

The regular expression pattern passed to a `preg_*` function is invalid and would produce a warning or error at runtime. PHP's PCRE engine cannot compile the pattern, which means the function call will fail. PHPStan validates regex patterns at analysis time when they can be statically resolved.

Common causes include missing closing delimiters, unmatched brackets, invalid escape sequences, and other PCRE syntax errors.

## How to fix it

Correct the regular expression syntax:

```diff-php
 <?php declare(strict_types = 1);

-$result = preg_match('/[unclosed/', 'test');
+$result = preg_match('/\[unclosed/', 'test');
```

Or if the intent was a character class, close the bracket:

```diff-php
 <?php declare(strict_types = 1);

-$result = preg_match('/[unclosed/', 'test');
+$result = preg_match('/[unclosed]/', 'test');
```

Test the corrected pattern using a tool like [regex101](https://regex101.com/) to verify it matches the intended strings before applying the fix.
