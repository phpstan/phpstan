const fs = require('fs');
const fsPromises = fs.promises;

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

		data.push({
			identifier: identifier,
			rules: rules,
		});
	}

	return data;
};
