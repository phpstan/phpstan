module.exports = {
	plugins: {
		"posthtml-extend": {
			root: './'
		},
		"posthtml-include": {
			root: './'
		},
		"posthtml-md": {},
		"posthtml-expressions": {
			"locals": {
				"mainMenuItems": {
					"playground": {
						title: "Playground",
						link: "/",
						activeMenuItemLink: "/"
					},
					"configReference": {
						title: "Config Reference",
						link: "/config-reference",
						activeMenuItemLink: "/config-reference"
					},
				},
			}
		},
		"posthtml-highlight": {},
	},
};
