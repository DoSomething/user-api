import React from 'react';

import Shell from '../components/utilities/Shell';

const NotFound = ({ title, type }) => {
  return (
    <Shell title={title} subtitle="Not found!">
      <div className="container__block">
        We couldn't find that {type}. Maybe it was deleted?
      </div>
    </Shell>
  );
};

export default NotFound;
