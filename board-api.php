<?php
header('Content-Type: application/json; charset=utf-8');

// קובץ הנתונים
$file = __DIR__ . '/board-data.json';

// יצירת קובץ ראשוני אם לא קיים
if (!file_exists($file)) {
    file_put_contents($file, json_encode([
        'title'   => 'לוח מודעות הבניין',
        'msgs'    => [],
        'adImage' => ''
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : 'load';

if ($method === 'GET' && $action === 'load') {
    $json = file_get_contents($file);
    if ($json === false) {
        echo json_encode(['error' => 'cannot_read'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo $json;
    exit;
}

if ($method === 'POST' && $action === 'save') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        echo json_encode(['error' => 'bad_json'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $clean = [
        'title'   => isset($data['title']) ? (string)$data['title'] : 'לוח מודעות הבניין',
        'msgs'    => [],
        'adImage' => isset($data['adImage']) ? (string)$data['adImage'] : '',
    ];

    if (isset($data['msgs']) && is_array($data['msgs'])) {
        foreach ($data['msgs'] as $m) {
            if (!isset($m['text'])) continue;
            $txt = trim((string)$m['text']);
            if ($txt === '') continue;
            $clean['msgs'][] = ['text' => $txt];
        }
    }

    file_put_contents(
        $file,
        json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );

    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['error' => 'invalid_request'], JSON_UNESCAPED_UNICODE);
