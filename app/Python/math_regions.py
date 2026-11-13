#!/usr/bin/env python3
"""Geometri region rumus: satu sumber kebenaran untuk kunci region dan penanda teks.

Ekstraksi teks dan pemindaian region wajib memakai pengelompokan, padding, dan
hash yang sama: penanda yang tertanam di dalam chunk adalah alamat yang dicari
saat guru menyetujui sebuah rumus. Kalau keduanya sampai berbeda jalur,
persetujuan kembali diam-diam jadi sekadar menambah chunk.
"""

import hashlib
import re

import pymupdf

GAP = 6.0  # toleransi jarak horizontal antar glif, dalam poin
MIN_SIZE = 5.0
PADDING = (-2.0, -2.0, 2.0, 1.0)
BASELINE_SLACK = 2.0
MARKER_HEX = 12


def is_math(font: str) -> bool:
    return "math" in (font or "").lower()


def region_key(page_number: int, rect) -> str:
    return hashlib.sha256(
        f"{page_number}:{rect.x0:.1f}:{rect.y0:.1f}:{rect.x1:.1f}:{rect.y1:.1f}".encode()
    ).hexdigest()


def marker(key: str) -> str:
    return f"⟦rumus:{key[:MARKER_HEX]}⟧"


def group_text(spans) -> str:
    """Teks lapisan PDF milik sebuah grup region, dipadatkan jadi satu baris."""
    chars = []
    for span in spans:
        for char in span["chars"]:
            bbox = char[3]
            chars.append((round(bbox[1], 1), round(bbox[0], 1), chr(char[0])))
    chars.sort()
    return re.sub(r"\s+", " ", "".join(char[2] for char in chars)).strip()


def group_math_spans(page, gap: float = GAP) -> list:
    """Span font math yang saling berdekatan digabung menjadi satu grup region."""
    groups = []
    for span in page.get_texttrace():
        if not is_math(span["font"]) or not span["chars"]:
            continue
        rect = pymupdf.Rect(span["bbox"])
        if rect.is_empty:
            continue
        for group in groups:
            if (rect.x0 <= group["rect"].x1 + gap and rect.x1 >= group["rect"].x0 - gap
                    and rect.y0 <= group["rect"].y1 + 1 and rect.y1 >= group["rect"].y0 - 1):
                group["rect"].include_rect(rect)
                group["spans"].append(span)
                break
        else:
            groups.append({"rect": rect, "spans": [span]})
    return groups


def math_regions(page, page_number: int, gap: float = GAP) -> list:
    """Region math dalam koordinat halaman, sudah dipadding dan di-clip, lengkap dengan kuncinya."""
    regions = []
    for group in group_math_spans(page, gap):
        bounds = group["rect"]
        if bounds.width < MIN_SIZE or bounds.height < MIN_SIZE:
            continue
        rect = pymupdf.Rect(
            bounds.x0 + PADDING[0],
            bounds.y0 + PADDING[1],
            bounds.x1 + PADDING[2],
            bounds.y1 + PADDING[3],
        ) & page.rect
        regions.append({"key": region_key(page_number, rect), "rect": rect, "spans": group["spans"]})
    return regions


def marked_text(page, page_number: int, gap: float = GAP):
    """Teks halaman dengan penanda region pada tempat glif math.

    Tanpa region math hasilnya persis `page.get_text("text")`, jadi dokumen normal
    tidak berubah sedikit pun. Kalau ada region, teks disusun ulang dari
    `get_text("dict")`: span non-math diambil apa adanya, span pertama sebuah
    region diganti penanda, span region berikutnya dibuang karena glifnya sudah
    diwakili penanda itu.

    Pencocokan span ke region memakai pusat sumbu-X span dan baseline-nya. bbox
    span `dict` tidak bisa dipakai untuk sumbu Y: tingginya menutupi beberapa
    baris, sehingga superskrip halaman lain ikut dianggap satu baris.
    """
    regions = math_regions(page, page_number, gap)
    if not regions:
        return page.get_text("text"), 0

    def region_of(span):
        x = (span["bbox"][0] + span["bbox"][2]) / 2
        baseline = span["origin"][1]
        for region in regions:
            rect = region["rect"]
            if rect.x0 - 1 <= x <= rect.x1 + 1 and rect.y0 - BASELINE_SLACK <= baseline <= rect.y1 + BASELINE_SLACK:
                return region
        return None

    ditandai = set()
    baris = []
    for block in page.get_text("dict")["blocks"]:
        if block["type"] != 0:
            continue
        for line in block["lines"]:
            bagian = []
            for span in line["spans"]:
                if not is_math(span["font"]):
                    bagian.append(span["text"])
                    continue
                region = region_of(span)
                if region is None:
                    bagian.append(span["text"])
                    continue
                if region["key"] not in ditandai:
                    ditandai.add(region["key"])
                    bagian.append(marker(region["key"]))
            baris.append("".join(bagian))

    return "\n".join(baris), len(regions)
