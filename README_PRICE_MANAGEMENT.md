# 🎯 Gopak Admin Panel - Fiyat Yönetim Sistemi

Bu dokümantasyon, Gopak admin panelinde yeni eklenen kapsamlı fiyat yönetim sistemini açıklamaktadır.

## 🚀 Özellikler

### 1. **Toplu Fiyat Güncelleme**
- Tüm ürün fiyatlarına yüzde bazında artış/azalış uygulama
- Örnek: %10 zam, %5 indirim gibi
- Güncelleme nedeni belirtme
- Etkilenecek ürünlerin önizlemesi

### 2. **Tekil Ürün Fiyat Düzenleme**
- Her ürünün fiyatını ayrı ayrı değiştirme
- Fiyat değişim geçmişi takibi
- Güncelleme nedeni kaydetme

### 3. **Fiyat Geçmişi Takibi**
- Tüm fiyat değişimlerinin kaydı
- Artış/azalış istatistikleri
- Hangi admin tarafından ne zaman yapıldığı bilgisi

### 4. **Çanta Ürünleri Özel Yönetimi**
- 3D Çanta (Yan Körüklü) fiyatları
- Düz Çanta (Yan Körüksüz) fiyatları
- Her boyut için farklı fiyat
- Minimum sipariş miktarları

## 📁 Dosya Yapısı

```
api/
├── admin_prices.php          # Fiyat yönetimi API'si
├── admin_price_history.php   # Fiyat geçmişi API'si
└── admin_products.php        # Ürün yönetimi API'si (güncellendi)

admin_panel.php               # Admin panel (fiyat yönetimi eklendi)

sql/
└── database_setup.sql        # Veritabanı yapısı (güncellendi)
```

## 🗄️ Veritabanı Yapısı

### `products` Tablosu
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500),
    category VARCHAR(100) DEFAULT 'Standart',
    is_custom BOOLEAN DEFAULT FALSE,
    bag_type VARCHAR(100) NULL,
    bag_dimensions VARCHAR(100) NULL,
    min_order_quantity INT NULL,
    bag_description TEXT NULL,
    has_custom_print BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### `price_history` Tablosu
```sql
CREATE TABLE price_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    change_type ENUM('increase', 'decrease') NOT NULL,
    percentage_change DECIMAL(5,2) NOT NULL,
    reason TEXT,
    affected_products INT DEFAULT 1,
    product_id INT NULL,
    product_name VARCHAR(255) NULL,
    old_price DECIMAL(10,2) NULL,
    new_price DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_change_type (change_type),
    INDEX idx_product_id (product_id)
);
```

## 🔧 Kurulum

### 1. Veritabanı Kurulumu
```bash
# MySQL veritabanında çalıştırın
mysql -u username -p database_name < sql/database_setup.sql
```

### 2. Dosya Yükleme
- Tüm dosyaları web sunucunuza yükleyin
- `config/database.php` dosyasında veritabanı bağlantı bilgilerini güncelleyin

### 3. Admin Girişi
- `admin_login.php` üzerinden giriş yapın
- Admin panelinde "💰 Fiyat Yönetimi" sekmesini görün

## 📖 Kullanım Kılavuzu

### Toplu Fiyat Güncelleme
1. Admin panelinde "💰 Fiyat Yönetimi" sekmesine tıklayın
2. "📈 Toplu Fiyat Güncelle" butonuna tıklayın
3. Fiyat değişim yüzdesini girin (örn: 10 = %10 artış, -5 = %5 azalış)
4. Güncelleme nedenini yazın
5. Etkilenecek ürünleri önizleyin
6. "Fiyatları Güncelle" butonuna tıklayın

### Tekil Ürün Fiyat Düzenleme
1. Fiyat listesinde "Fiyat Düzenle" butonuna tıklayın
2. Yeni fiyatı girin
3. Güncelleme nedenini yazın
4. "Fiyatı Güncelle" butonuna tıklayın

### Fiyat Geçmişi Görüntüleme
- Fiyat Yönetimi sayfasının alt kısmında otomatik olarak görüntülenir
- Tüm değişimler tarih sırasına göre listelenir
- Artış/azalış istatistikleri gösterilir

## 🔒 Güvenlik

- Tüm fiyat güncellemeleri admin kimlik doğrulaması gerektirir
- Fiyat değişimleri geri alınamaz (sadece yeni güncelleme ile düzeltilebilir)
- Tüm işlemler loglanır ve takip edilebilir

## 📊 API Endpoints

### `GET /api/admin_prices.php`
- Tüm ürün fiyatlarını getirir
- Admin kimlik doğrulaması gerekmez (sadece görüntüleme)

### `POST /api/admin_prices.php`
- Toplu fiyat güncellemesi yapar
- Admin kimlik doğrulaması gerekir

### `PUT /api/admin_prices.php?id={product_id}`
- Tek ürün fiyatını günceller
- Admin kimlik doğrulaması gerekir

### `GET /api/admin_price_history.php`
- Fiyat değişim geçmişini getirir
- Admin kimlik doğrulaması gerekir

## 🎨 Özelleştirme

### CSS Stilleri
Fiyat yönetimi için özel CSS stilleri `admin_panel.php` dosyasında tanımlanmıştır:
- `.status-badge` - Durum rozetleri
- `.data-table` - Veri tabloları
- `.modal` - Modal pencereler

### JavaScript Fonksiyonları
- `loadPrices()` - Fiyat verilerini yükler
- `displayPrices()` - Fiyat listesini gösterir
- `displayPriceHistory()` - Fiyat geçmişini gösterir
- `showBulkPriceModal()` - Toplu güncelleme modalını açar
- `editProductPrice()` - Tekil fiyat düzenleme modalını açar

## 🚨 Hata Yönetimi

- Tüm API yanıtları JSON formatında
- Hata durumlarında uygun HTTP status kodları
- Kullanıcı dostu hata mesajları
- Console'da detaylı hata logları

## 📈 Performans

- Veritabanı indeksleri optimize edilmiştir
- Sayfalama ile büyük veri setleri desteklenir
- Asenkron API çağrıları ile hızlı yanıt
- Önbellek mekanizmaları

## 🔄 Güncelleme Geçmişi

### v1.0.0 (İlk Sürüm)
- Toplu fiyat güncelleme sistemi
- Tekil ürün fiyat düzenleme
- Fiyat geçmişi takibi
- Admin panel entegrasyonu
- Çanta ürünleri özel yönetimi

## 🤝 Destek

Herhangi bir sorun veya öneri için:
- GitHub Issues kullanın
- Admin panel loglarını kontrol edin
- Veritabanı bağlantısını test edin

## 📝 Lisans

Bu proje Gopak için özel olarak geliştirilmiştir.
