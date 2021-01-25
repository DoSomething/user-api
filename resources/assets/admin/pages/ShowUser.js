import React from 'react';
import gql from 'graphql-tag';
import { map } from 'lodash';
import { useParams } from 'react-router-dom';
import { useQuery } from '@apollo/react-hooks';

import NotFound from './NotFound';
import PostsTable from '../components/PostsTable';
import Shell from '../components/utilities/Shell';
import SignupGallery from '../components/SignupGallery';
import MetaInformation from '../components/utilities/MetaInformation';
import UserInformation, {
  UserInformationFragment,
} from '../components/utilities/UserInformation';

const SHOW_USER_QUERY = gql`
  query ShowUserQuery($id: String!) {
    user(id: $id) {
      ...UserInformation
      permalink
      source
    }
  }

  ${UserInformationFragment}
`;

/**
 * @param {String} selectedTab
 */
const ShowUser = ({ selectedTab }) => {
  const { id } = useParams();

  const title = 'Members';
  const subtitle = 'User profiles & signups...';
  const { loading, error, data } = useQuery(SHOW_USER_QUERY, {
    variables: { id },
  });

  if (loading) {
    return <Shell title={title} subtitle={subtitle} loading />;
  }

  if (error) {
    return <Shell title={title} subtitle={subtitle} error={error} />;
  }

  if (!data.user) {
    return <NotFound title={title} type="user" />;
  }

  const { user } = data;

  return (
    <Shell title={title} subtitle={subtitle}>
      <div className="container__block -half">
        <h2 className="heading -emphasized -padded mb-4">
          <span>User Info</span>
        </h2>
        <UserInformation user={user}>
          <MetaInformation
            details={{
              ID: (
                <span>
                  {user.id} <a href={user.permalink}>(view full profile)</a>
                </span>
              ),
              'Registration Source': user.source,
            }}
          />
        </UserInformation>
      </div>
      <div className="container__block">
        {selectedTab ? (
          <React.Fragment>
            <h2 className="heading -emphasized -padded mb-4">
              <span>
                {selectedTab === 'referrals' ? 'Referral Posts' : 'Posts'}
              </span>
            </h2>
            {selectedTab === 'referrals' ? (
              <PostsTable referrerUserId={user.id} />
            ) : (
              <PostsTable userId={user.id} />
            )}
          </React.Fragment>
        ) : (
          <React.Fragment>
            <h2 className="heading -emphasized -padded">
              <span>Campaigns</span>
            </h2>
            <SignupGallery userId={user.id} />
          </React.Fragment>
        )}
      </div>
    </Shell>
  );
};

export default ShowUser;
