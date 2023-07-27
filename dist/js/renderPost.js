const renderPost=(post,container)=>{container||(container=document.querySelector("blockquote[data-instance=\"".concat(post.instance,"\"][data-post-id=\"").concat(post.post_id,"\"]")));// post.post_data = JSON.parse(post.post_data);
// console.log('post.post_data', post.post_data);
// console.log('debug:renderPost', {data, container});
// if (post.post_data.post_id === '123456789'){
// console.log('debug:renderPost', data);
// }
let postHasLabels=!1,postIsDeleted=!1,postIsUpdated=!1,accountIsBot=!1,accountIsOwner=!1;if(ftf_fediverse_embeds.config.show_post_labels&&post.post_data.account.roles){const accountRoles=post.post_data.account.roles.map(role=>role.name);accountRoles.includes("Owner")&&(accountIsOwner=!0,postHasLabels=!0)}post.post_data.account.bot&&(accountIsBot=!0,postHasLabels=!0),post.__status&&"deleted"===post.__status&&(postIsDeleted=!0,postHasLabels=!0),post.post_data.edited_at&&(postIsUpdated=!0,postHasLabels=!0);let postText=post.post_data.content,postUrl=post.post_data.url,renderedPostHTML="\n        <div class=\"card w-100\">\n          <div class=\"post-body-wrapper card-body pt-4 pb-0\">\n            <div class=\"fediverse-post-labels position-absolute top-0 end-0 mt-1 me-1\">\n      ";if(post.post_data.emojis&&post.post_data.emojis.forEach(emoji=>{postText=postText.replaceAll(":".concat(emoji.shortcode,":"),"<img class=\"fediverse-post-emoji\" src=\"".concat(window.ftf_fediverse_embeds.blog_url,"/wp-json/ftf/media-proxy?url=").concat(window.btoa(emoji.url),"\" />"))}),postHasLabels&&(renderedPostHTML+="<span class=\"badge rounded-pill text-bg-light\">",postIsUpdated&&(renderedPostHTML+="\n      <span\n        class=\"p-0\"\n        title=\"This post was updated.\"\n        aria-label=\"Updated post\"\n      >\uD83D\uDCDD</span>\n    "),postIsDeleted&&(renderedPostHTML+="\n      <span\n        class=\"p-0\"\n        title=\"This post was deleted.\"\n        aria-label=\"Deleted post\"\n      >\uD83D\uDDD1\uFE0F</span>\n    "),accountIsBot&&(renderedPostHTML+="\n      <span\n        class=\"p-0\"\n        title=\"This is a bot account.\"\n        aria-label=\"Bot account\"\n      >\uD83E\uDD16</span>\n    "),accountIsOwner&&(renderedPostHTML+="\n      <span\n        class=\"p-0\"\n        title=\"This is an admin account.\"\n        aria-label=\"Admin account\"\n      >\uD83D\uDC51</span>\n    "),renderedPostHTML+="</span>"),renderedPostHTML+="\n    </div>\n    <div class=\"card-text\">\n    <div class=\"row no-gutters mb-1\">\n  ",post.post_data.account.avatar_static&&(renderedPostHTML+="\n      <div class=\"fediverse-post-profile-image-wrapper col-2 col-sm-2 col-md-2 p-2 pt-0 ps-sm-1 pe-sm-1 ps-md-2 pe-md-1 ps-lg-3 pe-lg-1\">\n        <a href=\"".concat(post.post_data.account.url,"\" class=\"text-decoration-none\">\n          <img\n            title=\"Profile image\"\n            alt=\"Profile image of @").concat(post.post_data.account.display_name||post.post_data.account.username,"\"\n            loading=\"lazy\"\n            class=\"post-author-image rounded-circle border\"\n            width=\"48\"\n            height=\"48\"\n            src=\"").concat(window.ftf_fediverse_embeds.blog_url,"/wp-json/ftf/media-proxy?url=").concat(window.btoa(post.post_data.account.avatar_static),"\"\n          >\n        </a>\n      </div>")),renderedPostHTML+="\n    <div class=\"post-author ".concat(post.post_data.account.avatar_static?"col-10 col-sm-10 col-md-10 pl-2":"col-12 col-sm-12 col-md-12"," pb-3\">\n      <p class=\"font-weight-bold mb-0 mt-0\">\n        <a\n          class=\"text-dark text-decoration-none\"\n          href=\"").concat(post.post_data.account.url,"\"\n        >").concat(post.post_data.account.display_name,"</a>\n      </p>\n      <p class=\"mb-1 mb-md-2 mt-0 fs-6\">\n        <a class=\"text-muted text-decoration-none\" href=\"").concat(post.post_data.account.url,"\">\n          @").concat(post.post_data.account.username,"@").concat(post.instance,"\n        </a>\n      </p>\n  </div>\n  </div>\n  <div class=\"post-body\">"),post.post_data.card,post.post_data.media_attachments&&post.post_data.media_attachments.length)postText+="<div data-media-length=\"".concat(post.post_data.media_attachments.length,"\" class=\"post-media row mt-3 no-gutters\">"),post.post_data.media_attachments.forEach((media,index)=>{postText+=1===post.post_data.media_attachments.length?"<div data-media-type=\"".concat(media.type,"\" class=\"text-center col-sm-12 col-md-12 col-lg-12\">"):3===post.post_data.media_attachments.length?2===index?"<div data-media-type=\"".concat(media.type,"\" class=\"text-center col-sm-12 col-md-12 col-lg-12\">"):"<div data-media-type=\"".concat(media.type,"\" class=\"text-center col-sm-12 col-md-6 col-lg-6\">"):1<post.post_data.media_attachments.length&&5>post.post_data.media_attachments.length?"<div data-media-type=\"".concat(media.type,"\" class=\"text-center col-sm-12 col-md-6 col-lg-6\">"):"<div data-media-type=\"".concat(media.type,"\" class=\"text-center col-sm-12 col-md-3 col-lg-3\">"),"gifv"===media.type?postText+="<video class=\"w-100 mt-0\" controls loop>\n          <source src=\"".concat(window.ftf_fediverse_embeds.blog_url,"/wp-json/ftf/media-proxy?url=").concat(window.btoa(media.url),"\" type=\"video/mp4\">\n        </video>"):"video"===media.type?media.url&&(postText+="<video class=\"w-100 mt-0\" controls loop>\n            <source\n              src=\"".concat(window.ftf_fediverse_embeds.blog_url,"/wp-json/ftf/media-proxy?url=").concat(window.btoa(media.url),"\"\n              type=\"video/mp4\"\n            >\n            </video>")):"audio"===media.type?media.url&&(postText+="\n            <audio\n              controls\n              src=\"".concat(window.ftf_fediverse_embeds.blog_url,"/wp-json/ftf/media-proxy?url=").concat(window.btoa(media.url),"\"\n              class=\"w-100 mt-3 mb-3\"\n            ></audio>\n          ")):"image"===media.type&&(postText+="<a href=\"".concat(postUrl,"\" target=\"_blank\">\n          <img\n            alt=\"").concat(media.alt_text||"","\"\n            loading=\"lazy\"\n            width=\"").concat(media.width,"\"\n            height=\"").concat(media.height,"\"\n            class=\"w-100 rounded border mb-3\"\n            src=\"").concat(window.ftf_fediverse_embeds.blog_url,"/wp-json/ftf/media-proxy?url=").concat(window.btoa(media.url),"\"\n          >\n        </a>")),postText+="</div>"}),postText+="</div>";else if(post.post_data.card){let cardSource;post.post_data.card.url&&(cardSource=new URL(post.post_data.card.url),cardSource=cardSource.hostname),postText+=post.post_data.card.html?"<div class=\"ratio ratio-16x9\">".concat(post.post_data.card.html,"</div>"):post.post_data.card.image?"\n      <div class=\"card mb-4\">\n        <a href=\"".concat(post.post_data.card.url,"\">\n          <img src=\"").concat(window.ftf_fediverse_embeds.blog_url,"/wp-json/ftf/media-proxy?url=").concat(window.btoa(post.post_data.card.image),"\" class=\"card-img-top\" alt=\"...\">\n        </a>\n        <div class=\"card-body pb-1\">\n          <h5 class=\"card-title\">\n            <a href=\"").concat(post.post_data.card.title,"\">\n              ").concat(post.post_data.card.title,"\n            </a>\n          </h5>\n          <p class=\"card-text\"><small>").concat(post.post_data.card.description,"</small></p>\n        </div>\n        <div class=\"card-footer pb-3 pt-0\">\n          <small class=\"text-muted\">\n            ").concat(cardSource,"\n          </div>\n      </div>\n      "):"\n      <div class=\"card mb-4\">\n      <div class=\"card-body pb-1\">\n          <h5 class=\"card-title\">\n            <a href=\"".concat(post.post_data.card.title,"\">\n              ").concat(post.post_data.card.title,"\n            </a>\n          </h5>\n          <p class=\"card-text\"><small>").concat(post.post_data.card.description,"</small></p>\n        </div>\n        <div class=\"card-footer pb-3 pt-0\">\n          <small class=\"text-muted\">\n            ").concat(cardSource,"\n          </div>\n      </div>\n      ")}if(post.post_data.poll){if(postText+="<div class=\"mt-0 post-poll-results\">",post.post_data.poll.options&&post.post_data.poll.options.length){const voteCounts=post.post_data.poll.options.map(option=>option.votes_count),voteCountMax=Math.max(...voteCounts),votesTotal=voteCounts.reduce((total,num)=>total+num);postText+="<div class=\"row\">";const pollOptionsCount=post.post_data.poll.options;post.post_data.poll.options.forEach((option,index)=>{const votesPortion=100*(option.votes_count/votesTotal);postText+="\n          <div class=\"col-9\">\n            <p class=\"mb-2\">".concat(option.title,"</p>\n          </div>\n          <div class=\"col-3 text-end\">\n            <span class=\"w-100\">").concat(Math.round(100*(option.votes_count/votesTotal)),"%</span>\n          </div>\n          <div class=\"col-12\">\n            <div\n              class=\"progress ").concat(index===pollOptionsCount-1?"":" mb-3 ","\"\n            >\n              <div class=\"progress-bar ").concat(option.votes_count===voteCountMax?"bg-primary":"","\" \n                role=\"progressbar\" \n                style=\"width: ").concat(votesPortion,"%\" \n                aria-valuenow=\"").concat(votesPortion,"\" \n                aria-valuemin=\"0\" \n                aria-valuemax=\"100\">\n              </div>\n            </div>\n          </div>\n      ")}),postText+="<div class=\"col-12 mt-3\">\n        <p class=\"text-muted\">\n          <small>\n            ".concat(votesTotal.toLocaleString()," votes | ").concat(post.post_data.poll.expired?"Closed":"Open","\n          </small>\n        </p>\n      </div>")}postText+="</div></div>"}const postDate=new Date(post.post_data.created_at),postDateDate=postDate.toLocaleDateString(navigator.language,{month:"long",year:"numeric",day:"numeric"}),postDateTime=postDate.toLocaleTimeString();let editDate,editDateText;if(postIsUpdated){editDate=new Date(post.post_data.edited_at);const editDateDate=editDate.toLocaleDateString(navigator.language,{month:"long",year:"numeric",day:"numeric"}),editDateTime=editDate.toLocaleTimeString();editDateText="Updated on ".concat(editDateDate," at ").concat(editDateTime)}renderedPostHTML+=postText+"</div>\n      </div>\n    </div>\n  <div class=\"card-footer pb-3\"><small>",ftf_fediverse_embeds.config.show_metrics&&(renderedPostHTML+="\n        <small>\n          <span class=\"post-icon\" role=\"img\" aria-label=\"Reposts\">\uD83D\uDD01</span>\n          <span class=\"text-muted\">".concat(post.post_data.reblogs_count.toLocaleString(),"</span>\n          \n          <span class=\"post-icon\" role=\"img\" aria-label=\"Likes\">\u2764\uFE0F</span>\n          <span class=\"text-muted\">").concat(post.post_data.favourites_count.toLocaleString(),"</span>\n          \n          <span class=\"post-icon\" role=\"img\" aria-label=\"Replies\">\uD83D\uDCAC</span>\n          <span class=\"text-muted\">").concat(post.post_data.replies_count.toLocaleString(),"</span>\n        </small>\n        &centerdot; ")),renderedPostHTML+="<a class=\"text-muted\" href=\"".concat(postUrl,"\" target=\"_blank\">\n    <small title=\"").concat(editDate?editDateText:"","\">\n      ").concat(postDateDate," at ").concat(postDateTime).concat(editDate?"*":"","\n    </small>\n  </a>"),renderedPostHTML+="</small></div></div>";let renderedPost=document.createElement("div");renderedPost.className="fediverse-post fediverse-post-rendered w-100 mt-4 mb-4",renderedPost.innerHTML=renderedPostHTML;let lastUrl="";if(post.entities&&post.entities.urls&&post.entities.urls.length&&(lastUrl=post.entities.urls[post.entities.urls.length-1]),(post.post_data.media_attachments&&post.post_data.media_attachments.length||post.extended_entities&&post.extended_entities.media&&post.extended_entities.media.length)&&(lastUrl=""),lastUrl&&(renderedPost.dataset.urlAttachment=lastUrl.expanded_url,renderedPost.dataset.urlAttachmentProcessed="false"),container){container.querySelector(".post-body a:last-of-type");container.parentNode.replaceChild(renderedPost,container)}else{const post=document.querySelector("[data-post-id=\"".concat(post.id,"\"]"));post.parentNode.replaceChild(renderedPost,post)}return renderedPost};export{renderPost};
//# sourceMappingURL=renderPost.js.map
