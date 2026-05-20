<div class="table-wrap">
    <table id="algorithmTable" class="display">
        <thead>
            <tr>
                <th><span>Algorithm</span></th>
                <th><span>Motif</span></th>
                <th><span>Runtime</span></th>
                <th><span>Match Coverage</span></th>
                <th><span>MotifVoter Similarity</span></th>
                <th><span>Relative Internal Score</span></th>
                <th><span>Confidence Metric</span></th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($results as $i => $res): ?>

            <?php
                $category = $categories[$res['algorithm']] ?? 'General';

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
                    ? max($score, 1)
                    : 0;

                $outsideLabel = ($score > 0 && $score < 18);
            ?>

            <tr>
                <td>
                    <span class="badge badge-<?= strtolower(str_replace(' ', '-', $category)) ?>" style="font-size: 15px;">
                        <?= htmlspecialchars($res['algorithm']) ?>
                    </span>

                    <span class="notes-icon no-export" title="Show Notes">
                        ⓘ
                    </span>

                    <div class="notes-popup no-export">
                        <p class="txt-<?= strtolower(str_replace(' ', '-', $category)) ?>" style="font-weight: 700; margin-bottom: 6px;">
                            <?= htmlspecialchars($category) ?>
                        </p>

                        <i class="notes-text">
                            <?= htmlspecialchars($res['notes']) ?>
                        </i>
                    </div>
                </td>

                <td>
                    <span class="motif-chip">
                        <?= htmlspecialchars($res['motif']) ?>
                    </span>
                </td>

                <td data-order="<?= $res['runtime_ms'] ?>" style="text-align: right;">
                    <span class="runtime-badge <?= runtime_class($res['runtime_ms']) ?>">
                        <?= format_runtime($res['runtime_ms']) ?>
                    </span>
                </td>

                <td data-order="<?= $coveragePercent ?>">
                    <div class="progress-mini">
                        <div class="progress-fill fill-green" style="width: <?= $coveragePercent ?>%"></div>

                        <span class="progress-label">
                            <?= $res['covered'] ?>/<?= count($sequences) ?>
                            •
                            <?= number_format($coveragePercent, 2) ?>%
                        </span>
                    </div>

                    <small class="match-mode">
                        <?= $res['maxMismatch'] > 0 ? 'Mismatch-Tolerant' : 'Strict Match' ?>
                    </small>
                </td>

                <td data-order="<?= $res['similarity'] ?>">
                    <div class="progress-mini">
                        <div class="progress-fill fill-blue" style="width: <?= $res['similarity'] ?>%"></div>

                        <span class="progress-label">
                            <?= number_format($res['similarity'], 2) ?>%
                        </span>
                    </div>
                </td>

                <td data-order="<?= $score ?>">
                    <div class="score-progress">
                        <div class="score-progress-fill <?= $scoreClass ?>" style="width: <?= $displayWidth ?>%"></div>

                        <span class="score-progress-label">
                            <?= number_format($score, 2) ?>%
                        </span>
                    </div>
                </td>

                <td data-order="<?= $confidence ?>">
                    <div class="progress-mini">
                        <div class="progress-fill <?= $confidenceClass ?>" style="width: <?= $confidence ?>%"></div>

                        <span class="progress-label">
                            <?= number_format($confidence, 2) ?>%
                        </span>
                    </div>

                    <small class="<?= $confidenceClass ?>-label">
                        <?= $confidenceLabel ?>
                    </small>
                </td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>
</div>
