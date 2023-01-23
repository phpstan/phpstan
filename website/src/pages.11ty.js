class Pages {
  data() {
    return {
      permalink: '/pages.json',
    };
  }

  render(data) {
    const obj = {};
    for (const post of data.collections.all) {
      obj[this.trimDotHtml(post.url)] = post.data.title;
    }
    return JSON.stringify(obj);
  }
}

module.exports = Pages;
