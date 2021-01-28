import React from 'react';

import './post-tile.scss';

const PostTile = ({ post }) => {
  const altText = `Post #${post.id}`;

  if (!post.url) {
    return <div className="post-tile -fallback" />;
  }

  return <img className="post-tile" alt={altText} src={post.url} />;
};

export default PostTile;
