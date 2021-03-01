import gql from 'graphql-tag';
import React, { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useQuery } from '@apollo/react-hooks';

import NotFound from './NotFound';
import Empty from '../components/Empty';
import { formatDateTime } from '../helpers';
import Shell from '../components/utilities/Shell';
import PostsTable from '../components/PostsTable';
import SignupsTable from '../components/SignupsTable';
import EntityLabel from '../components/utilities/EntityLabel';
import MetaInformation from '../components/utilities/MetaInformation';

const SHOW_GROUP_QUERY = gql`
  query ShowGroupQuery($id: Int!) {
    group(id: $id) {
      id
      city
      goal
      groupType {
        id
        name
      }
      name
      schoolId
      location
    }
    voterRegistrationsCountByGroupId(groupId: $id)
  }
`;

/**
 * @param {String} selectedTab
 */
const ShowGroup = ({ selectedTab }) => {
  const { id } = useParams();
  const title = `Group #${id}`;
  document.title = title;

  const { loading, error, data } = useQuery(SHOW_GROUP_QUERY, {
    variables: { id: Number(id) },
  });

  if (error) {
    return <Shell error={error} />;
  }

  if (loading) {
    return <Shell title={title} loading />;
  }

  if (!data.group) return <NotFound title={title} type="group" />;

  const { city, goal, groupType, name, schoolId, location } = data.group;

  return (
    <Shell title={title} subtitle={`${name} (${groupType.name})`}>
      <div className="container__row">
        <div className="container__block -half">
          <MetaInformation
            details={{
              'Voter Registrations Goal': goal || '--',
              'Voter Registrations Completed':
                data.voterRegistrationsCountByGroupId,
              City: city || '--',
              Location: location || '--',
              School: schoolId ? (
                <Link to={`/admin/schools/${schoolId}`}>{schoolId}</Link>
              ) : (
                '--'
              ),
            }}
          />
        </div>

        <div className="container__block -half form-actions -inline text-right">
          <a className="button -tertiary" href={`/admin/groups/${id}/edit`}>
            Edit Group #{id}
          </a>
        </div>
      </div>

      <div className="container__row">
        <div className="container__block">
          {selectedTab === 'posts' ? (
            <PostsTable groupId={data.group.id} />
          ) : (
            <SignupsTable groupId={data.group.id} />
          )}
        </div>
      </div>

      <ul className="form-actions margin-vertical">
        <li>
          <a
            className="button -tertiary"
            href={`/admin/group-types/${groupType.id}`}
          >
            View all {groupType.name} Groups
          </a>
        </li>
      </ul>
    </Shell>
  );
};

export default ShowGroup;
