---
title: "The Plan to Support PHP 8.1"
date: 2021-11-25
tags: other
---

For the past few years PHPStan [always](/blog/phpstan-now-fully-supports-php-7-4) had [support](/blog/phpstan-is-ready-for-php8) for the latest PHP version on day one, and I considered a matter of pride to achieve that.

Today marks the new PHP 8.1 release that [contains a lot of cool new features](https://stitcher.io/blog/new-in-php-81) which I'm really excited about. Unfortunately, unlike the past releases, PHPStan isn't fully ready for them yet. This is because the whole fall season I've been really busy with the massive [PHPStan 1.0 release](/blog/phpstan-1-0-released).

So what's the current situation? Since 1.0 PHPStan was able to run on PHP 8.1 without triggering any deprecations or other issues, but you couldn't take advantage of the new PHP 8.1 features.

[PHPStan 1.1](https://github.com/phpstan/phpstan/releases/tag/1.1.0) brought support for the native `never` type, pure intersection types, and tentative return types.

[PHPStan 1.2](https://github.com/phpstan/phpstan/releases/tag/1.2.0) brought support for `new` in initializers, first-class callables, and the `array_is_list()` function.

[PHPStan 1.3](https://github.com/phpstan/phpstan/releases/tag/1.3.0) brought support for enums.

[PHPStan 1.4](https://github.com/phpstan/phpstan/releases/tag/1.4.0) brought support for readonly properties.

[PHPStan 1.6](https://github.com/phpstan/phpstan/releases/tag/1.6.0) updated changed function signatures. As of this release, **PHP 8.1 is fully supported**!

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I’d really appreciate it!
