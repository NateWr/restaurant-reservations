'use strict';

module.exports = function(grunt) {

	var export_dir = '../wp/wp-content/plugins';

	// Project configuration.
	grunt.initConfig({

		// Load grunt project configuration
		pkg: grunt.file.readJSON('package.json'),

		// Configure JSHint
		jshint: {
			test: {
				src: 'restaurant-reservations/assets/js/*.js'
			}
		},

		sync: {
			main: {
				files: [
					{
						cwd: 'restaurant-reservations/',
						src: '**',
						dest: export_dir + '/<%= pkg.name %>'
					}
				]
			}
		},

		// Watch for changes on some files and auto-compile them
		watch: {
			js: {
				files: ['restaurant-reservations/assets/js/*.js'],
				tasks: ['jshint', 'sync']
			},
			sync: {
				files: ['!restaurant-reservations/assets/js/*.js', 'restaurant-reservations/**/*'],
				tasks: ['sync']
			}
		}

	});

	// Load tasks
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-nodeunit');
	grunt.loadNpmTasks('grunt-sync');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');

	// Default task(s).
	grunt.registerTask('default', ['watch']);

};
