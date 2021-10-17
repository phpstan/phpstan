---
title: "Why you should always analyse the whole project"
date: 2021-05-16
tags: guides
---

Static analysis is a very complicated problem. People sometimes get and try out things that might seem like good ideas but in some contexts are rather counterproductive.

You might get an idea to run `git diff` and analyse only the files this command outputs. Or you might want to analyse only the file you're currently editing. One reason might be to speed up the analysis, another reason might be to perform checks only on files developers touch, because you don't want to deal with old bugs in old code. But the right solutions to both of these reasons are different, and I'm gonna tell you how and why.

Missing out on errors
------------------------

Analysing changed files only works in context of tools like PHP_CodeSniffer when there aren't any dependencies between files. [^pillars]

[^pillars]: More on differences between various static analysis tools [in my article](/blog/three-pillars-of-static-analysis-in-php).

But in PHPStan you can easily break an unchanged file by changing a different file. If you rename a method, or add a required argument, you need to analyse **all the files where this method is called**.

Fast by default
------------------------

Fortunately PHPStan already bears this in mind. It uses [the result cache](/user-guide/result-cache) by default so that subsequent analysis runs are much faster than the initial one. It encourages always analysing the whole project because changing the list of analysed paths between runs actually invalidates the whole result cache.

<video width="688" height="439" class="mb-8 rounded-lg border border-gray-300" autoplay muted loop playsinline>
  <source src="/tmp/images/result-cache.mp4" type="video/mp4">
</video>

Analysing traits
------------------------

Trait is analysed only when both the using class and the trait itself are in the analysed paths. So if you only analyse portion of the project, you might miss out on errors from traits too. [More on this topic »](/blog/how-phpstan-analyses-traits)

Care only about new errors? Use the baseline
------------------------

If you want to analyse only changed files with the goal of reporting only new errors because you don't care about the pre-existing ones, think again. It wouldn't work very well - when the developer edits a file, it would report 50 unrelated errors to that change that were already in the file.

Instead, use a solution built into PHPStan designated for this purpose. And that's the baseline. [Find out more about it »](/user-guide/baseline)

Analyse whole project with just `vendor/bin/phpstan`
------------------------

You can analyse your project just by running `vendor/bin/phpstan` if you satisfy the following conditions:

* You have `phpstan.neon` or `phpstan.neon.dist` in your current working directory
* This file contains the [`paths`](/config-reference#analysed-files) parameter to set a list of analysed paths
* This file contains the [`level`](/config-reference#rule-level) parameter to set the current rule level
