<?php
// Veritabanı bağlantı ayarları - Hostinger
// Bu bilgileri hPanel > Veritabanları > MySQL Veritabanları'ndan alacaksınız

$host = 'localhost'; // Genellikle localhost
$dbname = 'u144576298_main'; // SİZİN veritabanı adınız
$username = 'u144576298_muriarty1893'; // SİZİN kullanıcı adınız
$password = 'Murat0133.'; // SİZİN şifreniz

// NOT: Bu bilgileri hPanel'den aldıktan sonra güncelleyiniz!

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
