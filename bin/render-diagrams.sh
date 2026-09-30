#!/usr/bin/env bash
#
# Render the PlantUML diagrams in docs/diagrams to PNG.
#
# The rendered images are committed to the repository so GitHub renders them
# inline in Markdown — GitHub does not render PlantUML source. Re-run this
# script after editing any .puml file, and commit both the source and the PNG.
#
# Usage:
#   bin/render-diagrams.sh            re-render every diagram in place
#   bin/render-diagrams.sh --check    fail if any committed PNG is stale
#   bin/render-diagrams.sh NAME ...   only the named diagrams (without .puml)
#
# Reproducibility: the PlantUML version below is pinned, and diagrams are
# rendered with the bundled Smetana layout engine rather than the system
# Graphviz, so the committed bytes depend only on this file and the .puml
# sources — not on which machine or Graphviz build rendered them. That is what
# makes `composer diagrams -- --check` a meaningful staleness gate in CI.

set -euo pipefail

# --- single source of truth ------------------------------------------------
# Bump this to upgrade. CI reads it from here, so there is nothing else to edit.
PLANTUML_VERSION="1.2026.8"

# --- layout ----------------------------------------------------------------
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SOURCE_DIR="$ROOT/docs/diagrams"
OUTPUT_DIR="$ROOT/docs/assets/diagrams"
CACHE_DIR="${PLANTUML_CACHE_DIR:-$ROOT/.build/plantuml}"
JAR="$CACHE_DIR/plantuml-$PLANTUML_VERSION.jar"
JAR_URL="https://github.com/plantuml/plantuml/releases/download/v$PLANTUML_VERSION/plantuml-$PLANTUML_VERSION.jar"

CHECK_MODE=0
NAMES=()

for arg in "$@"; do
    case "$arg" in
        --check) CHECK_MODE=1 ;;
        -h|--help) sed -n '2,26p' "${BASH_SOURCE[0]}" | sed 's/^# \{0,1\}//'; exit 0 ;;
        *) NAMES+=("$arg") ;;
    esac
done

# --- prerequisites ---------------------------------------------------------
if ! command -v java >/dev/null 2>&1; then
    echo "error: java is required to run PlantUML but was not found on PATH." >&2
    echo "       Install a JRE (Java 17+), or point PLANTUML_JAR at a local plantuml.jar." >&2
    exit 1
fi

# --- pinned PlantUML -------------------------------------------------------
if [ ! -f "$JAR" ]; then
    mkdir -p "$CACHE_DIR"
    echo "Fetching PlantUML $PLANTUML_VERSION ..."
    if ! curl --fail --silent --show-error --location --output "$JAR.tmp" "$JAR_URL"; then
        rm -f "$JAR.tmp"
        echo "error: could not download PlantUML from $JAR_URL" >&2
        exit 1
    fi
    mv "$JAR.tmp" "$JAR"
fi

# --- select sources --------------------------------------------------------
if [ ${#NAMES[@]} -gt 0 ]; then
    SOURCES=()
    for name in "${NAMES[@]}"; do
        [ -f "$name" ] && SOURCES+=("$name") || SOURCES+=("$SOURCE_DIR/$name.puml")
    done
else
    mapfile -t SOURCES < <(find "$SOURCE_DIR" -maxdepth 1 -name '*.puml' | sort)
fi

if [ ${#SOURCES[@]} -eq 0 ]; then
    echo "No PlantUML sources found in $SOURCE_DIR" >&2
    exit 1
fi

# --- render ----------------------------------------------------------------
# In --check mode the images are rendered to a scratch directory and compared
# with what is committed, so a stale diagram fails the build without leaving
# modified files in the working tree.
if [ "$CHECK_MODE" -eq 1 ]; then
    RENDER_DIR="$(mktemp -d)"
    trap 'rm -rf "$RENDER_DIR"' EXIT
else
    RENDER_DIR="$OUTPUT_DIR"
    mkdir -p "$RENDER_DIR"
fi

echo "Rendering ${#SOURCES[@]} diagram(s) with PlantUML $PLANTUML_VERSION ..."
# -failfast2 makes PlantUML exit non-zero on a syntax error instead of quietly
# writing an image that says "error" in it, so a broken diagram breaks the build
# here rather than in the Markdown that embeds it.
java -jar "$JAR" \
    -Playout=smetana \
    -charset UTF-8 \
    -failfast2 \
    -tpng \
    -o "$RENDER_DIR" \
    "${SOURCES[@]}"

# --- verify ----------------------------------------------------------------
if [ "$CHECK_MODE" -eq 1 ]; then
    status=0
    for source in "${SOURCES[@]}"; do
        name="$(basename "$source" .puml)"
        rendered="$RENDER_DIR/$name.png"
        committed="$OUTPUT_DIR/$name.png"

        if [ ! -f "$rendered" ]; then
            echo "  MISSING RENDER  $name.png — PlantUML produced no image" >&2
            status=1
        elif [ ! -f "$committed" ]; then
            echo "  NOT COMMITTED   $name.png — run bin/render-diagrams.sh" >&2
            status=1
        elif ! cmp -s "$rendered" "$committed"; then
            echo "  STALE           $name.png — run bin/render-diagrams.sh and commit the result" >&2
            status=1
        else
            echo "  ok              $name.png"
        fi
    done

    if [ "$status" -ne 0 ]; then
        echo
        echo "Diagrams are out of date. Run 'composer diagrams' and commit the updated PNGs." >&2
    fi
    exit "$status"
fi

for source in "${SOURCES[@]}"; do
    name="$(basename "$source" .puml)"
    echo "  $OUTPUT_DIR/$name.png"
done
