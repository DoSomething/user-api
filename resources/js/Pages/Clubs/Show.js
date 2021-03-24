import React from 'react';

import Layout from '../../Shared/Layout';

const Show = ({ club }) => {
  return (
    <article className="base-12-grid h-full py-8">
      <div className="col-span-8">
        <h1 className="text-xl font-bold">Clubs Show Page</h1>

        <p>{club.name}</p>

        <p>{club.city}</p>
      </div>
    </article>
  );
};

Show.layout = page => <Layout children={page} title="Clubs Show" />;

export default Show;
