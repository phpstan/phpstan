---
title: "phpstan.reflection"
ignorable: false
---

## Code example

This error does not correspond to a specific PHP code pattern. It is emitted when PHPStan is unable to find the reflection of a class, function, or constant referenced in the analysed code.

## Why is it reported?

PHPStan needs to resolve information about all symbols (classes, interfaces, traits, functions, constants) used in the analysed code. When a symbol cannot be found through reflection, this error is reported.

This typically happens when:

- A class, interface, or trait is not autoloadable
- A required dependency is not installed
- The autoloader configuration does not cover all necessary paths

This error is not ignorable because PHPStan cannot perform accurate analysis when it cannot resolve the types involved.

## How to fix it

Make sure all symbols are autoloadable. Learn more at [Discovering Symbols](/user-guide/discovering-symbols).

Common solutions:

1. Run `composer install` or `composer dump-autoload` to ensure the autoloader is up to date.
2. Add missing paths to the `scanFiles` or `scanDirectories` configuration options in `phpstan.neon`.
3. Add stub files for symbols from extensions that are not always installed.
