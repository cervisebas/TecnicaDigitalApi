import gulp from "gulp";
import phpMinify from "@cedx/gulp-php-minify";
import fs from "fs";
import path from "path";



const assets = [
    './image/default.png',
    './image/default-admin.png',
    './image/default-admin-bad.png',
    './image/console.png'
];
const nodb = process.argv.slice(2).find((v)=>v.indexOf("--nodb") !== -1) !== undefined;

function wait(time) {
    return new Promise((resolve)=>setTimeout(resolve, time));
}
function copyRecursiveSync(src, dest) {
    var exists = fs.existsSync(src);
    var stats = exists && fs.statSync(src);
    var isDirectory = exists && stats.isDirectory();
    if (isDirectory) {
      fs.mkdirSync(dest);
      fs.readdirSync(src).forEach(function(childItemName) {
        copyRecursiveSync(path.join(src, childItemName),
                          path.join(dest, childItemName));
      });
    } else {
      fs.copyFileSync(src, dest);
    }
}
function compressPhp() {
    const dirs = [
        "./**/*.php",
        "!node_modules/**/*.php",
        "!libs/**/*.php",
        "!build/**/*.php"
    ];
    if (nodb) dirs.push("!scripts/database.php");
    return gulp.src(dirs, { read: false })
        .pipe(phpMinify({ binary: "C:\\xampp\\php\\php.exe" }))
        //.pipe(phpMinify({ mode: "safe" }))
        .pipe(gulp.dest("build"));
}
function copyFiles() {
    copyRecursiveSync("./libs", "./build/libs");
    console.log('Copiando: "./libs" => "./build/libs"');
    assets.forEach((v)=>{
        const dest = `./build/${v.replace('./', '')}`;
        console.log(`Copiando: ${v} => ${dest}`);
        copyRecursiveSync(v, dest);
    });
}
function clearBuild() {
    return new Promise(async(resolve)=>{
        if (fs.existsSync("./build")) {
            fs.rmdirSync("./build", { force: true, recursive: true });
            await wait(2000);
        }
        fs.mkdirSync("./build");
        fs.mkdirSync("./build/image");
        fs.mkdirSync("./build/tmp_files");
        await wait(500);
        resolve();
    });
}

(async()=>{
    console.log("Limpiando...");
    await clearBuild();
    console.log("Copiando archivos...");
    copyFiles();
    console.log("Procesando...");
    compressPhp();
})();