#!/usr/bin/env python3
"""
Keyword Orchestra - Multi Country Keyword Harvesting System
=============================================================
Usage: python main.py [command] [options]

Commands:
  sync              Sync countries with API
  status            Show current status
  run               Run full orchestration (Europe + Global)
  run-europe        Run only European countries
  run-global        Run only Global countries
  run-country       Run specific country (usage: python main.py run-country TR)
  test              Test API connection

Options:
  --keywords N      Keywords per country (default: 100)
  --suggest-off     Disable Google Suggest API (use fallback only)
  --suggest-only    Google Suggest only, no fallback generation
"""

import sys
import os
import argparse

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from api_client import LaravelAPIClient
from orchestrator import KeywordOrchestrator
from config import API_BASE_URL, API_TOKEN


def main():
    parser = argparse.ArgumentParser(description="Keyword Orchestra - Multi Country System")
    parser.add_argument("command", nargs="?", default="help", help="Command to run")
    parser.add_argument("country", nargs="?", help="Country code for country-specific commands")
    parser.add_argument("--keywords", type=int, default=100, help="Keywords per country")
    parser.add_argument("--url", default=API_BASE_URL, help="API Base URL")
    parser.add_argument("--token", default=API_TOKEN, help="API Token")
    parser.add_argument("--suggest-off", action="store_true", help="Disable Google Suggest")
    parser.add_argument("--suggest-only", action="store_true", help="Google Suggest only")

    args = parser.parse_args()

    api = LaravelAPIClient(args.url, args.token)
    use_suggest = not args.suggest_off
    orchestrator = KeywordOrchestrator(api, use_google_suggest=use_suggest)

    if args.command == "test":
        print("Testing API connection...")
        if api.health_check():
            print("API connection successful!")
            sys.exit(0)
        else:
            print("API connection failed!")
            sys.exit(1)

    elif args.command == "sync":
        print("Syncing countries...")
        result = api.sync_countries()
        print(result)

    elif args.command == "status":
        print("Getting status...")
        status = api.get_status()
        if status:
            print(f"Total Keywords: {status.get('total_keywords')}")
            print(f"Countries Available: {status.get('countries_available')}")
            print(f"By Language: {status.get('by_language')}")
            print(f"By Country: {status.get('by_country')}")
        else:
            print("Failed to get status")

    elif args.command == "run":
        print(f"Starting full orchestration ({args.keywords} keywords per country)...")
        print(f"Google Suggest: {'ON' if use_suggest else 'OFF'}")
        result = orchestrator.run_full_orchestration(args.keywords)
        print(f"\nDone. Total: {result.get('stats', {}).get('total_keywords', 0):,} keywords")

    elif args.command == "run-europe":
        print(f"Starting Europe orchestration ({args.keywords} keywords per country)...")
        result = orchestrator.process_europe(args.keywords)
        print(f"\nDone. Europe: {result.get('keywords', 0):,} keywords")

    elif args.command == "run-global":
        print(f"Starting Global orchestration ({args.keywords} keywords per country)...")
        result = orchestrator.process_global(args.keywords)
        print(f"\nDone. Global: {result.get('keywords', 0):,} keywords")

    elif args.command == "run-country":
        if not args.country:
            print("Error: Country code required")
            print("Usage: python main.py run-country TR")
            sys.exit(1)
        print(f"Starting for country: {args.country}")
        print(f"Keywords: {args.keywords} | Google Suggest: {'ON' if use_suggest else 'OFF'}")
        result = orchestrator.run_country_only(args.country, args.keywords)
        print(f"\nDone: {result}")

    else:
        print(__doc__)


if __name__ == "__main__":
    main()
