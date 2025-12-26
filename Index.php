
<?php
// ===== CONFIG =====
$BOT_TOKENS = [
    "5758296224:AAFov4wERpro_jN_55SPa4C3_edmdUcQEpU",
    "5105477515:AAHs_KuqtP5kYikZ2R5-qmag0tBTrDZr5xE",
  
];

$ALLOWED_USERS = [
    5374210828, // apna Telegram user ID
    7246595801
];

$ALLOWED_GROUPS = []; // empty = jis group me bot add ho, wahi allowed

$MAX_COUNT = 50; // safety limit (responsible use)

// ===== HELPERS =====
$input = file_get_contents("php://input");
$update = json_decode($input, true);

if (!$update) exit;

$message = $update['message'] ?? null;
if (!$message) exit;

$chatId = $message['chat']['id'];
$userId = $message['from']['id'];
$text   = $message['text'] ?? '';

function api($token, $method, $data) {
    $url = "https://api.telegram.org/bot{$token}/{$method}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $data,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// ===== ACCESS CHECK =====
if (!in_array($userId, $GLOBALS['ALLOWED_USERS'])) {
    exit;
}

// ===== COMMANDS =====
if ($text === '.ping') {
    foreach ($BOT_TOKENS as $token) {
        api($token, "sendMessage", [
            "chat_id" => $chatId,
            "text" => "PONG ✅"
        ]);
    }
}

if (strpos($text, '.spam') === 0) {
    $parts = explode(' ', $text, 3);
    $count = intval($parts[1] ?? 0);
    $msg   = $parts[2] ?? '';

    if ($count <= 0 || $count > $MAX_COUNT || $msg === '') {
        exit;
    }

    foreach ($BOT_TOKENS as $token) {
        for ($i = 0; $i < $count; $i++) {
            api($token, "sendMessage", [
                "chat_id" => $chatId,
                "text" => $msg
            ]);
            usleep(300000); // 0.3 sec delay (safe)
        }
    }
}
