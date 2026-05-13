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
