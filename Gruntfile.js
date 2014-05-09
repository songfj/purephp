'use strict';
module.exports = function(grunt) {

    grunt.initConfig({
        jshint: {
            options: {
                jshintrc: '.jshintrc'
            },
            files: [
                'Gruntfile.js',
                'content/js/*.js'
            ]
        },
        recess: {
            dist: {
                options: {
                    compile: true,
                    compress: true
                },
                files: {
                    'content/css/app.min.css': [
                        'content/vendor/css/bootstrap.css',
                        'content/vendor/css/font-awesome.css',
                        'content/css/sprites.css',
                        'content/less/app.less',
                        'content/less/galleries.less',
                        'content/vendor/css/helpers.css',
                    ]
                }
            }
        },
        jsbeautifier: {
            files: ['Gruntfile.js', 'content/js/*.js']
        },
        uglify: {
            dist: {
                files: {
                    'content/js/app.min.js': [
                        'content/vendor/js/jquery.js',
                        'content/vendor/js/bootstrap.min.js',
                        'content/vendor/js/happycookies.js',
                        'content/vendor/js/jquery.cycle2.min.js',
                        'content/vendor/js/jquery.bgsize.js',
                        'content/js/app.js'
                    ]
                }
            }
        },
        watch: {
            options: {
                livereload: true
            },
            less: {
                files: [
                    'content/less/*.less',
                    'content/css/*.css',
                    '!content/css/app.min.css'
                ],
                tasks: ['recess']
            },
            js: {
                files: [
                    'content/js/*.js',
                    '!content/js/app.min.js'
                ],
                tasks: ['jsbeautifier', 'uglify']
            }
        },
        clean: {
            dist: [
                'content/css/app.min.css',
                'content/js/app.min.js'
            ]
        }
    });

    // Load tasks
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-recess');
    grunt.loadNpmTasks('grunt-jsbeautifier');

    // Register tasks
    grunt.registerTask('default', [
        'clean',
        'recess',
        'jsbeautifier',
        'uglify'
    ]);
    grunt.registerTask('dev', [
        'watch'
    ]);

};
