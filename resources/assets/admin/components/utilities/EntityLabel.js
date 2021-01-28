import React from 'react';
import { Link } from 'react-router-dom';

/**
 * Renders entity name and id.
 *
 * @param {String|Number} id
 * @param {String} name
 * @param {String} path
 */
const EntityLabel = ({ id, name, path }) => {
  const label = (
    <>
      {name} <code className="footnote">({id})</code>
    </>
  );

  return path ? <Link to={`/${path}/${id}`}>{label}</Link> : label;
};

export default EntityLabel;
