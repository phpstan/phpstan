#!/bin/bash
set -e

# Build the site with blog posts from 2026+ excluded for stable visual tests
FREEZE_BLOG_POSTS=1 npm run build

# Run visual tests, passing through any extra arguments (e.g. --update-snapshots)
playwright test --config=visual-tests/playwright.config.ts "$@"
