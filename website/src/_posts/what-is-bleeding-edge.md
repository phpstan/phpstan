---
title: "What is Bleeding Edge?"
date: 2021-04-03
tags: guides
---

When developing new features that don't fit in a [minor version](/user-guide/backward-compatibility-promise), or aren't expected to be released right away, software developers have two options what to do:

* Develop the feature in a feature branch
* Develop it in the main branch but have it turned off in the minor release

Feature branches have a lot of downsides in this context. The code has to live detached from the primary development for months, risking code rot and difficult merging and/or rebasing. Additionally, the code just sits there without gathering valuable feedback, so when it's released to all users at once, there might be problems coming from the lack of testing in the wild.

These problems are solved with feature toggles. The code is developed and released in a minor version, but turned off by default. People can opt in to test the new behaviour and file issues, so the features will be in a much better state when released to all users in the next major version.

Bleeding edge users are [often rewarded](https://backendtea.com/post/use-phpstan-bleeding-edge/) with a much more capable analysis much sooner than the rest.

You can opt in to bleeding edge with this section in your `phpstan.neon`:

```neon
includes:
	- phar://phpstan.phar/conf/bleedingEdge.neon
```

When a new feature is added to bleeding edge, it's referenced in the [release notes](https://github.com/phpstan/phpstan/releases/tag/0.12.79). All the current new features enabled in bleeding edge can be inspected by looking at the [configuration file](https://github.com/phpstan/phpstan-src/blob/1.12.x/conf/bleedingEdge.neon) in PHPStan's source code.
