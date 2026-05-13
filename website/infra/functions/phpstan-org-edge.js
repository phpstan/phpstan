// CloudFront Function (runtime cloudfront-js-2.0), viewer-request.
//
// Replaces three pieces of legacy infrastructure:
//   1. CF Function `phpstan-org-viewer-request` — strips trailing `.html`
//   2. Lambda@Edge `web-phpstan-prg-rewrite-url` — appends `.html` to clean URLs
//   3. The www.phpstan.org redirect distribution
//
// CloudFront runs this file directly and looks for a top-level `function handler`.
// The trailing `module.exports` is gated on `typeof module` so the same source can
// be imported into Node-based unit tests; in the CF runtime `module` is not defined,
// so the export is silently skipped.
//
// Note on `/`: CloudFront applies DefaultRootObject AFTER viewer-request runs, so
// for a root request this function sees `uri === '/'`. We leave it alone and let
// DefaultRootObject (`index.html`) take over for the origin fetch. Same for any
// other trailing-slash URI — letting it pass through preserves current behavior
// (subdir trailing-slash 404s).

function formatQuerystring(qs) {
	var parts = [];
	for (var key in qs) {
		var entry = qs[key];
		if (entry.multiValue) {
			for (var i = 0; i < entry.multiValue.length; i++) {
				parts.push(key + '=' + entry.multiValue[i].value);
			}
		} else {
			parts.push(key + '=' + entry.value);
		}
	}
	return parts.length > 0 ? '?' + parts.join('&') : '';
}

function handler(event) {
	var request = event.request;
	var headers = request.headers;
	var uri = request.uri;
	var host = headers.host && headers.host.value;
	var qs = formatQuerystring(request.querystring);

	if (host === 'www.phpstan.org') {
		return {
			statusCode: 301,
			statusDescription: 'Moved Permanently',
			headers: {
				location: { value: 'https://phpstan.org' + uri + qs },
			},
		};
	}

	if (uri.endsWith('.html')) {
		return {
			statusCode: 301,
			statusDescription: 'Moved Permanently',
			headers: {
				location: { value: uri.substring(0, uri.length - 5) + qs },
			},
		};
	}

	if (uri.startsWith('/r/')) {
		request.uri = '/try.html';
		return request;
	}

	if (uri.startsWith('/error-identifiers/') && !uri.endsWith('.js') && !uri.endsWith('.css')) {
		request.uri = uri + '.html';
		return request;
	}

	var lastSegment = uri.substring(uri.lastIndexOf('/') + 1);
	if (lastSegment.length > 0 && lastSegment.indexOf('.') === -1) {
		request.uri = uri + '.html';
		return request;
	}

	return request;
}

if (typeof module !== 'undefined') {
	module.exports = { handler: handler };
}
