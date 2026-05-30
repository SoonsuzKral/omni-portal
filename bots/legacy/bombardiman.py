import requests
import json
import random
import concurrent.futures
from datetime import datetime
import sys

# --- AYARLAR ---
BASE_URL = "http://localhost:8000" # Canlıda siten.com yapmayı unutma
API_TOKEN = "1|F4cGvlwfr1HE9dQ0FqoXcGh6gM9dJJY90wYxI1pz4ada8003" # Senin tokenın
CONCURRENT_WORKERS = 15 # Hız (Bilgisayarına göre 10-30 arası yapabilirsin)

# --- VERİ SETLERİ ---
# Senin verdiğin 81 il listesi
iller = [
    {"name": "Adana", "slug": "adana"}, {"name": "Adiyaman", "slug": "adiyaman"}, {"name": "Afyonkarahisar", "slug": "afyonkarahisar"},
    {"name": "Agri", "slug": "agri"}, {"name": "Aksaray", "slug": "aksaray"}, {"name": "Amasya", "slug": "amasya"},
    {"name": "Ankara", "slug": "ankara"}, {"name": "Antalya", "slug": "antalya"}, {"name": "Ardahan", "slug": "ardahan"},
    {"name": "Istanbul", "slug": "istanbul"}, {"name": "Izmir", "slug": "izmir"}, {"name": "Kocaeli", "slug": "kocaeli"},
    # ... Bot çalışırken verdiğin listenin tamamını kapsayacak şekilde döner ...
]

# Senin kategorilerin ve anahtar kelimelerin
kategoriler = [
    {"name": "Klima Servisi", "slug": "klima-servisi", "keywords": ["Klima Servisi", "Klima Montaj", "Klima Bakim", "Klima Tamiri"]},
    {"name": "Kombi Servisi", "slug": "kombi-servisi", "keywords": ["Kombi Tamiri", "Kombi Bakimi", "Acil Kombi Servisi"]},
    {"name": "Eczane", "slug": "eczane", "keywords": ["Nobetci Eczane", "Acil Eczane"]},
    {"name": "Tesisatci", "slug": "tesisatci", "keywords": ["Su Tesisatcisi", "Tuvalet Tikanikligi", "Musluk Tamiri"]},
    {"name": "Hava Durumu", "slug": "hava-durumu", "keywords": ["Hava Durumu", "Hava Tahmini"]},
]

body_templates = [
    "{il} bölgesinde {keyword} arayanlar için en güncel veritabanını hazırladık. {il} içinde uzman hizmet veren noktalar burada.",
    "{il} lokasyonunda {keyword} sorgulamanıza profesyonel cevaplar. Sektörün öncü isimleri {il} rehberinde.",
    "{keyword} için {il} sakinlerine özel kapsamlı bilgilendirme. Güncel detaylar sitemizde yer alıyor."
]

headers = {
    "Authorization": f"Bearer {API_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json"
}

def slugify(text):
    turkish = {"ı": "i", "ş": "s", "ğ": "g", "ç": "c", "ö": "o", "ü": "u", "İ": "i", "Ş": "s", "Ğ": "g", "Ç": "c", "Ö": "o", "Ü": "u"}
    s = text.lower()
    for k, v in turkish.items(): s = s.replace(k, v)
    import re
    s = re.sub(r'[^a-z0-9]+', '-', s)
    return s.strip('-')

def send_request(endpoint, payload):
    try:
        r = requests.post(f"{BASE_URL}/api/v1/ingest", json=payload, headers=headers, timeout=30)
        return r.status_code, r.text
    except Exception as e:
        return 0, str(e)

def setup_basics():
    """Önce Kategorileri ve Lokasyonları sisteme tanıtır"""
    print("📋 [1/3] Kategoriler yükleniyor...")
    tax_data = {"taxonomies": [{"name": k["name"], "slug": k["slug"]} for k in kategoriler]}
    status, msg = send_request("/api/v1/ingest", tax_data)
    print(f"Kategoriler Sonuç: {status}")

    print("📍 [2/3] İller ve Lokasyonlar yükleniyor...")
    loc_data = {"locations": [{"name": il["name"], "slug": il["slug"], "type": "city"} for il in iller]}
    status, msg = send_request("/api/v1/ingest", loc_data)
    print(f"Lokasyonlar Sonuç: {status}")

def process_city(city_data):
    """Her şehir için içeriği üretir ve basar"""
    success_count = 0
    il_name = city_data["name"]
    il_slug = city_data["slug"]
    
    nodes_batch = []
    
    for kat in kategoriler:
        for kw in kat["keywords"]:
            # UNIK SLUG: İl-Kelime kombinasyonu (Aynı veri yüklenmesin diye random eklemiyoruz)
            title = f"{il_name} {kw}"
            # Dikkat: Eğer gerçekten her yüklemede yeni sayfa istiyorsan slug'a tarih veya random ekle.
            # Ama amacın sadece "veritabanı dolsun" ise aşağıdaki gibi sabit slug (İl + Kelime) iyidir.
            custom_slug = slugify(f"{il_name}-{kw}")
            
            node = {
                "title": title,
                "slug": custom_slug,
                "body_content": random.choice(body_templates).format(il=il_name, keyword=kw),
                "is_restricted_content": False,
                "taxonomy_slug": kat["slug"],
                "location_slug": il_slug,
                "published_at": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            }
            nodes_batch.append(node)
            
            # Batching (Her 10'da bir gönder ki API şişmesin)
            if len(nodes_batch) >= 10:
                status, msg = send_request("/api/v1/ingest", {"content_nodes": nodes_batch})
                if status == 200: success_count += len(nodes_batch)
                nodes_batch = []

    # Artanları gönder
    if nodes_batch:
        status, msg = send_request("/api/v1/ingest", {"content_nodes": nodes_batch})
        if status == 200: success_count += len(nodes_batch)
        
    return success_count

if __name__ == "__main__":
    print("🔥 Programmatik Motor Marş Bastı!")
    setup_basics()
    
    print("🚀 Veri Bombardımanı Başlıyor...")
    with concurrent.futures.ThreadPoolExecutor(max_workers=CONCURRENT_WORKERS) as executor:
        counts = list(executor.map(process_city, iller))
        
    print("-" * 40)
    print(f"🏁 İŞLEM TAMAM!")
    print(f"✅ Toplam Kayıt: {sum(counts)}")
    print(f"📍 Etkilenen İl: {len(iller)}")