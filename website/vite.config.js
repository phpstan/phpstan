import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';
import { readFileSync } from 'fs';
import { globSync } from 'glob';
import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = resolve(__dirname, 'tmp');

// Read files at config time for injection as constants
const releaseVersion = readFileSync(resolve(root, 'release.txt'), 'utf-8').trim();
const pagesJson = readFileSync(resolve(root, 'pages.json'), 'utf-8');
const errorsIdentifiersJson = readFileSync(resolve(root, 'errorsIdentifiers.json'), 'utf-8');

// Discover all HTML entry points in tmp/
const htmlFiles = globSync('**/*.html', { cwd: root });
const input = Object.fromEntries(
	htmlFiles.map(file => [
		file.replace(/\.html$/, ''),
		resolve(root, file),
	])
);

export default defineConfig({
	root,
	define: {
		__RELEASE_VERSION__: JSON.stringify(releaseVersion),
		__PAGES_JSON__: pagesJson,
		__ERRORS_IDENTIFIERS_JSON__: errorsIdentifiersJson,
	},
	server: {
		watch: {
			// tmp/ is gitignored but it's the Vite root (Eleventy output).
			// Override the default ignored list so that the watcher picks up
			// changes Eleventy writes to tmp/.
			ignored: ['**/node_modules/**', '**/.git/**'],
		},
	},
	build: {
		outDir: resolve(__dirname, 'dist'),
		emptyOutDir: true,
		rollupOptions: {
			input,
		},
	},
	plugins: [
		// Rewrite /r/{id} URLs to try.html so the playground SPA handles them
		{
			name: 'rewrite-playground-urls',
			configureServer(server) {
				server.middlewares.use((req, res, next) => {
					if (req.url && req.url.match(/^\/r\//)) {
						req.url = '/try.html';
					}
					next();
				});
			},
		},
		// Copy static files that Vite doesn't process natively
		viteStaticCopy({
			targets: [
				{ src: 'robots.txt', dest: '.' },
				{ src: 'llms.txt', dest: '.' },
				{ src: 'rss.xml', dest: '.' },
				{ src: 'sitemap.xml', dest: '.' },
				{ src: 'errorsIdentifiers.json', dest: '.' },
				{ src: 'pages.json', dest: '.' },
				{ src: 'release.txt', dest: '.' },
				{ src: 'images-emails/**/*', dest: 'images-emails' },
				{ src: 'images-emails-2/**/*', dest: 'images-emails-2' },
			],
		}),
	],
});
