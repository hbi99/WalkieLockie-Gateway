'use strict';

module.exports = function (grunt) {
	grunt.initConfig({

		// metadata
		pkg : grunt.file.readJSON('package.json'),

		mocha_phantomjs: {
			files: {
				src: ['tests/*.htm']
			}
		}

	});

	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-jasmine');
	grunt.loadNpmTasks('grunt-mocha-phantomjs');

	grunt.registerTask('default', [
		'jshint',
		'concat:nodelib'
	]);

	grunt.registerTask('lib', [ 'concat:nodelib' ]);

	grunt.registerTask('test', [ 'mocha_phantomjs' ]);

};

