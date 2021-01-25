import gql from 'graphql-tag';
import React, { useState } from 'react';
import { useMutation } from '@apollo/react-hooks';

import LazyImage from './LazyImage';

const ROTATE_POST_MUTATION = gql`
  mutation RotatePostMutation($id: Int!) {
    rotatePost(id: $id) {
      id
      url
    }
  }
`;

export const PostCard = ({ post }) => {
  const [cacheBuster, setCacheBuster] = useState('');
  const [rotatePost, { loading }] = useMutation(ROTATE_POST_MUTATION, {
    variables: { id: post.id },
    onCompleted: () => setCacheBuster('?' + Date.now()),
  });

  // If we've rotated the post, use a "cache buster" to force
  // the user's browser to reload the image from the edge.
  const url = `${post.url}${cacheBuster}`;

  return (
    <>
      {post.type == 'photo' ? (
        <LazyImage className="post-tile" alt="post image" src={url} />
      ) : (
        <div className="post-tile -fallback" />
      )}

      {post.type === 'photo' ? (
        <div className="clearfix">
          <div className="float-left">
            <a href={`/originals/${post.id}`} target="_blank">
              Original Photo
            </a>
          </div>
          <div className="float-right">
            {loading ? (
              <div className="spinner -small" />
            ) : (
              <a className="button -tertiary -rotate" onClick={rotatePost} />
            )}
          </div>
        </div>
      ) : null}
    </>
  );
};

export default PostCard;
