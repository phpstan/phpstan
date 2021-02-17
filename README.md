<h1 align="center">PHPStan - PHP Static Analysis Tool</h1>

<p align="center">
	<img src="https://i.imgur.com/MOt7taM.png" alt="PHPStan" width="300" height="300">
</p>

<p align="center">
	<a href="https://github.com/phpstan/phpstan/actions"><img src="https://github.com/phpstan/phpstan/workflows/Build/badge.svg" alt="Build Status"></a>
	<a href="https://packagist.org/packages/phpstan/phpstan"><img src="https://poser.pugx.org/phpstan/phpstan/v/stable" alt="Latest Stable Version"></a>
	<a href="https://packagist.org/packages/phpstan/phpstan/stats"><img src="https://poser.pugx.org/phpstan/phpstan/downloads" alt="Total Downloads"></a>
	<a href="https://choosealicense.com/licenses/mit/"><img src="https://poser.pugx.org/phpstan/phpstan/license" alt="License"></a>
	<a href="https://phpstan.org/"><img src="https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat" alt="PHPStan Enabled"></a>
</p>

------

PHPStan focuses on finding errors in your code without actually running it. It catches whole classes of bugs
even before you write tests for the code. It moves PHP closer to compiled languages in the sense that the correctness of each line of the code
can be checked before you run the actual line.

**[Read more about PHPStan in an introductory article »](https://phpstan.org/blog/find-bugs-in-your-code-without-writing-tests)**

**[Try out PHPStan on the on-line playground! »](https://phpstan.org/)**

## Sponsors

<a href="https://coders.thecodingmachine.com/phpstan"><img src="https://i.imgur.com/kQhNOTP.png" alt="TheCodingMachine" width="247" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://packagist.com/?utm_source=phpstan&utm_medium=readme&utm_campaign=sponsorlogo"><img src="https://i.imgur.com/PmMC45f.png" alt="Private Packagist" width="326" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://musement.recruiterbox.com/jobs/f661d5d5676e4c578d289bcf2fb40b02"><img src="https://i.imgur.com/uw5rAlR.png" alt="Musement" width="247" height="49"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://blackfire.io/docs/introduction?utm_source=phpstan&utm_medium=github_readme&utm_campaign=logo"><img src="https://i.imgur.com/zR8rsqk.png" alt="Blackfire.io" width="254" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://www.intracto.com/"><img src="https://i.imgur.com/XRCDGZi.png" alt="Intracto" width="254" height="65"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://www.startupjobs.cz/startup/shipmonk"><img src="https://i.imgur.com/bAC47za.jpg" alt="ShipMonk" width="290" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://togetter.com/"><img src="https://i.imgur.com/x9n5cj3.png" alt="Togetter" width="283" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://join.rightcapital.com/?utm_source=phpstan&utm_medium=github&utm_campaign=sponsorship"><img src="https://i.imgur.com/EuIgI08.png" alt="RightCapital" width="283" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://www.contentkingapp.com/?ref=php-developer&utm_source=phpstan&utm_medium=referral&utm_campaign=sponsorship"><img src="https://i.imgur.com/0blm7ki.png" alt="ContentKing" width="283" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://zol.fr?utm_source=phpstan"><img src="https://i.imgur.com/dzDgd4s.png" alt="ZOL" width="283" height="64"></a>

## Documentation

All the documentation lives on the [phpstan.org website](https://phpstan.org/):

* [Getting Started & User Guide](https://phpstan.org/user-guide/getting-started)
* [Config Reference](https://phpstan.org/config-reference)
* [PHPDocs Basics](https://phpstan.org/writing-php-code/phpdocs-basics) & [PHPDoc Types](https://phpstan.org/writing-php-code/phpdoc-types)
* [Extension Library](https://phpstan.org/user-guide/extension-library)
* [Developing Extensions](https://phpstan.org/developing-extensions/extension-types)

## PHPStan Pro

PHPStan Pro is a paid add-on on top of open-source PHPStan Static Analysis Tool with these premium features:

* Web UI for browsing found errors, you can click and open your editor of choice on the offending line.
* Continuous analysis (watch mode): scans changed files in the background, refreshes the UI automatically.
* Interactive fixer: lets you choose the right fix for found errors :blush:

Try it on PHPStan 0.12.45 or later by running it with the `--pro` option. You can create an account either by following the on-screen instructions, or by visiting [account.phpstan.com](https://account.phpstan.com/).

After 30-day free trial period it costs 7 EUR for individuals monthly, 70 EUR for teams (up to 25 members). By paying for PHPStan Pro, you're supporting the development of open-source PHPStan.

You can read more about it on [PHPStan's website](https://phpstan.org/blog/introducing-phpstan-pro).

## Code of Conduct

This project adheres to a [Contributor Code of Conduct](https://github.com/phpstan/phpstan/blob/master/CODE_OF_CONDUCT.md). By participating in this project and its community, you are expected to uphold this code.

## Contributing

Any contributions are welcome. PHPStan's source code open to pull requests lives at [`phpstan/phpstan-src`](https://github.com/phpstan/phpstan-src).
