import './app.scss';

import React from 'react';
import ReactDom from 'react-dom';

import { ready } from './helpers';

import Application from './Application';

// Display environment badge on local, dev, or QA:
require('environment-badge')();

ready(() => {
  // For "modern" client-side rendered routes:
  if (document.getElementById('app')) {
    ReactDom.render(<Application />, document.getElementById('app'));
  }
});
