// Regenerates src/js/data/playground-default.json: the playground API's answer
// for the default snippet (src/js/data/playground-default.php), which the
// playground shows before the first analysis. Run after the snippet or the
// API's response format changes:
//
//   node scripts/update-playground-default.mjs
//
// PLAYGROUND_API overrides the API base URL.
import {readFileSync, writeFileSync} from 'node:fs';
import {dirname, resolve} from 'node:path';
import {fileURLToPath} from 'node:url';

const dataDir = resolve(dirname(fileURLToPath(import.meta.url)), '../src/js/data');
const api = process.env.PLAYGROUND_API ?? 'https://api.phpstan.org';
const code = readFileSync(resolve(dataDir, 'playground-default.php'), 'utf8');

const response = await fetch(api + '/analyse', {
	method: 'POST',
	headers: {'Content-Type': 'application/json'},
	body: JSON.stringify({
		code,
		level: '10',
		strictRules: false,
		bleedingEdge: false,
		treatPhpDocTypesAsCertain: true,
		saveResult: false,
	}),
});
if (!response.ok) {
	throw new Error(`${api}/analyse answered ${response.status}`);
}
const {tabs} = await response.json();
writeFileSync(resolve(dataDir, 'playground-default.json'), JSON.stringify({tabs}, null, '\t') + '\n');
console.log(`wrote ${tabs.length} tabs: ${tabs.map((t) => t.title).join(', ')}`);
