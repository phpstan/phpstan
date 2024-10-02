---
title: Rule Levels
---

If you want to use PHPStan but your codebase isn't up to speed with strong typing and PHPStan's strict checks, you can currently choose from 10 levels (0 is the loosest and 9 is the strictest) by passing `-l|--level` to the `analyse` command.

```bash
vendor/bin/phpstan analyse -l 6 src tests
```

The default level is 0. Once you specify a configuration file, you also have to specify the level to run.

This feature enables incremental adoption of PHPStan checks. You can start using PHPStan with a lower rule level and increase it when you feel like it.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

**Baseline**

To be able to run a higher level without fixing all the reported errors first, check out a feature called [the baseline](/user-guide/baseline).
</div>

You can also use `--level max` as an alias for the highest level. This will ensure that you will always use the highest level when upgrading to new versions of PHPStan. [^levelmax]

[^levelmax]: Please note that this can create a significant obstacle when upgrading to a newer version because you might have to fix a lot of code to bring the number of errors down to zero.

Here's a brief overview of what's checked on each level. Levels are cumulative - for example running level 5 also gives you all the checks from levels 0-4.

0. basic checks, unknown classes, unknown functions, unknown methods called on `$this`, wrong number of arguments passed to those methods and functions, always undefined variables
1. possibly undefined variables, unknown magic methods and properties on classes with `__call` and `__get`
2. unknown methods checked on all expressions (not just `$this`), validating PHPDocs
3. return types, types assigned to properties
4. basic dead code checking - always false `instanceof` and other type checks, dead `else` branches, unreachable code after return; etc.
5. checking types of arguments passed to methods and functions
6. report missing typehints
7. report partially wrong union types - if you call a method that only exists on some types in a union type, level 7 starts to report that; other possibly incorrect situations
8. report calling methods and accessing properties on nullable types
9. be strict about explicit `mixed` type - the only allowed operation you can do with it is to pass it to another `mixed`
10. (New in PHPStan 2.0) be even more strict about the `mixed` type - reports errors even for implicit mixed (missing type), not just explicit mixed

Want to go further?
------------

If the level 9 isn't enough for you and you're looking for even more strictness and type safety, here are some tips. You can use them even alongside lower rule levels.

Use [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) extension. It configures PHPStan in a stricter way and offers additional rules that revolve around strictly and strongly typed code with no loose casting for those who want additional safety in extremely defensive programming.

Enable [Bleeding Edge](/blog/what-is-bleeding-edge). It's a preview of what's coming in the next major release of PHPStan, but shipping in the current stable release. Bleeding edge users are often rewarded with a more capable analysis sooner than the rest. It can also come with performance improvements. If you enable bleeding edge, and adopt new PHPStan features continuously, you're gonna have much less work to do when the next major version ships for everyone.

If you use a popular framework like Symfony, Doctrine or Laravel etc., make sure you install a [corresponding extension](/user-guide/extension-library). It will improve understanding of your code, and also comes with extra rules for correct usage.

Go through the extra [configuration options](/config-reference#stricter-analysis) for stricter analysis. Some of them are enabled when you install phpstan-strict-rules, but there are some extra options that aren't part of any rule level, nor phpstan-strict-rules. A few examples:

* [`checkUninitializedProperties`](/config-reference#checkuninitializedproperties): Report typed properties not set in constructor
* [`checkImplicitMixed`](/config-reference#checkimplicitmixed): Level 9 on steroids
* [`checkBenevolentUnionTypes`](/config-reference#checkbenevolentuniontypes): Report wrong usage of unknown array keys, and other types
* [`rememberPossiblyImpureFunctionValues: false`](/config-reference#rememberpossiblyimpurefunctionvalues): Do not remember return values of functions that are not marked as pure
* [`reportPossiblyNonexistentGeneralArrayOffset`](/config-reference#reportpossiblynonexistentgeneralarrayoffset): Make sure offset exists before accessing it on a general array
* [`reportPossiblyNonexistentConstantArrayOffset`](/config-reference#reportpossiblynonexistentconstantarrayoffset): Make sure offset exists before accessing it on an array shape
* [Bring your exceptions under control with `@throws`](/blog/bring-your-exceptions-under-control)
