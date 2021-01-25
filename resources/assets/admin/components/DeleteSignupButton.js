import React from 'react';
import gql from 'graphql-tag';
import { useMutation } from '@apollo/react-hooks';

const DELETE_SIGNUP_MUTATION = gql`
  mutation DeleteSignupMutation($id: Int!) {
    deleteSignup(id: $id) {
      id
      deleted
    }
  }
`;

const DeleteSignupButton = ({ signup }) => {
  const [deleteSignup] = useMutation(DELETE_SIGNUP_MUTATION, {
    variables: {
      id: signup.id,
    },
  });

  const handleClick = () => {
    const warning = 'Are you sure you want to delete this signup? 🔥';

    if (confirm(warning)) {
      deleteSignup();
    }
  };

  return (
    <button className="button -tertiary text-red" onClick={handleClick}>
      Delete Signup
    </button>
  );
};

export default DeleteSignupButton;
