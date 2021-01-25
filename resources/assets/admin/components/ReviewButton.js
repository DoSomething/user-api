import React from 'react';
import gql from 'graphql-tag';
import classNames from 'classnames';
import { useMutation } from '@apollo/react-hooks';

export const ReviewButtonFragment = gql`
  fragment ReviewButton on Post {
    id
    status
  }
`;

const REVIEW_POST_MUTATION = gql`
  mutation ReviewPostMutation($id: Int!, $status: ReviewStatus!) {
    reviewPost(id: $id, status: $status) {
      ...ReviewButton
    }

    ${ReviewButtonFragment}
  }
`;

const ReviewButton = ({ post, status, children }) => {
  const [reviewPost, { loading }] = useMutation(REVIEW_POST_MUTATION, {
    variables: {
      id: post.id,
      status: status,
    },
    // We'll optimistically update the interface with the given status
    // before waiting for the full network round-trip. Snappy!
    optimisticResponse: {
      __typename: 'Mutation',
      reviewPost: {
        __typename: 'Post',
        id: post.id,
        status: status,
      },
    },
  });

  const statusClass = `-${status.toLowerCase()}`;

  return (
    <button
      className={classNames('button', '-outlined-button', statusClass, {
        'is-selected': post.status === status,
        'is-loading': loading,
      })}
      onClick={reviewPost}
    >
      {children}
    </button>
  );
};

export const AcceptButton = ({ post }) => (
  <ReviewButton post={post} status="ACCEPTED">
    Accept
  </ReviewButton>
);

export const RejectButton = ({ post }) => (
  <ReviewButton post={post} status="REJECTED">
    Reject
  </ReviewButton>
);

export default ReviewButton;
