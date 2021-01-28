import gql from 'graphql-tag';
import React, { useState } from 'react';
import { parse, format } from 'date-fns';
import { useParams } from 'react-router-dom';
import { useQuery } from '@apollo/react-hooks';

import NotFound from './NotFound';
import Shell from '../components/utilities/Shell';
import EntityLabel from '../components/utilities/EntityLabel';
import ActionStatsTable from '../components/ActionStatsTable';
import MetaInformation from '../components/utilities/MetaInformation';

const SHOW_SCHOOL_QUERY = gql`
  query ShowSchoolQuery($id: String!) {
    school(id: $id) {
      id
      name
      city
      location
    }
    groups(schoolId: $id) {
      id
      groupTypeId
      groupType {
        id
        name
      }
    }
  }
`;

const ShowSchool = () => {
  const { id } = useParams();
  const title = `School #${id}`;
  document.title = title;

  const { loading, error, data } = useQuery(SHOW_SCHOOL_QUERY, {
    variables: { id },
  });

  if (error) {
    return <Shell error={error} />;
  }

  if (loading) {
    return <Shell title={title} loading />;
  }

  if (!data.school) return <NotFound title={title} type="school" />;

  const { city, name, location } = data.school;

  const groupList = data.groups.length ? (
    <ul>
      {data.groups.map(group => (
        <li key={group.id}>
          <EntityLabel
            id={group.id}
            name={group.groupType.name}
            path="groups"
          />
        </li>
      ))}
    </ul>
  ) : (
    '--'
  );

  return (
    <Shell title={title} subtitle={name}>
      <div className="container__row">
        <div className="container__block">
          <MetaInformation
            details={{
              ID: id,
              City: city,
              Location: location,
              Groups: groupList,
            }}
          />
        </div>
      </div>
      <div className="container__row">
        <div className="container__block">
          <ActionStatsTable schoolId={id} />
        </div>
      </div>
    </Shell>
  );
};

export default ShowSchool;
