---
title: "PHPStan Reports Different Errors Locally & in CI. What Should I Do?"
date: 2024-08-20
tags: guides
---

If you find that PHPStan reports different code on your machine and in continuous integration build system, here are the steps to follow to get rid of this problem.

Is it analysing the same code?
------------------

Make sure you're looking at the results of the same Git commit in CI as you have checked out locally, and that you have committed all the files in your working directory.

Run `git rev-parse HEAD` or look at `git log` to get the Git commit hash. CI systems usually print the current Git commit hash somewhere early in the build log.

Run `git status` to make sure there are no uncommitted changes you're running the analysis against.

Is it running the same PHP version?
------------------

Make sure to check that you're running the same PHP version locally and in CI. You can check that with `php -v`.


Require PHPStan in your Composer project
------------------

Do not run PHPStan installed somewhere globally in your operating system, nor locally, neither in CI. Install PHPStan in `composer.json` with your project dependencies with this command:

```bash
composer require --dev phpstan/phpstan
```

This way you will make sure everyone and everything is on the same PHPStan version - your machine, your teammates and most importantly, CI.

Make sure your `composer.lock` is committed in the Git repository
------------------

Commit and version your `composer.lock` file in Git to make sure your dependencies are locked and always the same until you update them.


Run `composer install`, not `composer update`
------------------

Even with `composer.lock` committed, make sure you run `composer install` to install dependencies in CI. Running `composer update` discards the contents of your `composer.lock` file and updates all dependencies. Which is something we don't want.


Run PHPStan's `diagnose` command to compare the environments
------------------

Since [release 1.11.8](https://github.com/phpstan/phpstan/releases/tag/1.11.8) PHPStan ships with a `diagnose` command. It prints useful information about its configuration and more. Do not forget to pass the correct [configuration file](/config-reference) and [rule level](/user-guide/rule-levels) when running the command.

The output looks like this:

<pre class="font-mono text-sm">
<span class="text-green-500">PHP runtime version:</span> 8.3.1
<span class="text-green-500">PHP version for analysis:</span> 8.3.99 (from config.platform.php in composer.json)

<span class="text-green-500">PHPStan version:</span> 1.11.11
<span class="text-green-500">PHPStan running from:</span>
/var/www/project/vendor/phpstan/phpstan

<span class="text-green-500">Extension installer:</span>
composer/pcre: 3.2.0
phpstan/phpstan-deprecation-rules: 1.2.0
phpstan/phpstan-doctrine: 1.4.8
phpstan/phpstan-nette: 1.3.5
phpstan/phpstan-phpunit: 1.4.0
phpstan/phpstan-strict-rules: 1.6.0
phpstan/phpstan-symfony: 1.4.6
phpstan/phpstan-webmozart-assert: 1.2.7
shipmonk/phpstan-rules: 3.1.0

<span class="text-green-500">Discovered Composer project root:</span>
/var/www/project

<span class="text-green-500">Doctrine's objectManagerLoader:</span> In use
Installed Doctrine packages:
doctrine/dbal: 3.8.6
doctrine/orm: 2.19.6
doctrine/common: 3.4.4
doctrine/collections: 2.2.2
doctrine/persistence: 3.3.3

<span class="text-green-500">Symfony's consoleApplicationLoader:</span> No
</pre>

Compare the output between your local machine and CI to find possible differences.

Check the config file path
------------------

Make sure you're running PHPStan `analyse` command with the same [configuration file](/config-reference). If you're passing a custom path with `-c`, make sure it's the same one as locally.

If you're letting PHPStan autodiscover `phpstan.neon` or `phpstan.neon.dist` file in the current working directory, it outputs this note at the beginning of the analysis:

<pre class="font-mono text-sm">
Note: Using configuration file /var/www/project/phpstan.neon.
</pre>

Make sure your local machine uses the same file as the CI environment.

Get rid of duplicate symbols
------------------

If you have two or more different class or function definitions with the exact same name, PHPStan does not guarantee which one it's going to give priority to. Your local machine [might discover](/user-guide/discovering-symbols) one definition first and your CI might discover a different definition first, leading to different analysis results.

Rename your classes and functions so that they are always unique to get rid of this problem.

Fix your custom non-deterministic autoloader
------------------

Sometimes the difference in results comes down to the order of files during the analysis. Because the CI environment might have a different number of CPU cores, the files are going to be analysed in different groups and in different order than on your local machine.

If the difference is about what classes are known and unknown to PHPStan, it's possible you're using a [custom autoloader](/user-guide/discovering-symbols#custom-autoloader) to discover them.

A following scenario can occur:

* You're referencing a class name in your code with wrong case. For example. Your class name is `AdminUser` but you have `adminUser` somewhere in your code when referencing the same class.
* On your local machine the first file that's analysed refers to the class with the correct name - `AdminUser`. That makes other occurrences even with the wrong case let the class to be successfully found by PHPStan.
* In CI the first file that's analysed refers to the class with an incorrect name - `adminUser`. Your custom autoloader should still find the class successfully but maybe has a bug. This makes PHPStan report "unknown class" error and it also makes it memoize that this class does not exist.
* When the second file that's analysed in CI refers correctly to `AdminUser`, it still makes PHPStan report an error about unknown class which is a different behaviour than you're experiencing locally.

To fix this problem, make your autoloader discover classes even with wrong case.

Open a discussion on PHPStan's GitHub
------------------

If none of the steps above helped you to get consistent results between running PHPStan locally and in CI, please [open a discussion on GitHub,](https://github.com/phpstan/phpstan/discussions/new?category=support) so we can help you figure out this problem.


---

Do you like PHPStan and use it every day? [**Consider sponsoring** further development of PHPStan on GitHub Sponsors and also **subscribe to PHPStan Pro**](/sponsor)! I’d really appreciate it!

