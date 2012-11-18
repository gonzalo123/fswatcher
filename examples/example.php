<?php
include __DIR__ . '/../vendor/autoload.php';

use FsWatcher\Watcher;
use Sh\Sh;

$directoryToWatch = './';
$sh = new Sh();

$watcher = Watcher::factory($directoryToWatch);
$watcher->registerExtensionToWatch('php');

$watcher->onSave(function ($file) use ($sh) {
    echo $sh->php("-l {$file}");
    echo "\n";
});

$watcher->onDelete(function ($file) {
    echo "DELETED: {$file}\n";
});

$watcher->onCreate(function ($file) {
    echo "CREATED: {$file}\n";
});

$watcher->start();