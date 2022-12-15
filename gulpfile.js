import gulp from "gulp";
import phpMinify from "@cedx/gulp-php-minify";
import fs from "fs";
import path from "path";
import UglifyPHP from "uglify-php";
import glob from "glob";



const assets = [
    './image/default.png',
    './image/default-admin.png',
    './image/default-admin-bad.png',
    './image/console.png',
    './image/server.png',
    './errores.txt'
];
const nodb = process.argv.slice(2).find((v)=>v.indexOf("--nodb") !== -1) !== undefined;
const minify = process.argv.slice(2).find((v)=>v.indexOf("--min") !== -1) !== undefined;

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
function compressPhp(finish) {
    const dirs = [
        "./**/*.php",
        "!node_modules/**/*.php",
        "!libs/**/*.php",
        "!build/**/*.php",
        "!test.php"
    ];
    let dirBuild = (minify)? ".tmp_compress": "build";
    if (nodb) dirs.push("!scripts/database.php");
    return gulp.src(dirs, { read: false })
        .pipe(phpMinify({ binary: "C:\\xampp\\php\\php.exe" }))
        //.pipe(phpMinify({ mode: "safe" }))
        .pipe(gulp.dest(dirBuild))
        .on('end', finish);
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
        if (fs.existsSync("./.tmp_compress")) {
            fs.rmdirSync("./.tmp_compress", { force: true, recursive: true });
            await wait(2000);
        }
        fs.mkdirSync("./build");
        fs.mkdirSync("./build/image");
        fs.mkdirSync("./build/tmp_files");
        fs.mkdirSync("./build/removes");
        fs.mkdirSync("./build/olds");
        await wait(500);
        resolve();
    });
}

async function minifyAll() {
    const options = {
        excludes: [
          '$GLOBALS',
           '$_SERVER',
           '$_GET',
           '$_POST',
           '$_FILES',
           '$_REQUEST',
           '$_SESSION',
           '$_ENV',
           '$_COOKIE',
           '$php_errormsg',
           '$HTTP_RAW_POST_DATA',
           '$http_response_header',
           '$argc',
           '$argv',
           '$this'
        ],
        minify: {
           replace_variables: false,
           remove_whitespace: true,
           remove_comments: true,
           minify_html: false
        }
    }

    const existFolder = (path)=>{
        if (!fs.existsSync(path)) {
            fs.mkdirSync(path);
        }
    };

    glob("./.tmp_compress/**/*.php", async(err, files)=>{
        if (err) return console.log('Error');
        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            // Check dir
            const checkDir = file.slice(0, file.lastIndexOf('/') + 1).replace('.tmp_compress', 'build');
            existFolder(checkDir);

            const nameFile = file.slice(file.lastIndexOf('/') + '/'.length, file.length);
            console.log(`Minify: ${nameFile}`);
            await UglifyPHP.minify(file, {
                ...options,
                output: file.replace('.tmp_compress', 'build')
            });
        }
        await wait(1000);
        fs.rmdirSync("./.tmp_compress", { force: true, recursive: true });
    });
}

(async()=>{
    console.log("Limpiando...");
    await clearBuild();
    console.log("Copiando archivos...");
    copyFiles();
    console.log("Procesando...");
    compressPhp(()=>{
        if (minify) {
            console.log("Minify...");
            minifyAll();
        }
    });
})();