---
name: run-on-3v4l
description: Execute PHP code on 3v4l.org to test runtime behavior across multiple PHP versions. Use when you need to verify what PHP actually does with a piece of code.
argument-hint: <php-code-or-file>
disable-model-invocation: false
---

# Run PHP code on 3v4l.org

Execute PHP code on 3v4l.org to test actual runtime behavior across multiple PHP versions. This is critical — do NOT reason about what PHP would do, actually test it.

The code to run: `$ARGUMENTS`

## Step 1: Research the 3v4l.org submission form

Fetch the 3v4l.org homepage and examine the HTML form to understand how to submit code:

```bash
curl -s https://3v4l.org/ | head -200
```

Look for:
- The form action URL and HTTP method
- Required form fields (the code textarea name, any hidden fields)
- How the response redirects to the result page

## Step 2: Prepare executable PHP code

Take the PHP code and make it executable:
- If the code defines classes/functions but doesn't call them, add test calls that exercise the interesting code paths
- Add `var_dump()` or `echo` statements to show return values and types
- Wrap potentially-erroring code in try/catch if needed to see the actual behavior
- Make sure the code actually produces output that demonstrates the behavior

## Step 3: Submit to 3v4l.org

Submit the prepared code to 3v4l.org using the form submission mechanism you discovered in Step 1.

## Step 4: Fetch results

After submission, retrieve the results via the REST API:

```bash
curl -s -H 'Accept: application/json' 'https://3v4l.org/<short-id>'
```

Parse the JSON to see actual PHP output across different PHP versions.

## Step 5: Present results

Show:
- The 3v4l.org link for the user to view in browser
- A summary of the output across PHP versions
- Any differences in behavior between PHP versions
