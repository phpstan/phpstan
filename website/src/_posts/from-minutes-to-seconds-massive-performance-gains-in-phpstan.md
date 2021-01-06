---
title: "From Minutes to Seconds: Massive Performance Gains in PHPStan"
date: 2020-03-01
tags: releases
ogImage: /images/f1-car.jpg
---

![F1 Car](/images/f1-car.jpg)

Strongly-typed object-oriented code helps me tremendously during refactoring. When I realize I need to pass more information from one place to another, I usually take my first step by changing a return typehint, or adding a required parameter to a method. Running PHPStan after making these changes tells me about all the code I need to fix. So I run it many times an hour during my workday. I don’t even check the web app in the browser until PHPStan’s result is green.

To encourage these types of workflows and to generally speed up feedback, I made massive improvements to its performance in [the latest release](https://github.com/phpstan/phpstan/releases/tag/0.12.12). You can now run PHPStan comfortably as part of Git hooks or manually on your machine, not just in CI pipelines when you decide to submit your branch for a code review at the end of the day. Even on huge codebases consisting of hundreds of thousands lines of code, **it finishes the analysis in seconds**!

How was this achieved?

## Parallel analysis

PHPStan now runs in multiple processes by default. The list of files to analyse is divided into small chunks which get processed by the same number of processes as the number of CPU cores you have. It works on all operating systems and doesn’t require any special PHP extension. **And it’s enabled by default.**

This feature was first released three weeks ago under an optional feature flag users had to turn on. The initial response from the early adopters was overwhelmingly positive:

<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

<blockquote class="twitter-tweet"><p lang="en" dir="ltr">That&#39;s awesome! <br>Runs smooth and uses all 16 cores.<br>Analysis of 2357 files in 12 seconds. Took minutes before.<br>&lt;3</p>&mdash; what does trump mean when he says words? (@alex_schnitzler) <a href="https://twitter.com/alex_schnitzler/status/1228372138173894663?ref_src=twsrc%5Etfw">February 14, 2020</a></blockquote>

<blockquote class="twitter-tweet"><p lang="en" dir="ltr">In a VM:<br>- app1: from ~22sec to ~12sec<br>- app2: from ~7sec to ~4sec<br><br>In GitHub Actions CI (we run Symfony `bin/console cache:clear` and PHPStan in the same step 😟):<br>- app1: from ~43sec to ~26sec<br>- app2: from ~13sec to ~10sec<br><br>that&#39;s nice :)</p>&mdash; Hugo Alliaume (Kocal) ☣️ (@HugoAlliaume) <a href="https://twitter.com/HugoAlliaume/status/1228231636820430848?ref_src=twsrc%5Etfw">February 14, 2020</a></blockquote>

<blockquote class="twitter-tweet"><p lang="en" dir="ltr">Pretty awesome! Thanks! roughly down from 15 to 5 minutes. 🥳</p>&mdash; Christopher Hertel (@el_stoffel) <a href="https://twitter.com/el_stoffel/status/1227994935925837824?ref_src=twsrc%5Etfw">February 13, 2020</a></blockquote>

<blockquote class="twitter-tweet"><p lang="en" dir="ltr">Well done!!! It’s 4-times faster now!!!<br>analysis takes 15 seconds instead of 1 minute with previous version.</p>&mdash; Lukáš Břečka (@ftipalek) <a href="https://twitter.com/ftipalek/status/1227988180693061639?ref_src=twsrc%5Etfw">February 13, 2020</a></blockquote>

---

## Result cache

Even with the improvements achieved by parallel analysis, there was one more opportunity to achieve an order-of-magnitude speed-up.

One idea that’s easy to come up with is to analyse only changed files. You might have seen a suggestion to run PHPStan like this:

```bash
# Don't do this!
git diff --name-only origin/master..HEAD -- *.php | xargs vendor/bin/phpstan analyse
```

First problem with this approach is that by changing one file, we can break a different file. For example if we add a method to an interface we need to reanalyse all the classes that implement the interface. We might miss out on real errors with this naïve approach.

Instead, we need to build a tree of dependencies between the files. So when we detect that `A.php` was changed, we analyse `A.php`+ all the files calling or otherwise referencing all the symbols in `A.php`.

Second problem is that even if we successfully mark all the right files that need to be reanalysed, we won’t see the errors from the remaining files. In order for users to not even notice they’re really analysing only a subset of files, we need to cache the list of errors from past analyses.

Hence the name: **result cache**.

We’ve been testing this feature at my day job @ [Slevomat](https://github.com/slevomat) for the past year and it’s really solid. **It’s now also enabled by default** in the [latest PHPStan release](https://github.com/phpstan/phpstan/releases/tag/0.12.12).

The cache gets invalidated, and PHPStan runs on all files again, if one or more of these items changed since the most recent analysis:

- PHPStan version
- PHP version
- Loaded PHP extensions
- composer.lock contents (3ʳᵈ party dependencies were updated)
- Contents of the project’s PHPStan configuration file
- Contents of included stub files for overriding 3ʳᵈ party PHPDocs
- Used rule level (using `--level` or `-l`on the command line)
- Analysed paths

Especially the last item now makes the practice using `git diff` counter-productive. The list of analysed paths needs to remain stable for the result cache to be used. So the **best practice is to always run PHPStan on the whole project**.

If you want to run PHPStan without the result cache being used for some reason, you can use the new `clear-result-cache` command:

```bash
vendor/bin/phpstan clear-result-cache -c phpstan.neon && \
vendor/bin/phpstan analyse -c phpstan.neon -l 8 src/ tests/
```

I’m really excited about these improvements and can’t wait till you try them out! I’m looking forward to your feedback.
