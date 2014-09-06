/* jshint node:true */
module.exports = function( grunt ) {
	'use strict';

	var util = grunt.option( 'util' );
	var config = {};

	function cleanSourceMaps( content, srcpath ) {

		if ( '.min.js' == srcpath.match( '.min.js$' ) || '.min.css' == srcpath.match( '.min.css$' ) ) { // jshint ignore:line
			content = content.replace( /^.*sourceMappingURL=.*$/mg, '' );
		}

		return content;
	}

	// copy files to /build dir
	config.copy = {
		build: {
			files: [{
				expand: true,
				src: [ '**' ],
				dest: 'build/'
			}],
			options: {
				process: cleanSourceMaps,
				noProcess: [
					'**/*.{png,jpg,gif}',
					'**/*.{eot,svg,ttf,woff}'
				]
			}
		}
	};

	return config;
};
