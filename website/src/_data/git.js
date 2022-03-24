const util = require('util');
const { exec } = require('child_process');

module.exports = async () => {
	const promiseExec = util.promisify(exec);
	const { stdout, stderr } = await promiseExec('git rev-parse --abbrev-ref HEAD');

	return {
		branch: stdout.trim(),
	};
};
