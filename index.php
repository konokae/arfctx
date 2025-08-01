<?php
// index.php - mesin pencari musik dengan AJAX real-time dan sumber CSV
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Arfctx Music Search</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
    }
    .search-box {
      max-width: 600px;
      margin: 40px auto 30px;
    }
    .highlight-card {
      background-color: #ffffff;
      border-left: 5px solid #0d6efd;
      padding: 1rem;
      border-radius: .5rem;
      margin-bottom: 1.2rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    #search-results {
      max-width: 800px;
      margin: 0 auto;
    }
    .app-title {
      font-size: 1.8rem;
      font-weight: bold;
    }
    footer {
      text-align: center;
      font-size: 0.9rem;
      color: #777;
      margin: 60px 0 20px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="text-center mb-4">
    <div class="app-title"><i class="bi bi-music-note-list"></i> Arfctx Music Search Engine</div>
    <p class="text-muted">Cari lagu, channel, dan informasi metadata dari koleksi CSV</p>
  </div>

  <div class="search-box text-center">
    <div class="input-group">
      <input type="text" id="search-input" class="form-control form-control-lg" placeholder="Cari lagu, channel, judul, dll..." autofocus>
      <button class="btn btn-secondary btn-lg" id="clear-btn">Clear</button>
    </div>
  </div>

  <div id="search-info" class="text-center text-muted mb-3"></div>

  <div id="search-results"></div>
  <nav>
    <ul class="pagination justify-content-center" id="pagination"></ul>
  </nav>

  <footer>
    <div id="summary-info"></div>
    <br>
    Mesin ini dikembangkan oleh <strong>Zenstudio</strong> | Hak cipta &copy; <?= date('Y') ?> Arfctx Music Project
  </footer>
</div>

<script>
const input = document.getElementById('search-input');
const resultBox = document.getElementById('search-results');
const paginationBox = document.getElementById('pagination');
const clearBtn = document.getElementById('clear-btn');
const infoBox = document.getElementById('search-info');
const summaryBox = document.getElementById('summary-info');
let timer = null;
let currentPage = 1;

function fetchResults(page = 1) {
  const q = input.value.trim();
  if (q.length > 1) {
    fetch('ajax_search.php?q=' + encodeURIComponent(q) + '&page=' + page)
      .then(res => res.text())
      .then(html => {
        resultBox.innerHTML = html;
        renderPagination(page);
      });
    fetch('ajax_search.php?count=1&q=' + encodeURIComponent(q))
      .then(res => res.json())
      .then(data => {
        infoBox.innerHTML = `${data.total} hasil ditemukan untuk kata kunci <strong>"${q}"</strong>`;
      });
  } else {
    resultBox.innerHTML = '';
    paginationBox.innerHTML = '';
    infoBox.innerHTML = '';
  }
}

function renderPagination(page) {
  const q = input.value.trim();
  fetch('ajax_search.php?count=1&q=' + encodeURIComponent(q))
    .then(res => res.json())
    .then(data => {
      const totalPages = Math.ceil(data.total / 10);
      let html = '';
      let start = Math.max(1, page - 2);
      let end = Math.min(totalPages, page + 2);
      if (start > 1) html += `<li class='page-item'><a class='page-link' href='#' data-page='1'>1</a></li><li class='page-item disabled'><span class='page-link'>...</span></li>`;
      for (let i = start; i <= end; i++) {
        html += `<li class='page-item ${i === page ? 'active' : ''}'><a class='page-link' href='#' data-page='${i}'>${i}</a></li>`;
      }
      if (end < totalPages) html += `<li class='page-item disabled'><span class='page-link'>...</span></li><li class='page-item'><a class='page-link' href='#' data-page='${totalPages}'>${totalPages}</a></li>`;
      paginationBox.innerHTML = html;
    });
}

input.addEventListener('input', function () {
  clearTimeout(timer);
  timer = setTimeout(() => {
    currentPage = 1;
    fetchResults(currentPage);
  }, 300);
});

paginationBox.addEventListener('click', function (e) {
  if (e.target.tagName === 'A') {
    e.preventDefault();
    const page = parseInt(e.target.getAttribute('data-page'));
    if (page) {
      currentPage = page;
      fetchResults(currentPage);
    }
  }
});

clearBtn.addEventListener('click', () => {
  input.value = '';
  resultBox.innerHTML = '';
  paginationBox.innerHTML = '';
  infoBox.innerHTML = '';
});

// Fetch summary info on load
fetch('summary.php')
  .then(res => res.text())
  .then(html => {
    summaryBox.innerHTML = html;
  });
</script>

</body>
</html>
