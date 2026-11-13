#!/usr/bin/env python3

import base64
import json
import sys

import pymupdf

from math_regions import group_text, math_regions

MAX_REGIONS = 60
MAX_PAGES = 40


def detect_regions(pdf_path):
    results = []
    with pymupdf.open(pdf_path) as doc:
        for page_index in range(min(len(doc), MAX_PAGES)):
            page = doc[page_index]
            for region in math_regions(page, page_index + 1):
                pix = page.get_pixmap(clip=region["rect"], dpi=300, alpha=False)
                results.append({
                    "page": page_index + 1,
                    "region_key": region["key"],
                    "image_base64": base64.b64encode(pix.tobytes("png")).decode("ascii"),
                    "source_text": group_text(region["spans"]),
                })
                if len(results) >= MAX_REGIONS:
                    return results
    return results


if __name__ == "__main__":
    if len(sys.argv) != 2:
        raise SystemExit("Usage: detect_formula_regions.py <pdf_path>")
    print(json.dumps(detect_regions(sys.argv[1])))
