import * as cdk from 'aws-cdk-lib';
import * as iam from 'aws-cdk-lib/aws-iam';
import { Construct } from 'constructs';

export interface GithubOidcStackProps extends cdk.StackProps {
	readonly githubOrg: string;
	readonly githubRepo: string;
	readonly deployBranch: string;
}

// The OIDC provider for GitHub Actions and the IAM role used by the
// website-infra workflow to run `cdk diff` / `cdk deploy`. The role assumes the
// CDK bootstrap roles (created by `cdk bootstrap`) — that is the standard
// minimum-privilege pattern: the workflow can do anything CDK can do, no more.
export class GithubOidcStack extends cdk.Stack {
	readonly oidcProvider: iam.IOpenIdConnectProvider;
	readonly infraDeployRole: iam.Role;

	constructor(scope: Construct, id: string, props: GithubOidcStackProps) {
		super(scope, id, props);

		this.oidcProvider = new iam.OpenIdConnectProvider(this, 'GithubOidcProvider', {
			url: 'https://token.actions.githubusercontent.com',
			clientIds: ['sts.amazonaws.com'],
		});

		const allowedSubjects = [
			`repo:${props.githubOrg}/${props.githubRepo}:ref:refs/heads/${props.deployBranch}`,
		];

		this.infraDeployRole = new iam.Role(this, 'InfraDeployRole', {
			roleName: 'phpstan-org-infra-deploy',
			description: 'Assumed by the website-infra GitHub Actions workflow to run cdk diff / cdk deploy.',
			assumedBy: new iam.FederatedPrincipal(
				this.oidcProvider.openIdConnectProviderArn,
				{
					StringEquals: {
						'token.actions.githubusercontent.com:aud': 'sts.amazonaws.com',
					},
					StringLike: {
						'token.actions.githubusercontent.com:sub': allowedSubjects,
					},
				},
				'sts:AssumeRoleWithWebIdentity',
			),
			maxSessionDuration: cdk.Duration.hours(1),
		});

		// Permission to assume any CDK bootstrap role in this account. The CDK
		// bootstrap stack scopes those roles to its own deployments, so granting
		// AssumeRole on `cdk-*` is effectively "what cdk-cli can do".
		this.infraDeployRole.addToPolicy(new iam.PolicyStatement({
			actions: ['sts:AssumeRole'],
			resources: [`arn:aws:iam::${this.account}:role/cdk-*`],
		}));

		new cdk.CfnOutput(this, 'InfraDeployRoleArn', {
			value: this.infraDeployRole.roleArn,
			description: 'Role ARN for the website-infra GitHub Actions workflow',
			exportName: 'PhpstanOrgInfraDeployRoleArn',
		});

		new cdk.CfnOutput(this, 'OidcProviderArn', {
			value: this.oidcProvider.openIdConnectProviderArn,
			exportName: 'PhpstanOrgGithubOidcProviderArn',
		});
	}
}
