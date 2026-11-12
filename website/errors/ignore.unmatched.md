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

When only some of the paths from the configuration are passed on the command line (for example `vendor/bin/phpstan analyse src/Foo`), or when only a list of files is analysed (for example `vendor/bin/phpstan analyse src/Foo.php src/Bar.php`), PHPStan cannot know whether an entry would match in the files that were not analysed. In that case:

* An entry whose path is an analysed file is reported, unless what is reported in that file depends on files that were not analysed - for example a trait used by a class in the file, a function whose declaration decides whether a call to it is reported, or subclasses that a call with named arguments depends on. A file that declares a trait is never judged on its own either, because the trait is analysed in the context of the classes using it.
* An entry whose path is a directory or a wildcard pattern is reported only when all the files it covers were analysed in this way.
* An entry with several `paths` is reported only with the paths that can be judged.
* An entry without a path is not reported at all, because the errors it matches could be anywhere.

When there are no `paths` in the configuration, the directories passed on the command line are taken as the whole project and all entries are reported as usual. A list of files is still only a part of the project, so the rules above apply to it.

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
