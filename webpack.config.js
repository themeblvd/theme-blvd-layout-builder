const path = require('path');
const webpack = require('webpack');
const addUnminified = require('unminified-webpack-plugin');

module.exports = {
  mode: 'production',
  entry: './inc/admin/assets/src/editor.js',
  output: {
    path: path.resolve(__dirname, 'inc'),
    filename: './admin/assets/js/editor.min.js'
  },
  externals: {
    wp: 'wp',
    l10n: 'themeblvdLayoutBuilderEditorL10n'
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader'
        }
      }
    ]
  },
  plugins: [
    /*
     * Always add an unminified version that can be
     * included via PHP when SCRIPT_DEBUG is true.
     */
    new addUnminified()
  ]
};
