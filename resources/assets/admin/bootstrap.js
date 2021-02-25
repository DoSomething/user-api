import './admin.scss';

import React from 'react';
import ReactDom from 'react-dom';

import { ready } from './helpers';

import Administration from './Administration';

// Display environment badge on local, dev, or QA:
require('environment-badge')();

ready(() => {
  // For "modern" client-side rendered routes:
  if (document.getElementById('app')) {
    ReactDom.render(<Administration />, document.getElementById('app'));
  }
});
