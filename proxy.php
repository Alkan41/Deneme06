<?php
// Gelen 'url' parametresini kontrol et
if (isset($_GET['url']) && !empty($_GET['url'])) {
    // URL'yi al ve temizle
    $url = htmlspecialchars($_GET['url']);

    // cURL ile bağlantı kurma
    $ch = curl_init();
    
    // cURL ayarlarını yap
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // İçeriği metin olarak geri döndür
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Varsa yönlendirmeleri takip et
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL sertifikasını doğrulama (gerekli olabilir)
    
    // Sayfa içeriğini çek
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Hata kontrolü
    if (curl_errno($ch) || $http_code >= 400) {
        // Hata varsa mesaj göster
        header("HTTP/1.1 500 Internal Server Error");
        echo "Bağlantı hatası: " . curl_error($ch) . " (HTTP Kodu: " . $http_code . ")";
    } else {
        // Başarılıysa içeriği yazdır
        header('Content-Type: text/html');
        echo $content;
    }

    // cURL oturumunu kapat
    curl_close($ch);

} else {
    // 'url' parametresi yoksa hata mesajı göster
    header("HTTP/1.1 400 Bad Request");
    echo "Hata: 'url' parametresi eksik.";
}
?>
