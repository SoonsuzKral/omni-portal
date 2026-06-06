#!/usr/bin/env python3
"""
akilli_bot.py — AKILLI BOT v3.0 (Prime SEO Matrix Injector)
============================================================
Özellikler:
  - 81 il + TÜM ilçeler (973+ lokasyon)
  - 73 kategori, 833+ anahtar kelime
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
from core.spintax_engine import SpintaxEngine, QUESTION_PATTERNS
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
}


class AkilliMatrix:
    """
    Matris çarpanı: {Lokasyon} x {Keyword} x {Soru Kalıbı}
    Lokasyon-kelime eşleştirmesi her zaman doğru yapılır.
    """

    def __init__(self, locations: list, categories: list):
        self.locations = locations
        self.categories = categories

    def estimate_nodes(self) -> int:
        total_kw = sum(len(c["keywords"]) for c in self.categories)
        return len(self.locations) * total_kw * len(QUESTION_PATTERNS)

    def yield_nodes(self) -> Generator[dict, None, None]:
        for loc in self.locations:
            for cat in self.categories:
                for kw in cat["keywords"]:
                    yield from self._make_nodes(loc, cat, kw)

    def _make_nodes(self, loc: dict, cat: dict, keyword: str) -> Generator[dict, None, None]:
        loc_name = loc["name"]
        loc_slug = loc["slug"]
        city_name = loc.get("parent_name", loc_name)

        for qp in QUESTION_PATTERNS:
            title = qp.format(il=loc_name, keyword=keyword)
            slug = self._unique_slug(f"{loc_slug}-{cat['slug']}-{slugify_tr(keyword)}", loc_slug + keyword + qp)
            body = SpintaxEngine.generate_unique_body(loc, keyword)
            meta = (
                f"{loc_name} {keyword} - Güncel {keyword} hizmetleri, "
                f"fiyatları ve firmaları {loc_name} sayfamızda."
            )
            yield {
                "title": title,
                "slug": slug,
                "body_content": body,
                "meta_description": meta[:320],
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
        self.locations = ALL_LOCATIONS
        self.categories = KATEGORILER
        self.matrix = AkilliMatrix(self.locations, self.categories)
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

    def _load_checkpoint(self) -> set:
        if not self.config.get("RESUME"):
            return set()
        if self.checkpoint_path.exists():
            try:
                data = json.loads(self.checkpoint_path.read_text("utf-8"))
                processed = set(data.get("processed", []))
                log.info(f"Checkpoint yüklendi: {len(processed)} slug işlenmiş")
                return processed
            except Exception:
                pass
        return set()

    def _save_checkpoint(self, processed: set):
        try:
            self.checkpoint_path.write_text(
                json.dumps({"processed": list(processed)[-50000:], "updated": datetime.now().isoformat()},
                           ensure_ascii=False),
                encoding="utf-8"
            )
        except Exception as e:
            log.warning(f"Checkpoint kaydedilemedi: {e}")

    def run(self):
        start = time.time()
        log.info("=" * 60)
        log.info("AKILLI BOT v3.0 — PRIME SEO MATRIX INJECTOR")
        log.info("=" * 60)

        if not self.api.is_configured():
            log.error("API_TOKEN boş! OMNI_API_TOKEN ayarlayın.")
            sys.exit(1)

        dry = self.config.get("DRY_RUN", False)
        quick = self.config.get("QUICK_MODE", False)

        total_kw = sum(len(c["keywords"]) for c in self.categories)
        estimated = self.matrix.estimate_nodes()
        log.info(f"Lokasyon: {len(self.locations)} (81 il + {len(self.locations)-81} ilçe)")
        log.info(f"Kategori: {len(self.categories)}")
        log.info(f"Anahtar Kelime: {total_kw}")
        log.info(f"Soru Kalıbı: {len(QUESTION_PATTERNS)}")
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
    return cfg


if __name__ == "__main__":
    cfg = parse_args()
    bot = AkilliBot(cfg)
    bot.run()
