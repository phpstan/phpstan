---
title: "PHPStan 0.12 Released!"
date: 2019-12-04
tags: releases
ogImage: /images/eu-fossa.jpg
---

This is a massive release that has been in the works for the past six months. We’ve managed to churn out minor releases even during the development of this new major version. This continuous workflow was enabled by [feature toggles](https://github.com/phpstan/phpstan/releases/tag/0.11.4) — people were able to try out the new features even while using the stable version by opting in and give feedback.

But I can’t wait for _everyone_ to get their hands on this new version because it’s so much better. And it’s designed so that everyone is able to upgrade to it immediately without breaking a sweat.

> If you’re not familiar with PHPStan, it’s a static analyser for PHP focused on finding bugs in your code. It catches whole classes of bugs even before you run your app at all. See the [introductory article](/blog/find-bugs-in-your-code-without-writing-tests) if you want to know more about the basics!

**PHPStan 0.12 is brought to you by Gábor Hojtsy ([@gaborhojtsy](https://twitter.com/gaborhojtsy)):**

> PHPStan has already been tremendous help in our preparation for Drupal 9 across the whole Drupal ecosystem, as a backbone of drupal-check, Upgrade Status and drupalci deprecation testing. It allows us to find common patterns in deprecated API use, make critical tooling decisions and rally the community. Thank you!

---

So what’s new in PHPStan 0.12?

## Generics

The flagship feature of this release. It deserves [its own article](/blog/generics-in-php-using-phpdocs) — check it out to learn about what they can do for the type safety and documentation of your code.

How this feature came into fruition in PHPStan? In mid-May this year, [Arnaud Le Blanc](https://twitter.com/arnaud_lb) sent me an email out of the blue that he’d like to help me out with implementing generics. This is exactly the kind of letter you print, frame and hang on the wall. Because what followed was the best collaboration I’ve had with anybody in a long time. We’ve bounced back and forth with over ~50 pull requests and hundreds of commits, gave each other feedback, tested and polished the code. And while there are still some ideas we have that could be implemented in the future, we’re quite happy with this and let others make use of it in 0.12.0.

Part of the feature (rules checking for correct usage of generic types) was implemented during the EU-FOSSA hackaton in Brussels at the beginning of October. But more on that a bit later.

## New level

After some time of having levels 0 to 7, there’s a new level in PHPStan. But it’s not 8.

> Levels in PHPStan serve as a vehicle for progressive introduction of the tool into the codebase and the workflow of the team. Instead of getting all the errors reported that PHPStan is capable to find, it only reports the most severe ones on level 0, lets developers fix them, and only then progress to higher levels which are more strict and fixing them actually requires to improve the overall quality of the code.

Brief overview of levels **on 0.11.x** and before is as follows:

0. basic checks, unknown classes, unknown functions, unknown methods called on `$this`, wrong number of arguments passed to those methods and functions, always undefined variables
1. possibly undefined variables, unknown magic methods and properties on classes with `__call` and `__get`
2. unknown methods checked on all expressions (not just `$this`), validating PHPDocs
3. return types, types assigned to properties
4. basic dead code checking - always false `instanceof` and other type checks, dead `else` branches, unreachable code after return; etc.
5. checking types of arguments passed to methods and functions
6. report partially wrong union types - if you call a method that only exists on some types in a union type, level 6 starts to report that; other possibly incorrect situations
7. report calling methods and accessing properties on nullable types

I believe that all projects are able to achieve level 5 given some reasonable amount of time. But levels 6 and 7 are much easier to achieve on greenfield projects where these checks are enforced since the beginning. They influence how you design your objects a lot.

What I wanted to do for PHPStan 0.12 was to report any missing typehints in the analysed code. If PHPStan doesn’t know type of a variable, it cannot check for any errors on it. Besides completely missing typehints, it also checks for missing iterable value typehints in arrays and other iterables.

Adding that as a new level as level 8 would be an easy way out but wouldn’t reflect the difficulty to achieve it. It’s easier to go through your code and add missing typehints than rewrite the whole thing to satisfy levels 6 and 7.

So the new levels design looks like this:

0. basic checks, unknown classes, unknown functions, unknown methods called on `$this`, wrong number of arguments passed to those methods and functions, always undefined variables
1. possibly undefined variables, unknown magic methods and properties on classes with `__call` and `__get`
2. unknown methods checked on all expressions (not just `$this`), validating PHPDocs
3. return types, types assigned to properties
4. basic dead code checking - always false `instanceof` and other type checks, dead `else` branches, unreachable code after return; etc.
5. checking types of arguments passed to methods and functions
6. **report missing typehints**
7. report partially wrong union types - if you call a method that only exists on some types in a union type, level 7 starts to report that; other possibly incorrect situations
8. report calling methods and accessing properties on nullable types

So who has previously been on levels 5+ should now be able to jump at least one level higher if they add typehints to all their code.

## PHAR distribution by default

PHPStan has been offering `phpstan/phpstan-shim` Composer package that distributes PHPStan as a single PHAR file without risk of conflicting dependencies. Over its lifetime I’ve been removing differencies in usage between the mainstream `phpstan/phpstan` package and this one. It reached a point when it could be used by everyone without any downside. So why it shouldn’t be the default and only distribution method when it’s less hassle and allows more people (those that depend on the same packages as PHPStan itself) to upgrade to the latest version?

With PHPStan 0.12, phpstan-shim is no longer needed and will not be updated anymore. All users should move to `phpstan/phpstan`. PHPStan extensions continue to work as expected.

Development of PHPStan including pull requests is now happening over at [phpstan/phpstan-src](https://github.com/phpstan/phpstan-src).

## Integer range type

PHPStan is now able to reason about integer values between specified bounds. Consider the following code sample:

```php
function foo(int $i)
{
    if ($i > 2 && $i < 5) {
        if ($i === 5) { } // error: this is always false
    }

    if ($i >= 2 && $i < 3) {
        // PHPStan knows that $i is 2
    }

}
```

With this feature, PHPStan again knows more about the code and is able to point out always true or false conditions.

Integer range type was developed by [Jan Tvrdík](https://twitter.com/jantvrdik) also at the EU-FOSSA hackaton in Brussels.

We were honored to be invited to the event. It was a great opportunity to achieve two full days-worth of open-source work what would otherwise take weeks of constant interruptions and back-and-forths. It definitely sped up the development of PHPStan 0.12 and helped bringing it to the final release this year.

## Doctrine extension: compare property type against @ORM\Column definition

[Lukáš Unger](https://twitter.com/lookyman_) implemented checking for inconsistencies in entity mappings at the hackaton. These errors can lead to sneaky bugs. This is also especially relevant now with native property types available in PHP 7.4.

```php
/**
 * @ORM\Column(type="string")
 * @var string|null
 */
private $text; // "Property $text type mapping mismatch: property can contain string|null but database expects string."

/**
 * @ORM\Column(type="string", nullable=true)
 */
private string $title; // "Property $one type mapping mismatch: database can contain string|null but property expects string."
```

You can even describe your own types for this purpose. [See the README](https://github.com/phpstan/phpstan-doctrine/#custom-types) for more details.

## "class-string" pseudotype

It’s dangerous to accept all possible strings in a function but pretend that they’re an existing class name. That’s why PHPStan introduces the “class-string” pseudotype applicable to all PHPDocs. Only literal string or `::class` constants can be passed as an argument into it, or you can require `class-string` through the whole call chain. You can also narrow a `string` into `class-string` with the help of `class_exists()` and related functions. Passing a general `string` into `class-string` is only reported on level 7+.

This type also pairs nicely with [generics](/blog/generics-in-php-using-phpdocs). You can use `class-string<T>` in the generic signature, or you can use `class-string<Foo>` to only accept valid class names that are subtypes of Foo.

## Function calls with no effect

It doesn’t make much sense to call functions and methods without any side effects on separate lines. It only makes sense to call them if you’re interested in their return value. They are the polar opposite of functions that have `void` return type. PHPStan detects if you forget to use the returned value for example by saving it into the variable or passing it to another function call.

Examples of such functions are `count` or `sprintf` and plenty of methods on `DateTimeImmutable`.

```php
$date = new \DateTimeImmutable();

// "Call to method DateTimeImmutable::setTime() on a separate line has no effect."
$date->setTime(0, 0, 0);

// OK
$midnight = $date->setTime(0, 0, 0);
```

## Baseline to the rescue!

This is not a complete list of new features and checks. It would be overwhelming to include everything in this article that's supposed to be easily digestible. That’s what [release notes](https://github.com/phpstan/phpstan/releases/tag/0.12.0) are for.

I encourage everyone to upgrade to get the latest checks. If you're worried how many new errors you will have to fix, I have good news for you. Thanks to the [baseline feature](/blog/phpstans-baseline-feature-lets-you-hold-new-code-to-a-higher-standard) released recently, you can get to a green build immediately and fix those errors later when you have time. But the new and changed code you write will be held to a higher standard set by the new version.
