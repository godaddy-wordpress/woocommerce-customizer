/* jshint node:true */
module.exports = function( grunt ) {
	'use strict';

	var _ = require( 'underscore' );
	var config = {};

	// Delete source map from the CoffeeScript compilation
	config.clean = {
		dev: [ '<%= dirs.js %>/admin/*.min.js.map', '<%= dirs.js %>/frontend/*.min.js.map' ],
		build_dir: [ '<%= dirs.build %>/**' ],
		build: [
			// Delete dev files and dirs
			'<%= dirs.build %>/grunt',
			'<%= dirs.build %>/node_modules',
			'<%= dirs.build %>/wp-assets',
			'<%= dirs.build %>/package.json',
			'<%= dirs.build %>/Gruntfile.js',

			// Delete map files
			'<%= dirs.build %>/<%= dirs.js %>/admin/*.map',
			'<%= dirs.build %>/<%= dirs.js %>/frontend/*.map',
			'<%= dirs.build %>/<%= dirs.css %>/admin/*.map',
			'<%= dirs.build %>/<%= dirs.css %>/frontend/*.map',

			// Delete .coffee files
			'<%= dirs.build %>/<%= dirs.js %>/admin/*.coffee',
			'<%= dirs.build %>/<%= dirs.js %>/frontend/*.coffee',

			// Delete unminified .js files
			'<%= dirs.build %>/<%= dirs.js %>/admin/*.js',
			'<%= dirs.build %>/<%= dirs.js %>/frontend/*.js',
			'!<%= dirs.build %>/<%= dirs.js %>/admin/*.min.js',
			'!<%= dirs.build %>/<%= dirs.js %>/frontend/*.min.js',

			// Delete .scss files
			'<%= dirs.build %>/<%= dirs.css %>/admin/*.scss',
			'<%= dirs.build %>/<%= dirs.css %>/frontend/*.scss',

			// Delete unminified .css files
			'<%= dirs.build %>/<%= dirs.css %>/admin/*.css',
			'<%= dirs.build %>/<%= dirs.css %>/frontend/*.css',
			'!<%= dirs.build %>/<%= dirs.css %>/admin/*.min.css',
			'!<%= dirs.build %>/<%= dirs.css %>/frontend/*.min.css',

			// Delete misc files
			'<%= dirs.build %>/modman',
			'<%= dirs.build %>/**.DS_Store',
			'<%= dirs.build %>/**.zip'
		]
	};

	return config;
};
