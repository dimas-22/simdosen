
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sudahLogin = isset($_SESSION['user_id']);
$linkLogin = $sudahLogin ? 'dashboard.php' : 'login.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="SIMDOSEN - Sistem Informasi Manajemen Dosen Universitas Muhammadiyah Bengkulu">
<title>SIMDOSEN | Sistem Informasi Manajemen Dosen</title>

<style>
:root {
    --biru: #1769e8;
    --biru-gelap: #102d60;
    --teks: #183461;
    --abu: #5e789c;
    --garis: #e1eafa;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    color: var(--teks);
    background: #ffffff;
    line-height: 1.6;
}

a {
    color: inherit;
    text-decoration: none;
}

.container {
    width: 87%;
    max-width: 1320px;
    margin: auto;
}

/* NAVBAR */
.navbar {
    min-height: 76px;
    padding: 12px 6.7%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
    background: white;
    border-bottom: 1px solid #edf2f9;
    position: sticky;
    top: 0;
    z-index: 10;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.logo {
    font-size: 39px;
    color: #1761ad;
}

.brand h1 {
    font-size: 25px;
    line-height: 1.2;
    color: #15549d;
}

.brand p {
    font-size: 11px;
    color: var(--abu);
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 30px;
}

.nav-links a {
    font-size: 13px;
    font-weight: bold;
    white-space: nowrap;
}

.nav-links a:hover,
.nav-links a.active {
    color: var(--biru);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: bold;
    transition: .2s;
}

.btn-primary {
    color: white;
    background: var(--biru);
    box-shadow: 0 7px 16px #1769e82b;
}

.btn-primary:hover {
    background: #1055c3;
    transform: translateY(-2px);
}

.btn-outline {
    color: var(--biru-gelap);
    background: white;
    border: 1px solid var(--biru);
}

.btn-outline:hover {
    background: #eef5ff;
}

/* HERO */
.hero {
    padding: 55px 0 48px;
    overflow: hidden;
    background:
        radial-gradient(ellipse at 70% 45%,
        #d8eaff 0%, #edf6ff 50%, #f8fbff 85%);
    position: relative;
}

.hero::after {
    content: "";
    position: absolute;
    width: 350px;
    height: 750px;
    right: -180px;
    top: -180px;
    border-radius: 50%;
    transform: rotate(40deg);
    background: linear-gradient(145deg, #1769e8, #b7d6ff);
    opacity: .75;
}

.hero-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    gap: 45px;
    position: relative;
    z-index: 1;
}

.pill {
    display: inline-block;
    background: #dceaff;
    color: #075ee0;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 18px;
}

.hero h2 {
    font-size: clamp(38px, 4vw, 56px);
    line-height: 1.12;
    letter-spacing: -1.3px;
    margin-bottom: 18px;
}

.hero h2 span {
    color: #2477e9;
}

.hero-description {
    font-size: 16px;
    color: #315b8e;
    margin-bottom: 24px;
    max-width: 590px;
}

.hero-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 13px;
}

.trust {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    margin-top: 25px;
    color: #3d5e87;
    font-size: 12px;
}

.check {
    display: inline-grid;
    place-items: center;
    width: 17px;
    height: 17px;
    margin-right: 5px;
    color: white;
    background: #20ad7c;
    border-radius: 50%;
    font-size: 11px;
}

/* ILUSTRASI DASHBOARD */
.dashboard-preview {
    padding: 12px;
    background: white;
    border-radius: 18px;
    box-shadow: 0 25px 55px #16365c30;
    transform: perspective(1000px) rotateY(-3deg);
}

.preview-screen {
    display: grid;
    grid-template-columns: 105px 1fr;
    min-height: 300px;
    overflow: hidden;
    border: 1px solid #dce7f5;
    border-radius: 11px;
    background: #f7faff;
}

.preview-sidebar {
    padding: 13px 8px;
    color: #dceaff;
    background: #102b55;
    font-size: 10px;
}

.preview-logo {
    color: white;
    font-weight: bold;
    margin-bottom: 20px;
}

.menu-item {
    padding: 7px 5px;
    margin: 4px 0;
    border-radius: 5px;
}

.menu-item.active {
    background: var(--biru);
    color: white;
}

.preview-content {
    padding: 14px;
    min-width: 0;
}

.preview-heading {
    font-size: 11px;
    margin-bottom: 17px;
}

.preview-heading small {
    font-size: 8px;
    color: var(--abu);
}

.metric-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 6px;
    margin-bottom: 12px;
}

.metric {
    padding: 8px 6px;
    color: white;
    border-radius: 7px;
    min-width: 0;
}

.metric small {
    display: block;
    font-size: 7px;
}

.metric strong {
    font-size: 21px;
}

.metric:nth-child(1) {
    background: linear-gradient(130deg, #2b80f7, #1458c7);
}

.metric:nth-child(2) {
    background: linear-gradient(130deg, #25cda0, #119779);
}

.metric:nth-child(3) {
    background: linear-gradient(130deg, #a079f4, #6e48d6);
}

.metric:nth-child(4) {
    background: linear-gradient(130deg, #ffc247, #f28b13);
}

.preview-panels {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.preview-panel {
    padding: 10px;
    border: 1px solid #e3ebf7;
    border-radius: 8px;
    background: white;
    font-size: 9px;
    min-width: 0;
}

.preview-panel strong {
    display: block;
    margin-bottom: 12px;
}

.chart-bars {
    height: 75px;
    display: flex;
    align-items: end;
    gap: 7px;
    border-bottom: 1px solid #dbe6f5;
}

.chart-bars span {
    flex: 1;
    background: #79adf7;
    border-radius: 3px 3px 0 0;
}

.activity {
    padding: 5px 0;
    border-bottom: 1px solid #eef2f8;
    color: #526784;
    font-size: 8px;
}

/* FITUR */
.features {
    padding: 35px 0 20px;
}

.section-heading {
    text-align: center;
    margin: 0 auto 28px;
    max-width: 800px;
}

.section-label {
    display: inline-block;
    background: #e4efff;
    color: var(--biru);
    padding: 5px 14px;
    border-radius: 25px;
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 7px;
}

.section-heading h2 {
    font-size: 29px;
    line-height: 1.3;
    margin-bottom: 5px;
}

.section-heading p {
    color: #6782a5;
    font-size: 14px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 18px;
}

.card {
    padding: 18px;
    border: 1px solid var(--garis);
    border-radius: 12px;
    background: white;
    transition: .2s;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px #183c7010;
    border-color: #b9d3fb;
}

.card-icon {
    width: 49px;
    height: 49px;
    display: grid;
    place-items: center;
    color: white;
    border-radius: 11px;
    font-size: 23px;
    margin-bottom: 11px;
}

.blue { background: linear-gradient(135deg, #3787f7, #1761d6); }
.green { background: linear-gradient(135deg, #28c98f, #10996c); }
.purple { background: linear-gradient(135deg, #a27af4, #7650dc); }
.orange { background: linear-gradient(135deg, #ffbd43, #ff8c17); }
.cyan { background: linear-gradient(135deg, #15c1d7, #0097b8); }

.card h3 {
    font-size: 14px;
    margin-bottom: 6px;
}

.card p {
    color: #6882a5;
    font-size: 12px;
    line-height: 1.6;
}

.card-arrow {
    display: inline-grid;
    place-items: center;
    width: 29px;
    height: 29px;
    margin-top: 9px;
    background: #e4efff;
    color: #174e9c;
    border-radius: 50%;
    font-weight: bold;
}

/* STATISTIK */
.stats {
    color: white;
    background: linear-gradient(105deg, #1b3d78, #2168c7);
    margin-top: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr) 1.4fr;
    gap: 20px;
    align-items: center;
    padding: 24px 0;
}

.stat {
    display: flex;
    align-items: center;
    gap: 11px;
}

.stat-icon {
    color: #9dcaff;
    font-size: 30px;
}

.stat strong {
    display: block;
    font-size: 23px;
    line-height: 1.2;
}

.stat small {
    font-size: 11px;
    color: #e0ecff;
}

.motto {
    text-align: center;
    font-size: 14px;
    font-style: italic;
    font-weight: bold;
    line-height: 1.4;
}

.call-to-action {
    text-align: center;
    padding: 45px 0;
}

.call-to-action h2 {
    font-size: 27px;
    margin-bottom: 8px;
}

.call-to-action p {
    color: var(--abu);
    margin-bottom: 20px;
}

footer {
    padding: 23px 6%;
    background: #102b55;
    color: #dce8f8;
    text-align: center;
    font-size: 12px;
}

footer p + p {
    margin-top: 4px;
    color: #a9bfdd;
}

/* RESPONSIVE */
@media (max-width: 1100px) {
    .nav-links { gap: 15px; }
    .cards { grid-template-columns: repeat(3, 1fr); }
    .motto { font-size: 12px; }
}

@media (max-width: 800px) {
    .navbar {
        flex-wrap: wrap;
        padding: 12px 5%;
    }

    .nav-links {
        order: 3;
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
        gap: 17px;
    }

    .nav-links a { font-size: 12px; }

    .hero { padding: 42px 0; }

    .hero-grid { grid-template-columns: 1fr; }

    .dashboard-preview {
        max-width: 650px;
        margin: auto;
        width: 100%;
    }

    .stats-grid { grid-template-columns: repeat(2, 1fr); }

    .cards { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 500px) {
    .container { width: 90%; }

    .brand h1 { font-size: 21px; }
    .brand p { font-size: 9px; }
    .logo { font-size: 31px; }

    .hero h2 { font-size: 37px; }
    .hero-description { font-size: 14px; }

    .hero-buttons .btn { width: 100%; }
    .trust { gap: 10px; font-size: 11px; }

    .preview-screen { grid-template-columns: 73px 1fr; }
    .preview-sidebar { font-size: 7px; padding: 9px 5px; }
    .preview-content { padding: 8px; }
    .metric-grid { gap: 3px; }
    .metric { padding: 6px 4px; }
    .metric small { font-size: 6px; }
    .metric strong { font-size: 15px; }
    .preview-panels { grid-template-columns: 1fr; }
    .preview-panel:nth-child(2) { display: none; }

    .cards { grid-template-columns: 1fr; }
    .section-heading h2 { font-size: 24px; }
    .stats-grid { gap: 15px; }
    .stat strong { font-size: 19px; }
}
</style>
</head>

<body>

<header class="navbar">
    <a href="index.php" class="brand">
        <span class="logo">🎓</span>
        <span>
            <h1>SIMDOSEN</h1>
            <p>Sistem Informasi Manajemen Dosen</p>
        </span>
    </a>

    <nav class="nav-links">
        <a class="active" href="index.php">Beranda</a>
        <a href="#fitur">Fitur</a>
        <a href="#panduan">Panduan</a>
        <a href="dokumentasi_api.php">Dokumentasi API</a>
        <a href="login.php">Login</a>
    </nav>

    <a class="btn btn-primary" href="<?= $linkLogin ?>">
        ♙ <?= $sudahLogin ? 'Dashboard' : 'Login' ?>
    </a>
</header>

<main>

<section class="hero">
    <div class="container hero-grid">

        <div>
            <div class="pill">★ &nbsp; Selamat Datang di SIMDOSEN</div>

            <h2>
                Kelola Data Dosen<br>
                dengan <span>Lebih Mudah</span>
            </h2>

            <p class="hero-description">
                SIMDOSEN adalah sistem informasi manajemen dosen yang dirancang
                untuk membantu perguruan tinggi dalam mengelola data dosen,
                aktivitas akademik, dan administrasi dengan cepat, aman, dan efisien.
            </p>

            <div class="hero-buttons">
                <a class="btn btn-primary" href="<?= $linkLogin ?>">
                    ▣ &nbsp; Mulai Sekarang &nbsp; →
                </a>

                <a class="btn btn-outline" href="dokumentasi_api.php">
                    ▤ &nbsp; Lihat Dokumentasi API
                </a>
            </div>

            <div class="trust">
                <span><b class="check">✓</b> Aman &amp; Terpercaya</span>
                <span><b class="check">✓</b> Mudah Digunakan</span>
                <span><b class="check">✓</b> Berbasis Web</span>
            </div>
        </div>

        <div class="dashboard-preview">
            <div class="preview-screen">

                <aside class="preview-sidebar">
                    <div class="preview-logo">🎓 SIMDOSEN</div>
                    <div class="menu-item active">▦ Dashboard</div>
                    <div class="menu-item">♙ Dosen</div>
                    <div class="menu-item">♧ Pengguna</div>
                    <div class="menu-item">⚿ API Tokens</div>
                    <div class="menu-item">▤ Laporan</div>
                    <div class="menu-item">⚙ Pengaturan</div>
                </aside>

                <div class="preview-content">
                    <div class="preview-heading">
                        <b>Halo, Administrator</b><br>
                        <small>Selamat datang di sistem informasi manajemen dosen.</small>
                    </div>

                    <div class="metric-grid">
                        <div class="metric">
                            <small>Total Dosen</small>
                            <strong>24</strong>
                        </div>
                        <div class="metric">
                            <small>Total Pengguna</small>
                            <strong>3</strong>
                        </div>
                        <div class="metric">
                            <small>API Tokens</small>
                            <strong>2</strong>
                        </div>
                        <div class="metric">
                            <small>Aktivitas Hari Ini</small>
                            <strong>12</strong>
                        </div>
                    </div>

                    <div class="preview-panels">
                        <div class="preview-panel">
                            <strong>Statistik Dosen</strong>
                            <div class="chart-bars">
                                <span style="height:30%"></span>
                                <span style="height:48%"></span>
                                <span style="height:38%"></span>
                                <span style="height:67%"></span>
                                <span style="height:53%"></span>
                                <span style="height:88%"></span>
                                <span style="height:70%"></span>
                            </div>
                        </div>

                        <div class="preview-panel">
                            <strong>Aktivitas Terbaru</strong>
                            <div class="activity">● Login administrator</div>
                            <div class="activity">● Tambah data dosen</div>
                            <div class="activity">● Update data pengguna</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

<section class="features" id="fitur">
    <div class="container">

        <div class="section-heading">
            <span class="section-label">Fitur Unggulan</span>
            <h2>Apa yang Membuat SIMDOSEN Istimewa?</h2>
            <p>Berbagai fitur lengkap untuk mendukung pengelolaan data dosen secara profesional dan efisien.</p>
        </div>

        <div class="cards">

            <article class="card">
                <div class="card-icon blue">♧</div>
                <h3>Manajemen Dosen</h3>
                <p>Kelola data dosen dengan lengkap meliputi profil, pendidikan, jabatan, dan riwayat mengajar.</p>
                <a class="card-arrow" href="<?= $sudahLogin ? 'dosen.php' : 'login.php' ?>">→</a>
            </article>

            <article class="card">
                <div class="card-icon green">♙</div>
                <h3>Manajemen Pengguna</h3>
                <p>Kelola akun pengguna dengan sistem role dan hak akses yang terstruktur.</p>
                <a class="card-arrow" href="<?= $sudahLogin ? 'pengguna.php' : 'login.php' ?>">→</a>
            </article>

            <article class="card">
                <div class="card-icon purple">⚿</div>
                <h3>API &amp; Integrasi</h3>
                <p>Akses data melalui API dengan autentikasi token yang aman dan terdokumentasi.</p>
                <a class="card-arrow" href="dokumentasi_api.php">→</a>
            </article>

            <article class="card">
                <div class="card-icon orange">▥</div>
                <h3>Laporan &amp; Statistik</h3>
                <p>Dapatkan laporan dan statistik dosen secara cepat dan terstruktur.</p>
                <a class="card-arrow" href="<?= $sudahLogin ? 'laporan.php' : 'login.php' ?>">→</a>
            </article>

            <article class="card">
                <div class="card-icon cyan">♢</div>
                <h3>Keamanan Data</h3>
                <p>Dilengkapi sistem keamanan untuk membantu melindungi data dan akses pengguna.</p>
                <a class="card-arrow" href="#panduan">→</a>
            </article>

        </div>
    </div>
</section>

<section class="stats" id="panduan">
    <div class="container stats-grid">

        <div class="stat">
            <span class="stat-icon">♙</span>
            <span><strong>24+</strong><small>Data Dosen</small></span>
        </div>

        <div class="stat">
            <span class="stat-icon">♧</span>
            <span><strong>3+</strong><small>Pengguna Aktif</small></span>
        </div>

        <div class="stat">
            <span class="stat-icon">⚿</span>
            <span><strong>2+</strong><small>API Token</small></span>
        </div>

        <div class="stat">
            <span class="stat-icon">♢</span>
            <span><strong>99.9%</strong><small>Keamanan Data</small></span>
        </div>

        <div class="motto">
            Bersama SIMDOSEN,<br>
            Menuju Pengelolaan Dosen<br>
            yang Lebih Baik!
        </div>

    </div>
</section>

<section class="call-to-action">
    <div class="container">
        <h2>Mulai Menggunakan SIMDOSEN</h2>
        <p>Masuk untuk mengakses fasilitas sesuai dengan hak akses akun Anda.</p>
        <a class="btn btn-primary" href="<?= $linkLogin ?>">
            <?= $sudahLogin ? 'Buka Dashboard' : 'Login ke SIMDOSEN' ?> →
        </a>
    </div>
</section>

</main>

<footer>
    <p><strong>SIMDOSEN</strong> — Universitas Muhammadiyah Bengkulu</p>
    <p>Sistem Informasi Manajemen Dosen | Web Tradisional dan Web Service API</p>
    <p>&copy; <?= date('Y') ?> SIMDOSEN. Semua hak dilindungi.</p>
</footer>

</body>
</html>
