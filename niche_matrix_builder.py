"""
niche_matrix_builder.py — INFINITE SEMANTIC WORD ENGINE v2.0
=============================================================
Auto-discovers dynamic niche/keyword data via:
  1. Google Suggest API (primary) — concurrent A-Z seed discovery
  2. Category-based seeding (secondary) — seed from known service categories
  3. Offline A-Z generic vocabulary (fallback when API blocks)
  4. Round 4: Location + Keyword cross-discovery

Generates locale-specific JSON files:
  niche_matrix_TR.json, niche_matrix_EN.json, niche_matrix_AR.json, niche_matrix_RU.json

Usage:
  python niche_matrix_builder.py                    # Build all 4 locales
  python niche_matrix_builder.py --locale EN,TR     # Specific locales only
  python niche_matrix_builder.py --force            # Force rebuild even if exists
  python niche_matrix_builder.py --dry-run          # Preview without saving
  python niche_matrix_builder.py --rounds 3         # Only run Rounds 1-3 (skip R4)
  python niche_matrix_builder.py --proxies proxy.txt # Use proxy list for rotation
"""

import json
import logging
import random
import string
import sys
import time
import re
import math
from collections import defaultdict, Counter
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, List, Optional, Set, Tuple

import requests

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[logging.StreamHandler()],
)
log = logging.getLogger("niche_matrix_builder")

GOOGLE_SUGGEST_URL = "http://suggestqueries.google.com/complete/search"

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:125.0) Gecko/20100101 Firefox/125.0",
]

LOCALE_ALPHABETS: Dict[str, List[str]] = {
    "TR": list("abcçdefgğhıijklmnoöprsştuüvyz"),
    "EN": list(string.ascii_lowercase),
    "AR": list("ابتثجحخدذرزسشصضطظعغفقكلمنهوي"),
    "RU": list("абвгдеёжзийклмнопрстуфхцчшщъыьэюя"),
}

LOCALE_HL: Dict[str, str] = {
    "TR": "tr",
    "EN": "en",
    "AR": "ar",
    "RU": "ru",
}

LOCALE_GL: Dict[str, str] = {
    "TR": "tr",
    "EN": "us",
    "AR": "sa",
    "RU": "ru",
}

PROMINENT_CATEGORIES: Dict[str, List[str]] = {
    "EN": [
        "AC Repair", "HVAC", "Plumber", "Electrician", "Roofing",
        "Landscaping", "Cleaning", "Painting", "Moving", "Pest Control",
        "Carpet Cleaning", "Garage Door", "Window Replacement", "Flooring",
        "Bathroom Remodeling", "Kitchen Remodeling", "Tree Service",
        "Water Damage", "Mold Remediation", "Fence Contractor",
        "Concrete Contractor", "Masonry", "Welding", "Locksmith",
        "Appliance Repair", "Computer Repair", "Phone Repair",
    ],
    "TR": [
        "Klima Servisi", "Kombi Servisi", "Tesisatçı", "Elektrikçi",
        "Doğalgaz", "Nakliye", "Halı Yıkama", "Oto Kurtarma",
        "Boya Badana", "Güvenlik Kamerası", "Avukat", "Doktor",
        "Diş Hekimi", "Eczane", "Kuaför", "Berber", "Çilingir",
        "Böcek İlaçlama", "Koltuk Yıkama", "Mimar",
    ],
    "AR": [
        "سباكة", "كهرباء", "دهان", "تنظيف", "مكافحة حشرات",
        "نقل عفش", "صيانة تكييف", "تبريد", "صيانة سيارات",
        "محامي", "دكتور", "صيدلية", "مطعم", "فندق",
    ],
    "RU": [
        "Сантехник", "Электрик", "Отопление", "Уборка", "Ремонт",
        "Грузоперевозки", "Вентиляция", "Кондиционеры", "Автосервис",
        "Юрист", "Стоматология", "Аптека", "Парикмахерская",
    ],
}

OFFLINE_BASE_VOCABULARY: Dict[str, List[str]] = {
    "EN": [
        "AC Repair", "Appliance Repair", "Auto Parts", "Auto Repair",
        "Bakery", "Barber Shop", "Beauty Salon", "Bike Shop",
        "Cafe", "Carpenter", "Carpet Cleaning", "Car Wash", "Chiropractor",
        "Cleaning Service", "Computer Repair", "Contractor",
        "Dentist", "Dermatologist", "Dog Groomer", "Dry Cleaner",
        "Electrician", "Electronics Store", "Event Planner",
        "Fitness Center", "Flooring Contractor", "Florist", "Furniture Store",
        "Garage Door Repair", "Gardener", "General Contractor",
        "Hair Salon", "Hardware Store", "Heating Repair", "Home Inspection",
        "HVAC Contractor", "Interior Designer", "IT Support",
        "Jewelry Store", "Landscaper", "Laundromat", "Lawyer",
        "Locksmith", "Maid Service", "Massage Therapist", "Mechanic",
        "Moving Company", "Nail Salon", "Notary",
        "Optometrist", "Orthodontist",
        "Painter", "Pet Groomer", "Pet Store", "Pharmacy", "Photographer",
        "Plumber", "Real Estate Agent", "Restaurant", "Roofing Contractor",
        "Security System", "Spa", "Storage Facility",
        "Tailor", "Tattoo Shop", "Taxi Service", "Tire Shop", "Towing Service",
        "Travel Agent", "Tree Service",
        "Veterinarian", "Water Damage Restoration", "Wedding Planner",
        "Window Cleaner", "Yoga Studio",
    ],
    "TR": [
        "Klima Servisi", "Kombi Servisi", "Tesisatçı", "Elektrikçi",
        "Doğalgaz", "Nakliye", "Halı Yıkama", "Oto Kurtarma",
        "Boya Badana", "Güvenlik Kamerası", "Avukat", "Doktor",
        "Diş Hekimi", "Eczane", "Hastane", "Kuaför", "Berber",
        "Güzellik Salonu", "Spor Salonu", "Pastane", "Fırın",
        "Market", "Manav", "Kafe", "Restoran", "Otel",
        "Bilgisayar Tamiri", "Cep Telefonu Tamiri", "Beyaz Eşya Servisi",
        "Çamaşır Makinesi Servisi", "Bulaşık Makinesi Servisi",
        "Buzdolabı Servisi", "Şofben Servisi", "Su Tesisatçısı",
        "Mimar", "İç Mimar", "Peyzaj Mimarı", "Dekorasyon",
        "Taksi", "Kiralık Araç", "Lastikçi", "Oto Yıkama",
        "Veteriner", "Pet Shop", "Çiçekçi", "Kırtasiye",
        "Matbaa", "Fotoğrafçı", "Düğün Salonu", "Organizasyon Şirketi",
        "Özel Ders", "Diyetisyen", "Psikolog", "Fizyoterapist",
        "Çilingir", "Böcek İlaçlama", "Koltuk Yıkama", "Kamyonet Kiralama",
    ],
    "AR": [
        "خدمة تكييف", "سباكة", "كهرباء", "دهان", "تنظيف",
        "مكافحة حشرات", "نقل عفش", "تبريد", "صيانة سيارات",
        "بنشر", "مطعم", "كافيه", "فندق", "مستشفى",
        "صيدلية", "دكتور", "طبيب أسنان", "مختبر طبي",
        "صالة رياضة", "كوافير", "حلاق", "خياط", "صائغ",
        "سوبر ماركت", "مخبز", "معجنات", "عطارة",
        "محامي", "مهندس مدني", "مقاول عام", "نجار", "حداد",
        "مبلط", "جبس بورد", "صيانة جوال", "كمبيوتر",
        "مكتب عقارات", "تأجير سيارات", "توصيل طلبات",
        "غسيل سيارات", "زراعة", "حدادة", "نجارة",
        "عزل أسطح", "خزانات مياه", "مسبح", "حدائق",
    ],
    "RU": [
        "Кондиционеры", "Сантехник", "Электрик", "Отопление",
        "Уборка", "Ремонт квартир", "Грузоперевозки", "Вентиляция",
        "Автосервис", "Шиномонтаж", "Стоматология", "Аптека",
        "Больница", "Ветеринар", "Парикмахерская", "Маникюр",
        "Косметология", "Фитнес", "Сауна", "Ресторан",
        "Кафе", "Продукты", "Пекарня", "Магазин",
        "Стройматериалы", "Мебель", "Окна", "Двери",
        "Юрист", "Адвокат", "Бухгалтер", "Нотариус",
        "Такси", "Автомойка", "Кузовной ремонт", "Химчистка",
        "Ремонт обуви", "Ремонт часов", "Фотоуслуги",
        "Турагентство", "Гостиница", "Аренда квартир",
        "Риелтор", "Сантехника", "Электромонтаж",
    ],
}

SUFFIX_MAP: Dict[str, List[str]] = {
    "EN": [" near me", " prices", " reviews", " cost", " service", " company", " near me 2026", " contractors", " repair", " installation"],
    "TR": [" fiyatları", " servisi", " yorumları", " telefonu", " fiyat", " usta", " tamiri", " montaj", " bakım"],
    "AR": [" أسعار", " رقم", " تقييمات", " شركة", " خدمة", " فني", " تركيب", " صيانة"],
    "RU": [" цены", " отзывы", " телефон", " стоимость", " услуги", " мастер", " ремонт", " установка"],
}


def load_proxies(path: str) -> List[str]:
    try:
        text = Path(path).read_text(encoding="utf-8")
        proxies = [line.strip() for line in text.splitlines() if line.strip()]
        log.info(f"Loaded {len(proxies)} proxies from {path}")
        return proxies
    except Exception as e:
        log.warning(f"Could not load proxies from {path}: {e}")
        return []


class GoogleSuggestClient:
    """Thread-safe Google Suggest API consumer with proxy rotation and rate limiting."""

    def __init__(self, requests_per_second: float = 8.0, proxies: List[str] = None):
        self._last_call: float = 0.0
        self._min_interval = 1.0 / requests_per_second if requests_per_second > 0 else 0
        self._proxies = proxies or []
        self._proxy_index = 0
        self._session = requests.Session()
        self._session.headers.update({"User-Agent": random.choice(USER_AGENTS)})
        adapter = requests.adapters.HTTPAdapter(pool_connections=100, pool_maxsize=100)
        self._session.mount("https://", adapter)
        self._session.mount("http://", adapter)
        self._consecutive_failures = 0
        self._total_calls = 0
        self._successful_calls = 0

    def _rotate_headers(self):
        self._session.headers.update({"User-Agent": random.choice(USER_AGENTS)})

    def _get_proxy(self) -> Optional[Dict[str, str]]:
        if not self._proxies:
            return None
        proxy = self._proxies[self._proxy_index % len(self._proxies)]
        self._proxy_index += 1
        if "://" not in proxy:
            proxy = f"http://{proxy}"
        return {"http": proxy, "https": proxy}

    def _rate_limit(self) -> None:
        if self._min_interval > 0:
            elapsed = time.time() - self._last_call
            if elapsed < self._min_interval:
                time.sleep(self._min_interval - elapsed + random.uniform(0, 0.05))

    def fetch_suggestions(self, seed: str, hl: str, geo: str = "") -> List[str]:
        self._rate_limit()
        self._total_calls += 1

        params = {"client": "chrome", "q": seed, "hl": hl}
        if geo:
            params["gl"] = geo

        proxies = self._get_proxy()

        try:
            resp = self._session.get(GOOGLE_SUGGEST_URL, params=params, timeout=10, proxies=proxies)
            self._rotate_headers()

            if resp.status_code == 429:
                self._consecutive_failures += 1
                wait = min(2 ** self._consecutive_failures, 30)
                log.warning(f"Rate limited (429) on '{seed}'. Waiting {wait}s...")
                time.sleep(wait)
                resp = self._session.get(GOOGLE_SUGGEST_URL, params=params, timeout=10)
                if resp.status_code != 200:
                    return []
            elif resp.status_code == 200:
                self._consecutive_failures = 0
                self._successful_calls += 1
            else:
                self._consecutive_failures += 1
                return []

            data = resp.json()
            if isinstance(data, list) and len(data) > 1 and isinstance(data[1], list):
                return [item[0] for item in data[1] if isinstance(item, list) and len(item) > 0]
        except (requests.RequestException, json.JSONDecodeError, IndexError, TypeError) as e:
            self._consecutive_failures += 1
            return []
        return []

    def stats(self) -> Dict[str, Any]:
        return {
            "total_calls": self._total_calls,
            "successful": self._successful_calls,
            "failed": self._total_calls - self._successful_calls,
            "success_rate": f"{self._successful_calls / max(self._total_calls, 1) * 100:.1f}%",
        }


class OfflineVocabularyGenerator:
    """Generates A-Z base vocabulary when Google Suggest API is unreachable."""

    @staticmethod
    def to_slug(text: str) -> str:
        s = text.lower().replace(" ", "-")
        return "".join(c for c in s if c.isalnum() or c == "-").strip("-")

    @staticmethod
    def build_fallback_niches(locale_code: str) -> List[Dict[str, Any]]:
        base_words = OFFLINE_BASE_VOCABULARY.get(locale_code, OFFLINE_BASE_VOCABULARY["EN"])
        niches = []
        for word in base_words:
            slug = OfflineVocabularyGenerator.to_slug(word)
            keywords = [word]
            for sfx in SUFFIX_MAP.get(locale_code, SUFFIX_MAP["EN"]):
                keywords.append(f"{word}{sfx}")
            niches.append({
                "name": word,
                "slug": slug,
                "keywords": list(set(keywords)),
                "source": "fallback",
            })
        return niches


class CategoryBasedSeeder:
    """Seeds Google Suggest from known service categories to find niche expansions."""

    def __init__(self, client: GoogleSuggestClient, locale_code: str):
        self.client = client
        self.locale_code = locale_code
        self.hl = LOCALE_HL.get(locale_code, "en")
        self.geo = LOCALE_GL.get(locale_code, "")

    def discover_from_categories(self, seen_keywords: Set[str]) -> List[str]:
        categories = PROMINENT_CATEGORIES.get(self.locale_code, [])
        if not categories:
            return []

        log.info(f"  [{self.locale_code}] Category seeding: {len(categories)} categories...")
        seeds = []
        for cat in categories:
            for letter in random.sample(LOCALE_ALPHABETS.get(self.locale_code, list(string.ascii_lowercase)), min(3, 29)):
                seeds.append(f"{cat} {letter}")

        all_suggestions: List[str] = []
        batch_size = 25
        for i in range(0, len(seeds), batch_size):
            batch = seeds[i:i + batch_size]
            with ThreadPoolExecutor(max_workers=8) as pool:
                fut_map = {pool.submit(self.client.fetch_suggestions, seed, self.hl, self.geo): seed for seed in batch}
                for fut in as_completed(fut_map):
                    try:
                        results = fut.result()
                        for s in results:
                            if s not in seen_keywords:
                                seen_keywords.add(s)
                                all_suggestions.append(s)
                    except Exception:
                        pass

        log.info(f"  [{self.locale_code}] Category seeding: {len(all_suggestions)} new suggestions")
        return all_suggestions


class NicheCategorizer:
    """Improved niche categorization using keyword clustering."""

    STOP_WORDS_BY_LOCALE: Dict[str, Set[str]] = {
        "EN": {"the", "a", "an", "in", "on", "at", "to", "for", "of", "and", "or", "is", "are", "near", "with", "by", "my", "best", "top", "cheap", "affordable", "professional"},
        "TR": {"bir", "ve", "veya", "ile", "için", "en", "iyi", "ucuz", "profesyonel", "acil", "uygun", "güvenilir", "kaliteli", "hızlı", "nerede", "nasıl", "yakın"},
        "AR": {"في", "من", "إلى", "على", "عن", "مع", "بين", "أفضل", "أرخص", "محترف", "ممتاز", "سريع"},
        "RU": {"в", "на", "с", "от", "до", "для", "по", "из", "у", "о", "лучший", "недорогой", "дешевый", "профессиональный"},
    }

    @classmethod
    def extract_niche_name(cls, keyword: str, locale: str) -> str:
        """Extract the core niche name from a keyword phrase."""
        stop_words = cls.STOP_WORDS_BY_LOCALE.get(locale, cls.STOP_WORDS_BY_LOCALE["EN"])
        words = keyword.split()
        # Filter out stop words and short words
        meaningful = [w for w in words if w.lower() not in stop_words and len(w) > 1]
        if not meaningful:
            return words[0] if words else keyword
        # Return the first meaningful word/phrase
        if len(meaningful) >= 2 and len(meaningful[0]) <= 3:
            return " ".join(meaningful[:2])
        return meaningful[0]

    @classmethod
    def parse_into_niches(cls, suggestions: List[str], locale: str, seen_keywords: Set[str]) -> List[Dict[str, Any]]:
        niches: Dict[str, Dict[str, Any]] = {}
        for suggestion in suggestions:
            suggestion = suggestion.strip()
            if not suggestion or suggestion in seen_keywords:
                continue
            seen_keywords.add(suggestion)

            niche_name = cls.extract_niche_name(suggestion, locale)
            niche_lower = niche_name.lower()

            if niche_lower not in niches:
                slug = niche_name.lower().replace(" ", "-")
                slug = "".join(c for c in slug if c.isalnum() or c == "-").strip("-")
                niches[niche_lower] = {
                    "name": niche_name,
                    "slug": slug if slug else "general",
                    "keywords": [],
                }
            niches[niche_lower]["keywords"].append(suggestion)

        # Sort niches by keyword count (most relevant first)
        result = sorted(niches.values(), key=lambda n: len(n["keywords"]), reverse=True)
        return result


class NicheMatrixBuilder:
    """End-to-end dynamic niche matrix builder for a single locale."""

    MAX_WORKERS = 15
    SECOND_PASS_LIMIT = 80

    def __init__(self, locale_code: str, force: bool = False, max_rounds: int = 4, proxies: List[str] = None):
        self.locale_code = locale_code
        self.force = force
        self.max_rounds = max_rounds
        self.alphabet = LOCALE_ALPHABETS.get(locale_code, list(string.ascii_lowercase))
        self.hl = LOCALE_HL.get(locale_code, "en")
        self.geo = LOCALE_GL.get(locale_code, "")
        self.client = GoogleSuggestClient(proxies=proxies)
        self.output_path = Path(f"niche_matrix_{locale_code}.json")
        self.seen_keywords: Set[str] = set()
        self.categorizer = NicheCategorizer()

    def _discover_round1(self) -> List[str]:
        """Round 1: Fire all alphabet letters concurrently to Google Suggest."""
        log.info(f"  [{self.locale_code}] Round 1/4: Alphabet discovery ({len(self.alphabet)} letters)...")
        all_suggestions: List[str] = []
        with ThreadPoolExecutor(max_workers=self.MAX_WORKERS) as pool:
            fut_map = {
                pool.submit(self.client.fetch_suggestions, letter, self.hl, self.geo): letter
                for letter in self.alphabet
            }
            for fut in as_completed(fut_map):
                letter = fut_map[fut]
                try:
                    results = fut.result()
                    if results:
                        log.debug(f"    [{self.locale_code}] '{letter}' → {len(results)} suggestions")
                        for s in results:
                            if s not in self.seen_keywords:
                                self.seen_keywords.add(s)
                                all_suggestions.append(s)
                except Exception:
                    pass
        log.info(f"  [{self.locale_code}] Round 1 total: {len(all_suggestions)} unique suggestions")
        return all_suggestions

    def _discover_round2(self, round1_niches: List[Dict[str, Any]]) -> List[str]:
        """Round 2: Deep-dive into promising niches for long-tail variants."""
        candidates = sorted(round1_niches, key=lambda n: len(n["keywords"]), reverse=True)
        candidates = candidates[:self.SECOND_PASS_LIMIT]

        seeds = []
        for niche in candidates:
            for kw in niche["keywords"][:4]:
                for letter in random.sample(self.alphabet, min(6, len(self.alphabet))):
                    seeds.append(f"{kw} {letter}")

        log.info(f"  [{self.locale_code}] Round 2/4: Deepening {len(candidates)} niches ({len(seeds)} seeds)...")
        all_deep: List[str] = []
        batch_size = 30
        for i in range(0, len(seeds), batch_size):
            batch = seeds[i:i + batch_size]
            with ThreadPoolExecutor(max_workers=self.MAX_WORKERS) as pool:
                fut_map = {pool.submit(self.client.fetch_suggestions, seed, self.hl, self.geo): seed for seed in batch}
                for fut in as_completed(fut_map):
                    try:
                        results = fut.result()
                        for s in results:
                            if s not in self.seen_keywords:
                                self.seen_keywords.add(s)
                                all_deep.append(s)
                    except Exception:
                        pass
        log.info(f"  [{self.locale_code}] Round 2 total: {len(all_deep)} new long-tail suggestions")
        return all_deep

    def _discover_round3_suffixes(self, round1_niches: List[Dict[str, Any]]) -> List[str]:
        """Round 3: Append high-intent suffixes to top niches."""
        suffixes = SUFFIX_MAP.get(self.locale_code, SUFFIX_MAP["EN"])
        seeds = []
        for niche in round1_niches[:50]:
            base = niche["keywords"][0] if niche["keywords"] else niche["name"]
            for sfx in suffixes:
                seeds.append(f"{base}{sfx}")

        log.info(f"  [{self.locale_code}] Round 3/4: Intent-suffix expansion ({len(seeds)} seeds)...")
        all_suffix: List[str] = []
        batch_size = 30
        for i in range(0, len(seeds), batch_size):
            batch = seeds[i:i + batch_size]
            with ThreadPoolExecutor(max_workers=self.MAX_WORKERS) as pool:
                fut_map = {pool.submit(self.client.fetch_suggestions, seed, self.hl, self.geo): seed for seed in batch}
                for fut in as_completed(fut_map):
                    try:
                        results = fut.result()
                        for s in results:
                            if s not in self.seen_keywords:
                                self.seen_keywords.add(s)
                                all_suffix.append(s)
                    except Exception:
                        pass
        log.info(f"  [{self.locale_code}] Round 3 total: {len(all_suffix)} new intent-expanded suggestions")
        return all_suffix

    def _discover_round4(self, round1_niches: List[Dict[str, Any]]) -> List[str]:
        """Round 4: Category x Location cross-discovery for localized content."""
        if self.locale_code != "TR":
            log.info(f"  [{self.locale_code}] Round 4: Skipping (TR-only location cross)")
            return []

        major_cities = [
            "İstanbul", "Ankara", "İzmir", "Bursa", "Antalya", "Adana",
            "Konya", "Gaziantep", "Mersin", "Diyarbakır", "Kayseri",
            "Eskişehir", "Samsun", "Trabzon", "Malatya",
        ]
        suffixes = SUFFIX_MAP.get("TR", [])
        seeds = []
        for niche in round1_niches[:20]:
            base = niche["keywords"][0] if niche["keywords"] else niche["name"]
            for city in major_cities[:5]:
                for sfx in random.sample(suffixes, min(3, len(suffixes))):
                    seeds.append(f"{base}{sfx} {city}")

        log.info(f"  [{self.locale_code}] Round 4/4: Location cross-discovery ({len(seeds)} seeds)...")
        all_loc: List[str] = []
        batch_size = 20
        for i in range(0, len(seeds), batch_size):
            batch = seeds[i:i + batch_size]
            with ThreadPoolExecutor(max_workers=10) as pool:
                fut_map = {pool.submit(self.client.fetch_suggestions, seed, self.hl, self.geo): seed for seed in batch}
                for fut in as_completed(fut_map):
                    try:
                        results = fut.result()
                        for s in results:
                            if s not in self.seen_keywords:
                                self.seen_keywords.add(s)
                                all_loc.append(s)
                    except Exception:
                        pass
        log.info(f"  [{self.locale_code}] Round 4 total: {len(all_loc)} new location-aware suggestions")
        return all_loc

    def build(self) -> List[Dict[str, Any]]:
        """Execute full discovery pipeline."""
        round1_suggestions = self._discover_round1()
        has_api_data = len(round1_suggestions) > 0

        all_suggestions = list(round1_suggestions)

        if has_api_data:
            round1_niches = self.categorizer.parse_into_niches(round1_suggestions, self.locale_code, self.seen_keywords)

            # Category-based seeding (always runs, adds more seeds)
            seeder = CategoryBasedSeeder(self.client, self.locale_code)
            cat_suggestions = seeder.discover_from_categories(self.seen_keywords)
            all_suggestions.extend(cat_suggestions)

            if self.max_rounds >= 2:
                round2 = self._discover_round2(round1_niches)
                all_suggestions.extend(round2)

            if self.max_rounds >= 3:
                round3 = self._discover_round3_suffixes(round1_niches)
                all_suggestions.extend(round3)

            if self.max_rounds >= 4:
                round4 = self._discover_round4(round1_niches)
                all_suggestions.extend(round4)

            all_niches = self.categorizer.parse_into_niches(all_suggestions, self.locale_code, set())
        else:
            log.info(f"  [{self.locale_code}] Google Suggest returned no data. Using offline fallback vocabulary.")
            return OfflineVocabularyGenerator.build_fallback_niches(self.locale_code)

        if not all_niches:
            log.info(f"  [{self.locale_code}] API returned no parseable niches. Falling back to offline vocabulary.")
            return OfflineVocabularyGenerator.build_fallback_niches(self.locale_code)

        log.info(f"  [{self.locale_code}] Total niches generated: {len(all_niches)}")
        total_kw = sum(len(n["keywords"]) for n in all_niches)
        log.info(f"  [{self.locale_code}] Total unique keywords: {total_kw}")

        # Enrich niches with keyword metrics
        for niche in all_niches:
            niche["keyword_count"] = len(niche["keywords"])
            niche["source"] = "google_suggest"

        return all_niches

    def save(self, niches: List[Dict[str, Any]]) -> None:
        data = {
            "locale": self.locale_code,
            "niche_count": len(niches),
            "keyword_count": sum(len(n["keywords"]) for n in niches),
            "generated_at": datetime.now().isoformat(),
            "api_stats": self.client.stats(),
            "niches": niches,
        }
        self.output_path.write_text(
            json.dumps(data, ensure_ascii=False, indent=2),
            encoding="utf-8",
        )
        size = self.output_path.stat().st_size
        log.info(f"  [{self.locale_code}] Saved → {self.output_path} ({size:,} bytes)")

    def load_existing(self) -> Optional[List[Dict[str, Any]]]:
        if not self.force and self.output_path.exists():
            try:
                data = json.loads(self.output_path.read_text(encoding="utf-8"))
                niches = data.get("niches", [])
                if niches:
                    log.info(f"  [{self.locale_code}] Loaded existing: {self.output_path} ({len(niches)} niches)")
                    return niches
            except (json.JSONDecodeError, KeyError):
                pass
        return None

    def run(self) -> List[Dict[str, Any]]:
        existing = self.load_existing()
        if existing is not None:
            return existing

        log.info(f"  [{self.locale_code}] Building fresh niche matrix ({self.max_rounds} rounds)...")
        niches = self.build()
        self.save(niches)
        return niches


def matrix_seeder(locale_codes: Optional[List[str]] = None, force: bool = False,
                  dry_run: bool = False, max_rounds: int = 4,
                  proxy_file: Optional[str] = None) -> Dict[str, List[Dict]]:
    if locale_codes is None:
        locale_codes = list(LOCALE_ALPHABETS.keys())

    proxies = load_proxies(proxy_file) if proxy_file else []

    results: Dict[str, List[Dict]] = {}
    for code in locale_codes:
        log.info(f"[{code}] {'=' * 45}")
        if code not in LOCALE_ALPHABETS:
            log.warning(f"  Unknown locale: {code}. Skipping.")
            continue

        builder = NicheMatrixBuilder(code, force=force, max_rounds=max_rounds, proxies=proxies)

        existing = builder.load_existing()
        if existing is not None and not force:
            results[code] = existing
            continue

        niches = builder.build()
        if not dry_run:
            builder.save(niches)
        else:
            log.info(f"  [DRY RUN] Would save {len(niches)} niches to niche_matrix_{code}.json")
        results[code] = niches

    return results


def parse_args() -> Dict[str, Any]:
    config = {
        "locales": None,
        "force": False,
        "dry_run": False,
        "max_rounds": 4,
        "proxy_file": None,
    }
    i = 1
    while i < len(sys.argv):
        arg = sys.argv[i]
        if arg == "--locale" and i + 1 < len(sys.argv):
            i += 1
            config["locales"] = [c.strip().upper() for c in sys.argv[i].split(",")]
        elif arg in ("--force", "-f"):
            config["force"] = True
        elif arg in ("--dry-run", "--dry"):
            config["dry_run"] = True
        elif arg == "--rounds" and i + 1 < len(sys.argv):
            i += 1
            config["max_rounds"] = max(1, min(4, int(sys.argv[i])))
        elif arg == "--proxies" and i + 1 < len(sys.argv):
            i += 1
            config["proxy_file"] = sys.argv[i]
        i += 1
    return config


if __name__ == "__main__":
    cfg = parse_args()
    log.info("=" * 60)
    log.info("NICHE MATRIX BUILDER v2.0 — INFINITE SEMANTIC ENGINE")
    log.info("=" * 60)
    log.info(f"Target locales: {cfg['locales'] or 'ALL'}")
    log.info(f"Force rebuild: {cfg['force']}")
    log.info(f"Dry run: {cfg['dry_run']}")
    log.info(f"Max rounds: {cfg['max_rounds']}")
    log.info(f"Proxy file: {cfg['proxy_file'] or 'None'}")

    start = time.time()
    results = matrix_seeder(
        locale_codes=cfg["locales"],
        force=cfg["force"],
        dry_run=cfg["dry_run"],
        max_rounds=cfg["max_rounds"],
        proxy_file=cfg["proxy_file"],
    )
    elapsed = time.time() - start

    log.info("=" * 60)
    log.info("SUMMARY")
    log.info("=" * 60)
    total_niches = 0
    total_keywords = 0
    for code, niches in results.items():
        kw_count = sum(len(n["keywords"]) for n in niches)
        log.info(f"  [{code}] {len(niches):,} niches, {kw_count:,} keywords")
        total_niches += len(niches)
        total_keywords += kw_count
    log.info(f"  TOTAL: {total_niches:,} niches, {total_keywords:,} keywords")
    log.info(f"  Time: {elapsed:.1f}s ({total_keywords/max(elapsed,1):.0f} keywords/s)")
    log.info("=" * 60)
