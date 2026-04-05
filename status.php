<?php
header('Content-Type: text/plain');

$searxUrl = 'https://synapse-searx.onrender.com';
$testQuery = $_GET['test'] ?? 'test';

echo "🔍 Synapse Browser Status\n";
echo "=========================\n\n";

echo "Backend: $searxUrl\n";
echo "Testing search query: $testQuery\n\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $searxUrl . "/search?q=" . urlencode($testQuery) . "&format=json",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => ['User-Agent: SynapseBrowser/1.0']
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $response) {
    $data = json_decode($response, true);
    $results = $data['results'] ?? [];
    echo "✅ Backend is working!\n";
    echo "📊 Found " . count($results) . " results\n\n";
    
    if (!empty($results)) {
        echo "Top results:\n";
        foreach (array_slice($results, 0, 5) as $i => $r) {
            $i++;
            echo "  $i. " . substr($r['title'], 0, 60) . "\n";
        }
    }
} else {
    echo "❌ Backend error (HTTP $httpCode)\n";
    echo "Check SEARX_URL configuration\n";
}

echo "\n📍 Browser: https://im24wil27051.imporsgrunn.no/Synapse_browser/\n";
?>
