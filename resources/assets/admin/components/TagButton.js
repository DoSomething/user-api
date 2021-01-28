import React from 'react';
import gql from 'graphql-tag';
import { without } from 'lodash';
import classNames from 'classnames';
import { useMutation } from '@apollo/react-hooks';

export const TagButtonFragment = gql`
  fragment TagButton on Post {
    id
    tags
  }
`;

const TAG_POST_MUTATION = gql`
  mutation TagPostMutation($id: Int!, $tag: String!) {
    tagPost(id: $id, tag: $tag) {
      ...TagButton
    }

    ${TagButtonFragment}
  }
`;

const TagButton = ({ post, tag, label }) => {
  const hasTag = post.tags.includes(tag);

  const [tagPost] = useMutation(TAG_POST_MUTATION, {
    variables: {
      id: post.id,
      tag: tag,
    },
    // We'll optimistically update the interface with the given tag
    // before waiting for the full network round-trip. Snappy!
    optimisticResponse: {
      __typename: 'Mutation',
      tagPost: {
        __typename: 'Post',
        id: post.id,
        tags: hasTag ? without(post.tags, tag) : [...post.tags, tag],
      },
    },
  });

  return (
    <button
      className={classNames('tag', { 'is-active': hasTag })}
      onClick={tagPost}
    >
      {label}
    </button>
  );
};

export default TagButton;
