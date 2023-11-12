const getPostData = (post) => {
    return {
        instance: post.dataset.instance,
        post_id: post.dataset.postId
    }
};

export { getPostData };
