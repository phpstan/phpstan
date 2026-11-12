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

When only some of the paths from the configuration are passed on the command line (for example `vendor/bin/phpstan analyse src/Foo`), PHPStan cannot know whether an entry would match in the files that were not analysed. In that case an entry whose path is a directory or a wildcard pattern is reported only when that path is under the analysed paths, and an entry with several `paths` is reported only with the analysed ones. An entry without a path is not reported at all, because the errors it matches could be anywhere. When there are no `paths` in the configuration, the paths passed on the command line are taken as the whole project and all entries are reported as usual.

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
