/* ============================================================
   InkTale — shared client logic for the static build.

   The PHP version kept the catalogue in MySQL and the basket in a
   PHP session. Here the catalogue is a JSON file fetched once, and
   the basket lives in localStorage, so the whole site runs as plain
   static files with no server behind it.
   ============================================================ */

const CART_KEY  = 'inktale_cart_v1';
const LIB_KEY   = 'inktale_library_v1';

/* ---------------- catalogue ---------------- */
let BOOKS = [];

async function loadBooks() {
  if (BOOKS.length) return BOOKS;
  try {
    const res = await fetch('data/books.json');
    if (!res.ok) throw new Error(res.status);
    BOOKS = await res.json();
  } catch (err) {
    console.error('Could not load the catalogue:', err);
    BOOKS = [];
  }
  return BOOKS;
}

const bookById = id => BOOKS.find(b => b.id === Number(id));

/* ---------------- basket ---------------- */
function readStore(key) {
  try { return JSON.parse(localStorage.getItem(key)) || {}; }
  catch { return {}; }
}

function writeStore(key, value) {
  try { localStorage.setItem(key, JSON.stringify(value)); }
  catch (err) { console.warn('Storage unavailable:', err); }
}

function getCart() {
  const raw = readStore(CART_KEY);
  const clean = {};
  for (const [id, qty] of Object.entries(raw)) {
    const n = Number(id), q = Number(qty);
    if (n > 0 && q > 0) clean[n] = q;
  }
  return clean;
}

function saveCart(cart) { writeStore(CART_KEY, cart); updateCartCount(); }

function addToCart(id, qty = 1) {
  const cart = getCart();
  cart[id] = (cart[id] || 0) + Math.max(1, qty);
  saveCart(cart);
}

function setCartQty(id, qty) {
  const cart = getCart();
  if (qty <= 0) delete cart[id]; else cart[id] = qty;
  saveCart(cart);
}

function clearCart() { saveCart({}); }

const cartCount = () => Object.values(getCart()).reduce((a, b) => a + b, 0);

/* ---------------- library (past "purchases") ---------------- */
function getLibrary() {
  const raw = readStore(LIB_KEY);
  return Array.isArray(raw) ? raw : [];
}

function addToLibrary(items) {
  const library = getLibrary();
  library.unshift({ date: new Date().toISOString(), items });
  writeStore(LIB_KEY, library);
}

/* ---------------- shared UI ---------------- */
function updateCartCount() {
  const n = cartCount();
  document.querySelectorAll('[data-cart-count]').forEach(el => {
    el.textContent = n > 0 ? ` (${n})` : '';
  });
}

const money = n => Number(n).toFixed(2) + ' SAR';

const esc = s => String(s ?? '').replace(/[&<>"']/g, c =>
  ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

/* Brief confirmation after adding to the basket. */
function toast(message) {
  let el = document.getElementById('inktale-toast');
  if (!el) {
    el = document.createElement('div');
    el.id = 'inktale-toast';
    el.style.cssText =
      'position:fixed;left:50%;bottom:28px;transform:translateX(-50%) translateY(14px);' +
      'background:#1b1816;color:#e8d9c0;padding:12px 24px;border-radius:4px;' +
      'font-size:14px;z-index:999;opacity:0;transition:opacity .28s,transform .28s;' +
      'box-shadow:0 8px 26px rgba(0,0,0,.3);pointer-events:none';
    document.body.appendChild(el);
  }
  el.textContent = message;
  requestAnimationFrame(() => {
    el.style.opacity = '1';
    el.style.transform = 'translateX(-50%) translateY(0)';
  });
  clearTimeout(el._t);
  el._t = setTimeout(() => {
    el.style.opacity = '0';
    el.style.transform = 'translateX(-50%) translateY(14px)';
  }, 1800);
}

document.addEventListener('DOMContentLoaded', updateCartCount);
