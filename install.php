<?php
/**
 * Synapse Browser Launcher
 * Access this page to launch Synapse Browser
 * URL: https://im24wil27051.imporsgrunn.no/Synapse_browser/install.php
 */

$browserUrl = 'https://synapse-browser.onrender.com';
$searxUrl = 'https://synapse-searx.onrender.com';

// Check if services are accessible
$browserStatus = @file_get_contents($browserUrl, false, stream_context_create([
    'http' => ['timeout' => 5, 'ignore_errors' => true]
]));
$browserOk = $browserStatus !== false && strlen($browserStatus) > 0;

$searxTest = @file_get_contents($searxUrl . '/search?q=test&format=json', false, stream_context_create([
    'http' => ['timeout' => 5, 'ignore_errors' => true]
]));
$searxOk = $searxTest !== false && strlen($searxTest) > 0;

// Handle auto-launch
if (isset($_GET['launch'])) {
    header('Location: ' . $browserUrl);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Synapse Browser</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .container {
            text-align: center;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            max-width: 500px;
        }
        h1 { font-size: 3em; margin-bottom: 10px; }
        .subtitle { font-size: 1.2em; opacity: 0.8; margin-bottom: 30px; }
        
        .status-box {
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .status-item:last-child { border-bottom: none; }
        .status-label { opacity: 0.7; }
        .status-value { font-weight: bold; }
        .ok { color: #4ade80; }
        .error { color: #f87171; }
        
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: bold;
            margin: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }
        .btn:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .info {
            margin-top: 20px;
            opacity: 0.6;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Synapse</h1>
        <p class="subtitle">Private Search Browser</p>
        
        <div class="status-box">
            <div class="status-item">
                <span class="status-label">🌐 Browser</span>
                <span class="status-value <?php echo $browserOk ? 'ok' : 'error'; ?>">
                    <?php echo $browserOk ? 'Ready' : 'Offline'; ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">⚡ Search Backend</span>
                <span class="status-value <?php echo $searxOk ? 'ok' : 'error'; ?>">
                    <?php echo $searxOk ? 'Ready' : 'Offline'; ?>
                </span>
            </div>
        </div>
        
        <?php if ($browserOk): ?>
            <a href="?launch=1" class="btn">🚀 Open Browser</a>
        <?php else: ?>
            <button class="btn" disabled>⚠️ Services Offline</button>
        <?php endif; ?>
        
        <p class="info">
            Backend: <?php echo htmlspecialchars($searxUrl); ?>
        </p>
    </div>
</body>
</html>
