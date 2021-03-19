import React, { useEffect } from 'react';

const Layout = ({ children, title }) => {
  useEffect(() => {
    document.title = title;
  }, [title]);

  return (
    <main>
      <article>{children}</article>
    </main>
  );
};

export default Layout;
