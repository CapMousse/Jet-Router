<?php

if (is_dir('../vendor/') && is_file('../vendor/autoload.php')) {
    include __DIR__.'../vendor/autoload.php';
} else {
    include __DIR__.'/../autoload.dist.php';
}