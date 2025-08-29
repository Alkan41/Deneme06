<?php

// Güvenlik: Yalnızca izin verilen alan adlarını tanımlayın.
// Bu, sunucunuzun kötü niyetli kullanımlara karşı korunmasını sağlar.
$allowed_domains = [
    'bulut.ogm.gov.tr',
    'www.mywebsite.com' // Kendi siteniz de olabilir
];

// Orijinal istek başlıklarını kontrol et ve güvenlik için ek başlıkları kaldır.
function filterHeaders($ch, $header) {
    $header_line = trim($header);
    // Güvenlik için iframe kısıtlamalarını kaldıran başlıkları filtrele
    if (stripos($header_line, 'X-Frame-Options') !== false ||
        stripos($header_line, 'Content-Security-Policy') !== false) {
        return strlen($header);
    }
    // Content-Security-Policy (CSP) başlığını filtrele ve sadece 'frame-ancestors' yönergesini kaldır.
    if (stripos($header_line, 'Content-Security-Policy') !== false) {
        $header_line = preg_replace('/(frame-ancestors) [^;]+;?/i', '', $header_line);
        header($header_line, false);
        return strlen($header);
    }
    // Diğer tüm başlıkları olduğu gibi ilet
    header($header_line, false);
    return strlen($header);
}

// URL doğrulamasını ve yönlendirmesini yap
$url = $_GET['url'] ?? '';

// URL boş veya geçerli değilse hata ver
if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    die('Hata: Geçersiz veya boş URL.');
}

// URL'nin izin verilen alan adında olup olmadığını kontrol et
$url_host = parse_url($url, PHP_URL_HOST);
if (!in_array($url_host, $allowed_domains)) {
    http_response_code(403);
    die('Hata: Bu alana erişim izniniz yok.');
}

// cURL oturumu başlat
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'filterHeaders');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

if ($response === false) {
    http_response_code(500);
    die('cURL hatası: ' . curl_error($ch));
}

// HTML içeriğindeki göreceli bağlantıları mutlak proxy URL'lerine dönüştür
// Bu, iframe içinde gezinme ve bağlantıların doğru çalışmasını sağlar
if (strpos($content_type, 'text/html') !== false) {
    $dom = new DOMDocument;
    @$dom->loadHTML($response, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    $links = $xpath->query('//a[@href]');

    foreach ($links as $link) {
        $href = $link->getAttribute('href');
        // Eğer bağlantı göreceli ise veya aynı alan adındaysa
        if (strpos($href, 'http') !== 0 && strpos($href, 'mailto') !== 0) {
            $base_url = rtrim($url, '/') . '/';
            $absolute_url = parse_url($base_url, PHP_URL_SCHEME) . '://' . parse_url($base_url, PHP_URL_HOST) . '/' . ltrim($href, '/');
            $link->setAttribute('href', 'proxy.php?url=' . urlencode($absolute_url));
        } elseif (strpos($href, $url_host) !== false) {
             $link->setAttribute('href', 'proxy.php?url=' . urlencode($href));
        }
    }
    $response = $dom->saveHTML();
}

http_response_code($http_code);
header('Content-Type: ' . ($content_type ? $content_type : 'text/html; charset=utf-8'));
echo $response;

curl_close($ch);
?>
