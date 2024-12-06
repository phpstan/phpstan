---
title: "Precise try-catch-finally analysis"
date: 2021-04-03
tags: guides
---

PHPStan never used to be very precise about type inference in try-catch-finally blocks. Consider this example:

```php
try {
    $foo = doFoo();
    $bar = doBar();
    $baz = doBaz();
} catch (\InvalidArgumentException $e) {
    // what variables are defined here?
} catch (\RuntimeException $e) {
    // and here?
} finally {
    // and finally... here?
}
```

PHPStan didn't consider what function calls can throw which exception types. So all variables might be undefined in the `catch` and `finally` blocks as far as PHPStan was concerned.

This changes in the [latest PHPStan release](https://github.com/phpstan/phpstan/releases/tag/0.12.83). It now has a very precise idea of each "throw point" in the try block and uses it to inform the type inference engine of possible types in `catch` and `finally` blocks.

```php
/** @throws \InvalidArgumentException */
function doBar(): int
{
    // ...
}

/** @throws \RuntimeException */
function doBaz(): string
{
    // ...
}

try {
    // $foo is surely defined in all catch blocks
    // because the literal 1 doesn't throw anything
    $foo = 1;

    $bar = doBar(); // might throw InvalidArgumentException
    $baz = doBaz(); // might throw RuntimeException
} catch (\InvalidArgumentException $e) {
    // $foo is always defined
    // $bar is never defined
    // $baz is never defined
} catch (\RuntimeException $e) {
    // $foo is always defined
    // $bar is always defined
    // $baz is never defined
}
```

By default, functions without any `@throws` annotation are considered to throw any exception, so all existing code will be interpreted very closely to the previous behavior.

But once you start adding `@throws` to your functions and methods, they will override the ones with only implicit `@throws`. As you add more `@throws` annotations, you get rewarded with more precise analysis!

There will be more exception-related features in the near future, stay tuned!

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I’d really appreciate it!

