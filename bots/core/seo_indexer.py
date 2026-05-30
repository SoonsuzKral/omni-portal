"""
seo_indexer.py — Google Indexing API & IndexNow Notifier
=========================================================
İçerik üretildikten sonra arama motorlarına bildirim gönderir.
"""

import logging
import os
import requests
from typing import Optional

log = logging.getLogger("seo_indexer")


class IndexNowNotifier:
    ENDPOINT = "https://api.indexnow.org/indexnow"
    MAX_BATCH = 10000

    def __init__(self, site_url: str = "", api_key: str = ""):
        self.site_url = site_url.rstrip("/") if site_url else os.getenv("SITE_URL", "https://omviportal.com")
        self.api_key = api_key or os.getenv("INDEXNOW_KEY", "")
        self.host = self.site_url.replace("https://", "").replace("http://", "")
        self.enabled = bool(self.api_key)

    def notify(self, urls: list) -> int:
        if not self.enabled or not urls:
            return 0
        success = 0
        for i in range(0, len(urls), self.MAX_BATCH):
            batch = urls[i:i + self.MAX_BATCH]
            payload = {"host": self.host, "key": self.api_key, "urlList": batch}
            try:
                resp = requests.post(self.ENDPOINT, json=payload, timeout=30,
                                     headers={"Content-Type": "application/json"})
                if resp.status_code in (200, 202):
                    success += len(batch)
                    log.info(f"IndexNow: {len(batch)} URL bildirildi")
                else:
                    log.warning(f"IndexNow hata {resp.status_code}: {resp.text[:200]}")
            except Exception as e:
                log.warning(f"IndexNow başarısız: {e}")
        return success


class GoogleIndexer:
    def __init__(self):
        self.enabled = False
        self.service = None

    def _init(self):
        path = os.getenv("GOOGLE_INDEXING_SERVICE_ACCOUNT_PATH", "storage/google-service-account.json")
        if not os.path.exists(path):
            return
        try:
            from google.oauth2 import service_account
            from googleapiclient.discovery import build
            creds = service_account.Credentials.from_service_account_file(
                path, scopes=["https://www.googleapis.com/auth/indexing"])
            self.service = build("indexing", "v3", credentials=creds)
            self.enabled = True
            log.info("Google Indexing API hazır")
        except ImportError:
            log.warning("google-auth kurulu değil")
        except Exception as e:
            log.warning(f"Google Indexing API başlatılamadı: {e}")

    def notify_batch(self, urls: list) -> int:
        if not self.enabled:
            self._init()
        if not self.enabled or not urls:
            return 0
        s = 0
        for url in urls:
            try:
                self.service.urlNotifications().publish(
                    body={"url": url, "type": "URL_UPDATED"}).execute()
                s += 1
            except Exception as e:
                log.debug(f"Google Indexing API hata ({url}): {e}")
        return s
