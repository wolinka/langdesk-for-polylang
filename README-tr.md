🇹🇷 **Türkçe** | 🇬🇧 [English](README.md)

---

# LangDesk: Polylang için Çeviri Rolleri

WordPress kullanıcılarına dil atayın; her çevirmen yalnızca **kendi dilinde**
içerik düzenlesin, ama çeviri yapabilmek için kaynak dili okumayı sürdürsün.
Ücretsiz [Polylang](https://wordpress.org/plugins/polylang/) eklentisi üzerine
kuruludur.

LangDesk, çok dilli bir Polylang sitesini çeviri ekipleri için derli toplu bir
çalışma alanına dönüştürür: "her dil tek bir Yazılar listesinde" karmaşası biter,
çevirmen sahip olmadığı bir dili yanlışlıkla bozamaz.

## 🚀 Özellikler

- **Dil bazlı düzenleme:** Bir kullanıcı yalnızca kendisine atanan dilleri
  düzenleyebilir. Diğer diller okunabilir kalır ama değiştirilemez.
- **Yetenek seviyesinde zorlama:** Kısıt `map_meta_cap` üzerinden uygulanır;
  böylece yalnızca klasik düzenleme ekranını değil, blok editör / REST API'yi,
  hızlı düzenlemeyi, toplu düzenlemeyi ve sayfa oluşturucuları (Elementor, Divi)
  de kapsar.
- **Fail-closed (güvenli kapanış):** Bir yazının dili belirlenemiyorsa düzenleme
  izin verilmek yerine reddedilir. Bir erişim kontrolü sızıntısı, hiç olmamasından
  daha kötüdür.
- **Temiz bir çalışma görünümü:** Kısıtlı çevirmenin yazı listeleri varsayılan
  olarak kendi diline filtrelenir. "To translate into X" görünümü o çeviri henüz
  eksik olan kaynak yazıları listeler; "All in {kaynak}" görünümü ise çeviri
  yapılacak tüm kaynak listesini gösterir.
- **Rollerinizle uyumlu:** LangDesk yalnızca dile göre kısıtlar. Kullanıcının
  yayınlayabilmesi mi yoksa yalnızca incelemeye gönderebilmesi mi gerektiği,
  mevcut WordPress rolüne bağlı kalır. Site yöneticileri (administrator) hiçbir
  zaman kısıtlanmaz.
- **Zarif düşme:** Polylang aktif değilse LangDesk hiçbir kısıt eklemez,
  hata vermek yerine bir yönetici uyarısı gösterir.

## ⚙️ Gereksinimler

- WordPress 6.0 veya üzeri
- PHP 7.4 veya üzeri
- [Polylang](https://wordpress.org/plugins/polylang/) (ücretsiz), kurulu ve aktif,
  dilleriniz yapılandırılmış olarak

## 📦 Kurulum

1. Polylang'i kurup etkinleştirin ve dillerinizi yapılandırın.
2. LangDesk'i kurup etkinleştirin.
3. Bir kullanıcının profilini düzenleyin ve **LangDesk: Translation Languages**
   altından izin verilen dilleri seçin.

Çevirmenlere Editor, Author veya Contributor gibi yönetici olmayan bir rol verin.
LangDesk, siteyi tümüyle yönetebilen kullanıcıları hiçbir zaman kısıtlamaz.

## 🔧 Nasıl çalışır

Dil atanmamış bir kullanıcı kısıtsızdır. En az bir dil atandığında (ve kullanıcı
site yöneticisi değilse) kısıtlı çevirmen olur:

- Çeviri yapabilmek için kaynak dahil her dili **okuyabilir**.
- Yalnızca atanan dil(ler)i **yazabilir**. Başka bir dildeki yazıyı düzenleme,
  silme veya yayınlama girişimleri yetenek seviyesinde reddedilir.
- Oluşturduğu yeni içerik kendi diline sabitlenir.
- Dil ataması yalnızca başka kullanıcıları düzenleyebilenler tarafından
  değiştirilebilir; böylece bir çevirmen kendine fazladan dil veremez.

Saklanan tek veri tek bir kullanıcı üst verisidir (`langdesk_allowed_langs`);
eklenti silindiğinde bu da kaldırılır. Özel tablo yok, ayar yok, dış servis yok,
takip yok.

## ❓ Sık Sorulan Sorular

**Polylang Pro gerekir mi?**
Hayır. LangDesk ücretsiz Polylang ile çalışır ve yalnızca onun açık API'sini
kullanır.

**Çevirmen kaynak içeriği yine de görebilir mi?**
Evet. Okuma asla engellenmez; yalnızca diğer dilleri yazmak engellenir.

**Çevirmen yine de her şeyi düzenleyebiliyor, neden?**
LangDesk site yöneticilerini (administrator / `manage_options`) hiçbir zaman
kısıtlamaz. Çevirmene Editor, Author veya Contributor gibi bir rol verin.

## 📄 Lisans

GPLv2 veya üzeri, [GNU GPL v2](https://www.gnu.org/licenses/gpl-2.0.html).

## 🤝 Katkıda Bulunma

Bu depo, dağıtılan eklentinin bir aynasıdır; buraya doğrudan gönderilen
değişiklikler bir sonraki senkronda üzerine yazılır. Bir hata bulduysanız veya
bir fikriniz varsa lütfen tartışmak için bir issue açın.

---

[Özlem Çimen](https://www.linkedin.com/in/ozlemcimen/) tarafından geliştirildi.
Kurumsal WordPress danışmanlığı: [Wolinka](https://wolinka.com.tr)
