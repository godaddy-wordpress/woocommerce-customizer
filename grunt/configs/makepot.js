/* jshint node:true */
module.exports = function( grunt ) {
	'use strict';

	var config = {};

	// Makepot
	config.makepot = {
		makepot: {
			options: {
				cwd: '',
				domainPath: 'i18n/languages',
				potFilename: grunt.option( 'plugin-slug' ) + '.pot',
				potHeaders: { 'report-msgid-bugs-to': 'https://github.com/skyverge/' + grunt.option( 'plugin-slug' ) + '/issues' },
				processPot: function( pot ) {
					delete pot.headers['x-generator'];
					return pot;
				}, // jshint ignore:line
				type: 'wp-plugin',
				updateTimestamp: false
			}
		}
	};


	return config;
};
