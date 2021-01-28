import React from 'react';
import gql from 'graphql-tag';
import { useMutation } from '@apollo/react-hooks';

const DELETE_POST_MUTATION = gql`
  mutation DeletePostMutation($id: Int!) {
    deletePost(id: $id) {
      id
      deleted
    }
  }
`;

const DeletePostButton = ({ post }) => {
  const [deletePost] = useMutation(DELETE_POST_MUTATION, {
    variables: {
      id: post.id,
    },
  });

  const handleClick = () => {
    const warning = 'Are you sure you want to permanently delete this post? 🔥';

    if (confirm(warning)) {
      deletePost();
    }
  };

  return (
    <button className="button -tertiary text-red" onClick={handleClick}>
      Delete
    </button>
  );
};

export default DeletePostButton;
