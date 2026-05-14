<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Kantin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/pembeli.css">
</head>
<body>

  <!-- NAVBAR -->
  <nav>
    <a class="nav-brand" href="#">
      <div class="nav-logo">🍽️</div>
      <span class="nav-title">KantinKu</span>
    </a>
    <ul class="nav-links">
      <li><a href="#">Beranda</a></li>
      <li><a href="#">Menu Hari Ini</a></li>
      <li><a href="#">Promo</a></li>
      <li><a href="#">Tentang</a></li>
      <li><a href="#" class="nav-cta">Pesan Sekarang</a></li>
    </ul>
    <button class="hamburger" id="menuBtn" aria-label="Buka menu">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <!-- MOBILE MENU -->
  <div class="mobile-menu" id="mobileMenu">
    <button class="mobile-menu-close" id="menuClose">✕</button>
    <a href="#">🏠 Beranda</a>
    <a href="#">🍛 Menu Hari Ini</a>
    <a href="#">🎁 Promo</a>
    <a href="#">ℹ️ Tentang</a>
    <a href="#" style="color: var(--accent); font-weight: 600; margin-top: 1rem;">🛒 Pesan Sekarang</a>
  </div>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-badge">✨ Selamat Datang di KantinKu</div>
    <h1>Makan Enak,<br><span>Harga Terjangkau</span></h1>
    <p>Nikmati berbagai pilihan kantin dengan menu lezat setiap hari. Pilih favoritmu dan pesan langsung dari sini.</p>

  </section>



  <!-- MAIN -->
  <main class="main" id="kantin">

    <!-- KANTIN LIST -->
    <div class="section-header">
      <div>
        <h2 class="section-title">Pilihan Kantin</h2>
        <p class="section-sub">Temukan kantin favorit Anda di sini</p>
      </div>
    </div>

    <!-- KANTIN CARDS -->
    <div class="kantin-grid" id="kantinGrid">

      <!-- 1 -->
      <div class="kantin-card">
        <div class="card-thumb">
          <div class="card-thumb-bg thumb-nasi">🍛</div>
        </div>
        <div class="card-body">
          <h3 class="card-name">Kantin Nasi Padang Bu Sari</h3>
          <p class="card-desc">Rendang, gulai, ayam pop, dan aneka masakan Padang autentik dengan bumbu khas Minang.</p>
        </div>
        <div class="card-footer">
          <button class="btn-sm">Lihat</button>
        </div>
      </div>

      <!-- 2 -->
      <div class="kantin-card">
        <div class="card-thumb">
          <div class="card-thumb-bg thumb-mie">🍜</div>
        </div>
        <div class="card-body">
          <h3 class="card-name">Bakso &amp; Mie Ayam Pak Darto</h3>
          <p class="card-desc">Bakso kenyal dengan kuah kaldu sapi pekat, tersedia mie ayam, bakso urat, dan bakso telur.</p>
        </div>
        <div class="card-footer">
          <button class="btn-sm">Lihat</button>
        </div>
      </div>

      <!-- 3 -->
      <div class="kantin-card">
        <div class="card-thumb">
          <div class="card-thumb-bg thumb-soto">🥣</div>
        </div>
        <div class="card-body">
          <h3 class="card-name">Soto Ayam &amp; Sup Ibu Dewi</h3>
          <p class="card-desc">Soto ayam kuning, soto betawi, sup iga sapi, dan tongseng gurih dengan rempah pilihan.</p>
        </div>
        <div class="card-footer">
          <button class="btn-sm">Lihat</button>
        </div>
      </div>

      <!-- 4 -->
      <div class="kantin-card">
        <div class="card-thumb">
          <div class="card-thumb-bg thumb-gado">🥗</div>
        </div>
        <div class="card-body">
          <h3 class="card-name">Gado-Gado &amp; Pecel Mbak Tini</h3>
          <p class="card-desc">Gado-gado segar, pecel daun, ketoprak, dan siomay dengan bumbu kacang homemade yang khas.</p>
        </div>
        <div class="card-footer">
          <button class="btn-sm">Lihat</button>
        </div>
      </div>

      <!-- 5 -->
      <div class="kantin-card">
        <div class="card-thumb">
          <div class="card-thumb-bg thumb-ayam">🍗</div>
        </div>
        <div class="card-body">
          <h3 class="card-name">Ayam Geprek &amp; Lalapan Bang Joni</h3>
          <p class="card-desc">Ayam geprek sambal bawang, ayam goreng kremes, nasi uduk, dan menu lalapan segar.</p>
        </div>
        <div class="card-footer">
          <button class="btn-sm">Lihat</button>
        </div>
      </div>

      <!-- 6 -->
      <div class="kantin-card">
        <div class="card-thumb">
          <div class="card-thumb-bg thumb-seaf">🦐</div>
        </div>
        <div class="card-body">
          <h3 class="card-name">Seafood &amp; Ikan Bakar Pak Herman</h3>
          <p class="card-desc">Ikan bakar, cumi goreng tepung, udang saus tiram, dan kepiting saos padang spesial.</p>
        </div>
        <div class="card-footer">
          <button class="btn-sm">Lihat</button>
        </div>
      </div>

      <!-- 7 -->
      <div class="kantin-card">
        <div class="card-thumb">
          <div class="card-thumb-bg thumb-veg">🥦</div>
        </div>
        <div class="card-body">
          <h3 class="card-name">Warung Vegetarian Segar</h3>
          <p class="card-desc">Menu vegetarian sehat: tempe goreng, tumis sayur, tahu crispy, dan minuman jus segar.</p>
        </div>
        <div class="card-footer">
          <button class="btn-sm">Lihat</button>
        </div>
      </div>

      <!-- 8 -->
      <div class="kantin-card">
        <div class="card-thumb">
          <div class="card-thumb-bg thumb-mie2">🍝</div>
        </div>
        <div class="card-body">
          <h3 class="card-name">Mie Goreng &amp; Kwetiau Mas Rudi</h3>
          <p class="card-desc">Mie goreng spesial, kwetiau siram, bihun goreng, dan nasi goreng dengan telur ceplok.</p>
        </div>
        <div class="card-footer">
          <button class="btn-sm">Lihat</button>
        </div>
      </div>

      <!-- 9 -->
      <div class="kantin-card">
        <div class="card-thumb">
          <div class="card-thumb-bg thumb-noodle">🍲</div>
        </div>
        <div class="card-body">
          <h3 class="card-name">Warung Sunda Kang Asep</h3>
          <p class="card-desc">Nasi liwet, karedok, sayur asem, lalapan segar, dan sambal terasi khas Sunda.</p>
        </div>
        <div class="card-footer">
          <button class="btn-sm">Lihat</button>
        </div>
      </div>

      <!-- 10 -->
      <div class="kantin-card">
        <div class="card-thumb">
          <div class="card-thumb-bg thumb-drink">🧋</div>
        </div>
        <div class="card-body">
          <h3 class="card-name">Kantin Minuman &amp; Jajanan</h3>
          <p class="card-desc">Es teh, jus buah segar, bubble tea, gorengan, dan aneka jajanan pasar pilihan.</p>
        </div>
        <div class="card-footer">
          <button class="btn-sm">Lihat</button>
        </div>
      </div>

    </div>
  </main>

  <!-- FOOTER -->
  <footer>
    <p>© 2025 <span>KantinKu</span> — Dibuat dengan ❤️ untuk kenyamanan Anda</p>
  </footer>

  <script>
    // Mobile menu
    const menuBtn  = document.getElementById('menuBtn');
    const menuClose = document.getElementById('menuClose');
    const mobileMenu = document.getElementById('mobileMenu');

    menuBtn.addEventListener('click', () => mobileMenu.classList.add('open'));
    menuClose.addEventListener('click', () => mobileMenu.classList.remove('open'));




  </script>
</body>
</html>