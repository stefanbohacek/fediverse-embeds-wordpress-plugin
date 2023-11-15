const renderPost = (post, container) => {
  if (!container){
    container = document.querySelector(`blockquote[data-instance="${ post.instance }"][data-post-id="${ post.post_id }"]`)
  }

  // post.post_data = JSON.parse(post.post_data);

  // console.log('post.post_data', post.post_data);
  // console.log('debug:renderPost', {data, container});

  // if (post.post_data.post_id === '123456789'){
  // console.log('debug:renderPost', data);
  // }

  let postHasLabels = false;
  let postIsDeleted = false;
  let postIsUpdated = false;

  let accountIsBot = false;
  let accountIsOwner = false;


  let renderedPost = document.createElement('div');
  let renderedPostHTML = '';

  if (postIsDeleted && ftf_fediverse_embeds.config.deleted_posts === 'redact'){
    post.post_data.account = {};
  }  

  if (ftf_fediverse_embeds.config.show_post_labels && post.post_data.account.roles){
    const accountRoles = post.post_data.account.roles.map(role => role.name);
    if (accountRoles.includes('Owner')){
      accountIsOwner = true;
      postHasLabels = true;
    }
  }

  if (post.post_data.account.bot){
    accountIsBot = true;
    postHasLabels = true;
  }

  if (post.__status){
    if (post.__status === 'deleted'){
      postIsDeleted = true;
      postHasLabels = true;
    }
  }

  if (postIsDeleted && ftf_fediverse_embeds.config.deleted_posts === 'hide'){
    renderedPost.className = `fediverse-post fediverse-post-deleted`;

    renderedPostHTML += `
    <blockquote
      data-instance="$instance"
      data-post-id="$post_id"
      class="ftf-fediverse-post-embed-removed"
    >
      <p>This post by @${ post.post_data.account.username}@${ post.instance } was removed.</p>
    </blockquote>
    `;
  } else {
    renderedPost.className = `fediverse-post fediverse-post-rendered w-100 mt-4 mb-4`;

    if (post.post_data.edited_at){
      postIsUpdated = true;
      postHasLabels = true;
    }
  
    let postText = post.post_data.content,
        postUrl = post.post_data.url,
        entities = null;

    renderedPostHTML += `
          <div class="card w-100">
            <div class="post-body-wrapper card-body pt-4 pb-0">
              <div class="fediverse-post-labels position-absolute top-0 end-0 mt-1 me-1">
        `;
  
    if (post.post_data.emojis){
      post.post_data.emojis.forEach(emoji => {
        postText = postText.replaceAll(`:${emoji.shortcode}:`, `<img class="fediverse-post-emoji" src="${window.ftf_fediverse_embeds.blog_url}/wp-json/ftf/media-proxy?url=${ window.btoa(emoji.url) }" />`);
      })
    }
  
    if (postHasLabels){
      renderedPostHTML += `<span class="badge rounded-pill text-bg-light">`;
  
      if (postIsUpdated){
        renderedPostHTML += `
        <span
          class="p-0"
          title="This post was updated."
          aria-label="Updated post"
        >📝</span>
      `
      }
      if (postIsDeleted){
        renderedPostHTML += `
        <span
          class="p-0"
          title="This post was deleted."
          aria-label="Deleted post"
        >🗑️</span>
      `
      }
  
      if (accountIsBot){
        renderedPostHTML += `
        <span
          class="p-0"
          title="This is a bot account."
          aria-label="Bot account"
        >🤖</span>
      `
      }
  
      if (accountIsOwner){
        renderedPostHTML += `
        <span
          class="p-0"
          title="This is an admin account."
          aria-label="Admin account"
        >👑</span>
      `
      }
  
      renderedPostHTML += `</span>`;
    }
          
    renderedPostHTML += `
      </div>
      <div class="card-text">
      <div class="row no-gutters mb-1">
    `;
  
    // console.log('debug:post', post);
   
    if (post.post_data.account.avatar_static){
      if (postIsDeleted && ftf_fediverse_embeds.config.deleted_posts === 'redact'){
        // noop
      } else {
        renderedPostHTML += `
        <div class="fediverse-post-profile-image-wrapper col-2 col-sm-2 col-md-2 p-2 pt-0 ps-sm-1 pe-sm-1 ps-md-2 pe-md-1 ps-lg-3 pe-lg-1">
          <a href="${ post.post_data.account.url }" class="text-decoration-none">
            <img
              title="Profile image"
              alt="Profile image of @${ post.post_data.account.display_name || post.post_data.account.username }"
              loading="lazy"
              class="post-author-image rounded-circle border"
              width="48"
              height="48"
              src="${window.ftf_fediverse_embeds.blog_url}/wp-json/ftf/media-proxy?url=${ window.btoa(post.post_data.account.avatar_static) }"
            >
          </a>
        </div>`;
      }
    }
  
    if (postIsDeleted && ftf_fediverse_embeds.config.deleted_posts === 'redact'){
      renderedPostHTML += `
      <div class="post-authorcol-12 col-sm-12 col-md-12 pb-3">
        <p class="font-weight-bold mb-0 mt-0"></p>
      </div>
    `;
    } else {
      renderedPostHTML += `
      <div class="post-author ${ 
        post.post_data.account.avatar_static ? 'col-10 col-sm-10 col-md-10 pl-2' : 'col-12 col-sm-12 col-md-12'
      } pb-3">
        <p class="font-weight-bold mb-0 mt-0">
          <a
            class="text-dark text-decoration-none"
            href="${ post.post_data.account.url }"
          >${ post.post_data.account.display_name }</a>
        </p>
        <p class="mb-1 mb-md-2 mt-0 fs-6">
          <a class="text-muted text-decoration-none" href="${ post.post_data.account.url }">
            @${ post.post_data.account.username}@${ post.instance }
          </a>
        </p>
      </div>
    `;
    }
  
    renderedPostHTML += `
    </div>
    <div class="post-body">`;
  
    if (post.post_data.card){
      // console.log('debug:post.post_data.card', post.post_data.card);
    }
    
    if (post.post_data.media_attachments && post.post_data.media_attachments.length){
      postText += `<div data-media-length="${ post.post_data.media_attachments.length }" class="post-media row mt-3 no-gutters">`;
      
      post.post_data.media_attachments.forEach((media, index) => {
        // console.log('debug:media', media);
  
        if (post.post_data.media_attachments.length === 1){
          postText += `<div data-media-type="${ media.type }" class="text-center col-sm-12 col-md-12 col-lg-12">`;
        } else if (post.post_data.media_attachments.length === 3){
          if (index === 2){
            postText += `<div data-media-type="${ media.type }" class="text-center col-sm-12 col-md-12 col-lg-12">`;
          } else {
            postText += `<div data-media-type="${ media.type }" class="text-center col-sm-12 col-md-6 col-lg-6">`;
          }
        } else if (post.post_data.media_attachments.length > 1 && post.post_data.media_attachments.length < 5){
          postText += `<div data-media-type="${ media.type }" class="text-center col-sm-12 col-md-6 col-lg-6">`;
        } else {
          postText += `<div data-media-type="${ media.type }" class="text-center col-sm-12 col-md-3 col-lg-3">`;
        }
        
        if (media.type === 'gifv'){
          postText += `<video class="w-100 mt-0" controls loop>
            <source src="${window.ftf_fediverse_embeds.blog_url}/wp-json/ftf/media-proxy?url=${ window.btoa(media.url) }" type="video/mp4">
          </video>`;
  
          // <source src="${ media.url }" type="video/mp4">
          // <source src="${window.ftf_fediverse_embeds.blog_url}/wp-json/ftf/media-proxy?url=${ window.btoa(media.url) }" type="video/mp4">
        } else if (media.type === 'video'){
          if (media.url){
            // src="${window.ftf_fediverse_embeds.blog_url}/wp-json/ftf/media-proxy?url=${ window.btoa(media.url) }"
            // src="${ media.url }"
  
            postText += `<video class="w-100 mt-0" controls loop>
              <source
                src="${window.ftf_fediverse_embeds.blog_url}/wp-json/ftf/media-proxy?url=${ window.btoa(media.url) }"
                type="video/mp4"
              >
              </video>`
          }
        } else if (media.type === 'audio'){
          if (media.url){
            // src="${window.ftf_fediverse_embeds.blog_url}/wp-json/ftf/media-proxy?url=${ window.btoa(media.url) }"
            // src="${ media.url }"
  
            postText += `
              <audio
                controls
                src="${window.ftf_fediverse_embeds.blog_url}/wp-json/ftf/media-proxy?url=${ window.btoa(media.url) }"
                class="w-100 mt-3 mb-3"
              ></audio>
            `;
          }
  
        } else if (media.type === 'image'){
          postText += `<a href="${ postUrl }" target="_blank">
            <img
              alt="${ media.alt_text || '' }"
              loading="lazy"
              width="${ media.width }"
              height="${ media.height }"
              class="w-100 rounded border mb-3"
              src="${window.ftf_fediverse_embeds.blog_url}/wp-json/ftf/media-proxy?url=${ window.btoa(media.url) }"
            >
          </a>`;
        }
        
        postText += '</div>';
      });
      
      postText += '</div>';
    } else if (post.post_data.card){
      let cardSource;
  
      if (post.post_data.card.url){
        cardSource = (new URL(post.post_data.card.url));
        cardSource = cardSource.hostname;
      }
  
      if (post.post_data.card.html){
        postText += `<div class="ratio ratio-16x9">${ post.post_data.card.html}</div>`;
      } else if (post.post_data.card.image){
        postText += `
        <div class="card mb-4">
          <a href="${ post.post_data.card.url }">
            <img src="${window.ftf_fediverse_embeds.blog_url}/wp-json/ftf/media-proxy?url=${ window.btoa(post.post_data.card.image) }" class="card-img-top" alt="...">
          </a>
          <div class="card-body pb-1">
            <h5 class="card-title">
              <a href="${ post.post_data.card.url }">
                ${ post.post_data.card.title }
              </a>
            </h5>
            <p class="card-text"><small>${ post.post_data.card.description }</small></p>
          </div>
          <div class="card-footer pb-3 pt-0">
            <small class="text-muted">
              ${ cardSource }
            </div>
        </div>
        `
      } else {
        postText += `
        <div class="card mb-4">
        <div class="card-body pb-1">
            <h5 class="card-title">
              <a href="${ post.post_data.card.url }">
                ${ post.post_data.card.title }
              </a>
            </h5>
            <p class="card-text"><small>${ post.post_data.card.description }</small></p>
          </div>
          <div class="card-footer pb-3 pt-0">
            <small class="text-muted">
              ${ cardSource }
            </div>
        </div>
        `
      }
    }
    
    if (post.post_data.poll){
      postText += '<div class="mt-0 post-poll-results">';
  
      if (post.post_data.poll.options && post.post_data.poll.options.length){
          
        const voteCounts = post.post_data.poll.options.map((option) => {
          return option.votes_count;
        });
        
        const voteCountMax = Math.max(...voteCounts);
        const votesTotal = voteCounts.reduce((total, num) => {
          return total + num;
        });
        
        postText += `<div class="row">`;
  
        const pollOptionsCount = post.post_data.poll.options;

        post.post_data.poll.options.forEach((option, index) => {
          let votesPortion;
          let votesPercentage;
          // votesPercentage = post.post_data.poll.expired ? `${Math.round(option.votes_count/votesTotal * 100)}%` : '';

          if (post.post_data.poll.multiple){
            votesPercentage = `${Math.round(option.votes_count/post.post_data.poll.voters_count * 100)}%`;
            votesPortion = option.votes_count/post.post_data.poll.voters_count * 100;
          } else {
            votesPercentage = `${Math.round(option.votes_count/votesTotal * 100)}%`;
            votesPortion = option.votes_count/votesTotal * 100;
          }
  
          postText += `
            <div class="col-9">
              <p class="mb-2">${ option.title }</p>
            </div>
            <div class="col-3 text-end">
              <span class="w-100">${ votesPercentage }</span>
            </div>
            <div class="col-12">
              <div
                class="progress ${ 
                  index === pollOptionsCount - 1 ? '' : ' mb-3 '
                }"
              >
                <div class="progress-bar ${ 
                  // post.post_data.poll.expired && option.votes_count === voteCountMax ? 'bg-primary' : ''
                  post.post_data.poll.expired && option.votes_count === voteCountMax ? 'bg-primary' : ''
                }" 
                  role="progressbar" 
                  xxxstyle="width: ${ post.post_data.poll.expired ? votesPortion : 0 }%" 
                  xxxaria-valuenow="${ post.post_data.poll.expired ? votesPortion : 0 }" 
                  style="width: ${ votesPortion }%" 
                  aria-valuenow="${ votesPortion }" 
                  aria-valuemin="0" 
                  aria-valuemax="100">
                </div>
              </div>
            </div>
        `;
        });
        postText += `<div class="col-12 mt-3">
          <p class="text-muted">
            <small>
              ${ votesTotal.toLocaleString() } votes | ${ post.post_data.poll.expired ? 'Closed' : 'Open'}
            </small>
          </p>
        </div>`;
      }    
  
      postText += '</div></div>';
    }
    
    const postDate = new Date(post.post_data.created_at);
    const postDateDate = postDate.toLocaleDateString(navigator.language, { month: 'long', year: 'numeric', day: 'numeric' });
    const postDateTime = postDate.toLocaleTimeString();
    
    let editDate, editDateText;
  
    if (postIsUpdated){
      editDate = new Date(post.post_data.edited_at);    
      const editDateDate = editDate.toLocaleDateString(navigator.language, { month: 'long', year: 'numeric', day: 'numeric' });
      const editDateTime = editDate.toLocaleTimeString();
      editDateText = `Updated on ${ editDateDate } at ${ editDateTime }`
    }
    
    renderedPostHTML += postText + `</div>
        </div>
      </div>
    <div class="card-footer pb-3"><small>`;
    
    // if (!container){
      if (ftf_fediverse_embeds.config.show_metrics){
  
        if (postIsDeleted && ftf_fediverse_embeds.config.deleted_posts === 'redact'){
        } else {
          renderedPostHTML += `
          <small>
            <span class="post-icon" role="img" aria-label="Reposts">🔁</span>
            <span class="text-muted">${ post.post_data.reblogs_count.toLocaleString() }</span>
            
            <span class="post-icon" role="img" aria-label="Likes">❤️</span>
            <span class="text-muted">${ post.post_data.favourites_count.toLocaleString() }</span>
            
            <span class="post-icon" role="img" aria-label="Replies">💬</span>
            <span class="text-muted">${ post.post_data.replies_count.toLocaleString() }</span>
          </small>
          &centerdot; `;
        }
      }
    // }
  
    if (postIsDeleted && ftf_fediverse_embeds.config.deleted_posts === 'redact'){
      renderedPostHTML += `
      <small>
        This post was deleted.
      </small>
    `;
    } else {
      renderedPostHTML += `<a class="text-muted" href="${ postUrl }" target="_blank">
        <small title="${ editDate ? editDateText : '' }">
          ${ postDateDate } at ${ postDateTime }${ editDate ? '*' :'' }
        </small>
      </a>`;
    }
    
    renderedPostHTML += '</small></div></div>';
  }

  renderedPost.innerHTML = renderedPostHTML;
  
  let lastUrl = '';
  
  if (post.entities && post.entities.urls && post.entities.urls.length){
    lastUrl = post.entities.urls[post.entities.urls.length - 1];
  }
  
  if ((post.post_data.media_attachments && post.post_data.media_attachments.length) || post.extended_entities && post.extended_entities.media && post.extended_entities.media.length){
    lastUrl = '';
  }
  
  if (lastUrl){
    renderedPost.dataset.urlAttachment = lastUrl.expanded_url;
    renderedPost.dataset.urlAttachmentProcessed = 'false';
  }
  
  if (container){
    const postContainer = container.querySelector('.post-body a:last-of-type');
    container.parentNode.replaceChild(renderedPost, container);
  } else {
    const post = document.querySelector(`[data-post-id="${ post.id }"]`);
    post.parentNode.replaceChild(renderedPost, post);
  }

  return renderedPost;
};

export { renderPost };
