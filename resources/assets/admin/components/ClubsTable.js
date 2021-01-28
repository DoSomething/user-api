import React from 'react';
import { get } from 'lodash';
import gql from 'graphql-tag';
import { useQuery } from '@apollo/react-hooks';

import Empty from './Empty';
import ErrorBlock from './utilities/ErrorBlock';
import EntityLabel from './utilities/EntityLabel';
import { updateQuery } from '../helpers';

const CLUBS_QUERY = gql`
  query ClubsIndexQuery($filter: String, $cursor: String) {
    clubs: paginatedClubs(after: $cursor, first: 50, name: $filter) {
      edges {
        cursor
        node {
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
      pageInfo {
        endCursor
        hasNextPage
      }
    }
  }
`;

/**
 * This component handles fetching & paginating a list of clubs matching the given filter.
 *
 * @param {String} filter
 */
const ClubsTable = ({ filter }) => {
  const { error, loading, data, fetchMore } = useQuery(CLUBS_QUERY, {
    variables: { filter },
    notifyOnNetworkStatusChange: true,
  });

  const clubs = get(data, 'clubs.edges', []);
  const noResults = !clubs.length && !loading;
  const { endCursor, hasNextPage } = get(data, 'clubs.pageInfo', {});

  const handleViewMore = () =>
    fetchMore({ variables: { cursor: endCursor }, updateQuery });

  if (error) {
    return <ErrorBlock error={error} />;
  }

  if (noResults && !hasNextPage) {
    return (
      <Empty
        copy={`Could not find any clubs${
          filter ? ` containing "${filter}"` : ''
        }.`}
      />
    );
  }

  return (
    <table className="table">
      <thead>
        <tr>
          <td>Club ID</td>
          <td>Location</td>
          <td>Leader</td>
          <td>School ID</td>
        </tr>
      </thead>

      <tbody>
        {clubs.map(({ node, cursor }) => (
          <tr key={cursor}>
            <td>
              <EntityLabel id={node.id} name={node.name} path="clubs" />
            </td>
            <td>{node.city ? `${node.city}, ${node.location}` : null}</td>
            <td>
              {node.leader ? (
                <EntityLabel
                  id={node.leader.id}
                  name={node.leader.displayName}
                  path="users"
                />
              ) : (
                node.leaderId
              )}
            </td>
            <td>
              {node.schoolId ? (
                <EntityLabel
                  id={node.schoolId}
                  name={node.schoolId}
                  path="schools"
                />
              ) : null}
            </td>
          </tr>
        ))}
      </tbody>

      <tfoot className="form-actions">
        {loading ? (
          <tr>
            <td colSpan="4">
              <div className="spinner margin-horizontal-auto margin-vertical" />
            </td>
          </tr>
        ) : null}

        {hasNextPage ? (
          <tr>
            <td colSpan="4">
              <button
                className="button -tertiary"
                onClick={handleViewMore}
                disabled={loading}
              >
                view more...
              </button>
            </td>
          </tr>
        ) : null}
      </tfoot>
    </table>
  );
};

export default ClubsTable;
