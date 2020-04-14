---
title: "Launching New PHPStan's Website"
date: 2020-04-14
tags: releases
---

PHPStan's documentation had been an underinvested area for a long time. Users had to scout through release notes and Git commits to learn about the latest features, and to be able to use PHPStan to the fullest. There was a README on GitHub I wrote back in 2016 when PHPStan was first released, and it didn't change much since then. In contrast with PHPStan itself which after dozens of releases over three and a half years is a much more capable and helpful tool.

As the popularity of PHPStan rises, so does the need for documentation. I have good news - today, I'm settling all family business. Using PHPStan is now a level playing field. Learning about awesome features like [the baseline](/user-guide/baseline), [stub files](/user-guide/stub-files), or various available [PHPDoc types](/writing-php-code/phpdoc-types) is now accessible to everyone without having to commit much time and effort. There's a lot of new material to learn from for everyone. Even if you're an advanced PHPStan user, you still might find stuff you didn't know about.

Documentation sections
--------------------

The documentation is divided into four distinct sections:

[**User Guide**](/user-guide/getting-started) talks about how to get started with PHPStan, command line usage, rule levels, ignoring errors etc.

[**Config Reference**](/config-reference) explains all available configuration options.

[**Writing PHP Code**](/writing-php-code/phpdocs-basics) is about best practices on writing static analyser-friendly PHP code, and also about PHPDoc syntax and PHPDoc types.

And [**Developing Extensions**](/developing-extensions/extension-types) talks about extending PHPStan capabilities with custom extensions that take advantage of the abstract syntax tree and type inference.

I want to encourage collaboration in the documentation - each page has an "Edit this page on GitHub" link at the bottom so if anyone feels like they can improve it, it's really easy. As no software is ever truly done, the same applies to documentation. I plan to expand the above sections with more content in the future. Issues and support questions from the users are a great source of inspiration for that.

New playground
--------------------

There's also a [new playground](/) with refreshed design and functionality. The feedback is now realtime without the need to click "Analyse".

New blog
--------------------

Medium.com served me well since the beginning of PHPStan's life, but having the blog on my own domain and tech stack puts me back in control of a really important aspect of the project. You never know when Medium decides to show a user-hostile pop-up to your readers and I don't want to worry about that. I also transferred all of the important articles here.

I hope you like these changes. Giving this website some TLC was one of the items on my #RoadTo1.0 checklist. Yes, next version of PHPStan will be a major milestone and I want to make sure that all of the important pieces are there to support it. [^major]

*[TLC]: Tender Loving Care

[^major]: And no, I don't have a release date in mind yet.
