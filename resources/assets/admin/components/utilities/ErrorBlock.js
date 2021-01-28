import React from 'react';

const ErrorBlock = error => (
  <div className="text-center">
    <p>There was an error. :(</p>

    <code>{JSON.stringify(error)}</code>
  </div>
);

export default ErrorBlock;
