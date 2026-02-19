// Algolia DocSearch Crawler Configuration
// https://crawler.algolia.com/admin/crawlers
//
// This file is the source of truth for the Algolia Crawler configuration.
// After modifying this file, update the crawler config via the API:
//
//   curl -X PATCH "https://crawler.algolia.com/api/1/crawlers/${CRAWLER_ID}/config" \
//     -H "Content-Type: application/javascript" \
//     -u "${CRAWLER_USER_ID}:${CRAWLER_API_KEY}" \
//     --data-binary @website/algolia-crawler-config.js
//
// To trigger a reindex:
//
//   curl -X POST "https://crawler.algolia.com/api/1/crawlers/${CRAWLER_ID}/reindex" \
//     -H "Content-Type: application/json" \
//     -u "${CRAWLER_USER_ID}:${CRAWLER_API_KEY}"
//
new Crawler({
  rateLimit: 8,
  startUrls: ["https://phpstan.org/", "https://apiref.phpstan.org/2.1.x/"],
  renderJavaScript: false,
  sitemaps: ["https://phpstan.org/sitemap.xml"],
  ignoreCanonicalTo: false,
  discoveryPatterns: [
    "https://phpstan.org/**",
    "https://apiref.phpstan.org/2.1.x/**",
  ],
  exclusionPatterns: [
    "https://phpstan.org/merch*",
    "https://phpstan.org/try*",
    "https://phpstan.org/sponsor*",
    "https://phpstan.org/rss*",
    "https://phpstan.org/sitemap*",
    "https://phpstan.org/llms*",
    "https://apiref.phpstan.org/*/source-*",
  ],
  schedule: "at 12:05 PM on Monday",
  actions: [
    {
      indexName: "phpstan",
      pathsToMatch: [
        "https://phpstan.org/**",
        "!https://phpstan.org/blog/**",
        "!https://phpstan.org/error-identifiers",
        "!https://phpstan.org/error-identifiers/**",
      ],
      recordExtractor: ({ helpers }) => {
        return helpers.docsearch({
          recordProps: {
            lvl1: ["header h1", "article h1", "main h1", "h1", "head > title"],
            content: ["article p, article li", "main p, main li", "p, li"],
            lvl0: {
              selectors: "#algoliaSectionTitle",
              defaultValue: "Documentation",
            },
            lvl2: ["article h2", "main h2", "h2"],
            lvl3: ["article h3", "main h3", "h3"],
            lvl4: ["article h4", "main h4", "h4"],
            lvl5: ["article h5", "main h5", "h5"],
            lvl6: ["article h6", "main h6", "h6"],
            pageRank: "90",
          },
          aggregateContent: true,
        });
      },
    },
    {
      indexName: "phpstan",
      pathsToMatch: [
        "https://phpstan.org/error-identifiers/**",
      ],
      recordExtractor: ({ helpers }) => {
        return helpers.docsearch({
          recordProps: {
            lvl1: ["header h1", "article h1", "main h1", "h1", "head > title"],
            content: ["article p, article li", "main p, main li", "p, li"],
            lvl0: {
              selectors: "#algoliaSectionTitle",
              defaultValue: "Error Identifiers",
            },
            lvl2: ["article h2", "main h2", "h2"],
            lvl3: ["article h3", "main h3", "h3"],
            lvl4: ["article h4", "main h4", "h4"],
            lvl5: ["article h5", "main h5", "h5"],
            lvl6: ["article h6", "main h6", "h6"],
            pageRank: "70",
          },
          aggregateContent: true,
        });
      },
    },
    {
      indexName: "phpstan",
      pathsToMatch: ["https://phpstan.org/blog/**"],
      recordExtractor: ({ helpers }) => {
        return helpers.docsearch({
          recordProps: {
            lvl1: ["header h1", "article h1", "main h1", "h1", "head > title"],
            content: ["article p, article li", "main p, main li", "p, li"],
            lvl0: {
              selectors: "#algoliaSectionTitle",
              defaultValue: "Blog",
            },
            lvl2: ["article h2", "main h2", "h2"],
            lvl3: ["article h3", "main h3", "h3"],
            lvl4: ["article h4", "main h4", "h4"],
            lvl5: ["article h5", "main h5", "h5"],
            lvl6: ["article h6", "main h6", "h6"],
            pageRank: "50",
          },
          aggregateContent: true,
        });
      },
    },
    {
      indexName: "phpstan",
      pathsToMatch: ["https://apiref.phpstan.org/2.1.x/**"],
      recordExtractor: ({ helpers }) => {
        return helpers.docsearch({
          recordProps: {
            lvl1: ["header h1", "article h1", "main h1", "h1", "head > title"],
            content: ["article p, article li", "main p, main li", "p, li"],
            lvl0: {
              selectors: "#sectionTitle",
              defaultValue: "API Reference",
            },
            lvl2: ["article h2", "main h2", "h2"],
            lvl3: ["article h3", "main h3", "h3"],
            lvl4: ["article h4", "main h4", "h4"],
            lvl5: ["article h5", "main h5", "h5"],
            lvl6: ["article h6", "main h6", "h6"],
            pageRank: "10",
          },
          aggregateContent: true,
        });
      },
    },
  ],
  initialIndexSettings: {
    phpstan: {
      attributesForFaceting: ["type", "lang"],
      attributesToRetrieve: [
        "hierarchy",
        "content",
        "anchor",
        "url",
        "url_without_anchor",
        "type",
      ],
      attributesToHighlight: ["hierarchy", "hierarchy_camel", "content"],
      attributesToSnippet: ["content:10"],
      camelCaseAttributes: ["hierarchy", "hierarchy_radio", "content"],
      searchableAttributes: [
        "unordered(hierarchy_radio_camel.lvl0)",
        "unordered(hierarchy_radio.lvl0)",
        "unordered(hierarchy_radio_camel.lvl1)",
        "unordered(hierarchy_radio.lvl1)",
        "unordered(hierarchy_radio_camel.lvl2)",
        "unordered(hierarchy_radio.lvl2)",
        "unordered(hierarchy_radio_camel.lvl3)",
        "unordered(hierarchy_radio.lvl3)",
        "unordered(hierarchy_radio_camel.lvl4)",
        "unordered(hierarchy_radio.lvl4)",
        "unordered(hierarchy_radio_camel.lvl5)",
        "unordered(hierarchy_radio.lvl5)",
        "unordered(hierarchy_radio_camel.lvl6)",
        "unordered(hierarchy_radio.lvl6)",
        "unordered(hierarchy_camel.lvl0)",
        "unordered(hierarchy.lvl0)",
        "unordered(hierarchy_camel.lvl1)",
        "unordered(hierarchy.lvl1)",
        "unordered(hierarchy_camel.lvl2)",
        "unordered(hierarchy.lvl2)",
        "unordered(hierarchy_camel.lvl3)",
        "unordered(hierarchy.lvl3)",
        "unordered(hierarchy_camel.lvl4)",
        "unordered(hierarchy.lvl4)",
        "unordered(hierarchy_camel.lvl5)",
        "unordered(hierarchy.lvl5)",
        "unordered(hierarchy_camel.lvl6)",
        "unordered(hierarchy.lvl6)",
        "content",
      ],
      distinct: true,
      attributeForDistinct: "url",
      customRanking: [
        "desc(weight.pageRank)",
        "desc(weight.level)",
        "asc(weight.position)",
      ],
      ranking: [
        "words",
        "filters",
        "typo",
        "attribute",
        "proximity",
        "exact",
        "custom",
      ],
      highlightPreTag:
        '<span class="algolia-docsearch-suggestion--highlight">',
      highlightPostTag: "</span>",
      minWordSizefor1Typo: 3,
      minWordSizefor2Typos: 7,
      allowTyposOnNumericTokens: false,
      minProximity: 1,
      ignorePlurals: true,
      advancedSyntax: true,
      attributeCriteriaComputedByMinProximity: true,
      removeWordsIfNoResults: "allOptional",
    },
  },
  appId: "563YUB35R3",
  apiKey: "xxx",
});
