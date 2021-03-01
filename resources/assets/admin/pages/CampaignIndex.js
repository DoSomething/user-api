import React, { useState } from 'react';
import { Link } from 'react-router-dom';

import Shell from '../components/utilities/Shell';
import CampaignsTable from '../components/CampaignsTable';

const CampaignIndex = ({ isOpen }) => {
  const [filter, setFilter] = useState('');

  return (
    <Shell title="Campaigns" subtitle="Reportback inbox & management...">
      <div className="container__block -half">
        <input
          type="text"
          className="text-field -search"
          placeholder="Filter by campaign ID, name, cause..."
          onChange={event => setFilter(event.target.value)}
        />
      </div>

      <div className="container__block -half form-actions -inline text-right">
        <a className="button -tertiary" href="/admin/campaigns/create">
          New Campaign
        </a>
      </div>

      {isOpen ? (
        <div className="container__block">
          <h3>
            Open Campaigns{' '}
            <span className="text-gray-500">
              {' / '}
              <Link
                to="/admin/campaigns/closed"
                className="text-gray-500 underline"
              >
                Closed Campaigns
              </Link>
            </span>
          </h3>

          <p>These campaigns are currently accepting new submissions.</p>
        </div>
      ) : (
        <div className="container__block">
          <h3>
            <span className="text-gray-500">
              <Link to="/campaigns" className="text-gray-500 underline">
                Open Campaigns
              </Link>
              {' / '}
            </span>
            Closed Campaigns
          </h3>

          <p>These campaigns are no longer accepting new submissions.</p>
        </div>
      )}

      <div className="container__block text-center text-sm fade-in-up">
        <mark>
          <strong>New!</strong> You can now click table headings to sort this
          list. Click again to sort in the opposite direction.
        </mark>
      </div>

      <div className="container__block">
        <CampaignsTable isOpen={isOpen} filter={filter} />
      </div>
    </Shell>
  );
};

export default CampaignIndex;
