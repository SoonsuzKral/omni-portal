#!/usr/bin/env python3
"""
akilli_bot.py — AKILLI BOT v4.0 (Multilingual SEO Matrix Injector)
============================================================
Özellikler:
  - 81 il + TÜM ilçeler (972+ lokasyon)
  - 30 kategori, çok dilli anahtar kelime
  - Çok dilli destek: TR/EN/RU/FR
  - Her dil kendi lokasyon ve keyword setiyle çalışır
  - Akıllı lokasyon eşleştirme (ilçe asla yanlış il ile eşleşmez)
  - Gelişmiş Spintax motoru
  - Google Trends entegrasyonu
  - Checkpoint/Resume desteği
  - Thread pool ile yüksek performanslı batch gönderim

Kullanım:
  python bots/akilli_bot.py
  python bots/akilli_bot.py --dry-run
  python bots/akilli_bot.py --quick
  python bots/akilli_bot.py --resume
  python bots/akilli_bot.py --lang en
  python bots/akilli_bot.py --cities istanbul,ankara,izmir
"""

import hashlib
import json
import logging
import os
import random
import re
import sys
import time
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime
from pathlib import Path
from typing import Generator
from dotenv import load_dotenv

load_dotenv()

from core.data import TURKIYE_IL_ILCE, ALL_LOCATIONS, slugify_tr, get_districts
from core.data import KATEGORILER, get_all_keywords, get_category_count
from core.spintax_engine import SpintaxEngine
from core.api_client import ApiClient
from core.seo_indexer import IndexNowNotifier

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[logging.StreamHandler(), logging.FileHandler("akilli_bot.log", encoding="utf-8")],
)
log = logging.getLogger("akilli_bot")

CONFIG = {
    "BASE_URL": os.getenv("OMNI_BASE_URL", "http://localhost:8000"),
    "API_TOKEN": os.getenv("OMNI_API_TOKEN", ""),
    "CONCURRENT_WORKERS": int(os.getenv("OMNI_WORKERS", "30")),
    "BATCH_SIZE": int(os.getenv("OMNI_BATCH_SIZE", "50")),
    "MAX_RETRIES": int(os.getenv("OMNI_MAX_RETRIES", "3")),
    "RATE_LIMIT_SLEEP": float(os.getenv("OMNI_RATE_SLEEP", "0.05")),
    "RESUME_FILE": "akilli_checkpoint.json",
    "DRY_RUN": False,
    "QUICK_MODE": False,
    "RESUME": False,
    "LANGUAGE": "tr",
    "CITIES": None,
}

DILLER = {
    'tr': {
        'code': 'tr',
        'soru_kaliplari': [
            "{keyword} {il} fiyatları 2026",
            "{keyword} {il} en iyi",
            "{keyword} {il} telefon numarası",
            "{keyword} {il} 7/24",
            "{keyword} {il} acil servis",
            "{keyword} {il} ucuz",
            "{keyword} {il} güvenilir",
            "{il} {keyword} tavsiye",
            "{il} en iyi {keyword}",
            "{keyword} {ilce} {il}",
        ],
        'meta_format': "{il} {keyword} - Güncel {keyword} hizmetleri, fiyatları ve firmaları.",
        'prefix': '',
    },
    'en': {
        'code': 'en',
        'soru_kaliplari': [
            "{keyword} in {il} 2026",
            "best {keyword} {il}",
            "{keyword} {il} phone number",
            "24/7 {keyword} {il}",
            "emergency {keyword} {il}",
            "cheap {keyword} {il}",
            "reliable {keyword} {il}",
            "{il} {keyword} recommendation",
            "top {keyword} in {il}",
            "{keyword} {ilce} {il}",
        ],
        'meta_format': "{il} {keyword} - Best {keyword} services, prices and companies.",
        'prefix': 'en-',
    },
    'ru': {
        'code': 'ru',
        'soru_kaliplari': [
            "{keyword} в {il} 2026",
            "лучший {keyword} {il}",
            "{keyword} {il} телефон",
            "срочный {keyword} {il}",
            "дешевый {keyword} {il}",
            "{keyword} {il} отзывы",
            "заказать {keyword} {il}",
        ],
        'meta_format': "{il} {keyword} - Услуги, цены и компании.",
        'prefix': 'ru-',
    },
    'fr': {
        'code': 'fr',
        'soru_kaliplari': [
            "{keyword} à {il} 2026",
            "meilleur {keyword} {il}",
            "{keyword} {il} téléphone",
            "urgence {keyword} {il}",
            "{keyword} {il} pas cher",
            "{keyword} {il} avis",
            "commander {keyword} {il}",
        ],
        'meta_format': "{il} {keyword} - Services, prix et entreprises.",
        'prefix': 'fr-',
    },
}

CITY_SLUG_TO_DISPLAY = {
    'istanbul': 'İstanbul', 'ankara': 'Ankara', 'izmir': 'İzmir',
    'bursa': 'Bursa', 'antalya': 'Antalya', 'adana': 'Adana',
    'konya': 'Konya', 'gaziantep': 'Gaziantep', 'mersin': 'Mersin',
    'kayseri': 'Kayseri', 'diyarbakir': 'Diyarbakır', 'hatay': 'Hatay',
    'manisa': 'Manisa', 'kocaeli': 'Kocaeli', 'samsun': 'Samsun',
    'balikesir': 'Balıkesir', 'kahramanmaras': 'Kahramanmaraş',
    'van': 'Van', 'aydin': 'Aydın', 'denizli': 'Denizli',
    'sakarya': 'Sakarya', 'tekirdag': 'Tekirdağ', 'mugla': 'Muğla',
    'eskisehir': 'Eskişehir', 'malatya': 'Malatya', 'trabzon': 'Trabzon',
    'erzurum': 'Erzurum', 'ordu': 'Ordu', 'sanliurfa': 'Şanlıurfa',
    'zonguldak': 'Zonguldak', 'corum': 'Çorum', 'amasya': 'Amasya',
    'kastamonu': 'Kastamonu', 'rize': 'Rize', 'sivas': 'Sivas',
}


class AkilliMatrix:
    def __init__(self, locations: list, categories: list, lang: str = 'tr'):
        self.locations = locations
        self.categories = categories
        self.lang = lang
        self.dil = DILLER.get(lang, DILLER['tr'])

    def estimate_nodes(self) -> int:
        total_kw = sum(len(c["keywords"]) for c in self.categories)
        return len(self.locations) * total_kw * len(self.dil['soru_kaliplari'])

    def yield_nodes(self) -> Generator[dict, None, None]:
        for loc in self.locations:
            for cat in self.categories:
                for kw in cat["keywords"]:
                    yield from self._make_nodes(loc, cat, kw)

    def _make_nodes(self, loc: dict, cat: dict, keyword: str) -> Generator[dict, None, None]:
        loc_name = loc["name"]
        loc_slug = loc["slug"]
        city_name = loc.get("parent_name", loc_name)
        prefix = self.dil['prefix']

        for qp in self.dil['soru_kaliplari']:
            title = qp.format(il=loc_name, keyword=keyword, ilce=loc.get('parent_name', ''))
            base_slug = self._unique_slug(f"{loc_slug}-{cat['slug']}-{slugify_tr(keyword)}", loc_slug + keyword + qp)
            slug = f"{prefix}{base_slug}"
            body = SpintaxEngine.generate_unique_body(loc, keyword)
            meta = self.dil['meta_format'].format(il=loc_name, keyword=keyword)
            yield {
                "title": title,
                "slug": slug,
                "body_content": body,
                "meta_description": meta[:320],
                "language": self.lang,
                "is_restricted_content": False,
                "taxonomy_slug": cat["slug"],
                "location_slug": loc_slug,
                "published_at": datetime.now().isoformat(),
            }

    @staticmethod
    def _unique_slug(base: str, seed: str) -> str:
        suffix = hashlib.md5(seed.encode()).hexdigest()[:8]
        return f"{slugify_tr(base)}-{suffix}"


class AkilliBot:
    def __init__(self, config: dict):
        self.config = config
        self.lang = config.get("LANGUAGE", "tr")
        self.locations = self._filter_locations()
        self.categories = KATEGORILER
        self.matrix = AkilliMatrix(self.locations, self.categories, self.lang)
        self.api = ApiClient(
            base_url=config["BASE_URL"],
            api_token=config["API_TOKEN"],
            batch_size=config["BATCH_SIZE"],
            max_retries=config["MAX_RETRIES"],
            rate_sleep=config["RATE_LIMIT_SLEEP"],
            concurrent=config["CONCURRENT_WORKERS"],
        )
        self.checkpoint_path = Path(config["RESUME_FILE"])
        self.indexnow = IndexNowNotifier()

    def _filter_locations(self):
        cities_param = self.config.get("CITIES")
        if cities_param:
            city_slugs = [c.strip().lower() for c in cities_param.split(",")]
            filtered = [loc for loc in ALL_LOCATIONS if loc["slug"] in city_slugs or loc.get("parent", "") in city_slugs]
            if filtered:
                log.info(f"Filtrelenmiş lokasyonlar: {len(filtered)} ({len(set(city_slugs))} il)")
                return filtered
        return ALL_LOCATIONS

    def _load_checkpoint(self) -> set:
        if not self.config.get("RESUME"):
            return set()
        if self.checkpoint_path.exists():
            try:
                data = json.loads(self.checkpoint_path.read_text("utf-8"))
                lang_key = f"processed_{self.lang}"
                processed = set(data.get(lang_key, data.get("processed", [])))
                log.info(f"Checkpoint yüklendi ({self.lang}): {len(processed)} slug işlenmiş")
                return processed
            except Exception:
                pass
        return set()

    def _save_checkpoint(self, processed: set):
        try:
            data = {"updated": datetime.now().isoformat()}
            if self.checkpoint_path.exists():
                try:
                    data = json.loads(self.checkpoint_path.read_text("utf-8"))
                except Exception:
                    pass
            lang_key = f"processed_{self.lang}"
            data[lang_key] = list(processed)[-50000:]
            self.checkpoint_path.write_text(
                json.dumps(data, ensure_ascii=False), encoding="utf-8"
            )
        except Exception as e:
            log.warning(f"Checkpoint kaydedilemedi: {e}")

    def run(self):
        start = time.time()
        log.info("=" * 60)
        log.info(f"AKILLI BOT v4.0 — DİL: {self.lang.upper()}")
        log.info("=" * 60)

        if not self.api.is_configured():
            log.error("API_TOKEN boş! OMNI_API_TOKEN ayarlayın.")
            sys.exit(1)

        dry = self.config.get("DRY_RUN", False)
        quick = self.config.get("QUICK_MODE", False)

        total_kw = sum(len(c["keywords"]) for c in self.categories)
        estimated = self.matrix.estimate_nodes()
        log.info(f"Lokasyon: {len(self.locations)}")
        log.info(f"Kategori: {len(self.categories)}")
        log.info(f"Anahtar Kelime: {total_kw}")
        log.info(f"Soru Kalıbı: {len(self.matrix.dil['soru_kaliplari'])}")
        log.info(f"Tahmini Node: ~{estimated:,}")
        if dry:
            log.info("DRY RUN — API çağrısı yapılmayacak")

        processed_slugs = self._load_checkpoint()

        nodes = []
        for node in self.matrix.yield_nodes():
            if node["slug"] in processed_slugs:
                continue
            nodes.append(node)
            if quick and len(nodes) >= 500:
                break

        if not nodes:
            log.info("İşlenecek node yok. Hepsini işlemişsiniz!")
            return

        log.info(f"İşlenecek node: {len(nodes):,}")

        taxonomies = [{"name": c["name"], "slug": c["slug"]} for c in self.categories]
        locations = [{"name": l["name"], "slug": l["slug"]} for l in self.locations]

        if not dry:
            self.api.setup_entities(taxonomies, locations)

            batches = [nodes[i:i + self.config["BATCH_SIZE"]]
                       for i in range(0, len(nodes), self.config["BATCH_SIZE"])]
            log.info(f"{len(batches)} batch gönderiliyor, {self.config['CONCURRENT_WORKERS']} worker...")

            with ThreadPoolExecutor(max_workers=self.config["CONCURRENT_WORKERS"]) as executor:
                fut_map = {executor.submit(self.api.send_batch, b): b for b in batches}
                done = 0
                for fut in as_completed(fut_map):
                    done += 1
                    if done % 20 == 0:
                        pct = done / len(batches) * 100
                        log.info(f"  İlerleme: {done}/{len(batches)} (%{pct:.0f})")
                    if done % 50 == 0:
                        slgs = {n["slug"] for b in batches[:done] for n in b}
                        self._save_checkpoint(slgs)

            all_slugs = {n["slug"] for n in nodes}
            self._save_checkpoint(all_slugs)

            site_url = os.getenv("SITE_URL", "https://omviportal.com")
            all_urls = [f"{site_url}/{slug}" for slug in all_slugs]
            indexed = self.indexnow.notify(all_urls)
            if indexed:
                log.info(f"IndexNow: {indexed} URL indekslendi")
        else:
            log.info(f"[DRY-RUN] İlk 3 node örneği:")
            for i, n in enumerate(nodes[:3]):
                log.info(f"  {i+1}. {n['title']} -> {n['slug']}")

        elapsed = time.time() - start
        stats = self.api.report_stats()
        log.info("=" * 60)
        log.info("MİSYON TAMAMLANDI")
        log.info(f"  Gönderilen: {stats['sent']:,}")
        log.info(f"  Başarısız: {stats['failed']:,}")
        log.info(f"  Süre: {elapsed:.1f}s")
        log.info("=" * 60)


def parse_args() -> dict:
    cfg = dict(CONFIG)
    for arg in sys.argv[1:]:
        if arg in ("--dry-run", "--dry"):
            cfg["DRY_RUN"] = True
        if arg in ("--quick", "--test"):
            cfg["QUICK_MODE"] = True
        if arg in ("--resume", "--continue"):
            cfg["RESUME"] = True
    for i, arg in enumerate(sys.argv[1:]):
        if arg == "--lang" and i + 2 < len(sys.argv):
            val = sys.argv[i + 2]
            if val in DILLER:
                cfg["LANGUAGE"] = val
        if arg == "--cities" and i + 2 < len(sys.argv):
            cfg["CITIES"] = sys.argv[i + 2]
    return cfg


if __name__ == "__main__":
    cfg = parse_args()
    bot = AkilliBot(cfg)
    bot.run()
