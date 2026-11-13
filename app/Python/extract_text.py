#!/usr/bin/env python3
"""
Multi-method PDF text extractor for SIMBA LMS.
Runs pdfplumber, PyMuPDF, and Tesseract OCR, then picks the best result.
"""

import sys
import os
import json
import re
import shutil
import subprocess
import tempfile
from pathlib import Path

# Force UTF-8 stdout on Windows
if sys.platform == "win32":
    import codecs
    sys.stdout = codecs.getwriter("utf-8")(sys.stdout.buffer, "replace")

# ─── CONFIG ──────────────────────────────────────────────────────────────────
# Script lives at <project>/app/Python/extract_text.py
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_DIR = os.path.dirname(os.path.dirname(SCRIPT_DIR))
BAWAAN_TESSERACT = r"C:\Program Files\Tesseract-OCR\tesseract.exe"

if SCRIPT_DIR not in sys.path:
    sys.path.insert(0, SCRIPT_DIR)

try:
    from math_regions import marked_text
except Exception as e:  # tanpa penanda rumus ekstraksi tetap berjalan seperti sebelumnya
    print(f"math_regions tidak tersedia: {e}", file=sys.stderr)
    marked_text = None


def resolve_tesseract() -> str:
    """Cari binary Tesseract: env TESSERACT_PATH, PATH, lalu lokasi umum.

    Dulu path Windows di-hardcode sehingga OCR selalu gagal di container Linux.
    """
    dari_env = os.environ.get("TESSERACT_PATH")
    if dari_env and os.path.exists(dari_env):
        return dari_env

    dari_path = shutil.which("tesseract")
    if dari_path:
        return dari_path

    for kandidat in (BAWAAN_TESSERACT, "/usr/bin/tesseract", "/usr/local/bin/tesseract"):
        if os.path.exists(kandidat):
            return kandidat

    return dari_env or "tesseract"


def resolve_tessdata() -> str:
    """Utamakan TESSDATA_PREFIX sistem, lalu storage/tessdata milik proyek."""
    dari_env = os.environ.get("TESSDATA_PREFIX")
    if dari_env and os.path.isdir(dari_env):
        return dari_env

    lokal = os.path.join(PROJECT_DIR, "storage", "tessdata")
    if os.path.isdir(lokal):
        return lokal

    return dari_env or lokal


TESSERACT_PATH = resolve_tesseract()
TESSDATA_DIR = resolve_tessdata()
TESSERACT_LANG = "ind+eng"  # Indonesian + English
MIN_TEXT_LENGTH = 50          # Minimum extracted text to be considered valid
GARBAGE_THRESHOLD = 0.15     # Max ratio of non-printable chars to be acceptable

if TESSDATA_DIR:
    os.environ["TESSDATA_PREFIX"] = TESSDATA_DIR


def clean_text(text: str) -> str:
    """Normalize whitespace and strip control characters."""
    text = re.sub(r'[ \t]+', ' ', text)
    text = re.sub(r'\n{3,}', '\n\n', text)
    # Remove control chars except newline/tab
    text = re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]', '', text)
    return text.strip()


def garbage_ratio(text: str) -> float:
    """Calculate ratio of non-printable / replacement chars."""
    if not text:
        return 1.0
    bad = sum(1 for c in text if ord(c) < 32 and c not in '\n\t'
              or ord(c) == 0xFFFD
              or 0xF000 <= ord(c) <= 0xFFFF)  # Private Use Area
    return bad / len(text)


def similarity(a: str, b: str) -> float:
    """Simple char-level overlap ratio between two texts."""
    if not a or not b:
        return 0.0
    set_a = set(a[:5000])
    set_b = set(b[:5000])
    overlap = len(set_a & set_b)
    total = len(set_a | set_b)
    return overlap / total if total else 0.0


# ─── METHOD 1: pdfplumber ───────────────────────────────────────────────────
def extract_pdfplumber(pdf_path: str) -> dict:
    """Extract text using pdfplumber (best for font-encoded PDFs)."""
    try:
        import pdfplumber
        pages = []
        full_text = []
        with pdfplumber.open(pdf_path) as pdf:
            for i, page in enumerate(pdf.pages):
                txt = page.extract_text() or ""
                txt = clean_text(txt)
                if txt:
                    pages.append({"page": i + 1, "text": txt})
                    full_text.append(txt)
        combined = "\n\n".join(full_text)
        return {
            "method": "pdfplumber",
            "ok": True,
            "length": len(combined),
            "pages": len(pages),
            "garbage_ratio": round(garbage_ratio(combined), 4),
            "text": combined,
            "pages_detail": pages,
        }
    except Exception as e:
        return {"method": "pdfplumber", "ok": False, "error": str(e), "text": "", "length": 0}


# ─── METHOD 2: PyMuPDF (fitz) ──────────────────────────────────────────────
def ada_font_math(page) -> bool:
    """Gerbang murah: halaman tanpa font math tidak akan pernah dapat penanda."""
    try:
        return any("math" in (str(f[3]) + str(f[4])).lower() for f in page.get_fonts())
    except Exception:
        return True


def extract_pymupdf(pdf_path: str) -> dict:
    """Extract text using PyMuPDF (best Unicode handling).

    Halaman yang punya region rumus ditulis dengan penanda region pada tempat
    glif math, supaya teks rusak tidak ikut ter-embed dan bisa diganti saat
    guru menyetujui hasil OCR.
    """
    try:
        import pymupdf
        doc = pymupdf.open(pdf_path)
        pages = []
        full_text = []
        for i in range(len(doc)):
            page = doc[i]
            regions = 0
            if marked_text is not None and ada_font_math(page):
                txt, regions = marked_text(page, i + 1)
            else:
                txt = page.get_text("text") or ""
            txt = clean_text(txt)
            if txt:
                pages.append({"page": i + 1, "text": txt, "math_regions": regions})
                full_text.append(txt)
        doc.close()
        combined = "\n\n".join(full_text)
        return {
            "method": "pymupdf",
            "ok": True,
            "length": len(combined),
            "pages": len(pages),
            "math_regions": sum(p["math_regions"] for p in pages),
            "garbage_ratio": round(garbage_ratio(combined), 4),
            "text": combined,
            "pages_detail": pages,
        }
    except Exception as e:
        return {"method": "pymupdf", "ok": False, "error": str(e), "text": "", "length": 0}


# ─── METHOD 3: Tesseract OCR ────────────────────────────────────────────────
def extract_tesseract(pdf_path: str, skip_pages: set[int] | None = None) -> dict:
    """Extract text via Tesseract OCR (converts PDF pages to images first).

    Pages already covered by text-layer extraction (pdfplumber/pymupdf) are
    skipped to avoid wasteful OCR on digital-text PDFs.
    """
    if skip_pages is None:
        skip_pages = set()
    if not os.path.exists(TESSERACT_PATH):
        return {"method": "tesseract", "ok": False, "error": "Tesseract not found", "text": "", "length": 0}
    if not os.path.isdir(TESSDATA_DIR):
        return {"method": "tesseract", "ok": False, "error": f"tessdata dir not found: {TESSDATA_DIR}", "text": "", "length": 0}
    try:
        import pymupdf as fitz_doc  # PyMuPDF for PDF→image conversion
        doc = fitz_doc.open(pdf_path)
        pages = []
        full_text = []
        total_pages = len(doc)
        # Limit OCR to first 50 pages to avoid timeout
        ocr_limit = min(total_pages, 50)
        for i in range(ocr_limit):
            page_num = i + 1
            if page_num in skip_pages:
                continue
            page = doc[i]
            # Render page to image (2x resolution for better OCR)
            pix = page.get_pixmap(dpi=200)
            # Unique temp name so concurrent ingestions don't collide.
            fd, img_path = tempfile.mkstemp(prefix="simba_ocr_", suffix=".png")
            os.close(fd)
            pix.save(img_path)
            # Run Tesseract
            try:
                result = subprocess.run(
                    [TESSERACT_PATH, img_path, "stdout", "-l", TESSERACT_LANG,
                     "--psm", "6", "--oem", "3"],
                    capture_output=True, text=True, timeout=60, encoding="utf-8", errors="replace"
                )
                txt = clean_text(result.stdout)
                if txt:
                    pages.append({"page": page_num, "text": txt})
                    full_text.append(txt)
            except subprocess.TimeoutExpired:
                pass
            finally:
                if os.path.exists(img_path):
                    os.remove(img_path)
        doc.close()
        combined = "\n\n".join(full_text)
        return {
            "method": "tesseract",
            "ok": len(full_text) > 0,
            "length": len(combined),
            "pages": len(pages),
            "garbage_ratio": round(garbage_ratio(combined), 4),
            "text": combined,
            "pages_detail": pages,
        }
    except Exception as e:
        return {"method": "tesseract", "ok": False, "error": str(e), "text": "", "length": 0}


# ─── METHOD 4: pix2tex LaTeX-OCR (formulas as images) ─────────────────────
_latex_ocr_model = None


def _get_latex_ocr():
    """Lazily load the pix2tex LaTeX-OCR model (downloads weights on first use).

    Uses the GPU (CUDA) when available. pix2tex defaults to ``no_cuda=True``,
    so we must explicitly pass ``no_cuda=False`` to enable GPU inference.
    """
    global _latex_ocr_model
    if _latex_ocr_model is None:
        import torch
        from munch import Munch
        from pix2tex.cli import LatexOCR

        use_cuda = torch.cuda.is_available()
        args = Munch({
            'config': 'settings/config.yaml',
            'checkpoint': 'checkpoints/weights.pth',
            'no_cuda': not use_cuda,
            'no_resize': False,
        })
        _latex_ocr_model = LatexOCR(args)
    return _latex_ocr_model


def extract_latexocr(pdf_path: str) -> dict:
    """
    Extract formulas embedded as images using pix2tex LaTeX-OCR.

    Scans each PDF page for embedded images (equations are often image
    objects), runs pix2tex on the image regions, and emits the recognized
    LaTeX. This recovers formulas that text-layer extractors miss.
    """
    try:
        import pymupdf
        import io
        from PIL import Image

        model = _get_latex_ocr()

        doc = pymupdf.open(pdf_path)
        pages = []
        full_text = []
        total_pages = len(doc)
        # Limit processed pages to avoid extremely long runs.
        ocr_limit = min(total_pages, 40)
        processed_regions = 0
        max_regions = 60  # global cap to bound runtime

        for i in range(ocr_limit):
            page = doc[i]
            formulas = []
            # Enumerate embedded images / displayed equation regions.
            try:
                image_infos = page.get_image_info()
            except Exception:
                image_infos = []

            for info in image_infos:
                if "bbox" not in info:
                    continue
                x0, y0, x1, y1 = info["bbox"]
                w = x1 - x0
                h = y1 - y0
                if w < 10 or h < 5:
                    continue
                # Equations are typically wide-and-short. Skip large/full-width
                # decorations (logos, photos, figures) which are rarely formulas.
                if w > 0.7 * page.rect.width and h > 0.5 * page.rect.height:
                    continue
                if processed_regions >= max_regions:
                    break
                try:
                    rect = pymupdf.Rect(x0, y0, x1, y1)
                    pix = page.get_pixmap(clip=rect, dpi=150)
                    img_bytes = pix.tobytes("png")
                    img = Image.open(io.BytesIO(img_bytes)).convert("RGB")
                    latex = model(img)
                    processed_regions += 1
                    if latex and latex.strip() and len(latex.strip()) > 2:
                        formulas.append(latex.strip())
                except Exception:
                    continue
            if processed_regions >= max_regions:
                break

            # Only render page-level OCR fallback for pages that had no images
            # but our global formula cap hasn't been reached.
            if not formulas and processed_regions < max_regions:
                pix = page.get_pixmap(dpi=150)
                img = Image.open(io.BytesIO(pix.tobytes("png"))).convert("RGB")
                try:
                    latex = model(img)
                    processed_regions += 1
                    if latex and latex.strip() and len(latex.strip()) > 2:
                        formulas.append(latex.strip())
                except Exception:
                    pass

            if formulas:
                page_text = "FORMULA: " + " | FORMULA: ".join(dict.fromkeys(formulas))
                pages.append({"page": i + 1, "text": page_text})
                full_text.append(page_text)

        doc.close()
        combined = "\n\n".join(full_text)
        return {
            "method": "latex_ocr",
            "ok": len(full_text) > 0,
            "length": len(combined),
            "pages": len(pages),
            "garbage_ratio": round(garbage_ratio(combined), 4),
            "text": combined,
            "pages_detail": pages,
        }
    except Exception as e:
        return {"method": "latex_ocr", "ok": False, "error": str(e), "text": "", "length": 0}
def _page_map(result: dict) -> dict:
    """Return a {page_number: text} dict from a result's pages_detail."""
    out = {}
    for p in result.get("pages_detail", []) or []:
        out[p["page"]] = p["text"]
    return out


def _is_text_page(text: str) -> bool:
    """A page is considered readable by text-methods if it has real content."""
    return len(text.strip()) >= 10


def merge_results(results: list) -> dict:
    """
    Synchronize the three extraction methods into ONE complete result
    by merging per-page content — NOT by eliminating sources.

    Strategy (per page, prefer the most accurate readable source):
    1. Collect each method's per-page text into a page map.
    2. Determine total page count from the method with the most pages.
    3. For every page:
       a. Start with the text-based method (pdfplumber vs pymupdf) whose
          page text has the best (lowest) garbage ratio, or is longest —
          this is the "text source" (accurate for font-encoded text).
       b. If the text source for this page is empty/too small AND the OCR
          produced content for this page → use the OCR text (handles
          image-only pages inside a mixed PDF).
       c. Otherwise keep the best text source.
       d. Record which method(s) contributed.
    4. Full text = merged pages in page order.
    """
    text_methods = [r for r in results if r["method"] in ("pdfplumber", "pymupdf") and r.get("ok")]
    ocr_method = next((r for r in results if r["method"] == "tesseract" and r.get("ok")), None)
    latex_method = next((r for r in results if r["method"] == "latex_ocr" and r.get("ok")), None)

    page_maps = {}
    for r in results:
        if r.get("ok"):
            page_maps[r["method"]] = _page_map(r)

    # Halaman yang memuat region rumus: hanya teks PyMuPDF yang punya penanda,
    # jadi metode lain tidak boleh menang di halaman itu meski hasilnya lebih
    # bersih menurut garbage_ratio (teks pdfplumber sama-sama rusak).
    region_maps = {}
    for r in results:
        if r.get("ok") and r["method"] == "pymupdf":
            region_maps = {p["page"]: p.get("math_regions", 0) for p in r.get("pages_detail", []) or []}

    # Total pages: use the max across methods that produced pages.
    total_pages = 0
    for m in page_maps.values():
        if m:
            total_pages = max(total_pages, max(m.keys()))

    if total_pages == 0:
        return {"chosen_method": None, "reason": "all_failed", "text": "",
                "text_length": 0, "pages_detail": [], "sources": {}}

    merged_pages = []
    page_sources = {}

    for pno in range(1, total_pages + 1):
        candidates = []

        # Gather text from each text method for this page.
        for tm in text_methods:
            m = page_maps.get(tm["method"], {})
            if pno in m:
                candidates.append((tm["method"], m[pno]))

        bertanda = bool(region_maps.get(pno)) and any(m == "pymupdf" for m, _ in candidates)
        if bertanda:
            candidates = [(m, t) for m, t in candidates if m == "pymupdf"]

        # OCR text for this page (if any).
        ocr_txt = ""
        if ocr_method:
            om = page_maps.get("tesseract", {})
            ocr_txt = om.get(pno, "")

        # LaTeX-OCR formulas for this page (if any).
        latex_txt = ""
        if latex_method:
            lm = page_maps.get("latex_ocr", {})
            latex_txt = lm.get(pno, "")

        # Step 1: choose the best readable text source for this page.
        best = None
        if candidates:
            # Prefer the candidate that has real content; tie-break by garbage
            # ratio (higher accuracy) then by length.
            readable = [(mth, txt, garbage_ratio(txt), len(txt)) for mth, txt in candidates]
            readable = [c for c in readable if c[2] < 0.5]
            if readable:
                best = min(readable, key=lambda c: (c[2], -c[3]))
            elif candidates:
                best = (candidates[0][0], candidates[0][1], garbage_ratio(candidates[0][1]), len(candidates[0][1]))

        # Step 2: assemble the final page content by COMBINING available sources
        # (not eliminating).
        parts = []
        source_tags = []
        has_text_body = best is not None and _is_text_page(best[1])

        # Case A: text-layer body exists → use it as the base, append formulas.
        if has_text_body:
            parts.append(best[1])
            source_tags.append(best[0])
            if latex_txt and len(latex_txt.strip()) >= 6:
                parts.append(latex_txt)
                source_tags.append("latex_ocr")

        # Case B: image-only page (no text layer) → combine full-page OCR text
        # with any LaTeX-OCR formulas.
        else:
            if _is_text_page(ocr_txt):
                parts.append(ocr_txt)
                source_tags.append("tesseract")
            if latex_txt and len(latex_txt.strip()) >= 6:
                parts.append(latex_txt)
                source_tags.append("latex_ocr")

        if parts:
            merged_pages.append({"page": pno, "text": "\n\n".join(parts), "math_regions": int(region_maps.get(pno, 0))})
            page_sources[pno] = list(dict.fromkeys(source_tags))

    # Build full text from merged pages.
    full_text = "\n\n".join(p["text"] for p in merged_pages)

    # Determine a single dominant method label for backward compat.
    flat = [m for v in page_sources.values() for m in v]
    dominant = max(set(flat), key=lambda s: flat.count(s)) if flat else "merged"

    return {
        "chosen_method": dominant,
        "reason": "merged_all_sources",
        "text": full_text,
        "text_length": len(full_text),
        "pages_detail": merged_pages,
        "sources": {str(k): v for k, v in page_sources.items()},
    }


# ─── MAIN ────────────────────────────────────────────────────────────────────
def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Usage: python extract_text.py <pdf_path>"}))
        sys.exit(1)

    pdf_path = sys.argv[1]
    if not os.path.exists(pdf_path):
        print(json.dumps({"error": f"File not found: {pdf_path}"}))
        sys.exit(1)

    # Run the text-layer methods first.
    r1 = extract_pdfplumber(pdf_path)
    r2 = extract_pymupdf(pdf_path)

    # Determine which pages already have readable text from text-layer methods,
    # so Tesseract OCR can skip them (avoid wasteful OCR on digital-text PDFs).
    text_page_maps = {}
    for r in (r1, r2):
        if r.get("ok"):
            text_page_maps[r["method"]] = _page_map(r)
    skip_ocr_pages: set[int] = set()
    if text_page_maps:
        # Union of all pages that have >= MIN_TEXT_LENGTH chars from any text method.
        all_text_pages = set()
        for pmap in text_page_maps.values():
            for pno, ptext in pmap.items():
                if _is_text_page(ptext):
                    all_text_pages.add(pno)
        skip_ocr_pages = all_text_pages

    # Run Tesseract OCR only on pages that lack text-layer content.
    r3 = extract_tesseract(pdf_path, skip_pages=skip_ocr_pages)

    # Run LaTeX-OCR (pix2tex) ONLY when the document actually contains image
    # regions (equations embedded as images) OR text extraction was poor.
    r4 = None
    r4_candidate = {
        "method": "latex_ocr", "ok": False, "length": 0, "pages": 0,
        "garbage_ratio": 0, "error": "not run",
    }
    try:
        import pymupdf
        _has_imgs = False
        _doc = pymupdf.open(pdf_path)
        for _i in range(min(len(_doc), 40)):
            if _doc[_i].get_image_info():
                _has_imgs = True
                break
        _doc.close()
        # LaTeX-OCR is expensive; only run when images exist.
        if _has_imgs:
            r4 = extract_latexocr(pdf_path)
        else:
            r4_candidate["error"] = "no_image_regions_found"
    except Exception as _e:
        r4_candidate["error"] = f"not_run:{_e}"

    all_results = [r1, r2, r3] + ([r4] if r4 is not None else [])

    # Synchronize (merge) all results into ONE complete output.
    merged = merge_results(all_results)

    # Strip page details from candidates to keep output manageable
    candidates_summary = []
    for r in all_results:
        candidates_summary.append({
            "method": r["method"],
            "ok": r.get("ok", False),
            "length": r.get("length", 0),
            "pages": r.get("pages", 0),
            "garbage_ratio": r.get("garbage_ratio", 0),
            "error": r.get("error"),
        })
    if r4 is None:
        candidates_summary.append(r4_candidate)

    output = {
        "chosen_method": merged["chosen_method"],
        "reason": merged["reason"],
        "candidates": candidates_summary,
        "text": merged["text"],
        "text_length": merged["text_length"],
        "pages_detail": merged["pages_detail"],
        "sources": merged["sources"],
    }

    print(json.dumps(output, ensure_ascii=False))


if __name__ == "__main__":
    main()
