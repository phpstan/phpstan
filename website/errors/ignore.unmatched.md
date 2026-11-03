---
title: "ignore.unmatched"
shortDescription: "Ignored error pattern in configuration was not matched by any actual error."
ignorable: false
autofixable: true
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

When the entry has a `path`, the error is reported in that file. When the entry has no path, the error is reported at the line where the entry is defined in the configuration file.

This error is controlled by the [`reportUnmatchedIgnoredErrors`](/user-guide/ignoring-errors#reporting-unused-ignores) configuration parameter.

## How to fix it

Remove the unmatched ignore pattern from your configuration. This can be done automatically by running PHPStan with [`--fix`](/user-guide/command-line-usage#--fix%2C---fix-ignored), which removes unmatched entries from the configuration files and the baseline, including entries without a path:

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
