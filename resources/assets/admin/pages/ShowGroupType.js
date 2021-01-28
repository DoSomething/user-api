import gql from 'graphql-tag';
import { UsaStates } from 'usa-states';
import React, { useState } from 'react';
import { useParams } from 'react-router-dom';
import { useQuery } from '@apollo/react-hooks';

import NotFound from './NotFound';
import Empty from '../components/Empty';
import { getLocations } from '../helpers';
import Shell from '../components/utilities/Shell';
import Select from '../components/utilities/Select';
import GroupsTable from '../components/GroupsTable';
import MetaInformation from '../components/utilities/MetaInformation';
import GroupTypeCampaignList from '../components/GroupTypeCampaignList';

const SHOW_GROUP_TYPE_QUERY = gql`
  query ShowGroupTypeQuery($id: Int!) {
    groupType(id: $id) {
      createdAt
      filterByLocation
      name
    }
  }
`;

const usaStateOptions = new UsaStates().states;

const ShowGroupType = () => {
  const [filter, setFilter] = useState('');
  const [groupLocation, setGroupLocation] = useState(null);

  const { id } = useParams();
  const title = `Group Type #${id}`;
  document.title = title;

  const { loading, error, data } = useQuery(SHOW_GROUP_TYPE_QUERY, {
    variables: { id: Number(id) },
  });

  if (error) {
    return <Shell error={error} />;
  }

  if (loading) {
    return <Shell title={title} loading />;
  }

  if (!data.groupType) return <NotFound title={title} type="group type" />;

  const { createdAt, filterByLocation, name } = data.groupType;

  return (
    <Shell title={title} subtitle={name}>
      <div className="container__row">
        <div className="container__block -half">
          <MetaInformation
            details={{
              Campaigns: <GroupTypeCampaignList groupTypeId={Number(id)} />,
            }}
          />

          {filterByLocation ? (
            <div className="mb-4">
              <Select
                value={groupLocation || ''}
                values={getLocations()}
                onChange={setGroupLocation}
              />
            </div>
          ) : null}

          <input
            type="text"
            className="text-field -search"
            placeholder="Filter by group name..."
            onChange={event => setFilter(event.target.value)}
          />
        </div>
        <div className="container__block -half form-actions -inline text-right">
          <a className="button -tertiary" href={`/group-types/${id}/edit`}>
            Edit Group Type #{id}
          </a>
        </div>
      </div>
      <div className="container__row">
        <div className="container__block">
          <GroupsTable
            filter={filter}
            groupLocation={groupLocation}
            groupTypeId={Number(id)}
          />

          <div className="container__block -narrow">
            <a
              className="button -primary"
              href={`/group-types/${id}/groups/create`}
            >
              Add Group
            </a>
          </div>
        </div>
      </div>
      <ul className="form-actions margin-vertical">
        <li>
          <a className="button -tertiary" href={`/group-types`}>
            View all Group Types
          </a>
        </li>
      </ul>
    </Shell>
  );
};

export default ShowGroupType;
