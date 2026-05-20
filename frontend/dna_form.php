<section class="blue-section">

    <form method="post" enctype="multipart/form-data" class="input-form">
        
        <div class="form-header">
            <h2>Input DNA Sequences</h2>

            <p class="hint">
                CSV format:
                <strong>id,sequence,expression</strong>.
                The expression column is optional and used by REDUCE.
            </p>
        </div>

        <!-- Upload Area -->
        <div class="input-card input-card-blue">
            <div class="upload-box" id="dropZone">
                <input type="file" id="csv_file" name="csv_file" accept=".csv,text/csv" hidden>

                <label for="csv_file" class="upload-label">
                    <span class="upload-icon">⬆</span>
                    <strong>Drop CSV file here</strong>
                    <small>or click to browse</small>
                    <span id="fileName" class="file-name">No file selected</span>
                </label>
                <small class="upload-note">
                    Supports .CSV files containing DNA sequences
                </small>
            </div>

            <a href="?download_template=1" class="btn-secondary">
                Download CSV Template
            </a>

        </div>

        <!-- Divider -->
        <div class="or-divider">
            <span>OR</span>
        </div>

        <!-- Paste Area -->
        <div class="input-card input-card-green">

            <label for="sequences">Paste DNA Sequences</label>

            <textarea id="sequences"
                name="sequences"
                rows="7"><?= htmlspecialchars($sequenceText) ?></textarea>

        </div>

        <!-- Controls -->
        <div class="controls-card">

            <div class="controls">

                <div class="control-group">
                    <label for="motif_length">Motif Length</label>

                    <input type="number"
                        id="motif_length"
                        name="motif_length"
                        min="3"
                        max="12"
                        value="<?= htmlspecialchars($k) ?>">
                </div>

                <button type="submit">
                    Run 10 Algorithms
                </button>

            </div>

        </div>

    </form>

</section>