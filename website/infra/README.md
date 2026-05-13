# phpstan.org website infrastructure (CDK)

CDK app that defines the AWS infrastructure for [phpstan.org](https://phpstan.org):
the private S3 bucket, the CloudFront distribution, the edge function for URL
rewriting, the response headers policy, the ACM cert, the Route 53 records, and
the IAM roles assumed by GitHub Actions via OIDC.

See `../CLAUDE.md` for the parent website project conventions.

## Stacks

| Stack | Resources |
| --- | --- |
| `PhpstanOrgGithubOidc` | GitHub OIDC provider + `phpstan-org-infra-deploy` role (used by this workflow) |
| `PhpstanOrgWebsite` | S3 bucket (OAC), CloudFront distribution, CF Function 2.0, Response Headers Policy, ACM cert, Route 53 records, `phpstan-org-website-deploy` role (used by `website.yml`) |

Region for both: `us-east-1` (required for CloudFront + ACM).

## The `productionAliases` flag

`PhpstanOrgWebsite` reads a CDK context flag `productionAliases` (default `false`):

- `false` (test mode): distribution carries only `new.phpstan.org`. The Route 53 records point this alias at the new distribution. Use this to validate the new stack end-to-end while the live site still runs on the legacy distributions.
- `true` (production): distribution carries `phpstan.org` + `www.phpstan.org`. Route 53 alias records for both are managed here, replacing the legacy distributions.

The ACM cert covers all three names from day one, so flipping the flag does not reissue the cert.

## Local development

```sh
npm ci
npm run check     # tsc --noEmit
npm test          # vitest: edge function unit tests + stack assertions
npm run synth     # cdk synth --all
npm run diff      # cdk diff --all (needs AWS creds for the target account)
```

## First-time bootstrap (one-off, from a maintainer's laptop)

The deploy workflow assumes an IAM role that doesn't exist yet, and it deploys
via the CDK bootstrap roles which also don't exist yet. Both must be created
once from a workstation that already has admin AWS credentials.

```sh
# CDK bootstrap (creates cdk-hnb659fds-* roles used for subsequent deploys)
npx cdk bootstrap aws://928192134594/us-east-1

# Deploy the OIDC stack so the workflow can assume the deploy role on subsequent runs
npx cdk deploy PhpstanOrgGithubOidc
```

After this:
- Note the `InfraDeployRoleArn` output and set it as the `INFRA_DEPLOY_ROLE_ARN` repository variable on GitHub (Settings → Secrets and variables → Actions → Variables).
- From now on, every PR touching `website/infra/**` runs `cdk diff` and posts the diff to the PR; every merge to `2.2.x` runs `cdk deploy`.

## Cutover runbook (test → production)

The aim is to move phpstan.org and www.phpstan.org from the legacy distributions
(`E1W83FJ5FCYXPT`, `E3VJ14QANBNGO9`) to the new distribution defined here.

1. **Verify on the test alias.** With `productionAliases: false`, push to `2.2.x` and let the workflow deploy. After the cert validates and the distribution finishes deploying:
   - Sync `website/dist/` to `phpstan-org-web` (the new bucket) once, manually, so there is content to test against.
   - Verify `https://new.phpstan.org/` returns 200 with HSTS, X-Content-Type-Options, X-Frame-Options, Referrer-Policy and no X-XSS-Protection.
   - Verify `https://new.phpstan.org/user-guide/getting-started.html` returns a 301 to `/user-guide/getting-started`, which then returns 200.
   - Verify `https://new.phpstan.org/r/<some-id>` serves the playground page.
   - Verify `https://new.phpstan.org/error-identifiers/<some-id>` serves that page.
   - Use Playwright (see `website/CLAUDE.md`) for an end-to-end smoke pass.
2. **Free the apex and www aliases on the legacy distributions.** CloudFront refuses to attach the same alias to two distributions. From the AWS console (or CLI):
   ```sh
   # Edit E1W83FJ5FCYXPT and remove the `phpstan.org` alias.
   # Edit E3VJ14QANBNGO9 and remove the `www.phpstan.org` alias.
   ```
   Both legacy distributions stay live on their `*.cloudfront.net` domain while the alias is gone, but DNS still points to them at this stage so the live site briefly becomes unavailable. Time this step with the next one — they should be back-to-back.
3. **Flip the flag.** Open a PR that sets `"productionAliases": true` in `cdk.json` (`context` block). Merge it. The workflow will attach the prod aliases to the new distribution and rewrite the Route 53 records to point apex and www at the new distribution.
4. **Final website deploy.** Run `website.yml` (push or `workflow_dispatch`) to make sure the new bucket has fresh content.
5. **Verify production.** Re-run the checks from step 1 against `https://phpstan.org/` and `https://www.phpstan.org/`. Watch CloudWatch on both old distributions for ~15 minutes to confirm traffic has moved.

### Rollback

If something goes wrong after step 3:
1. Revert the `productionAliases` PR. The workflow re-deploys the new stack in test mode (aliases come off, Route 53 records flip back to `new.phpstan.org`).
2. Re-attach `phpstan.org` and `www.phpstan.org` aliases to `E1W83FJ5FCYXPT` and `E3VJ14QANBNGO9` respectively (manual, in the AWS console).
3. Update Route 53 to alias back to the old distributions.

The legacy distributions, the old buckets and the Lambda@Edge function are not
touched by CDK — they stay around as a fallback until the cleanup runbook below
is run.

## Cleanup runbook (after production is stable)

Run this only after the new infra has been carrying production traffic for a
sensible cooling-off period (a week is plenty for a low-risk static site).

- Delete CloudFront distribution `E1W83FJ5FCYXPT` (disable first, wait for it to deploy, then delete).
- Delete CloudFront distribution `E3VJ14QANBNGO9` (same).
- Delete CloudFront functions `phpstan-org-viewer-request` and `secure-headers-response`.
- Delete the Lambda@Edge function `web-phpstan-prg-rewrite-url` (replicas take ~hours to fully drain after dissociation — be patient).
- Delete the IAM role `web-phpstan-org-request-lambda-role`.
- Empty and delete S3 buckets `web-phpstan.org` and `web-www.phpstan.org`.
- Delete the legacy ACM cert `arn:aws:acm:us-east-1:928192134594:certificate/5c906d85-3885-4a8c-af99-27c9fee23c33` if it's no longer in use (it is also currently bound to legacy distribution E3VJ14QANBNGO9 — only delete after that distribution is gone).
