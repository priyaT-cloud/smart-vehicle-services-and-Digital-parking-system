<?php
// includes/header.php
// Must be included AFTER config.php is loaded
$currentUser = getCurrentUser();
$B = baseUrl();   // short alias used throughout this file
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? clean($pageTitle) . ' — ' . SITE_NAME : SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">
<style>
/* ── Variables ───────────────────────────────────────────── */
:root{
  --bg:#0a0e1a; --surface:#111827; --surface2:#1a2235;
  --accent:#00d4aa; --accent2:#ff6b35; --accent3:#7c5cfc;
  --text:#f0f4ff; --muted:#8899aa;
  --border:rgba(255,255,255,0.08);
  --card:rgba(255,255,255,0.04);
  --glow:0 0 40px rgba(0,212,170,0.18);
  --radius:14px; --radius-lg:20px;
}
/* ── Reset ───────────────────────────────────────────────── */
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);
     min-height:100vh;overflow-x:hidden;}
h1,h2,h3,h4,h5{font-family:'Syne',sans-serif;}
a{color:inherit;text-decoration:none;}
img{display:block;}
/* ── Background ─────────────────────────────────────────── */
.bg-grid{position:fixed;inset:0;z-index:0;pointer-events:none;
  background-image:linear-gradient(rgba(0,212,170,.03) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(0,212,170,.03) 1px,transparent 1px);
  background-size:40px 40px;}
.bg-orb{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0;}
.orb1{width:420px;height:420px;background:rgba(0,212,170,.08);top:-120px;right:-100px;}
.orb2{width:320px;height:320px;background:rgba(124,92,252,.07);bottom:80px;left:-90px;}
/* ── Navbar ─────────────────────────────────────────────── */
.navbar{
  position:fixed;top:0;left:0;right:0;z-index:200;
  display:flex;align-items:center;justify-content:space-between;
  padding:.9rem 2rem;
  background:rgba(10,14,26,.92);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
}
.logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.35rem;color:var(--accent);}
.logo span{color:var(--text);}
.nav-right{display:flex;align-items:center;gap:.9rem;flex-wrap:wrap;}
.avatar{width:34px;height:34px;border-radius:50%;background:var(--accent);
        display:flex;align-items:center;justify-content:center;
        font-weight:700;font-size:.82rem;color:#000;flex-shrink:0;}
.uname{font-size:.88rem;color:var(--muted);max-width:160px;
       white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.nav-btn{background:none;border:1px solid var(--border);color:var(--muted);
         padding:.38rem .95rem;border-radius:8px;font-family:'DM Sans',sans-serif;
         font-size:.86rem;transition:all .2s;display:inline-block;}
.nav-btn:hover{border-color:var(--accent);color:var(--accent);}
/* ── Layout ─────────────────────────────────────────────── */
.main-wrap{position:relative;z-index:1;}
.page{min-height:100vh;display:flex;flex-direction:column;
      align-items:center;justify-content:center;padding:5.5rem 1.5rem 3rem;}
.page.top{justify-content:flex-start;padding-top:5.5rem;}
.container{width:100%;max-width:1100px;margin:0 auto;}
/* ── Card ───────────────────────────────────────────────── */
.card{background:var(--surface);border:1px solid var(--border);
      border-radius:var(--radius-lg);padding:2rem;}
/* ── Form ───────────────────────────────────────────────── */
.fg{margin-bottom:1.15rem;}
.fg label{display:block;font-size:.8rem;color:var(--muted);
          margin-bottom:.45rem;font-weight:500;letter-spacing:.04em;}
.fg input,.fg select,.fg textarea{
  width:100%;background:var(--bg);border:1px solid var(--border);
  border-radius:10px;padding:.72rem 1rem;color:var(--text);
  font-family:'DM Sans',sans-serif;font-size:.94rem;transition:border-color .2s;}
.fg input:focus,.fg select:focus,.fg textarea:focus{
  outline:none;border-color:var(--accent);}
.fg select option{background:var(--surface);}
.fg textarea{resize:vertical;min-height:80px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
@media(max-width:580px){.form-row{grid-template-columns:1fr;}}
/* ── Buttons ────────────────────────────────────────────── */
.btn{display:inline-block;padding:.78rem 1.7rem;border:none;
     border-radius:10px;cursor:pointer;font-family:'Syne',sans-serif;
     font-weight:700;font-size:.93rem;transition:all .25s;text-align:center;}
.btn-primary{background:var(--accent);color:#000;}
.btn-primary:hover{background:#00c49a;transform:translateY(-1px);box-shadow:var(--glow);}
.btn-outline{background:transparent;border:1px solid var(--border);color:var(--text);}
.btn-outline:hover{border-color:var(--accent);color:var(--accent);}
.btn-danger{background:rgba(255,70,70,.12);color:#ff5555;
            border:1px solid rgba(255,70,70,.25);border-radius:8px;
            padding:.4rem .85rem;font-size:.82rem;cursor:pointer;transition:all .2s;}
.btn-danger:hover{background:rgba(255,70,70,.25);}
.btn-edit{background:rgba(0,212,170,.1);color:var(--accent);
          border:1px solid rgba(0,212,170,.25);border-radius:8px;
          padding:.4rem .85rem;font-size:.82rem;cursor:pointer;transition:all .2s;}
.btn-edit:hover{background:rgba(0,212,170,.2);}
.btn-sm{padding:.42rem .85rem;font-size:.82rem;border-radius:8px;}
.btn-block{width:100%;display:block;}
/* ── Alerts ─────────────────────────────────────────────── */
.alert{padding:.82rem 1.1rem;border-radius:10px;margin-bottom:1.1rem;font-size:.88rem;}
.alert-error{background:rgba(255,70,70,.1);border:1px solid rgba(255,70,70,.25);color:#ff7070;}
.alert-success{background:rgba(0,212,170,.1);border:1px solid rgba(0,212,170,.25);color:var(--accent);}
/* ── Badges ─────────────────────────────────────────────── */
.badge{display:inline-block;padding:.22rem .7rem;border-radius:999px;
       font-size:.72rem;font-weight:600;letter-spacing:.05em;}
.badge-provider{background:rgba(255,107,53,.15);color:var(--accent2);border:1px solid rgba(255,107,53,.2);}
.badge-receiver{background:rgba(0,212,170,.15);color:var(--accent);border:1px solid rgba(0,212,170,.2);}
.badge-parking{background:rgba(124,92,252,.2);color:#a78bfa;}
.badge-washing{background:rgba(0,212,170,.15);color:var(--accent);}
.badge-rental{background:rgba(255,107,53,.15);color:var(--accent2);}
.badge-confirmed{background:rgba(0,212,170,.15);color:var(--accent);}
.badge-completed{background:rgba(110,232,122,.15);color:#6ee87a;}
.badge-cancelled{background:rgba(255,70,70,.1);color:#ff7070;}
/* ── Tabs ───────────────────────────────────────────────── */
.tabs{display:flex;gap:.5rem;margin-bottom:1.8rem;flex-wrap:wrap;}
.tab{padding:.52rem 1.15rem;border-radius:10px;border:1px solid var(--border);
     background:transparent;color:var(--muted);font-family:'DM Sans',sans-serif;
     font-size:.88rem;display:inline-block;transition:all .2s;}
.tab:hover{border-color:var(--accent);color:var(--accent);}
.tab.active{background:rgba(0,212,170,.1);border-color:var(--accent);
            color:var(--accent);font-weight:600;}
/* ── Cards grid ─────────────────────────────────────────── */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.4rem;}
/* ── Listing card ───────────────────────────────────────── */
.lcard{background:var(--surface);border:1px solid var(--border);
       border-radius:var(--radius);overflow:hidden;transition:all .3s;}
.lcard:hover{transform:translateY(-4px);border-color:rgba(255,255,255,.15);
             box-shadow:0 18px 50px rgba(0,0,0,.45);}
.lcard-img{width:100%;height:185px;background:var(--surface2);
           display:flex;align-items:center;justify-content:center;font-size:3.5rem;
           overflow:hidden;}
.lcard-img img{width:100%;height:100%;object-fit:cover;}
.lcard-body{padding:1.15rem;}
.lcard-title{font-family:'Syne',sans-serif;font-weight:700;font-size:.98rem;margin-bottom:.28rem;}
.lcard-meta{color:var(--muted);font-size:.8rem;margin-bottom:.45rem;}
.lcard-extra{font-size:.76rem;color:var(--muted);margin-bottom:.5rem;}
.lcard-price{color:var(--accent);font-weight:700;font-size:.93rem;margin-bottom:.55rem;}
.lcard-rating{display:flex;align-items:center;gap:.35rem;font-size:.82rem;margin-bottom:.9rem;}
.lcard-actions{display:flex;gap:.5rem;}
.lcard-btn{flex:1;padding:.48rem .5rem;border:none;border-radius:8px;
           cursor:pointer;font-family:'DM Sans',sans-serif;
           font-size:.83rem;font-weight:500;transition:all .2s;text-align:center;display:inline-block;}
.lcard-btn-primary{background:var(--accent);color:#000;}
.lcard-btn-primary:hover{background:#00c49a;}
.lcard-btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border);}
.lcard-btn-secondary:hover{border-color:var(--accent);color:var(--accent);}
/* ── Manage card ────────────────────────────────────────── */
.mcard{background:var(--surface);border:1px solid var(--border);
       border-radius:var(--radius);padding:1.1rem;
       display:flex;align-items:center;gap:1rem;margin-bottom:.9rem;flex-wrap:wrap;}
.mcard-thumb{width:68px;height:68px;border-radius:10px;background:var(--surface2);
             flex-shrink:0;display:flex;align-items:center;justify-content:center;
             font-size:1.6rem;overflow:hidden;}
.mcard-thumb img{width:100%;height:100%;object-fit:cover;}
.mcard-info{flex:1;min-width:140px;}
.mcard-title{font-weight:700;font-size:.93rem;margin-bottom:.22rem;}
.mcard-sub{color:var(--muted);font-size:.8rem;}
.mcard-actions{display:flex;gap:.5rem;flex-wrap:wrap;}
/* ── Booking item ───────────────────────────────────────── */
.bitem{background:var(--surface);border:1px solid var(--border);
       border-radius:var(--radius);padding:1.1rem;margin-bottom:.9rem;
       display:flex;justify-content:space-between;align-items:center;
       flex-wrap:wrap;gap:.9rem;}
.bitem-info h4{font-size:.93rem;font-weight:700;margin-bottom:.25rem;}
.bitem-info p{color:var(--muted);font-size:.8rem;line-height:1.5;}
/* ── Stats ──────────────────────────────────────────────── */
.stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));
       gap:1rem;margin-bottom:1.8rem;}
.stat{background:var(--surface);border:1px solid var(--border);
      border-radius:var(--radius);padding:1.1rem;}
.stat-num{font-family:'Syne',sans-serif;font-size:1.75rem;font-weight:800;margin-bottom:.18rem;}
.stat-lbl{color:var(--muted);font-size:.8rem;}
/* ── Upload box ─────────────────────────────────────────── */
.upload-box{border:2px dashed var(--border);border-radius:12px;
            padding:1.8rem;text-align:center;cursor:pointer;transition:all .3s;}
.upload-box:hover{border-color:var(--accent);background:rgba(0,212,170,.03);}
/* ── Filter bar ─────────────────────────────────────────── */
.filter-bar{display:flex;gap:.75rem;margin-bottom:1.4rem;flex-wrap:wrap;}
.filter-bar input,.filter-bar select{
  background:var(--surface);border:1px solid var(--border);border-radius:8px;
  padding:.48rem .88rem;color:var(--text);
  font-family:'DM Sans',sans-serif;font-size:.87rem;}
.filter-bar input:focus,.filter-bar select:focus{outline:none;border-color:var(--accent);}
.filter-bar input{flex:1;min-width:190px;}
/* ── Review item ────────────────────────────────────────── */
.rv-item{border-bottom:1px solid var(--border);padding:.9rem 0;}
.rv-item:last-child{border-bottom:none;}
.rv-user{font-weight:600;font-size:.88rem;margin-bottom:.25rem;}
.rv-text{color:var(--muted);font-size:.83rem;line-height:1.55;}
.rv-date{font-size:.74rem;color:var(--muted);margin-top:.25rem;}
/* ── Empty state ────────────────────────────────────────── */
.empty{text-align:center;padding:3.5rem 1.5rem;color:var(--muted);}
.empty .ei{font-size:2.8rem;margin-bottom:.8rem;}
/* ── Divider ────────────────────────────────────────────── */
.divider{height:1px;background:var(--border);margin:1.4rem 0;}
/* ── Cost box ───────────────────────────────────────────── */
.cost-box{background:rgba(0,212,170,.05);border:1px solid rgba(0,212,170,.2);
          border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.1rem;}
.cost-box .lbl{font-size:.8rem;color:var(--muted);margin-bottom:.3rem;}
.cost-box .val{font-size:1.45rem;font-weight:800;color:var(--accent);}
/* ── Context bar ────────────────────────────────────────── */
.ctx-bar{background:rgba(0,212,170,.06);border:1px solid rgba(0,212,170,.18);
         border-radius:10px;padding:.72rem 1rem;margin-bottom:1.4rem;
         font-size:.83rem;color:var(--accent);}
/* ── Scrollbar ──────────────────────────────────────────── */
::-webkit-scrollbar{width:6px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--surface2);border-radius:3px;}
/* ── Animations ─────────────────────────────────────────── */
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
.fu{animation:fadeUp .45s ease both;}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="bg-orb orb1"></div>
<div class="bg-orb orb2"></div>

<nav class="navbar">
  <a href="<?= $B ?>/index.php" class="logo">Smart<span>Vehi</span></a>
  <div class="nav-right">
    <?php if ($currentUser): ?>
      <div class="avatar"><?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?></div>
      <span class="uname"><?= clean($currentUser['full_name']) ?></span>
      <a href="<?= $B ?>/logout.php" class="nav-btn">Logout</a>
    <?php else: ?>
      <a href="<?= $B ?>/index.php" class="nav-btn">← Home</a>
    <?php endif; ?>
  </div>
</nav>

<div class="main-wrap">
