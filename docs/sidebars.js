/**
 * Creating a sidebar enables you to:
 - create an ordered group of docs
 - render a sidebar for each doc of that group
 - provide next/previous navigation

 The sidebars can be generated from the filesystem, or explicitly defined here.

 Create as many sidebars as you want.
 */

module.exports = {
  docs: {
    'Getting Started': [
      'getting-started/index',
      'getting-started/installation',
    ],
    Campaigns: ['campaigns/index'],
    Users: ['users/index'],
    OAuth: ['oauth/index'],
    Redirects: ['redirects/index'],
    'Data Importer': [
      'importer/index',
      'importer/email-subscriptions',
      'importer/mute-promotions',
      'importer/rock-the-vote',
    ],
  },
  api: {
    Introduction: ['api/index'],
  },
};
