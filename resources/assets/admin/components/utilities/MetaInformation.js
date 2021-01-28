import React from 'react';
import { map } from 'lodash';
import PropTypes from 'prop-types';

const MetaInformation = props => (
  <div className="container">
    {props.title ? <h4 className="heading">{props.title}</h4> : null}
    {map(props.details, (item, key) => (
      <span key={key}>
        <strong>{key}:</strong> {item}
        <br />
      </span>
    ))}
  </div>
);

MetaInformation.propTypes = {
  title: PropTypes.string,
  details: PropTypes.object.isRequired, // eslint-disable-line react/forbid-prop-types
};

MetaInformation.defaultProps = {
  title: null,
};

export default MetaInformation;
