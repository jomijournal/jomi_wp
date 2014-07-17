'use strict';
module.exports = function(grunt) {
  // Load all tasks
  require('load-grunt-tasks')(grunt);
  // Show elapsed time
  require('time-grunt')(grunt);

  var jsFileList = [
    'assets/js/plugins/bootstrap/transition.js',
    'assets/js/plugins/bootstrap/alert.js',
    'assets/js/plugins/bootstrap/button.js',
    'assets/js/plugins/bootstrap/carousel.js',
    'assets/js/plugins/bootstrap/collapse.js',
    'assets/js/plugins/bootstrap/dropdown.js',
    'assets/js/plugins/bootstrap/modal.js',
    'assets/js/plugins/bootstrap/tooltip.js',
    'assets/js/plugins/bootstrap/popover.js',
    'assets/js/plugins/bootstrap/scrollspy.js',
    'assets/js/plugins/bootstrap/tab.js',
    'assets/js/plugins/bootstrap/affix.js',
    'assets/js/plugins/*.js',
    'assets/js/_*.js'
  ];

  grunt.initConfig({
    jshint: {
      options: {
        jshintrc: '.jshintrc'
      },
      all: [
        'Gruntfile.js',
        'assets/js/*.js',
        '!assets/js/scripts.js',
        '!assets/**/*.min.*'
      ]
    },
    less: {
      dev: {
        files: {
          'assets/css/main.min.css': [
            'assets/less/app.less'
          ]
        },
        options: {
          compress: true,
          // LESS source map
          // To enable, set sourceMap to true and update sourceMapRootpath based on your install
          sourceMap: true,
          sourceMapFilename: 'assets/css/main.min.css.map',
          sourceMapRootpath: '/app/themes/roots/'
        }
      },
      build: {
        files: {
          'assets/css/main.min.css': [
            'assets/less/app.less'
          ]
        },
        options: {
          compress: true
        }
      }
    },
    concat: {
      options: {
        separator: ';',
      },
      dist: {
        src: [jsFileList],
        dest: 'assets/js/scripts.js',
      },
    },
    uglify: {
      dist: {
        files: {
          'assets/js/scripts.min.js': [jsFileList]
        }
      }
    },
    autoprefixer: {
      options: {
        browsers: ['last 2 versions', 'ie 8', 'ie 9', 'android 2.3', 'android 4', 'opera 12']
      },
      dev: {
        options: {
          map: 'assets/css/'
        },
        src: 'assets/css/main.css'
      },
      build: {
        src: 'assets/css/main.min.css'
      }
    },
    modernizr: {
      build: {
        devFile: 'assets/vendor/modernizr/modernizr.js',
        outputFile: 'assets/js/vendor/modernizr.min.js',
        files: {
          'src': [
            ['assets/js/scripts.min.js'],
            ['assets/css/main.min.css']
          ]
        },
        uglify: true,
        parseFiles: true
      }
    },
    version: {
      options: {
        file: 'lib/scripts.php',
        css: 'assets/css/main.min.css',
        cssHandle: 'roots_main',
        js: 'assets/js/scripts.min.js',
        jsHandle: 'roots_scripts'
      }
    },
    /*(version: {
      assets: {
        files: {
          'lib/scripts.php': ['assets/css/main.min.css', 'assets/js/scripts.min.js']
        }
      }
    },*/
    watch: {
      less: {
        files: [
          'assets/less/*.less',
          'assets/less/**/*.less'
        ],
        tasks: ['less:dev'/*, 'autoprefixer:dev'*/]
      },
      js: {
        files: [
          '<%= jshint.all %>'
        ],
        tasks: ['jshint', 'concat']
      },
      livereload: {
        // Browser live reloading
        // https://github.com/gruntjs/grunt-contrib-watch#live-reloading
        options: {
          livereload: true
        },
        files: [
          'assets/css/main.min.css',
          'assets/js/scripts.min.js',
          'templates/*.php',
          '*.php'
        ]
      }
    }
  });

  // Register tasks
  grunt.registerTask('default', [
    'dev'
  ]);
  grunt.registerTask('dev', [
    'jshint',
    'less:dev',
    //'autoprefixer:dev',
    'concat',
    'watch'
  ]);
  grunt.registerTask('build', [
    'jshint',
    'less:build',
    //'autoprefixer:build',
    'uglify',
    //'modernizr',
    'version'
  ]);
};