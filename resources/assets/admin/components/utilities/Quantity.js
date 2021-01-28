import React from 'react';
import PropTypes from 'prop-types';

const Quantity = props => (
  <div className="container__row figure -left -center">
    <div className="figure__media">
      <div className="quantity">{props.quantity}</div>
    </div>
    <div className="figure__body">
      {props.noun && props.verb ? (
        <strong>
          {props.noun} {props.verb}
        </strong>
      ) : null}
    </div>
  </div>
);

Quantity.propTypes = {
  noun: PropTypes.string,
  // @TODO - figure out why this comes in two different ways.
  quantity: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
  verb: PropTypes.string,
};

Quantity.defaultProps = {
  noun: 'things',
  quantity: 0,
  verb: 'done',
};

export default Quantity;
