const gulp = require('gulp'),
    sass = require('gulp-dart-sass'),
    path = require('path'),
    streamify = require('gulp-streamify'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-minify-css'),
    browserify = require('browserify'),
    babelify = require('babelify'),
    sourcemaps = require('gulp-sourcemaps'),
    babel = require('gulp-babel'),
    source = require('vinyl-source-stream'),
    gutil = require('gulp-util'),
    jshint = require('gulp-jshint'),
    stylish = require('jshint-stylish'),
    uglify = require('gulp-uglify'),
    minify = require('gulp-babel-minify'),    
    rename = require('gulp-rename'),
    clean = require('gulp-clean'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify');

function swallow_error(error) {
  console.log(error.toString());
  this.emit('end');
}

gulp.task('styles', function() {
  return gulp.src('src/frontend/styles/*.scss')
    .pipe(sass({
      paths: [ path.join(__dirname, 'scss', 'includes') ]
    }))
    .on('error', swallow_error)
    .pipe(autoprefixer('last 3 version', 'android >= 3', { cascade: true }))
    .on('error', swallow_error)
    .pipe(gulp.dest('dist/css'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(minifycss())
    .on('error', swallow_error)
    .pipe(gulp.dest('dist/css'));
});

gulp.task('scripts', function() {
  gulp.src('src/frontend/scripts/*.js')
    .pipe(sourcemaps.init())
    .pipe(babel())
    .on('error', swallow_error)
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./dist/js'))
});

gulp.task('jslint', function(){
  return gulp.src([
      './src/scripts/**/*.js'
    ]).pipe(jshint('tests/.jshintrc'))
    .on('error',gutil.noop)
    .pipe(jshint.reporter(stylish))
    .on('error', swallow_error);
});

gulp.task('clean', function() {
  return gulp.src(['dist/css', 'dist/js'], { read: false })
    .pipe(clean());
});

gulp.task('watch', function() {
  gulp.watch('src/frontend/styles/**/*.*', ['styles']);
  gulp.watch('src/frontend/scripts/**/*.*', ['jslint', 'scripts']);
});

gulp.task('default', ['clean'], function() {
    gulp.start('styles', 'jslint', 'scripts', 'watch');
});
