#!/usr/bin/env bash
#
# fetch-pexels-images.sh — Download stock photos from Pexels API
# based on the image manifest. Runs in GitHub Actions before FTPS deploy.
#
# Usage:
#   PEXELS_API_KEY=xxx ./scripts/fetch-pexels-images.sh [manifest] [output-dir]
#
set -euo pipefail

PEXELS_API_KEY="${PEXELS_API_KEY:?PEXELS_API_KEY environment variable is required}"
MANIFEST="${1:-scripts/image-manifest.json}"
OUTPUT_DIR="${2:-wordpress/themes/kadence-child-lvjcb/assets/images/pexels}"

if [ ! -f "$MANIFEST" ]; then
  echo "Manifest not found: $MANIFEST" >&2
  exit 1
fi

mkdir -p "$OUTPUT_DIR"

image_count=$(jq '.images | length' "$MANIFEST")
downloaded=0
skipped=0

echo "==> Pexels image fetch: $image_count image(s) in manifest"
echo ""

for i in $(seq 0 $(( image_count - 1 ))); do
  slug=$(jq -r ".images[$i].slug" "$MANIFEST")
  query=$(jq -r ".images[$i].query" "$MANIFEST")
  orientation=$(jq -r ".images[$i].orientation // \"landscape\"" "$MANIFEST")
  resolution=$(jq -r ".images[$i].resolution // \"large\"" "$MANIFEST")

  output_file="$OUTPUT_DIR/${slug}.jpg"

  if [ -f "$output_file" ]; then
    echo "  ✓ Skip: $slug (cached)"
    skipped=$((skipped + 1))
    continue
  fi

  echo "  → Fetching: $slug"
  echo "    Query: $query"

  encoded_query=$(printf '%s' "$query" | jq -sRr @uri)

  response=$(curl -sS -H "Authorization: $PEXELS_API_KEY" \
    "https://api.pexels.com/v1/search?query=${encoded_query}&orientation=${orientation}&per_page=1")

  photo_url=$(echo "$response" | jq -r ".photos[0].src.${resolution} // .photos[0].src.large // empty")
  photographer=$(echo "$response" | jq -r '.photos[0].photographer // "Unknown"')
  pexels_url=$(echo "$response" | jq -r '.photos[0].url // empty')

  if [ -z "$photo_url" ]; then
    echo "  ⚠ No results for: $slug (query: $query)"
    continue
  fi

  curl -sS -L -o "$output_file" "$photo_url"

  file_size=$(du -h "$output_file" | cut -f1)
  echo "  ✓ Downloaded: $slug ($file_size) — Photo by $photographer"
  echo "    $pexels_url"
  downloaded=$((downloaded + 1))

  sleep 0.5
done

echo ""
echo "==> Done: $downloaded downloaded, $skipped cached"
total=$(find "$OUTPUT_DIR" -name '*.jpg' 2>/dev/null | wc -l)
echo "    $total total image(s) in $OUTPUT_DIR"
