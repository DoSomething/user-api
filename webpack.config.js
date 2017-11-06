const webpack = require('webpack');
const configure = require('@dosomething/webpack-config');
const path = require('path');

module.exports = configure({
  entry: {
    app: './resources/assets/js/app.js'
  },
  output: {
    // Override output path for Laravel's "public" directory.
    path: path.join(__dirname, '/public/dist'),
  },
  plugins: [
    new webpack.DefinePlugin({
      PUCK_URL: JSON.stringify(process.env.PUCK_URL) || null,
    }),
  ],
});
