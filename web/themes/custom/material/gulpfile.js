let gulp = require('gulp'),
  sass = require('gulp-sass')(require('sass')),
  sourcemaps = require('gulp-sourcemaps'),
  $ = require('gulp-load-plugins')(),
  cleanCss = require('gulp-clean-css'),
  rename = require('gulp-rename'),
  postcss = require('gulp-postcss'),
  autoprefixer = require('autoprefixer'),
  postcssInlineSvg = require('postcss-inline-svg'),
  browserSync = require('browser-sync').create()
  pxtorem = require('postcss-pxtorem'),
    postcssProcessors = [
        postcssInlineSvg({
      removeFill: true,
      paths: ['./node_modules/bootstrap-icons/icons']
    }),
        pxtorem({
            propList: ['font', 'font-size', 'line-height', 'letter-spacing', '*margin*', '*padding*'],
            mediaQuery: true
        })
  ];

const paths = {
  scss: {
    dest: './css',
    src: './scss/**/*.scss',
  },
  js: {
    bootstrap: './node_modules/bootstrap/dist/js/bootstrap.min.js',
    popper: './node_modules/@popperjs/core/dist/umd/popper.min.js',
    barrio: '../../contrib/bootstrap_barrio/js/barrio.js',
    dest: './js'
  },
  twig: './templates'
}

// Compile sass into CSS & auto-inject into browsers
function styles() {
  return gulp.src([paths.scss.src])
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe($.postcss(postcssProcessors))
    .pipe(postcss([autoprefixer({
      overrideBrowserslist: [
        'Chrome >= 35',
        'Firefox >= 38',
        'Edge >= 12',
        'Explorer >= 10',
        'iOS >= 8',
        'Safari >= 8',
        'Android 2.3',
        'Android >= 4',
        'Opera >= 12']
    })]))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(cleanCss())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.scss.dest))
}

// Move the javascript files into our js folder
// function js () {
//   return gulp.src([paths.js.bootstrap, paths.js.popper, paths.js.barrio])
//     .pipe(gulp.dest(paths.js.dest))
//     .pipe(browserSync.stream())
// }

// Static Server + watching scss/html files
function serve() {
  browserSync.init({
    files: [paths.scss.dest, paths.js.dest, paths.twig],
    proxy: 'http://local.site/',
    serveStatic: ["css"],
    ghostMode: {
      clicks: true,
      forms: true,
      scroll: false
    }
  })
  gulp.watch([paths.scss.src, paths.twig], styles);
}

const build = gulp.series(styles, gulp.parallel(serve))

exports.styles = styles
// exports.js = js
exports.serve = serve

exports.default = build
