---
title: "Introducing PHPStan Pro – Save Your Keystrokes and Get More Productive!"
date: 2020-09-29
tags: releases
ogImage: /images/phpstan-pro-twitter-card.png
pageType: pro
---

I'm really excited to show everyone what I've been working on for the past 9 months. It's a new product aimed to enhance user experience when using PHPStan. I want to challenge a common presumption that developer tools like PHPStan are usually constrained to CLI, limiting their visual side, and possible interactions.

With PHPStan Pro you'll be able to sift through reported errors and fix them much faster. There are three parts that will enable that:

Web UI for browsing errors
============

When you launch PHPStan Pro by adding `--pro` flag to the `analyse` command, it will automatically open your web browser with its user interface:

<video width="688" height="574" class="mb-8 rounded-lg border border-gray-300" autoplay muted loop playsinline>
  <source src="/images/phpstan-pro-1.mp4" type="video/mp4">
</video>

Instead of scrolling through a textual output of errors on the command line, you'll get a beautiful interactive UI that allows you to go back and forth between files, and see the surrounding code. This is especially nice if you have a large number of errors.

<p>
Developers are also often slowed down by having to pull up the error location in their IDE manually – by searching for the file name and jumping to a specific line. PHPStan Pro will do that for you when you click this icon:

<svg class="inline h-6 w-6" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M4 1h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2h1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1H2a2 2 0 0 1 2-2z"/>
  <path d="M2 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H2zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H2zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H2z"/>
  <path fill-rule="evenodd" d="M8.646 5.646a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1 0 .708l-2 2a.5.5 0 0 1-.708-.708L10.293 8 8.646 6.354a.5.5 0 0 1 0-.708zm-1.292 0a.5.5 0 0 0-.708 0l-2 2a.5.5 0 0 0 0 .708l2 2a.5.5 0 0 0 .708-.708L5.707 8l1.647-1.646a.5.5 0 0 0 0-.708z"/>
</svg>

What a killer feature!

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

What's important to mention in this day and age of privacy nightmares and data leaks is that PHPStan Pro runs locally on the client and no information about the analysed code is transmitted to a server.

</div>

</p>

Continuous analysis (watch mode)
============

Once you fix all the found errors and achieve Error Zero, PHPStan Pro keeps running in the background, watching files, and re-running analysis each time it detects a change. There's a loading indicator in the lower right corner for that:

<video width="267" height="159" class="mb-8 rounded-lg border border-gray-300 mx-auto" autoplay muted loop playsinline>
  <source src="/images/phpstan-pro-loader.mp4" type="video/mp4">
</video>

Continuous analysis reflects and supports my preferred workflow when developing applications: when I realize that I have to pass an additional new value through multiple layers of a codebase, I start by changing method signatures – renaming them, adding parameters, changing return typehints. After that, I run PHPStan which essentially gives me a todo list of places to fix. But running PHPStan several times a minute to get instant feedback can become tedious. Having a persistent window that refreshes automatically and gives me an up-to-date view of the project is much nicer.

Interactive fixer
============

During workshops and conference talks I often get questions about automatic fixing of found errors. I had been refusing this idea for a long time because there isn't a sole obvious fix for most of them. For example, if you have access to an undefined property, what should be the fix?

1. Define the property in the class.
2. Define the property as `@property` PHPDoc above the class.
3. Renaming the accessed property to a one that exists.

Errors like this can't be fixed automatically. But one day I had a revelation – we can give the user a choice which fix should be applied! That's why this feature is called an **interactive** fixer.

There's also the potential to educate users and give them the right fix according to best practices, instead of letting them shoot themselves in the foot with something that suppresses the error to the [letter of the rule but not in the spirit of the rule](https://en.wikipedia.org/wiki/Letter_and_spirit_of_the_law).

Fixer suggestions also show how they'll affect the result. It's not always so clear-cut that a fix would only solve the error it's supposed to fix, it can also introduce new errors and that's a good thing, although it might not sound like it.

<img src="/images/phpstan-pro-delta.png" width="600" height="403" class="mb-8 rounded-lg border border-gray-300 mx-auto">

If you for example fix a missing return typehint of a function that returns integers by adding native `: int` or `@return int` PHPDoc, PHPStan will be able to point out all the places where the returned value is used as a string.

And of course, some errors have obvious fixes, so they can be applied with a single click without having to choose from multiple alternatives.

Pricing
===============

I want to address the [elephpant](https://elephpant.me/) in the room. As the name suggests, PHPStan Pro is a paid add-on for the free open-source PHPStan project.

I believe that successful open-source projects should have a business model behind them so that their creators can justify spending time on their development.

As PHPStan gets more popular and more complex, it takes a lot of time every day to just stay on top of new issues, let alone work on something new and innovative. That's why I'm creating something that people would only get if they pay for it, so I can work on PHPStan full-time and maybe even hire other developers to help me out. I'm in this for the long haul.

By paying for PHPStan Pro, you're supporting the development of both open-source PHPStan and PHPStan Pro. The incentives for me to continue working on the free open-source PHPStan are greatly aligned – by improving it, it advances and improves the paid add-on as well. The advancements that happened this year are evidence of this – [result cache, parallel analysis](/blog/from-minutes-to-seconds-massive-performance-gains-in-phpstan), and [static reflection](/blog/zero-config-analysis-with-static-reflection) were all done in the anticipation of PHPStan Pro, but users have already been benefiting from them for months.

PHPStan Pro is priced very moderately because I'm betting that a large portion of its user base will find it appealing.

If you're working alone as a freelancer or an independent developer, PHPStan will keep you company and help you every day for **€7/month**.

If you're a team of developers, PHPStan Pro costs **€70/month**, and as many as 25&nbsp;people can join a single team. That's less than a day worth of salary of a single developer where I come from. [^salary] Great value for teams that use PHPStan to find and prevent bugs from reaching production!

[^salary]: I'm aware it can be as much as a day, and as little as an hour elsewhere in the world.

If you decide to pay annually, you'll get PHPStan Pro for 12&nbsp;months for the price of 10&nbsp;months – that's **€70/year** for individual developers, **€700/year** for teams.

There's a 30-day free trial period for all the plans. And there's no limit on the number of projects - you can run PHPStan Pro on any code you have on your computer.

Start today!
===============

Make sure you have at least PHPStan 0.12.45 and launch PHPStan Pro by running the usual `analyse` command with one of these options:

* `--pro`
* `--fix`
* `--watch`

They all do the same thing so you can choose which one you like. PHPStan will download the PHPStan Pro PHAR file and open your web browser pointing to a locally hosted website. After that, you can follow the on-screen instructions and create an account.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

If you want to create the account and set up payments without running PHPStan in the CLI right now, head to <strong><a href="https://account.phpstan.com/">account.phpstan.com</a></strong>.

</div>

Keep in mind right now it's really early in PHPStan Pro's development life cycle. If you were a PHPStan user right after its initial release in December 2016, you'd remember it also didn’t do a lot but the main idea was there, ready to blossom.

I have plans on what to do next for both PHPStan and PHPStan Pro, but I rely on you to tell me what you want the most. Don't hesitate to use the built-in "Send feedback" form to tell me what you think.

Thank you.
