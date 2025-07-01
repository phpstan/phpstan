# Automated Broken Link Detection and Removal

This workflow automatically detects and removes broken links from the PHPStan website.

## How it works

1. **Link Detection**: The workflow uses the [Broken Links Crawler Action](https://github.com/ScholliYT/Broken-Links-Crawler-Action) to scan the PHPStan website for broken links.

2. **Smart URL Checking**: When broken links are detected, the workflow runs a Python script that:
   - Focuses on known problematic URL patterns (like StackOverflow, personal blogs, etc.)
   - Checks each potentially broken URL with proper retry logic
   - Identifies which URLs are actually inaccessible (404, 403, timeouts, etc.)

3. **Content Preservation**: For each broken link found:
   - Converts `[link text](broken-url)` → `link text`
   - Preserves the meaningful text content
   - Maintains document readability and flow
   - Cleans up any formatting issues

4. **Automated PR Creation**: Creates a pull request with:
   - Detailed description of what was changed
   - List of broken URLs that were removed
   - Files that were modified
   - Clear review guidelines

## When it runs

- **Weekly**: Every Monday at 9 AM UTC
- **On workflow changes**: When this workflow file is modified
- **Manual trigger**: Can be triggered manually via GitHub Actions
- **External trigger**: Via repository dispatch events

## Configuration

The workflow excludes certain URL patterns by default:
- `mailto:` links
- `https://twitter.com`
- `https://www.startupjobs.cz`
- `https://www.shopware.com`
- `https://github.com/phpstan` (internal PHPStan GitHub links)

## Known Problematic Patterns

The script specifically checks URLs matching these patterns that are known to break frequently:
- StackOverflow questions (often deleted or blocked)
- Personal blog posts (sites go offline)
- Medium articles (paywall/access issues)
- Blogspot sites (often abandoned)

## Review Process

When a PR is created:

1. **Verify the links are broken**: Check each removed URL manually
2. **Review content changes**: Ensure the text still makes sense without the links
3. **Check for missing context**: Make sure no important information was lost
4. **Merge when satisfied**: The changes should be safe since only broken links are removed

## Example Changes

Before:
```markdown
If you're not familiar with this [term](https://broken-link.com/article), it means...
```

After:
```markdown
If you're not familiar with this term, it means...
```

## Benefits

- **Improves user experience**: No more frustrating broken links
- **Maintains SEO**: Removes dead links that hurt search rankings
- **Saves maintenance time**: Automatically handles link rot
- **Preserves content**: Keeps useful text even when links break

## Troubleshooting

If the workflow creates a PR but you think some links shouldn't be removed:

1. Check if the URL is actually accessible
2. Consider if the link pattern should be excluded
3. You can always restore links in a follow-up commit
4. Update the exclusion patterns if needed

## Manual Execution

To test the link removal process locally:

```bash
# Install dependencies
pip install requests

# Run the link checker
python3 process_broken_links.py
```

The script will show what changes would be made without actually modifying files.
