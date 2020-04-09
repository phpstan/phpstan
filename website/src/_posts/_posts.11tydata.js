const blogData = require('./../blog.json');
blogData.layout = "_blogPost.njk";
blogData.permalink = '/blog/{{ page.fileSlug }}.html';
blogData.tags = ["blog"];

module.exports = blogData;
