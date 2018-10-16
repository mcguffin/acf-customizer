var autoprefixer = require('gulp-autoprefixer');
var concat = require('gulp-concat');
var gulp = require('gulp');
var gulputil = require('gulp-util');
var rename = require('gulp-rename');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');

function do_scss( src ) {
	var dir = src.substring( 0, src.lastIndexOf('/') );
	return gulp.src( './src/scss/' + src + '.scss' )
		.pipe( sourcemaps.init() )
		.pipe( sass( { outputStyle: 'nested' } ).on('error', sass.logError) )
		.pipe( autoprefixer({
			browsers:['last 2 versions']
		}) )
		.pipe( gulp.dest( './css/' + dir ) )
        .pipe( sass( { outputStyle: 'compressed' } ).on('error', sass.logError) )
		.pipe( rename( { suffix: '.min' } ) )
        .pipe( sourcemaps.write() )
        .pipe( gulp.dest( './css/' + dir ) );

}

function do_js( src, dir ) {
	var dir = src.substring( 0, src.lastIndexOf('/') );
	return gulp.src( './src/js/' + src + '.js' )
		.pipe( sourcemaps.init() )
		.pipe( gulp.dest( './js/' + dir ) )
		.pipe( uglify().on('error', gulputil.log ) )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write() )
		.pipe( gulp.dest( './js/' + dir ) );
}

function concat_js( src, dest ) {
	return gulp.src( src )
		.pipe( sourcemaps.init() )
		.pipe( concat( dest ) )
		.pipe( gulp.dest( './js/' ) )
		.pipe( uglify().on('error', gulputil.log ) )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write() )
		.pipe( gulp.dest( './js/' ) );

}


gulp.task('scss-customizer', function() {
	return do_scss('admin/customize-acf-fieldgroup-control');
});
// gulp.task('scss-field-group', function() {
// 	return do_scss('admin/field-group');
// });
gulp.task('scss', gulp.parallel( /*'scss-field-group',*/ 'scss-customizer' ));



gulp.task('js-admin-jqser', function() {
	return concat_js(
		['node_modules/jquery-serializejson/jquery.serializejson.js',],
		'jquery-serializejson.js'
	);
});
gulp.task('js-admin-legacy', function() {
	return do_js('legacy/5.6/admin/customize-acf-fieldgroup-control');
});
gulp.task('js-admin-control', function() {
	return do_js('admin/customize-acf-fieldgroup-control');
});
gulp.task('js-admin-preview', function() {
	return do_js('admin/customize-acf-fieldgroup-preview');
});


gulp.task('js-admin', gulp.parallel( 'js-admin-jqser', 'js-admin-legacy', 'js-admin-control', 'js-admin-preview' ));


// gulp.task( 'js', function(){
// 	return concat_js( [
// 	], 'frontend.js');
// } );


gulp.task('build', gulp.parallel('scss','js-admin') );


gulp.task('watch', function() {
	// place code for your default task here
	gulp.watch('./src/scss/**/*.scss',gulp.series( 'scss' ) );
	gulp.watch('./src/js/**/*.js',gulp.parallel( 'js-admin') );
});

gulp.task('default', gulp.series('build','watch'));
