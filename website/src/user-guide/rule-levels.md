---
title: Rule Levels
---

If you want to use PHPStan but your codebase isn't up to speed with strong typing and PHPStan's strict checks, you can currently choose from 9 levels (0 is the loosest and 8 is the strictest) by passing `-l|--level` to the `analyse` command.

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
