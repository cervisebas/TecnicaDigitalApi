import gulp from "gulp";
import phpMinify from "@cedx/gulp-php-minify";

export function compressPhp() {
  return gulp.src("**/*.php", { read: false })
    .pipe(phpMinify({ binary: "C:\\xampp\\php\\php.exe" }))
    .pipe(gulp.dest("build"));
}