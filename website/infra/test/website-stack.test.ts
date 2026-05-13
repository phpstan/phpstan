import { App } from 'aws-cdk-lib';
import { Match, Template } from 'aws-cdk-lib/assertions';
import { describe, it } from 'vitest';
import { WebsiteStack } from '../lib/website-stack';

const baseProps = {
	env: { account: '928192134594', region: 'us-east-1' },
	githubOrg: 'phpstan',
	githubRepo: 'phpstan',
	deployBranch: '2.2.x',
	oidcProviderArn: 'arn:aws:iam::928192134594:oidc-provider/token.actions.githubusercontent.com',
	hostedZoneId: 'Z3OJGVJEUUWZDN',
	hostedZoneName: 'phpstan.org',
	apexDomain: 'phpstan.org',
	wwwDomain: 'www.phpstan.org',
	testDomain: 'new.phpstan.org',
};

function synthesize(productionAliases: boolean): Template {
	const app = new App();
	const stack = new WebsiteStack(app, 'TestWebsite', { ...baseProps, productionAliases });
	return Template.fromStack(stack);
}

describe('WebsiteStack', () => {
	describe('test mode (productionAliases: false)', () => {
		const template = synthesize(false);

		it('creates a private S3 bucket with versioning and SSL enforcement', () => {
			template.hasResourceProperties('AWS::S3::Bucket', {
				BucketName: 'phpstan-org-web',
				PublicAccessBlockConfiguration: {
					BlockPublicAcls: true,
					BlockPublicPolicy: true,
					IgnorePublicAcls: true,
					RestrictPublicBuckets: true,
				},
				VersioningConfiguration: { Status: 'Enabled' },
			});
		});

		it('attaches the bucket policy to deny insecure transport', () => {
			template.hasResourceProperties('AWS::S3::BucketPolicy', {
				PolicyDocument: Match.objectLike({
					Statement: Match.arrayWith([
						Match.objectLike({
							Effect: 'Deny',
							Condition: { Bool: { 'aws:SecureTransport': 'false' } },
						}),
					]),
				}),
			});
		});

		it('creates an Origin Access Control', () => {
			template.resourceCountIs('AWS::CloudFront::OriginAccessControl', 1);
		});

		it('creates the CloudFront Function on the JS 2.0 runtime', () => {
			template.hasResourceProperties('AWS::CloudFront::Function', {
				Name: 'phpstan-org-edge',
				FunctionConfig: Match.objectLike({ Runtime: 'cloudfront-js-2.0' }),
			});
		});

		it('creates a Response Headers Policy with HSTS, XCTO, XFO, Referrer-Policy and no X-XSS-Protection', () => {
			template.hasResourceProperties('AWS::CloudFront::ResponseHeadersPolicy', {
				ResponseHeadersPolicyConfig: Match.objectLike({
					SecurityHeadersConfig: Match.objectLike({
						StrictTransportSecurity: Match.objectLike({
							AccessControlMaxAgeSec: 365 * 24 * 60 * 60,
							IncludeSubdomains: true,
							Preload: true,
							Override: true,
						}),
						ContentTypeOptions: { Override: true },
						FrameOptions: { FrameOption: 'SAMEORIGIN', Override: true },
						ReferrerPolicy: { ReferrerPolicy: 'strict-origin-when-cross-origin', Override: true },
					}),
				}),
			});
			// XSS Protection must NOT be configured (deprecated).
			template.hasResourceProperties('AWS::CloudFront::ResponseHeadersPolicy', {
				ResponseHeadersPolicyConfig: {
					SecurityHeadersConfig: Match.not(Match.objectLike({ XSSProtection: Match.anyValue() })),
				},
			});
		});

		it('attaches the function and the headers policy to the default behavior, and redirects HTTP to HTTPS', () => {
			template.hasResourceProperties('AWS::CloudFront::Distribution', {
				DistributionConfig: Match.objectLike({
					Aliases: ['new.phpstan.org'],
					DefaultCacheBehavior: Match.objectLike({
						ViewerProtocolPolicy: 'redirect-to-https',
						Compress: true,
						FunctionAssociations: Match.arrayWith([
							Match.objectLike({ EventType: 'viewer-request' }),
						]),
						ResponseHeadersPolicyId: Match.anyValue(),
					}),
					HttpVersion: 'http2and3',
					IPV6Enabled: true,
					ViewerCertificate: Match.objectLike({
						MinimumProtocolVersion: 'TLSv1.2_2021',
					}),
				}),
			});
		});

		it('issues an ACM cert covering all three names (apex, www, test) so cutover needs no cert reissue', () => {
			template.hasResourceProperties('AWS::CertificateManager::Certificate', {
				DomainName: 'phpstan.org',
				SubjectAlternativeNames: Match.arrayWith(['www.phpstan.org', 'new.phpstan.org']),
			});
		});

		it('creates Route 53 A and AAAA records only for the test alias', () => {
			template.resourceCountIs('AWS::Route53::RecordSet', 2);
			template.hasResourceProperties('AWS::Route53::RecordSet', {
				Name: 'new.phpstan.org.',
				Type: 'A',
			});
			template.hasResourceProperties('AWS::Route53::RecordSet', {
				Name: 'new.phpstan.org.',
				Type: 'AAAA',
			});
		});

		it('creates the website-deploy role scoped to the 2.2.x branch via OIDC', () => {
			template.hasResourceProperties('AWS::IAM::Role', {
				RoleName: 'phpstan-org-website-deploy',
				AssumeRolePolicyDocument: Match.objectLike({
					Statement: Match.arrayWith([
						Match.objectLike({
							Action: 'sts:AssumeRoleWithWebIdentity',
							Condition: {
								StringEquals: { 'token.actions.githubusercontent.com:aud': 'sts.amazonaws.com' },
								StringLike: {
									'token.actions.githubusercontent.com:sub': 'repo:phpstan/phpstan:ref:refs/heads/2.2.x',
								},
							},
						}),
					]),
				}),
			});
		});
	});

	describe('production mode (productionAliases: true)', () => {
		const template = synthesize(true);

		it('uses the apex + www aliases on the distribution', () => {
			template.hasResourceProperties('AWS::CloudFront::Distribution', {
				DistributionConfig: Match.objectLike({
					Aliases: Match.arrayWith(['phpstan.org', 'www.phpstan.org']),
				}),
			});
		});

		it('creates Route 53 records for apex and www, not the test alias', () => {
			template.resourceCountIs('AWS::Route53::RecordSet', 4);
			template.hasResourceProperties('AWS::Route53::RecordSet', { Name: 'phpstan.org.', Type: 'A' });
			template.hasResourceProperties('AWS::Route53::RecordSet', { Name: 'phpstan.org.', Type: 'AAAA' });
			template.hasResourceProperties('AWS::Route53::RecordSet', { Name: 'www.phpstan.org.', Type: 'A' });
			template.hasResourceProperties('AWS::Route53::RecordSet', { Name: 'www.phpstan.org.', Type: 'AAAA' });
		});
	});
});
