import React from 'react';

import Layout from '../../Shared/Layout';

const Show = ({ club, user }) => {
  return (
    <div
      className="base-12-grid py-8 mx-auto"
      style={{ 'max-width': '1400px' }}
    >
      <h1 className="col-span-full font-bold mb-10 text-gray-600 text-base uppercase">
        Clubs
      </h1>

      <article className="border border-gray-300 col-span-7 mb-10 px-4 py-4 rounded shadow">
        <div className="flex justify-between mb-4">
          <h2 className="text-lg">{club.name}</h2>

          <span className="text-gray-600 text-sm">ID: {club.id}</span>
        </div>

        <p className="mt-4">
          Club is led by{' '}
          <a className="text-teal-600" href="#">
            {user.first_name}
          </a>
        </p>

        <p className="mt-4">Located in {club.city}</p>

        <div className="mt-10 text-sm text-gray-600">
          Last updated on {club.updated_at}
        </div>
      </article>

      <aside className="col-span-4 col-start-9 text-gray-600">
        <p>
          Curabitur blandit tempus porttitor. Praesent commodo cursus magna, vel
          scelerisque nisl consectetur et.
        </p>
      </aside>

      {/* <ul className="col-span-8 text-gray-600 flex">
        <li>
          <a className="bg-blurple-500 p-4 text-white" href="#">
            Edit
          </a>
        </li>

        <li>
          <a href="#">Cancel</a>
        </li>
      </ul> */}
    </div>
  );
};

Show.layout = page => <Layout children={page} title="Clubs Show" />;

export default Show;
