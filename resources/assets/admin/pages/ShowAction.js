import gql from 'graphql-tag';
import React, { useState } from 'react';
import { parse, format } from 'date-fns';
import { useParams } from 'react-router-dom';
import { useQuery } from '@apollo/react-hooks';

import NotFound from './NotFound';
import { getLocations } from '../helpers';
import Shell from '../components/utilities/Shell';
import Select from '../components/utilities/Select';
import ActionStatsTable from '../components/ActionStatsTable';
import Action, { ActionFragment } from '../components/Action';
import MetaInformation from '../components/utilities/MetaInformation';

const SHOW_ACTION_QUERY = gql`
  query ShowActionQuery($id: Int!) {
    action(id: $id) {
      ...ActionFragment
    }
  }
  ${ActionFragment}
`;

const ShowAction = () => {
  const { id } = useParams();
  const title = `Action #${id}`;

  document.title = title;

  const [location, setLocation] = useState(null);

  const { loading, error, data } = useQuery(SHOW_ACTION_QUERY, {
    variables: { id: Number(id) },
  });

  if (error) {
    return <Shell error={error} />;
  }

  if (loading) {
    return <Shell title={title} loading />;
  }

  if (!data.action) {
    return <NotFound title={title} type="action" />;
  }

  const { campaign, name, noun, verb } = data.action;

  return (
    <Shell title={title} subtitle={name}>
      <Action action={data.action} isPermalink />

      <ul className="form-actions margin-vertical">
        <li>
          <a
            className="button -tertiary"
            href={`/admin/campaigns/${campaign.id}`}
          >
            View all Actions for Campaign "{campaign.internalTitle}"
          </a>
        </li>
      </ul>

      <div className="container__row">
        <div className="container__row">
          <div className="container__block -half">
            <Select
              value={location || ''}
              values={getLocations()}
              onChange={setLocation}
            />
          </div>
        </div>

        <div className="container__block">
          <ActionStatsTable
            actionId={data.action.id}
            location={location}
            orderBy="impact,desc"
          />
        </div>
      </div>
    </Shell>
  );
};

export default ShowAction;
