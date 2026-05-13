import { describe, expect, it } from 'vitest';
// eslint-disable-next-line @typescript-eslint/no-require-imports
const { handler } = require('../functions/phpstan-org-edge.js');

interface CfEvent {
	request: {
		uri: string;
		querystring: Record<string, { value: string; multiValue?: Array<{ value: string }> }>;
		headers: Record<string, { value: string }>;
		method?: string;
	};
}

function makeEvent(uri: string, host = 'phpstan.org', querystring: CfEvent['request']['querystring'] = {}): CfEvent {
	return {
		request: {
			uri,
			querystring,
			headers: { host: { value: host } },
			method: 'GET',
		},
	};
}

describe('phpstan-org-edge handler', () => {
	describe('www -> apex redirect', () => {
		it('redirects www.phpstan.org/ to https://phpstan.org/', () => {
			const result = handler(makeEvent('/', 'www.phpstan.org'));
			expect(result.statusCode).toBe(301);
			expect(result.headers.location.value).toBe('https://phpstan.org/');
		});

		it('preserves path on www redirect', () => {
			const result = handler(makeEvent('/user-guide/getting-started', 'www.phpstan.org'));
			expect(result.statusCode).toBe(301);
			expect(result.headers.location.value).toBe('https://phpstan.org/user-guide/getting-started');
		});

		it('preserves querystring on www redirect', () => {
			const result = handler(makeEvent('/blog', 'www.phpstan.org', {
				page: { value: '2' },
				tag: { value: 'release' },
			}));
			expect(result.statusCode).toBe(301);
			expect(result.headers.location.value).toMatch(/^https:\/\/phpstan\.org\/blog\?/);
			expect(result.headers.location.value).toContain('page=2');
			expect(result.headers.location.value).toContain('tag=release');
		});

		it('handles multi-value querystring on www redirect', () => {
			const result = handler(makeEvent('/search', 'www.phpstan.org', {
				q: { value: 'array', multiValue: [{ value: 'array' }, { value: 'string' }] },
			}));
			expect(result.headers.location.value).toContain('q=array');
			expect(result.headers.location.value).toContain('q=string');
		});
	});

	describe('.html stripping', () => {
		it('redirects /foo.html to /foo', () => {
			const result = handler(makeEvent('/foo.html'));
			expect(result.statusCode).toBe(301);
			expect(result.headers.location.value).toBe('/foo');
		});

		it('redirects /user-guide/getting-started.html to /user-guide/getting-started', () => {
			const result = handler(makeEvent('/user-guide/getting-started.html'));
			expect(result.statusCode).toBe(301);
			expect(result.headers.location.value).toBe('/user-guide/getting-started');
		});

		it('does not strip when host is www (www redirect wins)', () => {
			const result = handler(makeEvent('/foo.html', 'www.phpstan.org'));
			expect(result.statusCode).toBe(301);
			expect(result.headers.location.value).toBe('https://phpstan.org/foo.html');
		});
	});

	describe('/r/ playground link rewrite', () => {
		it('rewrites /r/abc to /try.html', () => {
			const result = handler(makeEvent('/r/abc'));
			expect(result.uri).toBe('/try.html');
			expect(result.statusCode).toBeUndefined();
		});

		it('rewrites /r/abc/something to /try.html', () => {
			const result = handler(makeEvent('/r/abc/something'));
			expect(result.uri).toBe('/try.html');
		});
	});

	describe('/error-identifiers/ rewrite', () => {
		it('appends .html to /error-identifiers/foo', () => {
			const result = handler(makeEvent('/error-identifiers/foo'));
			expect(result.uri).toBe('/error-identifiers/foo.html');
		});

		it('leaves /error-identifiers/app.js unchanged', () => {
			const result = handler(makeEvent('/error-identifiers/app.js'));
			expect(result.uri).toBe('/error-identifiers/app.js');
		});

		it('leaves /error-identifiers/style.css unchanged', () => {
			const result = handler(makeEvent('/error-identifiers/style.css'));
			expect(result.uri).toBe('/error-identifiers/style.css');
		});
	});

	describe('clean URL rewrites (append .html)', () => {
		it('appends .html to /user-guide/getting-started', () => {
			const result = handler(makeEvent('/user-guide/getting-started'));
			expect(result.uri).toBe('/user-guide/getting-started.html');
		});

		it('appends .html to /blog', () => {
			const result = handler(makeEvent('/blog'));
			expect(result.uri).toBe('/blog.html');
		});

		it('leaves /image.png unchanged', () => {
			const result = handler(makeEvent('/image.png'));
			expect(result.uri).toBe('/image.png');
		});

		it('leaves /assets/app.abc123.js unchanged', () => {
			const result = handler(makeEvent('/assets/app.abc123.js'));
			expect(result.uri).toBe('/assets/app.abc123.js');
		});

		it('leaves / unchanged (DefaultRootObject serves index.html)', () => {
			const result = handler(makeEvent('/'));
			expect(result.uri).toBe('/');
			expect(result.statusCode).toBeUndefined();
		});

		it('leaves /user-guide/ unchanged (trailing slash passes through)', () => {
			const result = handler(makeEvent('/user-guide/'));
			expect(result.uri).toBe('/user-guide/');
			expect(result.statusCode).toBeUndefined();
		});
	});
});
