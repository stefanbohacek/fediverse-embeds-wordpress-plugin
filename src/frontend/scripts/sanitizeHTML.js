const TRUSTED_IFRAME_ORIGINS = [
  "bandcamp.com",
  "dailymotion.com",
  "soundcloud.com",
  "spotify.com",
  "twitch.tv",
  "vimeo.com",
  "youtu.be",
  "youtube.com",
];

const isTrustedIframeOrigin = (src) => {
  try {
    const { hostname } = new URL(src);
    return TRUSTED_IFRAME_ORIGINS.some(
      (trusted) => hostname === trusted || hostname.endsWith("." + trusted),
    );
  } catch {
    return false;
  }
};

const escapeText = (str) => DOMPurify.sanitize(str || "", { ALLOWED_TAGS: [] });

const safeURL = (url) => {
  if (!url) return "";
  try {
    const { protocol } = new URL(url);
    return protocol === "http:" || protocol === "https:" ? url : "";
  } catch {
    return "";
  }
};

const sanitizeHTML = (html, allowIframes = false) => {
  if (!html) return "";

  if (allowIframes) {
    const sanitized = DOMPurify.sanitize(html, {
      ADD_TAGS: ["iframe"],
      ADD_ATTR: ["allow", "allowfullscreen", "frameborder", "scrolling"],
    });
    const doc = new DOMParser().parseFromString(sanitized, "text/html");
    doc.querySelectorAll("iframe").forEach((iframe) => {
      if (!isTrustedIframeOrigin(iframe.src)) iframe.remove();
    });
    return doc.body.innerHTML;
  }

  return DOMPurify.sanitize(html);
};

export { sanitizeHTML, escapeText, safeURL };
