"""
orchestrator.py — KeywordOrchestrator with Google Suggest + trend integration
==============================================================================
"""

import time
import logging
from datetime import datetime
from typing import Dict, List, Optional
from colorama import Fore, Style, init
from api_client import LaravelAPIClient
from keyword_generator import GoogleSuggestKeywordGenerator
from config import ALL_COUNTRIES, EUROPE_COUNTRIES, GLOBAL_COUNTRIES

init(autoreset=True)
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)


class KeywordOrchestrator:
    def __init__(self, api_client: LaravelAPIClient, use_google_suggest: bool = True):
        self.api = api_client
        self.generator = GoogleSuggestKeywordGenerator()
        self.use_google_suggest = use_google_suggest
        self.stats = {
            "countries_processed": 0,
            "total_keywords": 0,
            "start_time": None,
            "by_language": {},
            "by_source": {"google_suggest": 0, "fallback": 0},
        }

    def print_header(self):
        print(f"\n{Fore.CYAN}{'='*60}")
        print(f"{Fore.CYAN}KEYWORD ORCHESTRATOR — MULTI COUNTRY SYSTEM")
        print(f"{Fore.CYAN}Google Suggest: {'ON' if self.use_google_suggest else 'OFF'}")
        print(f"{Fore.CYAN}{'='*60}{Style.RESET_ALL}\n")

    def sync_countries_first(self) -> bool:
        print(f"{Fore.YELLOW}Syncing countries with Laravel API...")
        result = self.api.sync_countries()
        if result and result.get("success"):
            print(f"{Fore.GREEN}Countries synced: {result.get('total_countries')} total, {result.get('created')} new")
            return True
        print(f"{Fore.RED}Failed to sync countries")
        return False

    def process_country(self, country: Dict, keywords_per_country: int = 100) -> Dict:
        code = country["code"]
        name = country["name"]
        lang = country["language"]

        print(f"\n{Fore.BLUE}Processing: {name} ({code}) - Language: {lang}")

        keywords = self.generator.get_keywords_by_country(
            country_code=code,
            language=lang,
            count=keywords_per_country,
            use_suggest=self.use_google_suggest,
        )

        # Count by source
        suggest_count = sum(1 for k in keywords if k.get("source") == "google_suggest")
        fallback_count = len(keywords) - suggest_count
        self.stats["by_source"]["google_suggest"] += suggest_count
        self.stats["by_source"]["fallback"] += fallback_count

        print(f"  Generated {len(keywords)} keywords ({suggest_count} suggest, {fallback_count} fallback)")

        result = self.api.import_keywords_batch(keywords, lang, code)

        if result and result.get("success"):
            imported = result.get("imported", 0)
            updated = result.get("updated", 0)
            print(f"  {Fore.GREEN}Imported: {imported}, Updated: {updated}")

            if lang not in self.stats["by_language"]:
                self.stats["by_language"][lang] = 0
            self.stats["by_language"][lang] += imported + updated

            return result
        else:
            print(f"  {Fore.RED}Failed to import keywords")
            return {"success": False, "total": 0}

    def process_europe(self, keywords_per_country: int = 100) -> Dict:
        print(f"\n{Fore.MAGENTA}{'='*40}")
        print(f"{Fore.MAGENTA}PROCESSING EUROPE ({len(EUROPE_COUNTRIES)} countries)")
        print(f"{Fore.MAGENTA}{'='*40}{Style.RESET_ALL}")

        europe_stats = {"countries": 0, "keywords": 0}
        for country in EUROPE_COUNTRIES:
            result = self.process_country(country, keywords_per_country)
            if result and result.get("success"):
                europe_stats["countries"] += 1
                europe_stats["keywords"] += result.get("total", 0)
            time.sleep(0.5)

        return europe_stats

    def process_global(self, keywords_per_country: int = 100) -> Dict:
        print(f"\n{Fore.MAGENTA}{'='*40}")
        print(f"{Fore.MAGENTA}PROCESSING GLOBAL ({len(GLOBAL_COUNTRIES)} countries)")
        print(f"{Fore.MAGENTA}{'='*40}{Style.RESET_ALL}")

        global_stats = {"countries": 0, "keywords": 0}
        for country in GLOBAL_COUNTRIES:
            result = self.process_country(country, keywords_per_country)
            if result and result.get("success"):
                global_stats["countries"] += 1
                global_stats["keywords"] += result.get("total", 0)
            time.sleep(0.5)

        return global_stats

    def run_full_orchestration(self, keywords_per_country: int = 100) -> Dict:
        self.print_header()
        self.stats["start_time"] = datetime.now()

        if not self.sync_countries_first():
            return {"success": False, "error": "Failed to sync countries"}

        print(f"\n{Fore.GREEN}{'='*60}")
        print(f"{Fore.GREEN}STARTING FULL ORCHESTRATION")
        print(f"{Fore.GREEN}{'='*60}{Style.RESET_ALL}")

        start = time.time()
        europe = self.process_europe(keywords_per_country)
        global_stats = self.process_global(keywords_per_country)
        elapsed = time.time() - start

        self.stats["countries_processed"] = europe["countries"] + global_stats["countries"]
        self.stats["total_keywords"] = europe["keywords"] + global_stats["keywords"]

        final_status = self.api.get_status()

        print(f"\n{Fore.GREEN}{'='*60}")
        print(f"{Fore.GREEN}ORCHESTRATION COMPLETE")
        print(f"{Fore.GREEN}{'='*60}")
        print(f"  Countries Processed: {self.stats['countries_processed']}")
        print(f"  Total Keywords: {self.stats['total_keywords']:,}")
        print(f"  Google Suggest: {self.stats['by_source']['google_suggest']:,}")
        print(f"  Fallback: {self.stats['by_source']['fallback']:,}")
        print(f"  Europe: {europe['countries']} countries, {europe['keywords']} keywords")
        print(f"  Global: {global_stats['countries']} countries, {global_stats['keywords']} keywords")
        print(f"  Time: {elapsed:.1f}s")
        if self.stats.get("by_language"):
            print(f"  By Language: {self.stats['by_language']}")
        print(f"{Style.RESET_ALL}\n")

        return {
            "success": True,
            "stats": self.stats,
            "final_status": final_status,
        }

    def run_country_only(self, country_code: str, keywords_per_country: int = 100) -> Dict:
        self.print_header()

        country = next((c for c in ALL_COUNTRIES if c["code"] == country_code.upper()), None)
        if not country:
            print(f"{Fore.RED}Country not found: {country_code}")
            return {"success": False, "error": "Country not found"}

        result = self.process_country(country, keywords_per_country)
        return result

    def get_status(self) -> Dict:
        return self.api.get_status()
