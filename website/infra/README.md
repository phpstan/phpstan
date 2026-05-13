# phpstan.org website infrastructure (CDK)

CDK app that defines the AWS infrastructure for [phpstan.org](https://phpstan.org):
the private S3 bucket, the CloudFront distribution, the edge function for URL
rewriting, the response headers policy, the ACM cert, the staging Route 53
record, and the IAM roles assumed by GitHub Actions via OIDC.

See `../CLAUDE.md` for the parent website project conventions.

## Stacks

| Stack | Resources |
| --- | --- |
| `PhpstanOrgGithubOidc` | GitHub OIDC provider + `phpstan-org-infra-deploy` role (used by `website-infra.yml`) |
| `PhpstanOrgWebsite` | S3 bucket (OAC), CloudFront distribution with all three aliases (apex + www + `new.phpstan.org`), CF Function 2.0, Response Headers Policy, ACM cert, the `new.phpstan.org` Route 53 record, and `phpstan-org-website-deploy` role (used by `website.yml`) |

Region for both: `us-east-1` (required for CloudFront + ACM).

## Out-of-band resources

The apex (`phpstan.org`) and www (`www.phpstan.org`) Route 53 records are **not** managed by CDK. They were created during the initial cutover from the legacy distributions via raw `change-resource-record-sets` calls, and CloudFormation can't UPSERT a record that already exists outside of its own state. They are managed manually via the AWS Console or CLI. The `new.phpstan.org` record is the only Route 53 record CDK touches.

If you ever need to bring those records under CDK management, use `cdk import` against the apex/www `AWS::Route53::RecordSet` resources after adding the corresponding constructs to `WebsiteStack`. That import flow is interactive and not worth the ceremony unless DNS records start drifting.

## Local development

```sh
npm ci
npm run check     # tsc --noEmit
npm test          # vitest: edge function unit tests + stack assertions
npm run synth     # cdk synth --all
npm run diff      # cdk diff --all (needs AWS creds for the target account)
```

## One-time bootstrap (already done)

These commands have been run once and don't need to be repeated unless the account is rebuilt from scratch:

```sh
# CDK bootstrap (creates cdk-hnb659fds-* roles used for subsequent deploys)
npx cdk bootstrap aws://928192134594/us-east-1

# Deploy the OIDC stack
npx cdk deploy PhpstanOrgGithubOidc
```

GitHub repo variables that need to be set after first deploy:

- `INFRA_DEPLOY_ROLE_ARN` — the `InfraDeployRoleArn` output of `PhpstanOrgGithubOidc`. Used by `website-infra.yml`.
- `WEBSITE_DEPLOY_ROLE_ARN` — the `WebsiteDeployRoleArn` output of `PhpstanOrgWebsite`. Used by `website.yml`.
- `WEBSITE_BUCKET` — `phpstan-org-web`.
- `WEBSITE_DISTRIBUTION_ID` — the `DistributionId` output of `PhpstanOrgWebsite`.

## Rollback to the legacy distributions (emergency only)

The legacy distributions `E1W83FJ5FCYXPT` (apex) and `E3VJ14QANBNGO9` (www) still exist with their content (S3 buckets `web-phpstan.org`, `web-www.phpstan.org`) but without aliases attached. If a serious issue lands on the new stack, rollback steps:

1. Detach `phpstan.org` from the new distribution (CDK-managed `E2Y6ZJDXUL323J` — edit aliases via CLI to drop apex/www).
2. Re-attach `phpstan.org` to `E1W83FJ5FCYXPT` and `www.phpstan.org` to `E3VJ14QANBNGO9` (each requires an `update-distribution` with the original aliases list).
3. UPSERT Route 53 records back to the legacy CloudFront domains (`d31fkacuhtx2im.cloudfront.net` for apex, `d3jnpr60rvn14q.cloudfront.net` for www).

Each cutover hop (legacy ↔ new) takes ~5–10 min of intermittent 403s while CloudFront edges propagate, so this isn't free, but it's possible.

## Cleanup runbook (when the new stack has been stable for ~1 week)

- Delete CloudFront distribution `E1W83FJ5FCYXPT` (disable, wait, delete).
- Delete CloudFront distribution `E3VJ14QANBNGO9` (same).
- Delete CloudFront functions `phpstan-org-viewer-request` and `secure-headers-response`.
- Delete the Lambda@Edge function `web-phpstan-prg-rewrite-url` (replicas take ~hours to drain after dissociation — be patient).
- Delete the IAM role `web-phpstan-org-request-lambda-role`.
- Empty and delete S3 buckets `web-phpstan.org` and `web-www.phpstan.org`.
- Delete the legacy ACM cert `arn:aws:acm:us-east-1:928192134594:certificate/5c906d85-3885-4a8c-af99-27c9fee23c33` once it's no longer referenced.
