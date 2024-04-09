---
title: Backward Compatibility Promise
---

There are multiple aspects to backward compatibility in case of PHPStan. This article talks about what users can expect when upgrading minor versions and how PHPStan honors [semantic versioning](https://semver.org/).

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

To read about backward compatibility from the point of view of developing PHPStan extensions, <a href="/developing-extensions/backward-compatibility-promise">head here</a>.

</div>

In context of PHPStan, upgrading a minor version means increasing `y` in `0.x.y` before 1.0, and increasing `y` in `x.y.z` after 1.0 release. Upgrading a patch version means increasing `z`.

CLI arguments and options
--------------------

[Their meaning](/user-guide/command-line-usage) does not change in minor/patch versions. No options are removed. New options can be added.

Configuration options
--------------------

The meaning of existing [configuration options](/config-reference) and the file format does not change in minor/patch versions. No options are removed. New options can be added.

Checks performed on analysed code
--------------------

The library of performed rules on analysed code, and the meaning of [rule levels](/user-guide/rule-levels) does not change in minor/patch versions. This means that PHPStan will not look for new categories of errors in a minor/patch version. New rules are added only in major versions, and also as part of [bleeding edge](/blog/what-is-bleeding-edge) which is an opt-in preview of the next major version.

Type inference capabilities
--------------------

As bugs get fixed, PHPStan gets smarter, and figures out more precise information about existing code. This can lead to unavoidable changes in understanding analysed code, and to old errors stopped being reported, or new errors started being reported.

The nature of static analyser is that the output can change even in minor/patch version, because any single change leads to code being understood a bit differently, and therefore breaking someone's build.

Minimizing impact of type inference changes
--------------------

When PHPStan stops reporting a false-positive, it reports an [ignored error](/user-guide/ignoring-errors) no longer being present. So half of the reasons why PHPStan can fail after minor/patch version upgrade can be prevented by setting [`reportUnmatchedIgnoredErrors`](/user-guide/ignoring-errors#reporting-unused-ignores) to `false`.

Users usually welcome when PHPStan gets smarter so using the [caret version range](https://getcomposer.org/doc/articles/versions.md#caret-version-range-) (like `"phpstan/phpstan": "^1.10.60"`) in `composer.json` is encouraged. In combination with [committed `composer.lock`](https://www.amitmerchant.com/why-you-should-always-commit-the-composer-lock-file/) your build will be stable anyway, but you'll get new PHPStan versions when you run `composer update`.

If you value build stability over everything else, and you don't have a committed `composer.lock` file [^oss], lock PHPStan version to a specific patch version: `"phpstan/phpstan": "1.10.60"` and use [Dependabot](https://docs.github.com/en/code-security/supply-chain-security/keeping-your-dependencies-updated-automatically) or a similar solution to get notified about new releases and perform automatic upgrades.

[^oss]: Which is typical for open-source projects that need to stay compatible with a range of dependencies versions.
