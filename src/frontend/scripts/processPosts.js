import { fetchData } from "./fetchData.js";
import { getPostData } from "./getPostData.js";
import { renderPost } from "./renderPost.js";
import { dispatchEvent } from "./dispatchEvent.js";

const processPosts = async (fn) => {
  const postsEmbeds = document.querySelectorAll('blockquote.ftf-fediverse-post-embed');
  let posts = [];
  let renderedPostElements = [];

  for (const post of postsEmbeds) {
    const postData = getPostData(post);
    posts.push(postData);
  }
  
  // console.log('post data', posts);

  if (posts.length){
    await Promise.all(
      posts.map(
        async post => {
      await fetchData({
        action: 'ftf_get_post',
        post: JSON.stringify(post)
      }, (response) => {
        // console.log('ftf_get_post', {post, response});
        const renderedPostElement = renderPost(response);
        renderedPostElements.push(renderedPostElement);
      });
    }));

    dispatchEvent('ftf_fediverse_embeds_posts_processed', renderedPostElements);
  }
};

export { processPosts };
