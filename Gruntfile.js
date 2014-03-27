'use strict';

module.exports = function(grunt) {

	var export_dir = '../wp/wp-content/plugins';

	// Project configuration.
	grunt.initConfig({

		// Load grunt project configuration
		pkg: grunt.file.readJSON('package.json'),

		// Configure less CSS compiler
		less: {
			build: {
				options: {
					compress: true,
					cleancss: true,
					ieCompat: true
				},
				files: {
					'restaurant-table-bookings/assets/css/style.css': [
						'restaurant-table-bookings/assets/src/less/style.less',
						'restaurant-table-bookings/assets/src/less/style-*.less'
					]
				}
			}
		},

		// Configure JSHint
		jshint: {
			test: {
				src: 'restaurant-table-bookings/assets/src/js/*.js'
			}
		},

		// Concatenate scripts
		concat: {
			build: {
				files: {
					'restaurant-table-bookings/assets/js/frontend.js': [
						'restaurant-table-bookings/assets/src/js/frontend.js',
						'restaurant-table-bookings/assets/src/js/frontend-*.js'
					]
				}
			}
		},

		// Minimize scripts
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
			},
			build: {
				files: {
					'restaurant-table-bookings/assets/js/frontend.js' : 'restaurant-table-bookings/assets/js/frontend.js',
					'restaurant-table-bookings/assets/js/admin.js' : 'restaurant-table-bookings/assets/js/admin.js'
				}
			}
		},

		sync: {
			main: {
				files: [
					{
						cwd: 'restaurant-table-bookings/',
						src: '**',
						dest: export_dir + '/<%= pkg.name %>'
					}
				]
			}
		},

		// Watch for changes on some files and auto-compile them
		watch: {
			less: {
				files: ['restaurant-table-bookings/assets/src/less/*.less'],
				tasks: ['less', 'sync']
			},
			js: {
				files: ['restaurant-table-bookings/assets/src/js/*.js'],
				tasks: ['jshint', 'concat', 'uglify', 'sync']
			},
			sync: {
				files: ['!restaurant-table-bookings/**/*.less', '!restaurant-table-bookings/**/*.css', '!restaurant-table-bookings/**/*.js', 'restaurant-table-bookings/**/*'],
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
