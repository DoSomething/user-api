import React from 'react';

import Shell from '../components/utilities/Shell';
import PostsTable from '../components/PostsTable';

const PostIndex = () => {
  const title = 'Posts';
  document.title = title;

  return (
    <Shell title={title}>
      <div className="container__block">
        <PostsTable />
      </div>
    </Shell>
  );
};

export default PostIndex;
