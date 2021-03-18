import gql from 'graphql-tag';
import { Link } from 'react-router-dom';
import { useQuery } from '@apollo/react-hooks';
import React, { useRef, useState } from 'react';

import Empty from '../components/Empty';
import Shell from '../components/utilities/Shell';

const USER_SEARCH_QUERY = gql`
  query UserSearchQuery($term: String!) {
    users(search: $term) {
      id
      displayName
      emailPreview
      mobilePreview
    }
  }
`;

/**
 * Load & display search results for the given term.
 *
 * @param {string} term
 */
const SearchResults = ({ term }) => {
  const { loading, error, data } = useQuery(USER_SEARCH_QUERY, {
    variables: { term },
  });

  if (loading) {
    return <div className="spinner" />;
  }

  if (error) {
    return (
      <p>
        <strong>Error:</strong> {error}
      </p>
    );
  }

  if (data.users.length === 0) {
    return <Empty />;
  }

  return (
    <table className="table">
      <thead>
        <tr>
          <td>Name</td>
          <td>Email</td>
          <td>Mobile</td>
        </tr>
      </thead>
      <tbody>
        {data.users.map(user => (
          <tr key={user.id}>
            <td>
              <Link to={`/admin/users/${user.id}`}>
                {user.displayName || 'A Doer'}
              </Link>
            </td>
            <td>{user.emailPreview}</td>
            <td>{user.mobilePreview}</td>
          </tr>
        ))}
      </tbody>
    </table>
  );
};

/**
 * The user index!
 *
 * @param {string} term
 */
const UserIndex = () => {
  const [term, setTerm] = useState(null);
  const inputElement = useRef(null);

  const onSubmit = event => {
    event.preventDefault();
    setTerm(inputElement.current.value);
  };

  return (
    <Shell title="Members" subtitle="User profiles &amp; signups...">
      <div className="container__block">
        <form onSubmit={onSubmit} className="form-actions -inline">
          <li>
            <input
              type="search"
              className="text-field -search"
              placeholder="Find by full email, mobile, Northstar ID..."
              style={{ minWidth: '400px' }}
              ref={inputElement}
            />
          </li>
          <li>
            <button type="submit" className="button">
              Search
            </button>
          </li>
        </form>
      </div>
      <div className="container__block">
        {term ? (
          <SearchResults term={term} />
        ) : (
          <p>
            You can search for a user's signups and posts by typing their (full)
            email, mobile, or user ID above!
          </p>
        )}
      </div>
    </Shell>
  );
};

export default UserIndex;
