<?php
require_once __DIR__ . '/db.php';
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#020811">
<title>Mr Rashidi — Movies & Series</title>
<link rel="icon" href="assets/logo.svg" type="image/svg+xml">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div id="toast" class="toast"></div>
<div id="appLoader" class="app-loader"><div class="spinner"></div><span>در حال بارگذاری...</span></div>

<header class="topbar">
  <a class="brand" href="#home" aria-label="Mr Rashidi">
    <img src="assets/logo.svg" alt="Mr Rashidi Movies & Series">
  </a>
  <nav class="topnav" aria-label="منوی اصلی">
    <a class="active" href="#home">خانه</a><a href="#movies">فیلم‌ها</a><a href="#series">سریال‌ها</a><a href="#animation">انیمیشن</a><a href="#genres">ژانرها</a>
  </nav>
  <div class="top-actions">
    <div class="searchbox"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg><input id="searchInput" type="search" placeholder="جستجوی فیلم، سریال یا انیمیشن..." autocomplete="off"></div>
    <button class="icon-btn notif" title="اعلان‌ها"><svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M10 21h4"></path></svg><i>3</i></button>
    <button id="accountBtn" class="account-btn"><span id="avatar">MR</span><span id="accountText">ورود</span></button>
  </div>
</header>

<aside class="sidebar">
  <div class="sidebar-inner">
    <div class="side-title">منو</div>
    <a class="side-link active" href="#home" data-section="home"><span class="ico"><svg viewBox="0 0 24 24"><path d="m3 11 9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"></path></svg></span><span>خانه</span></a>
    <a class="side-link" href="#movies" data-section="movies"><span class="ico"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M3 9h18M8 4v5M16 4v5"></path></svg></span><span>فیلم‌ها</span></a>
    <a class="side-link" href="#series" data-section="series"><span class="ico"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M8 8h8M8 12h8M8 16h5"></path></svg></span><span>سریال‌ها</span></a>
    <a class="side-link" href="#animation" data-section="animation"><span class="ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"></circle><path d="M8 14c2-3 6-3 8 0M9 9h.01M15 9h.01"></path></svg></span><span>انیمیشن</span></a>
    <a class="side-link" href="#history" data-section="history"><span class="ico"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"></path><path d="M3 4v5h5M12 7v5l3 2"></path></svg></span><span>تاریخچه تماشا</span></a>
    <a class="side-link" href="#favorites" data-section="favorites"><span class="ico"><svg viewBox="0 0 24 24"><path d="M20.8 8.8c0 5-8.8 10.2-8.8 10.2S3.2 13.8 3.2 8.8A4.8 4.8 0 0 1 12 6.4a4.8 4.8 0 0 1 8.8 2.4z"></path></svg></span><span>لیست علاقه‌مندی‌ها</span></a>
    <a class="side-link" href="#plans" data-section="plans"><span class="ico"><svg viewBox="0 0 24 24"><path d="M12 3l2 4 4.4.6-3.2 3.1.8 4.3L12 13l-4 2 .8-4.3-3.2-3.1L10 7z"></path><path d="M5 20h14"></path></svg></span><span>اشتراک ویژه</span></a>
    <div class="side-divider"></div>
    <a class="side-link" href="#genres" data-section="genres"><span class="ico"><svg viewBox="0 0 24 24"><path d="M4 5h16M4 12h16M4 19h16"></path></svg></span><span>ژانرها</span></a>
    <a class="side-link" href="#about" data-section="about"><span class="ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 10v6M12 7h.01"></path></svg></span><span>درباره ما</span></a>
    <div class="premium-card">
      <div class="premium-icon">♛</div><strong>اشتراک ویژه</strong><p>دسترسی به امکانات ویژه و تجربه بهتر تماشا.</p><button data-open-plan>ارتقا به اشتراک ویژه</button>
    </div>
  </div>
</aside>

<main class="main" id="home">
  <section id="hero" class="hero"><div class="hero-bg"></div><div class="hero-content"><span class="eyebrow">پیشنهاد ویژه امروز</span><h1 id="heroTitle">در حال بارگذاری...</h1><div class="hero-meta" id="heroMeta"></div><p id="heroOverview"></p><div class="hero-buttons"><button class="primary" id="heroPlay">▶ تماشای رایگان</button><button class="ghost" id="heroInfo">ⓘ اطلاعات بیشتر</button></div></div><div class="hero-dots" id="heroDots"></div><button class="hero-arrow left" id="heroNext">‹</button><button class="hero-arrow right" id="heroPrev">›</button></section>

  <div class="content-wrap">
    <section class="section hidden" id="searchResults"><div class="section-head"><div><span class="tiny">نتایج جستجو</span><h2 id="searchTitle">جستجو</h2></div><button class="more" id="clearSearch">بستن</button></div><div class="row" id="searchRow"></div></section>
    <section class="section" id="special"><div class="section-head"><div><span class="tiny">منتخب امروز</span><h2>پیشنهاد ویژه <b>♛</b></h2></div><button class="more" data-load="special">مشاهده همه</button></div><div class="row" id="specialRow"></div></section>
    <section class="section" id="series"><div class="section-head"><div><span class="tiny">محبوب‌ترین‌ها</span><h2>محبوب‌ترین سریال‌ها <b>◆</b></h2></div><button class="more" data-load="series">مشاهده همه</button></div><div class="row" id="seriesRow"></div></section>
    <section class="section" id="animation"><div class="section-head"><div><span class="tiny">برای همه سنین</span><h2>انیمیشن‌های برتر <b>◆</b></h2></div><button class="more" data-load="animation">مشاهده همه</button></div><div class="row" id="animationRow"></div></section>
    <section class="section" id="movies"><div class="section-head"><div><span class="tiny">تازه‌ترین‌ها</span><h2>جدیدترین فیلم‌ها</h2></div><button class="more" data-load="movies">مشاهده همه</button></div><div class="row" id="moviesRow"></div></section>
    <section class="section" id="genres"><div class="section-head"><div><span class="tiny">دسته‌بندی</span><h2>ژانرهای محبوب</h2></div></div><div class="genre-grid" id="genreGrid"></div></section>
    <section class="section" id="favorites"><div class="section-head"><div><span class="tiny">ذخیره‌شده‌ها</span><h2>لیست علاقه‌مندی‌ها ♡</h2></div><button class="more" id="refreshFavorites">بروزرسانی</button></div><div class="row" id="favoritesRow"><div class="empty">برای دیدن لیست علاقه‌مندی‌ها وارد حساب شوید.</div></div></section>
    <section class="section" id="history"><div class="section-head"><div><span class="tiny">ادامه تماشا</span><h2>تاریخچه تماشا</h2></div></div><div class="row" id="historyRow"><div class="empty">هنوز چیزی در تاریخچه شما ثبت نشده است.</div></div></section>
    <section class="about" id="about"><h2>Mr Rashidi</h2><p>پلتفرم نمایشی فیلم و سریال با رابط کاربری فارسی، جستجوی زنده و اطلاعات به‌روز آثار. پخش و خرید اشتراک در این نسخه صرفاً نمایشی است.</p><div class="tmdb-credit">این محصول از TMDB API استفاده می‌کند و توسط TMDB تأیید یا گواهی نشده است.</div></section>
  </div>
</main>

<nav class="mobile-nav"><a class="active" href="#home"><span>⌂</span>خانه</a><a href="#movies"><span>▣</span>فیلم‌ها</a><a href="#series"><span>☷</span>سریال‌ها</a><a href="#favorites"><span>♡</span>علاقه‌مندی</a><button id="mobileAccount"><span>◉</span>حساب</button></nav>

<div class="modal" id="authModal"><div class="modal-card auth-card"><button class="close" data-close>×</button><div class="auth-tabs"><button class="active" data-auth-tab="login">ورود</button><button data-auth-tab="register">ثبت‌نام</button></div><div id="loginForm" class="auth-form"><h2>ورود به حساب</h2><p>برای ذخیره علاقه‌مندی‌ها و تاریخچه وارد شوید.</p><label>ایمیل<input id="loginEmail" type="email" autocomplete="email"></label><label>رمز عبور<input id="loginPassword" type="password" autocomplete="current-password"></label><button class="primary full" id="loginSubmit">ورود</button></div><div id="registerForm" class="auth-form hidden"><h2>ساخت حساب</h2><p>حساب خود را بسازید و اطلاعاتتان در سایت ذخیره می‌شود.</p><label>نام<input id="registerName" type="text" autocomplete="name"></label><label>ایمیل<input id="registerEmail" type="email" autocomplete="email"></label><label>رمز عبور<input id="registerPassword" type="password" autocomplete="new-password"></label><button class="primary full" id="registerSubmit">ثبت‌نام</button></div></div></div>

<div class="modal" id="detailsModal"><div class="modal-card details-card"><button class="close" data-close>×</button><div class="detail-hero" id="detailHero"></div><div class="detail-body"><div><h2 id="detailTitle"></h2><div id="detailMeta" class="hero-meta"></div><p id="detailOverview"></p><div class="detail-actions"><button class="primary" id="detailPlay">▶ پخش نمایشی</button><button class="ghost" id="detailFavorite">♡ علاقه‌مندی</button></div></div></div></div></div>

<div class="modal" id="playerModal"><div class="modal-card player-card"><button class="close" data-close>×</button><div class="fake-player"><div class="play-ring">▶</div><strong id="playerTitle">پخش نمایشی</strong><span>ویدیو در این نسخه واقعی نیست.</span><div class="fake-progress"><i></i></div><div class="player-controls">▶　 00:00 / 00:00　 🔊　 ⛶</div></div></div></div>

<div class="modal" id="planModal"><div class="modal-card plan-card"><button class="close" data-close>×</button><div class="plan-icon">♛</div><h2>اشتراک ویژه</h2><p>این بخش فعلاً نمایشی است و هیچ پرداخت واقعی انجام نمی‌شود.</p><div class="plans"><button data-demo-plan="ماهانه"><strong>ماهانه</strong><b>۹۹٬۰۰۰ تومان</b><span>۳۰ روز دسترسی نمایشی</span></button><button data-demo-plan="سالانه"><strong>سالانه</strong><b>۷۹۹٬۰۰۰ تومان</b><span>۳۶۵ روز دسترسی نمایشی</span></button></div></div></div>

<script>window.MR={csrf:<?=json_encode($csrf,JSON_UNESCAPED_UNICODE)?>};</script>
<script src="assets/app.js" defer></script>
</body></html>
