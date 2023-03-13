---
title: "Website Improvement: Social Post Preview Image"
date: 2023-03-13
tags: other
---

PHPStan's website is statically generated thanks to this stack:

* [Eleventy](https://www.11ty.dev/) - think Jekyll, but for the modern JavaScript age
* [Parcel](https://parceljs.org/) - my favourite bundler [^parcel]

[^parcel]: It might as well mean "I can't configure Webpack and I'm not ashamed!"

There's also plenty of other cool technologies like [TailwindCSS](https://tailwindcss.com/), [Markdown](https://daringfireball.net/projects/markdown/syntax), [Mermaid](https://mermaid.js.org/), and [Prism](https://prismjs.com/).

Being statically generated means I can host it on S3 & Cloudfront CDN and serve 120k+ views a month for literally pennies. But it makes implementing some features... not straightforward.

One day I noticed that [Tomáš Votruba](https://twitter.com/VotrubaT) has nice custom previews for his articles when he shares them on Twitter.

<blockquote class="twitter-tweet" data-lang="en" data-dnt="true"><p lang="en" dir="ltr">Want to ensure your codebase is rock-solid? 💪 <br><br>Measure your type coverage and improve your code&#39;s reliability! 🚀 <a href="https://t.co/d3ZU1bXyek">https://t.co/d3ZU1bXyek</a></p>&mdash; Tomas Votruba (@VotrubaT) <a href="https://twitter.com/VotrubaT/status/1634863819896901638?ref_src=twsrc%5Etfw">March 12, 2023</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

And I wanted the same thing! I checked out some Eleventy plugins, but didn't fall in love with any of them. I wanted to define how the image will look in HTML & TailwindCSS, screenshot it in a headless browser, and save the image to be uploaded alongside other regular website images.

So I've spent a few hours figuring it out, and designing the image. I love the result, both [the code that achieved that](https://github.com/phpstan/phpstan/commit/2304ef6326c4e29407e653f6be0cde04cc57b53e), and the look. See for yourself:

![Social Image Preview](/tmp/images/social-phpstan-1-10-comes-with-lie-detector.png)

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I’d really appreciate it!

