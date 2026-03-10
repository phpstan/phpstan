const fs = require('fs');
const fsPromises = fs.promises;
const matter = require('gray-matter');

const errorsDir = __dirname + '/../../errors';

module.exports = async () => {
	const identifiers = JSON.parse(await fsPromises.readFile(__dirname + '/../errorsIdentifiers.json'));
	const data = [];
	for (const identifier in identifiers) {
		if (!identifiers.hasOwnProperty(identifier)) {
			continue;
		}

		const rules = [];
		for (const rule in identifiers[identifier]) {
			if (!identifiers[identifier].hasOwnProperty(rule)) {
				continue;
			}

			const links = [];
			for (const repo in identifiers[identifier][rule]) {
				if (!identifiers[identifier][rule].hasOwnProperty(repo)) {
					continue;
				}

				links.push({
					repo: repo,
					links: identifiers[identifier][rule][repo],
				})
			}

			rules.push({
				ruleName: rule,
				links: links,
			})
		}

		const item = {
			identifier: identifier,
			prefix: identifier.split('.')[0],
			rules: rules,
		};

		const docPath = errorsDir + '/' + identifier + '.md';
		if (fs.existsSync(docPath)) {
			const file = matter(await fsPromises.readFile(docPath, 'utf8'));
			item.doc = {
				ignorable: file.data.ignorable !== false,
				feasible: file.data.feasible !== false,
				unlikely: file.data.unlikely === true,
				shortDescription: file.data.shortDescription || null,
				content: file.content,
			};
		}

		data.push(item);
	}

	return data;
};
