---
title: PHPStan Turbo
searchKeywords:
  - turbo
  - phpstan_turbo
  - phpstan/turbo
  - pie
  - performance
---

PHPStan Turbo is a native PHP extension that makes PHPStan about 60% faster on a cold run. It reimplements most of PHPStan's analysis engine in C++, and it also lets PHPStan use optimizations a plain PHP program can't.

The extension is optional. PHPStan runs fine without it, and the analysis result is identical with and without it. Only the speed changes.

| Analysing WordPress on M1 Pro | Without Turbo | With Turbo |
|---|--:|--:|
| PHPStan 2.2.6 | 32.0 s | 24.7 s |
| PHPStan 2.3.0 | 19.5 s | 7.9 s |

[Learn more about the performance improvements in PHPStan 2.3 »](/blog/phpstan-2-3-leap-in-performance)

Installation
-------------

**If you install PHPStan with Composer, you don't have to do anything.** The `phpstan/phpstan` package ships prebuilt binaries next to `phpstan.phar`. PHPStan picks the one matching the running PHP and restarts itself with it loaded through `php -d extension=`.

Binaries are available for PHP 8.2 and newer on these platforms:

* Linux x86_64 and arm64, both glibc and musl (Alpine)
* macOS arm64 (Apple Silicon)
* Windows x86_64

On Linux and Windows there are binaries for both non-thread-safe and thread-safe (ZTS) PHP. On macOS only non-thread-safe PHP is supported. Debug builds of PHP aren't supported.

Each platform has one shared core that all PHP versions load, plus a small binary for each PHP version, so the whole Composer package is about 29 MB to download and 87 MB unpacked.

The binaries can be verified with [build attestations](/user-guide/build-attestations).

### Installing with PIE

If you run a `phpstan.phar` you downloaded yourself instead of installing it with Composer, the binaries aren't next to it. PHPStan prints a note:

```
 ! [NOTE] PHPStan runs without PHPStan Turbo.
 !        With PHPStan Turbo, analysis is up to 60% faster.
 !        Install PHPStan with Composer: composer require --dev phpstan/phpstan
 !        Or install PHPStan Turbo with PIE: pie install phpstan/turbo:2.3.5
```

Install the extension with [PIE](https://github.com/php/pie) for the version of PHPStan you're running:

```bash
pie install phpstan/turbo:2.3.5
```

PIE adds the extension to your `php.ini`, so PHPStan doesn't have to restart to load it. You have to reinstall the extension each time you upgrade `phpstan.phar`.

### Incompatible version

Each PHPStan release expects exactly one version of the extension. If the loaded extension has a different version, for example after you upgraded PHPStan but not the extension installed with PIE, PHPStan prints a note and runs without it:

```
 ! [NOTE] Incompatible version of the phpstan_turbo extension.
 !        The loaded extension is version 2d4b432 but this PHPStan version
 !        expects 67acd19.
 !        The extension stays inactive and PHPStan runs slower than necessary.
 !        Update the extension to the version appropriate for PHPStan 2.3.5.
```

An outdated extension can only cost you speed, it never changes the analysis result.

Checking whether Turbo is enabled
-------------

Run `vendor/bin/phpstan diagnose`, or `vendor/bin/phpstan analyse -vvv`:

```
Turbo extension: enabled (version c9f7d89)
Turbo platform: linux-gnu-x86_64 (os: Linux, machine: x86_64, libc: gnu, php: 8.4, zts: no, debug: no)
Turbo worker binary: vendor/phpstan/phpstan/turbo-ext/linux-gnu-x86_64/phpstan_turbo-8.4.so (loaded via process restart)
Turbo trusted types: on (PHPStan's own argument and return type checks are dropped; --debug keeps them)
```

The `diagnose` command itself never restarts, so it reports the extension as loaded by the restart an `analyse` run performs.

There's no option to turn Turbo off. An installed extension with the matching version is always used.

Process restart
-------------

When the extension isn't already loaded through `php.ini`, PHPStan restarts its process at the start with the extension loaded. The same restart also:

* Enables OPcache in the configuration PHPStan runs fastest with, when the OPcache extension is available and the workers can be forked (see below).
* Disarms Xdebug, PCOV and uopz, which slow down the analysis 1.5× to 4.5×. Run PHPStan with `--xdebug` to keep Xdebug enabled.

The restarted process keeps your `memory_limit`.

PHPStan doesn't restart under the Blackfire profiler, or for the `diagnose`, `worker`, `clear-result-cache` and `dump-parameters` commands.

How it works
-------------

### Shadowed classes

Every native class replaces a PHP class of the same name. The PHP class is the reference implementation and is what runs without the extension. A PHP class with a native twin carries the `#[ShadowedByTurboExtension]` attribute:

```php
#[ShadowedByTurboExtension(
	implementation: __DIR__ . '/../turbo-ext/src/TrinaryLogic.cpp',
)]
final class TrinaryLogic
```

When the extension is active, PHPStan declares the native classes under their real names before any of the PHP twins can load. Every `instanceof`, type hint and dependency injection lookup resolves to the native class, and reflection still reads the PHP declaration with its PHPDocs.

About 350 classes are shadowed. They include `NodeScopeResolver`, `MutatingScope`, `TypeSpecifier`, every expression and statement handler, the `Type` implementations, `TypeCombinator`, and the most used reflection classes. The extension also reimplements the parser engine of [nikic/php-parser](https://github.com/nikic/PHP-Parser) and produces identical ASTs.

What stays in PHP: [rules](/developing-extensions/rules), third-party [extensions](/user-guide/extension-library), BetterReflection and phpdoc-parser. The [extension APIs](/developing-extensions/extension-types) work unchanged, so you don't have to do anything in your custom rules and extensions to benefit from Turbo.

### Trusted types

PHPStan analyses its own source code on level 8, so its run-time checks of parameter and return types mostly re-check what static analysis already proved. Turbo registers an OPcache optimizer pass that removes these checks from code compiled from the PHPStan phar.

Code outside the phar keeps its checks, including the checks on values PHPStan passes into it. What's lost is the type check when an extension passes a value into PHPStan's code: a wrong value doesn't throw a `TypeError` at the boundary, and an `int` passed to a `float` parameter isn't converted to `float`. If you suspect that's happening, run PHPStan with [`--debug`](/user-guide/command-line-usage#--debug), which keeps all type checks.

### Forked workers

PHPStan runs the analysis in [parallel](/config-reference#parallel-processing) worker processes. Without Turbo, it spawns them, and each worker boots PHPStan from scratch. With Turbo, PHPStan forks them with `pcntl_fork()`, so the workers inherit the booted process and the warm OPcache.

PHP running from a phar can't normally fork safely, because all the processes share one file descriptor for reading the archive. Turbo gives each forked worker its own.

Forking requires the `pcntl` and `posix` extensions, so it isn't available on Windows. PHPStan also doesn't fork when OPcache JIT is enabled. In these cases it spawns the workers with the extension loaded.

Trusted types are an OPcache optimizer pass, so they need OPcache too.

### Shared memory

Some data computed once per run, like the function signature map and the symbol indexes, is shared between the workers through shared memory instead of each worker holding its own copy.

Other tools
-------------

Tools built on top of PHPStan benefit from Turbo too. [Rector 2.8](https://github.com/rectorphp/rector/releases/tag/2.8.0) runs more than 2× faster with it, for example 10 s instead of 23.6 s.

The source code of the extension is at [phpstan/turbo-ext](https://github.com/phpstan/turbo-ext).
