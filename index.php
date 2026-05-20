<?php
require_once __DIR__ . '/backend/helpers.php';
require_once __DIR__ . '/backend/algos.php';
require_once __DIR__ . '/backend/controller.php';
require_once __DIR__ . '/frontend/logo.php';
/**
 * Motif Finding User Interface
 * Educational prototype for DNA motif discovery.
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web-Based DNA Motif Finder and Motif Voter</title>
    <link rel="stylesheet" href="assets/main.css">
    <link rel="stylesheet" href="assets/algorithm_table.css">
    <link rel="stylesheet" href="assets/section.css">
    <link rel="stylesheet" href="assets/motif.css">
    <link rel="stylesheet" href="assets/dna_form.css">
    <link rel="stylesheet" href="assets/logo.css">
    <link rel="stylesheet" href="assets/category.css">
    <link rel="stylesheet" href="assets/badge.css">
    <link rel="stylesheet" href="assets/progress_fill.css">
    <link rel="stylesheet" href="assets/academic_note.css">
    <link rel="stylesheet" href="assets/weight.css">
    <link rel="stylesheet" href="assets/confidence.css">
    <link rel="stylesheet" href="assets/fill.css">
    <link rel="stylesheet" href="assets/disclaimer.css">
    <link rel="stylesheet" href="assets/enhancement.css">
    <link rel="stylesheet" href="assets/grid_view.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
</head>
<body>
<div class="page-shell">
    <header class="hero">
        <div>
            <p class="eyebrow">Bioinformatics Prototype</p>
            <h1>DNA Motif Finder + MotifVoter</h1>
            <p>Upload DNA sequences in CSV form or paste sequences manually. The system runs 10 motif-finding algorithms, then feeds their motifs to MotifVoter.</p>
        </div>
        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle dark mode">
            <span class="theme-icon">🌙</span>
            <span class="theme-text">Dark Mode</span>
        </button>
    </header>
    <br><br><br>
    <main class="main-card">
        <?php if ($message): ?><div class="notice"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php require_once __DIR__ . '/frontend/dna_form.php'; ?>
        <?php if (!empty($results)): ?>
            
            <section class="green-section">
                <h2>Algorithm Results</h2>

                <div class="results-toolbar no-export">

                    <div class="view-toggle">
                        <button type="button"
                                class="view-btn active"
                                data-view="list">

                            List View
                        </button>

                        <button type="button"
                                class="view-btn"
                                data-view="grid">

                            Grid View
                        </button>
                    </div>

                    <div class="grid-controls" id="gridControls">

                        <input type="text"
                            id="gridSearch"
                            placeholder="Search algorithm...">

                        <select id="categoryFilter">
                            <option value="all">All Categories</option>
                            <option value="probabilistic">Probabilistic</option>
                            <option value="combinatorial">Combinatorial</option>
                            <option value="comparative">Comparative</option>
                        </select>

                        <select id="gridSort">
                            <option value="confidence-desc">
                                Confidence: High to Low
                            </option>

                            <option value="runtime-asc">
                                Runtime: Fastest First
                            </option>

                            <option value="coverage-desc">
                                Coverage: High to Low
                            </option>

                            <option value="similarity-desc">
                                Similarity: High to Low
                            </option>

                            <option value="score-desc">
                                Relative Score: High to Low
                            </option>

                            <option value="algorithm-asc">
                                Algorithm: A to Z
                            </option>
                        </select>

                    </div>

                </div>

                <div id="listView" class="results-view active-view">
                    <?php require_once __DIR__ . '/frontend/list_view.php'; ?>
                </div>

                <div id="gridView" class="results-view grid-view">
                    <?php require_once __DIR__ . '/frontend/grid_view.php'; ?>
                </div>
                
                <?php require_once __DIR__ . '/frontend/confidence.php'; ?>

                <?php require_once __DIR__ . '/frontend/academic_note.php'; ?>

                <?php require_once __DIR__ . '/frontend/disclaimer.php'; ?>
            
            </section>
            
            <section class="yellow-section">
                <h2>MotifVoter Final Result</h2>
                <p class="final-motif"><?= htmlspecialchars($voter['motif']) ?></p>
                <p>Final motif selected from the outputs of all 10 algorithms. Approximate occurrences: <strong><?= $voter['occurrences'] ?></strong>; sequence coverage: <strong><?= $voter['covered'] ?>/<?= count($sequences) ?></strong>.</p>
            </section>               
            
            <section class="blue-section">
                <h2>Sequence Logo</h2>
                <p class="hint">Generated from the motifs returned by the 10 algorithms.</p>
                <?php
                $logoMotifs = array_map(function($r) {
                    return $r['motif'];
                }, $results);
                
                echo render_sequence_logo($logoMotifs);
                ?>
            </section>
                        
            <section class="red-section">
                <h2>Highlighted Motif Occurrences</h2>
                <div class="sequence-list">
                    <?php foreach ($sequences as $index => $seq): ?>
                        <pre style="font-size: 18px;"><strong>Sequence <?= $index + 1 ?>:</strong> <?= highlight_sequence($seq, $selectedMotif, 1) ?></pre>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
    <br><br><br>
    <footer class="hero" style="background: linear-gradient(135deg, #ff3333, #ffcc00);">
        <div>
            <p class="eyebrow">Bioinformatics Tool Suite</p>
            <h1>Analyze. Ensemble. Discover.</h1>
            <p>Get started by dropping your dataset above. MotifVoter aggregates data from 10 distinct discovery engines to deliver consolidated genomic sequence insights instantly.</p>
        </div>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script>
$(document).ready(function() {
    if ($('#algorithmTable').length) {
        $('#algorithmTable').DataTable({
            paging: false,
            info: false,
            lengthChange: false,
            order: [[6, 'desc']],
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'Download Table CSV',
                    title: 'motif_algorithm_results',

                    exportOptions: {
                        // columns: ':not(.no-export)'
                        format: {
                        body: function (data, row, column, node) {

                            // Remove notes-text before export
                            $(node).find('.no-export').remove();

                            return $(node).text().trim();
                        }
                    }
                    }
                }
            ]
        });
    }
});
</script>
<script>
$(document).on('click', '.notes-toggle', function() {
    var notes = $(this).next('.notes-content');

    notes.slideToggle(150);

    if ($(this).text() === 'Show Notes') {
        $(this).text('Hide Notes');
    } else {
        $(this).text('Show Notes');
    }
});
</script>
<script>
$(document).on('click', '.notes-icon', function () {

    const popup = $(this).next('.notes-popup');

    $('.notes-popup').not(popup).slideUp(120);

    popup.slideToggle(120);
});
</script>
<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('csv_file');
const fileName = document.getElementById('fileName');

dropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', function() {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropZone.classList.remove('dragover');

    if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        fileName.textContent = e.dataTransfer.files[0].name;
    }
});

fileInput.addEventListener('change', function() {
    fileName.textContent = fileInput.files.length > 0
        ? fileInput.files[0].name
        : 'No file selected';
});
</script>

<!-- Grid w/ List View Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const buttons = document.querySelectorAll('.view-btn');
    const views = document.querySelectorAll('.results-view');

    const gridControls = document.getElementById('gridControls');

    const gridView = document.getElementById('gridView');

    const gridSearch = document.getElementById('gridSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const gridSort = document.getElementById('gridSort');

    /* =========================
       VIEW SWITCH
    ========================= */

    buttons.forEach(function(button) {

        button.addEventListener('click', function() {

            const selectedView = this.dataset.view;

            const targetView =
                document.getElementById(selectedView + 'View');

            buttons.forEach(btn =>
                btn.classList.remove('active')
            );

            views.forEach(view =>
                view.classList.remove('active-view')
            );

            this.classList.add('active');

            if (targetView) {
                targetView.classList.add('active-view');
            }

            /* Show filters only on grid */
            if (selectedView === 'grid') {
                gridControls.classList.add('show-grid-controls');
            }
            else {
                gridControls.classList.remove('show-grid-controls');
            }
        });
    });

    /* =========================
       GRID FILTERS
    ========================= */

    function applyGridFiltersAndSort() {

        if (!gridView) return;

        const cards =
            Array.from(
                gridView.querySelectorAll('.algorithm-card')
            );

        const searchText =
            gridSearch.value.toLowerCase().trim();

        const selectedCategory =
            categoryFilter.value;

        const selectedSort =
            gridSort.value;

        /* FILTER */
        cards.forEach(function(card) {

            const algorithm =
                card.dataset.algorithm || '';

            const category =
                card.dataset.category || '';

            const matchesSearch =
                algorithm.includes(searchText);

            const matchesCategory =
                selectedCategory === 'all'
                || category === selectedCategory;

            card.style.display =
                (matchesSearch && matchesCategory)
                ? ''
                : 'none';
        });

        /* SORT */
        const visibleCards =
            cards.filter(card =>
                card.style.display !== 'none'
            );

        visibleCards.sort(function(a, b) {

            switch (selectedSort) {

                case 'runtime-asc':
                    return parseFloat(a.dataset.runtime)
                         - parseFloat(b.dataset.runtime);

                case 'coverage-desc':
                    return parseFloat(b.dataset.coverage)
                         - parseFloat(a.dataset.coverage);

                case 'similarity-desc':
                    return parseFloat(b.dataset.similarity)
                         - parseFloat(a.dataset.similarity);

                case 'score-desc':
                    return parseFloat(b.dataset.score)
                         - parseFloat(a.dataset.score);

                case 'algorithm-asc':
                    return a.dataset.algorithm
                        .localeCompare(b.dataset.algorithm);

                case 'confidence-desc':
                default:
                    return parseFloat(b.dataset.confidence)
                         - parseFloat(a.dataset.confidence);
            }
        });

        visibleCards.forEach(card =>
            gridView.appendChild(card)
        );
    }

    gridSearch.addEventListener(
        'input',
        applyGridFiltersAndSort
    );

    categoryFilter.addEventListener(
        'change',
        applyGridFiltersAndSort
    );

    gridSort.addEventListener(
        'change',
        applyGridFiltersAndSort
    );

});
</script>
<!-- DARK MODE TOGGLE -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.querySelector('.theme-icon');
    const themeText = document.querySelector('.theme-text');

    const savedTheme = localStorage.getItem('theme');

    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        themeIcon.textContent = '☀️';
        themeText.textContent = 'Light Mode';
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            document.body.classList.toggle('dark-mode');

            const isDark = document.body.classList.contains('dark-mode');

            localStorage.setItem('theme', isDark ? 'dark' : 'light');

            themeIcon.textContent = isDark ? '☀️' : '🌙';
            themeText.textContent = isDark ? 'Light Mode' : 'Dark Mode';
        });
    }

    if (typeof window.redrawSequenceLogo === "function") {
        window.redrawSequenceLogo();
    }
});
</script>
<script>
    document.getElementById('csv_file').addEventListener('change', function(e) {
        let fileName = e.target.files[0].name;
        if(!fileName.endsWith('.csv')) {
            alert('Validation Error: System expects a standardized .csv structure.');
            e.target.value = '';
        }
    });
</script>
</body>
</html>
