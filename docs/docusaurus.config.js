/** @type {import('@docusaurus/types').DocusaurusConfig} */
module.exports = {
  title: 'Dev @ DoSomething',
  tagline:
    'Technical documentation and resources for development on DoSomething.org.',
  url: 'https://your-docusaurus-test-site.com',
  baseUrl: '/dev/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'img/favicon.ico',
  organizationName: 'DoSomething',
  projectName: 'northstar',
  themeConfig: {
    navbar: {
      title: 'DoSomething',
      logo: {
        alt: 'DoSomething Logo',
        src: 'img/dosomething_logo.svg',
      },
      items: [
        {
          type: 'doc',
          docId: 'introduction-docs',
          position: 'left',
          label: 'Docs',
        },
        {
          type: 'doc',
          docId: 'introduction-api',
          position: 'left',
          label: 'API',
        },
        {
          href: 'https://github.com/DoSomething',
          label: 'GitHub',
          position: 'right',
        },
      ],
    },
    footer: {
      style: 'dark',
      links: [
        {
          title: 'Docs',
          items: [
            {
              label: 'Docs',
              to: '/docs',
            },
            {
              label: 'API',
              to: '/docs/api',
            },
          ],
        },
        {
          title: 'Community',
          items: [
            {
              label: 'Instagram',
              href: 'https://www.instagram.com/dosomething',
            },
            {
              label: 'Twitter',
              href: 'https://twitter.com/dosomething',
            },
          ],
        },
        {
          title: 'More',
          items: [
            {
              label: 'GitHub',
              href: 'https://github.com/DoSomething',
            },
          ],
        },
      ],
      copyright: `Copyright © ${new Date().getFullYear()} DoSomething.org. Docs built with Docusaurus.`,
    },
  },
  presets: [
    [
      '@docusaurus/preset-classic',
      {
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
          // Please change this to your repo.
          editUrl: 'https://github.com/DoSomething/northstar/edit/main/docs/',
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      },
    ],
  ],
};
