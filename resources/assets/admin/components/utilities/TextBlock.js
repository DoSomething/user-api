import React from 'react';
import PropTypes from 'prop-types';

const TextBlock = props => (
  <div className="container">
    <h4 className="heading">{props.title}</h4>
    <p>{props.content}</p>
  </div>
);

TextBlock.propTypes = {
  title: PropTypes.string.isRequired,
  content: PropTypes.string,
};

TextBlock.defaultProps = {
  content: null,
};

export default TextBlock;
