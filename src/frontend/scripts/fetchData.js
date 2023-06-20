const fetchData = async (data, cb, done) => {
  done = done || function(){ /* noop */ }

  await fetch(window.ftf_fediverse_embeds.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'Cache-Control': 'no-cache',
      },
      body: new URLSearchParams(data) })
      .then((response) => response.json())
      .then((response) => {
          // console.log('response', response);
          cb(response);
      })
      .catch((error) => {
          console.error('ftf_fediverse_embeds_error', error);
      })
      .then(done);  
};

export { fetchData };
