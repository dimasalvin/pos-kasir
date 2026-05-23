<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Kasir POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Caveat:wght@600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --teal:#2BBFA4; --teal-dark:#1E9A87; --teal-light:#E6F9F5;
            --coral:#FF6B6B; --coral-light:#FFF0F0;
            --sky:#5BA4E5; --sky-light:#EEF5FD;
            --gold:#F4C842;
            --purple:#7C6BE8; --purple-light:#F0EEFF;
            --bg:#F0F4F8; --surface:#fff;
            --border:#E2E8F0; --text:#1A2B3C; --muted:#7A90A8;
            --sidebar-w:240px;
            --radius:14px; --shadow:0 4px 20px rgba(0,0,0,.07);
        }
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        html,body { height:100%; font-family:'Nunito',sans-serif; color:var(--text);
                    background:var(--bg); -webkit-font-smoothing:antialiased; }

        /* ── Sidebar ── */
        .sidebar { position:fixed; top:0; left:0; bottom:0; width:var(--sidebar-w);
                   background:var(--text); color:white; display:flex; flex-direction:column;
                   z-index:50; transition:transform .3s ease; }
        .sidebar-logo { padding:24px 20px 20px; border-bottom:1px solid rgba(255,255,255,.08); }
        .sidebar-logo .title { font-family:'Caveat',cursive; font-size:22px; color:white; }
        .sidebar-logo .sub   { font-size:11px; color:rgba(255,255,255,.45); margin-top:2px; }
        .sidebar-user { padding:16px 20px; border-bottom:1px solid rgba(255,255,255,.08);
                        display:flex; align-items:center; gap:12px; }
        .user-avatar { width:36px; height:36px; border-radius:50%;
                       background:linear-gradient(135deg,var(--teal),var(--teal-dark));
                       display:flex; align-items:center; justify-content:center;
                       font-weight:800; font-size:14px; flex-shrink:0; }
        .user-info .name { font-size:13px; font-weight:700; color:white; line-height:1.3; }
        .user-info .role { font-size:10px; font-weight:700; text-transform:uppercase;
                           letter-spacing:.06em; color:var(--teal); margin-top:2px; }
        nav.sidebar-nav { flex:1; padding:16px 12px; overflow-y:auto; }
        .nav-section-label { font-size:10px; font-weight:800; text-transform:uppercase;
                              letter-spacing:.1em; color:rgba(255,255,255,.3);
                              padding:10px 8px 6px; margin-top:8px; }
        .nav-item { display:flex; align-items:center; gap:10px; padding:10px 12px;
                    border-radius:10px; text-decoration:none; color:rgba(255,255,255,.65);
                    font-size:14px; font-weight:600; transition:all .15s; margin-bottom:2px; }
        .nav-item:hover { background:rgba(255,255,255,.08); color:white; }
        .nav-item.active { background:var(--teal); color:white; box-shadow:0 4px 12px rgba(43,191,164,.35); }
        .nav-item .icon { font-size:16px; width:20px; text-align:center; flex-shrink:0; }
        .nav-badge { background:var(--coral); color:white; font-size:10px; font-weight:800;
                     padding:2px 7px; border-radius:10px; margin-left:auto; }
        .sidebar-bottom { padding:16px 12px; border-top:1px solid rgba(255,255,255,.08); }
        .logout-btn { display:flex; align-items:center; gap:10px; padding:10px 12px;
                      border-radius:10px; color:rgba(255,255,255,.55); font-size:14px;
                      font-weight:600; background:none; border:none; width:100%;
                      text-align:left; cursor:pointer; font-family:'Nunito',sans-serif;
                      transition:all .15s; }
        .logout-btn:hover { background:rgba(255,100,100,.15); color:var(--coral); }

        /* ── Main ── */
        .main-wrap { margin-left:var(--sidebar-w); min-height:100vh; display:flex; flex-direction:column; }

        /* ── Topbar ── */
        .topbar { background:var(--surface); border-bottom:1px solid var(--border);
                  padding:0 28px; height:60px; display:flex; align-items:center;
                  justify-content:space-between; position:sticky; top:0; z-index:40;
                  box-shadow:0 1px 4px rgba(0,0,0,.04); }
        .topbar-title { font-size:17px; font-weight:800; color:var(--text); }
        .topbar-right  { display:flex; align-items:center; gap:16px; }
        .topbar-date   { font-size:12px; color:var(--muted); }
        .hamburger { display:none; background:none; border:none; cursor:pointer; padding:4px; }
        .hamburger span { display:block; width:22px; height:2px; background:var(--text);
                          margin:5px 0; border-radius:2px; }

        /* ── Page Content ── */
        .page-content { flex:1; padding:24px 28px 40px; }

        /* ── Stats Grid ── */
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
                      gap:16px; margin-bottom:28px; }
        .stat-card { background:var(--surface); border-radius:var(--radius); padding:20px;
                     box-shadow:var(--shadow); border:1px solid var(--border);
                     display:flex; flex-direction:column; gap:8px; transition:transform .2s; }
        .stat-card:hover { transform:translateY(-2px); }
        .stat-icon  { font-size:22px; line-height:1; }
        .stat-value { font-size:28px; font-weight:800; color:var(--text); line-height:1; }
        .stat-label { font-size:12px; color:var(--muted); font-weight:600; }
        .stat-card.teal   { border-top:3px solid var(--teal); }
        .stat-card.coral  { border-top:3px solid var(--coral); }
        .stat-card.sky    { border-top:3px solid var(--sky); }
        .stat-card.purple { border-top:3px solid var(--purple); }
        .stat-card.gold   { border-top:3px solid var(--gold); }

        /* ── Card ── */
        .card { background:var(--surface); border-radius:var(--radius);
                box-shadow:var(--shadow); border:1px solid var(--border); overflow:hidden; }
        .card-header { padding:18px 22px; border-bottom:1px solid var(--border);
                       display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
        .card-title    { font-size:15px; font-weight:800; color:var(--text); }
        .card-subtitle { font-size:12px; color:var(--muted); margin-top:2px; }
        .card-body     { padding:22px; }
        .chart-wrap    { position:relative; height:260px; }

        /* ── Grid ── */
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }
        .grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:20px; }
        .mb-20  { margin-bottom:20px; }
        .mb-28  { margin-bottom:28px; }

        /* ── Table ── */
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; font-size:14px; }
        th { padding:11px 16px; text-align:left; background:var(--bg);
             border-bottom:2px solid var(--border); font-size:11px; font-weight:800;
             text-transform:uppercase; letter-spacing:.06em; color:var(--muted); white-space:nowrap; }
        td { padding:12px 16px; border-bottom:1px solid var(--border); vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:var(--bg); }

        /* ── Badges ── */
        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px;
                 border-radius:20px; font-size:11px; font-weight:800;
                 text-transform:uppercase; letter-spacing:.05em; }
        .badge-teal   { background:var(--teal-light);   color:var(--teal-dark); }
        .badge-coral  { background:var(--coral-light);  color:var(--coral); }
        .badge-sky    { background:var(--sky-light);    color:var(--sky); }
        .badge-purple { background:var(--purple-light); color:var(--purple); }
        .badge-gold   { background:#FFF9E6; color:#996B00; }

        /* ── Buttons ── */
        .btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px;
               border-radius:8px; font-size:13px; font-weight:700; cursor:pointer;
               text-decoration:none; border:none; font-family:'Nunito',sans-serif;
               transition:all .15s; }
        .btn-primary { background:var(--teal); color:white; box-shadow:0 2px 8px rgba(43,191,164,.3); }
        .btn-primary:hover { background:var(--teal-dark); }
        .btn-danger  { background:var(--coral-light); color:var(--coral); }
        .btn-danger:hover  { background:var(--coral); color:white; }
        .btn-ghost   { background:var(--bg); color:var(--muted); border:1px solid var(--border); }
        .btn-ghost:hover { color:var(--text); background:var(--border); }
        .btn-sm      { padding:5px 11px; font-size:12px; }
        .btn-warning { background:#FFF9E6; color:#996B00; }
        .btn-warning:hover { background:#F4C842; color:#fff; }

        /* ── Form ── */
        .form-group   { margin-bottom:18px; }
        .form-label   { display:block; font-size:12px; font-weight:800; color:var(--muted);
                        text-transform:uppercase; letter-spacing:.06em; margin-bottom:7px; }
        .form-control { width:100%; padding:11px 14px; border:2px solid var(--border);
                        border-radius:9px; font-size:14px; font-family:'Nunito',sans-serif;
                        color:var(--text); background:var(--surface); transition:border-color .2s; }
        .form-control:focus { outline:none; border-color:var(--teal);
                              box-shadow:0 0 0 3px rgba(43,191,164,.1); }
        select.form-control { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%237A90A8' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
                              background-repeat:no-repeat; background-position:right 12px center; padding-right:32px; }
        .form-error { color:var(--coral); font-size:12px; margin-top:4px; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }

        /* ── Alerts ── */
        .alert { padding:12px 16px; border-radius:9px; font-size:13px; font-weight:600; margin-bottom:20px; }
        .alert-success { background:var(--teal-light); color:var(--teal-dark); border:1px solid var(--teal); }
        .alert-danger  { background:var(--coral-light); color:var(--coral); border:1px solid var(--coral); }
        .alert-warning { background:#FFF9E6; color:#996B00; border:1px solid var(--gold); }

        /* ── Pagination ── */
        .pagination { display:flex; gap:6px; align-items:center; padding:16px 22px; border-top:1px solid var(--border); flex-wrap:wrap; }
        .page-link { padding:6px 12px; border-radius:7px; font-size:13px; font-weight:700;
                     text-decoration:none; color:var(--muted); background:var(--bg); border:1px solid var(--border); }
        .page-link.active { background:var(--teal); color:white; border-color:var(--teal); }
        .page-link:hover:not(.active) { background:var(--border); color:var(--text); }

        /* ── Filter Bar ── */
        .filter-bar { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; margin-bottom:20px; }
        .filter-bar .form-group { margin-bottom:0; }
        .filter-bar .form-control { padding:8px 12px; font-size:13px; }

        /* ── Mobile ── */
        @media (max-width:768px) {
            .sidebar { transform:translateX(-100%); }
            .sidebar.open { transform:translateX(0); box-shadow:4px 0 24px rgba(0,0,0,.2); }
            .main-wrap { margin-left:0; }
            .hamburger { display:block; }
            .grid-2,.grid-3 { grid-template-columns:1fr; }
            .page-content { padding:16px; }
            .topbar { padding:0 16px; }
            .topbar-date { display:none; }
            .form-row { grid-template-columns:1fr; }
            .filter-bar { flex-direction:column; align-items:stretch; }
        }
        .overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:45; }
        .overlay.show { display:block; }
    </style>
    @stack('styles')
</head>
<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="title">💊 Kasir POS</div>
        <div class="sub">Point of Sale System</div>
    </div>
    <div class="sidebar-user">
        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <div class="user-info">
            <div class="name">{{ Str::limit(auth()->user()->name, 20) }}</div>
            <div class="role">{{ auth()->user()->role }}</div>
        </div>
    </div>
    <nav class="sidebar-nav">

        <div class="nav-section-label">Menu Utama</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="icon">📊</span> Dashboard
        </a>
        <a href="{{ route('kasir.index') }}" class="nav-item {{ request()->routeIs('kasir.*') ? 'active' : '' }}">
            <span class="icon">🛒</span> Kasir
        </a>

        @if(auth()->user()->isAdmin())
        <div class="nav-section-label">Master Data</div>
        <a href="{{ route('barang.index') }}" class="nav-item {{ request()->routeIs('barang.*') ? 'active' : '' }}">
            <span class="icon">📦</span> Barang
            @if(($stokRendahCount ?? 0) > 0)
                <span class="nav-badge">{{ $stokRendahCount }}</span>
            @endif
        </a>
        <a href="{{ route('supplier.index') }}" class="nav-item {{ request()->routeIs('supplier.*') ? 'active' : '' }}">
            <span class="icon">🏭</span> Supplier
        </a>

        <div class="nav-section-label">Transaksi</div>
        <a href="{{ route('pembelian.index') }}" class="nav-item {{ request()->routeIs('pembelian.*') ? 'active' : '' }}">
            <span class="icon">📥</span> Pembelian
        </a>
        <a href="{{ route('stock-opname.index') }}" class="nav-item {{ request()->routeIs('stock-opname.*') ? 'active' : '' }}">
            <span class="icon">📋</span> Stock Opname
        </a>
        <a href="{{ route('closing-kasir.index') }}" class="nav-item {{ request()->routeIs('closing-kasir.*') ? 'active' : '' }}">
            <span class="icon">🔒</span> Closing Kasir
        </a>

        <div class="nav-section-label">Laporan</div>
        <a href="{{ route('laporan.penjualan') }}" class="nav-item {{ request()->routeIs('laporan.penjualan') ? 'active' : '' }}">
            <span class="icon">📈</span> Lap. Penjualan
        </a>
        <a href="{{ route('laporan.stok') }}" class="nav-item {{ request()->routeIs('laporan.stok') ? 'active' : '' }}">
            <span class="icon">📊</span> Lap. Stok
        </a>
        <a href="{{ route('laporan.pembelian') }}" class="nav-item {{ request()->routeIs('laporan.pembelian') ? 'active' : '' }}">
            <span class="icon">📉</span> Lap. Pembelian
        </a>
        <a href="{{ route('laporan-kas.index') }}" class="nav-item {{ request()->routeIs('laporan-kas.*') ? 'active' : '' }}">
            <span class="icon">💰</span> Laporan Kas
        </a>

        <div class="nav-section-label">Pengaturan</div>
        <a href="{{ route('user.index') }}" class="nav-item {{ request()->routeIs('user.*') ? 'active' : '' }}">
            <span class="icon">👥</span> Manajemen User
        </a>
        @endif

    </nav>
    <div class="sidebar-bottom">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <span class="icon">🚪</span> Keluar
            </button>
        </form>
    </div>
</aside>

<!-- Main -->
<div class="main-wrap">
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:14px;">
            <button class="hamburger" onclick="openSidebar()">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar-title">@yield('page-title','Dashboard')</div>
        </div>
        <div class="topbar-right">
            <div class="topbar-date">{{ now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}</div>
        </div>
    </header>

    <main class="page-content">
        @if(session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif
        @if(session('error') || $errors->has('error'))
            <div class="alert alert-danger">{{ session('error') ?? $errors->first('error') }}</div>
        @endif
        @yield('content')
    </main>
</div>

<script>
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('overlay').classList.add('show');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('show');
}

/**
 * Format angka ke format Rupiah: Rp 1,000,000
 */
function formatRupiah(angka) {
    if (!angka && angka !== 0) return 'Rp 0';
    return 'Rp ' + Math.round(Number(angka)).toLocaleString('en-US');
}

/**
 * Parse string Rupiah kembali ke angka: "Rp 1,000,000" → 1000000
 */
function parseRupiah(str) {
    if (!str) return 0;
    return Number(String(str).replace(/[^0-9.-]/g, '')) || 0;
}

/**
 * Inisialisasi input currency (class="input-rupiah")
 * - Menampilkan format "Rp 1,000,000" saat user mengetik
 * - Menyimpan value asli di hidden input
 */
function initRupiahInputs() {
    document.querySelectorAll('.input-rupiah:not([data-rupiah-init])').forEach(function(input) {
        input.setAttribute('data-rupiah-init', '1');

        // Buat hidden input untuk menyimpan value asli
        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = input.name;
        hidden.value = input.value || 0;
        input.parentNode.insertBefore(hidden, input.nextSibling);

        // Hapus name & required dari input display agar tidak dikirim ke server
        input.removeAttribute('name');
        input.removeAttribute('required');
        input.type = 'text';
        input.inputMode = 'numeric';

        // Format value awal
        var initVal = Number(hidden.value) || 0;
        input.value = initVal > 0 ? formatRupiah(initVal) : 'Rp 0';

        // Format saat mengetik
        input.addEventListener('input', function() {
            var raw = parseRupiah(this.value);
            hidden.value = raw;
            var pos = this.selectionStart;
            var oldLen = this.value.length;
            this.value = raw > 0 ? formatRupiah(raw) : '';
            var newLen = this.value.length;
            var newPos = pos + (newLen - oldLen);
            this.setSelectionRange(newPos, newPos);
        });

        // Saat focus, select semua
        input.addEventListener('focus', function() {
            setTimeout(function() { input.select(); }, 50);
        });

        // Saat blur, pastikan format benar
        input.addEventListener('blur', function() {
            var raw = parseRupiah(this.value);
            hidden.value = raw;
            this.value = raw > 0 ? formatRupiah(raw) : 'Rp 0';
        });
    });
}

// Auto-init saat DOM ready
document.addEventListener('DOMContentLoaded', initRupiahInputs);

/**
 * Simpan & restore posisi scroll sidebar agar tidak reset saat pindah halaman
 */
(function() {
    var nav = document.querySelector('.sidebar-nav');
    if (!nav) return;

    // Restore posisi scroll dari localStorage
    var saved = localStorage.getItem('sidebar-scroll');
    if (saved) nav.scrollTop = parseInt(saved, 10);

    // Simpan posisi scroll setiap kali user scroll sidebar
    nav.addEventListener('scroll', function() {
        localStorage.setItem('sidebar-scroll', nav.scrollTop);
    });
})();
</script>
@stack('scripts')
</body>
</html>
