import requests
import random
import concurrent.futures
from datetime import datetime

BASE_URL = "http://localhost:8000"
API_TOKEN = "1|F4cGvlwfr1HE9dQ0FqoXcGh6gM9dJJY90wYxI1pz4ada8003"
CONCURRENT_WORKERS = 20

iller = [
    {"name": "Adana", "slug": "adana"},
    {"name": "Adiyaman", "slug": "adiyaman"},
    {"name": "Afyonkarahisar", "slug": "afyonkarahisar"},
    {"name": "Agri", "slug": "agri"},
    {"name": "Aksaray", "slug": "aksaray"},
    {"name": "Amasya", "slug": "amasya"},
    {"name": "Ankara", "slug": "ankara"},
    {"name": "Antalya", "slug": "antalya"},
    {"name": "Ardahan", "slug": "ardahan"},
    {"name": "Artvin", "slug": "artvin"},
    {"name": "Aydin", "slug": "aydin"},
    {"name": "Balikesir", "slug": "balikesir"},
    {"name": "Bartin", "slug": "bartin"},
    {"name": "Batman", "slug": "batman"},
    {"name": "Bayburt", "slug": "bayburt"},
    {"name": "Bilecik", "slug": "bilecik"},
    {"name": "Bingol", "slug": "bingol"},
    {"name": "Bitlis", "slug": "bitlis"},
    {"name": "Bolu", "slug": "bolu"},
    {"name": "Burdur", "slug": "burdur"},
    {"name": "Bursa", "slug": "bursa"},
    {"name": "Canakkale", "slug": "canakkale"},
    {"name": "Cankiri", "slug": "cankiri"},
    {"name": "Corum", "slug": "corum"},
    {"name": "Denizli", "slug": "denizli"},
    {"name": "Diyarbakir", "slug": "diyarbakir"},
    {"name": "Duzce", "slug": "duzce"},
    {"name": "Edirne", "slug": "edirne"},
    {"name": "Elazig", "slug": "elazig"},
    {"name": "Erzincan", "slug": "erzincan"},
    {"name": "Erzurum", "slug": "erzurum"},
    {"name": "Eskisehir", "slug": "eskisehir"},
    {"name": "Gaziantep", "slug": "gaziantep"},
    {"name": "Giresun", "slug": "giresun"},
    {"name": "Gumushane", "slug": "gumushane"},
    {"name": "Hakkari", "slug": "hakkari"},
    {"name": "Hatay", "slug": "hatay"},
    {"name": "Isparta", "slug": "isparta"},
    {"name": "Mersin", "slug": "mersin"},
    {"name": "Istanbul", "slug": "istanbul"},
    {"name": "Izmir", "slug": "izmir"},
    {"name": "Kars", "slug": "kars"},
    {"name": "Kastamonu", "slug": "kastamonu"},
    {"name": "Kayseri", "slug": "kayseri"},
    {"name": "Kirklareli", "slug": "kirklareli"},
    {"name": "Kirsehir", "slug": "kirsehir"},
    {"name": "Kilis", "slug": "kilis"},
    {"name": "Kocaeli", "slug": "kocaeli"},
    {"name": "Konya", "slug": "konya"},
    {"name": "Kutahya", "slug": "kutahya"},
    {"name": "Malatya", "slug": "malatya"},
    {"name": "Manisa", "slug": "manisa"},
    {"name": "Kahramanmaras", "slug": "kahramanmaras"},
    {"name": "Mardin", "slug": "mardin"},
    {"name": "Mugla", "slug": "mugla"},
    {"name": "Mus", "slug": "mus"},
    {"name": "Nevsehir", "slug": "nevsehir"},
    {"name": "Nigde", "slug": "nigde"},
    {"name": "Ordu", "slug": "ordu"},
    {"name": "Osmaniye", "slug": "osmaniye"},
    {"name": "Rize", "slug": "rize"},
    {"name": "Samsun", "slug": "samsun"},
    {"name": "Sanliurfa", "slug": "sanliurfa"},
    {"name": "Sirnak", "slug": "sirnak"},
    {"name": "Tekirdag", "slug": "tekirdag"},
    {"name": "Tokat", "slug": "tokat"},
    {"name": "Trabzon", "slug": "trabzon"},
    {"name": "Tunceli", "slug": "tunceli"},
    {"name": "Usak", "slug": "usak"},
    {"name": "Van", "slug": "van"},
    {"name": "Yalova", "slug": "yalova"},
    {"name": "Zonguldak", "slug": "zonguldak"},
    {"name": "Karabuk", "slug": "karabuk"},
    {"name": "Karaman", "slug": "karaman"},
    {"name": "Kirikkale", "slug": "kirikkale"},
    {"name": "Yozgat", "slug": "yozgat"},
    {"name": "Sinop", "slug": "sinop"},
]

kategoriler = [
    {"name": "Klima Servisi", "slug": "klima-servisi", "keywords": ["Klima Servisi", "Klima Montaj", "Klima Bakim", "Klima Tamiri", "Klima Gaz Dolum", "Klima Ariza"]},
    {"name": "Dogalgaz", "slug": "dogalgaz", "keywords": ["Dogalgaz Tesisat", "Dogalgaz Abonelik", "Dogalgaz Karti", "Dogalgaz Sayaci", "Dogalgaz Kacak", "Dogalgaz Tesisatci"]},
    {"name": "Kombi Servisi", "slug": "kombi-servisi", "keywords": ["Kombi Servisi", "Kombi Ariza", "Kombi Tamir", "Kombi Bakim", "Kombi Montaj", "Kombi Parcalari"]},
    {"name": "Eczane", "slug": "eczane", "keywords": ["Nobetci Eczane", "Acil Eczane", "Gece Eczanesi", "Eczane Adres", "Eczane Telefon", "Nobetci Eczane"]},
    {"name": "Altin Fiyatlari", "slug": "altin-fiyatlari", "keywords": ["Altin Fiyatlari", "Gram Altin", "Ceyrek Altin", "Yarim Altin", "Tam Altin", "Cumhuriyet Altini"]},
    {"name": "Hava Durumu", "slug": "hava-durumu", "keywords": ["Hava Durumu", "Hava Tahmini", "Bugun Hava", "Yarin Hava", "Haftalik Hava", "Nem Orani"]},
    {"name": "Tesisatci", "slug": "tesisatci", "keywords": ["Tesisatci", "Su Tesisati", "Kanalizasyon", "Tuvalet Tikanikligi", "Musluk Tamiri", "Boru Tamiri"]},
    {"name": "Elektrikci", "slug": "elektrikci", "keywords": ["Elektrikci", "Elektrik Arizasi", "Priz Tamiri", "Elektrikci Cagir", "Avize Montaj", "Elektrik Tadilat"]},
    {"name": "Boccek Ilaclama", "slug": "boccek-ilaclama", "keywords": ["Boccek Ilaclama", "Hasere Ilaclama", "Karinca Ilaclama", "Pire Ilaclama", "Fare Ilaclama", "Hamam Bocegi"]},
    {"name": "Guvenlik Kamerasi", "slug": "guvenlik-kamerasi", "keywords": ["Guvenlik Kamerasi", "Kamera Sistemi", "Kamerali Guvenlik", "Dahua Kamera", "Hikvision Kamera", "Yangin Alarm"]},
    {"name": "Sofben", "slug": "sofben", "keywords": ["Sofben Tamiri", "Sofben Montaj", "Sofben Ariza", "Sofben Servisi", "Sofben Gazi", "Sofben Calsimiyor"]},
    {"name": "Petek Temizligi", "slug": "petek-temizligi", "keywords": ["Petek Temizligi", "Petek Yikama", "Petek Temizleme", "Kalorifer Petek", "Petek Tikankligi", "Petek Havasi"]},
    {"name": "Nakliye", "slug": "nakliye", "keywords": ["Nakliye", "Tasimacilik", "Ev Nakliyesi", "Kamyon", "Kargo", "Tir Nakliye"]},
    {"name": "Oto Kurtarma", "slug": "oto-kurtarma", "keywords": ["Oto Kurtarma", "Cekici", "Yol Yardimi", "Aku Takviye", "Lastik Degisim", "Oto Tamir"]},
    {"name": "Bebek Bakici", "slug": "bebek-bakici", "keywords": ["Bebek Bakici", "Cocuk Bakici", "Yasli Bakici", "Hasta Bakici", "Ev Ici Bakim", "Hemsire"]},
    {"name": "Hali Yikama", "slug": "hali-yikama", "keywords": ["Hali Yikama", "Hali Temizleme", "Koltuk Yikama", "Perde Yikama", "Yer Yikama", "Minder Yikama"]},
    {"name": "Marangoz", "slug": "marangoz", "keywords": ["Marangoz", "Mobilya Tamir", "Ahsap Isleri", "Mutfak Dolabi", "Banyo Dolabi", "Kapı Tamiri"]},
    {"name": "Nefroloji", "slug": "nefroloji", "keywords": ["Nefroloji", "Bobrek Doktoru", "Bobrek Yetmezligi", "Diyaliz", "Bobrek Nakli", "Urolog"]},
    {"name": "Ortopedi", "slug": "ortopedi", "keywords": ["Ortopedi", "Ortopedist", "Diz Protezi", "Bel Agrisi", "Boyun Agrisi", "Skolyoz"]},
    {"name": "Boya Badana", "slug": "boya-badana", "keywords": ["Boya Badana", "Boya Ustasi", "Badana", "Dis Cephe Boya", "Icephe Yikama", "Tadilat"]},
]

body_templates = [
    "{il} bolgesinde {keyword} arayanlar icin en guncel veritabanini hazirladik. {il} ilcesinde hizmet veren en iyi {keyword} firmalari burada.",
    "{il} lokasyonunda {keyword} hizmeti arayanlara en kapsamli rehberi sunuyoruz. Uzman ekiplerimiz {keyword} konusunda en guncel bilgileri derledi.",
    "Biliyorsunuz ki {il} icinde {keyword} konusu oldukca populer. Sizler icin tum detaylari ve {keyword} hizmeti veren firmalari derledik.",
    "{keyword} icin {il}'de en hizli ve guvenilir hizmeti sunan firmalari bulmak artik cok kolay. Sitemiz uzerinden {keyword} arayanlara rehberlik ediyoruz.",
    "{il}'de {keyword} alaninda uzmanlasmis en kapsamli rehberini olusturduk. Tum {keyword} hizmetleri icin sitemizi ziyaret edin.",
    "{keyword} arayan {il} sakinleri icin en guncel verileri paylasiyoruz. {keyword} konusunda en iyi {il} firmalari.",
    "{il}'de {keyword} hizmeti veren en kaliteli firmalari sizler icin bir araya getirdik. {keyword} ariyorsaniz dogru adrestesiniz.",
    "{keyword} konusunda {il}'de deneyimli ve guvenilir firmalari araştırdık. Her noktasinda {keyword} hizmeti icin bize guvenebilirsiniz.",
]

headers = {
    "Authorization": f"Bearer {API_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json"
}

def slugify(text):
    if not text:
        return ""
    turkish = {"ı": "i", "ş": "s", "ğ": "g", "ç": "c", "ö": "o", "ü": "u", "İ": "i", "Ş": "s", "Ğ": "g", "Ç": "c", "Ö": "o", "Ü": "u"}
    s = text.lower()
    for k, v in turkish.items():
        s = s.replace(k, v)
    s = s.replace(" ", "-")
    result = ""
    for c in s:
        if c.isalnum() or c == "-":
            result += c
    return result.strip("-")

def setup():
    print("=" * 60)
    print("🚀 VERI TABANI OLUSTURULUYOR")
    print("=" * 60)
    
    taxonomies = [{"name": k["name"], "slug": k["slug"]} for k in kategoriler]
    r = requests.post(f"{BASE_URL}/api/v1/ingest", json={"taxonomies": taxonomies}, headers=headers, timeout=60)
    print(f"[1/3] Kategoriler: {r.status_code}")
    
    locations = [{"name": il["name"], "slug": il["slug"]} for il in iller]
    r = requests.post(f"{BASE_URL}/api/v1/ingest", json={"locations": locations}, headers=headers, timeout=120)
    print(f"[2/3] Iller: {r.status_code}")
    
    print("[3/3] Icerikler ekleniyor...")
    print("-" * 60)

def process_il(il_data):
    il_name = il_data["name"]
    il_slug = il_data["slug"]
    count = 0
    
    for kat in kategoriler:
        for keyword in kat["keywords"][:5]:
            title = f"{il_name} {keyword}"
            slug = slugify(f"{title}-{random.randint(10000, 99999)}")
            
            node = {
                "title": title,
                "slug": slug,
                "body_content": random.choice(body_templates).format(il=il_name, keyword=keyword),
                "is_restricted_content": False,
                "taxonomy_slug": kat["slug"],
                "location_slug": il_slug,
                "published_at": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            }
            
            try:
                r = requests.post(f"{BASE_URL}/api/v1/ingest", json={"content_nodes": [node]}, headers=headers, timeout=30)
                if r.status_code == 200:
                    count += 1
            except:
                pass
    
    return count

if __name__ == "__main__":
    setup()
    
    total = 0
    with concurrent.futures.ThreadPoolExecutor(max_workers=CONCURRENT_WORKERS) as executor:
        results = list(executor.map(process_il, iller))
        total = sum(results)
    
    print("=" * 60)
    print(f"✨ TAMAMLANDI!")
    print(f"📊 Toplam Icerik: {total}")
    print(f"📍 Il sayisi: {len(iller)}")
    print(f"📂 Kategori sayisi: {len(kategoriler)}")
    print(f"🔢 Potansiyel: {len(iller) * len(kategoriler) * 5}")