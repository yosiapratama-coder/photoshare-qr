<?php
require __DIR__ . '/config.php';
$events = db()->query('SELECT e.*, (SELECT COUNT(*) FROM photos p WHERE p.event_id=e.id) AS photo_count FROM events e ORDER BY e.created_at DESC')->fetchAll();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_event') {
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    if ($title === '' || mb_strlen($title) > 150) {
        $error = 'Nama acara wajib diisi (maksimal 150 karakter).';
    } else {
        $token = bin2hex(random_bytes(16));
        $stmt = db()->prepare('INSERT INTO events(title, description, access_token) VALUES(?,?,?)');
        $stmt->execute([$title, $description, $token]);
        redirect('event.php?token=' . $token . '&admin=1');
    }
}
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>PhotoShare QR — Berbagi Foto</title><link rel="stylesheet" href="style.css">
</head><body>
<header class="topbar"><a class="brand" href="index.php"><span class="brand-icon">▣</span> PhotoShare<span>QR</span></a><span class="top-note">TRANSFER FOTO • SATU QR</span></header>
<main class="container">
<section class="hero">
<div class="hero-copy"><div class="eyebrow">SIMPLE • CEPAT • BERSAMA</div><h1>Semua momen,<br><span>satu galeri.</span></h1>
<p>Unggah foto dari kamera atau handphone, bagikan QR Code, dan biarkan peserta mengakses galeri acara dari perangkat mereka.</p>
<div class="hero-pills"><span>Canon</span><span>Nikon</span><span>Sony</span><span>Fujifilm</span><span>Handphone</span></div>
<a class="button primary" href="#buat-acara">＋ Buat galeri acara</a></div>
<div class="hero-art"><div class="camera-emoji">📸</div><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div><div class="floating-card card-a">↗ Foto terunggah</div><div class="floating-card card-b">▦ Scan QR</div><div class="art-caption">YOUR MOMENTS, SHARED.</div></div>
</section>
<section class="section" id="buat-acara"><div class="section-head"><div><div class="eyebrow">MULAI DI SINI</div><h2>Buat galeri baru</h2><p class="muted">Setiap acara memiliki QR Code dan galeri sendiri.</p></div></div>
<?php if ($error): ?><div class="alert"><?=e($error)?></div><?php endif; ?>
<form method="post" class="create-form"><input type="hidden" name="action" value="create_event">
<label>Nama acara<input required maxlength="150" name="title" placeholder="Contoh: Natal HKPI 2026"></label>
<label>Deskripsi <span class="muted">(opsional)</span><input name="description" placeholder="Tanggal, lokasi, atau keterangan acara"></label>
<button class="button primary" type="submit">Buat galeri & QR <span>→</span></button></form>
</section>
<section class="section"><div class="section-head"><div><div class="eyebrow">GALERI TERSEDIA</div><h2>Acara terbaru</h2></div><span class="count"><?=count($events)?> galeri</span></div>
<?php if (!$events): ?><div class="empty"><div class="empty-icon">▧</div><h3>Belum ada galeri</h3><p>Buat galeri pertama untuk mulai mengumpulkan foto.</p></div>
<?php else: ?><div class="event-grid"><?php foreach ($events as $event): ?><a class="event-card" href="event.php?token=<?=e($event['access_token'])?>"><div class="event-thumb">▦<span><?= (int)$event['photo_count'] ?> foto</span></div><div class="event-info"><h3><?=e($event['title'])?></h3><p><?=e($event['description'] ?? '')?></p><span class="text-link">Buka galeri →</span></div></a><?php endforeach; ?></div><?php endif; ?>
</section>
<section class="how"><div><div class="eyebrow">CARA KERJA</div><h2>Dari kamera ke galeri bersama.</h2></div><div class="how-step"><b>01</b><div><strong>Buat acara</strong><p>Website menyiapkan tautan galeri.</p></div></div><div class="how-step"><b>02</b><div><strong>Unggah foto</strong><p>Pilih foto dari HP atau komputer.</p></div></div><div class="how-step"><b>03</b><div><strong>Bagikan QR</strong><p>Peserta scan, lihat, dan unduh foto.</p></div></div></section>
<footer>PhotoShare QR <span>•</span> Bagikan momen dengan mudah <span class="footer-right">© <?=date('Y')?></span></footer>
</main></body></html>
