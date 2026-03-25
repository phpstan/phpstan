# PHPStan Website (phpstan.org)

Static website for PHPStan - PHP Static Analysis Tool.

## Tech Stack

- **Static site generator:** Eleventy (11ty) v2
- **Bundler:** Vite
- **Styling:** TailwindCSS v3 with PostCSS and autoprefixer
- **Templating:** Nunjucks (11ty layouts/includes), Markdown (content pages, blog posts)
- **Client-side JS:** Knockout.js, TypeScript (ES6 target), jQuery
- **Code editor:** CodeMirror 6 (playground)
- **Syntax highlighting:** Prism.js (build-time via 11ty plugin)
- **Search:** Algolia DocSearch
- **Markdown:** markdown-it with plugins (anchors, footnotes, abbreviations, attrs)

## Build Pipeline

The build is a two-stage process:

1. **`npm run build:11ty`** — Eleventy compiles templates and content from `src/` into `tmp/`
2. **`npm run build:vite`** — Vite bundles and optimizes everything from `tmp/` into `dist/`

The final deployable output is the `dist/` directory.

### Key Directories

- `src/` — Source files (templates, content, styles, TypeScript)
- `tmp/` — Intermediate build output from Eleventy (gitignored)
- `dist/` — Final build output from Vite (gitignored)

### Scripts

- `npm run build` — Full production build (11ty + Vite)
- `npm run watch` — Dev mode (runs 11ty watch + Vite dev server in parallel)
- `npm run check` — TypeScript type-check + ESLint
- `npm run fix` — ESLint auto-fix
- `npm run test:visual` — Run Playwright visual regression tests
- `npm run test:visual:update` — Update visual test snapshots

### Deployment

Built on GitHub Actions, deployed to AWS S3 (`web-phpstan.org` bucket) with CloudFront CDN invalidation. Triggered by pushes to `2.1.x` branch affecting `website/**`.

## Source Structure

- `src/_layouts/` — Nunjucks page layouts
- `src/_includes/` — Nunjucks partial templates
- `src/_data/` — 11ty global data files
- `src/_posts/` — Blog posts (Markdown)
- `src/js/` — TypeScript source files
- `src/user-guide/` — User guide documentation (Markdown)
- `src/developing-extensions/` — Extension development docs
- `src/writing-php-code/` — PHP writing guide
- `src/app.pcss` — Main stylesheet (Tailwind + custom CSS)
- `visual-tests/` — Playwright visual regression tests and baseline screenshots

## Eleventy Configuration

Configured in `.eleventy.js`:
- Input: `src/`, Output: `tmp/`, Layouts dir: `_layouts`
- Plugins: syntax highlighting (with diff support), RSS, rendering
- Custom filters for dates (Luxon), reading time, path trimming
- Mermaid diagram rendering with file-based caching
- Social image generation via `capture-website`

## Verifying Changes

Run `npm run test:visual` to execute Playwright visual regression tests. These tests compare screenshots against baseline snapshots stored in `visual-tests/`. If changes are intentional, update snapshots with `npm run test:visual:update`.

When making changes, build with `npm run build` and compare the `dist/` directory before and after. Some differences are expected (hashed filenames, minor whitespace) but:
- No whole sections of generated HTML should disappear
- The website should still look the same visually
- Check that pages render correctly, not just that the build succeeds

### Playwright Screenshots for UI Development

When working on visual UI changes (especially the playground), use Playwright to take screenshots and iterate on the design. The dev server runs at `http://localhost:5173` (started via `npm run watch`). Use Node.js scripts with `@playwright/test` (installed in the project) like this:

```js
node -e "
const { chromium } = require('@playwright/test');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
  await page.goto('http://localhost:5173/try');
  await page.waitForTimeout(2000);
  // Interact with the page (click buttons, open dialogs, etc.)
  await page.click('button:has-text(\"Options\")');
  await page.waitForTimeout(500);
  await page.screenshot({ path: '/tmp/screenshot.png' });
  await browser.close();
})();
"
```

Also check the browser console for errors:

```js
page.on('console', msg => { if (msg.type() === 'error') console.log(msg.text()); });
page.on('pageerror', err => console.log(err.message));
```

Take screenshots at multiple viewport sizes (desktop 1280x900, mobile 375x812) to verify responsive layouts. Read the screenshot images to visually inspect the result, then adjust code and re-screenshot as needed.
