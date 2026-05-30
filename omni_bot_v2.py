"""
omni_bot_v2.py — GLOBAL SEO DOMINATOR v2.1 ∞ INFINITY ENGINE
=============================================================
Multi-Lingual / Multi-Region Programmatic SEO Matrix Injector.
Targets: AR (MENA), RU (Russia), EN (US/UK), TR (Türkiye).

Powered by INFINITE SEMANTIC WORD ENGINE:
  - Google Suggest A-Z alphabet discovery (all 4 languages)
  - Dynamic niche matrix loaded from JSON (niche_matrix_{locale}.json)
  - Automatic fallback to offline A-Z vocabulary when API is blocked
  - 3x+ expanded question patterns for organic ranking variety

Usage:
    python omni_bot_v2.py                                # All locales (auto-builds matrices)
    python omni_bot_v2.py --seed-matrix                   # Force rebuild niche matrices via Google Suggest
    python omni_bot_v2.py --skip-seed                     # Skip auto-build, use bootstrap only
    python omni_bot_v2.py --locale TR,EN                  # Specific locales
    python omni_bot_v2.py --dry-run                       # Preview only
    python omni_bot_v2.py --quick --locale AR             # Quick test Arabic
    python omni_bot_v2.py --resume                        # Resume from checkpoint
"""

from dotenv import load_dotenv

load_dotenv()

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

from niche_matrix_builder import (
    GoogleSuggestClient,
    NicheMatrixBuilder,
    OfflineVocabularyGenerator,
    matrix_seeder as run_matrix_seeder,
)

try:
    from slugify import slugify as _slugify_lib
    HAS_SLUGIFY = True
except ImportError:
    HAS_SLUGIFY = False

try:
    import unidecode as _unidecode_lib
    HAS_UNIDECODE = True
except ImportError:
    HAS_UNIDECODE = False

import requests

# ---------------------------------------------------------------------------
# CONFIG
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
    "RESUME_FILE": "omni_v2_checkpoint.json",
    "TRENDS_ENABLED": os.getenv("OMNI_TRENDS", "true").lower() == "true",
    "ACTIVE_LOCALES": [],  # populated from --locale or defaults to all
}

# ---------------------------------------------------------------------------
# LOGGING
# ---------------------------------------------------------------------------
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler("omni_bot_v2.log", encoding="utf-8"),
    ],
)
log = logging.getLogger("omni_bot_v2")

# ===========================================================================
# 1. LOCALE-AWARE MATRIX DICTIONARY
# ===========================================================================
# Each locale has its own locations, niches, question_patterns,
# body_templates, and spintax_map. The structure is self-contained
# and independently expandable.

LOCALE_MATRIX: Dict[str, Dict[str, Any]] = {}

# ---------------------------------------------------------------------------
# 1a. TR — Türkiye (Turkish)
# ---------------------------------------------------------------------------
LOCALE_MATRIX["TR"] = {
    "code": "TR",
    "name": "Türkiye",
    "language": "tr",
    "geo": "TR",
    "trends_geo": "TR",
    "pytrends_hl": "tr-TR",
    "pytrends_tz": 180,
    "pytrends_pn": "turkey",
    "locations": [
        {"name": "Adana", "slug": "adana"},
        {"name": "Adıyaman", "slug": "adiyaman"},
        {"name": "Afyonkarahisar", "slug": "afyonkarahisar"},
        {"name": "Ağrı", "slug": "agri"},
        {"name": "Aksaray", "slug": "aksaray"},
        {"name": "Amasya", "slug": "amasya"},
        {"name": "Ankara", "slug": "ankara"},
        {"name": "Antalya", "slug": "antalya"},
        {"name": "Ardahan", "slug": "ardahan"},
        {"name": "Artvin", "slug": "artvin"},
        {"name": "Aydın", "slug": "aydin"},
        {"name": "Balıkesir", "slug": "balikesir"},
        {"name": "Bartın", "slug": "bartin"},
        {"name": "Batman", "slug": "batman"},
        {"name": "Bayburt", "slug": "bayburt"},
        {"name": "Bilecik", "slug": "bilecik"},
        {"name": "Bingöl", "slug": "bingol"},
        {"name": "Bitlis", "slug": "bitlis"},
        {"name": "Bolu", "slug": "bolu"},
        {"name": "Burdur", "slug": "burdur"},
        {"name": "Bursa", "slug": "bursa"},
        {"name": "Çanakkale", "slug": "canakkale"},
        {"name": "Çankırı", "slug": "cankiri"},
        {"name": "Çorum", "slug": "corum"},
        {"name": "Denizli", "slug": "denizli"},
        {"name": "Diyarbakır", "slug": "diyarbakir"},
        {"name": "Düzce", "slug": "duzce"},
        {"name": "Edirne", "slug": "edirne"},
        {"name": "Elazığ", "slug": "elazig"},
        {"name": "Erzincan", "slug": "erzincan"},
        {"name": "Erzurum", "slug": "erzurum"},
        {"name": "Eskişehir", "slug": "eskisehir"},
        {"name": "Gaziantep", "slug": "gaziantep"},
        {"name": "Giresun", "slug": "giresun"},
        {"name": "Gümüşhane", "slug": "gumushane"},
        {"name": "Hakkâri", "slug": "hakkari"},
        {"name": "Hatay", "slug": "hatay"},
        {"name": "Iğdır", "slug": "igdir"},
        {"name": "Isparta", "slug": "isparta"},
        {"name": "İstanbul", "slug": "istanbul"},
        {"name": "İzmir", "slug": "izmir"},
        {"name": "Kahramanmaraş", "slug": "kahramanmaras"},
        {"name": "Karabük", "slug": "karabuk"},
        {"name": "Karaman", "slug": "karaman"},
        {"name": "Kars", "slug": "kars"},
        {"name": "Kastamonu", "slug": "kastamonu"},
        {"name": "Kayseri", "slug": "kayseri"},
        {"name": "Kilis", "slug": "kilis"},
        {"name": "Kırıkkale", "slug": "kirikkale"},
        {"name": "Kırklareli", "slug": "kirklareli"},
        {"name": "Kırşehir", "slug": "kirsehir"},
        {"name": "Kocaeli", "slug": "kocaeli"},
        {"name": "Konya", "slug": "konya"},
        {"name": "Kütahya", "slug": "kutahya"},
        {"name": "Malatya", "slug": "malatya"},
        {"name": "Manisa", "slug": "manisa"},
        {"name": "Mardin", "slug": "mardin"},
        {"name": "Mersin", "slug": "mersin"},
        {"name": "Muğla", "slug": "mugla"},
        {"name": "Muş", "slug": "mus"},
        {"name": "Nevşehir", "slug": "nevsehir"},
        {"name": "Niğde", "slug": "nigde"},
        {"name": "Ordu", "slug": "ordu"},
        {"name": "Osmaniye", "slug": "osmaniye"},
        {"name": "Rize", "slug": "rize"},
        {"name": "Sakarya", "slug": "sakarya"},
        {"name": "Samsun", "slug": "samsun"},
        {"name": "Şanlıurfa", "slug": "sanliurfa"},
        {"name": "Siirt", "slug": "siirt"},
        {"name": "Sinop", "slug": "sinop"},
        {"name": "Şırnak", "slug": "sirnak"},
        {"name": "Sivas", "slug": "sivas"},
        {"name": "Tekirdağ", "slug": "tekirdag"},
        {"name": "Tokat", "slug": "tokat"},
        {"name": "Trabzon", "slug": "trabzon"},
        {"name": "Tunceli", "slug": "tunceli"},
        {"name": "Uşak", "slug": "usak"},
        {"name": "Van", "slug": "van"},
        {"name": "Yalova", "slug": "yalova"},
        {"name": "Yozgat", "slug": "yozgat"},
        {"name": "Zonguldak", "slug": "zonguldak"},
    ],
    "niches": [],  # Populated dynamically from niche_matrix_TR.json
    "question_patterns": [
        "{keyword} {il}",
        "{keyword} {il} hizmeti",
        "{keyword} {il} fiyatları 2026",
        "En iyi {keyword} {il}",
        "En uygun {keyword} {il}",
        "En kaliteli {keyword} {il}",
        "En ucuz {keyword} {il}",
        "Profesyonel {keyword} {il}",
        "Uzman {keyword} {il}",
        "Güvenilir {keyword} {il}",
        "Yerel {keyword} {il}",
        "{keyword} {il} telefon numarası",
        "{keyword} {il} adres",
        "{keyword} {il} iletişim",
        "{keyword} {il} randevu",
        "{keyword} {il} online randevu",
        "Acil {keyword} {il}",
        "7/24 {keyword} {il}",
        "{keyword} {il} nerede",
        "{keyword} {il} nerede bulabilirim",
        "{keyword} {il} nereden alınır",
        "{keyword} {il} müşteri yorumları",
        "{keyword} {il} yorumlar",
        "{keyword} {il} tavsiye",
        "{keyword} {il} fiyat listesi",
        "{keyword} {il} ücretleri",
        "{keyword} {il} maliyeti",
        "{keyword} {il} en yakın",
        "{keyword} {il} firmaları",
        "{keyword} {il} şirketleri",
        "{keyword} {il} ustaları",
        "{keyword} {il} servisleri",
        "{keyword} {il} çalışma saatleri",
        "{keyword} {il} ücretsiz keşif",
        "{keyword} {il} ücretsiz danışmanlık",
        "Ücretsiz {keyword} {il}",
        "{keyword} {il} indirim",
        "{keyword} {il} kampanya",
        "{keyword} {il} fırsat",
        "{keyword} {il} hakkında",
        "{keyword} {il} rehberi",
        "{keyword} {il} listesi",
        "{keyword} {il} sıralaması",
        "{keyword} {il} puanları",
        "{keyword} {il} değerlendirme",
        "{keyword} {il} karşılaştırma",
        "{keyword} {il} hangisi daha iyi",
        "En iyi {keyword} {il} hangisi",
    ],
    "body_templates": [
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
            "isimlerini bir araya getirdik.</p>"
            "<p>{keyword} fiyatları, kampanyaları ve daha "
            "fazlası için web sitemizi ziyaret edebilir, {il} halkına "
            "özel avantajlardan yararlanabilirsiniz.</p>"
        ),
    ],
    "spintax_map": {
        "hizmet": ["servis", "destek", "yardım", "çözüm", "bakım"],
        "firma": ["şirket", "kuruluş", "işletme", "kurum", "marka"],
        "profesyonel": ["uzman", "deneyimli", "tecrübeli", "kalifiye", "yetenekli"],
        "kaliteli": ["başarılı", "güvenilir", "nitelikli", "seçkin", "premium"],
        "en iyi": ["en kaliteli", "en başarılı", "en güvenilir", "en uygun", "en seçkin"],
        "uygun fiyat": ["ekonomik", "hesaplı", "bütçe dostu", "avantajlı", "makul"],
        "güvenilir": ["itibarlı", "sağlam", "emin", "garantili", "referanslı"],
        "hızlı": ["pratik", "çabuk", "süratli", "acil", "ivedi"],
        "kolay": ["basit", "rahat", "pratik", "kullanışlı", "konforlu"],
        "detaylı": ["kapsamlı", "ayrıntılı", "etraflı", "derinlemesine", "tüm yönleriyle"],
        "bulabilirsiniz": ["erişebilirsiniz", "ulaşabilirsiniz", "edinebilirsiniz", "görebilirsiniz", "inceleyebilirsiniz"],
        "memnuniyet": ["tatmin", "mutluluk", "hoşnutluk", "beğeni", "takdir"],
        "ihtiyaç": ["gereksinim", "talep", "beklenti", "istek", "gerek"],
        "sürekli": ["düzenli", "devamlı", "kesintisiz", "mütemadiyen", "periyodik"],
        "yenilikçi": ["modern", "çağdaş", "ileri görüşlü", "vizyoner", "inovasyon"],
        "imkan": ["fırsat", "olanak", "seçenek", "alternatif", "opsiyon"],
        "avantaj": ["fayda", "kazanç", "çıkar", "yarar", "artı"],
    },
}

# ---------------------------------------------------------------------------
# 1b. EN — English (USA / UK / Canada / Australia / NZ / India / SG / ZA / IE)
# ---------------------------------------------------------------------------
LOCALE_MATRIX["EN"] = {
    "code": "EN",
    "name": "English",
    "language": "en",
    "geo": "US",
    "trends_geo": "US",
    "pytrends_hl": "en-US",
    "pytrends_tz": 360,
    "pytrends_pn": "united_states",
    "locations": [
        # USA
        {"name": "New York", "slug": "new-york"},
        {"name": "Los Angeles", "slug": "los-angeles"},
        {"name": "Chicago", "slug": "chicago"},
        {"name": "Houston", "slug": "houston"},
        {"name": "Phoenix", "slug": "phoenix"},
        {"name": "Philadelphia", "slug": "philadelphia"},
        {"name": "San Antonio", "slug": "san-antonio"},
        {"name": "San Diego", "slug": "san-diego"},
        {"name": "Dallas", "slug": "dallas"},
        {"name": "Austin", "slug": "austin"},
        {"name": "Jacksonville", "slug": "jacksonville"},
        {"name": "Fort Worth", "slug": "fort-worth"},
        {"name": "Columbus", "slug": "columbus"},
        {"name": "Charlotte", "slug": "charlotte"},
        {"name": "Indianapolis", "slug": "indianapolis"},
        {"name": "San Francisco", "slug": "san-francisco"},
        {"name": "Seattle", "slug": "seattle"},
        {"name": "Denver", "slug": "denver"},
        {"name": "Nashville", "slug": "nashville"},
        {"name": "Oklahoma City", "slug": "oklahoma-city"},
        {"name": "El Paso", "slug": "el-paso"},
        {"name": "Washington", "slug": "washington-dc"},
        {"name": "Boston", "slug": "boston"},
        {"name": "Las Vegas", "slug": "las-vegas"},
        {"name": "Portland", "slug": "portland"},
        {"name": "Memphis", "slug": "memphis"},
        {"name": "Louisville", "slug": "louisville"},
        {"name": "Baltimore", "slug": "baltimore"},
        {"name": "Milwaukee", "slug": "milwaukee"},
        {"name": "Albuquerque", "slug": "albuquerque"},
        {"name": "Tucson", "slug": "tucson"},
        {"name": "Fresno", "slug": "fresno"},
        {"name": "Sacramento", "slug": "sacramento"},
        {"name": "Mesa", "slug": "mesa"},
        {"name": "Atlanta", "slug": "atlanta"},
        {"name": "Kansas City", "slug": "kansas-city"},
        {"name": "Omaha", "slug": "omaha"},
        {"name": "Colorado Springs", "slug": "colorado-springs"},
        {"name": "Raleigh", "slug": "raleigh"},
        {"name": "Long Beach", "slug": "long-beach"},
        {"name": "Virginia Beach", "slug": "virginia-beach"},
        {"name": "Miami", "slug": "miami"},
        {"name": "Oakland", "slug": "oakland"},
        {"name": "Minneapolis", "slug": "minneapolis"},
        {"name": "Tampa", "slug": "tampa"},
        {"name": "Tulsa", "slug": "tulsa"},
        {"name": "Arlington", "slug": "arlington"},
        {"name": "New Orleans", "slug": "new-orleans"},
        {"name": "Cleveland", "slug": "cleveland"},
        {"name": "Honolulu", "slug": "honolulu"},
        {"name": "Anaheim", "slug": "anaheim"},
        # UK
        {"name": "London", "slug": "london"},
        {"name": "Manchester", "slug": "manchester"},
        {"name": "Birmingham", "slug": "birmingham"},
        {"name": "Liverpool", "slug": "liverpool"},
        {"name": "Leeds", "slug": "leeds"},
        {"name": "Glasgow", "slug": "glasgow"},
        {"name": "Sheffield", "slug": "sheffield"},
        {"name": "Edinburgh", "slug": "edinburgh"},
        {"name": "Bristol", "slug": "bristol"},
        {"name": "Nottingham", "slug": "nottingham"},
        {"name": "Leicester", "slug": "leicester"},
        {"name": "Newcastle", "slug": "newcastle"},
        {"name": "Southampton", "slug": "southampton"},
        {"name": "Portsmouth", "slug": "portsmouth"},
        # Canada
        {"name": "Toronto", "slug": "toronto"},
        {"name": "Vancouver", "slug": "vancouver"},
        {"name": "Montreal", "slug": "montreal"},
        {"name": "Calgary", "slug": "calgary"},
        {"name": "Edmonton", "slug": "edmonton"},
        {"name": "Ottawa", "slug": "ottawa"},
        {"name": "Winnipeg", "slug": "winnipeg"},
        {"name": "Quebec City", "slug": "quebec-city"},
        {"name": "Hamilton", "slug": "hamilton"},
        {"name": "Halifax", "slug": "halifax"},
        # Australia
        {"name": "Sydney", "slug": "sydney"},
        {"name": "Melbourne", "slug": "melbourne"},
        {"name": "Brisbane", "slug": "brisbane"},
        {"name": "Perth", "slug": "perth"},
        {"name": "Adelaide", "slug": "adelaide"},
        {"name": "Gold Coast", "slug": "gold-coast"},
        {"name": "Canberra", "slug": "canberra"},
        # New Zealand
        {"name": "Auckland", "slug": "auckland"},
        {"name": "Wellington", "slug": "wellington"},
        {"name": "Christchurch", "slug": "christchurch"},
        # Ireland
        {"name": "Dublin", "slug": "dublin"},
        {"name": "Cork", "slug": "cork"},
        {"name": "Galway", "slug": "galway"},
        # South Africa
        {"name": "Johannesburg", "slug": "johannesburg"},
        {"name": "Cape Town", "slug": "cape-town"},
        {"name": "Durban", "slug": "durban"},
        {"name": "Pretoria", "slug": "pretoria"},
        # India (English-speaking major cities)
        {"name": "Mumbai", "slug": "mumbai"},
        {"name": "Delhi", "slug": "delhi"},
        {"name": "Bangalore", "slug": "bangalore"},
        {"name": "Hyderabad", "slug": "hyderabad"},
        {"name": "Chennai", "slug": "chennai"},
        {"name": "Kolkata", "slug": "kolkata"},
        {"name": "Pune", "slug": "pune"},
        {"name": "Ahmedabad", "slug": "ahmedabad"},
        # Singapore
        {"name": "Singapore", "slug": "singapore"},
        # Nigeria
        {"name": "Lagos", "slug": "lagos"},
        {"name": "Abuja", "slug": "abuja"},
    ],
    "niches": [],  # Populated dynamically from niche_matrix_EN.json
    "question_patterns": [
        "{keyword} in {il}",
        "Best {keyword} in {il}",
        "Cheap {keyword} in {il}",
        "Top rated {keyword} in {il}",
        "Affordable {keyword} in {il}",
        "Professional {keyword} in {il}",
        "Licensed {keyword} in {il}",
        "Insured {keyword} in {il}",
        "Reliable {keyword} in {il}",
        "Experienced {keyword} in {il}",
        "Certified {keyword} in {il}",
        "Local {keyword} in {il}",
        "Best {keyword} near {il}",
        "Where to find {keyword} in {il}",
        "Where to buy {keyword} in {il}",
        "{keyword} near {il}",
        "Best {keyword} near {il}",
        "Cheap {keyword} near {il}",
        "{keyword} {il} near me",
        "Top {keyword} near {il}",
        "{keyword} {il} prices 2026",
        "{keyword} {il} cost 2026",
        "{keyword} {il} rates 2026",
        "How much does {keyword} cost in {il}",
        "{keyword} {il} price list",
        "Average cost of {keyword} in {il}",
        "Cheapest {keyword} in {il}",
        "Best rated {keyword} in {il}",
        "Top {keyword} in {il}",
        "{keyword} {il} reviews",
        "Top rated {keyword} {il}",
        "{keyword} {il} customer reviews",
        "{keyword} {il} ratings",
        "Emergency {keyword} in {il}",
        "24 hour {keyword} {il}",
        "{keyword} {il} same day service",
        "{keyword} {il} urgent",
        "{keyword} {il} 24/7",
        "{keyword} {il} phone number",
        "{keyword} {il} address",
        "{keyword} {il} contact",
        "{keyword} {il} appointment",
        "Book {keyword} in {il}",
        "Schedule {keyword} {il}",
        "{keyword} company {il}",
        "{keyword} service {il}",
        "{keyword} providers {il}",
        "{keyword} contractors {il}",
        "{keyword} specialists {il}",
        "{keyword} experts {il}",
        "{keyword} professionals {il}",
        "Free {keyword} estimate {il}",
        "{keyword} {il} free quote",
        "{keyword} {il} free consultation",
        "Compare {keyword} in {il}",
        "Quality {keyword} in {il}",
        "Trusted {keyword} {il}",
        "Recommended {keyword} in {il}",
        "Approved {keyword} {il}",
    ],
    "body_templates": [
        (
            "<p>Looking for the best <strong>{keyword}</strong> in {il}? "
            "You have come to the right place. Our comprehensive guide covers "
            "everything you need to know about {keyword} services in the "
            "{il} area, including pricing, customer reviews, and "
            "professional recommendations.</p>"
            "<p>Whether you need routine {keyword} maintenance or an "
            "emergency repair, {il} has a wide range of qualified "
            "professionals ready to help. We have researched and compiled "
            "the most up-to-date information to make your decision easier.</p>"
            "<p>Compare top {keyword} providers in {il}, read verified "
            "customer feedback, and find special offers. Our mission is to "
            "connect you with the best service at the right price.</p>"
        ),
        (
            "<p><strong>{keyword}</strong> is an essential service for "
            "homeowners and businesses in {il}. Whether you are dealing "
            "with an urgent issue or planning a routine upgrade, finding "
            "the right professional makes all the difference.</p>"
            "<p>Our platform features the most trusted {keyword} "
            "companies in {il}, each vetted for quality and reliability. "
            "We provide detailed profiles, pricing guides, and real "
            "customer testimonials to help you choose confidently.</p>"
            "<p>Stay informed about the latest {keyword} trends and "
            "pricing in {il}. Bookmark this page for future reference "
            "and never miss out on valuable service information.</p>"
        ),
        (
            "<p>Welcome to the ultimate resource for <strong>{keyword}</strong> "
            "in {il}. We understand how important it is to find a "
            "dependable service provider, and our goal is to simplify "
            "your search process completely.</p>"
            "<p>{il} residents have trusted our recommendations for "
            "years. We carefully evaluate each {keyword} provider based "
            "on credentials, customer satisfaction, and pricing "
            "transparency.</p>"
            "<p>From emergency services to scheduled maintenance, our "
            "guide covers all aspects of {keyword} in {il}. "
            "Start your search today and experience the difference.</p>"
        ),
        (
            "<p>Discover why {il} homeowners choose our recommended "
            "<strong>{keyword}</strong> professionals. With years of "
            "experience and a commitment to excellence, these providers "
            "set the standard for quality service.</p>"
            "<p>Our {keyword} guide for {il} includes everything from "
            "cost estimates and service comparisons to tips for "
            "maintaining your systems. We believe informed customers "
            "make the best decisions.</p>"
            "<p>Whether you need {keyword} for your home or business in "
            "{il}, our curated list of professionals is here to help. "
            "Get started with a free quote today.</p>"
        ),
        (
            "<p>Your search for <strong>{keyword}</strong> in {il} ends "
            "here. We have done the hard work of researching and "
            "reviewing the top service providers so you can make an "
            "informed choice with confidence.</p>"
            "<p>From pricing breakdowns to service quality ratings, our "
            "{keyword} guide covers every aspect you need to consider. "
            "{il} residents trust our platform for reliable, "
            "up-to-date information.</p>"
            "<p>Dont settle for anything less than the best {keyword} "
            "service in {il}. Explore our recommendations and find "
            "the perfect provider for your needs today.</p>"
        ),
    ],
    "spintax_map": {
        "service": ["support", "assistance", "solutions", "help", "care"],
        "company": ["provider", "business", "firm", "enterprise", "agency"],
        "professional": ["expert", "specialist", "certified", "licensed", "master"],
        "quality": ["premium", "superior", "excellent", "top-rated", "first-class"],
        "best": ["top", "finest", "leading", "premier", "outstanding"],
        "affordable": ["budget-friendly", "cost-effective", "reasonably-priced", "competitive", "economical"],
        "reliable": ["trustworthy", "dependable", "reputable", "trusted", "established"],
        "comprehensive": ["complete", "full", "extensive", "thorough", "in-depth"],
        "quick": ["fast", "rapid", "prompt", "immediate", "speedy"],
        "easy": ["simple", "convenient", "hassle-free", "straightforward", "effortless"],
        "find": ["locate", "discover", "search for", "get", "access"],
        "need": ["require", "want", "looking for", "searching for", "in need of"],
        "experienced": ["seasoned", "skilled", "knowledgeable", "proficient", "accomplished"],
        "residents": ["homeowners", "locals", "community", "citizens", "families"],
        "guide": ["resource", "directory", "reference", "handbook", "compilation"],
        "recommended": ["suggested", "preferred", "endorsed", "approved", "top-rated"],
        "pricing": ["rates", "costs", "charges", "fees", "quotes"],
        "emergency": ["urgent", "immediate", "24-hour", "after-hours", "same-day"],
        "perfect": ["ideal", "right", "best", "optimal", "suitable"],
        "review": ["assessment", "evaluation", "feedback", "testimonial", "rating"],
    },
}

# ---------------------------------------------------------------------------
# 1c. AR — Arabic (Saudi Arabia / UAE / Egypt / Levant / Maghreb / Gulf)
# ---------------------------------------------------------------------------
LOCALE_MATRIX["AR"] = {
    "code": "AR",
    "name": "العربية",
    "language": "ar",
    "geo": "SA",
    "trends_geo": "SA",
    "pytrends_hl": "ar-SA",
    "pytrends_tz": 180,
    "pytrends_pn": "saudi_arabia",
    "locations": [
        # Saudi Arabia
        {"name": "الرياض", "slug": "riyadh"},
        {"name": "جدة", "slug": "jeddah"},
        {"name": "مكة المكرمة", "slug": "makkah"},
        {"name": "المدينة المنورة", "slug": "madinah"},
        {"name": "الدمام", "slug": "dammam"},
        {"name": "الخبر", "slug": "khobar"},
        {"name": "الظهران", "slug": "dhahran"},
        {"name": "تبوك", "slug": "tabuk"},
        {"name": "بريدة", "slug": "buraidah"},
        {"name": "حائل", "slug": "hail"},
        {"name": "الهفوف", "slug": "hafuf"},
        {"name": "القطيف", "slug": "qatif"},
        {"name": "خميس مشيط", "slug": "khamis-mushait"},
        {"name": "نجران", "slug": "najran"},
        {"name": "ينبع", "slug": "yanbu"},
        {"name": "أبها", "slug": "abha"},
        {"name": "عرعر", "slug": "arar"},
        {"name": "سكاكا", "slug": "sakaka"},
        # UAE
        {"name": "دبي", "slug": "dubai"},
        {"name": "أبو ظبي", "slug": "abu-dhabi"},
        {"name": "الشارقة", "slug": "sharjah"},
        {"name": "عجمان", "slug": "ajman"},
        {"name": "رأس الخيمة", "slug": "ras-al-khaimah"},
        {"name": "الفجيرة", "slug": "fujairah"},
        {"name": "العين", "slug": "al-ain"},
        # Kuwait
        {"name": "الكويت", "slug": "kuwait-city"},
        {"name": "حولي", "slug": "hawalli"},
        {"name": "الفروانية", "slug": "farwaniya"},
        # Qatar
        {"name": "الدوحة", "slug": "doha"},
        {"name": "الوكرة", "slug": "al-wakrah"},
        {"name": "الخور", "slug": "al-khor"},
        # Oman
        {"name": "مسقط", "slug": "muscat"},
        {"name": "صلالة", "slug": "salalah"},
        {"name": "صحار", "slug": "sohar"},
        # Bahrain
        {"name": "المنامة", "slug": "manama"},
        {"name": "المحرق", "slug": "muharraq"},
        {"name": "الرفاع", "slug": "riffa"},
        # Egypt
        {"name": "القاهرة", "slug": "cairo"},
        {"name": "الإسكندرية", "slug": "alexandria"},
        {"name": "الجيزة", "slug": "giza"},
        {"name": "شرم الشيخ", "slug": "sharm-elsheikh"},
        {"name": "الأقصر", "slug": "luxor"},
        {"name": "أسوان", "slug": "aswan"},
        {"name": "بورسعيد", "slug": "port-said"},
        {"name": "طنطا", "slug": "tanta"},
        {"name": "المنصورة", "slug": "mansoura"},
        # Jordan
        {"name": "عمان", "slug": "amman"},
        {"name": "إربد", "slug": "irbid"},
        {"name": "الزرقاء", "slug": "zarqa"},
        {"name": "العقبة", "slug": "aqaba"},
        # Lebanon
        {"name": "بيروت", "slug": "beirut"},
        {"name": "طرابلس", "slug": "tripoli"},
        {"name": "صيدا", "slug": "sidon"},
        {"name": "صور", "slug": "tyre"},
        # Iraq
        {"name": "بغداد", "slug": "baghdad"},
        {"name": "البصرة", "slug": "basra"},
        {"name": "أربيل", "slug": "erbil"},
        {"name": "الموصل", "slug": "mosul"},
        {"name": "السليمانية", "slug": "sulaymaniyah"},
        # Morocco
        {"name": "الدار البيضاء", "slug": "casablanca"},
        {"name": "الرباط", "slug": "rabat"},
        {"name": "مراكش", "slug": "marrakech"},
        {"name": "فاس", "slug": "fes"},
        {"name": "طنجة", "slug": "tangier"},
        {"name": "أكادير", "slug": "agadir"},
        # Algeria
        {"name": "الجزائر", "slug": "algiers"},
        {"name": "وهران", "slug": "oran"},
        {"name": "قسنطينة", "slug": "constantine"},
        {"name": "عنابة", "slug": "annaba"},
        # Tunisia
        {"name": "تونس", "slug": "tunis"},
        {"name": "صفاقس", "slug": "sfax"},
        {"name": "سوسة", "slug": "sousse"},
    ],
    "niches": [],  # Populated dynamically from niche_matrix_AR.json
    "question_patterns": [
        "{keyword} في {il}",
        "أفضل {keyword} في {il}",
        "أرخص {keyword} في {il}",
        "أحسن {keyword} في {il}",
        "محترف {keyword} في {il}",
        "متخصص {keyword} في {il}",
        "معتمد {keyword} في {il}",
        "{keyword} في {il} أسعار 2026",
        "{keyword} في {il} تكلفة",
        "{keyword} في {il} سعر",
        "{keyword} في {il} عروض",
        "{keyword} في {il} خصم",
        "شركة {keyword} في {il}",
        "مكتب {keyword} في {il}",
        "مؤسسة {keyword} في {il}",
        "{keyword} {il} رقم",
        "{keyword} {il} اتصال",
        "{keyword} {il} واتساب",
        "{keyword} {il} عنوان",
        "{keyword} {il} موقع",
        "أين أجد {keyword} في {il}",
        "أين يمكنني إيجاد {keyword} في {il}",
        "أفضل شركة {keyword} في {il}",
        "أفضل محل {keyword} في {il}",
        "{keyword} في {il} تقييمات",
        "{keyword} في {il} مراجعات",
        "{keyword} في {il} آراء العملاء",
        "{keyword} في {il} تصنيف",
        "مقاول {keyword} {il}",
        "فني {keyword} {il}",
        "{keyword} {il} خدمة 24 ساعة",
        "{keyword} {il} طوارئ",
        "{keyword} {il} عاجل",
        "{keyword} {il} خدمة سريعة",
        "{keyword} {il} توصيل للمنزل",
        "{keyword} {il} خدمة منزلية",
        "{keyword} {il} ضمان",
        "{keyword} {il} استشارة مجانية",
        "كم سعر {keyword} في {il}",
        "احجز {keyword} في {il}",
        "احصل على {keyword} في {il}",
        "طلب {keyword} في {il}",
        "{keyword} في {il} مواعيد",
        "{keyword} في {il} ساعات العمل",
        "مقارنة {keyword} في {il}",
    ],
    "body_templates": [
        (
            "<p>هل تبحث عن <strong>{keyword}</strong> في {il}؟ "
            "لقد جئت إلى المكان الصحيح. نقدم لك الدليل الشامل "
            "لخدمات {keyword} في مدينة {il}، بما في ذلك الأسعار "
            "وتقييمات العملاء وتوصيات الخبراء.</p>"
            "<p>سواء كنت بحاجة إلى {keyword} بشكل عاجل أو تبحث عن "
            "خدمة دورية، فإن {il} تضم العديد من المحترفين المؤهلين "
            "المستعدين للمساعدة. قمنا بجمع أحدث المعلومات لتسهيل "
            "اختيارك.</p>"
            "<p>قارن بين أفضل مقدمي {keyword} في {il}، واقرأ "
            "التعليقات الموثقة من العملاء، واكتشف العروض الخاصة. "
            "مهمتنا هي ربطك بأفضل خدمة بأفضل سعر.</p>"
        ),
        (
            "<p><strong>{keyword}</strong> من الخدمات الأساسية "
            "لأصحاب المنازل والشركات في {il}. سواء كنت تتعامل مع "
            "مشكلة عاجلة أو تخطط لترقية دورية، فإن العثور على "
            "المحترف المناسب يحدث فرقاً كبيراً.</p>"
            "<p>منصتنا تضم أكثر شركات {keyword} موثوقية في {il}، "
            "وكلها مختارة بعناية من حيث الجودة والاعتمادية. نقدم "
            "ملفات تعريف مفصلة وأدلة أسعار وشهادات عملاء حقيقية "
            "لمساعدتك على الاختيار بثقة.</p>"
            "<p>ابق على اطلاع بأحدث اتجاهات وأسعار {keyword} في "
            "{il}. أضف هذه الصفحة إلى مفضلتك للرجوع إليها مستقبلاً "
            "ولا تفوت معلومات الخدمة القيمة.</p>"
        ),
        (
            "<p>مرحباً بك في المصدر النهائي لـ <strong>{keyword}</strong> "
            "في {il}. نحن ندرك أهمية العثور على مزود خدمة موثوق، "
            "وهدفنا هو تبسيط عملية البحث تماماً.</p>"
            "<p>سكان {il} يثقون في توصياتنا منذ سنوات. نقوم بتقييم "
            "كل مزود {keyword} بدقة بناءً على المؤهلات ورضا العملاء "
            "ووضوح الأسعار.</p>"
            "<p>من الخدمات الطارئة إلى الصيانة الدورية، يغطي دليلنا "
            "جميع جوانب {keyword} في {il}. ابدأ بحثك اليوم "
            "واكتشف الفرق.</p>"
        ),
        (
            "<p>اكتشف لماذا يختار أصحاب المنازل في {il} محترفي "
            "<strong>{keyword}</strong> الموصى بهم. مع سنوات من "
            "الخبرة والالتزام بالتميز، يضع هؤلاء المزودون معياراً "
            "للجودة في الخدمة.</p>"
            "<p>دليل {keyword} الخاص بنا لـ {il} يشمل كل شيء "
            "من تقديرات التكلفة ومقارنات الخدمة إلى نصائح الصيانة. "
            "نعتقد أن العملاء الم informed يتخذون أفضل القرارات.</p>"
            "<p>سواء كنت بحاجة إلى {keyword} لمنزلك أو عملك في "
            "{il}، فإن قائمتنا المختارة من المحترفين هنا للمساعدة. "
            "احصل على عرض سعر مجاني اليوم.</p>"
        ),
    ],
    "spintax_map": {
        "خدمة": ["صيانة", "مساعدة", "دعم", "رعاية", "حلول"],
        "شركة": ["مؤسسة", "منشأة", "مكتب", "وكالة", "مركز"],
        "محترف": ["فني", "خبير", "متخصص", "ماهر", "معتمد"],
        "جودة": ["ممتازة", "عالية", "متميزة", "فاخرة", "أولى"],
        "أفضل": ["أحسن", "ممتاز", "رائد", "متميز", "ممتازة"],
        "موثوق": ["مضمون", "أمين", "معتمد", "مرخص", "موصى به"],
        "سريع": ["فوري", "عاجل", "فوري", "مستعجل", "نفس اليوم"],
        "سهل": ["بسيط", "مريح", "مباشر", "يسير", "خالي من المتاعب"],
        "أسعار": ["تكاليف", "رسوم", "عروض", "تسعيرة", "قيمة"],
        "عروض": ["خصومات", "تخفيضات", "عروض خاصة", "حملات", "صفقات"],
        "اتصل": ["تواصل", "كلم", "راسل", "احجز", "استفسر"],
        "خدمة منزلية": ["خدمة بالمنزل", "زيارة منزلية", "خدمة في الموقع", "خدمة ميدانية", "معاينة"],
        "تركيب": ["تنصيب", "تثبيت", "وضع", "ربط", "تشغيل"],
        "صيانة": ["إصلاح", "تصليح", "ترميم", "عناية", "فحص"],
        "ضمان": ["كفالة", "تأمين", "التزام", "ضمانة", "تغطية"],
    },
}

# ---------------------------------------------------------------------------
# 1d. RU — Russian (Russia)
# ---------------------------------------------------------------------------
LOCALE_MATRIX["RU"] = {
    "code": "RU",
    "name": "Россия",
    "language": "ru",
    "geo": "RU",
    "trends_geo": "RU",
    "pytrends_hl": "ru-RU",
    "pytrends_tz": 180,
    "pytrends_pn": "russia",
    "locations": [
        {"name": "Москва", "slug": "moskva"},
        {"name": "Санкт-Петербург", "slug": "sankt-peterburg"},
        {"name": "Новосибирск", "slug": "novosibirsk"},
        {"name": "Екатеринбург", "slug": "yekaterinburg"},
        {"name": "Казань", "slug": "kazan"},
        {"name": "Нижний Новгород", "slug": "nizhniy-novgorod"},
        {"name": "Челябинск", "slug": "chelyabinsk"},
        {"name": "Самара", "slug": "samara"},
        {"name": "Омск", "slug": "omsk"},
        {"name": "Ростов-на-Дону", "slug": "rostov-na-donu"},
        {"name": "Уфа", "slug": "ufa"},
        {"name": "Красноярск", "slug": "krasnoyarsk"},
        {"name": "Воронеж", "slug": "voronezh"},
        {"name": "Пермь", "slug": "perm"},
        {"name": "Волгоград", "slug": "volgograd"},
        {"name": "Краснодар", "slug": "krasnodar"},
        {"name": "Саратов", "slug": "saratov"},
        {"name": "Тюмень", "slug": "tyumen"},
        {"name": "Тольятти", "slug": "tolyatti"},
        {"name": "Ижевск", "slug": "izhevsk"},
        {"name": "Барнаул", "slug": "barnaul"},
        {"name": "Ульяновск", "slug": "ulyanovsk"},
        {"name": "Иркутск", "slug": "irkutsk"},
        {"name": "Хабаровск", "slug": "khabarovsk"},
        {"name": "Ярославль", "slug": "yaroslavl"},
        {"name": "Владивосток", "slug": "vladivostok"},
        {"name": "Махачкала", "slug": "makhachkala"},
        {"name": "Томск", "slug": "tomsk"},
        {"name": "Оренбург", "slug": "orenburg"},
        {"name": "Кемерово", "slug": "kemerovo"},
        {"name": "Рязань", "slug": "ryazan"},
        {"name": "Астрахань", "slug": "astrahan"},
        {"name": "Пенза", "slug": "penza"},
        {"name": "Липецк", "slug": "lipetsk"},
        {"name": "Тула", "slug": "tula"},
        {"name": "Киров", "slug": "kirov"},
        {"name": "Чебоксары", "slug": "cheboksary"},
        {"name": "Калининград", "slug": "kaliningrad"},
        {"name": "Брянск", "slug": "bryansk"},
        {"name": "Курск", "slug": "kursk"},
        {"name": "Иваново", "slug": "ivanovo"},
        {"name": "Магнитогорск", "slug": "magnitogorsk"},
        {"name": "Тверь", "slug": "tver"},
        {"name": "Ставрополь", "slug": "stavropol"},
        {"name": "Сочи", "slug": "sochi"},
        {"name": "Белгород", "slug": "belgorod"},
        {"name": "Архангельск", "slug": "arhangelsk"},
        {"name": "Владимир", "slug": "vladimir"},
        {"name": "Смоленск", "slug": "smolensk"},
        {"name": "Мурманск", "slug": "murmansk"},
    ],
    "niches": [],  # Populated dynamically from niche_matrix_RU.json
    "question_patterns": [
        "{keyword} в {il}",
        "Лучший {keyword} в {il}",
        "Лучшие {keyword} в {il}",
        "Недорогой {keyword} в {il}",
        "Недорогие {keyword} в {il}",
        "Дешевый {keyword} в {il}",
        "Профессиональный {keyword} в {il}",
        "Качественный {keyword} в {il}",
        "Надежный {keyword} в {il}",
        "Проверенный {keyword} в {il}",
        "{keyword} в {il} цены 2026",
        "{keyword} в {il} стоимость",
        "{keyword} в {il} цена",
        "{keyword} в {il} прайс-лист",
        "{keyword} в {il} расценки",
        "{keyword} в {il} тарифы",
        "{keyword} в {il} телефон",
        "{keyword} в {il} номер телефона",
        "{keyword} в {il} адрес",
        "{keyword} в {il} контакты",
        "{keyword} в {il} сайт",
        "{keyword} в {il} отзывы",
        "{keyword} в {il} отзывы клиентов",
        "{keyword} в {il} рейтинг",
        "{keyword} в {il} рейтинг лучших",
        "{keyword} в {il} оценка",
        "Где найти {keyword} в {il}",
        "Где заказать {keyword} в {il}",
        "Где купить {keyword} в {il}",
        "Поиск {keyword} в {il}",
        "{keyword} в {il} срочно",
        "{keyword} в {il} круглосуточно",
        "{keyword} в {il} 24/7",
        "{keyword} в {il} экстренно",
        "{keyword} в {il} на дом",
        "{keyword} в {il} выезд",
        "{keyword} в {il} с выездом",
        "Мастер {keyword} в {il}",
        "Специалист {keyword} в {il}",
        "Бригада {keyword} в {il}",
        "Услуги {keyword} в {il}",
        "Компания {keyword} в {il}",
        "Фирма {keyword} в {il}",
        "{keyword} в {il} гарантия",
        "{keyword} в {il} со скидкой",
        "{keyword} в {il} акция",
        "{keyword} в {il} заказать",
        "{keyword} в {il} записаться",
        "Сравнить {keyword} в {il}",
        "Выбрать {keyword} в {il}",
        "Замена {keyword} в {il}",
        "Ремонт {keyword} в {il}",
        "Установка {keyword} в {il}",
        "Монтаж {keyword} в {il}",
        "Обслуживание {keyword} в {il}",
        "Чистка {keyword} в {il}",
    ],
    "body_templates": [
        (
            "<p>Ищете качественный <strong>{keyword}</strong> в {il}? "
            "Вы обратились по адресу. Наш подробный гид содержит "
            "всю необходимую информацию об услугах {keyword} в "
            "городе {il}, включая цены, отзывы клиентов и "
            "рекомендации профессионалов.</p>"
            "<p>Нужна ли вам плановая {keyword} или срочный ремонт, "
            "в {il} есть множество квалифицированных специалистов, "
            "готовых помочь. Мы собрали самую актуальную информацию, "
            "чтобы облегчить ваш выбор.</p>"
            "<p>Сравните лучших поставщиков {keyword} в {il}, "
            "прочитайте проверенные отзывы и найдите специальные "
            "предложения. Наша миссия — соединить вас с лучшим "
            "сервисом по лучшей цене.</p>"
        ),
        (
            "<p><strong>{keyword}</strong> — необходимая услуга "
            "для владельцев домов и бизнеса в {il}. Будь то "
            "срочная проблема или плановое обслуживание, правильный "
            "специалист имеет решающее значение.</p>"
            "<p>Наша платформа представляет самые надежные компании "
            "по {keyword} в {il}, проверенные на качество и "
            "надежность. Мы предоставляем подробные профили, "
            "прайс-листы и реальные отзывы клиентов.</p>"
            "<p>Следите за последними тенденциями и ценами на "
            "{keyword} в {il}. Добавьте эту страницу в закладки, "
            "чтобы не пропустить полезную информацию.</p>"
        ),
        (
            "<p>Добро пожаловать в главный ресурс по "
            "<strong>{keyword}</strong> в {il}. Мы понимаем, "
            "как важно найти надежного поставщика услуг, и наша "
            "цель — максимально упростить ваш поиск.</p>"
            "<p>Жители {il} доверяют нашим рекомендациям уже много "
            "лет. Мы тщательно оцениваем каждого поставщика {keyword} "
            "по квалификации, удовлетворенности клиентов и "
            "прозрачности цен.</p>"
            "<p>От экстренных услуг до планового обслуживания — "
            "наш гид охватывает все аспекты {keyword} в {il}. "
            "Начните поиск сегодня и ощутите разницу.</p>"
        ),
        (
            "<p>Узнайте, почему владельцы домов в {il} выбирают "
            "рекомендованных нами специалистов по "
            "<strong>{keyword}</strong>. С многолетним опытом и "
            "стремлением к совершенству эти профессионалы "
            "устанавливают стандарты качества.</p>"
            "<p>Наш гид по {keyword} в {il} включает все: от "
            "сметы расходов и сравнения услуг до советов по "
            "обслуживанию. Мы считаем, что информированные "
            "клиенты принимают лучшие решения.</p>"
            "<p>Нужен ли вам {keyword} для дома или бизнеса в "
            "{il}, наш список проверенных специалистов к вашим "
            "услугам. Получите бесплатную консультацию сегодня.</p>"
        ),
    ],
    "spintax_map": {
        "услуга": ["обслуживание", "сервис", "работа", "помощь", "поддержка"],
        "компания": ["фирма", "организация", "предприятие", "бизнес", "агентство"],
        "профессионал": ["специалист", "эксперт", "мастер", "опытный", "квалифицированный"],
        "качество": ["премиум", "высокое", "отличное", "первоклассное", "лучшее"],
        "лучший": ["отличный", "превосходный", "ведущий", "первоклассный", "выдающийся"],
        "надежный": ["проверенный", "достойный доверия", "репутационный", "гарантированный", "известный"],
        "быстрый": ["оперативный", "срочный", "немедленный", "скорый", "экстренный"],
        "простой": ["легкий", "удобный", "понятный", "доступный", "элементарный"],
        "цены": ["стоимость", "расценки", "тарифы", "прайс", "стоимость услуг"],
        "найти": ["искать", "подобрать", "выбрать", "открыть", "получить"],
        "нужен": ["требуется", "необходим", "понадобится", "востребован", "ищут"],
        "работа": ["услуга", "заказ", "задание", "проект", "обслуживание"],
        "гарантия": ["обязательство", "страховка", "залог", "ручательство", "поручительство"],
        "опыт": ["стаж", "практика", "квалификация", "навык", "умение"],
        "современный": ["новый", "передовой", "инновационный", "новейший", "актуальный"],
        "скидка": ["акция", "спецпредложение", "распродажа", "уценка", "бонус"],
    },
}

# ---------------------------------------------------------------------------
# TR District Expansion — Load districts from bot core data
# ---------------------------------------------------------------------------
TR_DISTRICT_MODE = os.getenv("OMNI_TR_DISTRICTS", "false").lower() == "true"

def expand_tr_with_districts():
    """Expand TR locations to include all districts from turkiye_il_ilce.py."""
    if not TR_DISTRICT_MODE:
        return
    try:
        import sys as _sys
        _sys.path.insert(0, os.path.join(os.path.dirname(__file__), "bots"))
        from core.data.turkiye_il_ilce import TURKIYE_IL_ILCE, get_districts
        tr_config = LOCALE_MATRIX["TR"]
        city_slugs = {loc["slug"] for loc in tr_config["locations"]}
        expanded = list(tr_config["locations"])
        for city_name, districts in TURKIYE_IL_ILCE.items():
            city_slug = Transliterator.slugify(city_name)
            for district in districts:
                d_slug = Transliterator.slugify(district)
                if d_slug not in city_slugs:
                    expanded.append({
                        "name": f"{district}, {city_name}",
                        "slug": f"{d_slug}-{city_slug}",
                        "parent_name": city_name,
                    })
                    city_slugs.add(d_slug)
        tr_config["locations"] = expanded
        log.info(f"[DistrictExpand] TR locations expanded to {len(expanded)} (cities + districts)")
    except ImportError as e:
        log.warning(f"[DistrictExpand] Could not load districts: {e}")
    except Exception as e:
        log.warning(f"[DistrictExpand] Error: {e}")


# Default locale order (controls processing priority)
DEFAULT_LOCALE_ORDER = ["TR", "EN", "AR", "RU"]

# ===========================================================================
# DYNAMIC NICHE STORE (populated from JSON at runtime)
# ===========================================================================
LOADED_NICHES: Dict[str, List[Dict[str, Any]]] = {}
# Bootstrap fallback — used only if no JSON file exists AND no --seed-matrix run
BOOTSTRAP_NICHES: Dict[str, List[Dict[str, Any]]] = {
    code: OfflineVocabularyGenerator.build_fallback_niches(code)
    for code in DEFAULT_LOCALE_ORDER
}

def load_niche_matrix(locale_code: str) -> List[Dict[str, Any]]:
    """Load niche data from JSON file. Falls back to bootstrap."""
    if locale_code in LOADED_NICHES:
        return LOADED_NICHES[locale_code]

    path = Path(f"niche_matrix_{locale_code}.json")
    if path.exists():
        try:
            data = json.loads(path.read_text(encoding="utf-8"))
            niches = data.get("niches", [])
            if niches:
                LOADED_NICHES[locale_code] = niches
                log.info(f"[NicheLoader] Loaded {len(niches)} niches from {path}")
                return niches
        except (json.JSONDecodeError, KeyError, Exception) as e:
            log.warning(f"[NicheLoader] Failed to load {path}: {e}")

    fallback = BOOTSTRAP_NICHES.get(locale_code, [])
    log.info(f"[NicheLoader] Using bootstrap fallback ({len(fallback)} niches) for {locale_code}")
    LOADED_NICHES[locale_code] = fallback
    return fallback


def ensure_niche_matrices(locale_codes: List[str]) -> None:
    """Ensure niche matrices exist for all locales, building if needed."""
    for code in locale_codes:
        path = Path(f"niche_matrix_{code}.json")
        if not path.exists():
            log.info(f"[NicheLoader] No matrix found for {code}. Building via Google Suggest...")
            try:
                builder = NicheMatrixBuilder(code, force=False)
                niches = builder.build()
                builder.save(niches)
                LOADED_NICHES[code] = niches
            except Exception as e:
                log.warning(f"[NicheLoader] Builder failed for {code}: {e}")
                load_niche_matrix(code)
        else:
            load_niche_matrix(code)

# ===========================================================================
# 2. TRANSLITERATOR — Robust Unicode → Latin Slug Pipeline
# ===========================================================================

class Transliterator:
    """Converts any language text into clean Latin-hyphenated URL slugs.

    Uses python-slugify (with unidecode backend) as the primary engine.
    Falls back to manual Turkish character replacement, then generic ASCII
    stripping for environments without the libraries installed.
    """

    @staticmethod
    def slugify(text: str) -> str:
        """Convert text to a clean, hyphenated Latin-char slug.

        Examples:
            "Москва"      → "moskva"
            "دبي"         → "dubayy"
            "İstanbul"    → "istanbul"
            "AC Repair"   → "ac-repair"
        """
        if not text:
            return ""

        if HAS_SLUGIFY:
            try:
                result = _slugify_lib(text, lowercase=True, separator="-")
                if result:
                    return result
            except Exception:
                pass

        if HAS_UNIDECODE:
            try:
                s = _unidecode_lib.unidecode(text).lower()
            except Exception:
                s = text.lower()
        else:
            s = text.lower()

        replacements = {
            "ı": "i", "ş": "s", "ğ": "g", "ç": "c", "ö": "o", "ü": "u",
            "İ": "i", "Ş": "s", "Ğ": "g", "Ç": "c", "Ö": "o", "Ü": "u",
        }
        for k, v in replacements.items():
            s = s.replace(k, v)

        s = re.sub(r"[^a-z0-9-]+", "-", s)
        s = re.sub(r"-{2,}", "-", s)
        return s.strip("-")

# ===========================================================================
# 3. GEO-TARGETED GOOGLE TRENDS
# ===========================================================================

class TrendsFetcher:
    """Multi-region Google Trends fetcher.

    Iterates over all active locales and fetches native-language daily
    trends for each corresponding geo region.
    """

    RSS_URL = "https://trends.google.com/trending/rss?geo={geo}"
    FALLBACK_GEO = "US"

    @staticmethod
    def fetch_rss(geo: str) -> List[str]:
        """Parse Google Trends RSS feed for a specific geo."""
        try:
            import xml.etree.ElementTree as ET
            url = TrendsFetcher.RSS_URL.format(geo=geo)
            resp = requests.get(url, headers={
                "User-Agent": (
                    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                    "AppleWebKit/537.36 (KHTML, like Gecko) "
                    "Chrome/124.0.0.0 Safari/537.36"
                )
            }, timeout=15)
            if resp.status_code != 200:
                return []
            root = ET.fromstring(resp.content)
            trends = []
            for item in root.iter("item"):
                title_el = item.find("title")
                if title_el is not None and title_el.text:
                    trends.append(title_el.text.strip())
            return trends[:15]
        except Exception as e:
            return []

    @staticmethod
    def fetch_pytrends(hl: str, tz: int, pn: str) -> List[str]:
        """Try pytrends library as fallback for a specific locale."""
        try:
            from pytrends.request import TrendReq
            pytrends = TrendReq(
                hl=hl,
                tz=tz,
                requests_args={"timeout": 15},
            )
            trending = pytrends.trending_searches(pn=pn)
            if not trending.empty:
                return trending[0].head(15).tolist()
        except ImportError:
            pass
        except Exception as e:
            pass
        return []

    @classmethod
    def get_live_trends(cls, locale_config: Dict[str, Any]) -> List[str]:
        """Fetch live trends for a single locale config.

        Args:
            locale_config: A locale dict from LOCALE_MATRIX (must contain
                'trends_geo', 'pytrends_hl', 'pytrends_tz', 'pytrends_pn').

        Returns:
            List of trending topic strings, or empty list on failure.
        """
        geo = locale_config.get("trends_geo", "US")
        hl = locale_config.get("pytrends_hl", "en-US")
        tz = locale_config.get("pytrends_tz", 360)
        pn = locale_config.get("pytrends_pn", "united_states")

        trends = cls.fetch_rss(geo)
        if not trends:
            trends = cls.fetch_pytrends(hl, tz, pn)
        if not trends and geo != cls.FALLBACK_GEO:
            trends = cls.fetch_rss(cls.FALLBACK_GEO)

        if trends:
            log.info(f"  [{locale_config['code']}] Fetched {len(trends)} trending topics (geo={geo})")
        else:
            log.info(f"  [{locale_config['code']}] No trends fetched for geo={geo}")
        return trends

    @classmethod
    def fetch_all_locales(cls, locale_configs: List[Dict[str, Any]]) -> Dict[str, List[str]]:
        """Fetch trends for multiple locales.

        Returns:
            Dict mapping locale_code -> list of trend keywords.
        """
        results = {}
        for lc in locale_configs:
            results[lc["code"]] = cls.get_live_trends(lc)
        return results

# ===========================================================================
# 4. SPINTAX ENGINE (Locale-Aware)
# ===========================================================================

class SpintaxEngine:
    """Multi-level text uniqueness engine per locale."""

    @staticmethod
    def get_spintax_map(locale_code: str) -> Dict[str, List[str]]:
        """Return the spintax map for the given locale."""
        locale_data = LOCALE_MATRIX.get(locale_code, LOCALE_MATRIX["EN"])
        return locale_data.get("spintax_map", {})

    @staticmethod
    def spin_text(text: str, locale_code: str, intensity: float = 0.5) -> str:
        """Apply word-level synonym replacement using locale spintax."""
        spintax_map = SpintaxEngine.get_spintax_map(locale_code)
        words = text.split()
        result = []
        for w in words:
            clean = w.strip(".,;:!?\"'()[]{}<>،؟«»").lower()
            if clean in spintax_map and random.random() < intensity:
                replacement = random.choice(spintax_map[clean])
                if w[0].isupper():
                    replacement = replacement.capitalize()
                punct = ""
                if w and w[-1] in ".,;:!?\"'()[]،؟":
                    punct = w[-1]
                result.append(replacement + punct)
            else:
                result.append(w)
        return " ".join(result)

    @staticmethod
    def spin_html_template(
        template: str, loc_name: str, keyword: str, locale_code: str
    ) -> str:
        """Fill a template with location/keyword, then apply spintax."""
        content = template.replace("{il}", loc_name).replace("{keyword}", keyword)
        content = SpintaxEngine.spin_text(content, locale_code, intensity=0.35)
        return content

    @staticmethod
    def generate_unique_body(
        loc: Dict[str, str], keyword: str, locale_code: str
    ) -> str:
        """Produce unique body_content using locale template + spintax."""
        locale_data = LOCALE_MATRIX.get(locale_code, LOCALE_MATRIX["EN"])
        templates = locale_data.get("body_templates", LOCALE_MATRIX["EN"]["body_templates"])

        seed = hashlib.md5(f"{loc['slug']}:{keyword}:{locale_code}".encode()).hexdigest()
        rng = random.Random(seed)
        tmpl_idx = rng.randint(0, len(templates) - 1)
        template = templates[tmpl_idx]
        body = SpintaxEngine.spin_html_template(template, loc["name"], keyword, locale_code)
        body = SpintaxEngine.apply_spintax_syntax(body)
        return body

    @staticmethod
    def apply_spintax_syntax(text: str) -> str:
        """Parse {opt1|opt2|opt3} spintax syntax and choose one randomly."""
        def _replacer(m: re.Match) -> str:
            options = [o.strip() for o in m.group(1).split("|")]
            return random.choice(options)
        pattern = r"\{([^{}]+?)\}"
        prev = None
        while prev != text:
            prev = text
            text = re.sub(pattern, _replacer, text)
        return text

# ===========================================================================
# 5. MULTI-LOCALE CONTENT MATRIX
# ===========================================================================

class ContentMatrix:
    """Locale-aware content node generator.

    Builds Cartesian product: {Locations} × {Keywords per Niche} × {Question Patterns}
    for each active locale, with transliterated slugs and locale-tagged payloads.
    """

    def __init__(self, locale_codes: List[str]):
        self.locale_codes = locale_codes
        self.trend_keywords: Dict[str, List[str]] = {}  # locale_code -> [trends]

    def inject_trends(self, all_trends: Dict[str, List[str]]) -> None:
        """Inject per-locale Google Trends keywords."""
        for locale_code, trends in all_trends.items():
            if trends and locale_code in self.locale_codes:
                self.trend_keywords[locale_code] = trends
                log.info(f"  [{locale_code}] Injected {len(trends)} trend keywords")

    def _get_niches(self, locale_code: str) -> List[Dict[str, Any]]:
        """Get niches for locale — from LOADED_NICHES or fallback."""
        loaded = LOADED_NICHES.get(locale_code, [])
        if loaded:
            return loaded
        return load_niche_matrix(locale_code)

    def estimate_nodes(self) -> Dict[str, int]:
        """Return estimated node count per locale."""
        estimates = {}
        for code in self.locale_codes:
            niches = self._get_niches(code)
            total_kws = sum(len(n["keywords"]) for n in niches)
            ld = LOCALE_MATRIX.get(code, LOCALE_MATRIX["EN"])
            loc_count = len(ld["locations"])
            qp_count = len(ld["question_patterns"])
            base = loc_count * total_kws * qp_count
            if code in self.trend_keywords and self.trend_keywords[code]:
                base += loc_count * len(self.trend_keywords[code]) * qp_count
            estimates[code] = base
        return estimates

    def yield_nodes(self) -> Generator[Dict[str, Any], None, None]:
        """Lazily generate unique content nodes from all active locales."""
        for locale_code in self.locale_codes:
            yield from self._yield_locale_nodes(locale_code)

    def _yield_locale_nodes(self, locale_code: str) -> Generator[Dict[str, Any], None, None]:
        """Generate nodes for a single locale."""
        locale_data = LOCALE_MATRIX.get(locale_code, LOCALE_MATRIX["EN"])
        locations = locale_data["locations"]
        niches = self._get_niches(locale_code)
        question_patterns = locale_data["question_patterns"]

        for loc in locations:
            for niche in niches:
                for kw in niche["keywords"]:
                    yield from self._make_nodes_for(
                        loc=loc,
                        niche=niche,
                        keyword=kw,
                        pattern_list=question_patterns,
                        locale_code=locale_code,
                    )

            if locale_code in self.trend_keywords and self.trend_keywords[locale_code]:
                trend_niche = {
                    "name": (
                        "Trend Konular" if locale_code == "TR"
                        else "Trending Topics" if locale_code == "EN"
                        else "Трендовые темы" if locale_code == "RU"
                        else "اتجاهات"
                    ),
                    "slug": "trending-topics",
                }
                for trend_kw in self.trend_keywords[locale_code]:
                    yield from self._make_nodes_for(
                        loc=loc,
                        niche=trend_niche,
                        keyword=trend_kw,
                        pattern_list=question_patterns,
                        locale_code=locale_code,
                    )

    def _make_nodes_for(
        self,
        loc: Dict[str, str],
        niche: Dict[str, Any],
        keyword: str,
        pattern_list: List[str],
        locale_code: str,
    ) -> Generator[Dict[str, Any], None, None]:
        """Generate content nodes for a specific keyword-locale combination."""
        loc_slug = loc.get("slug", Transliterator.slugify(loc["name"]))
        niche_slug = niche.get("slug", Transliterator.slugify(niche["name"]))
        kw_slug = Transliterator.slugify(keyword)

        for qp in pattern_list:
            title = qp.replace("{il}", loc["name"]).replace("{keyword}", keyword)
            slug_base = f"{loc_slug}-{niche_slug}-{kw_slug}"
            seed_str = f"{loc_slug}:{niche_slug}:{kw_slug}:{qp}:{locale_code}"
            slug = self._unique_slug(slug_base, seed_str)
            body = SpintaxEngine.generate_unique_body(loc, keyword, locale_code)

            if locale_code == "TR":
                meta_desc = (
                    f"{loc['name']} {keyword} | Güncel {keyword} hizmetleri, "
                    f"fiyatları ve firmaları {loc['name']} sayfamızda."
                )
            elif locale_code == "EN":
                meta_desc = (
                    f"Top {keyword} in {loc['name']} | Find the best {keyword} "
                    f"professionals, pricing, and reviews in {loc['name']}."
                )
            elif locale_code == "AR":
                meta_desc = (
                    f"{keyword} في {loc['name']} | أفضل خدمات {keyword} "
                    f"وأسعارها في {loc['name']}"
                )
            elif locale_code == "RU":
                meta_desc = (
                    f"{keyword} в {loc['name']} | Лучшие {keyword} "
                    f"в {loc['name']}, цены и отзывы"
                )
            else:
                meta_desc = f"{keyword} in {loc['name']}"

            yield {
                "title": title,
                "slug": slug,
                "body_content": body,
                "meta_description": meta_desc,
                "is_restricted_content": False,
                "taxonomy_slug": niche_slug,
                "location_slug": loc_slug,
                "locale": locale_code,
                "published_at": datetime.now().isoformat(),
            }

    @staticmethod
    def _unique_slug(base: str, seed: str) -> str:
        """Append a short hash to ensure slug uniqueness."""
        suffix = hashlib.md5(seed.encode()).hexdigest()[:8]
        return f"{base}-{suffix}"

# ===========================================================================
# 6. API CLIENT (Locale-Aware Payload)
# ===========================================================================

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
            "User-Agent": "OmniBot/v2.0",
        })
        self._lock = random.Random()
        self.stats = {
            "sent": 0,
            "failed": 0,
            "errors": [],
            "per_locale": {},
        }

    def _send(self, payload: Dict, endpoint: str = "/api/v1/ingest") -> Optional[Dict]:
        """Single POST request with retry and exponential backoff."""
        if self.dry_run:
            return {"success": True, "dry_run": True}

        url = f"{self.base_url}{endpoint}"
        for attempt in range(1, self.max_retries + 2):
            try:
                resp = self.session.post(url, json=payload, timeout=60)
                if resp.status_code in (200, 201, 202):
                    return resp.json()
                if resp.status_code == 429:
                    wait = (2 ** attempt) + random.uniform(0, 1)
                    time.sleep(wait)
                    continue
                if resp.status_code >= 500:
                    wait = (2 ** attempt) + random.uniform(0, 1)
                    time.sleep(wait)
                    continue
                return None
            except requests.ConnectionError as e:
                wait = (2 ** attempt) + random.uniform(0, 2)
                time.sleep(wait)
            except requests.Timeout:
                wait = (2 ** attempt) + random.uniform(0, 1)
                time.sleep(wait)
            except Exception as e:
                return None
        return None

    def send_batch(self, nodes: List[Dict]) -> int:
        """Send a batch of content nodes. Returns count of accepted nodes."""
        time.sleep(self.rate_sleep)
        result = self._send({"content_nodes": nodes})
        if result and result.get("success"):
            self.stats["sent"] += len(nodes)
            for node in nodes:
                lc = node.get("locale", "??")
                self.stats["per_locale"][lc] = self.stats["per_locale"].get(lc, 0) + 1
            return len(nodes)
        self.stats["failed"] += len(nodes)
        return 0

    def setup_taxonomies_and_locations(
        self,
        locale_nodes: List[Tuple[str, List[Dict]]],
    ) -> None:
        """Pre-create all taxonomies & locations in the backend per locale."""
        if self.dry_run:
            return

        all_taxonomies = []
        all_locations = {}

        for locale_code, nodes in locale_nodes:
            for node in nodes:
                t_slug = node.get("taxonomy_slug", "")
                if t_slug and t_slug not in {t["slug"] for t in all_taxonomies}:
                    all_taxonomies.append({
                        "name": t_slug.replace("-", " ").title(),
                        "slug": t_slug,
                        "locale": locale_code,
                    })
                l_slug = node.get("location_slug", "")
                if l_slug and l_slug not in all_locations:
                    all_locations[l_slug] = {
                        "name": l_slug.replace("-", " ").title(),
                        "slug": l_slug,
                    }

        log.info(f"Setting up {len(all_taxonomies)} taxonomies...")
        for i in range(0, len(all_taxonomies), self.batch_size):
            batch = all_taxonomies[i:i + self.batch_size]
            resp = self._send({"taxonomies": batch})
            status = resp.get("success", False) if resp else False

        loc_list = list(all_locations.values())
        log.info(f"Setting up {len(loc_list)} locations...")
        for i in range(0, len(loc_list), self.batch_size):
            batch = loc_list[i:i + self.batch_size]
            resp = self._send({"locations": batch})
            status = resp.get("success", False) if resp else False

    def report_stats(self) -> Dict[str, Any]:
        """Return aggregate stats."""
        return dict(self.stats)

# ===========================================================================
# 7. CHECKPOINT / RESUME
# ===========================================================================

class Checkpoint:
    """Persistent checkpoint for resume capability."""

    def __init__(self, path: str):
        self.path = Path(path)
        self.data: Dict[str, Any] = {"processed": [], "total": 0, "locale_totals": {}}

    def load(self) -> bool:
        if self.path.exists():
            try:
                self.data = json.loads(self.path.read_text(encoding="utf-8"))
                log.info(f"Resumed from checkpoint: {len(self.data['processed'])} items processed")
                return True
            except Exception:
                pass
        return False

    def save(self, processed_slugs: List[str], total: int, locale_totals: Dict[str, int] = None) -> None:
        self.data["processed"] = processed_slugs[-10000:]
        self.data["total"] = total
        self.data["updated_at"] = datetime.now().isoformat()
        if locale_totals:
            self.data["locale_totals"] = locale_totals
        self.path.write_text(json.dumps(self.data, ensure_ascii=False), encoding="utf-8")

    def is_processed(self, slug: str) -> bool:
        return slug in self.data["processed"]

# ===========================================================================
# 8. XML SITEMAP GENERATOR
# ===========================================================================

class SitemapGenerator:
    """Generates XML sitemaps from processed content nodes."""

    def __init__(self, site_url: str = ""):
        self.site_url = site_url.rstrip("/") if site_url else os.getenv("SITE_URL", "https://omviportal.com")

    def generate(self, nodes: List[Dict[str, Any]], output_path: str = "sitemap.xml") -> str:
        urls_by_locale: Dict[str, List[str]] = {}
        for node in nodes:
            lc = node.get("locale", "unknown")
            if lc not in urls_by_locale:
                urls_by_locale[lc] = []
            urls_by_locale[lc].append(f"{self.site_url}/{node['slug']}")

        timestamp = datetime.now().strftime("%Y-%m-%d")

        parts = ['<?xml version="1.0" encoding="UTF-8"?>']
        parts.append('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">')

        for lc in sorted(urls_by_locale.keys()):
            for url in urls_by_locale[lc]:
                parts.append("  <url>")
                parts.append(f"    <loc>{url}</loc>")
                parts.append(f"    <lastmod>{timestamp}</lastmod>")
                parts.append(f"    <changefreq>weekly</changefreq>")
                parts.append(f"    <priority>0.8</priority>")
                parts.append("  </url>")

        parts.append("</urlset>")
        content = "\n".join(parts)

        Path(output_path).write_text(content, encoding="utf-8")
        log.info(f"[Sitemap] Generated {len(nodes)} URLs → {output_path}")

        # Generate per-locale sitemaps
        for lc, urls in urls_by_locale.items():
            loc_path = f"sitemap_{lc.lower()}.xml"
            loc_parts = ['<?xml version="1.0" encoding="UTF-8"?>']
            loc_parts.append('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">')
            for url in urls:
                loc_parts.append("  <url>")
                loc_parts.append(f"    <loc>{url}</loc>")
                loc_parts.append(f"    <lastmod>{timestamp}</lastmod>")
                loc_parts.append(f"    <changefreq>weekly</changefreq>")
                loc_parts.append(f"    <priority>0.8</priority>")
                loc_parts.append("  </url>")
            loc_parts.append("</urlset>")
            Path(loc_path).write_text("\n".join(loc_parts), encoding="utf-8")
            log.info(f"[Sitemap] Generated {len(urls)} URLs → {loc_path}")

        return content


# ===========================================================================
# 9. MAIN ORCHESTRATOR
# ===========================================================================

class OmniBotV2:
    """Global SEO Dominator orchestrator — multi-locale pipeline."""

    def __init__(self, config: Dict[str, Any]):
        self.config = config
        self.dry_run = config.get("DRY_RUN", False)
        self.quick_mode = config.get("QUICK_MODE", False)
        self.resume_mode = config.get("RESUME", False)

        # Determine active locales
        active_locales = config.get("ACTIVE_LOCALES", [])
        if not active_locales:
            active_locales = list(DEFAULT_LOCALE_ORDER)
        # Validate — only include those defined in LOCALE_MATRIX
        self.active_locales = [lc for lc in active_locales if lc in LOCALE_MATRIX]
        if not self.active_locales:
            self.active_locales = list(DEFAULT_LOCALE_ORDER)

        # Expand TR with districts if enabled
        expand_tr_with_districts()

        self.seed_mode = config.get("SEED_MATRIX", False)
        self.skip_seed = config.get("SKIP_SEED", False)

        if self.seed_mode:
            log.info("--seed-matrix mode: building niche matrices via Google Suggest...")
            run_matrix_seeder(locale_codes=self.active_locales, force=True)
        elif not self.skip_seed:
            auto_built = False
            for code in self.active_locales:
                path = Path(f"niche_matrix_{code}.json")
                if not path.exists():
                    log.info(f"Auto-building niche matrix for {code} (no JSON found)...")
                    try:
                        builder = NicheMatrixBuilder(code, force=False)
                        niches = builder.build()
                        builder.save(niches)
                        LOADED_NICHES[code] = niches
                        auto_built = True
                    except Exception as e:
                        log.warning(f"Auto-build failed for {code}: {e}")
                        load_niche_matrix(code)
                else:
                    load_niche_matrix(code)
            if auto_built:
                log.info("Niche matrices auto-built successfully.")
        else:
            for code in self.active_locales:
                load_niche_matrix(code)

        self.matrix = ContentMatrix(self.active_locales)
        self.checkpoint = Checkpoint(config.get("RESUME_FILE", "omni_v2_checkpoint.json"))
        self.api = ApiClient(config)
        self.trend_keywords: Dict[str, List[str]] = {}
        self.locale_configs: List[Dict] = [
            LOCALE_MATRIX[lc] for lc in self.active_locales
        ]

    def _fetch_trends(self) -> None:
        """Fetch live trends for all active locales."""
        if self.config.get("TRENDS_ENABLED", True):
            log.info("Fetching Google Trends data for all active locales...")
            self.trend_keywords = TrendsFetcher.fetch_all_locales(self.locale_configs)
            if self.trend_keywords:
                self.matrix.inject_trends(self.trend_keywords)
        else:
            log.info("Google Trends disabled via config")

    def _estimate_work(self) -> Dict[str, int]:
        """Return estimated node count per locale."""
        return self.matrix.estimate_nodes()

    def run(self) -> None:
        """Execute full multi-locale pipeline."""
        start = time.time()
        log.info("=" * 60)
        log.info("OMNI-BOT v2.1 ∞ INFINITY ENGINE — GLOBAL SEO DOMINATOR")
        log.info("=" * 60)
        log.info(f"Active locales: {', '.join(self.active_locales)}")

        if self.dry_run:
            log.info("DRY RUN MODE — no API calls will be made")
        elif not self.config["API_TOKEN"]:
            log.error("API_TOKEN is empty. Set OMNI_API_TOKEN env var or edit CONFIG.")
            sys.exit(1)
        if self.quick_mode:
            log.info("QUICK MODE — limiting to 500 sample nodes")
        if self.resume_mode:
            log.info("RESUME MODE — skipping already-processed slugs")

        # Step 1: Fetch live trends per locale
        self._fetch_trends()

        # Step 2: Calculate work per locale
        estimates = self._estimate_work()
        total_estimated = sum(estimates.values())
        log.info(f"Matrix estimated output: ~{total_estimated:,} unique content nodes")
        for lc, est in estimates.items():
            ld = LOCALE_MATRIX[lc]
            niches = self.matrix._get_niches(lc)
            log.info(f"  [{lc}] {ld['name']}: {est:,} nodes "
                     f"({len(ld['locations'])} locs x "
                     f"{sum(len(n['keywords']) for n in niches)} kws x "
                     f"{len(ld['question_patterns'])} patterns)")
            if lc in self.trend_keywords and self.trend_keywords[lc]:
                log.info(f"    ├─ Live Trends injected: {len(self.trend_keywords[lc])}")

        # Step 3: Load checkpoint
        processed_slugs: List[str] = []
        if self.resume_mode:
            self.checkpoint.load()
            processed_slugs = self.checkpoint.data.get("processed", [])

        # Step 4: Build the full node list across all locales
        nodes: List[Dict] = []
        for node in self.matrix.yield_nodes():
            if self.resume_mode and self.checkpoint.is_processed(node["slug"]):
                continue
            nodes.append(node)
            if self.quick_mode and len(nodes) >= 500:
                break

        total_nodes = len(nodes)

        # Count nodes per locale
        node_locale_counts: Dict[str, int] = {}
        for n in nodes:
            lc = n.get("locale", "??")
            node_locale_counts[lc] = node_locale_counts.get(lc, 0) + 1

        log.info(f"Nodes to process: {total_nodes:,}")
        for lc, count in node_locale_counts.items():
            log.info(f"  [{lc}] {count:,} nodes")

        if total_nodes == 0:
            log.info("No nodes to process. Either all done or filter too narrow.")
            return

        # Step 5: Setup taxonomies & locations in backend
        locale_grouped = [(lc, [n for n in nodes if n.get("locale") == lc]) for lc in self.active_locales]
        self.api.setup_taxonomies_and_locations(locale_grouped)

        # Step 6: Batch & send via thread pool
        batches = [
            nodes[i:i + self.config["BATCH_SIZE"]]
            for i in range(0, len(nodes), self.config["BATCH_SIZE"])
        ]
        log.info(f"Sending {len(batches)} batches with {self.config['CONCURRENT_WORKERS']} workers...")

        with ThreadPoolExecutor(max_workers=self.config["CONCURRENT_WORKERS"]) as executor:
            fut_map = {executor.submit(self.api.send_batch, b): b for b in batches}
            done_count = 0
            for fut in as_completed(fut_map):
                done_count += 1
                if done_count % 20 == 0:
                    pct = done_count / len(batches) * 100
                    log.info(f"  Progress: {done_count}/{len(batches)} batches ({pct:.0f}%)")
                if done_count % 50 == 0:
                    slgs = [
                        n["slug"]
                        for b in batches[:done_count]
                        for n in b
                    ]
                    self.checkpoint.save(slgs, done_count * self.config["BATCH_SIZE"])

        # Step 7: Final checkpoint
        all_slugs = [n["slug"] for n in nodes]
        self.checkpoint.save(all_slugs, total_nodes, node_locale_counts)

        # Step 8: Generate XML sitemaps
        if not self.dry_run and nodes:
            try:
                site_url = os.getenv("SITE_URL", "https://omviportal.com")
                sitemap_gen = SitemapGenerator(site_url)
                sitemap_gen.generate(nodes, "sitemap_global.xml")
            except Exception as e:
                log.warning(f"Sitemap generation failed: {e}")

        # Step 9: IndexNow notification
        if not self.dry_run and nodes:
            try:
                indexnow_key = os.getenv("INDEXNOW_KEY", "")
                site_url = os.getenv("SITE_URL", "https://omviportal.com")
                if indexnow_key:
                    from bots.core.seo_indexer import IndexNowNotifier
                    notifier = IndexNowNotifier(site_url, indexnow_key)
                    all_urls = [f"{site_url}/{slug}" for slug in all_slugs]
                    indexed = notifier.notify(all_urls[:10000])
                    log.info(f"IndexNow: {indexed} URLs notified")
            except Exception as e:
                log.warning(f"IndexNow notification failed: {e}")

        # Step 10: Report
        elapsed = time.time() - start
        stats = self.api.report_stats()
        log.info("=" * 60)
        log.info("MISSION COMPLETE — GLOBAL DOMINATION ACHIEVED")
        log.info(f"  Total Nodes Sent: {stats['sent']:,}")
        log.info(f"  Failed: {stats['failed']:,}")
        if stats.get("per_locale"):
            log.info("  Per-locale breakdown:")
            for lc, cnt in sorted(stats["per_locale"].items()):
                log.info(f"    [{lc}] {cnt:,} nodes")
        log.info(f"  Throughput: {total_nodes/elapsed:.0f} nodes/sec")
        log.info(f"  Elapsed: {elapsed:.1f}s")
        log.info(f"  Checkpoint: {self.checkpoint.path}")
        log.info("=" * 60)

# ===========================================================================
# 9. CLI ENTRY POINT
# ===========================================================================

def parse_args() -> Dict[str, Any]:
    """Parse CLI arguments over config."""
    config = dict(CONFIG)
    i = 1
    while i < len(sys.argv):
        arg = sys.argv[i]
        if arg in ("--dry-run", "--dry"):
            config["DRY_RUN"] = True
        elif arg in ("--quick", "--test"):
            config["QUICK_MODE"] = True
        elif arg in ("--resume", "--continue"):
            config["RESUME"] = True
        elif arg == "--locale" and i + 1 < len(sys.argv):
            i += 1
            raw = sys.argv[i]
            codes = [c.strip().upper() for c in raw.split(",")]
            valid = [c for c in codes if c in LOCALE_MATRIX]
            if valid:
                config["ACTIVE_LOCALES"] = valid
            else:
                print(f"Invalid locale(s): {codes}. Valid: {list(LOCALE_MATRIX.keys())}")
                sys.exit(1)
        elif arg in ("--seed-matrix", "--seed"):
            config["SEED_MATRIX"] = True
        elif arg in ("--skip-seed", "--no-auto"):
            config["SKIP_SEED"] = True
        i += 1
    return config


if __name__ == "__main__":
    cfg = parse_args()
    bot = OmniBotV2(cfg)
    bot.run()
