<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';

$id   = (int)($_GET['id'] ?? 0);
$case = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM use_cases WHERE id = ?");
    $stmt->execute([$id]);
    $case = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$case) { header('Location: use_cases.php'); exit; }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = trim($_POST['title'] ?? '');
    $tag    = trim($_POST['tag'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $stat   = trim($_POST['stat_label'] ?? '');
    $img    = trim($_POST['image_url'] ?? '');
    $order  = max(0, (int)($_POST['sort_order'] ?? 0));
    $active = isset($_POST['is_active']) ? 1 : 0;

    if (!$title || !$tag || !$desc) {
        $error = 'Title, tag, and description are required.';
    } else {
        if ($id) {
            $pdo->prepare(
                "UPDATE use_cases SET title=?, tag=?, description=?, stat_label=?, image_url=?, sort_order=?, is_active=? WHERE id=?"
            )->execute([$title, $tag, $desc, $stat ?: null, $img ?: null, $order, $active, $id]);
        } else {
            $pdo->prepare(
                "INSERT INTO use_cases (title, tag, description, stat_label, image_url, sort_order, is_active) VALUES (?,?,?,?,?,?,?)"
            )->execute([$title, $tag, $desc, $stat ?: null, $img ?: null, $order, $active]);
        }
        header('Location: use_cases.php');
        exit;
    }
}

$v = $case ?? ['title'=>'','tag'=>'','description'=>'','stat_label'=>'','image_url'=>'','sort_order'=>0,'is_active'=>1];

// Normalize image URL for display
function admin_img(string $url): string {
    if (!$url) return '';
    if (str_starts_with($url, 'http')) return $url;
    if (!str_starts_with($url, '/')) $url = '/images/use-cases/' . $url;
    return SITE_BASE . $url;
}

open_layout($id ? 'Edit Use Case' : 'Add Use Case', 'use_cases');
?>

<div class="max-w-2xl mx-auto">

  <!-- Back + heading -->
  <div class="flex items-center gap-3 mb-6">
    <a href="use_cases.php" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50">
      <span class="material-icons text-lg">arrow_back</span>
    </a>
    <div>
      <h2 class="text-xl font-extrabold" style="color:#0f2d52"><?= $id ? 'Edit Use Case' : 'Add New Use Case' ?></h2>
      <p class="text-xs text-slate-400"><?= $id ? "Editing ID #{$id}" : 'New card for the "What We Power" section' ?></p>
    </div>
  </div>

  <?php if ($error): ?>
  <div class="mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" id="ucForm" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-5">

    <!-- Hidden image_url — populated by uploader or URL fallback -->
    <input type="hidden" name="image_url" id="imageUrlField" value="<?= htmlspecialchars($v['image_url'] ?? '') ?>" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <!-- Title -->
      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Title *</label>
        <input name="title" value="<?= htmlspecialchars($v['title']) ?>" required
               placeholder="e.g. Home Solar Installation"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
      </div>

      <!-- Tag -->
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tag / Category *</label>
        <input name="tag" value="<?= htmlspecialchars($v['tag']) ?>" required
               placeholder="e.g. Residential"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
      </div>

      <!-- Stat label -->
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Stat / Highlight</label>
        <input name="stat_label" value="<?= htmlspecialchars($v['stat_label'] ?? '') ?>"
               placeholder="e.g. From KES 3,500/mo"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
      </div>
    </div>

    <!-- Description -->
    <div>
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Description *</label>
      <textarea name="description" rows="3" required
                placeholder="Describe this use case in 1–2 sentences…"
                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 resize-none"><?= htmlspecialchars($v['description']) ?></textarea>
    </div>

    <!-- ── Image uploader ─────────────────────────────────────────────── -->
    <div>
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Image</label>

      <!-- Drop zone -->
      <div id="dropZone"
           class="relative rounded-xl border-2 border-dashed transition-colors cursor-pointer"
           style="border-color:#cbd5e1; min-height:180px;"
           onclick="document.getElementById('fileInput').click()"
           ondragover="handleDragOver(event)"
           ondragleave="handleDragLeave(event)"
           ondrop="handleDrop(event)">

        <!-- Hidden file input -->
        <input type="file" id="fileInput" name="file_upload" accept="image/jpeg,image/png,image/webp,image/gif"
               class="hidden" onchange="handleFileSelect(this.files[0])" />

        <!-- Idle state (shown when no image selected) -->
        <div id="dropPlaceholder" class="flex flex-col items-center justify-center gap-2 py-10 px-4 text-center"
             style="display:<?= $v['image_url'] ? 'none' : 'flex' ?> !important">
          <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-1" style="background:#f1f5f9">
            <span class="material-icons text-2xl text-slate-400">cloud_upload</span>
          </div>
          <p class="text-sm font-semibold text-slate-600">Drag & drop an image here</p>
          <p class="text-xs text-slate-400">or click to browse — JPG, PNG, WebP, GIF · max 5 MB</p>
        </div>

        <!-- Preview (shown once image is selected/loaded) -->
        <div id="previewWrap" class="relative"
             style="display:<?= $v['image_url'] ? 'block' : 'none' ?>">
          <img id="imgPreview"
               src="<?= htmlspecialchars(admin_img($v['image_url'] ?? '')) ?>"
               alt="Preview"
               class="w-full rounded-xl object-cover"
               style="max-height:260px; display:<?= $v['image_url'] ? 'block' : 'none' ?>" />

          <!-- Overlay on hover: change image -->
          <div id="previewOverlay"
               class="absolute inset-0 rounded-xl flex items-center justify-center gap-3 opacity-0 hover:opacity-100 transition-opacity"
               style="background:rgba(0,0,0,0.45)">
            <button type="button" onclick="document.getElementById('fileInput').click(); event.stopPropagation();"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-full text-white text-xs font-bold"
                    style="background:#f97316">
              <span class="material-icons text-sm">upload</span> Change
            </button>
            <button type="button" onclick="clearImage(event)"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-full text-white text-xs font-bold"
                    style="background:rgba(255,255,255,0.2)">
              <span class="material-icons text-sm">delete</span> Remove
            </button>
          </div>
        </div>

        <!-- Upload progress bar -->
        <div id="uploadProgress" class="absolute bottom-0 left-0 right-0 rounded-b-xl overflow-hidden" style="display:none; height:4px; background:#e2e8f0">
          <div id="uploadBar" class="h-full transition-all" style="width:0%; background:#f97316"></div>
        </div>
      </div>

      <!-- Upload status message -->
      <div id="uploadStatus" class="mt-2 text-xs hidden"></div>

      <!-- URL fallback -->
      <details class="mt-3">
        <summary class="text-xs text-slate-400 cursor-pointer select-none hover:text-slate-600 transition-colors">
          Or enter an image URL manually
        </summary>
        <input id="urlFallback" type="text"
               value="<?= htmlspecialchars($v['image_url'] ?? '') ?>"
               oninput="setImageFromUrl(this.value)"
               placeholder="https://example.com/photo.jpg  or  /images/photo.jpg"
               class="mt-2 w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
      </details>
    </div>

    <div class="grid grid-cols-2 gap-5">
      <!-- Sort order -->
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sort Order</label>
        <input name="sort_order" type="number" min="0" value="<?= (int)$v['sort_order'] ?>"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
      </div>

      <!-- Active toggle -->
      <div class="flex flex-col justify-center">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1"
                 <?= $v['is_active'] ? 'checked' : '' ?>
                 class="w-4 h-4 rounded accent-orange-500" />
          <span class="text-sm font-semibold text-slate-700">Visible on website</span>
        </label>
      </div>
    </div>

    <!-- Submit -->
    <div class="flex gap-3 pt-2">
      <button type="submit" id="submitBtn"
              class="flex-1 py-3 rounded-xl text-white font-bold text-sm hover:opacity-90 transition-opacity"
              style="background:#f97316">
        <?= $id ? 'Save Changes' : 'Create Use Case' ?>
      </button>
      <a href="use_cases.php"
         class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition-colors">
        Cancel
      </a>
    </div>
  </form>
</div>

<script>
const SITE_BASE      = '<?= SITE_BASE ?>';
const dropZone       = document.getElementById('dropZone');
const fileInput      = document.getElementById('fileInput');
const dropPlaceholder= document.getElementById('dropPlaceholder');
const previewWrap    = document.getElementById('previewWrap');
const imgPreview     = document.getElementById('imgPreview');
const uploadProgress = document.getElementById('uploadProgress');
const uploadBar      = document.getElementById('uploadBar');
const uploadStatus   = document.getElementById('uploadStatus');
const imageUrlField  = document.getElementById('imageUrlField');
const urlFallback    = document.getElementById('urlFallback');
const submitBtn      = document.getElementById('submitBtn');

// ── Drag events ──────────────────────────────────────────────
function handleDragOver(e) {
  e.preventDefault();
  dropZone.style.borderColor = '#f97316';
  dropZone.style.background  = '#fff7ed';
}
function handleDragLeave(e) {
  dropZone.style.borderColor = '#cbd5e1';
  dropZone.style.background  = '';
}
function handleDrop(e) {
  e.preventDefault();
  dropZone.style.borderColor = '#cbd5e1';
  dropZone.style.background  = '';
  const file = e.dataTransfer.files[0];
  if (file) handleFileSelect(file);
}

// ── File selected ────────────────────────────────────────────
function handleFileSelect(file) {
  if (!file) return;

  const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
  if (!allowed.includes(file.type)) {
    showStatus('Only JPG, PNG, WebP and GIF files are allowed.', 'error');
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    showStatus('Image must be under 5 MB.', 'error');
    return;
  }

  // Show local preview immediately while uploading
  const reader = new FileReader();
  reader.onload = e => showPreview(e.target.result);
  reader.readAsDataURL(file);

  uploadFile(file);
}

// ── XHR upload ───────────────────────────────────────────────
function uploadFile(file) {
  const fd = new FormData();
  fd.append('image', file);

  const xhr = new XMLHttpRequest();

  // Disable submit while uploading
  submitBtn.disabled = true;
  submitBtn.textContent = '⏳ Uploading image…';

  xhr.upload.onprogress = e => {
    if (e.lengthComputable) {
      const pct = Math.round((e.loaded / e.total) * 100);
      uploadProgress.style.display = 'block';
      uploadBar.style.width = pct + '%';
    }
  };

  xhr.onload = () => {
    uploadProgress.style.display = 'none';
    uploadBar.style.width = '0%';
    submitBtn.disabled = false;
    submitBtn.textContent = '<?= $id ? 'Save Changes' : 'Create Use Case' ?>';

    try {
      const res = JSON.parse(xhr.responseText);
      if (xhr.status === 200 && res.url) {
        imageUrlField.value = res.url;
        urlFallback.value   = res.url;
        showPreview(res.url);
        showStatus('Image uploaded successfully.', 'success');
      } else {
        showStatus(res.error || 'Upload failed.', 'error');
        clearImage();
      }
    } catch {
      showStatus('Unexpected server response.', 'error');
    }
  };

  xhr.onerror = () => {
    uploadProgress.style.display = 'none';
    submitBtn.disabled = false;
    submitBtn.textContent = '<?= $id ? 'Save Changes' : 'Create Use Case' ?>';
    showStatus('Network error — upload failed.', 'error');
  };

  xhr.open('POST', 'api/upload_image.php');
  xhr.send(fd);
}

// ── Helpers ──────────────────────────────────────────────────
function adminImgUrl(src) {
  if (!src) return src;
  if (src.startsWith('http') || src.startsWith('data:')) return src;
  if (!src.startsWith('/')) src = '/images/use-cases/' + src;
  return SITE_BASE + src;
}

function showPreview(src) {
  imgPreview.src          = adminImgUrl(src);
  imgPreview.style.display= 'block';
  previewWrap.style.display = 'block';
  dropPlaceholder.style.display = 'none';
  dropZone.style.borderColor = '#f97316';
  dropZone.style.borderStyle = 'solid';
}

function clearImage(e) {
  if (e) e.stopPropagation();
  imgPreview.src            = '';
  imgPreview.style.display  = 'none';
  previewWrap.style.display = 'none';
  dropPlaceholder.style.display = 'flex';
  dropZone.style.borderColor = '#cbd5e1';
  dropZone.style.borderStyle = 'dashed';
  imageUrlField.value = '';
  urlFallback.value   = '';
  fileInput.value     = '';
  uploadStatus.classList.add('hidden');
}

function setImageFromUrl(url) {
  imageUrlField.value = url;
  if (url) {
    showPreview(url);
    showStatus('', '');
  } else {
    clearImage();
  }
}

function showStatus(msg, type) {
  if (!msg) { uploadStatus.classList.add('hidden'); return; }
  uploadStatus.classList.remove('hidden');
  uploadStatus.textContent = msg;
  uploadStatus.style.color = type === 'error' ? '#ef4444' : '#16a34a';
}
</script>

<?php close_layout(); ?>
