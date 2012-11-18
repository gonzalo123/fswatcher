Filesystem watcher with PHP

[![Build Status](https://secure.travis-ci.org/gonzalo123/fswatcher.png?branch=master)](https://travis-ci.org/gonzalo123/fswatcher)

Actualy only works with Linux. Uses inotifywait to detect modifications and Événement to manage events

One usage example:
```php
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
```
