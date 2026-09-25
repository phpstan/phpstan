#!/usr/bin/env bash
# Runs smoke-test.php in the Bref PHP runtime the function is deployed with.
#
# The container gets its own /tmp for the handler to wipe, a read-only view of the
# package, and no network - so nothing reaches Sentry either.
set -euo pipefail

cd "$(dirname "$0")"

image="bref/php-85:3"
case "$(uname -m)" in
	arm64 | aarch64) image="bref/arm-php-85:3" ;;
esac

exec docker run --rm --network none \
	-v "$PWD:/var/task:ro" \
	-e LAMBDA_TASK_ROOT=/var/task \
	-e BREF_LOOP_MAX=50 \
	--entrypoint php \
	"$image" /var/task/smoke-test.php
