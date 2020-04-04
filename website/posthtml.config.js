module.exports = {
	plugins: {
		"posthtml-extend": {
			root: './',
			strict: false
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
					"userGuide": {
						title: "User Guide",
						link: "/user-guide/getting-started",
						activeMenuItemLink: "/user-guide/getting-started",
					},
					"configReference": {
						title: "Config Reference",
						link: "/config-reference",
						activeMenuItemLink: "/config-reference"
					},
					"developingExtensions": {
						title: "Developing Extensions",
						link: "/developing-extensions/extension-types",
						activeMenuItemLink: "/developing-extensions/extension-types"
					},
				},
			}
		},
		"posthtml-highlight": {},
	},
};
