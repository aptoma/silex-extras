/**
 * Grunt configuration - http://gruntjs.com
 */


module.exports = function (grunt) {
    'use strict';

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

                // http://www.squizlabs.com/php-codesniffer
                'phpcs': {
                    cmd: function () {
                        return 'mkdir -p build/reports && vendor/bin/phpcs --report=full --report=checkstyle --tab-width=4 --report-checkstyle=build/reports/checkstyle.xml ' +
                            '--standard=PSR2 ' + grunt.config.data.dirs.phpcs.join(' ');
                    }
                },

                // http://phpmd.org/documentation/index.html
                'phpmd-cli': {
                    cmd: function () {
                        return 'mkdir -p build/reports && vendor/bin/phpmd ' + grunt.config.data.dirs.phpmd.join(',') + ' text phpmd.xml --suffixes=php';
                    }
                },

                'phpmd': {
                    cmd: function () {
                        return 'mkdir -p build/reports && vendor/bin/phpmd ' + grunt.config.data.dirs.phpmd.join(',') + ' xml phpmd.xml --suffixes=php --reportfile build/reports/phpmd.xml';
                    }
                },

                'composer-install': {
                    cmd: 'composer --dev install'
                },

                'ci-prepare': {
                    cmd: 'curl -s https://getcomposer.org/installer | php' +
                        '&& php composer.phar --dev install' +
                        '&& rm composer.phar '
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
    grunt.registerTask('default', ['phpunit']);
    grunt.registerTask('jenkins', ['exec:ci-prepare', 'phpunit-ci', 'phpcs', 'phpmd']);
}
;
