import React from 'react';
import classNames from 'classnames';

const SortableHeading = ({ column, orderBy, onChange, label }) => {
  const [currentColumn, direction] = orderBy.split(',', 2);

  // Determine if we should show an "arrow" indicator:
  const isSorting = column == currentColumn;
  const sortClass = classNames({
    'arrow-up': isSorting && direction === 'asc',
    'arrow-down': isSorting && direction === 'desc',
  });

  // If we click, should we swap direction?
  const otherDirection = direction === 'asc' ? 'desc' : 'asc';
  const newDirection = isSorting ? otherDirection : 'desc';

  return (
    <td
      className="cursor-pointer hover:underline"
      onClick={() => onChange(`${column},${newDirection}`)}
    >
      <span className={sortClass}>{label}</span>
    </td>
  );
};

export default SortableHeading;
