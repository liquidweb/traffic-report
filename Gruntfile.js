module.exports = function( grunt ) {
	'use strict';

	// Project configuration.
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		copy: {
			main: {
				src: [
					'includes/**',
					'languages/**',
					'composer.json',
					//'CHANGELOG.md',
					'LICENSE.txt',
					'readme.txt',
					'traffic-report.php'
				],
				dest: 'dist/'
			},
		},

		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					mainFile: 'traffic-report.php',
					potFilename: 'traffic-report.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: false,

					exclude: [
						'bin',
						'dist',
						'node_modules',
						'vendor',
					],
				}
			}
		},
	} );

	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	grunt.registerTask( 'build', [ 'i18n', 'copy' ] );
	grunt.registerTask( 'i18n', [ 'makepot' ] );

	grunt.util.linefeed = '\n';

};
