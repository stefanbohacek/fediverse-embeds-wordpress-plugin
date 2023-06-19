import { fetchData } from "./fetchData.js";
import { getPostData } from "./getPostData.js";
import { renderPost } from "./renderPost.js";
import { dispatchEvent } from "./dispatchEvent.js";

const processPosts = (fn) => {
  const postsEmbeds = document.querySelectorAll('blockquote.ftf-fediverse-post-embed');
  let posts = [];

  for (const post of postsEmbeds) {
    const postData = getPostData(post);
    posts.push(postData);
  }
  
  // console.log('post data', posts);

  if (posts.length){
    posts.forEach(post => {
      fetchData({
        action: 'ftf_get_post',
        post: JSON.stringify(post)
      }, (response) => {
        console.log('ftf_get_post', {post, response});
        renderPost(response);
      });
    })
  }
};

export { processPosts };

/***************************************************************************************************************/
        //TODO: Old code for handling post attachments, might be useful for non-Mastodon platforms.
        
        // const postsWithAttachment = document.querySelectorAll('[data-url-attachment-processed="false"]');
        // let postsWithAttachmentCount = postsWithAttachment.length;
        
        // if (postsWithAttachmentCount === 0){
        //   dispatchEvent('ftf_fediverse_embeds_posts_processed');
        // }
        
        // // console.log('postsWithAttachment', postsWithAttachment);
        
        // for (const post of postsWithAttachment) {
        //   post.dataset.urlAttachmentProcessed = 'true';
          
        //   // TODO: Render quotes.
          
        //   fetchData({
        //     action: 'ftf_get_site_info',
        //     url: post.dataset.urlAttachment
        //   }, (data) => {
        //     if (data && data.image){
        //       let urlAttachmentPreview = document.createElement('div');
        //       urlAttachmentPreview.className = `post-attachment-preview card mt-4`;
              
        //       let tmpAnchor = document.createElement ('a');
        //       tmpAnchor.href = post.dataset.urlAttachment;
              
        //       let urlAttachmentPreviewHTML = '';
        //       // console.log('debug:data.image', data.image);
        //       if (data.image){
        //         urlAttachmentPreviewHTML += `<a href="${ post.dataset.urlAttachment }"><img loading="lazy" class="post-attachment-site-thumbnail card-img-top" src="${window.ftf_fediverse_embeds.blog_url}/wp-json/ftf/media-proxy?url=${ encodeURI(data.image) }" alt="Preview image for ${post.dataset.urlAttachment}"></a>`;
        //       }
              
        //       urlAttachmentPreviewHTML += `<div class="card-body">`;
        //       urlAttachmentPreviewHTML += `<p class="card-text"><a class="stretched-link text-muted" href="${ post.dataset.urlAttachment }" target="_blank">${ tmpAnchor.hostname }</a></p>`;
              
        //       if (data.title){
        //         urlAttachmentPreviewHTML += `<p class="card-title">${ data.title }</p>`;
        //       }
              
        //       if (data.description){
        //         urlAttachmentPreviewHTML += `<p class="card-subtitle mb-2 text-muted">${ data.description }</p>`;
        //       }
              
        //       urlAttachmentPreviewHTML += `</div>`;
              
        //       urlAttachmentPreview.innerHTML = urlAttachmentPreviewHTML;
        //       post.querySelector('.post-body-wrapper').appendChild(urlAttachmentPreview);
        //     }
            
        //   }, () => {
        //     postsWithAttachmentCount--;
        //     // console.log('postsWithAttachmentCount', postsWithAttachmentCount);
        //     if (postsWithAttachmentCount === 0){
        //       dispatchEvent('ftf_fediverse_embeds_posts_processed');
        //     }
        //   });                           
        // }
