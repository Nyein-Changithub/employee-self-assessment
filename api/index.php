<?php

// Vercel entry point: Vercel's filesystem is read-only except /tmp,
// so Laravel's writable folders are created there before booting.
foreach (['framework/views', 'framework/cache', 'framework/sessions', 'logs'] as $dir) {
    if (! is_dir("/tmp/storage/{$dir}")) {
        mkdir("/tmp/storage/{$dir}", 0755, true);
    }
}

require __DIR__.'/../public/index.php';
