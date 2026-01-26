<?php

/**
 * Finora Bank - Server Setup & Deployment Script
 *
 * This script allows running essential server commands via web browser
 * for shared hosting environments without SSH access.
 *
 * SECURITY: Change the SECRET_KEY before deploying to production!
 * Access: yoursite.com/setup.php?key=YOUR_SECRET_KEY
 */

// ============================================
// SECURITY CONFIGURATION - CHANGE THIS!
// ============================================
define('SECRET_KEY', 'finora_setup_2026_change_me');
define('ALLOW_DANGEROUS_COMMANDS', false); // Set to true only when needed

// ============================================
// CONFIGURATION
// ============================================
// Laravel app path (parent of public folder)
define('FINORA_BASE_PATH', dirname(__DIR__));
define('FINORA_PUBLIC_PATH', __DIR__);
define('FINORA_PHP_BIN', '/usr/local/bin/php');
define('FINORA_COMPOSER', '/usr/local/bin/composer');
define('FINORA_NPM', '/usr/local/bin/npm');
define('FINORA_NODE', '/usr/local/bin/node');

// Increase limits for long-running commands
set_time_limit(600); // 10 minutes
ini_set('memory_limit', '512M');

// ============================================
// SECURITY CHECK
// ============================================
$providedKey = $_GET['key'] ?? $_POST['key'] ?? '';

if ($providedKey !== SECRET_KEY) {
    http_response_code(403);
    exit('<!DOCTYPE html><html><head><title>Access Denied</title></head><body style="font-family: system-ui; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #1a1a2e;"><div style="text-align: center; color: #fff;"><h1 style="font-size: 72px; margin: 0;">403</h1><p style="color: #888;">Access Denied</p></div></body></html>');
}

// ============================================
// HELPER FUNCTIONS
// ============================================

function runCommand($command, $cwd = null)
{
    $cwd = $cwd ?? FINORA_BASE_PATH;
    $output = [];
    $returnCode = 0;

    $fullCommand = 'cd '.escapeshellarg($cwd).' && '.$command.' 2>&1';
    exec($fullCommand, $output, $returnCode);

    return [
        'success' => $returnCode === 0,
        'output' => implode("\n", $output),
        'code' => $returnCode,
        'command' => $command,
    ];
}

function runArtisan($command)
{
    return runCommand(FINORA_PHP_BIN.' artisan '.$command.' --no-interaction');
}

function formatOutput($result)
{
    $status = $result['success'] ? '✅' : '❌';
    $statusClass = $result['success'] ? 'success' : 'error';

    return "<div class='result {$statusClass}'>
        <div class='result-header'>{$status} <code>{$result['command']}</code> (exit: {$result['code']})</div>
        <pre class='result-output'>".htmlspecialchars($result['output'] ?: 'No output').'</pre>
    </div>';
}

function checkEnvironment()
{
    $checks = [];

    // PHP Version
    $checks['PHP Version'] = [
        'value' => PHP_VERSION,
        'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
    ];

    // Required Extensions
    $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'curl'];
    foreach ($requiredExtensions as $ext) {
        $checks["ext-{$ext}"] = [
            'value' => extension_loaded($ext) ? 'Loaded' : 'Missing',
            'ok' => extension_loaded($ext),
        ];
    }

    // Writable directories
    $writableDirs = ['storage', 'storage/logs', 'storage/framework', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'bootstrap/cache'];
    foreach ($writableDirs as $dir) {
        $path = FINORA_BASE_PATH.'/'.$dir;
        $writable = is_dir($path) && is_writable($path);
        $checks["writable:{$dir}"] = [
            'value' => $writable ? 'Writable' : (is_dir($path) ? 'Not Writable' : 'Missing'),
            'ok' => $writable,
        ];
    }

    // .env file
    $checks['.env file'] = [
        'value' => file_exists(FINORA_BASE_PATH.'/.env') ? 'Exists' : 'Missing',
        'ok' => file_exists(FINORA_BASE_PATH.'/.env'),
    ];

    // Composer
    $composerCheck = runCommand('which composer || echo "not found"');
    $checks['Composer'] = [
        'value' => strpos($composerCheck['output'], 'not found') === false ? 'Available' : 'Not Found',
        'ok' => strpos($composerCheck['output'], 'not found') === false,
    ];

    return $checks;
}

// ============================================
// HANDLE ACTIONS
// ============================================
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$results = [];

if ($action) {
    switch ($action) {
        // === COMPOSER ===
        case 'composer_install':
            $results[] = runCommand(FINORA_COMPOSER.' install --no-dev --optimize-autoloader');
            break;

        case 'composer_install_dev':
            $results[] = runCommand(FINORA_COMPOSER.' install');
            break;

        case 'composer_update':
            $results[] = runCommand(FINORA_COMPOSER.' update --no-dev');
            break;

        case 'composer_dump':
            $results[] = runCommand(FINORA_COMPOSER.' dump-autoload -o');
            break;

            // === NPM ===
        case 'npm_install':
            $results[] = runCommand(FINORA_NPM.' install');
            break;

        case 'npm_build':
            $results[] = runCommand(FINORA_NPM.' run build');
            break;

        case 'npm_install_build':
            $results[] = runCommand(FINORA_NPM.' install');
            $results[] = runCommand(FINORA_NPM.' run build');
            break;

            // === ARTISAN - CACHE ===
        case 'cache_clear':
            $results[] = runArtisan('cache:clear');
            break;

        case 'config_clear':
            $results[] = runArtisan('config:clear');
            break;

        case 'config_cache':
            $results[] = runArtisan('config:cache');
            break;

        case 'route_clear':
            $results[] = runArtisan('route:clear');
            break;

        case 'route_cache':
            $results[] = runArtisan('route:cache');
            break;

        case 'view_clear':
            $results[] = runArtisan('view:clear');
            break;

        case 'view_cache':
            $results[] = runArtisan('view:cache');
            break;

        case 'event_clear':
            $results[] = runArtisan('event:clear');
            break;

        case 'event_cache':
            $results[] = runArtisan('event:cache');
            break;

            // === ARTISAN - OPTIMIZATION ===
        case 'optimize':
            $results[] = runArtisan('optimize');
            break;

        case 'optimize_clear':
            $results[] = runArtisan('optimize:clear');
            break;

        case 'clear_all':
            $results[] = runArtisan('cache:clear');
            $results[] = runArtisan('config:clear');
            $results[] = runArtisan('route:clear');
            $results[] = runArtisan('view:clear');
            $results[] = runArtisan('event:clear');
            break;

        case 'cache_all':
            $results[] = runArtisan('config:cache');
            $results[] = runArtisan('route:cache');
            $results[] = runArtisan('view:cache');
            $results[] = runArtisan('event:cache');
            break;

            // === ARTISAN - DATABASE ===
        case 'migrate':
            $results[] = runArtisan('migrate --force');
            break;

        case 'migrate_status':
            $results[] = runArtisan('migrate:status');
            break;

        case 'db_seed':
            $results[] = runArtisan('db:seed --force');
            break;

            // === ARTISAN - STORAGE ===
        case 'storage_link':
            $results[] = runArtisan('storage:link');
            break;

            // === ARTISAN - KEY ===
        case 'key_generate':
            if (ALLOW_DANGEROUS_COMMANDS) {
                $results[] = runArtisan('key:generate --force');
            } else {
                $results[] = ['success' => false, 'output' => 'Dangerous command blocked. Set ALLOW_DANGEROUS_COMMANDS to true.', 'code' => 1, 'command' => 'key:generate'];
            }
            break;

            // === ARTISAN - INFO ===
        case 'about':
            $results[] = runArtisan('about');
            break;

        case 'route_list':
            $results[] = runArtisan('route:list');
            break;

        case 'env':
            $results[] = runArtisan('env');
            break;

            // === ARTISAN - MAINTENANCE ===
        case 'down':
            $results[] = runArtisan('down --secret="finora-maintenance"');
            break;

        case 'up':
            $results[] = runArtisan('up');
            break;

            // === ARTISAN - QUEUE ===
        case 'queue_work':
            $results[] = runArtisan('queue:work --stop-when-empty');
            break;

        case 'queue_failed':
            $results[] = runArtisan('queue:failed');
            break;

        case 'queue_retry':
            $results[] = runArtisan('queue:retry all');
            break;

            // === ARTISAN - FILAMENT ===
        case 'filament_optimize':
            $results[] = runArtisan('filament:optimize'); // Caches components + icons
            break;

        case 'filament_cache':
            $results[] = runArtisan('filament:cache-components');
            $results[] = runArtisan('icons:cache');
            break;

        case 'filament_clear':
            $results[] = runArtisan('filament:clear-cached-components');
            $results[] = runArtisan('icons:clear');
            break;

            // === FULL SETUP ===
        case 'full_setup':
            $results[] = runCommand(FINORA_COMPOSER.' install --no-dev --optimize-autoloader');
            $results[] = runArtisan('storage:link');
            $results[] = runArtisan('migrate --force');
            $results[] = runArtisan('db:seed --force');
            $results[] = runArtisan('optimize'); // Laravel caching
            $results[] = runArtisan('filament:optimize'); // Filament caching
            break;

        case 'quick_deploy':
            $results[] = runArtisan('optimize:clear');
            $results[] = runArtisan('migrate --force');
            $results[] = runArtisan('optimize');
            $results[] = runArtisan('filament:cache-components');
            $results[] = runArtisan('icons:cache');
            break;

            // === FULL PERFORMANCE OPTIMIZATION ===
        case 'full_optimize':
            // Clear all caches first
            $results[] = runArtisan('optimize:clear');
            $results[] = runArtisan('filament:clear-cached-components');
            $results[] = runArtisan('icons:clear');
            // Rebuild all caches for maximum performance
            $results[] = runArtisan('config:cache');
            $results[] = runArtisan('route:cache');
            $results[] = runArtisan('view:cache');
            $results[] = runArtisan('event:cache');
            // Filament-specific optimizations
            $results[] = runArtisan('filament:optimize'); // Combines component + icon caching
            // Composer autoload optimization
            $results[] = runCommand(FINORA_COMPOSER.' dump-autoload -o');
            break;

            // === PERMISSIONS ===
        case 'fix_permissions':
            $results[] = runCommand('chmod -R 755 '.FINORA_BASE_PATH);
            $results[] = runCommand('chmod -R 775 '.FINORA_BASE_PATH.'/storage');
            $results[] = runCommand('chmod -R 775 '.FINORA_BASE_PATH.'/bootstrap/cache');
            break;

            // === CREATE STORAGE DIRS ===
        case 'create_storage':
            $dirs = [
                'storage/app/public',
                'storage/framework/cache/data',
                'storage/framework/sessions',
                'storage/framework/views',
                'storage/logs',
                'bootstrap/cache',
            ];
            foreach ($dirs as $dir) {
                $path = FINORA_BASE_PATH.'/'.$dir;
                if (! is_dir($path)) {
                    mkdir($path, 0775, true);
                    $results[] = ['success' => true, 'output' => "Created: {$dir}", 'code' => 0, 'command' => "mkdir {$dir}"];
                } else {
                    $results[] = ['success' => true, 'output' => "Exists: {$dir}", 'code' => 0, 'command' => "check {$dir}"];
                }
            }
            break;

            // === CUSTOM COMMAND ===
        case 'custom':
            $customCmd = $_POST['custom_command'] ?? '';
            if ($customCmd) {
                // Block dangerous commands
                $blocked = ['rm ', 'rm -rf', 'DROP ', 'DELETE ', 'TRUNCATE ', ':wipe', ':fresh', ':reset', ':rollback'];
                $isBlocked = false;
                foreach ($blocked as $b) {
                    if (stripos($customCmd, $b) !== false) {
                        $isBlocked = true;
                        break;
                    }
                }
                if ($isBlocked) {
                    $results[] = ['success' => false, 'output' => 'Command blocked for security reasons.', 'code' => 1, 'command' => $customCmd];
                } else {
                    $results[] = runArtisan($customCmd);
                }
            }
            break;
    }
}

$envChecks = checkEnvironment();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finora Bank - Server Setup</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            color: #e2e8f0;
            padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 10px;
            background: linear-gradient(90deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 30px;
        }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
        .card {
            background: rgba(30, 41, 59, 0.8);
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.1);
            overflow: hidden;
        }
        .card-header {
            background: rgba(51, 65, 85, 0.5);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-body { padding: 20px; }
        .btn-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: #fff;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .btn-success { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .btn-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .btn-info { background: linear-gradient(135deg, #06b6d4, #0891b2); }
        .btn-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .btn-full { grid-column: span 2; }
        .result {
            background: rgba(15, 23, 42, 0.8);
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        .result.success { border-left: 4px solid #22c55e; }
        .result.error { border-left: 4px solid #ef4444; }
        .result-header {
            padding: 10px 15px;
            background: rgba(30, 41, 59, 0.5);
            font-size: 14px;
        }
        .result-header code {
            background: rgba(51, 65, 85, 0.5);
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Monaco', 'Consolas', monospace;
        }
        .result-output {
            padding: 15px;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 12px;
            line-height: 1.5;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 300px;
            overflow-y: auto;
            color: #94a3b8;
        }
        .env-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .env-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 6px;
            font-size: 13px;
        }
        .env-item .label { color: #94a3b8; }
        .env-item .value { font-weight: 500; }
        .env-item .value.ok { color: #22c55e; }
        .env-item .value.error { color: #ef4444; }
        .custom-form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .custom-form input {
            flex: 1;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(15, 23, 42, 0.8);
            color: #e2e8f0;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 14px;
        }
        .custom-form input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        .warning-box {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .warning-box h4 { color: #f59e0b; margin-bottom: 5px; }
        .warning-box p { color: #fbbf24; font-size: 14px; }
        .quick-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 30px;
        }
        .quick-actions .btn { padding: 15px 30px; font-size: 16px; }
        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
            .btn-grid { grid-template-columns: 1fr; }
            .btn-full { grid-column: span 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏦 Finora Bank - Server Setup</h1>
        <p class="subtitle">Run deployment commands without terminal access</p>
        
        <?php if (SECRET_KEY === 'finora_setup_2026_change_me') { ?>
        <div class="warning-box">
            <h4>⚠️ Security Warning</h4>
            <p>You're using the default SECRET_KEY. Please edit setup.php and change it before using in production!</p>
        </div>
        <?php } ?>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="?key=<?= SECRET_KEY ?>&action=full_setup" class="btn btn-success" onclick="return confirm('Run full setup? This will install dependencies, run migrations, and cache everything.')">
                🚀 Full Setup (First Deploy)
            </a>
            <a href="?key=<?= SECRET_KEY ?>&action=quick_deploy" class="btn btn-primary" onclick="return confirm('Run quick deploy? This will clear caches, migrate, and optimize.')">
                ⚡ Quick Deploy (Updates)
            </a>
            <a href="?key=<?= SECRET_KEY ?>&action=full_optimize" class="btn btn-info" onclick="return confirm('Run full optimization? This maximizes performance for production.')">
                🔥 Full Optimize (Speed Boost)
            </a>
            <a href="?key=<?= SECRET_KEY ?>&action=clear_all" class="btn btn-warning">
                🧹 Clear All Caches
            </a>
        </div>
        
        <!-- Results -->
        <?php if (! empty($results)) { ?>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">📋 Command Results</div>
            <div class="card-body">
                <?php foreach ($results as $result) { ?>
                    <?= formatOutput($result) ?>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        
        <div class="grid">
            <!-- Environment Check -->
            <div class="card">
                <div class="card-header">🔍 Environment Check</div>
                <div class="card-body">
                    <div class="env-grid">
                        <?php foreach ($envChecks as $name => $check) { ?>
                        <div class="env-item">
                            <span class="label"><?= $name ?></span>
                            <span class="value <?= $check['ok'] ? 'ok' : 'error' ?>"><?= $check['value'] ?></span>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            
            <!-- Composer Commands -->
            <div class="card">
                <div class="card-header">📦 Composer</div>
                <div class="card-body">
                    <div class="btn-grid">
                        <a href="?key=<?= SECRET_KEY ?>&action=composer_install" class="btn btn-primary">Install (Production)</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=composer_install_dev" class="btn btn-info">Install (Dev)</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=composer_update" class="btn btn-warning">Update</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=composer_dump" class="btn btn-purple">Dump Autoload</a>
                    </div>
                </div>
            </div>
            
            <!-- NPM Commands -->
            <div class="card">
                <div class="card-header">📦 NPM / Assets</div>
                <div class="card-body">
                    <div class="btn-grid">
                        <a href="?key=<?= SECRET_KEY ?>&action=npm_install" class="btn btn-primary">NPM Install</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=npm_build" class="btn btn-success">NPM Build</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=npm_install_build" class="btn btn-purple btn-full">Install + Build</a>
                    </div>
                </div>
            </div>
            
            <!-- Cache Commands -->
            <div class="card">
                <div class="card-header">🗄️ Cache Management</div>
                <div class="card-body">
                    <div class="btn-grid">
                        <a href="?key=<?= SECRET_KEY ?>&action=cache_clear" class="btn btn-warning">Cache Clear</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=config_clear" class="btn btn-warning">Config Clear</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=config_cache" class="btn btn-success">Config Cache</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=route_clear" class="btn btn-warning">Route Clear</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=route_cache" class="btn btn-success">Route Cache</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=view_clear" class="btn btn-warning">View Clear</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=view_cache" class="btn btn-success">View Cache</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=event_clear" class="btn btn-warning">Event Clear</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=clear_all" class="btn btn-danger btn-full">Clear All Caches</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=cache_all" class="btn btn-success btn-full">Cache Everything</a>
                    </div>
                </div>
            </div>
            
            <!-- Optimization -->
            <div class="card">
                <div class="card-header">⚡ Optimization</div>
                <div class="card-body">
                    <div class="btn-grid">
                        <a href="?key=<?= SECRET_KEY ?>&action=optimize" class="btn btn-success">Laravel Optimize</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=optimize_clear" class="btn btn-warning">Optimize Clear</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=filament_optimize" class="btn btn-primary">Filament Optimize</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=filament_cache" class="btn btn-purple">Filament Cache</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=filament_clear" class="btn btn-info">Filament Clear</a>
                    </div>
                </div>
            </div>
            
            <!-- Database -->
            <div class="card">
                <div class="card-header">🗃️ Database</div>
                <div class="card-body">
                    <div class="btn-grid">
                        <a href="?key=<?= SECRET_KEY ?>&action=migrate" class="btn btn-primary" onclick="return confirm('Run migrations?')">Run Migrations</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=migrate_status" class="btn btn-info">Migration Status</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=db_seed" class="btn btn-warning" onclick="return confirm('Run database seeders?')">Run Seeders</a>
                    </div>
                </div>
            </div>
            
            <!-- Storage & Permissions -->
            <div class="card">
                <div class="card-header">📁 Storage & Permissions</div>
                <div class="card-body">
                    <div class="btn-grid">
                        <a href="?key=<?= SECRET_KEY ?>&action=storage_link" class="btn btn-primary">Storage Link</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=create_storage" class="btn btn-info">Create Storage Dirs</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=fix_permissions" class="btn btn-warning btn-full">Fix Permissions</a>
                    </div>
                </div>
            </div>
            
            <!-- Maintenance -->
            <div class="card">
                <div class="card-header">🔧 Maintenance</div>
                <div class="card-body">
                    <div class="btn-grid">
                        <a href="?key=<?= SECRET_KEY ?>&action=down" class="btn btn-danger" onclick="return confirm('Enable maintenance mode?')">Maintenance ON</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=up" class="btn btn-success">Maintenance OFF</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=key_generate" class="btn btn-purple" onclick="return confirm('Generate new APP_KEY? This may invalidate existing sessions!')">Generate Key</a>
                    </div>
                </div>
            </div>
            
            <!-- Queue -->
            <div class="card">
                <div class="card-header">📬 Queue</div>
                <div class="card-body">
                    <div class="btn-grid">
                        <a href="?key=<?= SECRET_KEY ?>&action=queue_work" class="btn btn-primary">Process Queue</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=queue_failed" class="btn btn-info">Failed Jobs</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=queue_retry" class="btn btn-warning btn-full">Retry All Failed</a>
                    </div>
                </div>
            </div>
            
            <!-- Information -->
            <div class="card">
                <div class="card-header">ℹ️ Information</div>
                <div class="card-body">
                    <div class="btn-grid">
                        <a href="?key=<?= SECRET_KEY ?>&action=about" class="btn btn-info">About</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=env" class="btn btn-info">Environment</a>
                        <a href="?key=<?= SECRET_KEY ?>&action=route_list" class="btn btn-purple btn-full">Route List</a>
                    </div>
                </div>
            </div>
            
            <!-- Custom Command -->
            <div class="card">
                <div class="card-header">⌨️ Custom Artisan Command</div>
                <div class="card-body">
                    <p style="color: #94a3b8; font-size: 14px; margin-bottom: 10px;">
                        Run any artisan command (without "php artisan" prefix)
                    </p>
                    <form method="POST" action="?key=<?= SECRET_KEY ?>&action=custom" class="custom-form">
                        <input type="text" name="custom_command" placeholder="migrate:status" required>
                        <button type="submit" class="btn btn-primary">Run</button>
                    </form>
                </div>
            </div>
        </div>
        
        <p style="text-align: center; margin-top: 30px; color: #64748b; font-size: 14px;">
            Finora Bank Server Setup v1.0 | 
            <a href="?key=<?= SECRET_KEY ?>" style="color: #3b82f6;">Refresh</a> |
            🔒 Secured with secret key
        </p>
    </div>
</body>
</html>
