<?php
<?php
// Gelen 'url' parametresini kontrol et
eğer (isset($_GET['url']) && !empty($_GET['url'])) {
    // URL'yi al ve güvenlik için temizle ( kişiyi ama iyi bir pratik)
    $url = htmlözelkarakterler($_GET['url']);

    // Güvenlik için sadece belirli bir alan adını (domain) kontrol edebilirsiniz.
    // eğer (strpos($url, 'bulut.ogm.gov.tr') === false) {
    // die('Erişim reddedildi: Sadece OGM alan adı desteklenmektedir.');
    // }

    // Hedef URL'den içeriği çek
    $içerik = @file_get_contents($url);

    // İçerik çekilebildiyse
    eğer ($içerik !== YANLIŞ) {
        // İçerik tipini HTML olarak belirle
        başlık('İçerik Türü: metin/html');
        // gönderilen içeriği doğrudan yazdırın
        yankı $içerik;
    } başka {
        // Hata durumunda bir mesaj göster
        header("HTTP/1.1 500 Dahili Sunucu Hatası");
        echo "Bağlantı kurulurken bir hata oluştu veya sunucu erişimi reddedildi.";
    }
} başka {
    // 'url' parametresi yoksa bir hata mesajı göster
    header("HTTP/1.1 400 Kötü İstek");
    echo "Hata: 'url' parametresi eksik.";
}
?>
?>