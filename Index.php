<?php
// ===== CONFIG =====
$BOTS = [
  "BOT_TOKEN_1",
  "BOT_TOKEN_2",
  // up to 10 tokens
];

$ADMIN_IDS = [123456789]; // your Telegram user ID(s)

// Allowed groups (private OR public)
$ALLOWED_GROUPS = [
  -1001111111111, // private group ID
  -1002222222222  // public group ID
];

// ===== CORE =====
$update = json_decode(file_get_contents("php://input"), true);
$msg = $update["message"] ?? null;
if (!$msg) exit;

$chatId = $msg["chat"]["id"];
$userId = $msg["from"]["id"];
$text   = trim($msg["text"] ?? "");

if (!in_array($chatId, $ALLOWED_GROUPS)) exit;
if (!in_array($userId, $ADMIN_IDS)) exit;

function sendMsg($token, $chatId, $text) {
  file_get_contents("https://api.telegram.org/bot$token/sendMessage?" .
    http_build_query([
      "chat_id" => $chatId,
      "text" => $text
    ])
  );
}

// ===== COMMANDS =====
if ($text === ".ping") {
  sendMsg($BOTS[0], $chatId, "✅ Pong");
}

if (str_starts_with($text, ".bday")) {
  $parts = explode(" ", $text, 3);
  $count = intval($parts[1] ?? 0);

  $message =
    $parts[2] ??
    ($msg["reply_to_message"]["text"] ?? null);

  if ($count <= 0 || !$message) {
    sendMsg($BOTS[0], $chatId, "❌ Format: .bday 50 Happy Birthday");
    exit;
  }

  foreach ($BOTS as $bot) {
    for ($i = 0; $i < $count; $i++) {
      sendMsg($bot, $chatId, $message);
      usleep(500000); // 0.5 sec delay (SAFE)
    }
  }
}
