import requests
import time
import logging
from typing import Dict, List, Any, Optional
from config import API_BASE_URL, API_TOKEN, API_ENDPOINTS, BATCH_SIZE, REQUEST_TIMEOUT, MAX_RETRIES

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class LaravelAPIClient:
    def __init__(self, base_url: str = None, token: str = None):
        self.base_url = base_url or API_BASE_URL
        self.token = token or API_TOKEN
        self.session = requests.Session()
        self.session.headers.update({
            "Authorization": f"Bearer {self.token}",
            "Accept": "application/json",
            "Content-Type": "application/json",
        })

    def _request(self, method: str, endpoint: str, data: Dict = None, retries: int = 0) -> Optional[Dict]:
        url = f"{self.base_url}{endpoint}"
        try:
            if method.upper() == "GET":
                response = self.session.get(url, timeout=REQUEST_TIMEOUT)
            elif method.upper() == "POST":
                response = self.session.post(url, json=data, timeout=REQUEST_TIMEOUT)
            else:
                response = self.session.request(method, url, json=data, timeout=REQUEST_TIMEOUT)

            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            logger.error(f"Request failed: {e}")
            if retries < MAX_RETRIES:
                logger.info(f"Retrying... ({retries + 1}/{MAX_RETRIES})")
                time.sleep(2 ** retries)
                return self._request(method, endpoint, data, retries + 1)
            return None

    def sync_countries(self) -> Dict:
        return self._request("GET", API_ENDPOINTS["sync_countries"])

    def get_status(self) -> Dict:
        return self._request("GET", API_ENDPOINTS["status"])

    def import_keywords(self, keywords: List[Dict], language: str = "en", country_code: str = "US", category: str = None) -> Dict:
        payload = {
            "keywords": keywords,
            "language": language,
            "country_code": country_code,
            "category": category,
        }
        return self._request("POST", API_ENDPOINTS["keywords"], payload)

    def import_keywords_batch(self, keywords: List[Dict], language: str = "en", country_code: str = "US", category: str = None) -> Dict:
        total_imported = 0
        total_updated = 0
        batches = [keywords[i:i + BATCH_SIZE] for i in range(0, len(keywords), BATCH_SIZE)]

        for i, batch in enumerate(batches):
            logger.info(f"Sending batch {i+1}/{len(batches)} ({len(batch)} keywords)...")
            result = self.import_keywords(batch, language, country_code, category)
            if result:
                total_imported += result.get("imported", 0)
                total_updated += result.get("updated", 0)
            time.sleep(0.5)

        return {
            "success": True,
            "imported": total_imported,
            "updated": total_updated,
            "total": total_imported + total_updated,
        }

    def health_check(self) -> bool:
        try:
            response = self.session.get(f"{self.base_url}/api/health", timeout=5)
            return response.status_code == 200
        except:
            return False