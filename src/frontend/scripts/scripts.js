'use strict';
import { ready } from "./ready.js";
import { processPosts } from "./processPosts.js";

ready(function() {
  processPosts();

  document.addEventListener('click', (ev) => {
    if (ev.target.classList.contains('ftf-fediverse-post-alt-text') && ev.target.title) {
      ev.preventDefault();
      alert(ev.target.title);
    }
  });
});
