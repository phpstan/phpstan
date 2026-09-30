---
title: "argument.invalidPregQuote"
shortDescription: "Call to preg_quote() inside a regular expression pattern is missing the delimiter argument or uses a different delimiter than the pattern."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function foo(string $input): void {
    preg_match("/" . preg_quote($input) . "/", "test");
}
```

## Why is it reported?

The call to `preg_quote()` is missing the delimiter parameter or uses an incorrect delimiter. `preg_quote()` escapes special regex characters, but it needs to know the delimiter character to escape it as well. Without the correct delimiter parameter, the quoted string may still contain unescaped delimiter characters, which will break the regular expression.

In the example above, the pattern uses `/` as the delimiter, but `preg_quote()` is called without specifying this delimiter as the second argument.

The error is also reported when `preg_quote()` is given a delimiter that differs from the one the pattern actually uses, for example `preg_quote($input, '/')` inside a pattern delimited by `&`. Delimiters that `preg_quote()` always escapes (such as `#`, `{`, `}`, `(`, `)` and `|`) don't need to be passed explicitly.

Besides single patterns passed to `preg_match()`, `preg_match_all()`, `preg_grep()`, `preg_split()` and others, each element of an array of patterns passed to `preg_replace()`, `preg_replace_callback()` and `preg_filter()` is checked as well:

```php
<?php declare(strict_types = 1);

function bar(string $input, string $subject): string {
    return preg_replace(['&' . preg_quote($input, '/') . 'pattern&'], 'x', $subject);
}
```

## How to fix it

Pass the correct delimiter as the second argument to `preg_quote()`:

```diff-php
 <?php declare(strict_types = 1);

 function foo(string $input): void {
-    preg_match("/" . preg_quote($input) . "/", "test");
+    preg_match("/" . preg_quote($input, "/") . "/", "test");
 }
```

Make sure the delimiter passed to `preg_quote()` matches the one used by the pattern:

```diff-php
 <?php declare(strict_types = 1);

 function bar(string $input, string $subject): string {
-    return preg_replace(['&' . preg_quote($input, '/') . 'pattern&'], 'x', $subject);
+    return preg_replace(['&' . preg_quote($input, '&') . 'pattern&'], 'x', $subject);
 }
```

Alternatively, switch the pattern to a delimiter that `preg_quote()` always escapes, such as `#`:

```diff-php
 <?php declare(strict_types = 1);

 function foo(string $input): void {
-    preg_match("/" . preg_quote($input) . "/", "test");
+    preg_match("#" . preg_quote($input) . "#", "test");
 }
```
