"use strict";

(() => {
  const escapeHTML = (str) => {
    const div = document.createElement("div");
    div.innerText = str;
    return div.innerHTML;
  };

  if (window.ftf_fediverse_embeds && window.ftf_fediverse_embeds.admin) {
    document.addEventListener(
      "error",
      (ev) => {
        if (ev.target.tagName === "IMG") {
          const embed = ev.target.closest(".fediverse-post");
          if (embed && !embed.dataset.ftfMediaError && !embed.dataset.postDeleted) {
            let domain = "";
            try {
              const proxyUrl = new URL(ev.target.src);
              const encoded = proxyUrl.searchParams.get("url");
              if (encoded) {
                domain = new URL(atob(encoded.replace(/ /g, "+"))).hostname;
              }
            } catch (err) {}

            if (domain && window.ftf_fediverse_embeds.admin.allowed_domains && window.ftf_fediverse_embeds.admin.allowed_suffixes) {
              if (window.ftf_fediverse_embeds.admin.allowed_domains.includes(domain)) {
                return;
              }
              for (const suffix of window.ftf_fediverse_embeds.admin.allowed_suffixes) {
                if (domain.endsWith(suffix)) {
                  return;
                }
              }
            }

            const domainHTML = domain
              ? ` from <code>${escapeHTML(domain)}</code>`
              : "";
            const cardBody = document.createElement("div");
            cardBody.className = "card-body py-2 px-3";
            cardBody.innerHTML = /* html */ `
                <h6 class="card-title mb-1">Admin note</h6>
                <p class="card-text mt-0 mb-2">
                    <small>Media${domainHTML} was blocked.</small>
                </p>
                <a href="${escapeHTML(window.ftf_fediverse_embeds.admin.settings_url)}" target="_blank" rel="noopener noreferrer"><small>Update settings</small></a>
            `;
            const notice = document.createElement("div");
            notice.className =
              "ftf-admin-media-notice card w-100 mt-2 border-1";
            notice.style.wordBreak = "break-all";
            notice.appendChild(cardBody);
            embed.dataset.ftfMediaError = "1";
            embed.after(notice);
          }
        }
      },
      true,
    );
  }
})();
