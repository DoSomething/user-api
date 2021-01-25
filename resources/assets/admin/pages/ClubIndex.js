import React, { useState } from 'react';

import Shell from '../components/utilities/Shell';
import ClubsTable from '../components/ClubsTable';

const ClubIndex = () => {
  const [filter, setFilter] = useState('');

  const title = 'Clubs';
  document.title = title;

  return (
    <Shell title={title}>
      <div className="container__row">
        <div className="container__block -half">
          <input
            type="text"
            className="text-field -search"
            placeholder="Filter by club name"
            onChange={event => setFilter(event.target.value)}
          />
        </div>

        <div className="container__block -half form-actions -inline text-right">
          <a className="button -tertiary" href="/clubs/create">
            New Club
          </a>
        </div>
      </div>

      <div className="container__row">
        <div className="container__block">
          <ClubsTable filter={filter} />
        </div>
      </div>
    </Shell>
  );
};

export default ClubIndex;
