import React from 'react';

const BaseShell = ({ title, subtitle, children }) => (
  <div>
    <header className="header" role="banner">
      <div className="wrapper">
        <h1 className="header__title">{title}</h1>
        <p className="header__subtitle">{subtitle}</p>
      </div>
    </header>
    <div className="container">
      <div className="wrapper">{children}</div>
    </div>
  </div>
);

const Shell = ({ title, subtitle, children, error, loading }) => {
  if (error) {
    return (
      <BaseShell title="Oops!" subtitle="Something went wrong...">
        <div className="placeholder flex-center-xy">{error.toString()}</div>
      </BaseShell>
    );
  }

  if (loading) {
    return (
      <BaseShell title={title || 'Loading...'} subtitle={subtitle || '...'}>
        <div className="h-content flex-center-xy">
          <div className="spinner" />
        </div>
      </BaseShell>
    );
  }

  return (
    <BaseShell title={title} subtitle={subtitle}>
      {children}
    </BaseShell>
  );
};

export default Shell;
