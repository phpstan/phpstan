---
title: "Zero Config Analysis with Static Reflection"
date: 2020-06-08
tags: releases
---

Some PHPStan releases bring new checks, some of them improve performance, and others consist mostly of bugfixes. But [the latest release](https://github.com/phpstan/phpstan/releases/tag/0.12.26) is about making lives of PHPStan's users much easier. It now requires less configuration and overall fiddling when getting started with it. And it makes analysis of certain codebases possible, whereas before it wasn't possible at all.

A little bit of history
-----------------------

For the past 3.5 years of PHPStan's existence it relied on [runtime PHP reflection](https://www.php.net/manual/en/book.reflection.php) to find out information about the analysed code. It served us well, but came with a few tradeoffs.

Runtime reflection relies on autoloading - the analysed classes and functions have to be loaded in memory for the analysis to be successful. PHPStan users had to set up autoloading for all analysed code which had been a bit awkward - directories like `tests` or `migrations` had to be added to Composer's `autoload-dev` section even if the application or test runner didn't require it.

Also, it wasn't possible to analyse files that mixed classes/functions and side-effects:

```php
function doSomething(): void
{
    // ...
}

doSomething();
```

Loading this file (so that the function is known and code can be analysed) would mean that the function would actually be executed!

<blockquote class="twitter-tweet tw-align-center" data-conversation="none" data-lang="en" data-dnt="true" data-theme="light"><p lang="en" dir="ltr">PHPstan complains that the class doesn&#39;t exist if I just pass it the single class file to analyse. I have to also pass the same class file as an autoload parameter... and then it works.<br><br>I just wanted PHP / something to warn me when I pass too many parameters to a function :D</p>&mdash; Kohan Ikin (@syneryder) <a href="https://twitter.com/syneryder/status/1235674566166339584?ref_src=twsrc%5Etfw">March 5, 2020</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

For 3.5 years I had to tell people they need to separate their files into two piles: declarations-only and side-effects-only, otherwise PHPStan isn't usable on their code. But today, I'm releasing a new PHPStan version (0.12.26) which I finally don't have to be embarrassed about. [^compatible]

[^compatible]: It's fully compatible with the current setups of 0.12.x series.

Static reflection
-----------------------

These tradeoffs can go away thanks to [Roave's BetterReflection](https://github.com/Roave/BetterReflection) library. It reimplements PHP's reflection API using AST (same technology PHPStan itself is based on). It parses all the files related to the analysis and provides all the same information but without the aforementioned downsides.

*[AST]: Abstract Syntax Tree

Setting up autoloading for all the parsed sources is no longer necessary - BetterReflection simply looks at all the files in analysed paths to discover the desired symbols. PHPStan will be able to analyse most codebases without any additional configuration. I wrote a [handful guide](/user-guide/discovering-symbols) for the rest.

Such a deep integration of a technology inevitably leads to finding bugs in that technology. In the true spirit of open source, we improved BetterReflection a ton in collaboration with Marco [@Ocramius](https://twitter.com/Ocramius) and Jarda [@kukulich](https://twitter.com/kukulich). [^lovemyjob]

[^lovemyjob]: I love my job!

Unlocking new use cases
-----------------------

Files that mix declarations and side-effects are no longer a problem. Previous versions of PHPStan would work only with pure-OOP codebases. But the latest one will also work with code that looks like it was written a decade or two ago.

*[OOP]: Object-Oriented Programming

To prove this, I tested PHPStan on [Adminer](https://github.com/vrana/adminer) which is an awesome database manager from users' perspective, but not so much when you look at [its code](https://github.com/vrana/adminer/blob/2021ea8fd7046aedfc2c9f9672baaa747ba00485/adminer/include/connect.inc.php).

PHPStan 0.12.25 (released a month ago) reported 1,945 errors on level 0 - a totally broken analysis full of unknown symbols and misunderstood code. After the static reflection has been deployed and it was able to analyse such files, it brought the number of errors down to 18! And all of them were actionable and relevant.

<blockquote class="twitter-tweet tw-align-center" data-conversation="none" data-lang="en" data-dnt="true"><p lang="en" dir="ltr">Wow! It should simplify integration with non-PSR1-compliant legacy code a lot.</p>&mdash; Sergei Morozov (@srgmrzv) <a href="https://twitter.com/srgmrzv/status/1263825391287848960?ref_src=twsrc%5Etfw">May 22, 2020</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

Hybrids are better for the environment
-----------------------

With all that said, PHPStan can still use runtime reflection where appropriate. So it's hybrid reflection, not entirely static. Static reflection is hungrier for CPU time and RAM allocation, so if PHPStan concludes that a class can be autoloaded, it will use runtime reflection for that one, and static reflection for the rest.

-----------------------

If you have tried PHPStan before and it didn't work on your project, now is the time to give it a chance again. If you're already using PHPStan, you can definitely simplify your configuration. I'm looking forward to your feedback!
