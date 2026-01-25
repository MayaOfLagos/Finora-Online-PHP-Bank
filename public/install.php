<?php
/**
 * Finora Bank - Bootstrap Installer
 * 
 * This script downloads Composer and installs dependencies
 * for shared hosting without terminal access.
 * 
 * Access: yoursite.com/install.php?key=finora_install_2026
 */

// ============================================
// SECURITY - CHANGE THIS KEY!
// ============================================
define('INSTALL_KEY', 'finora_install_2026');

// ============================================
// CONFIGURATION
// ============================================
define('BASE_PATH', dirname(__FILE__));
define('ROOT_PATH', dirname(BASE_PATH)); // Go up from public to root

set_time_limit(600);
ini_set('memory_limit', '512M');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security check
$key = $_GET['key'] ?? '';
if ($key !== INSTALL_KEY) {
    die('<!DOCTYPE html><html><head><title>Access Denied</title></head><body style="font-family:system-ui;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;background:#1a1a2e;"><div style="text-align:center;color:#fff;"><h1 style="font-size:72px;margin:0;">403</h1><p style="color:#888;">Invalid Key</p></div></body></html>');
}

$action = $_GET['action'] ?? 'status';
$messages = [];

// Helper function
function addMessage($type, $text) {
    global $messages;
    $messages[] = ['type' => $type, 'text' => $text];
}

function runCmd($cmd) {
    $output = [];
    $code = 0;
    exec($cmd . ' 2>&1', $output, $code);
    return ['output' => implode("\n", $output), 'code' => $code];
}

// Check what we're working with
function getStatus() {
    $status = [];
    
    // Check paths
    $status['base_path'] = BASE_PATH;
    $status['root_path'] = ROOT_PATH;
    $status['is_laravel_root'] = file_exists(ROOT_PATH . '/artisan');
    $status['public_is_root'] = file_exists(BASE_PATH . '/artisan');
    
    // Determine actual Laravel root
    if ($status['is_laravel_root']) {
        $status['laravel_root'] = ROOT_PATH;
    } elseif ($status['public_is_root']) {
        $status['laravel_root'] = BASE_PATH;
    } else {
        $status['laravel_root'] = null;
    }
    
    $root = $status['laravel_root'] ?? ROOT_PATH;
    
    // Check files
    $status['has_composer_json'] = file_exists($root . '/composer.json');
    $status['has_vendor'] = is_dir($root . '/vendor');
    $status['has_autoload'] = file_exists($root . '/vendor/autoload.php');
    $status['has_env'] = file_exists($root . '/.env');
    $status['has_env_example'] = file_exists($root . '/.env.example');
    $status['has_artisan'] = file_exists($root . '/artisan');
    $status['has_composer_phar'] = file_exists($root . '/composer.phar');
    
    // Check PHP
    $status['php_version'] = PHP_VERSION;
    $status['php_ok'] = version_compare(PHP_VERSION, '8.2.0', '>=');
    
    // Check if composer is available globally
    $composerCheck = runCmd('which composer');
    $status['composer_global'] = strpos($composerCheck['output'], 'composer') !== false && $composerCheck['code'] === 0;
    
    // Check PHP binary paths
    $phpPaths = ['/usr/local/bin/php', '/usr/bin/php', 'php'];
    foreach ($phpPaths as $path) {
        $check = runCmd("$path -v");
        if ($check['code'] === 0) {
            $status['php_binary'] = $path;
            break;
        }
    }
    
    return $status;
}

$status = getStatus();
$laravelRoot = $status['laravel_root'] ?? ROOT_PATH;
$phpBin = $status['php_binary'] ?? 'php';

// Handle actions
if ($action === 'download_composer') {
    addMessage('info', 'Downloading Composer...');
    
    $composerUrl = 'https://getcomposer.org/composer.phar';
    $composerPath = $laravelRoot . '/composer.phar';
    
    $ch = curl_init($composerUrl);
    $fp = fopen($composerPath, 'w');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $success = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    fclose($fp);
    
    if ($success && file_exists($composerPath) && filesize($composerPath) > 1000) {
        chmod($composerPath, 0755);
        addMessage('success', 'Composer downloaded successfully!');
    } else {
        addMessage('error', 'Failed to download Composer: ' . $error);
    }
    
    $status = getStatus();
}

if ($action === 'composer_install') {
    $composerCmd = '';
    
    if ($status['composer_global']) {
        $composerCmd = 'composer';
    } elseif ($status['has_composer_phar']) {
        $composerCmd = $phpBin . ' ' . $laravelRoot . '/composer.phar';
    } else {
        addMessage('error', 'Composer not found. Please download it first.');
    }
    
    if ($composerCmd) {
        addMessage('info', 'Running composer install (this may take a few minutes)...');
        
        $cmd = "cd " . escapeshellarg($laravelRoot) . " && $composerCmd install --no-dev --optimize-autoloader --no-interaction 2>&1";
        $result = runCmd($cmd);
        
        if ($result['code'] === 0) {
            addMessage('success', 'Composer install completed!');
        } else {
            addMessage('warning', 'Composer finished with code: ' . $result['code']);
        }
        addMessage('output', $result['output']);
    }
    
    $status = getStatus();
}

if ($action === 'create_env') {
    if (!$status['has_env'] && $status['has_env_example']) {
        if (copy($laravelRoot . '/.env.example', $laravelRoot . '/.env')) {
            addMessage('success', '.env file created from .env.example');
        } else {
            addMessage('error', 'Failed to create .env file');
        }
    } elseif ($status['has_env']) {
        addMessage('info', '.env file already exists');
    } else {
        addMessage('error', '.env.example not found');
    }
    $status = getStatus();
}

if ($action === 'generate_key') {
    if ($status['has_autoload']) {
        $cmd = "cd " . escapeshellarg($laravelRoot) . " && $phpBin artisan key:generate --force --no-interaction 2>&1";
        $result = runCmd($cmd);
        addMessage($result['code'] === 0 ? 'success' : 'error', 'Key generate: ' . $result['output']);
    } else {
        addMessage('error', 'Vendor not installed. Run composer install first.');
    }
}

if ($action === 'create_storage') {
    $dirs = [
        '/storage/app/public',
        '/storage/framework/cache/data',
        '/storage/framework/sessions',
        '/storage/framework/views',
        '/storage/logs',
        '/bootstrap/cache'
    ];
    
    foreach ($dirs as $dir) {
        $path = $laravelRoot . $dir;
        if (!is_dir($path)) {
            if (mkdir($path, 0775, true)) {
                addMessage('success', "Created: $dir");
            } else {
                addMessage('error', "Failed to create: $dir");
            }
        } else {
            addMessage('info', "Exists: $dir");
        }
    }
}

if ($action === 'fix_permissions') {
    $dirs = ['/storage', '/bootstrap/cache'];
    foreach ($dirs as $dir) {
        $path = $laravelRoot . $dir;
        if (is_dir($path)) {
            $cmd = "chmod -R 775 " . escapeshellarg($path) . " 2>&1";
            $result = runCmd($cmd);
            addMessage($result['code'] === 0 ? 'success' : 'warning', "Permissions for $dir: " . ($result['output'] ?: 'OK'));
        }
    }
}

if ($action === 'storage_link') {
    if ($status['has_autoload']) {
        $cmd = "cd " . escapeshellarg($laravelRoot) . " && $phpBin artisan storage:link --no-interaction 2>&1";
        $result = runCmd($cmd);
        addMessage($result['code'] === 0 ? 'success' : 'error', 'Storage link: ' . $result['output']);
    } else {
        addMessage('error', 'Vendor not installed.');
    }
}

if ($action === 'migrate') {
    if ($status['has_autoload']) {
        $cmd = "cd " . escapeshellarg($laravelRoot) . " && $phpBin artisan migrate --force --no-interaction 2>&1";
        $result = runCmd($cmd);
        addMessage($result['code'] === 0 ? 'success' : 'error', $result['output']);
    } else {
        addMessage('error', 'Vendor not installed.');
    }
}

if ($action === 'seed') {
    if ($status['has_autoload']) {
        $cmd = "cd " . escapeshellarg($laravelRoot) . " && $phpBin artisan db:seed --force --no-interaction 2>&1";
        $result = runCmd($cmd);
        addMessage($result['code'] === 0 ? 'success' : 'error', $result['output']);
    } else {
        addMessage('error', 'Vendor not installed.');
    }
}

if ($action === 'optimize') {
    if ($status['has_autoload']) {
        $cmds = [
            'config:cache',
            'route:cache', 
            'view:cache',
            'event:cache'
        ];
        foreach ($cmds as $artisanCmd) {
            $cmd = "cd " . escapeshellarg($laravelRoot) . " && $phpBin artisan $artisanCmd --no-interaction 2>&1";
            $result = runCmd($cmd);
            addMessage($result['code'] === 0 ? 'success' : 'warning', "$artisanCmd: " . ($result['output'] ?: 'OK'));
        }
    } else {
        addMessage('error', 'Vendor not installed.');
    }
}

if ($action === 'full_install') {
    // Step 1: Download Composer if needed
    if (!$status['composer_global'] && !$status['has_composer_phar']) {
        addMessage('info', 'Step 1: Downloading Composer...');
        $composerUrl = 'https://getcomposer.org/composer.phar';
        $composerPath = $laravelRoot . '/composer.phar';
        $ch = curl_init($composerUrl);
        $fp = fopen($composerPath, 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        chmod($composerPath, 0755);
        addMessage('success', 'Composer downloaded');
        $status = getStatus();
    }
    
    // Step 2: Create storage directories
    addMessage('info', 'Step 2: Creating storage directories...');
    $dirs = ['/storage/app/public', '/storage/framework/cache/data', '/storage/framework/sessions', '/storage/framework/views', '/storage/logs', '/bootstrap/cache'];
    foreach ($dirs as $dir) {
        $path = $laravelRoot . $dir;
        if (!is_dir($path)) mkdir($path, 0775, true);
    }
    addMessage('success', 'Storage directories created');
    
    // Step 3: Create .env
    if (!file_exists($laravelRoot . '/.env') && file_exists($laravelRoot . '/.env.example')) {
        addMessage('info', 'Step 3: Creating .env file...');
        copy($laravelRoot . '/.env.example', $laravelRoot . '/.env');
        addMessage('success', '.env created');
    }
    
    // Step 4: Composer install
    addMessage('info', 'Step 4: Running composer install (this may take several minutes)...');
    $composerCmd = $status['composer_global'] ? 'composer' : $phpBin . ' ' . $laravelRoot . '/composer.phar';
    $cmd = "cd " . escapeshellarg($laravelRoot) . " && $composerCmd install --no-dev --optimize-autoloader --no-interaction 2>&1";
    $result = runCmd($cmd);
    if ($result['code'] === 0) {
        addMessage('success', 'Composer install completed');
    } else {
        addMessage('error', 'Composer install failed: ' . $result['output']);
    }
    
    // Refresh status
    $status = getStatus();
    
    if ($status['has_autoload']) {
        // Step 5: Generate key
        addMessage('info', 'Step 5: Generating app key...');
        $cmd = "cd " . escapeshellarg($laravelRoot) . " && $phpBin artisan key:generate --force --no-interaction 2>&1";
        $result = runCmd($cmd);
        addMessage($result['code'] === 0 ? 'success' : 'warning', 'Key: ' . $result['output']);
        
        // Step 6: Storage link
        addMessage('info', 'Step 6: Creating storage link...');
        $cmd = "cd " . escapeshellarg($laravelRoot) . " && $phpBin artisan storage:link --no-interaction 2>&1";
        runCmd($cmd);
        addMessage('success', 'Storage linked');
        
        // Step 7: Optimize
        addMessage('info', 'Step 7: Optimizing...');
        $cmd = "cd " . escapeshellarg($laravelRoot) . " && $phpBin artisan optimize --no-interaction 2>&1";
        runCmd($cmd);
        addMessage('success', 'Optimized');
        
        addMessage('success', '🎉 Installation complete! Configure your .env file with database credentials, then run migrations.');
    }
    
    $status = getStatus();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finora Bank - Installer</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            color: #e2e8f0;
            padding: 20px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        h1 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 10px;
            background: linear-gradient(90deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .subtitle { text-align: center; color: #64748b; margin-bottom: 30px; }
        .card {
            background: rgba(30, 41, 59, 0.8);
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header {
            background: rgba(51, 65, 85, 0.5);
            padding: 15px 20px;
            font-weight: 600;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        }
        .card-body { padding: 20px; }
        .status-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .status-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 8px;
        }
        .status-item .label { color: #94a3b8; }
        .status-item .value { font-weight: 600; }
        .status-item .value.ok { color: #22c55e; }
        .status-item .value.error { color: #ef4444; }
        .status-item .value.warning { color: #f59e0b; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            color: #fff;
            margin: 5px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .btn-success { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .btn-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .btn-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .btn-lg { padding: 16px 32px; font-size: 18px; }
        .btn-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .message.success { background: rgba(34, 197, 94, 0.2); border-left: 4px solid #22c55e; }
        .message.error { background: rgba(239, 68, 68, 0.2); border-left: 4px solid #ef4444; }
        .message.warning { background: rgba(245, 158, 11, 0.2); border-left: 4px solid #f59e0b; }
        .message.info { background: rgba(59, 130, 246, 0.2); border-left: 4px solid #3b82f6; }
        .message.output {
            background: rgba(15, 23, 42, 0.8);
            border-left: 4px solid #64748b;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        .steps { counter-reset: step; }
        .step {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .step-num {
            counter-increment: step;
            width: 32px;
            height: 32px;
            background: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
        }
        .step-num::before { content: counter(step); }
        .step.done .step-num { background: #22c55e; }
        .step.done .step-num::before { content: '✓'; }
        .step-content { flex: 1; }
        .step-title { font-weight: 600; margin-bottom: 5px; }
        .step-desc { color: #94a3b8; font-size: 14px; }
        .center { text-align: center; }
        .mt-4 { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏦 Finora Bank Installer</h1>
        <p class="subtitle">Install dependencies and set up your application</p>
        
        <?php if (!empty($messages)): ?>
        <div class="card">
            <div class="card-header">📋 Results</div>
            <div class="card-body">
                <?php foreach ($messages as $msg): ?>
                <div class="message <?= $msg['type'] ?>"><?= htmlspecialchars($msg['text']) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Quick Install Button -->
        <?php if (!$status['has_autoload']): ?>
        <div class="card">
            <div class="card-body center">
                <p style="margin-bottom: 20px; font-size: 18px;">Ready to install Finora Bank?</p>
                <a href="?key=<?= INSTALL_KEY ?>&action=full_install" class="btn btn-success btn-lg" onclick="this.innerHTML='⏳ Installing... Please wait...'; this.style.pointerEvents='none';">
                    🚀 Run Full Installation
                </a>
                <p style="margin-top: 15px; color: #64748b; font-size: 14px;">
                    This will download Composer, install dependencies, and set up the application.<br>
                    It may take 2-5 minutes depending on your server.
                </p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Status -->
        <div class="card">
            <div class="card-header">📊 System Status</div>
            <div class="card-body">
                <div class="status-grid">
                    <div class="status-item">
                        <span class="label">PHP Version</span>
                        <span class="value <?= $status['php_ok'] ? 'ok' : 'error' ?>"><?= $status['php_version'] ?></span>
                    </div>
                    <div class="status-item">
                        <span class="label">Laravel Root</span>
                        <span class="value <?= $status['laravel_root'] ? 'ok' : 'error' ?>"><?= $status['laravel_root'] ? 'Found' : 'Not Found' ?></span>
                    </div>
                    <div class="status-item">
                        <span class="label">composer.json</span>
                        <span class="value <?= $status['has_composer_json'] ? 'ok' : 'error' ?>"><?= $status['has_composer_json'] ? '✓' : '✗' ?></span>
                    </div>
                    <div class="status-item">
                        <span class="label">Composer</span>
                        <span class="value <?= ($status['composer_global'] || $status['has_composer_phar']) ? 'ok' : 'warning' ?>">
                            <?= $status['composer_global'] ? 'Global' : ($status['has_composer_phar'] ? 'Local' : 'Not Found') ?>
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="label">Vendor Directory</span>
                        <span class="value <?= $status['has_vendor'] ? 'ok' : 'error' ?>"><?= $status['has_vendor'] ? '✓ Installed' : '✗ Missing' ?></span>
                    </div>
                    <div class="status-item">
                        <span class="label">.env File</span>
                        <span class="value <?= $status['has_env'] ? 'ok' : 'warning' ?>"><?= $status['has_env'] ? '✓' : '✗ Missing' ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Installation Steps -->
        <div class="card">
            <div class="card-header">📝 Installation Steps</div>
            <div class="card-body">
                <div class="steps">
                    <div class="step <?= ($status['composer_global'] || $status['has_composer_phar']) ? 'done' : '' ?>">
                        <div class="step-num"></div>
                        <div class="step-content">
                            <div class="step-title">Download Composer</div>
                            <div class="step-desc">Get the Composer package manager</div>
                            <?php if (!$status['composer_global'] && !$status['has_composer_phar']): ?>
                            <a href="?key=<?= INSTALL_KEY ?>&action=download_composer" class="btn btn-primary" style="margin-top:10px">Download Composer</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="step <?= $status['has_autoload'] ? 'done' : '' ?>">
                        <div class="step-num"></div>
                        <div class="step-content">
                            <div class="step-title">Install Dependencies</div>
                            <div class="step-desc">Run composer install to get all packages</div>
                            <?php if (($status['composer_global'] || $status['has_composer_phar']) && !$status['has_autoload']): ?>
                            <a href="?key=<?= INSTALL_KEY ?>&action=composer_install" class="btn btn-primary" style="margin-top:10px" onclick="this.innerHTML='⏳ Installing...'; this.style.pointerEvents='none';">Run Composer Install</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="step <?= $status['has_env'] ? 'done' : '' ?>">
                        <div class="step-num"></div>
                        <div class="step-content">
                            <div class="step-title">Create .env File</div>
                            <div class="step-desc">Copy .env.example to .env and configure database</div>
                            <?php if (!$status['has_env']): ?>
                            <a href="?key=<?= INSTALL_KEY ?>&action=create_env" class="btn btn-warning" style="margin-top:10px">Create .env</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-num"></div>
                        <div class="step-content">
                            <div class="step-title">Configure Database</div>
                            <div class="step-desc">Edit .env file in File Manager with your database credentials</div>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-num"></div>
                        <div class="step-content">
                            <div class="step-title">Run Migrations</div>
                            <div class="step-desc">Create database tables</div>
                            <?php if ($status['has_autoload']): ?>
                            <a href="?key=<?= INSTALL_KEY ?>&action=migrate" class="btn btn-purple" style="margin-top:10px">Run Migrations</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-num"></div>
                        <div class="step-content">
                            <div class="step-title">Seed Database</div>
                            <div class="step-desc">Add initial data (admin user, settings, etc.)</div>
                            <?php if ($status['has_autoload']): ?>
                            <a href="?key=<?= INSTALL_KEY ?>&action=seed" class="btn btn-purple" style="margin-top:10px">Run Seeders</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Manual Actions -->
        <?php if ($status['has_autoload']): ?>
        <div class="card">
            <div class="card-header">🔧 Additional Actions</div>
            <div class="card-body">
                <div class="btn-grid">
                    <a href="?key=<?= INSTALL_KEY ?>&action=generate_key" class="btn btn-warning">Generate App Key</a>
                    <a href="?key=<?= INSTALL_KEY ?>&action=storage_link" class="btn btn-primary">Storage Link</a>
                    <a href="?key=<?= INSTALL_KEY ?>&action=create_storage" class="btn btn-primary">Create Storage Dirs</a>
                    <a href="?key=<?= INSTALL_KEY ?>&action=fix_permissions" class="btn btn-warning">Fix Permissions</a>
                    <a href="?key=<?= INSTALL_KEY ?>&action=optimize" class="btn btn-success">Optimize/Cache</a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body center">
                <p style="color: #22c55e; font-size: 18px; margin-bottom: 15px;">✅ Dependencies installed!</p>
                <a href="setup.php?key=finora_setup_2026_change_me" class="btn btn-success btn-lg">Go to Setup Panel →</a>
                <p style="margin-top: 15px; color: #64748b; font-size: 14px;">
                    Use the Setup Panel for ongoing maintenance and artisan commands.
                </p>
            </div>
        </div>
        <?php endif; ?>
        
        <p style="text-align: center; margin-top: 30px; color: #64748b; font-size: 14px;">
            Finora Bank Installer v1.0 | 
            <a href="?key=<?= INSTALL_KEY ?>" style="color: #3b82f6;">Refresh Status</a>
        </p>
    </div>
</body>
</html>
