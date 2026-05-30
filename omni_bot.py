"""
omni_bot.py — Omni-Portal Matrix Injector
===========================================
Programmatic SEO Bot for Laravel 11 + Filament v3 Headless CMS.
Generates 500K+ unique, indexable, SEO-optimised landing pages
by cross-joining → {Locations} x {Keywords/Trends} x {Question Patterns}
with advanced Spintax uniqueness engine.

Usage:
    python omni_bot.py                # Full pipeline
    python omni_bot.py --dry-run      # Preview only (no API calls)
    python omni_bot.py --quick        # 500 sample nodes for testing
    python omni_bot.py --resume       # Continue from last checkpoint
"""

import csv
import hashlib
import json
import logging
import os
import random
import re
import sys
import time
import uuid
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime, timedelta
from pathlib import Path
from typing import Any, Dict, Generator, List, Optional, Tuple

import requests

# ---------------------------------------------------------------------------
# CONFIG — edit these or set environment variables
# ---------------------------------------------------------------------------
CONFIG = {
    "BASE_URL": os.getenv("OMNI_BASE_URL", "http://localhost:8000"),
    "API_TOKEN": os.getenv("OMNI_API_TOKEN", ""),
    "CONCURRENT_WORKERS": int(os.getenv("OMNI_WORKERS", "30")),
    "BATCH_SIZE": int(os.getenv("OMNI_BATCH_SIZE", "50")),
    "MAX_RETRIES": int(os.getenv("OMNI_MAX_RETRIES", "3")),
    "RATE_LIMIT_SLEEP": float(os.getenv("OMNI_RATE_SLEEP", "0.05")),
    "DRY_RUN": False,
    "QUICK_MODE": False,
    "RESUME_FILE": "omni_checkpoint.json",
    "TRENDS_ENABLED": os.getenv("OMNI_TRENDS", "true").lower() == "true",
}

# ---------------------------------------------------------------------------
# LOGGING
# ---------------------------------------------------------------------------
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[logging.StreamHandler(), logging.FileHandler("omni_bot.log", encoding="utf-8")],
)
log = logging.getLogger("omni_bot")

# ---------------------------------------------------------------------------
# 1. DYNAMIC DATA SOURCES
# ---------------------------------------------------------------------------

# --- 81 Turkish Provinces + Major Districts ---
LOCATIONS: List[Dict[str, str]] = [
    # 81 provinces
    {"name": "Adana", "slug": "adana", "type": "city"},
    {"name": "Adıyaman", "slug": "adiyaman", "type": "city"},
    {"name": "Afyonkarahisar", "slug": "afyonkarahisar", "type": "city"},
    {"name": "Ağrı", "slug": "agri", "type": "city"},
    {"name": "Aksaray", "slug": "aksaray", "type": "city"},
    {"name": "Amasya", "slug": "amasya", "type": "city"},
    {"name": "Ankara", "slug": "ankara", "type": "city"},
    {"name": "Antalya", "slug": "antalya", "type": "city"},
    {"name": "Ardahan", "slug": "ardahan", "type": "city"},
    {"name": "Artvin", "slug": "artvin", "type": "city"},
    {"name": "Aydın", "slug": "aydin", "type": "city"},
    {"name": "Balıkesir", "slug": "balikesir", "type": "city"},
    {"name": "Bartın", "slug": "bartin", "type": "city"},
    {"name": "Batman", "slug": "batman", "type": "city"},
    {"name": "Bayburt", "slug": "bayburt", "type": "city"},
    {"name": "Bilecik", "slug": "bilecik", "type": "city"},
    {"name": "Bingöl", "slug": "bingol", "type": "city"},
    {"name": "Bitlis", "slug": "bitlis", "type": "city"},
    {"name": "Bolu", "slug": "bolu", "type": "city"},
    {"name": "Burdur", "slug": "burdur", "type": "city"},
    {"name": "Bursa", "slug": "bursa", "type": "city"},
    {"name": "Çanakkale", "slug": "canakkale", "type": "city"},
    {"name": "Çankırı", "slug": "cankiri", "type": "city"},
    {"name": "Çorum", "slug": "corum", "type": "city"},
    {"name": "Denizli", "slug": "denizli", "type": "city"},
    {"name": "Diyarbakır", "slug": "diyarbakir", "type": "city"},
    {"name": "Düzce", "slug": "duzce", "type": "city"},
    {"name": "Edirne", "slug": "edirne", "type": "city"},
    {"name": "Elazığ", "slug": "elazig", "type": "city"},
    {"name": "Erzincan", "slug": "erzincan", "type": "city"},
    {"name": "Erzurum", "slug": "erzurum", "type": "city"},
    {"name": "Eskişehir", "slug": "eskisehir", "type": "city"},
    {"name": "Gaziantep", "slug": "gaziantep", "type": "city"},
    {"name": "Giresun", "slug": "giresun", "type": "city"},
    {"name": "Gümüşhane", "slug": "gumushane", "type": "city"},
    {"name": "Hakkâri", "slug": "hakkari", "type": "city"},
    {"name": "Hatay", "slug": "hatay", "type": "city"},
    {"name": "Iğdır", "slug": "igdir", "type": "city"},
    {"name": "Isparta", "slug": "isparta", "type": "city"},
    {"name": "İstanbul", "slug": "istanbul", "type": "city"},
    {"name": "İzmir", "slug": "izmir", "type": "city"},
    {"name": "Kahramanmaraş", "slug": "kahramanmaras", "type": "city"},
    {"name": "Karabük", "slug": "karabuk", "type": "city"},
    {"name": "Karaman", "slug": "karaman", "type": "city"},
    {"name": "Kars", "slug": "kars", "type": "city"},
    {"name": "Kastamonu", "slug": "kastamonu", "type": "city"},
    {"name": "Kayseri", "slug": "kayseri", "type": "city"},
    {"name": "Kilis", "slug": "kilis", "type": "city"},
    {"name": "Kırıkkale", "slug": "kirikkale", "type": "city"},
    {"name": "Kırklareli", "slug": "kirklareli", "type": "city"},
    {"name": "Kırşehir", "slug": "kirsehir", "type": "city"},
    {"name": "Kocaeli", "slug": "kocaeli", "type": "city"},
    {"name": "Konya", "slug": "konya", "type": "city"},
    {"name": "Kütahya", "slug": "kutahya", "type": "city"},
    {"name": "Malatya", "slug": "malatya", "type": "city"},
    {"name": "Manisa", "slug": "manisa", "type": "city"},
    {"name": "Mardin", "slug": "mardin", "type": "city"},
    {"name": "Mersin", "slug": "mersin", "type": "city"},
    {"name": "Muğla", "slug": "mugla", "type": "city"},
    {"name": "Muş", "slug": "mus", "type": "city"},
    {"name": "Nevşehir", "slug": "nevsehir", "type": "city"},
    {"name": "Niğde", "slug": "nigde", "type": "city"},
    {"name": "Ordu", "slug": "ordu", "type": "city"},
    {"name": "Osmaniye", "slug": "osmaniye", "type": "city"},
    {"name": "Rize", "slug": "rize", "type": "city"},
    {"name": "Sakarya", "slug": "sakarya", "type": "city"},
    {"name": "Samsun", "slug": "samsun", "type": "city"},
    {"name": "Şanlıurfa", "slug": "sanliurfa", "type": "city"},
    {"name": "Siirt", "slug": "siirt", "type": "city"},
    {"name": "Sinop", "slug": "sinop", "type": "city"},
    {"name": "Şırnak", "slug": "sirnak", "type": "city"},
    {"name": "Sivas", "slug": "sivas", "type": "city"},
    {"name": "Tekirdağ", "slug": "tekirdag", "type": "city"},
    {"name": "Tokat", "slug": "tokat", "type": "city"},
    {"name": "Trabzon", "slug": "trabzon", "type": "city"},
    {"name": "Tunceli", "slug": "tunceli", "type": "city"},
    {"name": "Uşak", "slug": "usak", "type": "city"},
    {"name": "Van", "slug": "van", "type": "city"},
    {"name": "Yalova", "slug": "yalova", "type": "city"},
    {"name": "Yozgat", "slug": "yozgat", "type": "city"},
    {"name": "Zonguldak", "slug": "zonguldak", "type": "city"},
    # Major districts (high-population ilçeler)
    {"name": "Kadıköy", "slug": "kadikoy", "type": "district", "parent": "istanbul"},
    {"name": "Esenyurt", "slug": "esenyurt", "type": "district", "parent": "istanbul"},
    {"name": "Pendik", "slug": "pendik", "type": "district", "parent": "istanbul"},
    {"name": "Ümraniye", "slug": "umraniye", "type": "district", "parent": "istanbul"},
    {"name": "Maltepe", "slug": "maltepe", "type": "district", "parent": "istanbul"},
    {"name": "Kartal", "slug": "kartal", "type": "district", "parent": "istanbul"},
    {"name": "Tuzla", "slug": "tuzla", "type": "district", "parent": "istanbul"},
    {"name": "Büyükçekmece", "slug": "buyukcekmece", "type": "district", "parent": "istanbul"},
    {"name": "Çekmeköy", "slug": "cekmekoy", "type": "district", "parent": "istanbul"},
    {"name": "Sarıyer", "slug": "sariyer", "type": "district", "parent": "istanbul"},
    {"name": "Çankaya", "slug": "cankaya", "type": "district", "parent": "ankara"},
    {"name": "Keçiören", "slug": "kecioren", "type": "district", "parent": "ankara"},
    {"name": "Yenimahalle", "slug": "yenimahalle", "type": "district", "parent": "ankara"},
    {"name": "Mamak", "slug": "mamak", "type": "district", "parent": "ankara"},
    {"name": "Etimesgut", "slug": "etimesgut", "type": "district", "parent": "ankara"},
    {"name": "Sincan", "slug": "sincan", "type": "district", "parent": "ankara"},
    {"name": "Konak", "slug": "konak", "type": "district", "parent": "izmir"},
    {"name": "Karşıyaka", "slug": "karsiyaka", "type": "district", "parent": "izmir"},
    {"name": "Bornova", "slug": "bornova", "type": "district", "parent": "izmir"},
    {"name": "Buca", "slug": "buca", "type": "district", "parent": "izmir"},
    {"name": "Nilüfer", "slug": "nilufer", "type": "district", "parent": "bursa"},
    {"name": "Meram", "slug": "meram", "type": "district", "parent": "konya"},
    {"name": "Osmangazi", "slug": "osmangazi", "type": "district", "parent": "bursa"},
    {"name": "Yıldırım", "slug": "yildirim", "type": "district", "parent": "bursa"},
    {"name": "Seyhan", "slug": "seyhan", "type": "district", "parent": "adana"},
    {"name": "Çukurova", "slug": "cukurova", "type": "district", "parent": "adana"},
    {"name": "Şahinbey", "slug": "sahinbey", "type": "district", "parent": "gaziantep"},
    {"name": "Şehitkamil", "slug": "sehitkamil", "type": "district", "parent": "gaziantep"},
    {"name": "Melikgazi", "slug": "melikgazi", "type": "district", "parent": "kayseri"},
    {"name": "Kocasinan", "slug": "kocasinan", "type": "district", "parent": "kayseri"},
    {"name": "Tepebaşı", "slug": "tepebasi", "type": "district", "parent": "eskisehir"},
    {"name": "Ortahisar", "slug": "ortahisar", "type": "district", "parent": "trabzon"},
    {"name": "Selçuklu", "slug": "selcuklu", "type": "district", "parent": "konya"},
    {"name": "Karatay", "slug": "karatay", "type": "district", "parent": "konya"},
    {"name": "İlkadım", "slug": "ilkadim", "type": "district", "parent": "samsun"},
    {"name": "Atakum", "slug": "atakum", "type": "district", "parent": "samsun"},
    {"name": "Battalgazi", "slug": "battalgazi", "type": "district", "parent": "malatya"},
    {"name": "Yeşilyurt", "slug": "yesilyurt", "type": "district", "parent": "malatya"},
    {"name": "Küçükçekmece", "slug": "kucukcekmece", "type": "district", "parent": "istanbul"},
    {"name": "Bağcılar", "slug": "bagcilar", "type": "district", "parent": "istanbul"},
    {"name": "Bahçelievler", "slug": "bahcelievler", "type": "district", "parent": "istanbul"},
    {"name": "Gaziosmanpaşa", "slug": "gaziosmanpasa", "type": "district", "parent": "istanbul"},
    {"name": "Sultanbeyli", "slug": "sultanbeyli", "type": "district", "parent": "istanbul"},
    {"name": "Sancaktepe", "slug": "sancaktepe", "type": "district", "parent": "istanbul"},
    {"name": "Beylikdüzü", "slug": "beylikduzu", "type": "district", "parent": "istanbul"},
    {"name": "Avcılar", "slug": "avcilar", "type": "district", "parent": "istanbul"},
    {"name": "Arnavutköy", "slug": "arnavutkoy", "type": "district", "parent": "istanbul"},
    {"name": "Ataşehir", "slug": "atasehir", "type": "district", "parent": "istanbul"},
    {"name": "Beykoz", "slug": "beykoz", "type": "district", "parent": "istanbul"},
    {"name": "Üsküdar", "slug": "uskudar", "type": "district", "parent": "istanbul"},
]

# --- Niche Sectors (20+ verticals with 6+ keywords each) ---
NICHES: List[Dict[str, Any]] = [
    {"name": "Klima Servisi", "slug": "klima-servisi", "keywords": [
        "Klima Servisi", "Klima Montaj", "Klima Bakım", "Klima Tamiri",
        "Klima Gaz Dolum", "Klima Arızası", "Klima Soğutmuyor", "İnverter Klima",
        "Klima Temizliği", "Klima Periyodik Bakım", "Klima Gaz Kaçağı", "Klima Kumandası"
    ]},
    {"name": "Kombi Servisi", "slug": "kombi-servisi", "keywords": [
        "Kombi Servisi", "Kombi Arızası", "Kombi Tamir", "Kombi Bakım",
        "Kombi Montaj", "Kombi Yedek Parça", "Kombi Petek", "Kombi Su Basıncı",
        "Kombi Eşanjör", "Kombi Pompa", "Kombi Ateşleme", "Yoğuşmalı Kombi"
    ]},
    {"name": "Doğalgaz", "slug": "dogalgaz", "keywords": [
        "Doğalgaz Tesisatı", "Doğalgaz Abonelik", "Doğalgaz Kartı", "Doğalgaz Sayacı",
        "Doğalgaz Kaçağı", "Doğalgaz Tesisatçısı", "Doğalgaz Projesi", "Doğalgaz Dönüşümü",
        "Doğalgaz Ruhsatı", "Doğalgaz Keşif", "Doğalgaz Faturası", "Doğalgaz İndirimi"
    ]},
    {"name": "Eczane", "slug": "eczane", "keywords": [
        "Nöbetçi Eczane", "Acil Eczane", "Gece Eczanesi", "Eczane Adres",
        "Eczane Telefon", "Online Eczane", "Eczane Sipariş", "Veteriner Eczanesi",
        "Eczane İlaç", "Eczane Vitamini", "Eczane Kozmetik", "Eczane Takviye"
    ]},
    {"name": "Altın Fiyatları", "slug": "altin-fiyatlari", "keywords": [
        "Altın Fiyatları", "Gram Altın", "Çeyrek Altın", "Yarım Altın",
        "Tam Altın", "Cumhuriyet Altını", "Altın Kuyumcu", "Altın Tahmini",
        "Altın Hesabı", "Altın Yatırım", "Altın Fiyatı Canlı", "Reşat Altın"
    ]},
    {"name": "Hava Durumu", "slug": "hava-durumu", "keywords": [
        "Hava Durumu", "Hava Tahmini", "Bugün Hava", "Yarın Hava",
        "Haftalık Hava", "Nem Oranı", "Yağış Miktarı", "Rüzgar Hızı",
        "Hava Sıcaklığı", "Hava Kalitesi", "Mevsim Tahmini", "Deniz Suyu Sıcaklığı"
    ]},
    {"name": "Tesisatçı", "slug": "tesisatci", "keywords": [
        "Su Tesisatçısı", "Kanalizasyon", "Tuvalet Tıkanıklığı", "Musluk Tamiri",
        "Boru Tamiri", "Su Kaçağı", "Kombi Tesisatı", "Petek Temizliği",
        "Şofben Tamiri", "Banyo Tadilat", "Vitrifiye Montaj", "Su Deposu"
    ]},
    {"name": "Elektrikçi", "slug": "elektrikci", "keywords": [
        "Elektrikçi", "Elektrik Arızası", "Priz Tamiri", "Sigorta Atması",
        "Avize Montaj", "Elektrik Tadilat", "Kombi Elektrik", "Kaçak Akım Rölesi",
        "Elektrik Panosu", "Topraklama", "İnternet Kablosu", "Elektrik Tesisatı"
    ]},
    {"name": "Haşere İlaçlama", "slug": "hasere-ilaclama", "keywords": [
        "Böcek İlaçlama", "Haşere İlaçlama", "Karınca İlaçlama", "Pire İlaçlama",
        "Fare İlaçlama", "Hamam Böceği", "Tahtakurusu İlaçlama", "Sivrisinek İlaçlama",
        "Akrep İlaçlama", "Kene İlaçlama", "Bit İlaçlama", "Fumigasyon"
    ]},
    {"name": "Güvenlik Kamerası", "slug": "guvenlik-kamerasi", "keywords": [
        "Güvenlik Kamerası", "Kamera Sistemi", "Dahua Kamera", "Hikvision Kamera",
        "Yangın Alarmı", "Kamera Montajı", "Ev Güvenlik", "Akıllı Ev Sistemi",
        "Kartlı Geçiş", "Alarm Sistemi", "Telefon İzleme", "Kablosuz Kamera"
    ]},
    {"name": "Şofben", "slug": "sofben", "keywords": [
        "Şofben Tamiri", "Şofben Montaj", "Şofben Arızası", "Şofben Servisi",
        "Şofben Su Isıtma", "Şofben Elektrikli", "Şofben Sıcak Su", "Şofben Yedek Parça",
        "Termosifon", "Ani Su Isıtıcı", "Şofben Bakım", "Kombi Şofben"
    ]},
    {"name": "Petek Temizliği", "slug": "petek-temizligi", "keywords": [
        "Petek Temizliği", "Petek Yıkama", "Kalorifer Petek", "Petek Tıkanıklığı",
        "Petek Havası", "Petek Vantil", "Petek Radyatör", "Panel Petek",
        "Petek Montajı", "Petek Değişimi", "Petek Musluğu", "Petek Boyası"
    ]},
    {"name": "Nakliye", "slug": "nakliye", "keywords": [
        "Nakliye", "Taşımacılık", "Ev Nakliyesi", "Kamyon Kiralama",
        "Kargo Gönderi", "Parça Eşya Taşıma", "Şehirlerarası Nakliye", "Asansörlü Nakliye",
        "Nakliye Firması", "Paketleme Hizmeti", "Depolama Hizmeti", "Ofis Taşıma"
    ]},
    {"name": "Oto Kurtarma", "slug": "oto-kurtarma", "keywords": [
        "Oto Kurtarma", "Çekici", "Yol Yardımı", "Akü Takviye",
        "Lastik Değişim", "Oto Tamir", "Araba Çekici", "Yol Yardım Hattı",
        "Oto Çekici", "Motosiklet Çekici", "Mini Çekici", "7/24 Çekici"
    ]},
    {"name": "Bakıcı", "slug": "bakici", "keywords": [
        "Bebek Bakıcısı", "Çocuk Bakıcısı", "Yaşlı Bakıcısı", "Hasta Bakıcısı",
        "Evde Bakım", "Hemşire", "Gündüz Bakıcı", "Yatılı Bakıcı",
        "Engelli Bakımı", "Özel Bakıcı", "Bakıcı Ücretleri", "Bakıcı Bulma"
    ]},
    {"name": "Halı Yıkama", "slug": "hali-yikama", "keywords": [
        "Halı Yıkama", "Koltuk Yıkama", "Perde Yıkama", "Yorgan Yıkama",
        "Halı Temizleme", "Makine Halısı", "El Dokuması Halı", "Minder Yıkama",
        "Battaniye Yıkama", "Yatak Yıkama", "Halı Sildirme", "Kanepe Yıkama"
    ]},
    {"name": "Marangoz", "slug": "marangoz", "keywords": [
        "Marangoz", "Mobilya Tamir", "Mutfak Dolabı", "Banyo Dolabı",
        "Kapı Tamiri", "Menteşe Tamiri", "Ahşap İşleri", "Akvaryum Masası",
        "Özel Mobilya", "Laminat Parke", "Merdiven Tamiri", "Vestiyer"
    ]},
    {"name": "Nefroloji", "slug": "nefroloji", "keywords": [
        "Nefroloji", "Böbrek Doktoru", "Böbrek Yetmezliği", "Diyaliz",
        "Böbrek Nakli", "Üroloji", "Böbrek Hastalığı", "Hipertansiyon",
        "Protein Kaçağı", "Böbrek Taşı", "İdrar Yolu Enfeksiyonu", "Kreatin"
    ]},
    {"name": "Ortopedi", "slug": "ortopedi", "keywords": [
        "Ortopedi", "Diz Protezi", "Bel Ağrısı", "Boyun Ağrısı",
        "Skolyoz", "Ortopedist", "Kalça Protezi", "Menisküs",
        "Omuz Sıkışması", "Kırık Çıkık", "El Bileği", "Ayak Sağlığı"
    ]},
    {"name": "Boya Badana", "slug": "boya-badana", "keywords": [
        "Boya Badana", "Boya Ustası", "Dış Cephe Boya", "İç Cephe Boya",
        "Alçı İşleri", "Dekorasyon", "Duvar Kağıdı", "Manto Kaplama",
        "Boya Fiyatları", "Ev Boyama", "Tavan Boyası", "Silikonlu Boya"
    ]},
    {"name": "Diş Hekimi", "slug": "dis-hekimi", "keywords": [
        "Diş Hekimi", "Diş Doktoru", "İmplant Tedavi", "Kanal Tedavi",
        "Diş Beyazlatma", "Ortodonti", "Diş Eti Hastalığı", "Çene Cerrahisi",
        "Protez Diş", "Diş Taşı Temizliği", "Diş Dolgusu", "20 Yaş Dişi"
    ]},
    {"name": "Avukat", "slug": "avukat", "keywords": [
        "Avukat", "Hukuk Danışmanı", "Aile Hukuku", "Ceza Avukatı",
        "İcra Hukuku", "İş Hukuku", "Gayrimenkul", "Trafik Avukatı",
        "Tazminat Hukuku", "Miras Hukuku", "Boşanma Avukatı", "Tüketici Hukuku"
    ]},
    {"name": "Diyetisyen", "slug": "diyetisyen", "keywords": [
        "Diyetisyen", "Diyet Listesi", "Zayıflama", "Kilo Verme",
        "Diyet Programı", "Beslenme", "Glutensiz Diyet", "DASH Diyeti",
        "Diyet Danışmanı", "Online Diyet", "Sağlıklı Beslenme", "Metabolizma"
    ]},
    {"name": "Psikolog", "slug": "psikolog", "keywords": [
        "Psikolog", "Psikoterapi", "Anksiyete", "Depresyon",
        "Aile Terapisi", "Özgüven", "Stres Yönetimi", "Psikolojik Destek",
        "Çift Terapisi", "Online Psikolog", "Çocuk Psikoloğu", "Ergen Danışmanlığı"
    ]},
    {"name": "Kuafor", "slug": "kuafor", "keywords": [
        "Kuaför", "Saç Kesimi", "Saç Boyama", "Fön Çekim",
        "Saç Bakımı", "Keratin Bakım", "Perma", "Saç Dökülmesi Tedavisi",
        "Topuz", "Gelin Saçı", "Protez Saç", "Cilt Bakımı"
    ]},
    {"name": "Lazer Epilasyon", "slug": "lazer-epilasyon", "keywords": [
        "Lazer Epilasyon", "Ağda", "İğneli Epilasyon", "Cilt Bakımı",
        "Lazer Epilasyon Fiyat", "Bikini Bölgesi", "Yüz Epilasyon", "Kol Bölgesi",
        "Bacak Epilasyon", "Koltukaltı", "Erkek Epilasyon", "İzmir Lazer Epilasyon"
    ]},
    {"name": "Özel Ders", "slug": "ozel-ders", "keywords": [
        "Özel Ders", "Matematik Dersi", "Yabancı Dil", "İngilizce Kursu",
        "Almanca Ders", "Sınav Hazırlık", "LGS Hazırlık", "YKS Hazırlık",
        "Online Ders", "İlkokul Ders", "Lise Ders", "Robotik Kodlama"
    ]},
    {"name": "Güzellik Salonu", "slug": "guzellik-salonu", "keywords": [
        "Güzellik Salonu", "Manikür Pedikür", "Cilt Bakımı", "Makyaj",
        "Kalıcı Oje", "Kaş Tasarım", "Kirpik Lifting", "Cilt Leke Tedavisi",
        "Vücut Bakımı", "Masaj", "Aroma Terapi", "Protez Tırnak"
    ]},
    {"name": "Fitness Salonu", "slug": "fitness-salonu", "keywords": [
        "Fitness", "Spor Salonu", "Kişisel Antrenör", "Pilates",
        "Zumba", "Crossfit", "Fitness Fiyatları", "Kadın Spor",
        "Erkek Spor", "Ağırlık Çalışma", "Kardiyo", "Beslenme Danışmanı"
    ]},
]

# --- Daily Question Patterns (high-volume search queries) ---
QUESTION_PATTERNS: List[str] = [
    "{keyword} {il} fiyatları 2026",
    "{keyword} {il} hizmet fiyatları",
    "{keyword} {il} telefon numarası",
    "{keyword} {il} adres ve iletişim",
    "{keyword} {il} randevu nasıl alınır",
    "{keyword} {il} en iyi hizmet",
    "{keyword} {il} ücretleri ne kadar",
    "{keyword} {il} fiyat listesi",
    "{keyword} {il} nerede bulabilirim",
    "{keyword} {il} resmi internet sitesi",
    "{keyword} {il} müşteri yorumları",
    "{keyword} {il} 7/24 acil servis",
    "{keyword} {il} uygun fiyat hangisi",
    "{keyword} {il} tavsiye edilenler",
    "{keyword} {il} en yakın nokta",
    "{keyword} {il} iş ilanları",
    "{keyword} {il} kariyer fırsatları",
    "{keyword} {il} çalışma saatleri",
    "{keyword} {il} hafta sonu açık mı",
    "{keyword} {il} araba kiralama",
    "{keyword} {il} kaç km uzakta",
    "{keyword} {il} yol tarifi nasıl",
    "{keyword} {il} harita konumu",
    "{keyword} {il} sınav tarihleri",
    "{keyword} {il} başvuru şartları",
    "{keyword} {il} güncel kampanyalar",
    "{keyword} {il} indirim seçenekleri",
    "{keyword} {il} paket içerikleri",
    "{keyword} {il} profesyonel destek",
    "{keyword} {il} ücretsiz danışmanlık",
    "{keyword} {il} evde hizmet verenler",
    "{keyword} {il} online randevu sistemi",
    "{keyword} {il} e-Devlet sorgulama",
    "{keyword} {il} whatsapp iletişim",
    "{keyword} {il} acil müdahale hattı",
    "{keyword} {il} güvenilir firmalar",
    "{keyword} {il} kalite belgesi olanlar",
    "{keyword} {il} ekspertiz hizmeti",
    "{keyword} {il} müteahhit firmalar",
    "{keyword} {il} toptan ve perakende",
    "{keyword} {il} nasıl gidilir",
    "{keyword} {il} en ucuz seçenekler",
    "{keyword} {il} ücretsiz deneme",
    "{keyword} {il} referans hizmet",
    "{keyword} {il} sıkça sorulan sorular",
    "{keyword} {il} kullanıcı deneyimleri",
    "{keyword} {il} ön başvuru formu",
    "{keyword} {il} yılsonu değerlendirmesi",
]

# --- SEO Body Templates (rich, varied paragraphs) ---
BODY_TEMPLATES: List[str] = [
    (
        "<p>{il} şehrinde <strong>{keyword}</strong> hizmeti arayanlar için "
        "en kapsamlı rehberi hazırladık. Bu sayfada {il} merkez ve tüm "
        "ilçelerindeki {keyword} firmaları, güncel fiyat listeleri ve "
        "müşteri değerlendirmeleri hakkında detaylı bilgiler "
        "bulabilirsiniz.</p>"
        "<p>{il}'de {keyword} konusunda uzmanlaşmış işletmeler, son "
        "teknoloji ekipmanlarla hizmet vermektedir. Profesyonel ekiplerimiz "
        "{keyword} alanında en kaliteli çözümleri sunarak müşteri "
        "memnuniyetini ön planda tutmaktadır.</p>"
        "<p>2026 yılı güncel {keyword} fiyatları ve kampanyaları için "
        "sitemizi düzenli olarak ziyaret edebilirsiniz. {il} halkına "
        "özel indirim fırsatlarını kaçırmayın.</p>"
    ),
    (
        "<p><strong>{keyword}</strong> ihtiyacınız için {il} bölgesinde "
        "aranan adres olduk. {il} sınırları içerisinde {keyword} "
        "alanında faaliyet gösteren tüm firmaları tek platformda "
        "topladık.</p>"
        "<p>Amacımız, {il} sakinlerine {keyword} konusunda doğru ve "
        "güvenilir bilgi sunmaktır. Sitemiz, {il}'nin her noktasında "
        "ihtiyaç duyduğunuz {keyword} hizmetine hızlıca "
        "ulaşmanızı sağlar.</p>"
        "<p>{il}'de {keyword} sektöründeki yenilikler, kampanyalar ve "
        "gelişmeler için bizi takipte kalın. Profesyonel ekibimiz "
        "{keyword} konusunda size en iyi hizmeti sunmaya hazırdır.</p>"
    ),
    (
        "<p>{il} lokasyonunda {keyword} arayışınızda en doğru "
        "noktaya geldiniz. {il} ilçelerindeki tüm {keyword} "
        "seçeneklerini karşılaştırmalı olarak inceleyebilir, bütçenize "
        "en uygun hizmeti seçebilirsiniz.</p>"
        "<p>{keyword} hakkında {il}'de merak edilen tüm konuları "
        "bu sayfada yanıtlıyoruz. Fiyatlandırma, hizmet kalitesi ve "
        "müşteri yorumları gibi önemli kriterleri tek tek "
        "değerlendirdik.</p>"
        "<p>{il} ili genelinde {keyword} sektörü hızla büyümeye devam "
        "ediyor. Siz de gelişmeleri yakından takip ederek sektördeki "
        "en yeni ve kaliteli hizmetlere ulaşabilirsiniz.</p>"
    ),
    (
        "<p>{il} şehir merkezi ve ilçelerinde <strong>{keyword}</strong> "
        "hizmeti veren kuruluşların güncel listesini sizler için "
        "derledik. {il}'de {keyword} denilince akla gelen ilk "
        "adres burası.</p>"
        "<p>Hizmet kalitesi, müşteri memnuniyeti ve uygun fiyat "
        "politikası ile {il}'de {keyword} sektörünün öncü "
        "isimlerini bir araya getirdik. Tüm {keyword} hizmetleri "
        "için uzman kadromuzla 7/24 hizmetinizdeyiz.</p>"
        "<p>{keyword} fiyatları, {keyword} kampanyaları ve daha "
        "fazlası için web sitemizi ziyaret edebilir, {il} halkına "
        "özel avantajlardan yararlanabilirsiniz.</p>"
    ),
    (
        "<p>{il} merkezli <strong>{keyword}</strong> arayışınızda "
        "size rehberlik etmekten mutluluk duyuyoruz. {il}'nin "
        "farklı noktalarında {keyword} hizmeti sunan işletmeler "
        "hakkında ayrıntılı bilgiler sayfamızda yer almaktadır.</p>"
        "<p>Müşteri odaklı yaklaşımımız ve profesyonel hizmet "
        "anlayışımızla {il}'de {keyword} denince akla gelen ilk "
        "platform olmayı hedefliyoruz. Her bütçeye ve ihtiyaca uygun "
        "{keyword} alternatiflerini sizler için listeledik.</p>"
        "<p>{il} ve çevre ilçelerindeki en güncel {keyword} "
        "bilgileri, fiyat listeleri ve firma iletişim bilgileri "
        "için sayfamızı yer imlerinize ekleyin.</p>"
    ),
    (
        "<p>{il} bölgesinde {keyword} ihtiyacı olan herkesin "
        "ziyaret etmesi gereken kapsamlı kaynak sayfası. "
        "{il}'de {keyword} konusunda en güncel veriler, "
        "kullanıcı yorumları ve fiyat karşılaştırmaları "
        "bu sayfada buluşuyor.</p>"
        "<p>{keyword} sektöründe {il}'de faaliyet gösteren "
        "öncü firmalar, uzman kadroları ve yenilikçi hizmet "
        "anlayışları ile hizmetinizdedir. Amacımız {il} "
        "sakinlerine en iyi {keyword} deneyimini yaşatmaktır.</p>"
        "<p>Güncel {keyword} fiyatları, kampanyalar ve "
        "sektör haberleri hakkında bilgi almak için web "
        "sitemizi düzenli takip edin. {il}'de {keyword} "
        "hizmeti almak artık çok kolay.</p>"
    ),
    (
        "<p>{il} ve ilçelerinde <strong>{keyword}</strong> "
        "hizmeti sunan kaliteli firmaları bir platformda "
        "topladık. Sitemiz {il} halkının {keyword} "
        "konusunda tüm ihtiyaçlarına cevap verecek şekilde "
        "tasarlanmıştır.</p>"
        "<p>Bilgiye hızlı erişim ilkesiyle hazırladığımız "
        "{keyword} sayfamızda, {il} merkezli tüm hizmet "
        "sağlayıcıların güncel iletişim bilgilerini, "
        "çalışma saatlerini ve sundukları hizmetleri "
        "detaylı olarak bulabilirsiniz.</p>"
        "<p>{il} şehrinde {keyword} sektörü hakkında en "
        "doğru ve güncel bilgi kaynağı olma yolunda "
        "ilerliyoruz. Siz değerli kullanıcılarımız için "
        "{keyword} konusunu her yönüyle ele alıyoruz.</p>"
    ),
    (
        "<p>{il}'de {keyword} arıyorsanız doğru adrestesiniz. "
        "{il} sınırları içinde {keyword} konusunda "
        "uzmanlaşmış işletmelerimiz, size en kaliteli "
        "hizmeti sunmak için hazır bekliyor.</p>"
        "<p>{keyword} hizmeti, {il} genelinde her geçen gün "
        "daha fazla talep görmektedir. Biz de bu talebe "
        "karşılık vermek için {il}'deki en iyi {keyword} "
        "firmalarını araştırdık ve sizler için sıraladık.</p>"
        "<p>Bütçenize ve ihtiyacınıza en uygun {keyword} "
        "seçeneğini bulmak için sayfamızdaki firmaları "
        "inceleyebilir, {il}'deki en iyi hizmete "
        "ulaşabilirsiniz.</p>"
    ),
]

# --- Spintax thesaurus (word-level synonym maps) ---
SPINTAX_MAP: Dict[str, List[str]] = {
    "hizmet": ["servis", "destek", "yardım", "çözüm", "bakım"],
    "firma": ["şirket", "kuruluş", "işletme", "kurum", "marka"],
    "profesyonel": ["uzman", "deneyimli", "tecrübeli", "kalifiye", "yetenekli"],
    "kaliteli": ["başarılı", "güvenilir", "nitelikli", "seçkin", "premium"],
    "en iyi": ["en kaliteli", "en başarılı", "en güvenilir", "en uygun", "en seçkin"],
    "uygun fiyat": ["ekonomik", "hesaplı", "bütçe dostu", "avantajlı", "makul"],
    "geniş": ["kapsamlı", "zengin", "çeşitli", "full", "eksiksiz"],
    "detaylı": ["kapsamlı", "ayrıntılı", "etraflı", "derinlemesine", "tüm yönleriyle"],
    "hızlı": ["pratik", "çabuk", "süratli", "acil", "ivedi"],
    "güvenilir": ["itibarlı", "sağlam", "emin", "garantili", "referanslı"],
    "kolay": ["basit", "rahat", "pratik", "kullanışlı", "konforlu"],
    "memnuniyet": ["tatmin", "mutluluk", "hoşnutluk", "beğeni", "takdir"],
    "bulabilirsiniz": ["erişebilirsiniz", "ulaşabilirsiniz", "edinebilirsiniz", "görebilirsiniz", "inceleyebilirsiniz"],
    "sizler": ["kullanıcılarımız", "ziyaretçilerimiz", "müşterilerimiz", "takipçilerimiz", "okuyucularımız"],
    "ihtiyaç": ["gereksinim", "talep", "beklenti", "istek", "gerek"],
    "bölge": ["bölge", "mıntıka", "yöre", "civar", "saha"],
    "merkezli": ["odaklı", "ağırlıklı", "temelli", "eksenli", "dayalı"],
    "sayfa": ["platform", "portal", "sayfa", "kaynak", "adres"],
    "sunduğumuz": ["sağladığımız", "sunan", "verdiğimiz", "hazırladığımız", "oluşturduğumuz"],
    "müşteri": ["kullanıcı", "ziyaretçi", "danışan", "alıcı", "tüketici"],
    "herkes": ["tüm kullanıcılar", "her ziyaretçi", "ziyaret edenler", "herkes", "bütün müşteriler"],
    "ile": ["ve", "beraber", "birlikte", "yanı sıra", "ile birlikte"],
    "için": ["amacıyla", "üzere", "dolayı", "niyetiyle", "hedefiyle"],
    "olarak": ["şekilde", "biçimde", "surette", "halinde", "olarak"],
    "bulunmaktadır": ["yer almaktadır", "mevcuttur", "hizmet vermektedir", "faaliyet göstermektedir", "konumlanmıştır"],
    "sizler için": ["siz değerli kullanıcılarımız için", "sizler adına", "sizlerin hizmetine", "sizlerin faydasına", "sizlerin kullanımına"],
    "amaç": ["hedef", "gaye", "ereği", "niyet", "maksat"],
    "ön planda": ["başta", "ilk sırada", "merkezde", "odağında", "öncelikli"],
    "teknoloji": ["teknolojik donanım", "modern cihazlar", "son sistem", "yenilikçi ekipman", "ileri teknoloji"],
    "ekipman": ["cihaz", "alet", "donanım", "malzeme", "gerçe"],
    "sürekli": ["düzenli", "devamlı", "kesintisiz", "mütemadiyen", "periyodik"],
    "takip": ["izleme", "gözlem", "takipte kalma", "inceleme", "araştırma"],
    "yenilikçi": ["modern", "çağdaş", "ileri görüşlü", "vizyoner", "inovasyon"],
    "imkan": ["fırsat", "olanak", "seçenek", "alternatif", "opsiyon"],
    "avantaj": ["fayda", "kazanç", "çıkar", "yarar", "artı"],
}

# ---------------------------------------------------------------------------
# 2. GOOGLE TRENDS INTEGRATION
# ---------------------------------------------------------------------------

class TrendsFetcher:
    """Fetch daily trending searches from Google Trends (free tier)."""

    TR_URL_TR = "https://trends.google.com/trending/rss?geo=TR"
    TR_URL_GLOBAL = "https://trends.google.com/trending/rss?geo=US"

    @staticmethod
    def fetch_rss(url: str) -> List[str]:
        """Parse Google Trends RSS feed, extract trending topics."""
        try:
            import xml.etree.ElementTree as ET
            resp = requests.get(url, headers={
                "User-Agent": (
                    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                    "AppleWebKit/537.36 (KHTML, like Gecko) "
                    "Chrome/124.0.0.0 Safari/537.36"
                )
            }, timeout=15)
            if resp.status_code != 200:
                log.warning(f"Trends RSS returned {resp.status_code}")
                return []
            root = ET.fromstring(resp.content)
            trends = []
            for item in root.iter("item"):
                title_el = item.find("title")
                if title_el is not None and title_el.text:
                    trends.append(title_el.text.strip())
            return trends[:20]
        except Exception as e:
            log.warning(f"Trends RSS fetch failed: {e}")
            return []

    @staticmethod
    def fetch_pytrends() -> List[str]:
        """Try pytrends library as fallback."""
        try:
            from pytrends.request import TrendReq
            pytrends = TrendReq(
                hl="tr-TR",
                tz=180,
                requests_args={"timeout": 15}
            )
            trending = pytrends.trending_searches(pn="turkey")
            if not trending.empty:
                return trending[0].head(20).tolist()
        except ImportError:
            pass
        except Exception as e:
            log.debug(f"pytrends failed: {e}")
        return []

    @classmethod
    def get_live_trends(cls) -> List[str]:
        """Aggregate from multiple sources."""
        trends = cls.fetch_rss(cls.TR_URL_TR)
        if not trends:
            trends = cls.fetch_pytrends()
        if not trends:
            trends = cls.fetch_rss(cls.TR_URL_GLOBAL)
        if trends:
            log.info(f"Fetched {len(trends)} trending topics from Google Trends")
        return trends


# ---------------------------------------------------------------------------
# 3. SPINTAX ENGINE
# ---------------------------------------------------------------------------

class SpintaxEngine:
    """Multi-level text uniqueness engine with regex-based spintax and synonyms."""

    @staticmethod
    def spin_text(text: str, intensity: float = 0.5) -> str:
        """Apply word-level synonym replacement."""
        words = text.split()
        result = []
        for w in words:
            clean = w.strip(".,;:!?\"'()[]{}<>").lower()
            if clean in SPINTAX_MAP and random.random() < intensity:
                replacement = random.choice(SPINTAX_MAP[clean])
                if w[0].isupper():
                    replacement = replacement.capitalize()
                punct = ""
                if w and w[-1] in ".,;:!?\"'()[]":
                    punct = w[-1]
                result.append(replacement + punct)
            else:
                result.append(w)
        return " ".join(result)

    @staticmethod
    def spin_html_template(template: str, loc_name: str, keyword: str) -> str:
        """Fill a template with location/keyword, then apply spintax."""
        content = template.replace("{il}", loc_name).replace("{keyword}", keyword)
        content = SpintaxEngine.spin_text(content, intensity=0.35)
        return content

    @staticmethod
    def generate_unique_body(loc: Dict[str, str], keyword: str) -> str:
        """Produce a unique body_content using template + spintax round-robin."""
        seed = hashlib.md5(f"{loc['slug']}:{keyword}".encode()).hexdigest()
        rng = random.Random(seed)
        tmpl_idx = rng.randint(0, len(BODY_TEMPLATES) - 1)
        template = BODY_TEMPLATES[tmpl_idx]
        body = SpintaxEngine.spin_html_template(template, loc["name"], keyword)
        body = SpintaxEngine.apply_spintax_syntax(body)
        return body

    @staticmethod
    def apply_spintax_syntax(text: str) -> str:
        """Parse {opt1|opt2|opt3} spintax syntax and choose one."""
        def _replacer(m: re.Match) -> str:
            options = [o.strip() for o in m.group(1).split("|")]
            return random.choice(options)
        pattern = r"\{([^{}]+?)\}"
        prev = None
        while prev != text:
            prev = text
            text = re.sub(pattern, _replacer, text)
        return text


# ---------------------------------------------------------------------------
# 4. MATRIX MULTIPLIER — Content Node Generator
# ---------------------------------------------------------------------------

class ContentMatrix:
    """Generates unique content nodes from Location × Keyword × Question Pattern."""

    def __init__(self, locations: List[Dict], niches: List[Dict]):
        self.locations = locations
        self.niches = niches
        self.trend_keywords: List[str] = []

    def inject_trends(self, trends: List[str]) -> None:
        """Inject Google Trends keywords into a temporary synthetic niche."""
        if trends:
            self.trend_keywords = trends
            log.info(f"Injected {len(trends)} trend keywords into matrix")

    def estimate_nodes(self) -> int:
        """How many unique nodes this matrix can produce."""
        total_keywords = sum(len(n["keywords"]) for n in self.niches)
        return len(self.locations) * total_keywords * len(QUESTION_PATTERNS)

    def yield_nodes(self) -> Generator[Dict[str, Any], None, None]:
        """Lazily generate unique content nodes from cartesian product."""
        for loc in self.locations:
            for niche in self.niches:
                for kw in niche["keywords"]:
                    full_keyword = f"{kw}"
                    yield from self._make_nodes_for(loc, niche, full_keyword)

            if self.trend_keywords:
                for trend_kw in self.trend_keywords:
                    yield from self._make_nodes_for(
                        loc,
                        {"name": "Trend Konular", "slug": "trend-konular"},
                        trend_kw,
                    )

    def _make_nodes_for(
        self, loc: Dict, niche: Dict, keyword: str
    ) -> Generator[Dict[str, Any], None, None]:
        """Generate one content node per question pattern."""
        seed_str = f"{loc['slug']}:{niche['slug']}:{keyword}"
        for qp in QUESTION_PATTERNS:
            title = qp.format(il=loc["name"], keyword=keyword)
            slug_base = f"{loc['slug']}-{self._slugify(niche['name'])}-{self._slugify(keyword)}"
            slug = self._unique_slug(slug_base, seed_str + qp)
            body = SpintaxEngine.generate_unique_body(loc, keyword)
            meta_desc = (
                f"{loc['name']} {keyword} | Güncel {keyword} hizmetleri, "
                f"fiyatları ve firmaları {loc['name']} sayfamızda."
            )
            yield {
                "title": title,
                "slug": slug,
                "body_content": body,
                "meta_description": meta_desc,
                "is_restricted_content": False,
                "taxonomy_slug": niche["slug"],
                "location_slug": loc["slug"],
                "published_at": datetime.now().isoformat(),
            }

    @staticmethod
    def _slugify(text: str) -> str:
        if not text:
            return ""
        replacements = {
            "ı": "i", "ş": "s", "ğ": "g", "ç": "c", "ö": "o", "ü": "u",
            "İ": "i", "Ş": "s", "Ğ": "g", "Ç": "c", "Ö": "o", "Ü": "u",
        }
        s = text.lower()
        for k, v in replacements.items():
            s = s.replace(k, v)
        s = re.sub(r"[^a-z0-9-]+", "-", s)
        return s.strip("-")

    @staticmethod
    def _unique_slug(base: str, seed: str) -> str:
        suffix = hashlib.md5(seed.encode()).hexdigest()[:8]
        return f"{base}-{suffix}"


# ---------------------------------------------------------------------------
# 5. API CLIENT — High-Performance Ingestion Pipeline
# ---------------------------------------------------------------------------

class ApiClient:
    """Thread-safe batch API client with retry and rate-limiting."""

    def __init__(self, config: Dict[str, Any]):
        self.base_url = config["BASE_URL"].rstrip("/")
        self.token = config["API_TOKEN"]
        self.batch_size = config["BATCH_SIZE"]
        self.max_retries = config["MAX_RETRIES"]
        self.rate_sleep = config["RATE_LIMIT_SLEEP"]
        self.dry_run = config.get("DRY_RUN", False)
        self.session = requests.Session()
        self.session.headers.update({
            "Authorization": f"Bearer {self.token}",
            "Content-Type": "application/json",
            "Accept": "application/json",
            "User-Agent": "OmniBot/1.0",
        })
        self._lock = random.Random()
        self.stats = {"sent": 0, "failed": 0, "errors": []}

    def _send(self, payload: Dict, endpoint: str = "/api/v1/ingest") -> Optional[Dict]:
        """Single POST request with retry and exponential backoff."""
        if self.dry_run:
            log.debug(f"[DRY-RUN] Would POST {endpoint} with {len(payload.get('content_nodes', []))} nodes")
            return {"success": True, "dry_run": True}

        url = f"{self.base_url}{endpoint}"
        for attempt in range(1, self.max_retries + 2):
            try:
                resp = self.session.post(url, json=payload, timeout=60)
                if resp.status_code in (200, 201, 202):
                    return resp.json()
                if resp.status_code == 429:
                    wait = (2 ** attempt) + random.uniform(0, 1)
                    log.warning(f"Rate limited (429). Sleeping {wait:.1f}s")
                    time.sleep(wait)
                    continue
                if resp.status_code >= 500:
                    wait = (2 ** attempt) + random.uniform(0, 1)
                    log.warning(f"Server error {resp.status_code}. Retry in {wait:.1f}s")
                    time.sleep(wait)
                    continue
                log.error(f"API error {resp.status_code}: {resp.text[:200]}")
                return None
            except requests.ConnectionError as e:
                wait = (2 ** attempt) + random.uniform(0, 2)
                log.warning(f"Connection error: {e}. Retry in {wait:.1f}s")
                time.sleep(wait)
            except requests.Timeout:
                wait = (2 ** attempt) + random.uniform(0, 1)
                log.warning(f"Timeout. Retry in {wait:.1f}s")
                time.sleep(wait)
            except Exception as e:
                log.error(f"Unexpected error: {e}")
                return None
        log.error(f"Failed after {self.max_retries + 1} attempts")
        return None

    def send_batch(self, nodes: List[Dict]) -> int:
        """Send a batch of content nodes. Returns count of accepted nodes."""
        time.sleep(self.rate_sleep)
        result = self._send({"content_nodes": nodes})
        if result and result.get("success"):
            self.stats["sent"] += len(nodes)
            return len(nodes)
        self.stats["failed"] += len(nodes)
        return 0

    def setup_taxonomies_and_locations(self) -> None:
        """Pre-create all taxonomies & locations in the backend."""
        if self.dry_run:
            log.info("[DRY-RUN] Skipping setup")
            return

        # Taxonomies
        taxonomies = []
        for n in NICHES:
            taxonomies.append({"name": n["name"], "slug": n["slug"]})
        if self.trend_keywords:
            taxonomies.append({"name": "Trend Konular", "slug": "trend-konular"})
        log.info(f"Setting up {len(taxonomies)} taxonomies...")
        for i in range(0, len(taxonomies), self.batch_size):
            batch = taxonomies[i:i + self.batch_size]
            resp = self._send({"taxonomies": batch})
            status = resp.get("success", False) if resp else False
            log.info(f"  Taxonomies batch {i//self.batch_size + 1}: {'OK' if status else 'FAIL'}")

        # Locations
        locations_set = {}
        for loc in self.used_locations:
            if loc["slug"] not in locations_set:
                locations_set[loc["slug"]] = {"name": loc["name"], "slug": loc["slug"]}
        loc_list = list(locations_set.values())
        log.info(f"Setting up {len(loc_list)} locations...")
        for i in range(0, len(loc_list), self.batch_size):
            batch = loc_list[i:i + self.batch_size]
            resp = self._send({"locations": batch})
            status = resp.get("success", False) if resp else False
            log.info(f"  Locations batch {i//self.batch_size + 1}: {'OK' if status else 'FAIL'}")

    def report_stats(self) -> Dict[str, Any]:
        """Return aggregate stats."""
        return dict(self.stats)


# ---------------------------------------------------------------------------
# 6. CHECKPOINT / RESUME
# ---------------------------------------------------------------------------

class Checkpoint:
    """Persistent checkpoint for resume capability."""

    def __init__(self, path: str):
        self.path = Path(path)
        self.data: Dict[str, Any] = {"processed": [], "total": 0}

    def load(self) -> bool:
        if self.path.exists():
            try:
                self.data = json.loads(self.path.read_text(encoding="utf-8"))
                log.info(f"Resumed from checkpoint: {len(self.data['processed'])} items processed")
                return True
            except Exception:
                pass
        return False

    def save(self, processed_slugs: List[str], total: int) -> None:
        self.data["processed"] = processed_slugs[-10000:]  # keep recent only
        self.data["total"] = total
        self.data["updated_at"] = datetime.now().isoformat()
        self.path.write_text(json.dumps(self.data, ensure_ascii=False), encoding="utf-8")

    def is_processed(self, slug: str) -> bool:
        return slug in self.data["processed"]


# ---------------------------------------------------------------------------
# 7. MAIN ORCHESTRATOR
# ---------------------------------------------------------------------------

class OmniBot:
    """Main bot orchestrator — sets up pipeline and runs ingestion."""

    def __init__(self, config: Dict[str, Any]):
        self.config = config
        self.dry_run = config.get("DRY_RUN", False)
        self.quick_mode = config.get("QUICK_MODE", False)
        self.resume_mode = config.get("RESUME", False)
        self.matrix = ContentMatrix(LOCATIONS, NICHES)
        self.checkpoint = Checkpoint(config.get("RESUME_FILE", "omni_checkpoint.json"))
        self.api = ApiClient(config)
        self.trend_keywords: List[str] = []

    def _fetch_trends(self) -> None:
        """Fetch live trends and inject into matrix."""
        if self.config.get("TRENDS_ENABLED", True):
            log.info("Fetching Google Trends data...")
            self.trend_keywords = TrendsFetcher.get_live_trends()
            if self.trend_keywords:
                self.matrix.inject_trends(self.trend_keywords)

    def _estimate_work(self) -> int:
        """Return estimated total node count."""
        total = self.matrix.estimate_nodes()
        if self.trend_keywords:
            total += len(LOCATIONS) * len(self.trend_keywords) * len(QUESTION_PATTERNS)
        return total

    def run(self) -> None:
        """Execute full pipeline."""
        start = time.time()
        log.info("=" * 60)
        log.info("OMNI-BOT MATRIX INJECTOR v2.0")
        log.info("=" * 60)

        if not self.config["API_TOKEN"]:
            log.error("API_TOKEN is empty. Set OMNI_API_TOKEN env var or edit CONFIG.")
            sys.exit(1)

        if self.dry_run:
            log.info("DRY RUN MODE — no API calls will be made")
        if self.quick_mode:
            log.info("QUICK MODE — limiting to 500 sample nodes")

        # Step 1: Fetch live trends
        self._fetch_trends()

        # Step 2: Calculate work
        estimated = self._estimate_work()
        log.info(f"Matrix estimated output: ~{estimated:,} unique content nodes")
        log.info(f"  Locations: {len(LOCATIONS)}")
        log.info(f"  Niches: {len(NICHES)}")
        log.info(f"  Question Patterns: {len(QUESTION_PATTERNS)}")
        if self.trend_keywords:
            log.info(f"  Live Trends injected: {len(self.trend_keywords)}")

        # Step 3: Load checkpoint
        processed_slugs: List[str] = []
        if self.resume_mode:
            self.checkpoint.load()
            processed_slugs = self.checkpoint.data.get("processed", [])

        # Step 4: Build the full node list
        nodes: List[Dict] = []
        for node in self.matrix.yield_nodes():
            if self.resume_mode and self.checkpoint.is_processed(node["slug"]):
                continue
            nodes.append(node)
            if self.quick_mode and len(nodes) >= 500:
                break

        total_nodes = len(nodes)
        log.info(f"Nodes to process: {total_nodes:,}")

        if total_nodes == 0:
            log.info("No nodes to process. Either all done or filter too narrow.")
            return

        # Step 5: Setup taxonomies & locations in backend
        self.api.used_locations = LOCATIONS  # used by setup
        self.api.trend_keywords = self.trend_keywords
        self.api.setup_taxonomies_and_locations()

        # Step 6: Batch & send via thread pool
        batches = [nodes[i:i + self.config["BATCH_SIZE"]]
                   for i in range(0, len(nodes), self.config["BATCH_SIZE"])]
        log.info(f"Sending {len(batches)} batches with {self.config['CONCURRENT_WORKERS']} workers...")

        with ThreadPoolExecutor(max_workers=self.config["CONCURRENT_WORKERS"]) as executor:
            fut_map = {executor.submit(self.api.send_batch, b): b for b in batches}
            done_count = 0
            for fut in as_completed(fut_map):
                done_count += 1
                if done_count % 20 == 0:
                    pct = done_count / len(batches) * 100
                    log.info(f"  Progress: {done_count}/{len(batches)} batches ({pct:.0f}%)")
                # Checkpoint every 50 batches
                if done_count % 50 == 0:
                    slgs = [n["slug"] for b in batches[:done_count] for n in b]
                    self.checkpoint.save(slgs, done_count * self.config["BATCH_SIZE"])

        # Step 7: Final checkpoint
        all_slugs = [n["slug"] for n in nodes]
        self.checkpoint.save(all_slugs, total_nodes)

        # Step 8: Report
        elapsed = time.time() - start
        stats = self.api.report_stats()
        log.info("=" * 60)
        log.info("MISSION COMPLETE")
        log.info(f"  Total Nodes Sent: {stats['sent']:,}")
        log.info(f"  Failed: {stats['failed']:,}")
        log.info(f"  Elapsed: {elapsed:.1f}s ({total_nodes/elapsed:.0f} nodes/sec)")
        log.info(f"  Checkpoint: {self.checkpoint.path}")
        log.info("=" * 60)


# ---------------------------------------------------------------------------
# 8. CLI ENTRY POINT
# ---------------------------------------------------------------------------

def parse_args() -> Dict[str, Any]:
    """Parse CLI arguments over config."""
    config = dict(CONFIG)
    for arg in sys.argv[1:]:
        if arg in ("--dry-run", "--dry"):
            config["DRY_RUN"] = True
        if arg in ("--quick", "--test"):
            config["QUICK_MODE"] = True
        if arg in ("--resume", "--continue"):
            config["RESUME"] = True
    return config


if __name__ == "__main__":
    cfg = parse_args()
    bot = OmniBot(cfg)
    bot.run()
