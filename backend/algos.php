<?php

require_once __DIR__ . '/helpers.php';
/**
 * Motif Finding Simplified Algorithms
 */
// 1. PWM Scanning / Known Motif Scanning (prototype uses most frequent seed motif)
function algo_pwm_scanning($sequences, $k) {
    $counts = count_exact_kmers($sequences, $k);
    $topMotifs = array_slice(array_keys($counts), 0, min(8, count($counts)));
    $pwm = build_pwm($topMotifs);

    $best = '';
    $bestScore = -1;
    foreach (all_unique_kmers($sequences, $k) as $candidate) {
        $score = score_kmer_pwm($candidate, $pwm) * (($counts[$candidate] ?? 0) + 1);
        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $candidate;
        }
    }
    return result_pack([
        'algorithm' => 'PWM Scanning',
        'motif' => $best,
        'score' => $bestScore,
        'sequences' => $sequences,
        'maxMismatch' => 0,
        'notes' => 'Evaluates k-mers using a Position Weight Matrix; matches are scored based on log-likelihood of occurrence.'
    ]);
}

// 2. Gibbs Motif Sampler
function algo_gibbs_sampler($sequences, $k, $iterations = 120) {
    if (empty($sequences)) return result_pack([
        'algorithm' => 'Gibbs Motif Sampler',
        'motif' => '',
        'score' => 0,
        'sequences' => $sequences
    ]);
    $selected = [];
    foreach ($sequences as $seq) {
        if (strlen($seq) < $k) continue;
        $start = rand(0, strlen($seq) - $k);
        $selected[] = substr($seq, $start, $k);
    }

    $bestConsensus = consensus_from_motifs($selected);
    [$bestCov, $bestOcc] = coverage_score($sequences, $bestConsensus, 1);
    $bestScore = $bestCov * 100 + $bestOcc;

    for ($it = 0; $it < $iterations; $it++) {
        $removeIndex = array_rand($sequences);
        $training = $selected;
        unset($training[$removeIndex]);
        $pwm = build_pwm($training);
        $seq = $sequences[$removeIndex];
        $bestLocal = null;
        $bestLocalScore = -1;
        foreach (get_kmers($seq, $k) as $kmer) {
            $score = score_kmer_pwm($kmer, $pwm);
            if ($score > $bestLocalScore) {
                $bestLocalScore = $score;
                $bestLocal = $kmer;
            }
        }
        if ($bestLocal) $selected[$removeIndex] = $bestLocal;
        $cons = consensus_from_motifs($selected);
        [$cov, $occ] = coverage_score($sequences, $cons, 1);
        $score = $cov * 100 + $occ;
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestConsensus = $cons;
        }
    }
    return result_pack([
        'algorithm' => 'Gibbs Motif Sampler',
        'motif' => $bestConsensus,
        'score' => $bestScore,
        'sequences' => $sequences,
        'maxMismatch' => 1,
        'notes' => 'A stochastic search algorithm that avoids local optima through randomized iterative refinement of the motif profile.'
    ]);
}

// 3. MEME simplified with EM-like PWM refinement
function algo_meme_simplified($sequences, $k, $iterations = 20) {
    $counts = count_exact_kmers($sequences, $k);
    $seed = array_key_first($counts);
    $motifs = [$seed];
    $pwm = build_pwm($motifs);

    for ($it = 0; $it < $iterations; $it++) {
        $motifs = [];
        foreach ($sequences as $seq) {
            $best = '';
            $bestScore = -1;
            foreach (get_kmers($seq, $k) as $kmer) {
                $score = score_kmer_pwm($kmer, $pwm);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $kmer;
                }
            }
            if ($best !== '') $motifs[] = $best;
        }
        $pwm = build_pwm($motifs);
    }

    $cons = consensus_from_motifs($motifs);
    [$cov, $occ] = coverage_score($sequences, $cons, 1);
    return result_pack([
        'algorithm' => 'MEME',
        'motif' => $cons,
        'score' => $cov * 100 + $occ,
        'sequences' => $sequences,
        'maxMismatch' => 1,
        'notes' => 'Employs a simplified Expectation-Maximization logic to maximize the statistical likelihood of the motif within the sequences.'
    ]);
}

// 4. Exhaustive Pattern-Driven Algorithm
function algo_exhaustive_pattern_driven($sequences, $k) {
    $best = '';
    $bestScore = PHP_INT_MAX;
    foreach (all_unique_kmers($sequences, $k) as $candidate) {
        $totalDistance = 0;
        foreach ($sequences as $seq) {
            $minDist = PHP_INT_MAX;
            foreach (get_kmers($seq, $k) as $kmer) {
                $minDist = min($minDist, hamming_distance($candidate, $kmer));
            }
            $totalDistance += $minDist;
        }
        if ($totalDistance < $bestScore) {
            $bestScore = $totalDistance;
            $best = $candidate;
        }
    }
    return result_pack([
        'algorithm' => 'Exhaustive Pattern-Driven',
        'motif' => $best,
        'score' => 1000 - $bestScore,
        'sequences' => $sequences,
        'maxMismatch' => 1,
        'notes' => 'Systematically evaluates the search space to solve the Median String Problem by minimizing total Hamming distance.'
    ]);
}

// 5. Sample-Driven Approach
function algo_sample_driven($sequences, $k) {
    $best = '';
    $bestScore = -1;
    foreach (all_unique_kmers($sequences, $k) as $candidate) {
        [$covered, $occ] = coverage_score($sequences, $candidate, 0);
        $score = $covered * 100 + $occ;
        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $candidate;
        }
    }
    return result_pack([
        'algorithm' => 'Sample-Driven',
        'motif' => $best,
        'score' => $bestScore,
        'sequences' => $sequences,
        'maxMismatch' => 0,
        'notes' => 'A combinatorial heuristic that optimizes discovery speed by sampling candidate k-mers directly from the input.'
    ]);
}

// 6. Extended Sample-Driven Algorithm
function algo_extended_sample_driven($sequences, $k, $maxMismatch = 1) {
    $best = '';
    $bestScore = -1;
    foreach (all_unique_kmers($sequences, $k) as $candidate) {
        [$covered, $occ] = coverage_score($sequences, $candidate, $maxMismatch);
        $score = $covered * 100 + $occ;
        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $candidate;
        }
    }
    return result_pack([
        'algorithm' => 'Extended Sample-Driven',
        'motif' => $best,
        'score' => $bestScore,
        'sequences' => $sequences,
        'maxMismatch' => $maxMismatch,
        'notes' => 'An approximate string-matching approach that expands the search space by allowing for specific mismatch thresholds (d).'
    ]);
}

// 7. Suffix Tree-Based Algorithm simplified as repeated substring indexing
function algo_suffix_tree_based($sequences, $k) {
    $counts = count_exact_kmers($sequences, $k);
    $best = array_key_first($counts) ?? '';
    $score = $counts[$best] ?? 0;
    return result_pack([
        'algorithm' => 'Suffix Tree-Based',
        'motif' => $best,
        'score' => $score,
        'sequences' => $sequences,
        'maxMismatch' => 0,
        'notes' => 'Utilizes a frequency-based indexing approach to identify highly repeated substrings in linear time.'
    ]);
}

// 8. Graph-Based Method simplified with k-mer similarity network degree
function algo_graph_based($sequences, $k, $maxMismatch = 1) {
    $nodes = [];
    foreach ($sequences as $si => $seq) {
        foreach (get_kmers($seq, $k) as $kmer) $nodes[] = ['seq' => $si, 'word' => $kmer];
    }
    $degree = [];
    $limit = min(count($nodes), 600); // prevent slow demo runs
    for ($i = 0; $i < $limit; $i++) {
        for ($j = $i + 1; $j < $limit; $j++) {
            if ($nodes[$i]['seq'] === $nodes[$j]['seq']) continue;
            if (hamming_distance($nodes[$i]['word'], $nodes[$j]['word']) <= $maxMismatch) {
                $degree[$nodes[$i]['word']] = ($degree[$nodes[$i]['word']] ?? 0) + 1;
                $degree[$nodes[$j]['word']] = ($degree[$nodes[$j]['word']] ?? 0) + 1;
            }
        }
    }
    arsort($degree);
    $best = array_key_first($degree) ?? (array_key_first(count_exact_kmers($sequences, $k)) ?? '');
    return result_pack([
        'algorithm' => 'Graph-Based',
        'motif' => $best,
        'score' => $degree[$best] ?? 0,
        'sequences' => $sequences,
        'maxMismatch' => $maxMismatch,
        'notes' => 'Identifies conserved motifs by modeling k-mer overlaps as a similarity network and detecting dense subgraphs (cliques).'
    ]);
}

// 9. REDUCE Algorithm simplified using expression correlation when available
function algo_reduce($sequences, $k, $expressions = []) {
    // $hasExpr = count(array_filter($expressions, fn($v) => $v !== null)) === count($sequences) && count($sequences) > 1;
    $hasExpr = count(array_filter($expressions, function($v) {
        return $v !== null;
    })) === count($sequences) && count($sequences) > 1;

    if (!$hasExpr) {
        $counts = count_exact_kmers($sequences, $k);
        $best = array_key_first($counts) ?? '';
        return result_pack([
            'algorithm' => 'REDUCE',
            'motif' => $best,
            'score' => $counts[$best] ?? 0,
            'sequences' => $sequences,
            'maxMismatch' => 0,
            'notes' => 'Correlates motif frequency with biological context (Fallback: uses statistical overrepresentation).'
        ]);
    }

    $best = '';
    $bestScore = -1;
    foreach (all_unique_kmers($sequences, $k) as $candidate) {
        $motifCounts = [];
        foreach ($sequences as $seq) {
            $c = 0;
            foreach (get_kmers($seq, $k) as $kmer) if ($kmer === $candidate) $c++;
            $motifCounts[] = $c;
        }
        $score = abs(pearson_correlation($motifCounts, $expressions));
        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $candidate;
        }
    }
    return result_pack([
        'algorithm' => 'REDUCE',
        'motif' => $best,
        'score' => $bestScore,
        'sequences' => $sequences,
        'maxMismatch' => 0,
        'notes' => 'A comparative method that identifies motifs by correlating sequence overrepresentation with biological context (e.g., expression levels).'
    ]);
}

// 10. Phylogenetic Footprinting simplified as conserved k-mer discovery
function algo_phylogenetic_footprinting($sequences, $k, $maxMismatch = 1) {
    $best = '';
    $bestScore = -1;
    foreach (all_unique_kmers($sequences, $k) as $candidate) {
        [$covered, $occ] = coverage_score($sequences, $candidate, $maxMismatch);
        $score = $covered * 100 + $occ;
        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $candidate;
        }
    }
    return result_pack([
        'algorithm' => 'Phylogenetic Footprinting',
        'motif' => $best,
        'score' => $bestScore,
        'sequences' => $sequences,
        'maxMismatch' => $maxMismatch,
        'notes' => 'Detects functional regulatory elements by identifying patterns conserved across homologous or related sequences.'
    ]);
}

function motif_voter($algorithmResults, $sequences) {
    $votes = [];
    foreach ($algorithmResults as $res) {
        $motif = $res['motif'];
        if ($motif === '') continue;
        $votes[$motif] = ($votes[$motif] ?? 0) + 1;
    }
    arsort($votes);
    $topMotif = array_key_first($votes) ?? '';

    // Tie or diversity handling: consensus of algorithm output motifs.
    //$outputMotifs = array_map(fn($r) => $r['motif'], $algorithmResults);
    $outputMotifs = array_map(function($r) {
        return $r['motif'];
    }, $algorithmResults);

    $consensus = consensus_from_motifs($outputMotifs);
    if (($votes[$topMotif] ?? 0) <= 1 && $consensus !== '') $topMotif = $consensus;

    [$covered, $occ] = coverage_score($sequences, $topMotif, 1);
    return [
        'algorithm' => 'MotifVoter',
        'motif' => $topMotif,
        'votes' => $votes,
        'covered' => $covered,
        'occurrences' => $occ,
        'inputMotifs' => $outputMotifs
    ];
}