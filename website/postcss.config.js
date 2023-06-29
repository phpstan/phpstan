const tailwindcss = require('tailwindcss');
const autoprefixer = require('autoprefixer');

const plugins = [];
plugins.push(tailwindcss)
plugins.push(tailwindcss(__dirname + '/tailwind.config.js'))
plugins.push(autoprefixer)

module.exports = { plugins }
