<?php
// vk-news.php — серверный прокси к VK API (wall.get)

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Access-Control-Allow-Origin: *');

$TOKEN   = 'vk1.a.CrA9_JIZ4L3zD5DbXmEWXKI8WY6dKCF0gFKgdXAEpoh0GnEmhA05GxINLP0o9ha1XAZ0kwqbiQnIUV8-xmL_gHDem4nLY7aFcSYZrs1MujbWGLTxOer8OO6p8L0gfwgK7guekrEClhtiCvFQrkXhMUuFNlFvZMTZYkC_hj24vTCCcplBGAijLOVsLj-pY-iZ';
$API_V   = '5.199';
$OWNER_ID = -227671500; // natopchane

$count  = isset($_GET['count'])  ? max(1, min(100, (int)$_GET['count'])) : 9;
$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

$params = [
  'owner_id'     => $OWNER_ID,
  'count'        => $count,
  'offset'       => $offset,
  'extended'     => 1,
  'access_token' => $TOKEN,
  'v'            => $API_V,
];

$url = 'https://api.vk.com/method/wall.get?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

// ---- HTTP-запрос (cURL -> fallback stream) ----
function http_get($url) {
  if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT        => 20,
      CURLOPT_USERAGENT      => 'vk-proxy/1.0'
    ]);
    $res  = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($res === false || $code !== 200) {
      return [null, "HTTP $code $err"];
    }
    return [$res, null];
  } else {
    $ctx = stream_context_create([
      'http' => ['method'=>'GET', 'timeout'=>20, 'header'=>"User-Agent: vk-proxy/1.0\r\n"]
    ]);
    $res = @file_get_contents($url, false, $ctx);
    if ($res === false) return [null, 'file_get_contents failed'];
    return [$res, null];
  }
}

list($raw, $httpErr) = http_get($url);
if ($httpErr) {
  echo json_encode(['error' => ['message' => 'VK request failed', 'detail' => $httpErr]], JSON_UNESCAPED_UNICODE);
  exit;
}

echo $raw;
