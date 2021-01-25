import React from 'react';

import Shell from '../components/utilities/Shell';
import SignupsTable from '../components/SignupsTable';

const SignupIndex = () => {
  const title = 'Signups';
  document.title = title;

  return (
    <Shell title={title}>
      <div className="container__block">
        <SignupsTable />
      </div>
    </Shell>
  );
};

export default SignupIndex;
