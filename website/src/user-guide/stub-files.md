---
title: Stub Files
---

PHPStan depends on [PHPDocs](/writing-php-code/phpdocs-basics) in the analysed and used code. You might encounter a PHPDoc in your `vendor/` that's imprecise and causes an error in the analysis of your project that's a false positive.

To mitigate this, you can write a stub file with the right PHPDoc. It's like source code, but PHPStan only reads PHPDocs from it. So the namespace and class/interface/trait/method/function names must match with the original source you're describing. Method bodies can stay empty, PHPStan is only interested in the PHPDocs.

Native parameter types and return types added in stubs are not considered.

Get inspired by [the stubs PHPStan itself uses](https://github.com/phpstan/phpstan-src/tree/1.12.x/stubs) or by [the stubs from the phpstan-doctrine extension](https://github.com/phpstan/phpstan-doctrine/tree/1.3.x/stubs).

Stub files aren't a replacement for [discovering symbols](/user-guide/discovering-symbols) so if you're trying to fix errors like "Function not found" or "Class not found", check out the [discovering symbols](/user-guide/discovering-symbols) guide instead.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

**What about the analysis of the stub files?**

Stub files don't need to be added to the list of analysed paths. They're analysed independently of the chosen rule level with a set of select rules mainly evaluating the PHPDoc well-formedness.

Errors reported in the stub files also can't be ignored and all of them must be fixed.

</div>

The stub file needs to be added to the `stubFiles` key in the [configuration file](/config-reference):

```yaml
parameters:
	stubFiles:
		- stubs/Foo.stub
		- stubs/Bar.stub
```

They can have any file extension you'd like. Consider choosing `.stub` over `.php` so that the stubs don't confuse your IDE.

Relative paths in the `stubFiles` key are resolved based on the directory of the config file is in.

<div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 mb-4" role="alert">

**Why is PHPStan complaining about "Class not found" in a stub file?**

Stub files are analysed independently from the rest of your codebase. This means that any type they're referencing must also be present as a stub, even if you don't want to override any of its PHPDocs.

*This is a way of tackling the problem of optional dependencies. We want to report any undefined names used in the stubs to prevent typos, but at the same time we also need to support optional dependencies in PHPStan extensions. That's why all the symbols must be defined again in the stubs, even as [empty shells](https://github.com/phpstan/phpstan-phpunit/blob/26394996368b6d033d012547d3197f4e07e23021/stubs/MockObject.stub).*

</div>
