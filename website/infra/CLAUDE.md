# phpstan.org website infrastructure (CDK)

AWS CDK app (TypeScript) that defines the production infra for [phpstan.org](https://phpstan.org):
S3 origin, CloudFront distribution, edge function, security headers policy,
ACM cert, Route 53 records, and the IAM roles that GitHub Actions assumes via OIDC.

See `README.md` for the bootstrap and cutover runbook. See `../CLAUDE.md` for
the parent website project conventions.

## Stacks

Both stacks deploy to `us-east-1` (required for CloudFront + ACM).

| Stack | Defined in | Resources |
| --- | --- | --- |
| `PhpstanOrgGithubOidc` | `lib/github-oidc-stack.ts` | GitHub OIDC provider + `phpstan-org-infra-deploy` role (used by `website-infra.yml` to deploy this CDK app) |
| `PhpstanOrgWebsite` | `lib/website-stack.ts` | S3 bucket (OAC, private, versioned), CloudFront distribution carrying all three aliases (apex + www + `new.phpstan.org`), CF Function 2.0, Response Headers Policy, ACM cert (DNS-validated, covers all 3 hostnames), the `new.phpstan.org` Route 53 record, and `phpstan-org-website-deploy` role (used by `website.yml` to sync content + invalidate) |

`bin/infra.ts` is the CDK app entrypoint. It hard-codes the account/region/repo/zone constants. No runtime flags.

## Out-of-band resources

The apex (`phpstan.org`) and www (`www.phpstan.org`) Route 53 records are **not** managed by CDK. They were created during the initial cutover from the legacy distributions via raw `change-resource-record-sets` calls, and CloudFormation cannot UPSERT a record that already exists outside its own state. They are managed manually via the AWS Console or CLI. The `new.phpstan.org` record is the only Route 53 record CDK touches.

If you change the new distribution's CloudFront domain (e.g., a recreate), you must also update the apex/www Route 53 records to point at the new domain — CDK will not do it for you. The README has the rollback runbook with explicit `change-resource-record-sets` payloads.

## Edge function

`functions/phpstan-org-edge.js` is the CloudFront Function 2.0 source, written
as plain ES5-ish JS so it runs in the CF runtime unchanged. It replaces three
pieces of legacy infrastructure:

1. The viewer-response CF Function `secure-headers-response` — its security headers moved to the Response Headers Policy in `website-stack.ts`. Not handled by the function.
2. The viewer-request CF Function `phpstan-org-viewer-request` — `.html` strip → 301 (was 302).
3. The origin-request Lambda@Edge `web-phpstan-prg-rewrite-url` — `/r/*` → `/try.html`, `/error-identifiers/*` → `.html` append, clean-URL → `.html` append.

It also takes over the www→apex 301, which was previously a second CloudFront
distribution (`E3VJ14QANBNGO9`).

The file uses a `typeof module !== 'undefined'` guard at the bottom to export
`handler` for Node-based unit tests; in the CF runtime there is no `module`
global so the export is silently skipped.

### Behavior to preserve when editing

The function runs on **viewer-request**, so its rewrites form the cache key. The
order matters:

1. `host === 'www.phpstan.org'` → 301 to `https://phpstan.org${uri}${qs}` (host check first so .html strip never fires for www).
2. `uri.endsWith('.html')` → 301 to `uri` without `.html` (clean-URL canonical form).
3. `uri.startsWith('/r/')` → rewrite to `/try.html` (playground short links — all `/r/*` resolve to the same page).
4. `uri.startsWith('/error-identifiers/')` and not `.js`/`.css` → append `.html`.
5. Last path segment has no `.` and is non-empty → append `.html`.
6. Trailing slash on a non-root URI (`uri.length > 1`) → 301 to the slash-less canonical form (`/blog/foo/` → `/blog/foo`, which then gets `.html` appended on the follow-up request). The root `/` is left alone — CloudFront's `defaultRootObject: 'index.html'` handles it *after* the function runs, and redirecting `/` would loop.

Querystring is preserved on the 301 redirects via the `formatQuerystring` helper, which handles both single-value and multi-value params.

## Project layout

```
website/infra/
├── bin/infra.ts              # CDK app entrypoint — wires both stacks
├── lib/
│   ├── github-oidc-stack.ts  # OIDC provider + infra-deploy role
│   └── website-stack.ts      # everything that serves traffic
├── functions/
│   └── phpstan-org-edge.js   # CloudFront Function 2.0 source
├── test/
│   ├── phpstan-org-edge.test.ts   # Vitest: 22 cases on the edge function
│   └── website-stack.test.ts      # Vitest: CDK assertions on the synth template
├── cdk.json                  # CDK config + context (incl. productionAliases)
├── package.json
├── tsconfig.json
├── vitest.config.ts
├── README.md                 # bootstrap + cutover runbook (human-facing)
└── CLAUDE.md                 # this file
```

## Conventions

- **Tabs for indentation** in TS, JSON, and JS files (matches the parent website repo).
- **2-space indent** for YAML workflows (matches the existing `.github/workflows/` style).
- **Pin GitHub Actions to commit SHAs** with the version in a comment — matches the existing repo style and what `step-security/harden-runner` audits.
- **No `module.exports` / ESM imports in `functions/*.js`** — they run in the CloudFront Function runtime, not Node. The only allowed exception is the `typeof module` guard for unit-test interop.
- Resource IDs in CDK use **PascalCase** (CDK convention). Resource *names* (`bucketName`, `roleName`, `functionName`, `responseHeadersPolicyName`) use **kebab-case** with the `phpstan-org-` prefix so they're easy to spot in the console alongside other workloads in this account.
- Output exports use the `PhpstanOrg…` prefix (`PhpstanOrgWebsiteBucketName`, etc.) so other stacks (or scripts) can reference them by name.

## Commands

```sh
npm ci             # install (run after pulling)
npm run check      # tsc --noEmit
npm test           # vitest run — 32 tests (edge function + stack assertions)
npm run synth      # cdk synth --all (no AWS creds needed)
npm run diff       # cdk diff --all (needs AWS creds for the target account)
npm run deploy     # cdk deploy --all
```

`npm test` is the gate before any deploy — the CI workflow runs `check` + `test` + `synth` in a `test` job and blocks `diff` and `deploy` on it via `needs: test`.

## CI

`.github/workflows/website-infra.yml` triggers on PRs and pushes that touch
`website/infra/**` or the workflow file itself. It has three jobs:

1. `test` — `npm ci && npm run check && npm test && npx cdk synth --all` (no AWS creds).
2. `diff` (needs: `test`) — assumes `INFRA_DEPLOY_ROLE_ARN` via OIDC, runs `cdk diff --all`, posts a sticky PR comment with the diff.
3. `deploy` (needs: `[test, diff]`, only on push to `2.3.x`) — assumes the same role, runs `cdk deploy --all --require-approval never`.

The deploy is gated on **both** test and diff succeeding, so a broken edge
function unit test or a CDK synth error blocks the deploy.

## When to edit what

- **Changing edge logic** (URL rewrites, redirects, headers in JS) → `functions/phpstan-org-edge.js` + add a test case to `test/phpstan-org-edge.test.ts`.
- **Changing security headers** → `lib/website-stack.ts` (the `responseHeadersPolicy` block), not the function.
- **Adding cache behaviors, origins, or new functions** → `lib/website-stack.ts`. If the change is non-obvious, extend `test/website-stack.test.ts` with an assertion.
- **Changing the trust policy** (e.g. allowing another branch to deploy) → `lib/github-oidc-stack.ts`.
- **Toggling test/production mode** → `cdk.json` `context.productionAliases`. This is the cutover knob; see `README.md` for the full sequence.

## What lives elsewhere

- The website source and build pipeline (Eleventy + Vite + Tailwind) — `../` (see `../CLAUDE.md`).
- The S3 sync + CloudFront invalidation that publishes the website itself — `.github/workflows/website.yml` (uses the `phpstan-org-website-deploy` role from this stack).
- The playground API and runner — `../../playground-api/`, `../../playground-runner/` (separate Serverless Framework stacks, not in scope here).
- The `apiref.phpstan.org` distribution and its CF Function — not modeled here; same modernization pattern would apply but is a separate piece of work.
