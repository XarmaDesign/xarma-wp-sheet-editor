#!/bin/bash

PLUGIN_SLUG="xarma-wp-sheet-editor"
RELEASE_DIR="../${PLUGIN_SLUG}-release"
ZIP_NAME="${PLUGIN_SLUG}.zip"

echo "🔧 Creazione pacchetto plugin: $PLUGIN_SLUG"

# Pulizia directory di output
rm -rf "$RELEASE_DIR"
mkdir -p "$RELEASE_DIR"

# Copia i file necessari (escludendo development files)
rsync -av --exclude=".git" \
          --exclude="node_modules" \
          --exclude="backups" \
          --exclude="tests" \
          --exclude=".DS_Store" \
          --exclude="release.sh" \
          --exclude="*.zip" \
          . "$RELEASE_DIR/$PLUGIN_SLUG"

# Vai nella directory superiore e crea lo ZIP
cd "$RELEASE_DIR"
zip -r "$ZIP_NAME" "$PLUGIN_SLUG"

echo "✅ ZIP creato: $RELEASE_DIR/$ZIP_NAME"