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

		// Collect unique non-phpstan-src repos
		const repos = new Set();
		for (const ruleRepos of Object.values(rules)) {
			for (const repo of Object.keys(ruleRepos)) {
				if (repo !== 'phpstan/phpstan-src') {
					repos.add(repo.replace('phpstan/', ''));
				}
			}
		}

		// Read ignorable status from .md file
		let ignorable = true;
		const docPath = errorsDir + '/' + identifier + '.md';
		if (fs.existsSync(docPath)) {
			const file = matter(await fsPromises.readFile(docPath, 'utf8'));
			if (file.data.ignorable === false) {
				ignorable = false;
			}
		}

		groups.get(prefix).push({
			identifier,
			label: repos.size > 0 ? [...repos].join(', ') : null,
			ignorable,
		});
	}

	return [...groups.entries()]
		.sort(([a], [b]) => a.localeCompare(b))
		.map(([prefix, items]) => ({
			prefix,
			count: items.length,
			identifiers: items.sort((a, b) => a.identifier.localeCompare(b.identifier)),
		}));
};
