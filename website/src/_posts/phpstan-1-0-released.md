---
title: "PHPStan 1.0 Released!"
date: 2021-11-01
tags: releases
---

<img src="/tmp/images/phpstan-1-0.png" alt="PHPStan 1.0" class="rounded-lg mb-8">

Today is a big milestone for PHPStan after 6 years of development. I realized it reached a level of maturity worthy the major version that's 1.0. Going multiple years [^twelve] without the need for a BC break while delivering improvements in almost streaming fashion with a release every 7 days on average qualifies as being stable enough to deserve it.

[^twelve]: PHPStan 0.12 [was released](/blog/phpstan-0-12-released) on December 4th 2019.

> If you're not familiar with PHPStan, it’s a static analyser for PHP focused on finding bugs in your code. It catches whole classes of bugs even before you run your app at all. See the [introductory article](/blog/find-bugs-in-your-code-without-writing-tests) if you want to know more about the basics!

During those 6 years I saw the early adopters being both delighted and frustrated, I reviewed and merged awesome contributions I wouldn't have come up with myself, I watched all the numbers like the GitHub stars, downloads, and website visitors rise to unforeseen heights. If someone told at the beginning that this project would become a big part of my life and my livelihood, I'd have to pinch myself. I enjoy it so much. I automated and scaled my favourite part of being a software developer: pointing out mistakes in other people's code 🤣

---

For this release I decided it's time to try out something new and to accompany the milestone with a limited merchandise sale. When everything I create is purely virtual, working on something physical and tangible in the real world has been an interesting experience. [Go get yourself](/merch) a PHPStan T-shirt available in blue triblend and white cotton! I also got the idea of [rule level](/user-guide/rule-levels) badges that you can pin to your clothes and bags to show off the code quality of the project you're working on 😅

Don't hesitate too much - the sale is on only for the next three weeks until **November&nbsp;22nd**.

<a href="/merch"><img src="/tmp/images/og-merch.jpg" alt="PHPStan Merch" class="rounded-lg mb-8 border border-indigo-500 p-4 hover:border-indigo-300"></a>

I can't wait to see the T-shirts and the badges on other people at conferences, or even better – randomly on a street.

---

## Level 9

The flagship feature of PHPStan 1.0 is a brand-new level 9. It acknowledges that using `mixed` in your code isn't actually safe at all, and that you should really do something about it. It's unforgiving and downright brutal. The only thing that you can do with `mixed` on level 9 is to pass it to another `mixed`. You can't pass it to a different type, you can't call methods on it, you can't access properties on it etc., because they might not exist. Don't be afraid to try it and see what it finds in your codebase. You can always promise yourself you'll do a better job from now on and get a little help from [the baseline](/user-guide/baseline) 😊

## Goodies from Bleeding Edge

Instead of working on new features separately in a feature branch without them seeing the light of day until the next major version, I take a different approach: the code [lives in regular releases but is turned off](/blog/what-is-bleeding-edge) by default. Users have the option to try out these features without jumping through any hoops. The next major version enables them for everyone. The advantage is that when these features come out, they're already battle-tested, reshaped, and polished by feedback from early adopters.

Dozens of points from the [1.0 release notes](https://github.com/phpstan/phpstan/releases/tag/1.0.0) were initially released as part of bleeding edge during the 0.12.x era, including the level 9. Some of the most notable are:

**Consistent remembering and forgetting returned values**. PHPStan now remembers when you call a function for a second time and the function is supposed to return the same value. In some cases you'll have to use `@phpstan-pure` and `@phpstan-impure` annotations to persuade it about the opposite. [Learn more »](/blog/remembering-and-forgetting-returned-values)

**Precise try-catch-finally analysis**. `@throws` annotations are now fully taken into account when inferring types in `catch` and `finally` blocks. [Learn more »](/blog/precise-try-catch-finally-analysis)

**Detecting unused private properties, methods, and constants**. Declaring a private class element, and not using it, is certainly a mistake. PHPStan also allows extending PHPStan's comprehension of your code where you might declare seemingly unused properties and constants for good reasons. [Learn more »](/blog/detecting-unused-private-properties-methods-constants)

**Generic array function stubs**. This feature will tell you when you're passing an incompatible callback into functions like `array_map` or `usort`. [See an example »](/r/898c9c94-eb22-42cd-b9eb-2f5959290f63)

**Inspecting types for missing typehints recursively**. Typing `array<array>` is no longer good enough. PHPStan requires specifying the type of values of the inner array too. [^mixed]

[^mixed]: You can still choose to silence PHPStan with `array<mixed>` but that's not going to get you very far on level 9 😅

**Type descriptions are cross-compatible with PHPDocs**. Types in error messages can now be directly [used in PHPDocs](/writing-php-code/phpdoc-types). For example: [array shapes](/writing-php-code/phpdoc-types#array-shapes) are now described with the same `array{...}` syntax instead of `array(...)`.

**...and much much more!** The [1.0 release notes](https://github.com/phpstan/phpstan/releases/tag/1.0.0) contain more than 200 items, both contributed recently and accumulated in bleeding edge over the past 1½ years.

## Stability Benefits

The 1.x version range comes with a greater responsibility. That's why I outlined two Backward Compatibility Promise documents [for users](/user-guide/backward-compatibility-promise) and [for developers](/developing-extensions/backward-compatibility-promise). I encourage you to read them as some aspects of developing a static analyser is challenging from this perspective. One person's bugfix can still be another person's failed build.

The [developer documentation](/developing-extensions/extension-types) is now also much more comprehensive. The subarticles in [Core concepts](/developing-extensions/core-concepts) tell you everything you need to take advantage of static analysis in your custom extensions. And after reading the [Custom rules](/developing-extensions/rules) article you'll certainly be able to automate the tedious parts of code reviews with rules tailored to your codebase.

## A Bright Future

1.0 doesn't mean it's perfect or finished. Thanks to [GitHub Sponsors](https://github.com/sponsors/ondrejmirtes), [PHPStan Pro](/blog/introducing-phpstan-pro), and other income sources, developing PHPStan is now my full-time job. There are many ideas in the pipeline and we can do much more with the knowledge of the code PHPStan has. I can't already wait for what's gonna be in the upcoming releases, starting with PHP 8.1 support.

Another area to look out for is that PHPStan also serves as a foundation to other innovative projects like [Rector](https://getrector.org/), and I want to continue taking care of that too.

I'd like to thank everyone that have been part of PHPStan's development over the past 6 years. It doesn't matter if you're a contributor, a user, or my patient and understanding family, PHPStan wouldn't be the same without you.
