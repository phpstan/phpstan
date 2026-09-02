#!/usr/bin/env node
import * as cdk from 'aws-cdk-lib';
import { GithubOidcStack } from '../lib/github-oidc-stack';
import { WebsiteStack } from '../lib/website-stack';

const app = new cdk.App();

const account = process.env.CDK_DEFAULT_ACCOUNT ?? '928192134594';
const region = 'us-east-1';
const env = { account, region };

const githubOrg = 'phpstan';
const githubRepo = 'phpstan';
const deployBranch = '2.3.x';

const hostedZoneId = 'Z3OJGVJEUUWZDN';
const hostedZoneName = 'phpstan.org';
const apexDomain = 'phpstan.org';
const wwwDomain = 'www.phpstan.org';
const testDomain = 'new.phpstan.org';

const oidcStack = new GithubOidcStack(app, 'PhpstanOrgGithubOidc', {
	env,
	githubOrg,
	githubRepo,
	deployBranch,
	description: 'GitHub Actions OIDC provider and infra-deploy role for phpstan.org website infra.',
});

new WebsiteStack(app, 'PhpstanOrgWebsite', {
	env,
	githubOrg,
	githubRepo,
	deployBranch,
	oidcProviderArn: oidcStack.oidcProvider.openIdConnectProviderArn,
	hostedZoneId,
	hostedZoneName,
	apexDomain,
	wwwDomain,
	testDomain,
	description: 'phpstan.org website (S3 + CloudFront + CF Function)',
});

app.synth();
