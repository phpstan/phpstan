const blogData = require('./../blog.json');

const freezeBlogPosts = !!process.env.FREEZE_BLOG_POSTS;
const cutoffYear = 2026;

function isFuturePost(data) {
	return freezeBlogPosts && data.date instanceof Date && data.date.getFullYear() >= cutoffYear;
}

module.exports = {
	...blogData,
	layout: "_blogPost.njk",
	tags: ["blog"],
	eleventyComputed: {
		permalink: (data) => {
			if (isFuturePost(data)) {
				return false;
			}
			return `/blog/${data.page.fileSlug}.html`;
		},
		eleventyExcludeFromCollections: (data) => {
			return isFuturePost(data);
		},
	},
};
