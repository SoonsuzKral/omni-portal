"""
api_client.py — Birleşik API İstemcisi
======================================
Rate limiting, retry mekanizması, batch gönderim.
"""

import os
import time
import random
import logging
from typing import Optional

import requests

log = logging.getLogger("api_client")


class ApiClient:
    def __init__(self, base_url: str = "", api_token: str = "",
                 batch_size: int = 50, max_retries: int = 3,
                 rate_sleep: float = 0.05, concurrent: int = 30):
        self.base_url = (base_url or os.getenv("OMNI_BASE_URL", "http://localhost:8000")).rstrip("/")
        self.token = api_token or os.getenv("OMNI_API_TOKEN", "")
        self.batch_size = batch_size
        self.max_retries = max_retries
        self.rate_sleep = rate_sleep
        self.concurrent = concurrent
        self.session = requests.Session()
        self.session.headers.update({
            "Authorization": f"Bearer {self.token}",
            "Content-Type": "application/json",
            "Accept": "application/json",
            "User-Agent": "AkilliBot/2.0",
        })
        self.stats = {"sent": 0, "failed": 0, "errors": []}

    def is_configured(self) -> bool:
        return bool(self.token)

    def _send(self, payload: dict, endpoint: str = "/api/v1/ingest", retry_429: bool = True) -> Optional[dict]:
        url = f"{self.base_url}{endpoint}"
        for attempt in range(1, self.max_retries + 2):
            try:
                resp = self.session.post(url, json=payload, timeout=120)
                if resp.status_code in (200, 201, 202):
                    return resp.json()
                if resp.status_code == 429:
                    wait = (2 ** attempt) + random.uniform(0, 1)
                    log.warning(f"Rate limited (429). Bekleniyor {wait:.1f}s")
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

    def send_batch(self, nodes: list) -> int:
        time.sleep(self.rate_sleep)
        result = self._send({"content_nodes": nodes})
        if result and result.get("success"):
            self.stats["sent"] += len(nodes)
            return len(nodes)
        self.stats["failed"] += len(nodes)
        return 0

    def setup_entities(self, taxonomies: list, locations: list):
        # Taxonomies ve locations zaten sunucuda mevcut
        if taxonomies:
            log.info(f"Setting up {len(taxonomies)} taxonomies...")
            for i in range(0, len(taxonomies), 50):
                batch = taxonomies[i:i + 50]
                resp = self._send({"taxonomies": batch})
                if resp and resp.get("success"):
                    log.info(f"  Taxonomies batch {i//50+1}: OK")
                else:
                    log.warning(f"  Taxonomies batch {i//50+1}: FAIL")
            time.sleep(3)
        if locations:
            log.info(f"Setting up {len(locations)} locations...")
            for i in range(0, len(locations), 200):
                batch = locations[i:i + 200]
                resp = self._send({"locations": batch})
                if resp and resp.get("success"):
                    log.info(f"  Locations batch {i//200+1}: OK")
                else:
                    log.warning(f"  Locations batch {i//200+1}: FAIL")
                time.sleep(2)

    def health_check(self) -> bool:
        try:
            resp = self.session.get(f"{self.base_url}/api/health", timeout=10)
            return resp.status_code == 200
        except Exception:
            return False

    def report_stats(self) -> dict:
        return dict(self.stats)
