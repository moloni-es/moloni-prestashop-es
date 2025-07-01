<?php

/**
 * 2025 - Moloni.com
 *
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Moloni
 * @copyright Moloni
 * @license   https://creativecommons.org/licenses/by-nd/4.0/
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

use Moloni\Configurations;

define('_PS_VERSION_', 'BUILDER');
define('PLUGIN_VERSION', ltrim(getenv('PLUGIN_VERSION') ?: 'v0.0.01', 'v'));

require_once __DIR__ . '/src/Configurations.php';
require_once __DIR__ . '/src/Exceptions/MoloniException.php';

const INCLUDE_DIRS = [
    'config',
    'mails',
    'src',
    'translations',
    'upgrade',
    'views',
    'vendor',
];
const INCLUDE_FILES = [
    '.htaccess',
    'index.php',
    'CoreModule.php',
    'molonies.php',
    'composer.json',
    'composer.lock',
];

function copyDir($src, $dst, $exclude = [])
{
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    while (false !== ($file = readdir($dir))) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        if (in_array($file, $exclude)) {
            continue;
        }

        $srcPath = "$src/$file";
        $dstPath = "$dst/$file";

        if (is_dir($srcPath)) {
            copyDir($srcPath, $dstPath, $exclude);
        } else {
            @mkdir(dirname($dstPath), 0755, true);
            copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
}

function buildZip()
{
    $platform = (new Configurations())->getAll();

    $base = __DIR__;

    $folderName = $platform['folder_name'];
    $zipName = $platform['zip_name'];

    $buildDir = "$base/build/$folderName";

    // Clean old build
    if (is_dir($buildDir)) {
        PHP_OS_FAMILY === 'Windows' ?
            shell_exec("rd /s /q \"$buildDir\"") :
            shell_exec('rm -rf ' . escapeshellarg($buildDir));

        echo "✅ Deleted existing build \n";
    }

    // Step 1: Copy allowlisted root files
    @mkdir($buildDir, 0777, true);

    foreach (INCLUDE_FILES as $fileName) {
        $srcPath = "$base/$fileName";
        $dstPath = "$buildDir/$fileName";

        if (!is_file($srcPath)) {
            continue;
        }

        copy($srcPath, $dstPath);
    }

    // Step 2: Copy allowlisted directories
    foreach (INCLUDE_DIRS as $dirName) {
        $srcDir = "$base/$dirName";

        if (!is_dir($srcDir)) {
            continue;
        }

        copyDir($srcDir, "$buildDir/$dirName");
    }

    // Step 3: Zip the build folder
    $zip = new ZipArchive();
    $zipPath = "$base/build/$zipName.zip";
    $zipFolderName = $zipName;

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($buildDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($buildDir) + 1);

            // Normalize to forward slashes
            $relativePath = str_replace('\\', '/', $relativePath);
            $zipPathInArchive = "$zipFolderName/$relativePath";
            $zipPathInArchive = str_replace('\\', '/', $zipPathInArchive);

            $zip->addFile($filePath, $zipPathInArchive);
        }

        $zip->close();
        echo "✅ Created ZIP: $zipPath\n";
    } else {
        echo "❌ Failed to create ZIP: $zipPath\n";
    }
}

buildZip();
