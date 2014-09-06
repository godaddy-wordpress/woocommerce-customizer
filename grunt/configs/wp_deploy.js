/* jshint node:true */
module.exports = function( grunt ) {
	'use strict';

	var config = {};

	// Makepot
	config.wp_deploy = {
		deploy: {
			options: {
				plugin_slug: grunt.option( 'plugin-slug' ),
				svn_user: 'SkyVerge',
				build_dir: 'build',
				assets_dir: 'wp-assets'
			}
		}
	};

	return config;
};
