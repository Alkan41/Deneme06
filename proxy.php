<?php
header('Access-Control-Allow-Origin: *');

// Hedef URL'yi al
$url = $_GET['url'] ?? '';

// URL boşsa veya geçerli değilse hata ver
if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    die('Hata: Geçersiz veya boş URL.');
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    http_response_code(500);
    die('cURL hatası: ' . curl_error($ch));
}

// Orijinal sayfanın Content-Type başlığını gönder
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
if ($content_type) {
    header('Content-Type: ' . $content_type);
} else {
    header('Content-Type: text/html; charset=utf-8');
}

http_response_code($http_code);
echo $response;

curl_close($ch);
?>
