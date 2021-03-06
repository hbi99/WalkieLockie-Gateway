'use strict';

module.exports = function (grunt) {
	grunt.initConfig({
		// metadata
		pkg : grunt.file.readJSON('package.json'),

		'mochaTest' : {
			all: {
				options: {
					reporter: 'list'
				},
				src: [
					//'tests/device-register.test.js'
					//'tests/domain-register.test.js'
					'tests/domain-qr.test.js'
				]
			}
		}
	});


	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-mocha-test');

	grunt.registerTask('default', [ 'jshint' ]);

	grunt.registerTask('test', [ 'mochaTest' ]);

};

