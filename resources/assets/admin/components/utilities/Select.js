import React from 'react';
import { map } from 'lodash';

const Select = ({ values, value, onChange }) => (
  <div className="select">
    <select value={value} onChange={event => onChange(event.target.value)}>
      <option value={''}>---</option>
      {map(values, (label, id) => (
        <option key={id} value={id}>
          {label}
        </option>
      ))}
    </select>
  </div>
);

export default Select;
