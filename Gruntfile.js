module.exports = function (grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        ts: {
            options: {
                diagnostics: false,
                declaration: false,
                removeComments: true,
                locale: "pt-BR",
                module: "system",
                target: "ES5",
                noImplicitAny: false,
                sourceMap: false,
                inlineSources: false,
                moduleResolution: "node",
                fast: "always"
            },
            default: {
                src: ["etc/ts/main.ts"],
                out: "public_html/assets/js/main.ts.js"
            }
        },
        less: {
            dev: {
                options: {
                    optimization: 1
                },
                files: {"public_html/assets/css/main.less.css": "etc/less/main.dev.less"}
            },
            main: {
                options: {
                    optimization: 1
                },
                files: {"public_html/assets/css/main.less.css": "etc/less/main.dev.less"}
            },
            prod: {
                options: {
                    syncImport: true,
                    compress: true,
                    yuicompress: true,
                    optimization: 2
                },
                files: {"public_html/assets/css/main.less.css": "etc/less/main.prod.less"}
            }
        },
        concat: {
            css: {
                src: [
                    "etc/less/import.css",
                    "public_html/assets/css/main.less.css"
                ],
                dest: "public_html/assets/css/main.css"
            },
            jsdevsrc: {
                src: ["etc/js/topo.js"],
                dest: "public_html/assets/js/main.src.js"
            },
            jsdevmain: {
                src: [
                    "public_html/assets/js/main.vendor.js",
                    "public_html/assets/js/main.src.js"
                ],
                dest: "public_html/assets/js/main.js"
            },
            jsprod: {
                src: [
                    "public_html/assets/js/main.vendor.js",
                    "public_html/assets/js/main.src.js",
                ],
                dest: "public_html/assets/js/main.js"
            },
            jsprodmin: {
                src: [
                    "public_html/assets/js/main.vendor.js",
                    "public_html/assets/js/main.src.min.js",
                ],
                dest: "public_html/assets/js/main.min.js"
            }
        },
        cssmin: {
            css: {
                src: "public_html/assets/css/main.css",
                dest: "public_html/assets/css/main.min.css"
            }
        },
        copy: {
            default: {
                files: [
                    {
                        src: 'public_html/assets/css/main.css',
                        dest: 'public_html/assets/css/main.min.css'
                    },
                    {
                        src: 'public_html/assets/js/main.js',
                        dest: 'public_html/assets/js/main.min.js'
                    },
                ],
            },
        },
        babel: {
            options: {
                sourceMap: false
            },
            dist: {
                files: {
                    'public_html/assets/js/main.src.es6.js': 'public_html/assets/js/main.src.js'
                }
            }
        },
        uglify: {
            options: {
                mangle: false,
                compress: {
                    drop_console: true,
                }
            },
            default: {
                src: "public_html/assets/js/main.src.es6.js",
                dest: "public_html/assets/js/main.src.min.js"
            }
        },
        clean: {
            css: ["public_html/assets/css/main.less.css"],
            js: [
                "public_html/assets/js/main.ts.js",
                "public_html/assets/js/main.vendor.js",
                "public_html/assets/js/main.src.js",
                "public_html/assets/js/main.src.min.js",
                "public_html/assets/js/main.src.es6.js"
            ],
        },
        watch: {
            css: {
                files: ["etc/less/*.less", "etc/less/*.css", "etc/less/inc/*.less", "etc/less/inc/**/*.less"],
                tasks: ["less:main", "concat:css", "copy"]
            },
            ts: {
                files: ['etc/ts/**/*.ts'],
                tasks: ["ts", "concat:jsdevsrc", "concat:jsdevmain", "copy"]
            },
            js: {
                files: ['etc/js/*.js', 'etc/js/**/*.js'],
                tasks: ["ts", "concat:jsdevsrc", "concat:jsdevmain", "copy"]
            }
        }
    });

    grunt.loadNpmTasks("grunt-ts");
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-babel');

    grunt.registerTask('default', ["ts", "less:dev", "concat:css", "concat:jsdevsrc", "concat:jsdevmain", "copy", "watch"]);
    grunt.registerTask('prod', ["ts", "less:prod", "concat:css", "cssmin", "concat:jsdevsrc", "babel", "uglify", "concat:jsprod", "concat:jsprodmin", "clean"]);

};