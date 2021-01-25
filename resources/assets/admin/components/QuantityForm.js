import gql from 'graphql-tag';
import classNames from 'classnames';
import React, { useState } from 'react';
import { useMutation } from '@apollo/react-hooks';

export const QuantityFormFragment = gql`
  fragment QuantityForm on Post {
    id
    quantity
    impact
  }
`;

const UPDATE_QUANTITY_MUTATION = gql`
  mutation UpdateQuantityMutation($id: Int!, $quantity: Int!) {
    updatePostQuantity(id: $id, quantity: $quantity) {
      ...QuantityForm
    }

    ${QuantityFormFragment}
  }
`;

const isInteger = string => /^\d+$/.test(string);

const QuantityForm = ({ post }) => {
  const [newQuantity, setNewQuantity] = useState(post.quantity);
  const [updateQuantity, { loading }] = useMutation(UPDATE_QUANTITY_MUTATION, {
    variables: { id: post.id },
  });

  const handleSubmit = () =>
    updateQuantity({ variables: { quantity: Number(newQuantity) } });

  return (
    <>
      <div className="modal__block">
        <h3>Edit Quantity</h3>
      </div>
      <div className="modal__block">
        <div className="container__block -half">
          <h4>Current Quantity</h4>
          <input
            type="text"
            className="text-field"
            value={post.impact}
            disabled
          />
        </div>
        <div className="container__block -half">
          <h4>New Quantity</h4>
          <div className="form-item">
            <input
              type="number"
              onChange={event => setNewQuantity(event.target.value)}
              className="text-field"
              value={newQuantity}
            />
          </div>
        </div>
      </div>

      <button
        className={classNames('button -attached', { 'is-loading': loading })}
        disabled={!isInteger(newQuantity)}
        onClick={handleSubmit}
      >
        Save
      </button>
    </>
  );
};

export default QuantityForm;
