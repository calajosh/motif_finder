<?php

/**
 * Motif Finding Controller Logic
 */

$categories = [
    'PWM Scanning'              => 'Probabilistic',
    'Gibbs Motif Sampler'       => 'Probabilistic',
    'MEME'                      => 'Probabilistic',

    'Exhaustive Pattern-Driven' => 'Combinatorial',
    'Sample-Driven'             => 'Combinatorial',
    'Extended Sample-Driven'    => 'Combinatorial',
    'Suffix Tree-Based'         => 'Combinatorial',

    'Graph-Based'               => 'Comparative',
    'REDUCE'                    => 'Comparative',
    'Phylogenetic Footprinting' => 'Comparative',

    'MotifVoter'                => 'Ensemble Layer'
];

$defaultSequences = "GCACGCGGTATCGTTAGCTTGACAATGAAGACCCCCGCTCGACAGGAAT\nGCATACTTTGACACTGACTTCGCTTCTTTAATGTTTAATGAAACATGCG\nCCCTCTGGAAATTAGTGCGGTCTCACAACCCGAGGAATGACCAAAGTTG\nGTATTGAAAGTAAGTAGATGGTGATCCCCATGACACCAAAGATGCTGCA\nCAACGCTGGCCAAGCTTGACAGGTGACGCTTGACTGCGGCCCTCG\nGTCTCTTGACCGCTTAATCCTAAAGGGATGTAGTACGCCATG\nGCACAGGAGCGGAAGCTTGACCTTAATCT";

$sequenceText = $_POST['sequences'] ?? $defaultSequences;
$k = isset($_POST['motif_length']) ? max(3, min(12, (int)$_POST['motif_length'])) : 6;
$sequences = parse_sequences_from_text($sequenceText);
$expressions = [];
$message = '';

if (isset($_GET['download_template'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="motif_sequence_template.csv"');

    echo "id,sequence,expression\n";
    echo "seq1,TTGACA,5.20\n";
    echo "seq2,TTGACA,4.80\n";
    echo "seq3,TTGACA,6.10\n";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
    [$csvSeqs, $csvExpr, $ids] = parse_csv_sequences($_FILES['csv_file']['tmp_name']);
    if (!empty($csvSeqs)) {
        $sequences = $csvSeqs;
        $expressions = $csvExpr;
        $sequenceText = implode("\n", $csvSeqs);
        $message = 'CSV uploaded successfully. Loaded ' . count($csvSeqs) . ' sequences.';
    } else {
        $message = 'CSV uploaded, but no valid DNA sequences were found. Use a column named sequence.';
    }
}

$results = [];
$voter = null;
$selectedMotif = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && count($sequences) > 0) {
    $results = run_all_algorithms($sequences, $k, $expressions);

    // Normalize raw algorithm scores into a fair 0–100 scale.
    $rawScores = array_column($results, 'score');
    $minScore = min($rawScores);
    $maxScore = max($rawScores);

    foreach ($results as $key => $res) {
        $score = isset($res['score']) ? $res['score'] : 0;

        if ($maxScore == $minScore) {
            $normalized = 100;
        } else {
            $normalized = (($score - $minScore) / ($maxScore - $minScore)) * 100;
        }

        $results[$key]['relative_internal_score'] = round($normalized, 2);
    }

    $voter = motif_voter($results, $sequences);
    $selectedMotif = $voter['motif'];

    foreach ($results as $key => $res) {
        $results[$key]['similarity'] = calculate_similarity(
            $res['motif'],
            $selectedMotif
        );
    }

    foreach ($results as $key => $res) {

        $coveragePercent = count($sequences) > 0
            ? ($res['covered'] / count($sequences)) * 100
            : 0;

        $similarity = $res['similarity'] ?? 0;
        $normalizedScore = $res['relative_internal_score'] ?? 0;
        $runtime = $res['runtime_ms'] ?? 0;

        // Cap runtime penalty so slow algorithms are penalized but not destroyed
        $runtimePenalty = min($runtime, 100);

        $confidence =
            ($similarity * 0.35) +
            ($coveragePercent * 0.30) +
            ($normalizedScore * 0.25) -
            ($runtimePenalty * 0.10);

        $results[$key]['confidence'] = max(0, min(100, round($confidence, 2)));
    }

}