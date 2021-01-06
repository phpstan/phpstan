---
title: "How I Got From 0 to 1 000 Stars on GitHub in Three Months With My Open Source Side Project"
date: 2017-02-27
tags: other
---

Most developers have side projects. That's how we try out new things or make something that we miss on the market or in our dev stack. But most side projects end up unfinished and never actually see the light of day. And even if a developer builds up the courage to show his work to the public, he quickly finds out that just publishing a repository doesn't actually bring the masses to his doorstep.

At the beginning of last December, I released [PHPStan](https://phpstan.org/) — static analysis tool for PHP that focuses on finding bugs. The project gained a lot of traction resulting in currently over 1 300 stars on GitHub and more than 30 000 downloads on Packagist.

When I set out to create PHPStan, of course I haven't had the faintest idea if anyone else would be interested in it. But I was mainly scratching my own itch — I knew how to make a static analysis tool and I hoped I'd build something that would serve my needs much better than existing tools on the market. That should be your primary motivator — not to chase stars, likes and other popularity contents, but to create something for yourself that will make your job easier. There's a chance you will not be the only one that will find it useful.

In this article, I'd like to share with you what I did to make sure that the project doesn't end up in the dustbin of history. I will concentrate on open source software, but the following advice may as well apply to any creative endeavour.

## Build the hard stuff first

[Bikeshedding](https://en.wikipedia.org/wiki/Law_of_triviality) is real. It's too common to start a project by brainstorming on a project name or buying a domain. I'd even advise against starting by designing your homepage, sign up and login screens. That's just procrastination and deferring the real work. You should start with implementing the core idea where the value of the project lies. That way, you find out whether the project is viable and can actually be implemented before worrying about its looks and nonessential details. You also start to get value from it faster.

## Ship early, ship often

Release the first version as soon as it’s useful. Don’t wait for it to be perfect. You don’t have to be satisifed with it. You’ll never be. But you should be eager for some feedback!

When you add a useful feature to your project, you don't have to wait for anything to release it or deploy it. It just has to work. The feedback loop with inputs from other people will tell you whether what you're building makes sense or whether you should take it in other directions. It's good to get that feedback as soon as possible.

## Serve market needs

There's nothing wrong with reinventing the wheel. When I create a new project, I set out its purpose at the beginning — either I want to learn some new technology, or I want to create something useful. So I make something boring that I did a thousand times before with exciting new tech, or I make something new and useful with something really familiar and solid. Either I want to learn a piece of technology or solve a problem. Doing both of those at the same time just slows me down and the result isn't ideal.

I’ve solved a real problem with PHPStan and that’s because I’ve been working with PHP for the past 12 years.

Decide what’s the purpose of your project right at the beginning and plan accordingly. If you’re solving a real-world problem, there might be other people interested in your solution.

Don’t forget about documentation. Describe the project’s purpose and everything the user needs to do to get from 0 to 60 mph.

Follow the best practices on open-source packages of your platform of choice. Use a continuous integration service like Travis CI to check every commit. Have and enforce a coding standard. Write unit tests. Make it as easy as possible for other people to contribute your project.

## Promote

Without marketing, even the best ones would starve. If you have a loyal following on Twitter, tell them about your project. If you don’t, it’s never too late to start building one.

It does not have to be just one tweet with an announcement. Share behind-the-scenes glimpses of how the project is developed. Share that you just had an awesome 6-hour uninterrupted working session. Celebrate important milestones. Be truly open about the development.

Start a blog. Think about where the target audience of your project lives and share the links with them. This includes, but doesn’t have to be limited to Reddit and popular community newsletters.

Give a talk about your project at the local user group. Submit a talk to a prestigious conference. Network with people at relevant events and tell them about your work.

## Ask for money

Yes. The money. It can really boost your motivation and confidence if people consider your project to be actually worth something. You can just have a PayPal Donate button at the top of your README, or set up a Kickstarter campaign.

## And most of all: Be nice!

As you already realized, having an open-source project is hardly only about writing code. It’s also about getting the word out there. But most importantly, it’s about people. As your project gets rolling, you will receive bug reports, pull requests, and opinions. It turns out that when people criticize something it’s because they care about it. Keep that in mind when you read reactions to your work.

I take pride in being responsive to all incoming feedback. Although I don’t fix each reported bug right away, I at least respond whether the bug report is valid or I need more information. Doing code reviews on GitHub is pretty easy [since the recent changes](https://github.com/blog/2291-introducing-review-requests). When someone feels like they didn’t receive a response they deserve, I [take the time and explain](https://github.com/phpstan/phpstan/issues/107#issuecomment-277455084) the problem more thoroughly.

Don’t forget to add and uphold a [code of conduct](http://contributor-covenant.org/) so that all contributors get the same treatment from the community.
