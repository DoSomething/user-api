const configure = require('@dosomething/webpack-config');
const path = require('path');

module.exports = configure({
  entry: {
    app: './resources/assets/js/app.js',
    admin: './resources/assets/admin/bootstrap.js',
    inertia: './resources/js/app.js',
  },
  output: {
    filename: '[name]-[chunkhash].js',
    // Override output path for Laravel's "public" directory.
    path: path.join(__dirname, '/public/dist'),
    publicPath: '/dist/',
  },
});
