import { fetchData } from "./fetchData.js";
import { getPostData } from "./getPostData.js";
import { renderPost } from "./renderPost.js";
import { dispatchEvent } from "./dispatchEvent.js";

const fetchPost = async (post) => {
  const postData = getPostData(post);
  let renderedPostElement;

  await fetchData({
    action: 'ftf_get_post',
    nonce: ftf_fediverse_embeds.nonce,
    post: JSON.stringify(postData)
  }, (response) => {
    renderedPostElement = renderPost(response);
    dispatchEvent('ftf_fediverse_embeds_post_processed', renderedPostElement);
  });

  return renderedPostElement;
};

const EAGER_LOAD_COUNT = 4;

const processPosts = async () => {
  const postEmbeds = [...document.querySelectorAll('blockquote.ftf-fediverse-post-embed')];

  if (!postEmbeds.length) return;

  const eager = postEmbeds.slice(0, EAGER_LOAD_COUNT);
  const deferred = postEmbeds.slice(EAGER_LOAD_COUNT);

  eager.forEach(fetchPost);

  if (!deferred.length) return;

  if (!('IntersectionObserver' in window)) {
    deferred.forEach(fetchPost);
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        observer.unobserve(entry.target);
        fetchPost(entry.target);
      }
    });
  }, {
    rootMargin: '100%',
  });

  deferred.forEach((post) => observer.observe(post));
};

export { processPosts };
