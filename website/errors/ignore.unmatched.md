---
title: "ignore.unmatched"
ignorable: true
---

## Code example

```neon
# phpstan.neon
parameters:
    ignoreErrors:
        -
            message: '#Call to undefined method Foo::bar\(\)#'
            path: src/MyClass.php
```

## Why is it reported?

An ignored error pattern in your PHPStan configuration was not matched by any actual error during analysis. This means the pattern is no longer needed, either because the underlying code has been fixed or the error no longer occurs. Keeping stale ignore patterns can hide new, unrelated errors that happen to match the pattern.

## How to fix it

Remove the unmatched ignore pattern from your configuration:

```diff-neon
 # phpstan.neon
 parameters:
     ignoreErrors:
-        -
-            message: '#Call to undefined method Foo::bar\(\)#'
-            path: src/MyClass.php
```

If you want to keep the pattern temporarily, you can disable reporting of unmatched errors by setting `reportUnmatched: false` on the specific entry:

```neon
parameters:
    ignoreErrors:
        -
            message: '#Call to undefined method Foo::bar\(\)#'
            path: src/MyClass.php
            reportUnmatched: false
```
