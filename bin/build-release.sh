#!/usr/bin/env bash
#
# Builds a distributable zip of the plugin.
#
#   ./bin/build-release.sh [--force] [--no-composer]
#
# The version is read from the plugin header of mill3-wp-utils.php and the archive
# is written to :
#
#   release/<version>/mill3-wp-utils-plugin.zip
#
# The file name never changes — only the directory does — so the download URL of a
# GitHub release asset always ends with /mill3-wp-utils-plugin.zip.
#
# Inside the archive everything sits under a single mill3-wp-utils-plugin/ directory,
# which is the folder name WordPress expects when it unzips the plugin.
#
# Options :
#   --force         Overwrite an existing archive for this version.
#   --no-composer   Copy vendor/ as-is instead of reinstalling without dev packages.
#                   The archive will then also contain PHPUnit and friends.

set -euo pipefail

ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
SLUG="mill3-wp-utils-plugin"
MAIN_FILE="$ROOT/mill3-wp-utils.php"

FORCE=0
USE_COMPOSER=1

for arg in "$@"; do
    case "$arg" in
        --force|-f)     FORCE=1 ;;
        --no-composer)  USE_COMPOSER=0 ;;
        --help|-h)      sed -n '2,22p' "${BASH_SOURCE[0]}" | sed 's/^# \{0,1\}//'; exit 0 ;;
        *)              echo "Unknown option : $arg (try --help)" >&2; exit 1 ;;
    esac
done

info()  { printf '\033[0;34m→\033[0m %s\n' "$1"; }
warn()  { printf '\033[0;33m!\033[0m %s\n' "$1" >&2; }
die()   { printf '\033[0;31m✗ %s\033[0m\n' "$1" >&2; exit 1; }

[ -f "$MAIN_FILE" ] || die "Cannot find $MAIN_FILE"

# --- version ---------------------------------------------------------------
# Read it from the plugin header, then make sure the MILL3_WP_UTILS_VERSION constant
# agrees : forgetting one of the two is the classic mistake when publishing.
HEADER_VERSION="$(sed -n 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*\([^[:space:]]*\).*/\1/p' "$MAIN_FILE" | head -1)"
CONST_VERSION="$(sed -n "s/^define([[:space:]]*'MILL3_WP_UTILS_VERSION',[[:space:]]*'\([^']*\)'.*/\1/p" "$MAIN_FILE" | head -1)"

[ -n "$HEADER_VERSION" ] || die "Could not read 'Version:' from the plugin header."
[ -n "$CONST_VERSION" ]  || die "Could not read MILL3_WP_UTILS_VERSION from $MAIN_FILE."

if [ "$HEADER_VERSION" != "$CONST_VERSION" ]; then
    die "Version mismatch : plugin header says $HEADER_VERSION, MILL3_WP_UTILS_VERSION says $CONST_VERSION."
fi

VERSION="$HEADER_VERSION"

# A missing changelog entry is worth a warning, not a failure.
if [ -f "$ROOT/README.txt" ] && ! grep -q "^= ${VERSION} =" "$ROOT/README.txt"; then
    warn "README.txt has no '= ${VERSION} =' changelog entry."
fi

DEST_DIR="$ROOT/release/$VERSION"
ZIP_FILE="$DEST_DIR/$SLUG.zip"
STAGING="$DEST_DIR/$SLUG"

if [ -e "$ZIP_FILE" ] && [ "$FORCE" -ne 1 ]; then
    die "$ZIP_FILE already exists. Bump the version, or pass --force to overwrite."
fi

info "Building $SLUG $VERSION"

# --- staging ---------------------------------------------------------------
rm -rf "$STAGING"
mkdir -p "$STAGING"

# Everything not listed here ships. Keep development-only files out : they are
# useless to a WordPress install and some of them (tests, phpcs config) leak
# paths from this machine.
EXCLUDES=(
    --exclude ".git/"
    --exclude ".github/"
    --exclude ".gitignore"
    --exclude ".gitattributes"
    --exclude ".editorconfig"
    --exclude ".travis.yml"
    --exclude ".phpcs.xml.dist"
    --exclude "phpunit.xml.dist"
    --exclude ".phpunit.result.cache"
    --exclude "tests/"
    --exclude "bin/"
    --exclude "release/"
    --exclude "node_modules/"
    --exclude ".claude/"
    --exclude ".remember/"
    --exclude ".vscode/"
    --exclude ".DS_Store"
    --exclude "._*"
    --exclude "*~"
    --exclude "*.log"
    --exclude "*.pid"
)

if [ "$USE_COMPOSER" -eq 1 ] && command -v composer >/dev/null 2>&1; then
    # Let Composer rebuild vendor/ from scratch without the dev requirements.
    EXCLUDES+=( --exclude "vendor/" )
    rsync -a "${EXCLUDES[@]}" "$ROOT/" "$STAGING/"

    info "Installing production dependencies"
    ( cd "$STAGING" && composer install --no-dev --optimize-autoloader --no-interaction --quiet )
else
    if [ "$USE_COMPOSER" -eq 1 ]; then
        warn "Composer not found : copying vendor/ as-is, dev dependencies included."
    fi
    rsync -a "${EXCLUDES[@]}" "$ROOT/" "$STAGING/"
fi

[ -f "$STAGING/vendor/autoload.php" ] || die "vendor/autoload.php is missing from the build — the plugin would fatal on load."
[ -f "$STAGING/$SLUG.php" ] || [ -f "$STAGING/mill3-wp-utils.php" ] || die "Main plugin file is missing from the build."

# --- syntax check ----------------------------------------------------------
# Cheap guard against shipping a file that fatals. Skips vendor/, which is not ours.
if command -v php >/dev/null 2>&1; then
    info "Linting PHP files"
    while IFS= read -r -d '' file; do
        php -l "$file" >/dev/null || die "Syntax error in ${file#$STAGING/}"
    done < <(find "$STAGING" -path "$STAGING/vendor" -prune -o -name '*.php' -type f -print0)
fi

# --- zip -------------------------------------------------------------------
info "Creating archive"
rm -f "$ZIP_FILE"
( cd "$DEST_DIR" && zip -r -q -X -9 "$SLUG.zip" "$SLUG" -x '*.DS_Store' )

rm -rf "$STAGING"

SIZE="$(du -h "$ZIP_FILE" | cut -f1 | tr -d ' ')"
COUNT="$(unzip -Z1 "$ZIP_FILE" | wc -l | tr -d ' ')"

printf '\033[0;32m✓\033[0m %s\n' "release/$VERSION/$SLUG.zip  ($SIZE, $COUNT entries)"
