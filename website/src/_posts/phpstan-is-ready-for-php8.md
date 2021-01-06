---
title: "PHPStan is ready for PHP 8!"
date: 2020-11-24
tags: releases
---

PHP 8 is just around the corner! And it's massive. PHPStan is ready to analyse your codebases that will be taking advantage of the latest features in the coming weeks and months.

I'll leave the job of describing [all the new features and changes](https://php.watch/versions/8.0) to others that [specialize in that](https://stitcher.io/), and [as usual](https://phpstan.org/blog/phpstan-now-fully-supports-php-7-4), I'll geek out a bit about the challenges linked with making PHPStan understand a new major version of the language, and also describe new implemented checks you'll be able to enjoy alongside the new language features.

Make sure you have [PHPStan 0.12.57](https://github.com/phpstan/phpstan/releases/tag/0.12.57) or later installed before you start experimenting with PHP 8! 💪

Match expression
======================

[Ambitious language feature](https://wiki.php.net/rfc/match_expression_v2) from PHPStan contributor Ilija Tovilo that aims to be a better alternative to a switch statement.

Since the `match` expression uses a strict comparison equivalent with the `===` operator, we can afford to report type mismatches - arms that will [never be executed](https://phpstan.org/r/ad9f2b05-98d5-49fa-b0c3-4e1c476f349f).

Arms also might not be executed if one of the arms is a catch-all because the comparison will be [always true](https://phpstan.org/r/c6a35cff-50a8-45d4-a4bb-4d785e95043a).

The `match` expression also throws an exception when the given value isn't handled by one of the arms. Since PHPStan knows enough information about the code, it can [detect it as well](https://phpstan.org/r/379724eb-514c-45fd-b357-02a5f4ebae18). I hope that this feature will lead to using more [advanced types in PHPDocs](https://phpstan.org/writing-php-code/phpdoc-types), which in turn will actually lead to more type safety (PHPStan will point out wrong values going into the function at the callsite):

```php
/**
 * @param 1|2|3 $i
 */
function foo(int $i): void {
	match ($i) {
		1 => 'foo',
		2 => 'bar',
		3 => 'baz',
	};
}
```

Named arguments
======================

I'm a big fan of [named arguments](https://stitcher.io/blog/php-8-named-arguments). They will make function calls with multiple arguments more clear [^moreArguments].

[^moreArguments]: A popular "clean code" argument is that a function shouldn't have more than a few arguments, but it's not always possible nor practical.

```php
return in_array(needle: $i, haystack: $intList, strict: true);
```

Implementing support for this feature was pretty straightforward, so I took extra care with user-facing error messages. This is how an ordinary error looks like when named arguments aren't involved:

> Parameter #2 $b of function foo expects int, string given.

With named arguments, the order is no longer significant, so when they're involved in a function call, I'm removing the parameter number: [^ux]

[^ux]: Good UX is getting thousand tiny little things right.

> Parameter $b of function foo expects int, string given.

When the developer passes a wrong number of arguments to a function, the ordinary error looks like this:

> Function foo invoked with 1 parameter, 2 required.

When a named argument is used in the function call, it's much nicer to show this:

> Missing parameter $b (int) in call to function foo.

So that's what PHPStan does.

Changed function signatures
======================

Some functions changed their signatures, for example `curl_*` functions no longer return resource, [but a `CurlHandle` object](https://php.watch/versions/8.0/resource-CurlHandle). Many functions have removed `false` from possible returned values and [throw `ValueError` instead](https://php.watch/versions/8.0/ValueError).

<a href="https://phpstan.org/r/5043c64b-59f1-418c-a0da-9341f9f4938e"><img src="/images/curl-php-8.png" class="mb-8 rounded-lg border border-gray-300 mx-auto"></a>

Fortunately, PHP 8 starts to offer [official stubs](https://github.com/search?q=repo%3Aphp%2Fphp-src+filename%3A*.stub.php&type=Code) that we can take advantage of here. I created a [new repository](https://github.com/phpstan/php-8-stubs) that allows including those stubs as a Composer dependency. It's automatically updated each night to mirror the latest changes in php-src.

It wasn't straightforward to start using those stubs, because they don't contain all the information PHPStan needs, so for example they cannot be used in place of [jetbrains/phpstorm-stubs](https://github.com/jetbrains/phpstorm-stubs). Class definitions do not contain constants and properties, some global constants are referenced but not defined etc. So PHPStan only reads parameter types, parameter names, return types, and PHPDocs, and uses them in a very customized way.

Also, I didn't want to lose other metadata we already have in [functionMap.php](https://github.com/phpstan/phpstan-src/blob/3e956033ad718b56c607f026bd670613db02f151/resources/functionMap.php), like what value types are in typehinted arrays, or callback signatures. So in the end all of this information is merged together in the final definitions used during the analysis.

Constructor property promotion
======================

I really like [this feature](https://php.watch/versions/8.0/constructor-property-promotion), because it decreases the number of times an injected property name needs to mentioned from 4 to 1. A lot of boilerplate will be simplified.

The most interesting part of the implementation was finding out how people would write additional type information with PHPDocs. Sure, we have typed properties since PHP 7.4, but for example in case of `array`, we need to know what's in it, so PHPDocs are still necessary in some cases.

Since the Twitter poll ended with 74 %/26 % split, I decided to implement both variants. 26 % is still a lot of people.

PHPStan will also check that you [haven't declared](https://phpstan.org/r/3c1a5fd2-8157-4808-8485-fd4035bd8f5b) duplicate properties with the same name, and that you haven't tried to write a promoted property in [another method than constructor](https://phpstan.org/r/83420326-6076-479b-a6f3-68761c3a101a) (which isn't a parse error).

Nullsafe operator
======================

Adding support for [this one](https://wiki.php.net/rfc/nullsafe_operator) (also by Ilija Tovilo) was more work than I originally expected. [PHP-Parser](https://github.com/nikic/php-parser) added two new AST nodes to represent this operator: `NullsafeMethodCall` and `NullsafePropertyFetch`. So I had to go through all the code where the usual `MethodCall` and `PropertyFetch` nodes are mentioned[^mostCommon] and make sure it also makes sense for handling the nullsafe variants.

[^mostCommon]: Arguably the most common thing PHP developers do is calling methods and accessing properties so there's a lot of concerns in PHPStan handling those.

Another tricky part was the short-circuiting. I've had to reread this part of the RFC several times before I realized it has two implications for PHPStan:

1) When analysing an ordinary method call or a property fetch, the result might be nullable even if the called method and accessed property aren't nullable, because there might be a nullsafe operator earlier in the chain.
2) The `$foo` in `$foo?->bar()` will not be nullable when referenced again in the same chain.

PHPStan will also tell you if you're using `?->` where an ordinary `->` would suffice, [on level 4](https://phpstan.org/r/3de670aa-814b-4160-b0df-0e01adbf881c).

The RFC also disallows assign-by-ref when the nullsafe operator is involved, PHPStan will [tell you about that too](https://phpstan.org/r/fad45e1a-6f1c-4518-9b68-f030d6228910).

Nullsafe operator also cannot be used on the [left side of an assignment](https://phpstan.org/r/ae38ff4b-2a17-4bca-813d-bb9c8e0b2d86).

And the nullsafe operator cannot be used with [parameters passed by reference](https://phpstan.org/r/36b0ff5d-5ba6-494e-9280-7ae4789b4089) and as a return value in function that [return by reference](https://phpstan.org/r/0a9357aa-0df4-459a-93db-30a43b27f584).

As I said - a lot of work 😅

$object::class
======================

PHP 7 allows you to get a string with a class name with `Foo::class`. PHP 8 allows you to access `::class` on an object.

> Did you know that PHP does not check whether `Foo` exists and will [happily create any string](https://3v4l.org/htngG) like that? Fortunately [PHPStan checks that for you](https://phpstan.org/r/e5516379-ceba-4cbe-a0d5-de5ed31361cd) 😊

During the implementation I found out that the accessed variable cannot be a string, so PHPStan also [checks for that](https://phpstan.org/r/d4fd58ab-064a-4c4d-850d-239242e92bab).

Attributes
======================

After finally [deciding on the syntax](https://wiki.php.net/rfc/shorter_attribute_syntax_change#voting), attributes have a bright future ahead. I'm especially looking forward to using them as part of Doctrine ORM entities instead of current PHPDoc-based annotations.

Validation of attributes in PHP runtime itself is postponed until the code tries to call `newInstance()` on the obtained `ReflectionAttribute` instance. The RFC specifically mentions that static analysis is a great fit to validate attribute usage so that the user isn't surprised when they run the code that reads and instantiate the attributes.

PHPStan provides the following checks related to attributes:

* #[Attribute]-annotated class cannot be abstract and must have a public constructor ([playground example](https://phpstan.org/r/85ef24c3-df32-452b-97e0-e4ae4e3d5d45))
* Attribute name used in code must be an existing class annotated with #[Attribute] ([playground example](https://phpstan.org/r/8fd5e419-18b4-4142-8fa7-bc4ba8bcbc22))
* Attribute class constructor must be called correctly ([playground example](https://phpstan.org/r/2f10a38e-e4f1-4b78-9f01-740b7e4b1b5a))
* Attribute class can be used only with the specified target(s) ([playground example](https://phpstan.org/r/1103bd62-101d-43f3-ba4e-dba68d0a0a60))
* Non-repeatable attribute class cannot occur multiple times above the same element ([playground example](https://phpstan.org/r/b94dc461-5b9b-42d5-a4c6-c1dc33f44d91))

New Docker image
=========================

If you prefer to run PHPStan through Docker, I recommend you to switch to a new image hosted in GitHub Container Registry: `ghcr.io/phpstan/phpstan`

It's based on PHP 8. If you want to analyse a codebase as if it was written for an older PHP version, change `phpVersion` in your `phpstan.neon`:

```yaml
parameters:
    phpVersion: 70400 # PHP 7.4
```

See the image's [homepage in GHCR](https://github.com/orgs/phpstan/packages/container/package/phpstan), or the documentation [here on phpstan.org](/user-guide/docker).

The old image hosted on DockerHub is now deprecated.

---

Do you like PHPStan and use it every day? Support the development by checking out and subscribing to [PHPStan Pro](/blog/introducing-phpstan-pro). Thank you!
