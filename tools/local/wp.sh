#!/usr/bin/env bash
# WP-CLI for the local Egypt Roamer site (WordPress in ./wordpress, SQLite). Usage: ./wp.sh <wp args>
cd "$(dirname "$0")"
PHP="${PHP:-/c/Users/morsy/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/php.exe}"
export PYTHONUTF8=1
exec "$PHP" -d memory_limit=1024M wp-cli.phar --path=wordpress "$@"
