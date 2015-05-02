'use strict';

module.exports = function (grunt) {
	grunt.initConfig({

		// metadata
		pkg : grunt.file.readJSON('package.json'),

		casperjs: {
			options: {
				verbose: true,
				'log-level': 'debug',
				async: {
					parallel: false
				}
			},
			files: ['tests/test-*.js']
		}

	});

	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-casperjs');

	grunt.registerTask('default', [
		'jshint',
		'concat:nodelib'
	]);

	grunt.registerTask('lib', [ 'concat:nodelib' ]);

	grunt.registerTask('test', [ 'casperjs' ]);

};

