import React from 'react';

import Layout from '../../Shared/Layout';

const Index = () => {
  return (
    <>
      <h1>Cooking with Inertia!</h1>

      <p>We are doing some sweet, sweeting Inertia sizzling!</p>
    </>
  );
};

Index.layout = page => <Layout children={page} title="Welcome!" />;

export default Index;
