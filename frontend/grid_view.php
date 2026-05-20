<?php foreach ($results as $res): ?>

    <?php
        $category = $categories[$res['algorithm']] ?? 'General';
        $categorySlug = strtolower(str_replace(' ', '-', $category));

        $coveragePercent = (count($sequences) > 0)
            ? ($res['covered'] / count($sequences)) * 100
            : 0;

        $confidence = $res['confidence'];

        if ($confidence >= 80) {
            $confidenceClass = 'confidence-high';
            $confidenceLabel = 'High';
        }
        elseif ($confidence >= 50) {
            $confidenceClass = 'confidence-moderate';
            $confidenceLabel = 'Moderate';
        }
        else {
            $confidenceClass = 'confidence-low';
            $confidenceLabel = 'Low';
        }

        $score = $res['relative_internal_score'];

        if ($score >= 75) {
            $scoreClass = 'fill-blue';
        }
        elseif ($score >= 50) {
            $scoreClass = 'fill-green';
        }
        elseif ($score >= 25) {
            $scoreClass = 'fill-orange';
        }
        else {
            $scoreClass = 'fill-red';
        }

        $displayWidth = ($score > 0)
            ? max($score, 6)
            : 0;
    ?>

    <div class="algorithm-card card-<?= $categorySlug ?>"
         data-algorithm="<?= htmlspecialchars(strtolower($res['algorithm'])) ?>"
         data-category="<?= htmlspecialchars($categorySlug) ?>"
         data-runtime="<?= htmlspecialchars($res['runtime_ms']) ?>"
         data-coverage="<?= htmlspecialchars($coveragePercent) ?>"
         data-similarity="<?= htmlspecialchars($res['similarity']) ?>"
         data-score="<?= htmlspecialchars($score) ?>"
         data-confidence="<?= htmlspecialchars($confidence) ?>">

        <div class="algorithm-card-header">
            <span class="badge badge-<?= $categorySlug ?>">
                <?= htmlspecialchars($res['algorithm']) ?>
            </span>

            <span class="motif-chip">
                <?= htmlspecialchars($res['motif']) ?>
            </span>
        </div>

        <p class="algorithm-category txt-<?= $categorySlug ?>">
            <?= htmlspecialchars($category) ?>
        </p>

        <p class="algorithm-notes">
            <?= htmlspecialchars($res['notes']) ?>
        </p>

        <div class="grid-metric">
            <span>Runtime</span>

            <strong class="runtime-badge <?= runtime_class($res['runtime_ms']) ?>">
                <?= format_runtime($res['runtime_ms']) ?>
            </strong>
        </div>

        <div class="grid-progress">
            <label>Match Coverage</label>

            <div class="progress-mini">
                <div class="progress-fill fill-green"
                     style="width: <?= $coveragePercent ?>%">
                </div>

                <span class="progress-label">
                    <?= $res['covered'] ?>/<?= count($sequences) ?>
                    •
                    <?= number_format($coveragePercent, 2) ?>%
                </span>
            </div>

            <small class="match-mode">
                <?= $res['maxMismatch'] > 0 ? 'Mismatch-Tolerant' : 'Strict Match' ?>
            </small>
        </div>

        <div class="grid-progress">
            <label>MotifVoter Similarity</label>

            <div class="progress-mini">
                <div class="progress-fill fill-blue"
                     style="width: <?= $res['similarity'] ?>%">
                </div>

                <span class="progress-label">
                    <?= number_format($res['similarity'], 2) ?>%
                </span>
            </div>
        </div>

        <div class="grid-progress">
            <label>Relative Internal Score</label>

            <div class="progress-mini">
                <div class="progress-fill <?= $scoreClass ?>"
                     style="width: <?= $displayWidth ?>%">
                </div>

                <span class="progress-label">
                    <?= number_format($score, 2) ?>%
                </span>
            </div>
        </div>

        <div class="grid-progress">
            <label>Confidence Metric</label>

            <div class="progress-mini">
                <div class="progress-fill <?= $confidenceClass ?>"
                     style="width: <?= $confidence ?>%">
                </div>

                <span class="progress-label">
                    <?= number_format($confidence, 2) ?>%
                </span>
            </div>

            <small class="<?= $confidenceClass ?>-label">
                <?= $confidenceLabel ?>
            </small>
        </div>

    </div>

<?php endforeach; ?>