import gql from 'graphql-tag';
import { assign, get } from 'lodash';
import { useQuery } from '@apollo/react-hooks';
import React, { useState, useEffect } from 'react';

import Empty from './Empty';
import { updateQuery } from '../helpers';
import EntityLabel from './utilities/EntityLabel';

const ACTION_STATS_QUERY = gql`
  query ActionStatsIndexQuery(
    $actionId: Int
    $schoolId: String
    $location: String
    $orderBy: String
    $cursor: String
  ) {
    stats: paginatedSchoolActionStats(
      after: $cursor
      first: 20
      actionId: $actionId
      location: $location
      orderBy: $orderBy
      schoolId: $schoolId
    ) {
      edges {
        cursor
        node {
          id
          impact
          location
          action {
            id
            name
            noun
            verb
          }
          schoolId
          school {
            id
            name
            city
          }
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
 * This component handles fetching & paginating a list of action stats.
 *
 * @param {Number} actionId
 * @param {String} schoolId
 */
const ActionStatsTable = ({ actionId, location, orderBy, schoolId }) => {
  const variables = {};

  if (actionId) {
    assign(variables, { actionId });
  }

  if (location) {
    assign(variables, { location });
  }

  if (orderBy) {
    assign(variables, { orderBy });
  }

  if (schoolId) {
    assign(variables, { schoolId });
  }

  const { error, loading, data, fetchMore } = useQuery(ACTION_STATS_QUERY, {
    variables,
    notifyOnNetworkStatusChange: true,
  });

  const stats = data ? data.stats.edges : [];
  const noResults = stats.length === 0 && !loading;
  const { endCursor, hasNextPage } = get(data, 'stats.pageInfo', {});

  const handleViewMore = () => {
    fetchMore({
      variables: { cursor: endCursor },
      updateQuery,
    });
  };

  if (error) {
    return (
      <div className="text-center">
        <p>There was an error. :(</p>

        <code>{JSON.stringify(error)}</code>
      </div>
    );
  }

  if (noResults && !hasNextPage) {
    return <Empty />;
  }

  return (
    <>
      <table className="table">
        <thead>
          <tr>
            {schoolId ? null : <td>School</td>}

            {actionId ? null : <td>Action</td>}

            <td>Impact</td>
          </tr>
        </thead>

        <tbody>
          {stats.map(({ node, cursor }) => {
            const { action, impact, location, school } = node;

            return (
              <tr key={cursor}>
                {schoolId ? null : (
                  <td>
                    <EntityLabel
                      id={school.id}
                      name={school.name}
                      path="admin/schools"
                    />

                    <div>
                      {school.city ? `${school.city}, ${location}` : null}
                    </div>
                  </td>
                )}

                {actionId ? null : (
                  <td>
                    <EntityLabel
                      id={action.id}
                      name={action.name}
                      path="admin/actions"
                    />
                  </td>
                )}

                <td>
                  {impact}{' '}
                  {actionId ? null : (
                    <span className="text-sm">
                      {action.noun} {action.verb}
                    </span>
                  )}
                </td>
              </tr>
            );
          })}
        </tbody>

        <tfoot className="form-actions">
          {loading ? (
            <tr>
              <td colSpan={schoolId || actionId ? 2 : 3}>
                <div className="spinner margin-horizontal-auto margin-vertical" />
              </td>
            </tr>
          ) : null}

          {hasNextPage ? (
            <tr>
              <td colSpan={schoolId || actionId ? 2 : 3}>
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

export default ActionStatsTable;
