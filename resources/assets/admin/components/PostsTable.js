import { get } from 'lodash';
import gql from 'graphql-tag';
import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useQuery } from '@apollo/react-hooks';

import Empty from './Empty';
import EntityLabel from './utilities/EntityLabel';
import { formatDateTime, updateQuery } from '../helpers';

const POSTS_INDEX_QUERY = gql`
  query PostsIndexQuery(
    $campaignId: String
    $groupId: Int
    $referrerUserId: String
    $userId: String
    $cursor: String
  ) {
    posts: paginatedPosts(
      after: $cursor
      first: 50
      campaignId: $campaignId
      groupId: $groupId
      referrerUserId: $referrerUserId
      userId: $userId
    ) {
      edges {
        cursor
        node {
          id
          createdAt
          userId
          type
          status
          campaign {
            id
            internalTitle
          }
          club {
            id
            name
          }
          groupId
          group {
            id
            name
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
 * This component handles fetching & paginating a list of posts.
 *
 * @param {String} campaignId
 * @param {Number} groupId
 * @param {String} referrerUserId
 * @param {String} userId
 */
const PostsTable = ({ campaignId, groupId, referrerUserId, userId }) => {
  const { error, loading, data, fetchMore } = useQuery(POSTS_INDEX_QUERY, {
    variables: { campaignId, groupId, referrerUserId, userId },
    notifyOnNetworkStatusChange: true,
  });

  const posts = data ? data.posts.edges : [];
  const noResults = posts.length === 0 && !loading;
  const { endCursor, hasNextPage } = get(data, 'posts.pageInfo', {});

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
    return <Empty copy="No posts found." />;
  }

  return (
    <>
      <table className="table">
        <thead>
          <tr>
            <td>Post</td>

            {userId ? null : <td>User</td>}

            {campaignId ? null : <td>Campaign</td>}

            <td>Type</td>

            <td>Status</td>

            {groupId ? null : <td>Group</td>}

            <td>Club</td>
          </tr>
        </thead>
        <tbody>
          {posts.map(({ node, cursor }) => (
            <tr key={node.id}>
              <td>
                <Link to={`/admin/posts/${node.id}`}>
                  {formatDateTime(node.createdAt)}
                </Link>
              </td>

              {userId ? null : (
                <td>
                  <Link to={`/admin/activity/${node.userId}`}>
                    {node.userId}
                  </Link>
                </td>
              )}

              {campaignId ? null : (
                <td>
                  <EntityLabel
                    id={node.campaign.id}
                    name={node.campaign.internalTitle}
                    path="campaigns"
                  />
                </td>
              )}

              <td>{node.type}</td>

              <td>{node.status}</td>

              {groupId ? null : (
                <td>
                  {node.groupId ? (
                    <EntityLabel
                      id={node.group.id}
                      name={node.group.name}
                      path="groups"
                    />
                  ) : null}
                </td>
              )}

              <td>
                {node.club ? (
                  <EntityLabel
                    id={node.club.id}
                    name={node.club.name}
                    path="clubs"
                  />
                ) : null}
              </td>
            </tr>
          ))}
        </tbody>
        <tfoot className="form-actions">
          {loading ? (
            <tr>
              <td colSpan={campaignId || groupId || userId ? 5 : 6}>
                <div className="spinner margin-horizontal-auto margin-vertical" />
              </td>
            </tr>
          ) : null}
          {hasNextPage ? (
            <tr>
              <td colSpan={campaignId || groupId || userId ? 5 : 6}>
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

export default PostsTable;
