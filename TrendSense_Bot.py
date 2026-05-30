#!/usr/bin/env python3
"""
TrendSense_Bot.py — Intelligent Trend Sense & Logical Matrix Router
===============================================================
Architecture:
  1. DUAL INTAKE: Google Trends RSS + Trends24 (Twitter)
  2. ENTITY CLASSIFICATION: Rule A (Local) / Rule B (Global) / Rule C (National)
  3. SMART TEMPLATES: Intent-matched question/pattern engine
  4. AUTO-INDEXING: Google Indexing API push on publish
  5. API SYNC: Laravel Omni Portal content node ingestion

Usage:
  python TrendSense_Bot.py --dry-run
  python TrendSense_Bot.py --quick
  python TrendSense_Bot.py --resume
"""

import hashlib
import hmac
import io
import json
import logging
import os
import random
import re
import string
import sys
import time
import traceback
import urllib.parse
from collections import OrderedDict
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime, timedelta
from typing import Any, Dict, List, Optional, Tuple
from xml.etree import ElementTree

import requests
from dotenv import load_dotenv
from slugify import slugify
import unidecode

load_dotenv()

# ──────────────────────────────────────────────────────────────
# LOGGING (UTF-8 safe for Windows console)
# ──────────────────────────────────────────────────────────────
if sys.platform == "win32":
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding="utf-8", errors="replace")
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding="utf-8", errors="replace")

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s - %(message)s",
    handlers=[
        logging.StreamHandler(sys.stdout),
        logging.FileHandler("trendsense_bot.log", encoding="utf-8"),
    ],
)
log = logging.getLogger("TrendSense")

# ──────────────────────────────────────────────────────────────
# CONFIG
# ──────────────────────────────────────────────────────────────
CONFIG = {
    "API_BASE_URL": os.getenv("OMNI_BASE_URL", "http://127.0.0.1:8000"),
    "API_TOKEN": os.getenv("OMNI_API_TOKEN", ""),
    "GOOGLE_INDEXING_SERVICE_ACCOUNT": os.getenv(
        "GOOGLE_INDEXING_SERVICE_ACCOUNT_PATH",
        "storage/google-service-account.json",
    ),
    "INDEXNOW_KEY": os.getenv("INDEXNOW_KEY", ""),
    "INDEXNOW_HOST": os.getenv("SITE_URL", "https://omviportal.com"),
    "SITE_URL": "https://omviportal.com",
    "MAX_WORKERS": int(os.getenv("TRENDSENSE_WORKERS", "5")),
    "BATCH_SIZE": int(os.getenv("TRENDSENSE_BATCH_SIZE", "50")),
    "MAX_RETRIES": 3,
    "REQUEST_TIMEOUT": 30,
    "TRENDS24_URL": "https://trends24.in/turkey",
    "GOOGLE_TRENDS_RSS": "https://trends.google.com/trends/trendingsearches/daily/rss?geo=TR",
    "WIKIPEDIA_API": "https://en.wikipedia.org/w/api.php",
    "WIKIDATA_API": "https://www.wikidata.org/wiki/Special:EntityData",
    "RESUME_FILE": "trendsense_checkpoint.json",
    "LANGUAGE": "tr",
    "SOURCE": "trendsense",
}

# ──────────────────────────────────────────────────────────────
# TURKISH ADMINISTRATIVE DATA
# ──────────────────────────────────────────────────────────────

import sys
sys.path.insert(0, os.path.join(os.path.dirname(__file__), "bots"))
from core.data.turkiye_il_ilce import TURKIYE_IL_ILCE

TURKISH_PROVINCES = list(TURKIYE_IL_ILCE.keys())
TURKISH_DISTRICTS: Dict[str, List[str]] = dict(TURKIYE_IL_ILCE)

ALL_LOCATIONS: List[str] = []
ALL_LOCATIONS.extend(TURKISH_PROVINCES)
for province, districts in TURKISH_DISTRICTS.items():
    for d in districts:
        label = f"{province} {d}"
        if label not in ALL_LOCATIONS:
            ALL_LOCATIONS.append(label)


# ──────────────────────────────────────────────────────────────
# LOCAL-SENSITIVE KEYWORDS (RULE A TRIGGER)
# ──────────────────────────────────────────────────────────────
LOCAL_TRIGGER_KEYWORDS = [
    "deprem", "sel", "kaza", "elektrik kesintisi", "hava durumu",
    "yol durumu", "yangın", "su baskını", "heyelan", "çığ",
    "fırtına", "hortum", "dolu", "kar yağışı", "buzlanma",
    "trafik kazası", "doğalgaz patlaması", "terör saldırısı",
    "silahlı saldırı", "intihar", "kaçak göç", "operasyon",
    "narkotik", "uyuşturucu", "hırsızlık", "soygun",
    "cinayet", "trafik", "ulaşım", "otobüs kazası",
    "tren kazası", "iş kazası", "maden kazası", "göçük",
    "orman yangını", "ev yangını", "pazar yeri", "viran",
]

# ──────────────────────────────────────────────────────────────
# WIKIPEDIA-BASED CLASSIFICATION CATEGORIES
# ──────────────────────────────────────────────────────────────

CLASSIFICATION_CATEGORIES = {
    "SPORT": {
        "triggers": [
            "footballer", "basketball", "soccer", "tennis", "volleyball",
            "sports team", "football club", "athlete", "coach", "manager",
            "FIFA", "UEFA", "league", "championship", "tournament",
            "Olympic", "Grand Slam", "Formula One", "motorsport",
            "cricket", "rugby", "golf", "boxing", "MMA", "wrestling",
            "cycling", "swimming", "athletics", "sport", "game",
            "futbolcu", "basketbolcu", "spor kulübü", "milli takım",
            "antrenör", "teknik direktör", "hakem", "sporcu",
        ],
        "intent_keywords": [
            "puan durumu", "canlı skor", "maç ne zaman",
            "son maçı", "kaçıncı sırada", "şampiyonluk",
            "fikstür", "transfer", "kadro",
        ],
        "category_label": "Spor",
    },
    "PERSON": {
        "triggers": [
            "politician", "president", "prime minister", "minister",
            "senator", "governor", "mayor", "member of parliament",
            "actor", "actress", "singer", "musician", "songwriter",
            "director", "producer", "writer", "author", "poet",
            "artist", "painter", "sculptor", "architect",
            "scientist", "professor", "researcher", "physician",
            "businessperson", "entrepreneur", "philanthropist",
            "model", "television presenter", "journalist",
            "royalty", "noble", "religious leader",
            "siyasetçi", "oyuncu", "şarkıcı", "yazar",
            "bilim insanı", "gazeteci", "sanatçı",
        ],
        "intent_keywords": [
            "kimdir", "evli mi", "nereli", "boyu kaç",
            "kilosu kaç", "yaşı kaç", "mesleği ne",
            "serveti ne kadar", "eşi kim", "çocuğu var mı",
            "nerede doğdu", "kaç yaşında",
        ],
        "category_label": "Kişi",
    },
    "FILM_ENTERTAINMENT": {
        "triggers": [
            "film", "movie", "television series", "TV series",
            "soap opera", "documentary", "animation", "anime",
            "netflix", "disney", "hbo", "episode", "season",
            "trailer", "premiere", "release date", "box office",
            "soundtrack", "cinema", "theatre", "musical",
            "dizi", "sinema filmi", "belgesel", "program",
        ],
        "intent_keywords": [
            "ne zaman çıkacak", "konusu ne", "oyuncuları kimler",
            "kaç sezon", "IMDB puanı", "nerede izlenir",
            "yorumları", "fragman", "kaç bölüm",
        ],
        "category_label": "Film/Dizi",
    },
    "BRAND_TECH": {
        "triggers": [
            "company", "corporation", "brand", "startup",
            "smartphone", "iphone", "samsung", "xiaomi",
            "AI", "artificial intelligence", "software", "app",
            "electric vehicle", "EV", "tesla", "technology",
            "social media", "platform", "e-commerce",
            "marka", "şirket", "telefon", "bilgisayar",
            "teknoloji", "uygulama", "yapay zeka",
        ],
        "intent_keywords": [
            "fiyatı ne kadar", "özellikleri neler",
            "yorumları", "nerede üretiliyor",
            "hisse fiyatı", "kaç çalışanı var",
            "kurucusu kim", "merkezi nerede",
        ],
        "category_label": "Marka/Teknoloji",
    },
    "HEALTH": {
        "triggers": [
            "disease", "illness", "virus", "vaccine", "treatment",
            "hospital", "surgery", "medicine", "drug", "therapy",
            "cancer", "COVID", "pandemic", "epidemic",
            "symptom", "diagnosis", "health",
            "hastalık", "virüs", "aşı", "tedavi", "ilaç",
            "sağlık", "hastane", "doktor", "ameliyat",
            "koronavirüs", "grip", "nezle", "kanser",
        ],
        "intent_keywords": [
            "belirtileri neler", "tedavisi var mı",
            "bulaşıcı mı", "korunma yolları",
            "hangi hastanede", "randevu nasıl alınır",
        ],
        "category_label": "Sağlık",
    },
    "POLITICS": {
        "triggers": [
            "election", "president", "parliament", "government",
            "law", "constitution", "referendum", "party",
            "protest", "demonstration", "sanction", "policy",
            "seçim", "cumhurbaşkanı", "milletvekili",
            "siyasi parti", "kanun", "anayasa", "meclis",
            "hükümet", "bakan", "belediye başkanı",
        ],
        "intent_keywords": [
            "son durum ne", "anket sonuçları",
            "kaç oy aldı", "açıklama yaptı mı",
            "ne zaman seçim", "görev süresi ne kadar",
        ],
        "category_label": "Politika",
    },
    "NATURE_DISASTER": {
        "triggers": [
            "earthquake", "deprem", "tsunami", "flood", "sel",
            "wildfire", "yangın", "hurricane", "kasırga",
            "tornado", "hortum", "avalanche", "çığ",
            "landslide", "heyelan", "eruption", "volcano",
            "storm", "fırtına", "drought", "kuraklık",
        ],
        "intent_keywords": [
            "nerede oldu", "büyüklüğü kaç", "hissedildi mi",
            "can kaybı var mı", "hasar durumu",
            "yardım nasıl yapılır", "son depremler",
            "kaç şiddetinde", "derinliği ne kadar",
        ],
        "category_label": "Doğal Afet",
    },
    "ECONOMY": {
        "triggers": [
            "economy", "inflation", "interest rate", "stock market",
            "exchange rate", "dollar", "euro", "gold", "bitcoin",
            "cryptocurrency", "budget", "tax", "GDP", "recession",
            "ekonomi", "enflasyon", "faiz", "dolar kuru",
            "euro kuru", "altın fiyatı", "borsa", "kripto",
            "vergi", "asgari ücret", "emekli maaşı",
        ],
        "intent_keywords": [
            "ne kadar oldu", "kaç TL", "yorumları",
            "uzman görüşü", "beklenti ne",
            "ne zaman açıklanacak",
        ],
        "category_label": "Ekonomi",
    },
    "EDUCATION": {
        "triggers": [
            "school", "university", "exam", "student", "education",
            "scholarship", "course", "lesson", "teacher",
            "okul", "üniversite", "sınav", "öğrenci",
            "eğitim", "burs", "ders", "öğretmen", "profesör",
            "YKS", "KPSS", "ALES", "TYT", "AYT", "LGS",
        ],
        "intent_keywords": [
            "sonuçları ne zaman açıklanır",
            "puanı kaç", "başvuru nasıl yapılır",
            "hangi bölüm kaç puan", "kontenjan",
            " kaç soru", "süresi ne kadar",
        ],
        "category_label": "Eğitim",
    },
}

# ──────────────────────────────────────────────────────────────
# CONTENT TEMPLATES
# ──────────────────────────────────────────────────────────────

BODY_TEMPLATES_LOCAL = [
    """{keyword} {location} için en güncel bilgileri bu sayfada bulabilirsiniz. {date} tarihi itibarıyla {location} bölgesindeki {keyword} ile ilgili tüm detayları sizler için derledik.

{category_intent_lines}

{location} ve çevresinde {keyword} konusunda yaşanan gelişmeleri yakından takip ediyoruz. Bölge halkını ilgilendiren bu önemli konuda en doğru ve güncel bilgiye ulaşmak için sayfamızı ziyaret edebilirsiniz.

SEO hedefi: {location} {keyword} aramalarında üst sıralarda yer almak için optimize edilmiş içerik.""",

    """{location} {keyword} başlığı altında {date} güncel durumu, uzman yorumları ve bölgesel etkileri hakkında kapsamlı bir değerlendirme sunuyoruz.

{category_intent_lines}

{location} sakinleri için {keyword} konusu büyük önem taşımaktadır. Bu nedenle konuyla ilgili tüm resmi açıklamaları ve gelişmeleri anlık olarak sayfamıza ekliyoruz.

{location} genelinde {keyword} ile ilgili farklı noktalardan gelen bilgileri tek bir kaynakta topluyoruz.""",

    """Son dakika: {location} {keyword} ile ilgili önemli gelişmeler yaşanıyor. {date} itibarıyla {location} bölgesindeki son durumu aktarıyoruz.

{category_intent_lines}

{location} bölgesinde {keyword} konusunda yetkililerden gelen açıklamaları ve bölge sakinlerinin deneyimlerini bu sayfada bulabilirsiniz.

Amacımız {location} ve çevre illerde yaşayan vatandaşlarımıza {keyword} hakkında en kapsamlı ve güncel bilgiyi sunmaktır.""",

    """{keyword} {location} — {date} güncel durum analizi. {location} özelinde {keyword} ile ilgili tüm verileri, istatistikleri ve gelişmeleri bu içerikte bulabilirsiniz.

{category_intent_lines}

Bu sayfa {location} için {keyword} konusunda arama yapan kullanıcılara en doğru bilgiyi sunmak amacıyla hazırlanmıştır. Konuyla ilgili resmi kaynaklardan elde edilen bilgileri düzenli olarak güncelliyoruz.

{location} {keyword} aramalarında karşılaştığınız bu sayfa, size en güncel ve güvenilir bilgiyi sunmayı hedeflemektedir.""",
]

BODY_TEMPLATES_GLOBAL = [
    """{keyword} hakkında kapsamlı ve detaylı bir inceleme. {date} itibarıyla güncellenen bu sayfada {keyword} ile ilgili merak ettiğiniz her şeyi bulabilirsiniz.

{category_intent_lines}

{keyword} konusu son dönemin en çok konuşulan başlıklarından biri haline geldi. Bu kapsamlı rehberde, konuyla ilgili bilmeniz gereken tüm detayları, uzman görüşlerini ve en son gelişmeleri bir araya getirdik.

SEO hedefi: {keyword} aramalarında en üst sıralarda yer almak için optimize edilmiş, kullanıcı odaklı içerik.""",

    """{keyword} — {date} güncel bilgiler, detaylı analiz ve kullanıcı yorumları. Bu sayfa {keyword} hakkında en kapsamlı Türkçe kaynak olmayı hedeflemektedir.

{category_intent_lines}

{keyword} ile ilgili araştırmalarınızda karşılaştığınız bu içerik, size en güncel, doğru ve detaylı bilgiyi sunmak için hazırlanmıştır. Konuyla ilgili tüm alt başlıkları, sık sorulan soruları ve merak edilen noktaları ele alıyoruz.""",

    """{keyword} hakkında her şey bu sayfada! {date} güncellemesi ile {keyword} konusunu tüm yönleriyle ele alıyoruz.

{category_intent_lines}

Bu içerik {keyword} hakkında arama yapan kullanıcıların ihtiyaç duyduğu tüm bilgileri tek bir kaynakta toplamak amacıyla oluşturulmuştur. Görsel, istatistik ve referans bilgilerle desteklenen bu kapsamlı rehberi kaçırmayın.""",
]

BODY_TEMPLATES_NATIONAL = [
    """{keyword} — {date} güncel gelişmeler ve detaylı analiz. Türkiye genelinde büyük yankı uyandıran {keyword} konusunu tüm yönleriyle ele alıyoruz.

{category_intent_lines}

{keyword} ile ilgili son dakika gelişmeleri, resmi açıklamaları ve uzman değerlendirmelerini bu sayfada bulabilirsiniz. Konuyla ilgili en kapsamlı Türkçe kaynak olmayı hedefliyoruz.""",

    """Son dakika haberi: {keyword} — Türkiye gündeminde önemli bir yer tutan {keyword} konusundaki tüm gelişmeleri anlık olarak aktarıyoruz. {date}

{category_intent_lines}

{keyword} başlığı altında yaşanan gelişmeleri yakından takip ediyor ve siz değerli okuyucularımıza en güncel bilgiyi sunuyoruz. Resmi kaynaklardan teyit edilen bilgileri sayfamızda bulabilirsiniz.""",
]

# ──────────────────────────────────────────────────────────────
# SPINTAX SYNONYM MAPS
# ──────────────────────────────────────────────────────────────

SPINTAX_MAP: Dict[str, List[str]] = {
    "en güncel": ["en son", "güncel", "en taze", "en yeni", "son"],
    "kapsamlı": ["detaylı", "geniş kapsamlı", "eksiksiz", "derinlemesine"],
    "bilgiler": ["veriler", "detaylar", "bilgileri", "haberler"],
    "bu sayfada": ["bu içerikte", "bu makalede", "burada", "bu kaynakta"],
    "bulabilirsiniz": ["ulaşabilirsiniz", "görebilirsiniz", "erişebilirsiniz", "öğrenebilirsiniz"],
    "sizler için": ["siz değerli okuyucularımız için", "sizin için", "okuyucularımız için"],
    "en doğru": ["en güvenilir", "en sağlıklı", "en kesin", "en net"],
    "önem taşımaktadır": ["önemlidir", "dikkat çekmektedir", "gündemdedir"],
    "gelişmeleri": ["yenilikleri", "son durumu", "güncellemeleri", "ilerlemeleri"],
    "sayfamızı": ["içeriğimizi", "makalemizi", "sayfamızı", "kaynağımızı"],
}

# ──────────────────────────────────────────────────────────────
# HELPER FUNCTIONS
# ──────────────────────────────────────────────────────────────

def tr_slug(text: str) -> str:
    text = unidecode.unidecode(text).lower().strip()
    text = re.sub(r"[^a-z0-9\s-]", "", text)
    text = re.sub(r"[\s]+", "-", text)
    text = re.sub(r"-+", "-", text)
    return text.strip("-")


def random_suffix(length: int = 6) -> str:
    return "".join(random.choices(string.ascii_lowercase + string.digits, k=length))


def generate_deterministic_slug(keyword: str, location: str, seed: str = "") -> str:
    base = tr_slug(f"{keyword} {location} {seed}")
    raw = f"{keyword}|{location}|{seed}|{datetime.now().strftime('%Y%m%d')}"
    uid = hashlib.md5(raw.encode()).hexdigest()[:8]
    return f"{base}-{uid}"


def spin_text(text: str) -> str:
    for key, variants in SPINTAX_MAP.items():
        chosen = random.choice(variants)
        text = text.replace(key, chosen, 1)
    return text


def today_str() -> str:
    return datetime.now().strftime("%d %B %Y")


def get_category_for_label(label: str) -> Optional[str]:
    for cat_key, cat_data in CLASSIFICATION_CATEGORIES.items():
        if cat_data["category_label"] == label:
            return cat_key
    return None


# ──────────────────────────────────────────────────────────────
# 1. TREND SOURCE — DUAL INTAKE
# ──────────────────────────────────────────────────────────────

class TrendSource:
    """Fetches trending keywords from Google Trends RSS and Trends24."""

    @staticmethod
    def fetch_google_rss(geo: str = "TR") -> List[str]:
        urls_to_try = [
            f"https://trends.google.com/trending/rss?geo={geo}",
            f"https://trends.google.com/trends/trendingsearches/daily/rss?geo={geo}",
            f"https://trends.google.com/trends/trendingsearches/daily?geo={geo}&hl=tr",
        ]
        keywords = []
        for url in urls_to_try:
            try:
                resp = requests.get(url, timeout=CONFIG["REQUEST_TIMEOUT"],
                                    headers={"User-Agent": "Mozilla/5.0"})
                resp.raise_for_status()
                content_type = resp.headers.get("Content-Type", "")
                if "xml" in content_type or url.endswith("rss"):
                    root = ElementTree.fromstring(resp.content)
                    ns = {"ht": "http://www.w3.org/2005/Atom"}
                    for item in root.iter("item"):
                        title_el = item.find("title")
                        if title_el is not None and title_el.text:
                            kw = title_el.text.strip()
                            if kw not in keywords:
                                keywords.append(kw)
                        news_title_el = item.find("ht:news_item_title", ns)
                        if news_title_el is not None and news_title_el.text:
                            kw = news_title_el.text.strip()
                            if kw not in keywords:
                                keywords.append(kw)
                else:
                    found = re.findall(
                        r'<div[^>]*class=["\']title["\'][^>]*>([^<]+)</div>',
                        resp.text,
                    )
                    for kw in found:
                        kw = kw.strip()
                        if kw and kw not in keywords:
                            keywords.append(kw)
                    found2 = re.findall(
                        r'<a[^>]*href=["\'].*?trend[^>]*>([^<]+)</a>',
                        resp.text, re.IGNORECASE,
                    )
                    for kw in found2:
                        kw = kw.strip()
                        if kw and kw not in keywords:
                            keywords.append(kw)
                if keywords:
                    break
            except Exception as e:
                log.debug(f"Google Trends URL failed ({url}): {e}")
                continue
        if keywords:
            log.info(f"Google Trends ({geo}): {len(keywords)} keywords fetched")
        return keywords

    @staticmethod
    def fetch_trends24() -> List[str]:
        urls_to_try = [
            CONFIG["TRENDS24_URL"],
            "https://trends24.in/turkey/",
            "https://trends24.in/trends/turkey/",
        ]
        keywords = set()
        for url in urls_to_try:
            try:
                resp = requests.get(
                    url,
                    timeout=CONFIG["REQUEST_TIMEOUT"],
                    headers={
                        "User-Agent": (
                            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                            "AppleWebKit/537.36 (KHTML, like Gecko) "
                            "Chrome/120.0.0.0 Safari/537.36"
                        )
                    },
                )
                resp.raise_for_status()
                html = resp.text

                patterns = [
                    r'<a[^>]*href=["\']/turkey[^>]*>([^<]+)</a>',
                    r'<li[^>]*class=["\']trend-card__item["\'][^>]*>.*?<a[^>]*>([^<]+)</a>',
                    r'class=["\']trend-card__item["\'][^>]*>.*?<a[^>]*>([^<]+)</a>',
                    r'<span[^>]*class=["\']trend-name["\'][^>]*>([^<]+)</span>',
                    r'<a[^>]*class=["\']trend-link["\'][^>]*>([^<]+)</a>',
                    r'<a[^>]*href=["\']/trends/[^>]*>([^<]+)</a>',
                    r'<a[^>]*class=["\'][^"\']*trend[^"\']*["\'][^>]*>([^<]+)</a>',
                    r'<a[^>]*class=["\']twitter-timeline-link["\'][^>]*>([^<]+)</a>',
                    r'data-trend-name=["\']([^"\']+)["\']',
                ]

                for pattern in patterns:
                    matches = re.findall(pattern, html, re.DOTALL | re.IGNORECASE)
                    for m in matches:
                        kw = m.strip()
                        if kw and len(kw) > 2 and len(kw) < 200:
                            keywords.add(kw)

                if keywords:
                    log.info(f"Trends24 ({url}): {len(keywords)} keywords")
                    break
            except Exception as e:
                log.debug(f"Trends24 URL failed ({url}): {e}")
                continue
        if not keywords:
            log.debug("Trends24: 0 keywords fetched from all URLs")
        return list(keywords)

    @staticmethod
    def fetch_google_realtime() -> List[str]:
        try:
            from pytrends.request import TrendReq
            pytrends = TrendReq(hl="tr-TR", tz=180, timeout=10)
            trending = pytrends.trending_searches(pn="turkey")
            if trending is not None and not trending.empty:
                keywords = trending[0].tolist()
                log.info(f"Google Realtime: {len(keywords)} keywords fetched")
                return keywords
        except ImportError:
            log.warning("pytrends not installed; skipping Google Realtime")
        except Exception as e:
            log.warning(f"Google Realtime fetch failed: {e}")
        return []

    @classmethod
    def fetch_all(cls) -> List[str]:
        all_kw: List[str] = []
        all_kw.extend(cls.fetch_google_rss("TR"))
        all_kw.extend(cls.fetch_google_realtime())
        all_kw.extend(cls.fetch_trends24())

        seen: set = set()
        unique: List[str] = []
        for kw in all_kw:
            normalized = kw.lower().strip()
            normalized = re.sub(r"[^\w\s]", "", normalized)
            if normalized and normalized not in seen:
                seen.add(normalized)
                unique.append(kw.strip())
        log.info(f"TrendSource total unique keywords: {len(unique)}")
        return unique


# ──────────────────────────────────────────────────────────────
# 2. ENTITY CLASSIFIER — WIKIPEDIA / WIKIDATA INTEGRATION
# ──────────────────────────────────────────────────────────────

class WikipediaClassifier:
    """Classifies keywords using Wikipedia/Wikidata APIs."""

    def __init__(self):
        self.cache: Dict[str, Optional[str]] = {}

    def _fetch_wikipedia_summary(self, keyword: str) -> Optional[str]:
        params = {
            "action": "query",
            "titles": keyword,
            "prop": "extracts|pageprops",
            "exintro": True,
            "explaintext": True,
            "format": "json",
            "redirects": 1,
            "ppprop": "wikibase_item",
        }
        try:
            resp = requests.get(
                CONFIG["WIKIPEDIA_API"],
                params=params,
                timeout=CONFIG["REQUEST_TIMEOUT"],
                headers={"User-Agent": "TrendSenseBot/1.0 (omni portal)"},
            )
            resp.raise_for_status()
            data = resp.json()
            pages = data.get("query", {}).get("pages", {})
            for page_id, page in pages.items():
                if page_id == "-1":
                    continue
                extract = page.get("extract", "")
                wikibase = page.get("pageprops", {}).get("wikibase_item", "")
                if extract or wikibase:
                    return json.dumps({
                        "extract": extract[:2000],
                        "wikibase_item": wikibase,
                        "title": page.get("title", keyword),
                    })
            return None
        except Exception as e:
            log.debug(f"Wikipedia query failed for '{keyword}': {e}")
            return None

    def _fetch_wikidata_type(self, qid: str) -> Optional[str]:
        if not qid:
            return None
        url = f"{CONFIG['WIKIDATA_API']}/{qid}.json"
        try:
            resp = requests.get(url, timeout=CONFIG["REQUEST_TIMEOUT"])
            resp.raise_for_status()
            data = resp.json()
            entity = data.get("entities", {}).get(qid, {})
            claims = entity.get("claims", {})

            instance_of = claims.get("P31", [])
            if instance_of:
                main_snak = instance_of[0].get("mainsnak", {})
                datavalue = main_snak.get("datavalue", {})
                value = datavalue.get("value", {})
                return value.get("id", None)

            subclass_of = claims.get("P279", [])
            if subclass_of:
                main_snak = subclass_of[0].get("mainsnak", {})
                datavalue = main_snak.get("datavalue", {})
                value = datavalue.get("value", {})
                return value.get("id", None)

            return None
        except Exception as e:
            log.debug(f"Wikidata query failed for {qid}: {e}")
            return None

    @staticmethod
    def _wikidata_qid_to_label(qid: str) -> Optional[str]:
        mapping = {
            "Q5": "PERSON",
            "Q4830453": "BRAND_TECH",
            "Q188509": "BRAND_TECH",
            "Q11424": "FILM_ENTERTAINMENT",
            "Q11436": "FILM_ENTERTAINMENT",
            "Q20655472": "SPORT",
            "Q7725634": "SPORT",
            "Q15944511": "SPORT",
            "Q476028": "SPORT",
            "Q20661391": "SPORT",
            "Q215627": "PERSON",
            "Q82955": "POLITICS",
            "Q1391410": "POLITICS",
            "Q10855167": "PERSON",
            "Q21191270": "PERSON",
            "Q33999": "HEALTH",
            "Q12136": "HEALTH",
            "Q167745": "HEALTH",
            "Q189655": "EDUCATION",
            "Q3918": "EDUCATION",
            "Q16521": "NATURE_DISASTER",
            "Q11364": "ECONOMY",
        }
        return mapping.get(qid)

    def classify(self, keyword: str) -> Tuple[Optional[str], str]:
        """Returns (category_key, confidence_reason) or (None, reason)."""
        normalized = keyword.lower().strip()
        if normalized in self.cache:
            return CLASSIFICATION_CATEGORIES.get(self.cache[normalized]), self.cache[normalized]

        raw_result = self._fetch_wikipedia_summary(keyword)
        if raw_result is None:
            self.cache[normalized] = None
            return None, "wikipedia_no_result"

        try:
            parsed = json.loads(raw_result)
            extract = parsed.get("extract", "").lower()
            qid = parsed.get("wikibase_item", "")

            wikidata_label = None
            if qid:
                wd_type = self._fetch_wikidata_type(qid)
                if wd_type:
                    wikidata_label = self._wikidata_qid_to_label(wd_type)

            for cat_key, cat_data in CLASSIFICATION_CATEGORIES.items():
                for trigger in cat_data["triggers"]:
                    if trigger.lower() in extract:
                        self.cache[normalized] = cat_key
                        return cat_key, f"wikipedia_match:{trigger}"

            if wikidata_label:
                self.cache[normalized] = wikidata_label
                return wikidata_label, f"wikidata_match:{qid}"

            self.cache[normalized] = None
            return None, "no_classification_match"

        except Exception as e:
            log.debug(f"Classification parse error for '{keyword}': {e}")
            self.cache[normalized] = None
            return None, f"parse_error:{e}"


class EntityClassifier:
    """Main classifier engine — Rule A, Rule B, Rule C logic."""

    def __init__(self):
        self.wikipedia = WikipediaClassifier()

    def is_local_trigger(self, keyword: str) -> bool:
        kw_lower = keyword.lower()
        for trigger in LOCAL_TRIGGER_KEYWORDS:
            if trigger in kw_lower:
                return True
        return False

    def classify(self, keyword: str) -> Dict[str, Any]:
        """
        Returns:
          {
            "keyword": str,
            "rule": "A" | "B" | "C",
            "category": str | None,
            "category_label": str | None,
            "is_local": bool,
            "intent_keywords": list[str],
            "reason": str,
          }
        """
        kw_lower = keyword.lower()

        if self.is_local_trigger(keyword):
            category = "NATURE_DISASTER"
            cat_data = CLASSIFICATION_CATEGORIES.get(category)
            return {
                "keyword": keyword,
                "rule": "A",
                "category": category,
                "category_label": cat_data["category_label"] if cat_data else "Yerel Olay",
                "is_local": True,
                "intent_keywords": cat_data["intent_keywords"] if cat_data else [],
                "reason": f"rule_a:local_trigger_detected",
            }

        cat_key, reason = self.wikipedia.classify(keyword)
        if cat_key:
            cat_data = CLASSIFICATION_CATEGORIES.get(cat_key, {})
            is_local = cat_key in ("NATURE_DISASTER", "SPORT", "ECONOMY", "HEALTH")
            rule = "A" if is_local else "B"
            return {
                "keyword": keyword,
                "rule": rule,
                "category": cat_key,
                "category_label": cat_data.get("category_label", "Global"),
                "is_local": is_local,
                "intent_keywords": cat_data.get("intent_keywords", []),
                "reason": reason,
            }

        return {
            "keyword": keyword,
            "rule": "C",
            "category": None,
            "category_label": "Ulusal Olay",
            "is_local": False,
            "intent_keywords": [],
            "reason": "rule_c:unidentified_national_event",
        }


# ──────────────────────────────────────────────────────────────
# 3. SMART TEMPLATE ENGINE — CONTENT MATRIX
# ──────────────────────────────────────────────────────────────

class ContentMatrix:
    """Generates SEO-optimized content nodes based on classification."""

    def __init__(self, classifier: EntityClassifier):
        self.classifier = classifier

    def _build_intent_lines(self, intent_keywords: List[str], keyword: str, location: str = "") -> str:
        if not intent_keywords:
            return ""
        selected = random.sample(
            intent_keywords,
            min(random.randint(2, 4), len(intent_keywords)),
        )
        loc = location if location else keyword
        lines = []
        for intent in selected:
            full_q = f"{keyword} {intent}"
            if location:
                full_q = f"{keyword} {location} {intent}"
            lines.append(f"- **{full_q}** hakkında detaylı bilgi")
        return "\n".join(lines)

    def _generate_body(self, keyword: str, location: str, intent_lines: str,
                       templates: List[str]) -> str:
        tmpl = random.choice(templates)
        # str.format() yerine manuel replace — Arapça/Kiril/özel karakterlerde güvenli
        body = tmpl
        body = body.replace("{keyword}", keyword)
        body = body.replace("{location}", location if location else keyword)
        body = body.replace("{date}", today_str())
        body = body.replace("{category_intent_lines}", intent_lines)
        body = spin_text(body)
        return body

    def _safe_body(self, keyword: str, location: str, intent_lines: str,
                   templates: List[str]) -> str:
        body = self._generate_body(keyword, location, intent_lines, templates)
        if not body or len(body.strip()) < 50:
            loc_part = f" {location}" if location else ""
            body = (
                f"{keyword}{loc_part} — {today_str()} güncel durum hakkında "
                f"kapsamlı bilgiler. Bu sayfada {keyword}{loc_part} ile ilgili "
                f"en son gelişmeleri, detaylı analizleri ve uzman yorumlarını "
                f"bulabilirsiniz.\n\n"
                f"{intent_lines}\n\n"
                f"{keyword}{loc_part} konusuyla ilgili tüm güncel bilgiler "
                f"için sayfamızı ziyaret edin. En doğru ve güvenilir bilgi "
                f"kaynağınız olmayı hedefliyoruz."
            )
        return body

    def generate_local_matrix(self, classification: Dict[str, Any]) -> List[Dict[str, Any]]:
        keyword = classification["keyword"]
        intent_kw = classification.get("intent_keywords", [])
        cat_label = classification.get("category_label", "Yerel Olay")
        cat_slug = tr_slug(cat_label)
        nodes: List[Dict[str, Any]] = []

        for location in ALL_LOCATIONS:
            loc_slug = tr_slug(location)
            intent_lines = self._build_intent_lines(intent_kw, keyword, location)
            title = f"{keyword} {location} — {today_str()} Güncel Durum"
            slug = generate_deterministic_slug(keyword, loc_slug)
            body_content = self._safe_body(keyword, location, intent_lines, BODY_TEMPLATES_LOCAL)
            meta_desc = (
                f"{keyword} {location} için en güncel bilgiler. "
                f"{today_str()} itibarıyla {location} bölgesinde {keyword} "
                f"ile ilgili tüm detaylar."
            )

            nodes.append({
                "title": title,
                "slug": slug,
                "body_content": body_content,
                "meta_description": meta_desc[:320],
                "taxonomy_slug": cat_slug,
                "taxonomy_label": cat_label,
                "location_slug": loc_slug,
                "location_label": location,
                "language": CONFIG["LANGUAGE"],
                "is_restricted_content": False,
                "source": CONFIG["SOURCE"],
            })

        return nodes

    def generate_global_article(self, classification: Dict[str, Any]) -> List[Dict[str, Any]]:
        keyword = classification["keyword"]
        intent_kw = classification.get("intent_keywords", [])
        cat_label = classification.get("category_label", "Global")
        cat_slug = tr_slug(cat_label)
        intent_lines = self._build_intent_lines(intent_kw, keyword)
        title = f"{keyword} — {today_str()} Kapsamlı Rehber ve Güncel Bilgiler"
        slug = generate_deterministic_slug(keyword, "global")
        body_content = self._safe_body(keyword, "", intent_lines, BODY_TEMPLATES_GLOBAL)
        meta_desc = (
            f"{keyword} hakkında en kapsamlı Türkçe kaynak. "
            f"{today_str()} itibarıyla güncellenen bu sayfada "
            f"{keyword} ile ilgili merak ettiğiniz her şey."
        )

        return [{
            "title": title,
            "slug": slug,
            "body_content": body_content,
            "meta_description": meta_desc[:320],
            "taxonomy_slug": cat_slug,
            "taxonomy_label": cat_label,
            "language": CONFIG["LANGUAGE"],
            "is_restricted_content": False,
            "source": CONFIG["SOURCE"],
        }]

    def generate_national_article(self, classification: Dict[str, Any]) -> List[Dict[str, Any]]:
        keyword = classification["keyword"]
        intent_lines = self._build_intent_lines(
            classification.get("intent_keywords", []),
            keyword,
        )
        title = f"{keyword} — {today_str()} Son Dakika Gelişmeleri ve Detaylar"
        slug = generate_deterministic_slug(keyword, "national")
        body_content = self._safe_body(keyword, "Türkiye", intent_lines, BODY_TEMPLATES_NATIONAL)
        meta_desc = (
            f"{keyword} ile ilgili son dakika gelişmeleri. "
            f"{today_str()} itibarıyla Türkiye gündemindeki "
            f"{keyword} konusunda tüm detaylar."
        )

        return [{
            "title": title,
            "slug": slug,
            "body_content": body_content,
            "meta_description": meta_desc[:320],
            "taxonomy_slug": "ulusal-olay",
            "taxonomy_label": "Ulusal Olay",
            "location_slug": "turkiye",
            "location_label": "Türkiye",
            "language": CONFIG["LANGUAGE"],
            "is_restricted_content": False,
            "source": CONFIG["SOURCE"],
        }]

    def generate(self, classification: Dict[str, Any]) -> List[Dict[str, Any]]:
        rule = classification["rule"]
        if rule == "A":
            return self.generate_local_matrix(classification)
        elif rule == "B":
            return self.generate_global_article(classification)
        else:
            return self.generate_national_article(classification)


# ──────────────────────────────────────────────────────────────
# 4. AUTO-INDEXING API — GOOGLE INDEXING
# ──────────────────────────────────────────────────────────────

class GoogleIndexer:
    """Google Indexing API client. Pushes URL_UPDATED notifications."""

    def __init__(self):
        self.enabled = False
        self.service_account_path = CONFIG["GOOGLE_INDEXING_SERVICE_ACCOUNT"]

    def _setup(self):
        self.enabled = False
        if not os.path.exists(self.service_account_path):
            log.warning(
                f"Google service account not found at {self.service_account_path}. "
                "Indexing disabled."
            )
            return

        try:
            from google.oauth2 import service_account
            from googleapiclient.discovery import build

            self.credentials = service_account.Credentials.from_service_account_file(
                self.service_account_path,
                scopes=["https://www.googleapis.com/auth/indexing"],
            )
            self.service = build("indexing", "v3", credentials=self.credentials)
            self.enabled = True
            log.info("Google Indexing API initialized successfully")
        except ImportError:
            log.warning(
                "google-auth / google-api-python-client not installed. "
                "Indexing disabled. Install: pip install google-auth google-api-python-client"
            )
        except Exception as e:
            log.warning(f"Google Indexing API setup failed: {e}")

    def notify(self, url: str, action: str = "URL_UPDATED") -> bool:
        if not self.enabled:
            log.debug(f"Indexing skipped (disabled) for: {url}")
            return False
        try:
            body = {"url": url, "type": action}
            response = self.service.urlNotifications().publish(body=body).execute()
            log.info(f"Indexing notification sent: {url} -> {response.get('urlNotificationMetadata', {})}")
            return True
        except Exception as e:
            log.error(f"Indexing API error for {url}: {e}")
            return False

    def notify_batch(self, urls: List[str], action: str = "URL_UPDATED") -> int:
        if not self.enabled:
            return 0
        success = 0
        for url in urls:
            if self.notify(url, action):
                success += 1
        return success


# ──────────────────────────────────────────────────────────────
# 4B. INDEXNOW NOTIFIER — Bing / Yandex / Seznam
# ──────────────────────────────────────────────────────────────

class IndexNowNotifier:
    """IndexNow protocol notifier. Pushes URLs to Bing, Yandex, Seznam etc."""

    INDEXNOW_ENDPOINT = "https://api.indexnow.org/indexnow"
    MAX_BATCH_SIZE = 10000

    def __init__(self):
        self.key = CONFIG["INDEXNOW_KEY"]
        self.host = CONFIG["INDEXNOW_HOST"].rstrip("/").replace("https://", "").replace("http://", "")
        self.enabled = bool(self.key)
        if not self.enabled:
            log.warning("IndexNow key not set. IndexNow disabled.")

    def notify_batch(self, urls: List[str]) -> int:
        if not self.enabled:
            log.debug("IndexNow skipped (disabled)")
            return 0
        success = 0
        for i in range(0, len(urls), self.MAX_BATCH_SIZE):
            batch = urls[i : i + self.MAX_BATCH_SIZE]
            payload = {
                "host": self.host,
                "key": self.key,
                "urlList": batch,
            }
            try:
                resp = requests.post(
                    self.INDEXNOW_ENDPOINT,
                    json=payload,
                    timeout=CONFIG["REQUEST_TIMEOUT"],
                    headers={"Content-Type": "application/json"},
                )
                if resp.status_code in (200, 202):
                    success += len(batch)
                    log.info(f"IndexNow: {len(batch)} URLs notified (HTTP {resp.status_code})")
                else:
                    log.error(f"IndexNow API error {resp.status_code}: {resp.text[:300]}")
            except Exception as e:
                log.error(f"IndexNow request failed: {e}")
        return success


# ──────────────────────────────────────────────────────────────
# 5. API CLIENT — LARAVEL OMNI PORTAL
# ──────────────────────────────────────────────────────────────

class ApiClient:
    """Ingests content nodes into the Laravel Omni Portal backend."""

    def __init__(self):
        self.base_url = CONFIG["API_BASE_URL"].rstrip("/")
        self.token = CONFIG["API_TOKEN"]
        self.session = requests.Session()
        self.session.headers.update({
            "Authorization": f"Bearer {self.token}",
            "Content-Type": "application/json",
            "Accept": "application/json",
            "User-Agent": "TrendSenseBot/1.0",
        })

    def _build_payload(self, nodes: List[Dict]) -> Dict:
        taxonomies_map: Dict[str, Dict] = OrderedDict()
        locations_map: Dict[str, Dict] = OrderedDict()
        content_items = []

        for node in nodes:
            tax_slug = node.get("taxonomy_slug", "genel")
            tax_label = node.get("taxonomy_label", tax_slug.replace("-", " ").title())
            loc_slug = node.get("location_slug", "")
            loc_label = node.get("location_label", loc_slug.replace("-", " ").title() if loc_slug else "")

            if tax_slug not in taxonomies_map:
                taxonomies_map[tax_slug] = {"slug": tax_slug, "name": tax_label}
            if loc_slug and loc_slug not in locations_map:
                locations_map[loc_slug] = {"slug": loc_slug, "name": loc_label}

            item = {
                "title": node["title"],
                "slug": node["slug"],
                "body_content": node.get("body_content") or f"{node['title']} hakkında güncel bilgiler.",
                "meta_description": node.get("meta_description", "")[:320],
                "taxonomy_slug": tax_slug,
                "is_restricted_content": node.get("is_restricted_content", False),
            }
            if loc_slug:
                item["location_slug"] = loc_slug

            content_items.append(item)

        return {
            "content_nodes": content_items,
            "taxonomies": list(taxonomies_map.values()),
            "locations": list(locations_map.values()),
        }

    def ingest(self, nodes: List[Dict]) -> bool:
        if not nodes:
            return True
        payload = self._build_payload(nodes)
        url = f"{self.base_url}/api/v1/ingest"

        for attempt in range(CONFIG["MAX_RETRIES"]):
            try:
                resp = self.session.post(
                    url,
                    json=payload,
                    timeout=CONFIG["REQUEST_TIMEOUT"],
                )
                if resp.status_code in (200, 201, 202):
                    log.info(
                        f"Ingested {len(nodes)} nodes "
                        f"({len(payload['taxonomies'])} tax, "
                        f"{len(payload['locations'])} loc)"
                    )
                    return True
                elif resp.status_code == 429:
                    wait = 2 ** (attempt + 2)
                    log.warning(f"Rate limited. Waiting {wait}s...")
                    time.sleep(wait)
                elif resp.status_code >= 500:
                    wait = 2 ** (attempt + 1)
                    log.warning(
                        f"Server error {resp.status_code}. "
                        f"Retry {attempt+1}/{CONFIG['MAX_RETRIES']} in {wait}s"
                    )
                    time.sleep(wait)
                else:
                    log.error(
                        f"API error {resp.status_code}: "
                        f"{resp.text[:500]}"
                    )
                    return False
            except requests.exceptions.ConnectionError as e:
                wait = 2 ** (attempt + 1)
                log.warning(f"Connection error: {e}. Retry in {wait}s")
                time.sleep(wait)
            except Exception as e:
                log.error(f"Unexpected API error: {e}")
                return False

        log.error(f"Failed to ingest {len(nodes)} nodes after {CONFIG['MAX_RETRIES']} retries")
        return False

    def health_check(self) -> bool:
        try:
            resp = self.session.get(
                f"{self.base_url}/api/health",
                timeout=10,
            )
            return resp.status_code == 200
        except Exception:
            return False


# ──────────────────────────────────────────────────────────────
# CHECKPOINT / RESUME
# ──────────────────────────────────────────────────────────────

class Checkpoint:
    """Tracks processed keywords to enable resume capability."""

    def __init__(self, path: str = CONFIG["RESUME_FILE"]):
        self.path = path
        self.data: Dict[str, Any] = self._load()

    def _load(self) -> Dict:
        if os.path.exists(self.path):
            try:
                with open(self.path, "r", encoding="utf-8") as f:
                    return json.load(f)
            except Exception:
                return {"processed": [], "failed": [], "stats": {}}
        return {"processed": [], "failed": [], "stats": {}}

    def save(self):
        try:
            with open(self.path, "w", encoding="utf-8") as f:
                json.dump(self.data, f, ensure_ascii=False, indent=2)
        except Exception as e:
            log.error(f"Checkpoint save failed: {e}")

    @staticmethod
    def _today() -> str:
        return datetime.now().strftime("%Y-%m-%d")

    def is_processed(self, keyword: str) -> bool:
        kw = keyword.lower().strip()
        today = self._today()
        for entry in self.data.get("processed", []):
            if isinstance(entry, str):
                if entry == kw:
                    return True
            elif isinstance(entry, dict):
                if entry.get("keyword") == kw and entry.get("date") == today:
                    return True
        return False

    def mark_processed(self, keyword: str, stats: Optional[Dict] = None):
        kw = keyword.lower().strip()
        today = self._today()
        entry = {"keyword": kw, "date": today}
        if entry not in self.data["processed"]:
            self.data["processed"].append(entry)
        if stats:
            self.data["stats"][kw] = stats
        self.data.setdefault("failed", [])
        self.save()

    def mark_failed(self, keyword: str, reason: str = ""):
        kw = keyword.lower().strip()
        entry = {"keyword": kw, "reason": reason, "time": datetime.now().isoformat()}
        if entry not in self.data["failed"]:
            self.data["failed"].append(entry)
        self.save()

    def get_remaining(self, keywords: List[str]) -> List[str]:
        return [kw for kw in keywords if not self.is_processed(kw)]


# ──────────────────────────────────────────────────────────────
# 6. MAIN ORCHESTRATOR
# ──────────────────────────────────────────────────────────────

class TrendSenseBot:
    """Intelligent Trend Sense & Logical Matrix Router — Main Orchestrator."""

    def __init__(self, dry_run: bool = False, quick: bool = False, resume: bool = False):
        self.dry_run = dry_run
        self.quick = quick
        self.resume_mode = resume
        self.checkpoint = Checkpoint()
        self.classifier = EntityClassifier()
        self.matrix = ContentMatrix(self.classifier)
        self.api = ApiClient()
        self.indexer = GoogleIndexer()
        self.indexnow = IndexNowNotifier()
        self.stats = {
            "fetched": 0,
            "classified": 0,
            "rule_a": 0,
            "rule_b": 0,
            "rule_c": 0,
            "generated_nodes": 0,
            "ingested": 0,
            "indexed_google": 0,
            "indexed_indexnow": 0,
            "failed": 0,
            "skipped": 0,
        }

    def run(self):
        log.info("=" * 60)
        log.info("TREND SENSE BOT STARTED")
        log.info(f"Mode: {'DRY RUN' if self.dry_run else 'LIVE'}")
        log.info(f"Quick: {self.quick}")
        log.info(f"Resume: {self.resume_mode}")
        log.info("=" * 60)

        if not self.dry_run:
            healthy = self.api.health_check()
            if not healthy:
                log.warning("API health check failed. Continuing anyway...")
            else:
                log.info("API health check OK")

        log.info("Phase 1/4: FETCHING TRENDS...")
        keywords = TrendSource.fetch_all()
        self.stats["fetched"] = len(keywords)
        log.info(f"Fetched {len(keywords)} trending keywords")

        if not keywords:
            log.warning("No trending keywords found. Using fallback sample...")
            keywords = [
                "deprem", "Cristiano Ronaldo", "enflasyon oranı",
                "Messi", "seçim sonuçları", "hava durumu İstanbul",
                "Altın fiyatı", "Dolar kuru", "Trendyol",
                "yol durumu Ankara", "yangın Antalya", "Kılıçdaroğlu",
                "MasterChef", "YKS sınavı", "asgari ücret",
            ]

        if self.quick:
            keywords = keywords[:10]
            log.info(f"Quick mode: limited to {len(keywords)} keywords")

        if self.resume_mode:
            remaining = self.checkpoint.get_remaining(keywords)
            skipped = len(keywords) - len(remaining)
            self.stats["skipped"] = skipped
            keywords = remaining
            log.info(f"Resume mode: {skipped} already processed, {len(keywords)} remaining")

        log.info(f"Processing {len(keywords)} keywords")
        log.info("Phase 2/4: CLASSIFYING KEYWORDS...")

        classifications = []
        for kw in keywords:
            if self.resume_mode and self.checkpoint.is_processed(kw):
                self.stats["skipped"] += 1
                continue
            try:
                cls_result = self.classifier.classify(kw)
                classifications.append(cls_result)
                self.stats["classified"] += 1
                rule = cls_result["rule"]
                if rule == "A":
                    self.stats["rule_a"] += 1
                elif rule == "B":
                    self.stats["rule_b"] += 1
                else:
                    self.stats["rule_c"] += 1
                log.info(
                    f"  [{rule}] {kw} -> "
                    f"{cls_result.get('category_label', 'N/A')} "
                    f"({cls_result['reason']})"
                )
            except Exception as e:
                log.error(f"Classification failed for '{kw}': {e}")
                self.checkpoint.mark_failed(kw, str(e))
                self.stats["failed"] += 1

        if not classifications:
            log.warning("No keywords could be classified. Exiting.")
            self._print_stats()
            return

        log.info("Phase 3/4: GENERATING CONTENT MATRIX...")
        all_nodes: List[Dict] = []
        for cls in classifications:
            try:
                nodes = self.matrix.generate(cls)
                all_nodes.extend(nodes)
                self.stats["generated_nodes"] += len(nodes)
                log.info(
                    f"  Generated {len(nodes)} nodes for "
                    f"'{cls['keyword']}' (Rule {cls['rule']})"
                )
            except Exception as e:
                log.error(f"Content generation failed for '{cls['keyword']}': {e}")
                traceback.print_exc()
                self.checkpoint.mark_failed(cls["keyword"], str(e))
                self.stats["failed"] += 1

        log.info(f"Total content nodes generated: {len(all_nodes)}")
        if self.dry_run:
            log.info("DRY RUN: Skipping API ingestion and indexing.")
            self._print_sample_nodes(all_nodes[:3])
            self._print_stats()
            return

        log.info("Phase 4/4: INGESTING INTO API + INDEXING...")
        self._ingest_nodes(all_nodes)

        for cls in classifications:
            self.checkpoint.mark_processed(cls["keyword"], {"node_count": self.stats["generated_nodes"]})

        self._print_stats()
        log.info("TREND INTELLIGENCE ENGINE IS ACTIVE")

    def _ingest_nodes(self, all_nodes: List[Dict]):
        batch_size = CONFIG["BATCH_SIZE"]
        batches = [
            all_nodes[i : i + batch_size]
            for i in range(0, len(all_nodes), batch_size)
        ]
        log.info(f"Ingesting {len(all_nodes)} nodes in {len(batches)} batches")

        all_urls: List[str] = []
        batch_num = 0

        with ThreadPoolExecutor(max_workers=CONFIG["MAX_WORKERS"]) as executor:
            futures = {}
            for batch in batches:
                batch_num += 1
                future = executor.submit(self.api.ingest, batch)
                futures[future] = (batch_num, batch)

            for future in as_completed(futures):
                bnum, batch = futures[future]
                try:
                    success = future.result()
                    if success:
                        self.stats["ingested"] += len(batch)
                        for node in batch:
                            slug = node["slug"]
                            url = f"{CONFIG['SITE_URL']}/{slug}"
                            all_urls.append(url)
                    else:
                        self.stats["failed"] += len(batch)
                        log.error(f"Batch {bnum} ingestion failed")
                except Exception as e:
                    self.stats["failed"] += len(batch)
                    log.error(f"Batch {bnum} error: {e}")

        if all_urls:
            if self.indexer.enabled:
                indexed_g = self.indexer.notify_batch(all_urls)
                self.stats["indexed_google"] = indexed_g
                log.info(f"Google Indexing: {indexed_g}/{len(all_urls)} URLs")
            else:
                log.info(f"Google Indexing disabled. Skipping {len(all_urls)} URLs.")
            indexed_in = self.indexnow.notify_batch(all_urls)
            self.stats["indexed_indexnow"] = indexed_in

    def _print_sample_nodes(self, samples: List[Dict]):
        log.info("--- SAMPLE NODES (DRY RUN) ---")
        for i, node in enumerate(samples[:3], 1):
            log.info(f"  Sample {i}:")
            log.info(f"    Title: {node['title']}")
            log.info(f"    Slug: {node['slug']}")
            log.info(f"    Taxonomy: {node.get('taxonomy_label', node.get('taxonomy_slug', 'N/A'))}")
            log.info(f"    Location: {node.get('location_label', node.get('location_slug', ''))}")
            log.info(f"    Body preview: {node.get('body_content', '')[:200]}...")

    def _print_stats(self):
        log.info("=" * 60)
        log.info("TREND SENSE BOT — STATISTICS")
        log.info("=" * 60)
        for key, value in self.stats.items():
            label = key.replace("_", " ").title()
            log.info(f"  {label}: {value}")
        log.info("=" * 60)


# ──────────────────────────────────────────────────────────────
# CLI ENTRY POINT
# ──────────────────────────────────────────────────────────────

def main():
    import argparse

    parser = argparse.ArgumentParser(
        description="TrendSense Bot — Intelligent Trend Sense & Logical Matrix Router"
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Simulate without sending to API",
    )
    parser.add_argument(
        "--quick",
        action="store_true",
        help="Process only top 10 keywords",
    )
    parser.add_argument(
        "--resume",
        action="store_true",
        help="Skip already-processed keywords",
    )

    args = parser.parse_args()

    bot = TrendSenseBot(
        dry_run=args.dry_run,
        quick=args.quick,
        resume=args.resume,
    )
    try:
        bot.run()
    except KeyboardInterrupt:
        log.info("Interrupted by user. Saving checkpoint...")
        bot.checkpoint.save()
        bot._print_stats()
        sys.exit(0)
    except Exception as e:
        log.critical(f"Fatal error: {e}")
        traceback.print_exc()
        bot.checkpoint.save()
        sys.exit(1)


if __name__ == "__main__":
    main()