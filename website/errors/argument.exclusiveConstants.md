---
title: "argument.exclusiveConstants"
shortDescription: "Combining mutually exclusive constants with the | operator in a function parameter."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$a = [3, 1, 2];
sort($a, SORT_NUMERIC | SORT_STRING);
```

## Why is it reported?

The constants `SORT_NUMERIC` and `SORT_STRING` belong to the same exclusive group — they represent alternative sorting strategies. Combining them with `|` is not meaningful because the function can only sort one way at a time. The resulting bitmask value produces undefined behavior.

Some function parameters accept bitmask flags, but certain subsets of those flags are mutually exclusive. For example, `htmlspecialchars()` has two exclusive groups within its `$flags` parameter: one for quote handling (`ENT_QUOTES`, `ENT_NOQUOTES`, `ENT_COMPAT`) and one for document type (`ENT_HTML401`, `ENT_HTML5`, `ENT_XHTML`, `ENT_XML1`). Combining flags from different groups is fine, but combining flags within the same group is an error.

This check is only performed on PHP 8.0+.

## How to fix it

Use only one constant from the exclusive group:

```diff-php
 $a = [3, 1, 2];
-sort($a, SORT_NUMERIC | SORT_STRING);
+sort($a, SORT_NUMERIC);
```

Non-exclusive modifiers can still be combined with the chosen constant:

```diff-php
 $a = [3, 1, 2];
-sort($a, SORT_NUMERIC | SORT_STRING);
+sort($a, SORT_STRING | SORT_FLAG_CASE);
```
