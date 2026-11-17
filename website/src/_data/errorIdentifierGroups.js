const fs = require('fs');
const fsPromises = fs.promises;
const matter = require('gray-matter');

const errorsDir = __dirname + '/../../errors';

module.exports = async () => {
	const identifiers = JSON.parse(await fsPromises.readFile(__dirname + '/../errorsIdentifiers.json'));
	const groups = new Map();

	for (const [identifier, rules] of Object.entries(identifiers)) {
		const prefix = identifier.split('.')[0];
		if (!groups.has(prefix)) {
			groups.set(prefix, []);
		}

		// Collect repos, check if phpstan-src reports this identifier
		let hasSrc = false;
		const otherRepos = new Set();
		for (const ruleRepos of Object.values(rules)) {
			for (const repo of Object.keys(ruleRepos)) {
				if (repo === 'phpstan/phpstan-src') {
					hasSrc = true;
				} else {
					otherRepos.add(repo.replace('phpstan/', ''));
				}
			}
		}

		// Read front matter from .md file
		let ignorable = true;
		let feasible = true;
		let unlikely = false;
		let autofixable = false;
		let shortDescription = null;
		const docPath = errorsDir + '/' + identifier + '.md';
		if (fs.existsSync(docPath)) {
			const file = matter(await fsPromises.readFile(docPath, 'utf8'));
			if (file.data.ignorable === false) {
				ignorable = false;
			}
			if (file.data.feasible === false) {
				feasible = false;
			}
			if (file.data.unlikely === true) {
				unlikely = true;
			}
			if (file.data.autofixable === true) {
				autofixable = true;
			}
			if (file.data.shortDescription) {
				shortDescription = file.data.shortDescription;
			}
		}

		// Skip identifiers that are not feasible or are unlikely
		if (!feasible || unlikely) {
			continue;
		}

		groups.get(prefix).push({
			identifier,
			// Only identifiers that phpstan-src never reports need an extension installed
			extensions: hasSrc ? [] : [...otherRepos].sort(),
			ignorable,
			autofixable,
			shortDescription,
		});
	}

	return [...groups.entries()]
		.filter(([, items]) => items.length > 0)
		.sort(([a], [b]) => a.localeCompare(b))
		.map(([prefix, items]) => ({
			prefix,
			count: items.length,
			autofixableCount: items.filter((item) => item.autofixable).length,
			// Set when the whole group comes from extensions, e.g. phpunit.*
			extensions: items.every((item) => item.extensions.length > 0)
				? [...new Set(items.flatMap((item) => item.extensions))].sort()
				: [],
			identifiers: items.sort((a, b) => a.identifier.localeCompare(b.identifier)),
		}));
};
