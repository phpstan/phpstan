---
title: "Making the Most of AI in Open Source"
date: 2026-11-20
tags: other
socialImage: /images/making-the-most-of-ai-in-open-source.png
---

Today I gave a talk at [TechMeetup Ostrava](https://techmeetup.cz/) about how AI changed the way I work on PHPStan. AI tools and trends change from week to week, so instead of tricks that expire with the next model, I tried to share lessons that should last.

The talk was in Czech, but I translated the slides to English. Every slide also ends with a short summary of what I said, so you don't need the recording to follow along:

1. **What I built with AI that wouldn't exist without it**: [documentation for all 1100+ error identifiers](/error-identifiers), and [PHPStan Turbo](/blog/phpstan-2-3-leap-in-performance), about 200 thousand lines of C++, even though I don't know C++.
2. **AI in everyday work on GitHub**: keeping the documentation in sync with the code, and `phpstan-bot` fixing bugs I assign to it.
3. **One big disaster that almost happened**: how a prompt injection in an issue could have stolen PHPStan's secrets, how I fixed it, and the GitHub Actions security traps that have nothing to do with AI.
4. **Mob Programming 2.0**: why I'd rather get a well-described issue than a generated pull request.
5. **Where humans are still irreplaceable**: taste, priorities, and the idea an agent doesn't come up with on its own.
6. **Take care of yourselves.**

<iframe src="https://ondrejmirtes.github.io/ai-v-open-source/en/#/1" title="Slides: Making the Most of AI in Open Source" class="block w-full aspect-video mb-6 rounded-lg border border-gray-300" allow="fullscreen; screen-wake-lock" allowfullscreen loading="lazy"></iframe>

Click into the slides and use the arrow keys, or the controls in their bottom left corner, which also go fullscreen. They're also available [on their own page](https://ondrejmirtes.github.io/ai-v-open-source/en/), and the [Czech original](https://ondrejmirtes.github.io/ai-v-open-source/) too.

If you're running AI agents in GitHub Actions, the third part is the one I'd read first.
