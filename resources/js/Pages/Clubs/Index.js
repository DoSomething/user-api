import React from 'react';

import Layout from '../../Shared/Layout';

const Index = () => {
  return (
    <>
      <h1>Clubs Index Page</h1>
    </>
  );
};

Index.layout = page => <Layout children={page} title="Welcome!" />;

export default Index;
