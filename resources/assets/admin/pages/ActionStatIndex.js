import React, { useState } from 'react';

import { getLocations } from '../helpers';
import Shell from '../components/utilities/Shell';
import Select from '../components/utilities/Select';
import ActionStatsTable from '../components/ActionStatsTable';

const ActionStatIndex = () => {
  const title = 'Action Stats';
  document.title = title;

  const [location, setLocation] = useState(null);

  return (
    <Shell title={title}>
      <div className="container__row">
        <div className="container__block -half">
          <Select
            value={location || ''}
            values={getLocations()}
            onChange={setLocation}
          />
        </div>
      </div>
      <div className="container__row">
        <div className="container__block">
          <ActionStatsTable location={location} />
        </div>
      </div>
    </Shell>
  );
};

export default ActionStatIndex;
