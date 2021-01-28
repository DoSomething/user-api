import React from 'react';
import gql from 'graphql-tag';
import { Link } from 'react-router-dom';
import { useQuery } from '@apollo/react-hooks';

import Empty from '../components/Empty';
import Shell from '../components/utilities/Shell';
import EntityLabel from '../components/utilities/EntityLabel';

const GROUP_TYPE_INDEX_QUERY = gql`
  query GroupTypeIndexQuery {
    groupTypes {
      id
      name
    }
  }
`;

const GroupTypeIndex = () => {
  const title = 'Group Types';
  const { loading, error, data } = useQuery(GROUP_TYPE_INDEX_QUERY);

  document.title = title;

  if (error) {
    return <Shell error={error} />;
  }

  if (loading) {
    return <Shell title={title} loading />;
  }

  if (!data.groupTypes) {
    return (
      <Shell title={title}>
        <Empty />
      </Shell>
    );
  }

  return (
    <Shell title={title}>
      <div className="container__block -half"></div>
      <div className="container__block -half form-actions -inline text-right">
        <a className="button -tertiary" href="/group-types/create">
          New Group Type
        </a>
      </div>
      <div className="container__block">
        <table className="table">
          <thead>
            <tr>
              <td>Group Type ID</td>
            </tr>
          </thead>
          <tbody>
            {data.groupTypes.map(groupType => (
              <tr key={groupType.id}>
                <td>
                  <EntityLabel
                    id={groupType.id}
                    name={groupType.name}
                    path="group-types"
                  />
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </Shell>
  );
};

export default GroupTypeIndex;
