<?php
require __DIR__ . '/config.php';
$token = preg_replace('/[^a-f0-9]/', '', (string)($_GET['token'] ?? ''));
$stmt = db()->prepare('SELECT * FROM events WHERE access_token=?');
$stmt->execute([$token]); $event = $stmt->fetch();
if (!$event) { http_response_code(404); exit('Galeri tidak ditemukan. Periksa kembali tautan QR.'); }
$admin = isset($_GET['admin']) && $_GET['admin'] === '1';
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
    $files = $_FILES['photos'];
    $count = is_array($files['name']) ? count($files['name']) : 0;
    if ($count > MAX_FILES_PER_REQUEST) $notice = 'Maksimal ' . MAX_FILES_PER_REQUEST . ' foto per unggahan.';
    else {
        $allowed = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp', 'image/gif'=>'gif'];
        $finfo = new finfo(FILEINFO_MIME_TYPE); $success = 0; $errors = [];
        for ($i=0; $i<$count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) { $errors[] = 'Ada file yang gagal diunggah.'; continue; }
            if (($files['size'][$i] ?? 0) > MAX_FILE_BYTES) { $errors[] = basename($files['name'][$i]) . ' melebihi 12 MB.'; continue; }
            $tmp = $files['tmp_name'][$i];
            $mime = $finfo->file($tmp);
            if (!isset($allowed[$mime]) || @getimagesize($tmp) === false) { $errors[] = 'Hanya foto JPG, PNG, WEBP, atau GIF yang diterima.'; continue; }
            $stored = bin2hex(random_bytes(18)) . '.' . $allowed[$mime];
            if (!move_uploaded_file($tmp, __DIR__ . '/uploads/' . $stored)) { $errors[] = 'Gagal menyimpan file.'; continue; }
            $ins = db()->prepare('INSERT INTO photos(event_id, original_name, stored_name, mime_type, file_size) VALUES(?,?,?,?,?)');
            $ins->execute([(int)$event['id'], mb_substr(basename($files['name'][$i]), 0, 255), $stored, $mime, (int)$files['size'][$i]]);
            $success++;
        }
        $notice = $success . ' foto berhasil diunggah.' . ($errors ? ' Catatan: ' . implode(' ', array_slice($errors,0,3)) : '');
    }
}
$photosStmt = db()->prepare('SELECT * FROM photos WHERE event_id=? ORDER BY uploaded_at DESC');
$photosStmt->execute([(int)$event['id']]); $photos = $photosStmt->fetchAll();
$shareUrl = base_url('event.php?token=' . $event['access_token']);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="refresh" content="25"><title><?=e($event['title'])?> — PhotoShare QR</title><link rel="stylesheet" href="style.css"><script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script></head><body>
<header class="topbar"><a class="brand" href="index.php"><span class="brand-icon">▣</span> PhotoShare<span>QR</span></a><a class="back-link" href="index.php">← Semua galeri</a></header>
<main class="container event-container">
<div class="event-heading"><div><div class="eyebrow">GALERI ACARA</div><h1><?=e($event['title'])?></h1><p class="muted"><?=e($event['description'] ?? '')?></p><div class="event-meta"><span>▣ <?=count($photos)?> foto</span><span>↻ Galeri diperbarui otomatis</span></div></div>
<div class="qr-panel"><div id="qrcode"></div><strong>SCAN UNTUK BUKA</strong><small>Bagikan QR ini kepada peserta</small><button type="button" class="button secondary small" onclick="downloadQR()">Unduh QR Code</button><button type="button" class="button ghost small" onclick="copyLink()">Salin tautan</button></div></div>
<?php if ($notice): ?><div class="alert"><?=e($notice)?></div><?php endif; ?>
<section class="upload-panel"><div class="upload-icon">↑</div><div class="upload-copy"><h2>Tambahkan foto</h2><p>Pilih foto dari HP, kamera yang terhubung, atau komputer. JPG, PNG, WEBP, GIF — maksimal 12 MB per foto.</p></div>
<form method="post" enctype="multipart/form-data" class="upload-form"><label class="file-label">Pilih foto<input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required></label><button class="button primary" type="submit">Unggah foto ↑</button></form></section>
<div class="gallery-title"><div><div class="eyebrow">MOMEN YANG DIBAGIKAN</div><h2>Galeri foto</h2></div><span class="count"><?=count($photos)?> foto</span></div>
<?php if (!$photos): ?><div class="empty"><div class="empty-icon">▧</div><h3>Foto pertama dimulai dari sini</h3><p>Unggah foto untuk menampilkannya di galeri.</p></div>
<?php else: ?><div class="photo-grid"><?php foreach ($photos as $photo): ?><article class="photo-card"><a href="<?=e('uploads/'.$photo['stored_name'])?>" target="_blank" rel="noopener"><img loading="lazy" src="<?=e('uploads/'.$photo['stored_name'])?>" alt="<?=e($photo['original_name'])?>"></a><div class="photo-footer"><span title="<?=e($photo['original_name'])?>"><?=e($photo['original_name'])?></span><a class="download-link" href="<?=e('uploads/'.$photo['stored_name'])?>" download="<?=e($photo['original_name'])?>" aria-label="Unduh foto">↓</a></div></article><?php endforeach; ?></div><?php endif; ?>
<div class="privacy-note">ⓘ Siapa pun yang memiliki tautan/QR galeri ini dapat melihat dan mengunduh foto. Bagikan hanya kepada orang yang kamu percayai.</div>
<footer>PhotoShare QR <span>•</span> <?=e($event['title'])?> <span class="footer-right"><a href="index.php">Beranda</a></span></footer>
</main>
<script>
const shareUrl = <?=json_encode($shareUrl, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)?>;
const qr = new QRCode(document.getElementById('qrcode'), {text:shareUrl,width:168,height:168,colorDark:'#17233b',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.M});
function downloadQR(){const canvas=document.querySelector('#qrcode canvas');const img=document.querySelector('#qrcode img');const url=canvas?canvas.toDataURL('image/png'):img?.src;if(!url){alert('QR belum siap. Coba lagi.');return;}const a=document.createElement('a');a.href=url;a.download='QR-PhotoShare.png';a.click();}
async function copyLink(){try{await navigator.clipboard.writeText(shareUrl);alert('Tautan galeri disalin.');}catch(e){prompt('Salin tautan ini:',shareUrl);}}
</script></body></html>
