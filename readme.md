# Proje Taslağı: Akademik Proje ve Staj "Eşleşme" Portalı

## 1. Proje Tanımı ve Amacı
Bu proje, üniversite ekosistemi içerisindeki akademisyenlerin proje ihtiyaçları ile öğrencilerin teknik yetkinliklerini (tech-stack) optimize edilmiş bir algoritma ile bir araya getiren web tabanlı bir platformdur. Yazılım Mühendisliği öğrencilerinin gerçek dünya projelerine dahil olmasını kolaylaştırmayı ve firmaların doğru stajyer adayına ulaşmasını sağlamayı hedefler.

**Problem:** Akademik ilanların ve staj duyurularının dağınık olması, öğrencilerin yeteneklerine uygun projeleri bulamaması ve manuel eleme süreçlerinin zaman alması.
**Çözüm:** Veri odaklı bir eşleştirme (matching) algoritması kullanan, dinamik ve yerel ağda çalışabilen PHP tabanlı portal.

---

## 2. Teknik Mimari (Tech Stack)
- **Dil:** PHP
- **Veri Saklama:** Yerel dosya tabanli JSON format
- **Arayuz:** HTML5, CSS3, JavaScript (Bootstrap veya Tailwind CSS opsiyonel)
- **Sunucu:** Yerel Ag/LAN yayini

---

## 3. Temel Özellikler ve PHP Fonksiyon Odakları

### Sayfa Yapisi (3 Sayfa)
1. **Giris Sayfasi**
	- Ogrenci girisi
	- Ogretmen girisi
2. **Ogrenci Sayfasi**
3. **Ogretmen Sayfasi**

**Notlar:** Ogretmen sayfayi LAN uzerinden actiginda ogrenciler giris yapip kendi sayfalarini duzenleyebilecek. Her ogrenci, kendi ogrenci nosu ile giris yapip kendi bilgilerini guncelleyebilecek.

### A. "Smart-Match" Algoritması (Projenin Kalbi)
Sıradan bir listeleme yerine, PHP tarafında geliştirilen bir mantık ile eşleşme skoru hesaplanır.
- **PHP Mantığı:** `array_intersect()` veya iç içe döngülerle öğrenci yetenek listesi ile proje gereksinimleri karşılaştırılır.
- **Puanlama:** Eşleşen her yetenek için bir ağırlık puanı atanır ve sonuç `%` olarak kullanıcıya gösterilir.

### B. Profil ve Dosya Yonetimi
- **CRUD Islemleri:** Kullanici kayit, ilan olusturma ve profil guncelleme.
- **Veri Yazma/Okuma:** JSON dosyalarina `file_get_contents()` / `file_put_contents()` ile kayit, `flock()` ile es zamanli yazma kilidi.
- **CV/Portfolyo Yukleme:** `move_uploaded_file()` fonksiyonu kullanilarak guvenli dosya yukleme sistemi.
- **Guvenlik:** `pathinfo()` ile sadece `.pdf` gibi belirli uzantilara izin verilmesi.

### C. Basvuru ve Durum Takibi
- Basvurularin yerel JSON dosyalarinda saklanmasi ve akademisyen panelinde "Onaylandi/Reddedildi/Mulakat" olarak guncellenmesi.

---

## 4. Uygulama ve Dağıtım Stratejisi (LAN Yayını)
Proje, internete çıkış gerektirmeden yerel ağ (Wi-Fi/Ethernet) üzerinden tüm sınıfla etkileşime girecek şekilde tasarlanmıştır.

- **Erişim:** Sunucu bilgisayarın (Hoca/Yönetici) yerel IPv4 adresi üzerinden (Örn: `http://192.168.1.x/portal`).
- **Ölçeklenebilirlik:** Birden fazla öğrencinin aynı anda veri girişi yapabilmesi için `php.ini` üzerinde gerekli performans ayarlamaları (zaman aşımı ve girdi limitleri).

---

## 5. Geliştirici Görüşleri ve Tavsiyeler

### Teknik Tavsiyeler:
1.  **Guvenlik:** Sifreleri asla duz metin olarak saklama; `password_hash()` ve `password_verify()` kullan.
2.  **Temiz Kod:** Sık kullanilan fonksiyonlar ve veri katmani icin `storage.php` veya `functions.php` gibi moduler dosyalar olustur.
3.  **Hata Yonetimi:** Yerel agda test yaparken `error_reporting(E_ALL);` satirini aktif ederek olasi baglanti ve dosya erisim hatalarini anlik takip et.
4.  **Veri Butunlugu:** Dosya yazimlarinda `flock()` kullan, duzenli olarak yedek al.

### Vizyoner Dokunuş:
Bu portal, sadece bir ders projesi değil; üniversite bünyesindeki yazılım toplulukları ve mühendislik adayları için yaşayan bir "Yetenek Havuzu"na dönüştürülebilir. Gelecekte sisteme yapay zeka tabanlı mülakat simülasyonları veya GitHub API entegrasyonu eklenerek proje kapsamı genişletilebilir.

---

**Hazırlayan:** Mehmet Akif Akkoç (Mefamex)
**Tarih:** Nisan 2026
**Ders:** İnternet Programcılığı - Final Projesi Taslağı
