import React from 'react';
import Proptypes from 'prop-types';

import dosomethingLogo from './dosomething_logo.svg';

const DoSomethingLogo = ({ className }) => (
  <img
    className={className}
    src={dosomethingLogo}
    alt="DoSomething logo"
    style={{ pointerEvents: 'none' }}
  />
);

DoSomethingLogo.propTypes = {
  className: Proptypes.string,
};

DoSomethingLogo.defaultProps = {
  className: null,
};

export default DoSomethingLogo;
