import React from 'react';
import gql from 'graphql-tag';
import { get, map } from 'lodash';
import { useQuery } from '@apollo/react-hooks';

import Empty from './Empty';
import Shell from './utilities/Shell';
import SignupCard, { SignupCardFragment } from './SignupCard';
import { updateQuery } from '../helpers';

const SIGNUP_GALLERY_QUERY = gql`
  query SignupGalleryQuery($userId: String, $cursor: String) {
    signups: paginatedSignups(userId: $userId, after: $cursor, first: 10) {
      edges {
        cursor
        node {
          ...SignupCardFragment
        }
      }

      pageInfo {
        hasNextPage
        endCursor
      }
    }
  }

  ${SignupCardFragment}
`;

const SignupGallery = ({ userId }) => {
  const { loading, error, data, fetchMore } = useQuery(SIGNUP_GALLERY_QUERY, {
    variables: { userId },
    notifyOnNetworkStatusChange: true,
  });

  if (error) {
    return <div>{error}</div>;
  }

  const signups = get(data, 'signups.edges', []);
  const { endCursor, hasNextPage } = get(data, 'signups.pageInfo', {});

  const handleViewMore = () => {
    fetchMore({
      variables: { cursor: endCursor },
      updateQuery,
    });
  };

  if (signups.length == 0) {
    return loading ? (
      <div className="h-content flex-center-xy">
        <div className="spinner" />
      </div>
    ) : (
      <div className="h-content flex-center-xy">
        <Empty copy="This user has no signups yet." />
      </div>
    );
  }

  return (
    <>
      {map(signups, signup => (
        <SignupCard key={signup.cursor} signup={signup.node} />
      ))}
      {loading ? (
        <div className="container__block">
          <div className="spinner margin-horizontal-auto margin-vertical" />
        </div>
      ) : null}
      <div className="form-actions -padded">
        {hasNextPage ? (
          <button
            className="button -tertiary"
            onClick={handleViewMore}
            disabled={loading}
          >
            view more...
          </button>
        ) : (
          <p className="footnote margin-horizontal-auto">...and that's all!</p>
        )}
      </div>
    </>
  );
};

export default SignupGallery;
