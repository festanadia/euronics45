// Gulp.
const gulp = require('gulp'),

 sass = require('gulp-sass'),
 rename = require('gulp-rename'),
 concat = require('gulp-concat'),
 exec = require('child_process').exec,
 sourcemaps = require('gulp-sourcemaps'),
 babel = require('gulp-babel'),
 minify = require('gulp-minify'),
 frep = require('gulp-frep');

 var del = require('del');

 
 var PRODUCTION = process.argv.includes('-production');
 const jsdest = 'amd/build';
 const jssrc = 'amd/src/**/*.js';

// Pattern for newline replacement for windows development environment.
var pattern = [{
    pattern: /\\r\\n/g,
    replacement: '\\n'
}];

 gulp.task('styles', function() {
    var task = gulp.src(['./scss/*.scss'])
    .pipe(sass({
        outputStyle: 'compressed'
    })).pipe(concat('styles.css'))
  
  
    return task.pipe(rename('styles.css'))
    .pipe(gulp.dest('./'));
});





gulp.task('compileAmd', function(cb) {
    exec('npm run buildAmd', function (err, stdout, stderr) {
        console.log(stdout);
        console.log(stderr);
        cb(err);
      });
});





gulp.task('compress', function() {
    var task = gulp.src(jssrc)
    .pipe(sourcemaps.init());
    
        task = task.pipe(babel({presets: [["@babel/preset-env"]]}))
        .pipe(minify({
            ext: {
                min: '.min.js'
            },
            noSource: true,
            ignoreFiles: []
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(frep(pattern));

    return task.pipe(gulp.dest(jsdest));
});

gulp.task('clean', function(done) {
    del(jsdest);
    done();
});



gulp.task('watch', function(done) {
    gulp.watch([
        './scss/*.scss',
    ],gulp.series('styles'))
    gulp.watch([
        './amd/src/*.js',
    ],gulp.series('compileAmd'))
    done();
});


gulp.task('build', gulp.series('clean','styles','compress'));