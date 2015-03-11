/**
 * Grunt configuration - http://gruntjs.com
 */


module.exports = function (grunt) {
    'use strict';

    require('time-grunt')(grunt);

    // Project configuration.
    grunt.initConfig({
            pkg: grunt.file.readJSON('package.json'),

            // files to be used (minimatch syntax) - https://github.com/isaacs/minimatch
            files: {
                phplint: [
                    'src/*.php',
                    'tests/*.php'
                ]
            },

            dirs: {
                phpcs: [
                    'src',
                    'tests'
                ],
                phpmd: [
                    'src',
                    'tests'
                ]
            },

            phplint: {
                files: {
                    src: '<%= files.phplint %>'
                }
            },

            // https://github.com/jharding/grunt-exec
            exec: {

                // https://github.com/sebastianbergmann/phpunit/
                'phpunit': {
                    cmd: 'vendor/bin/phpunit -c phpunit.xml.dist'
                },

                'phpunit-ci': {
                    cmd: 'vendor/bin/phpunit -c phpunit.xml.dist ' +
                        '--coverage-html build/coverage ' +
                        '--coverage-clover build/logs/clover.xml ' +
                        '--log-junit build/logs/junit.xml'
                },

                'phpunit-travis': {
                    cmd: 'vendor/bin/phpunit --coverage-clover build/logs/clover.xml'
                },

                // http://www.squizlabs.com/php-codesniffer
                'phpcs': {
                    cmd: function () {
                        return 'mkdir -p build/reports && vendor/bin/phpcs --report=full --report=checkstyle --tab-width=4 --report-checkstyle=build/reports/checkstyle.xml ' +
                            '--standard=PSR2 ' + grunt.config.data.dirs.phpcs.join(' ');
                    }
                },

                'phpcs-travis': {
                    cmd: function () {
                        return 'vendor/bin/phpcs --standard=PSR2 --extensions=php ' + grunt.config.data.dirs.phpcs.join(' ');
                    }
                },

                'phpmd': {
                    cmd: function () {
                        return 'vendor/bin/phpmd ' + grunt.config.data.dirs.phpmd.join(',') + ' text phpmd.xml --suffixes=php';
                    }
                },

                'phpmd-ci': {
                    cmd: function () {
                        return 'mkdir -p build/reports && vendor/bin/phpmd ' + grunt.config.data.dirs.phpmd.join(',') + ' xml phpmd.xml --suffixes=php --reportfile build/reports/phpmd.xml';
                    }
                },

                'composer-install': {
                    cmd: 'composer install'
                },

                'npm-install': {
                    cmd: 'npm install'
                },

                'bundle-install': {
                    cmd: 'bundle install'
                }
            }
        }
    )
    ;

    // Tasks from NPM
    grunt.loadNpmTasks('grunt-exec');

    // Task aliases
    grunt.registerTask('phpunit', 'PHP Unittests', 'exec:phpunit');
    grunt.registerTask('phpunit-ci', 'PHP Unittests for CI', 'exec:phpunit-ci');
    grunt.registerTask('phpcs', 'PHP Codesniffer', 'exec:phpcs');
    grunt.registerTask('phpmd', 'PHP Mess Detector', 'exec:phpmd');
    grunt.registerTask('install', 'Install all project dependencies', ['exec:npm-install', 'exec:composer-install', 'exec:bundle-install']);
    grunt.registerTask('default', ['qa']);
    grunt.registerTask('qa', ['exec:composer-install', 'phpunit', 'phpcs', 'phpmd']);
    grunt.registerTask('travis', ['exec:composer-install', 'exec:phpunit-travis', 'exec:phpcs-travis', 'phpmd']);
}
;
