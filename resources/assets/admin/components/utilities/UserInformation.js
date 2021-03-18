import React from 'react';
import gql from 'graphql-tag';
import { isEmpty } from 'lodash';

export const UserInformationFragment = gql`
  fragment UserInformation on User {
    id
    displayName
    age
    emailPreview
    mobilePreview
    addrCity
    addrState
  }
`;

/**
 * Returns a readable City and State string.
 *
 * @param {Object} user
 * @return {ReactElement|null}
 */
const UserLocation = ({ user }) => {
  if (!user || !user.addrCity || !user.addrState) {
    return null;
  }

  return (
    <span>
      {user.addrCity}, {user.addrState}
      <br />
    </span>
  );
};

/**
 * Returns a readable display name and age (if provided).
 *
 * @param {Object} user
 * @return {ReactElement}
 */
const UserName = ({ user, link }) => {
  let displayName = user.displayName || 'N/A';

  if (user.age) {
    displayName += `, ${user.age}`;
  }

  if (link) {
    return <a href={link}>{displayName}</a>;
  }

  return <span>{displayName}</span>;
};

const UserInformation = ({ user, userId, linkSignup, children }) => (
  <div>
    {!isEmpty(user) ? (
      <div className="mb-4">
        <h2 className="heading">
          <UserName
            user={user}
            link={linkSignup ? `/admin/signups/${linkSignup}` : null}
          />
        </h2>
        <p>
          {user.emailPreview ? (
            <span>
              {user.emailPreview}
              <br />
            </span>
          ) : null}

          {user.mobilePreview ? (
            <span>
              {user.mobilePreview}
              <br />
            </span>
          ) : null}

          <UserLocation user={user} />
        </p>
      </div>
    ) : (
      <div className="mb-4">
        <h2 className="heading">
          {linkSignup ? (
            <a href={`/admin/signups/${linkSignup}`}>{userId}</a>
          ) : (
            <span>{userId}</span>
          )}
        </h2>
      </div>
    )}
    {children}
  </div>
);

export default UserInformation;
