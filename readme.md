# Proje: Akademik Proje ve Staj "Eslesme" Portali

## 1. Proje Tanimi ve Amaci
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

## 3. Temel Ozellikler ve Uygulama Detaylari

### Sayfa Yapisi (3 Sayfa)
1. **Giris Sayfasi**
	- Ogrenci girisi
	- Ogretmen girisi
2. **Ogrenci Sayfasi**
3. **Ogretmen Sayfasi**

**Notlar:** Ogretmen sayfayi LAN uzerinden actiginda ogrenciler giris yapip kendi sayfalarini duzenleyebilecek. Her ogrenci, kendi ogrenci nosu ile giris yapip kendi bilgilerini guncelleyebilecek.

### A. "Smart-Match" Algoritmasi (Projenin Kalbi)
Sıradan bir listeleme yerine, PHP tarafında geliştirilen bir mantık ile eşleşme skoru hesaplanır.
- **PHP Mantığı:** `array_intersect()` veya iç içe döngülerle öğrenci yetenek listesi ile proje gereksinimleri karşılaştırılır.
- **Puanlama:** Eşleşen her yetenek için bir ağırlık puanı atanır ve sonuç `%` olarak kullanıcıya gösterilir.

### B. Profil ve Dosya Yonetimi
- **CRUD Islemleri:** Kullanici kayit, ilan olusturma ve profil guncelleme.
- **Veri Yazma/Okuma:** JSON dosyalarina `file_get_contents()` / `file_put_contents()` ile kayit, `flock()` ile es zamanli yazma kilidi.
- **CV/Portfolyo Yukleme:** `move_uploaded_file()` fonksiyonu kullanilarak guvenli dosya yukleme sistemi.
- **Guvenlik:** `pathinfo()` ile sadece `.pdf` gibi belirli uzantilara izin verilmesi.

### C. Basvuru ve Durum Takibi
- Basvurularin yerel JSON dosyalarinda saklanmasi ve akademisyen panelinde "Bekliyor/Onaylandi/Reddedildi/Mulakat" olarak guncellenmesi.

### D. Arayuz ve Kullanilabilirlik (Son Duzenlemeler)
- Tum sayfalarda ustte ders/proje bilgisi iceren banner.
- Kart tabanli yerlesim, rozetli eslesme yuzdesi ve durum etiketleri.
- Tablolarda sabit baslik (sticky header) ve mobil icin yatay kaydirma.
- Ogretmen panelinde ilan, basvuru ve ogrenci listeleri icin anlik arama (yazdikca filtre).

---

## 4. Uygulama ve Dagitim Stratejisi (LAN Yayini)
Proje, internete çıkış gerektirmeden yerel ağ (Wi-Fi/Ethernet) üzerinden tüm sınıfla etkileşime girecek şekilde tasarlanmıştır.

- **Erişim:** Sunucu bilgisayarın (Hoca/Yönetici) yerel IPv4 adresi üzerinden (Örn: `http://192.168.1.x/portal`).
- **Ölçeklenebilirlik:** Birden fazla öğrencinin aynı anda veri girişi yapabilmesi için `php.ini` üzerinde gerekli performans ayarlamaları (zaman aşımı ve girdi limitleri).

---

## 5. Gelistirici Gorusleri ve Tavsiyeler

### Teknik Tavsiyeler:
1.  **Guvenlik:** Sifreleri asla duz metin olarak saklama; `password_hash()` ve `password_verify()` kullan.
2.  **Temiz Kod:** Sık kullanilan fonksiyonlar ve veri katmani icin `storage.php` veya `functions.php` gibi moduler dosyalar olustur.
3.  **Hata Yonetimi:** Yerel agda test yaparken `error_reporting(E_ALL);` satirini aktif ederek olasi baglanti ve dosya erisim hatalarini anlik takip et.
4.  **Veri Butunlugu:** Dosya yazimlarinda `flock()` kullan, duzenli olarak yedek al.

### Vizyoner Dokunus:
Bu portal, sadece bir ders projesi değil; üniversite bünyesindeki yazılım toplulukları ve mühendislik adayları için yaşayan bir "Yetenek Havuzu"na dönüştürülebilir. Gelecekte sisteme yapay zeka tabanlı mülakat simülasyonları veya GitHub API entegrasyonu eklenerek proje kapsamı genişletilebilir.

---

## 6. Baslangic Notlari
- **Varsayilan girisler:** Ogrenci no `240211003`, ogretmen `admin / admin123`.
- **Veri dosyalari:** Ornek ogrenci ve ilan verileri JSON dosyalarinda hazir.
- **Calistirma:** Yerel sunucuda `public/` klasorunu yayinlamak yeterli.

---

## 7. Dosya Yapisi
```
data/
	applications.json
	projects.json
	students.json
	teachers.json
	.htaccess
includes/
	auth.php
	config.php
	helpers.php
	storage.php
public/
	index.php
	logout.php
	student.php
	teacher.php
	styles.css
uploads/
readme.md
run.sh
```

---

**Hazırlayan:** Mehmet Akif Akkoç (Mefamex)
**Tarih:** Mayıs 2026
**Ders:** İnternet Programcılığı - Final Projesi Taslağı
