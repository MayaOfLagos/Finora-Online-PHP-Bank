<?php

/**
 * Laravel - Shared Hosting Bootstrap
 * 
 * This file redirects all requests to the public/index.php
 * for shared hosting environments where Laravel is in public_html
 */

// Change directory to public folder and load the real index.php
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
