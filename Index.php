<?php
// ===== CONFIG =====
$BOTS = [
  "5758296224:AAFov4wERpro_jN_55SPa4C3_edmdUcQEpU",
  "5105477515:AAHs_KuqtP5kYikZ2R5-qmag0tBTrDZr5xE",
  // up to 10
];

// Only these users can use commands
$ALLOWED_USERS = [
  5374210828,// your ID
  7246595801
];

$MAX_COUNT = 2000;     // high but safe
$DELAY_US  = 300000;  // ❗ minimum safe delay (0.3 sec)

// ===== CORE =====
$update = json_decode(file_get_contents("php://input"), true);
$msg = $update["message"] ?? null;
if (!$msg) exit;

$chatId   = $msg["chat"]["id"];
$chatType = $msg["chat"]["type"];
$userId   = $msg["from"]["id"];
$text     = trim($msg["text"] ?? "");

// Only group / supergroup
if (!in_array($chatType, ["group", "supergroup"])) exit;

// Only allowed users
if (!in_array($userId, $ALLOWED_USERS)) exit;

// Telegram helper
function tg($token, $method, $params = []) {
  $url = "https://api.telegram.org/bot$token/$method";
  $opts = ["http" => [
    "method"  => "POST",
    "header"  => "Content-Type: application/json",
    "content" => json_encode($params)
  ]];
  return json_decode(file_get_contents($url, false, stream_context_create($opts)), true);
}

// Check admin
$member = tg($GLOBALS['BOTS'][0], "getChatMember", [
  "chat_id" => $chatId,
  "user_id" => $userId
]);
$status = $member["result"]["status"] ?? "";
if (!in_array($status, ["creator", "administrator"])) exit;

// Send message (ONLY provided text)
function sendMsg($token, $chatId, $text) {
  tg($token, "sendMessage", [
    "chat_id" => $chatId,
    "text"    => $text
  ]);
}

// ===== COMMANDS =====
if ($text === ".ping") {
  sendMsg($BOTS[0], $chatId, "pong");
  exit;
}

if (str_starts_with($text, ".bday")) {
  $parts = explode(" ", $text, 3);
  $count = intval($parts[1] ?? 0);

  // ❗ NO DEFAULT MESSAGE
  $message =
    $parts[2] ??
    ($msg["reply_to_message"]["text"] ?? null);

  if ($count <= 0 || !$message) exit;
  if ($count > $MAX_COUNT) exit;

  foreach ($BOTS as $bot) {
    for ($i = 0; $i < $count; $i++) {
      sendMsg($bot, $chatId, $message);
      usleep($DELAY_US);
    }
  }
}
