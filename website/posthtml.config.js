module.exports = {
	plugins: {
		"posthtml-extend": {
			root: './'
		},
		"posthtml-include": {
			root: './'
		},
		"posthtml-expressions": {
			"locals": {
				"mainMenuItems": {
					"playground": {
						title: "Playground",
						link: "/",
						activeMenuItemLink: "/"
					},
				},
			}
		},
	},
};
