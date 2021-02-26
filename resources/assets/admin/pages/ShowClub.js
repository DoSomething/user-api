import React from 'react';
import gql from 'graphql-tag';
import { useQuery } from '@apollo/react-hooks';
import { Link, useParams } from 'react-router-dom';

import NotFound from '../pages/NotFound';
import Shell from '../components/utilities/Shell';
import MetaInformation from '../components/utilities/MetaInformation';

const SHOW_CLUB_QUERY = gql`
  query ShowClubQuery($id: Int!) {
    club(id: $id) {
      id
      name
      city
      location
      leaderId
      leader {
        id
        displayName
      }
      schoolId
    }
  }
`;

const ShowClub = () => {
  const { id } = useParams();
  const title = `Club #${id}`;
  document.title = title;

  const { loading, error, data } = useQuery(SHOW_CLUB_QUERY, {
    variables: { id: Number(id) },
  });

  if (error) {
    return <Shell error={error} />;
  }

  if (loading) {
    return <Shell title={title} loading />;
  }

  if (!data.club) {
    return <NotFound title={title} type="club" />;
  }

  const { name, city, location, leader, schoolId } = data.club;

  return (
    <Shell title={title} subtitle={name}>
      <div className="container__row">
        <div className="container__block -half">
          <MetaInformation
            details={{
              Name: name,
              City: city || '--',
              Location: location || '--',
              Leader: leader ? (
                <Link to={`/admin/users/${leader.id}`}>
                  {leader.displayName}
                </Link>
              ) : (
                leaderId
              ),
              School: schoolId ? (
                <Link to={`/admin/schools/${schoolId}`}>{schoolId}</Link>
              ) : (
                '--'
              ),
            }}
          />
        </div>

        <div className="container__block -half form-actions -inline text-right">
          <a className="button -tertiary" href={`/admin/clubs/${id}/edit`}>
            Edit Club #{id}
          </a>
        </div>

        {/* @TODO: Display table of club members here. */}
        {/* @TODO: Display table of clubs posts/signups. */}
      </div>
    </Shell>
  );
};

export default ShowClub;
