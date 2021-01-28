import { get } from 'lodash';
import gql from 'graphql-tag';
import { useQuery } from '@apollo/react-hooks';
import React, { useState, useEffect } from 'react';

import Empty from './Empty';
import ErrorBlock from './utilities/ErrorBlock';
import EntityLabel from './utilities/EntityLabel';
import { updateQuery } from '../helpers';

const GROUPS_QUERY = gql`
  query GroupsIndexQuery(
    $filter: String
    $groupTypeId: Int!
    $location: String
    $cursor: String
  ) {
    groups: paginatedGroups(
      after: $cursor
      first: 50
      groupTypeId: $groupTypeId
      location: $location
      name: $filter
    ) {
      edges {
        cursor
        node {
          id
          name
          goal
          city
          location
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
 * This component handles fetching & paginating a list of groups in a group type ID.
 *
 * @param {String} filter
 * @param {String} groupLocation
 * @param {Number} groupTypeId
 */
const GroupsTable = ({ filter, groupLocation, groupTypeId }) => {
  const variables = { filter, groupTypeId };

  if (groupLocation) {
    variables.location = groupLocation;
  }

  const { error, loading, data, fetchMore } = useQuery(GROUPS_QUERY, {
    variables,
    notifyOnNetworkStatusChange: true,
  });

  const groups = data ? data.groups.edges : [];
  const noResults = groups.length === 0 && !loading;
  const { endCursor, hasNextPage } = get(data, 'groups.pageInfo', {});

  const handleViewMore = () => {
    fetchMore({
      variables: { cursor: endCursor },
      updateQuery,
    });
  };

  if (error) {
    return <ErrorBlock error={error} />;
  }

  if (noResults && !hasNextPage) {
    return (
      <Empty
        copy={
          filter
            ? `Could not find any groups with name containing "${filter}".`
            : 'No groups have been added to this group type.'
        }
      />
    );
  }

  return (
    <>
      <table className="table">
        <thead>
          <tr>
            <td>Group ID</td>
            <td>Location</td>
            <td>Goal</td>
          </tr>
        </thead>
        <tbody>
          {groups.map(({ node, cursor }) => (
            <tr key={cursor}>
              <td>
                <EntityLabel id={node.id} name={node.name} path="groups" />
              </td>
              <td>{node.city ? `${node.city}, ${node.location}` : null}</td>
              <td>{node.goal || '-'}</td>
            </tr>
          ))}
        </tbody>
        <tfoot className="form-actions">
          {loading ? (
            <tr>
              <td colSpan="3">
                <div className="spinner margin-horizontal-auto margin-vertical" />
              </td>
            </tr>
          ) : null}
          {hasNextPage ? (
            <tr>
              <td colSpan="3">
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
    </>
  );
};

export default GroupsTable;
