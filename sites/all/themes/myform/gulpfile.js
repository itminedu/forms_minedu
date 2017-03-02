'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var spritesmith = require('gulp.spritesmith');

gulp.task('sass:prod', function () {
  gulp.src('./scss/*.scss')
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(autoprefixer({
       browsers: ['last 2 version', '> 1%', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4']
    }))
    .pipe(gulp.dest('./css'));
});


gulp.task('sprite', function () {
    var spriteData = gulp.src('images/sprites/*.jpg')
        .pipe(spritesmith({
            /* this whole image path is used in css background declarations */
            imgName: '../images/footer-mobile-icons-sprite.jpg',
            cssName: 'sprite.css'
        }));
    spriteData.img.pipe(gulp.dest('images/sprites'));
    spriteData.css.pipe(gulp.dest('images/sprites'));
});


gulp.task('sass:dev', function () {
  gulp.src('./scss/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({outputStyle: 'normal'}).on('error', sass.logError))
    .pipe(autoprefixer({
       browsers: ['last 2 version', '> 1%', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4']
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./css'));
});

gulp.task('sass:watch', function () {
  gulp.watch('./scss/**/*.scss', ['sass:dev']);
});

gulp.task('default', ['sass:dev', 'sass:watch']);
