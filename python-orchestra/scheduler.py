#!/usr/bin/env python3
"""
Automated Keyword Harvester Scheduler
======================================
Run this script to continuously harvest keywords on a schedule.

Usage:
  python scheduler.py                    # Run every 6 hours
  python scheduler.py --interval 12     # Run every 12 hours
  python scheduler.py --once            # Run once and exit
  python scheduler.py --country TR      # Run single country once
  python scheduler.py --suggest-off     # Disable Google Suggest
"""

import sys
import os
import time
import json
import logging
from datetime import datetime
from pathlib import Path

try:
    import schedule
except ImportError:
    schedule = None

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from api_client import LaravelAPIClient
from orchestrator import KeywordOrchestrator
from config import API_BASE_URL, API_TOKEN

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler("orchestra_scheduler.log", encoding="utf-8"),
    ],
)
log = logging.getLogger("scheduler")


def send_webhook(result: dict, webhook_url: str = ""):
    """Send completion notification via webhook (Slack, Discord, etc.)."""
    if not webhook_url:
        return
    try:
        import requests
        stats = result.get("stats", {})
        payload = {
            "text": (
                f"Keyword Orchestra Complete\n"
                f"Countries: {stats.get('countries_processed', 0)}\n"
                f"Keywords: {stats.get('total_keywords', 0):,}\n"
                f"Time: {result.get('elapsed', 0):.1f}s"
            ),
        }
        requests.post(webhook_url, json=payload, timeout=10)
    except Exception as e:
        log.warning(f"Webhook failed: {e}")


def run_harvest(keywords_per_country: int = 50, use_suggest: bool = True) -> dict:
    """Execute full harvest cycle."""
    log.info("=" * 50)
    log.info("HARVEST CYCLE STARTING")
    log.info(f"  Keywords per country: {keywords_per_country}")
    log.info(f"  Google Suggest: {'ON' if use_suggest else 'OFF'}")
    log.info("=" * 50)

    start = time.time()
    try:
        api = LaravelAPIClient(API_BASE_URL, API_TOKEN)
        orchestrator = KeywordOrchestrator(api, use_google_suggest=use_suggest)
        result = orchestrator.run_full_orchestration(keywords_per_country=keywords_per_country)
        elapsed = time.time() - start

        log.info(f"Harvest complete in {elapsed:.1f}s")
        log.info(f"Total keywords: {result.get('stats', {}).get('total_keywords', 0):,}")

        # Write status file
        status = {
            "last_run": datetime.now().isoformat(),
            "elapsed": elapsed,
            "total_keywords": result.get("stats", {}).get("total_keywords", 0),
            "countries": result.get("stats", {}).get("countries_processed", 0),
            "success": True,
        }
        Path("orchestra_status.json").write_text(
            json.dumps(status, indent=2), encoding="utf-8"
        )

        webhook_url = os.getenv("ORCHESTRA_WEBHOOK_URL", "")
        if webhook_url:
            send_webhook(result, webhook_url)

        return result
    except Exception as e:
        log.error(f"Harvest failed: {e}", exc_info=True)
        Path("orchestra_status.json").write_text(
            json.dumps({
                "last_run": datetime.now().isoformat(),
                "error": str(e),
                "success": False,
            }, indent=2),
            encoding="utf-8",
        )
        return {"success": False, "error": str(e)}


def main():
    import argparse
    parser = argparse.ArgumentParser(description="Keyword Harvester Scheduler")
    parser.add_argument("--interval", type=int, default=6, help="Hours between harvests (default: 6)")
    parser.add_argument("--once", action="store_true", help="Run once and exit")
    parser.add_argument("--country", type=str, help="Run single country code (e.g. TR) and exit")
    parser.add_argument("--keywords", type=int, default=50, help="Keywords per country")
    parser.add_argument("--suggest-off", action="store_true", help="Disable Google Suggest API")
    parser.add_argument("--suggest-only", action="store_true", help="Google Suggest only, no fallback")

    args = parser.parse_args()

    use_suggest = not args.suggest_off

    if args.country:
        api = LaravelAPIClient(API_BASE_URL, API_TOKEN)
        orchestrator = KeywordOrchestrator(api, use_google_suggest=use_suggest)
        result = orchestrator.run_country_only(args.country.upper(), args.keywords)
        print(f"Result: {result}")
        return

    if args.once:
        run_harvest(args.keywords, use_suggest)
        return

    if schedule is None:
        log.error("schedule library not installed. Install with: pip install schedule")
        log.error("Use --once for single run instead.")
        sys.exit(1)

    log.info(f"Keyword Harvester Scheduler")
    log.info(f"  Running every {args.interval} hours")
    log.info(f"  Keywords per country: {args.keywords}")
    log.info(f"  Google Suggest: {'ON' if use_suggest else 'OFF'}")
    log.info("  Press Ctrl+C to stop\n")

    schedule.every(args.interval).hours.do(run_harvest, args.keywords, use_suggest)

    # Run first cycle immediately
    run_harvest(args.keywords, use_suggest)

    try:
        while True:
            schedule.run_pending()
            time.sleep(60)
    except KeyboardInterrupt:
        log.info("Scheduler stopped by user")


if __name__ == "__main__":
    main()
