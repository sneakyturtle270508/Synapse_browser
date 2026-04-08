<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    http_response_code(400);
    echo json_encode(['error' => 'Query parameter "q" is required']);
    exit;
}

$searxUrl = getenv('SEARX_URL') ?: 'http://searx:8080';

// Check if this is a person search
$isPerson = isPersonSearch($query);
$githubProfile = null;
$wikipediaInfo = null;
$results = [];

// Only search for GitHub profile if query looks like a person's name or username
if (isLikelyPersonName($query) || isLikelyUsername($query)) {
    $githubProfile = findGitHubProfile($query);
}

// Always get search results
$results = searchSearx($searxUrl, $query);

// Extract Wikipedia info from results
foreach ($results as $r) {
    if (!$wikipediaInfo && (strpos($r['url'], 'wikipedia.org') !== false || strpos($r['url'], 'wikidata.org') !== false)) {
        $wikipediaInfo = [
            'name' => $r['title'],
            'description' => $r['snippet'],
            'url' => $r['url'],
            'image' => 'https://en.wikipedia.org/static/images/project-logos/enwiki.png'
        ];
    }
}

echo json_encode([
    'query' => $query,
    'results' => $results,
    'total' => count($results),
    'githubProfile' => $githubProfile,
    'wikipediaInfo' => $wikipediaInfo,
    'isPersonSearch' => $isPerson
]);

function isLikelyPersonName($query) {
    $words = explode(' ', trim($query));
    if (count($words) < 2 || count($words) > 4) return false;
    
    foreach ($words as $word) {
        if (!ctype_alpha($word) || !ctype_upper(substr($word, 0, 1))) {
            return false;
        }
    }
    return true;
}

function isLikelyUsername($query) {
    $query = trim($query);
    if (strpos($query, ' ') !== false) return false;
    if (strlen($query) < 3 || strlen($query) > 39) return false;
    return preg_match('/^[a-zA-Z0-9_-]+$/', $query) === 1;
}

function isPersonSearch($query) {
    $personIndicators = [
        'actor', 'actress', 'singer', 'musician', 'artist', 'painter',
        'president', 'prime minister', 'ceo', 'founder', 'entrepreneur', 'billionaire',
        'footballer', 'basketball', 'tennis', 'player', 'coach', 'champion',
        'scientist', 'physicist', 'chemist', 'biologist', 'doctor', 'professor',
        'author', 'writer', 'poet', 'novelist', 'journalist',
        'politician', 'governor', 'mayor', 'senator', 'representative',
        'comedian', 'director', 'producer', 'filmmaker',
        'inventor', 'engineer', 'architect', 'designer',
        'rapper', 'dj', 'celebrity', 'developer', 'programmer'
    ];
    
    $queryLower = strtolower($query);
    foreach ($personIndicators as $indicator) {
        if (strpos($queryLower, $indicator) !== false) {
            return true;
        }
    }
    
    // Check for name-like patterns
    $words = explode(' ', $query);
    if (count($words) >= 2 && count($words) <= 4) {
        $allCapitalized = true;
        foreach ($words as $word) {
            if (!ctype_alpha($word) || !ctype_upper(substr($word, 0, 1))) {
                $allCapitalized = false;
                break;
            }
        }
        if ($allCapitalized) return true;
    }
    
    return false;
}

function findGitHubProfile($query) {
    // Extract potential username from query
    $words = explode(' ', $query);
    
    // Generate possible usernames
    $usernames = generateUsernames($words);
    $usernames = array_unique($usernames);
    
    foreach ($usernames as $username) {
        $profile = fetchGitHubProfile($username);
        if ($profile) {
            return $profile;
        }
    }
    
    return null;
}

function generateUsernames($words) {
    $usernames = [];
    $cleanWords = [];
    
    foreach ($words as $word) {
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', $word);
        if (strlen($clean) > 0) {
            $cleanWords[] = $clean;
        }
    }
    
    if (empty($cleanWords)) return $usernames;
    
    // john doe -> johndoe, john-doe, john_doe, johnd
    $firstName = $cleanWords[0];
    $lastName = end($cleanWords);
    
    $usernames[] = strtolower($firstName) . strtolower($lastName);
    $usernames[] = strtolower($firstName) . '-' . strtolower($lastName);
    $usernames[] = strtolower($firstName) . '_' . strtolower($lastName);
    $usernames[] = strtolower(substr($firstName, 0, 1)) . strtolower($lastName);
    $usernames[] = strtolower($firstName) . strtolower(substr($lastName, 0, 1));
    $usernames[] = strtolower($firstName);
    $usernames[] = strtolower($lastName);
    $usernames[] = strtolower(implode('', $cleanWords));
    
    return $usernames;
}

function fetchGitHubProfile($username) {
    $username = trim($username);
    if (strlen($username) < 2 || strlen($username) > 39) {
        return null;
    }
    
    $url = "https://api.github.com/users/" . urlencode($username);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => [
            'Accept: application/vnd.github.v3+json',
            'User-Agent: WilliamsBrowser/1.0'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($response !== false) {
        unset($ch);
    }
    
    if ($httpCode !== 200) {
        return null;
    }
    
    $data = json_decode($response, true);
    if (!$data || isset($data['message'])) {
        return null;
    }
    
    return [
        'username' => $data['login'] ?? '',
        'name' => $data['name'] ?? $data['login'],
        'bio' => $data['bio'] ?? '',
        'avatar' => $data['avatar_url'] ?? '',
        'url' => $data['html_url'] ?? '',
        'company' => $data['company'] ?? '',
        'location' => $data['location'] ?? '',
        'followers' => $data['followers'] ?? 0,
        'following' => $data['following'] ?? 0,
        'publicRepos' => $data['public_repos'] ?? 0,
        'blog' => $data['blog'] ?? '',
        'twitter' => $data['twitter_username'] ?? ''
    ];
}

function searchSearx($searxUrl, $query) {
    $encodedQuery = urlencode($query);
    $url = rtrim($searxUrl, '/') . '/search?q=' . $encodedQuery . '&format=json&engines=google,bing,duckduckgo';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 50,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: Williams Browser/1.0'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    if ($response !== false) {
        unset($ch);
    }
    
    if ($httpCode !== 200 || empty($response) || $error) {
        return [];
    }
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['results'])) {
        return [];
    }
    
    $results = [];
    foreach (array_slice($data['results'], 0, 70) as $result) {
        $title = $result['title'] ?? '';
        $url = $result['url'] ?? '';
        $snippet = $result['content'] ?? '';
        
        // Skip adult content
        if (isAdultContent($title, $url, $snippet)) {
            continue;
        }
        
        $results[] = [
            'title' => $title,
            'url' => $url,
            'snippet' => $snippet
        ];
        
        if (count($results) >= 70) break;
    }
    
    return $results;
}

# Simple function to filter out adult content based on keywords in title, URL, and snippet
function isAdultContent($title, $url, $snippet) {
    $adultDomains = [
        'porn', 'xxx', 'sex', 'nude', 'naked', 'erotic', 'adult',
        'xvideos', 'xhamster', 'redtube', 'youporn', 'tube8',
        'spankbang', 'hqporner', 'eporner', 'thumbzilla',
        'xnxx', 'x Videos', 'freeones', 'brazzers', 'naughtyamerica',
        'bangbros', 'realitykings', 'digitalplayground', 'mofos',
        'tna', 'nubiles', 'fakeagent', 'proporn', 'daftsex'
    ];
    
    $adultKeywords = [
        'porn', 'xxx', 'sex tape', 'nude photo', 'naked photo',
        'erotic massage', 'adult content', 'nsfw'
    ];
    
    $combined = strtolower($title . ' ' . $url . ' ' . $snippet);
    
    foreach ($adultDomains as $domain) {
        if (strpos($combined, strtolower($domain)) !== false) {
            return true;
        }
    }
    
    foreach ($adultKeywords as $keyword) {
        if (strpos($combined, strtolower($keyword)) !== false) {
            return true;
        }
    }
    
    return false;
}