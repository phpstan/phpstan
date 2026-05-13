import * as cdk from 'aws-cdk-lib';
import * as acm from 'aws-cdk-lib/aws-certificatemanager';
import * as cloudfront from 'aws-cdk-lib/aws-cloudfront';
import * as origins from 'aws-cdk-lib/aws-cloudfront-origins';
import * as iam from 'aws-cdk-lib/aws-iam';
import * as route53 from 'aws-cdk-lib/aws-route53';
import * as route53Targets from 'aws-cdk-lib/aws-route53-targets';
import * as s3 from 'aws-cdk-lib/aws-s3';
import { Construct } from 'constructs';
import * as path from 'node:path';

export interface WebsiteStackProps extends cdk.StackProps {
	readonly githubOrg: string;
	readonly githubRepo: string;
	readonly deployBranch: string;
	readonly oidcProviderArn: string;
	readonly hostedZoneId: string;
	readonly hostedZoneName: string;
	readonly apexDomain: string;
	readonly wwwDomain: string;
	readonly testDomain: string;
	readonly productionAliases: boolean;
}

// The website infrastructure: private S3 bucket served via CloudFront with OAC,
// a single CloudFront Function 2.0 handling host redirect + URL rewriting, a
// Response Headers Policy for security headers, ACM cert (DNS-validated), and
// Route 53 records.
//
// The `productionAliases` flag switches the distribution between test mode
// (only `new.phpstan.org`) and production mode (`phpstan.org` + `www.phpstan.org`).
// The cert always covers all three names so the cutover is alias-only — no cert
// reissue needed.
export class WebsiteStack extends cdk.Stack {
	readonly bucket: s3.Bucket;
	readonly distribution: cloudfront.Distribution;
	readonly websiteDeployRole: iam.Role;

	constructor(scope: Construct, id: string, props: WebsiteStackProps) {
		super(scope, id, props);

		this.bucket = new s3.Bucket(this, 'WebsiteBucket', {
			bucketName: 'phpstan-org-web',
			blockPublicAccess: s3.BlockPublicAccess.BLOCK_ALL,
			encryption: s3.BucketEncryption.S3_MANAGED,
			versioned: true,
			removalPolicy: cdk.RemovalPolicy.RETAIN,
			enforceSSL: true,
		});

		const hostedZone = route53.HostedZone.fromHostedZoneAttributes(this, 'HostedZone', {
			hostedZoneId: props.hostedZoneId,
			zoneName: props.hostedZoneName,
		});

		// One cert covers everything from day one: the test alias plus both prod
		// aliases. Flipping `productionAliases` later is alias-only — no cert churn.
		const certificate = new acm.Certificate(this, 'Certificate', {
			domainName: props.apexDomain,
			subjectAlternativeNames: [props.wwwDomain, props.testDomain],
			validation: acm.CertificateValidation.fromDns(hostedZone),
		});

		const edgeFunction = new cloudfront.Function(this, 'EdgeFunction', {
			functionName: 'phpstan-org-edge',
			comment: 'Viewer-request: www->apex 301, .html strip 301, clean-URL rewrite. Replaces Lambda@Edge.',
			runtime: cloudfront.FunctionRuntime.JS_2_0,
			code: cloudfront.FunctionCode.fromFile({
				filePath: path.join(__dirname, '..', 'functions', 'phpstan-org-edge.js'),
			}),
		});

		const responseHeadersPolicy = new cloudfront.ResponseHeadersPolicy(this, 'SecurityHeadersPolicy', {
			responseHeadersPolicyName: 'phpstan-org-security-headers',
			comment: 'HSTS, X-Content-Type-Options, X-Frame-Options, Referrer-Policy. Replaces secure-headers-response CF Function.',
			securityHeadersBehavior: {
				strictTransportSecurity: {
					accessControlMaxAge: cdk.Duration.days(365),
					includeSubdomains: true,
					preload: true,
					override: true,
				},
				contentTypeOptions: { override: true },
				frameOptions: {
					frameOption: cloudfront.HeadersFrameOption.SAMEORIGIN,
					override: true,
				},
				referrerPolicy: {
					referrerPolicy: cloudfront.HeadersReferrerPolicy.STRICT_ORIGIN_WHEN_CROSS_ORIGIN,
					override: true,
				},
			},
		});

		const domainNames = props.productionAliases
			? [props.apexDomain, props.wwwDomain]
			: [props.testDomain];

		this.distribution = new cloudfront.Distribution(this, 'Distribution', {
			comment: `phpstan.org (productionAliases=${props.productionAliases})`,
			domainNames,
			certificate,
			defaultRootObject: 'index.html',
			minimumProtocolVersion: cloudfront.SecurityPolicyProtocol.TLS_V1_2_2021,
			priceClass: cloudfront.PriceClass.PRICE_CLASS_100,
			httpVersion: cloudfront.HttpVersion.HTTP2_AND_3,
			enableIpv6: true,
			defaultBehavior: {
				origin: origins.S3BucketOrigin.withOriginAccessControl(this.bucket),
				viewerProtocolPolicy: cloudfront.ViewerProtocolPolicy.REDIRECT_TO_HTTPS,
				allowedMethods: cloudfront.AllowedMethods.ALLOW_GET_HEAD,
				cachedMethods: cloudfront.CachedMethods.CACHE_GET_HEAD,
				compress: true,
				cachePolicy: cloudfront.CachePolicy.CACHING_OPTIMIZED,
				responseHeadersPolicy,
				functionAssociations: [{
					function: edgeFunction,
					eventType: cloudfront.FunctionEventType.VIEWER_REQUEST,
				}],
			},
		});

		const distributionTarget = route53.RecordTarget.fromAlias(
			new route53Targets.CloudFrontTarget(this.distribution),
		);

		if (props.productionAliases) {
			new route53.ARecord(this, 'ApexARecord', {
				zone: hostedZone,
				recordName: props.apexDomain,
				target: distributionTarget,
			});
			new route53.AaaaRecord(this, 'ApexAaaaRecord', {
				zone: hostedZone,
				recordName: props.apexDomain,
				target: distributionTarget,
			});
			new route53.ARecord(this, 'WwwARecord', {
				zone: hostedZone,
				recordName: props.wwwDomain,
				target: distributionTarget,
			});
			new route53.AaaaRecord(this, 'WwwAaaaRecord', {
				zone: hostedZone,
				recordName: props.wwwDomain,
				target: distributionTarget,
			});
		} else {
			new route53.ARecord(this, 'TestARecord', {
				zone: hostedZone,
				recordName: props.testDomain,
				target: distributionTarget,
			});
			new route53.AaaaRecord(this, 'TestAaaaRecord', {
				zone: hostedZone,
				recordName: props.testDomain,
				target: distributionTarget,
			});
		}

		this.websiteDeployRole = this.createWebsiteDeployRole(props);

		new cdk.CfnOutput(this, 'BucketName', {
			value: this.bucket.bucketName,
			description: 'S3 bucket name for the website',
			exportName: 'PhpstanOrgWebsiteBucketName',
		});
		new cdk.CfnOutput(this, 'DistributionId', {
			value: this.distribution.distributionId,
			description: 'CloudFront distribution ID',
			exportName: 'PhpstanOrgWebsiteDistributionId',
		});
		new cdk.CfnOutput(this, 'DistributionDomain', {
			value: this.distribution.distributionDomainName,
			description: 'CloudFront default domain (for testing without alias)',
		});
		new cdk.CfnOutput(this, 'WebsiteDeployRoleArn', {
			value: this.websiteDeployRole.roleArn,
			description: 'Role ARN for the website GitHub Actions workflow',
			exportName: 'PhpstanOrgWebsiteDeployRoleArn',
		});
	}

	private createWebsiteDeployRole(props: WebsiteStackProps): iam.Role {
		const role = new iam.Role(this, 'WebsiteDeployRole', {
			roleName: 'phpstan-org-website-deploy',
			description: 'Assumed by the website GitHub Actions workflow to sync the bucket and invalidate CloudFront.',
			assumedBy: new iam.FederatedPrincipal(
				props.oidcProviderArn,
				{
					StringEquals: {
						'token.actions.githubusercontent.com:aud': 'sts.amazonaws.com',
					},
					StringLike: {
						'token.actions.githubusercontent.com:sub': `repo:${props.githubOrg}/${props.githubRepo}:ref:refs/heads/${props.deployBranch}`,
					},
				},
				'sts:AssumeRoleWithWebIdentity',
			),
			maxSessionDuration: cdk.Duration.hours(1),
		});

		this.bucket.grantReadWrite(role);
		this.bucket.grantDelete(role);

		role.addToPolicy(new iam.PolicyStatement({
			actions: [
				'cloudfront:CreateInvalidation',
				'cloudfront:GetInvalidation',
				'cloudfront:ListInvalidations',
			],
			resources: [
				`arn:aws:cloudfront::${this.account}:distribution/${this.distribution.distributionId}`,
			],
		}));

		return role;
	}
}
