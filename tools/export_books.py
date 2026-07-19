#!/usr/bin/env python3
"""Convert the InkTale MySQL dump into a static books.json for the browser demo."""
import re, json, os

ROOT = os.path.join(os.path.dirname(__file__), "..", "server")
OUT  = os.path.join(os.path.dirname(__file__), "..")
SQL  = os.path.join(ROOT, "database/inktale_simple_db.sql")

sql = open(SQL, encoding="utf-8").read()

m = re.search(r"INSERT INTO `products`\s*\(([^)]*)\)\s*VALUES\s*", sql, re.S)
columns = [c.strip().strip('`') for c in m.group(1).split(',')]

body = sql[m.end():]
body = body[:body.index(";\n")] if ";\n" in body else body


def split_rows(text):
    """Yield each (...) tuple, respecting quotes and escapes."""
    rows, depth, cur = [], 0, []
    in_str = False
    i = 0
    while i < len(text):
        ch = text[i]
        if in_str:
            if ch == '\\':
                cur.append(ch)
                i += 1
                if i < len(text):
                    cur.append(text[i])
                i += 1
                continue
            if ch == "'":
                if i + 1 < len(text) and text[i + 1] == "'":
                    cur.append("''"); i += 2; continue
                in_str = False
            cur.append(ch); i += 1; continue
        if ch == "'":
            in_str = True; cur.append(ch); i += 1; continue
        if ch == '(':
            depth += 1
            if depth == 1:
                cur = []; i += 1; continue
        elif ch == ')':
            depth -= 1
            if depth == 0:
                rows.append(''.join(cur)); i += 1; continue
        if depth >= 1:
            cur.append(ch)
        i += 1
    return rows


def split_values(row):
    """Split one tuple's comma-separated values."""
    out, cur, in_str = [], [], False
    i = 0
    while i < len(row):
        ch = row[i]
        if in_str:
            if ch == '\\' and i + 1 < len(row):
                nxt = row[i + 1]
                cur.append({'n': '\n', 't': '\t', 'r': '\r'}.get(nxt, nxt))
                i += 2; continue
            if ch == "'":
                if i + 1 < len(row) and row[i + 1] == "'":
                    cur.append("'"); i += 2; continue
                in_str = False; i += 1; continue
            cur.append(ch); i += 1; continue
        if ch == "'":
            in_str = True; i += 1; continue
        if ch == ',':
            out.append(''.join(cur).strip()); cur = []; i += 1; continue
        cur.append(ch); i += 1
    out.append(''.join(cur).strip())
    return out


books = []
for row in split_rows(body):
    vals = split_values(row)
    if len(vals) != len(columns):
        print(f"  skipped a row: {len(vals)} values for {len(columns)} columns")
        continue
    rec = dict(zip(columns, vals))
    books.append({
        "id":          int(rec["product_id"]),
        "title":       rec["name"],
        "description": rec["description"],
        "cover":       rec["picture"],
        "price":       float(rec["price"]),
        "stock":       int(rec["stock"]),
        "category":    rec["category"],
        "language":    rec["language"],
        "author":      rec["author_name"],
        "authorBio":   rec["author_bio"],
        "authorPhoto": rec["author_picture"],
    })

books.sort(key=lambda b: b["id"])

os.makedirs(os.path.join(OUT, "data"), exist_ok=True)
with open(os.path.join(OUT, "data/books.json"), "w", encoding="utf-8") as f:
    json.dump(books, f, ensure_ascii=False, indent=1)

cats = sorted({b["category"] for b in books})
langs = sorted({b["language"] for b in books})
missing = [b["title"] for b in books if not os.path.exists(os.path.join(OUT, b["cover"]))]

print(f"books exported : {len(books)}")
print(f"categories     : {', '.join(cats)}")
print(f"languages      : {', '.join(langs)}")
print(f"missing covers : {len(missing)}")
print(f"price range    : {min(b['price'] for b in books):.2f} – {max(b['price'] for b in books):.2f} SAR")
