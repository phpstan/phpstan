---
title: Build Attestations
searchKeywords:
  - attestation
  - provenance
  - sigstore
  - verify
  - signature
---

Distributed builds of PHPStan and of the Turbo extension are compiled on GitHub Actions in the [phpstan/phpstan-src](https://github.com/phpstan/phpstan-src) repository. For each file, the build creates a [SLSA build provenance](https://slsa.dev/spec/v1.0/provenance) attestation signed through [Sigstore](https://www.sigstore.dev/). The attestation names the file by its SHA-256 digest and records the repository, workflow, ref and commit it was built from. It's stored in phpstan/phpstan-src and recorded in Sigstore's public transparency log. The file itself is not modified.

A successful verification proves the file was built by PHPStan's CI from that commit and was not modified afterwards, no matter where it was downloaded from.

Attested files
-------------

| Files | Published in | Since |
|---|---|---|
| `phpstan.phar` | [phpstan/phpstan](https://github.com/phpstan/phpstan) | 2.3.6 |
| Turbo extension binaries in `turbo-ext/` | [phpstan/phpstan](https://github.com/phpstan/phpstan) | 2.3.6 |
| [PIE](https://github.com/php/pie) release assets and the extension binaries inside them | [phpstan/turbo-ext releases](https://github.com/phpstan/turbo-ext/releases) | 2.3.6 |

Development builds committed to the `2.3.x` branch of phpstan/phpstan are attested as well.

Verifying with GitHub CLI
-------------

Install [GitHub CLI](https://cli.github.com/) and run `gh attestation verify` on the file. When PHPStan is installed with Composer:

```bash
gh attestation verify vendor/phpstan/phpstan/phpstan.phar --repo phpstan/phpstan-src
```

The Turbo binaries bundled in the Composer package:

```bash
for file in vendor/phpstan/phpstan/turbo-ext/*/phpstan_turbo*; do
    gh attestation verify "$file" --repo phpstan/phpstan-src
done
```

A release asset downloaded from phpstan/turbo-ext, or the extension PIE installed from it. An extension PIE compiled from source instead has no attestation.

```bash
gh attestation verify php_phpstan_turbo-2.3.6_php8.5-x86_64-linux-glibc.zip --repo phpstan/phpstan-src
gh attestation verify "$(php -r 'echo ini_get("extension_dir");')/phpstan_turbo.so" --repo phpstan/phpstan-src
```

`--repo` accepts an attestation from any workflow in phpstan/phpstan-src. To also require the workflow that builds the releases and a specific release tag:

```bash
gh attestation verify phpstan.phar --repo phpstan/phpstan-src \
    --signer-workflow phpstan/phpstan-src/.github/workflows/phar.yml \
    --source-ref refs/tags/2.3.6
```

Releases in phpstan/turbo-ext are immutable, and GitHub additionally attests that each asset belongs to its release:

```bash
gh release verify-asset 2.3.6 php_phpstan_turbo-2.3.6_php8.5-x86_64-linux-glibc.zip --repo phpstan/turbo-ext
```

Verifying without GitHub CLI
-------------

The attestations are standard Sigstore bundles, so any Sigstore client can verify them. Download the bundle for a file's digest from the GitHub API, which doesn't require authentication, and verify it with [cosign](https://github.com/sigstore/cosign):

```bash
DIGEST=$(sha256sum phpstan.phar | cut -d' ' -f1)
curl -s "https://api.github.com/repos/phpstan/phpstan-src/attestations/sha256:$DIGEST?predicate_type=provenance" \
    | jq '.attestations[0].bundle' > phpstan.phar.sigstore.json

cosign verify-blob-attestation phpstan.phar \
    --bundle phpstan.phar.sigstore.json --new-bundle-format \
    --type slsaprovenance1 \
    --certificate-oidc-issuer https://token.actions.githubusercontent.com \
    --certificate-identity-regexp '^https://github.com/phpstan/phpstan-src/\.github/workflows/phar\.yml@'
```
