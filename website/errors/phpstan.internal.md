---
title: "phpstan.internal"
ignorable: false
---

## Code example

This error does not correspond to a specific PHP code pattern. It is emitted when PHPStan encounters an unexpected internal error while analysing a file, such as an unhandled exception in the analyser.

## Why is it reported?

PHPStan encountered an unexpected error during analysis. This is an internal error within PHPStan itself, not a problem with the analysed code. The error message will contain details about the exception that occurred.

This error is not ignorable because it indicates that PHPStan was unable to properly analyse the file. The analysis results for that file are incomplete and cannot be relied upon.

## How to fix it

1. Make sure PHPStan and all its extensions are up to date.
2. Check that all classes, functions, and symbols referenced in the analysed code are autoloadable. Learn more at [Discovering Symbols](/user-guide/discovering-symbols).
3. If the error persists, report it as a bug at [github.com/phpstan/phpstan/issues](https://github.com/phpstan/phpstan/issues) with a minimal reproducing example and the full stack trace.
