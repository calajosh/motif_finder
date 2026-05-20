<?php $sample = "holaaaaa";
/**
 * Motif Finding Helper Functions
 */
if (!function_exists('array_key_first')) {
    function array_key_first(array $array) {
        foreach ($array as $key => $value) {
            return $key;
        }
        return null;
    }
}

function calculate_accuracy($predicted, $expected) {

    $matches = 0;
    $length = min(strlen($predicted), strlen($expected));

    for ($i = 0; $i < $length; $i++) {
        if ($predicted[$i] === $expected[$i]) {
            $matches++;
        }
    }

    return ($matches / strlen($expected)) * 100;
}

function calculate_similarity($predicted, $expected) {

    $predicted = strtoupper(trim($predicted));
    $expected  = strtoupper(trim($expected));

    if ($predicted === '' || $expected === '') {
        return 0;
    }

    $maxLen = max(strlen($predicted), strlen($expected));
    $matches = 0;

    for ($i = 0; $i < $maxLen; $i++) {

        $p = $predicted[$i] ?? '-';
        $e = $expected[$i] ?? '-';

        if ($p === $e) {
            $matches++;
        }
    }

    return round(($matches / $maxLen) * 100, 2);
}

function clean_sequence($seq) {
    return preg_replace('/[^ACGT]/', '', strtoupper(trim($seq)));
}

function parse_sequences_from_text($text) {
    $lines = preg_split('/\R+/', trim($text));
    $seqs = [];
    foreach ($lines as $line) {
        $clean = clean_sequence($line);
        if ($clean !== '') $seqs[] = $clean;
    }
    return $seqs;
}

function parse_csv_sequences($filePath) {
    $sequences = [];
    $expressions = [];
    $ids = [];

    if (!file_exists($filePath)) return [$sequences, $expressions, $ids];

    $handle = fopen($filePath, 'r');
    if (!$handle) return [$sequences, $expressions, $ids];

    $header = fgetcsv($handle);
    if ($header === false) return [$sequences, $expressions, $ids];

    // $lower = array_map(fn($h) => strtolower(trim($h)), $header);
    $lower = array_map(function($h) {
        return strtolower(trim($h));
    }, $header);

    $seqCol = array_search('sequence', $lower);
    $idCol = array_search('id', $lower);
    $exprCol = array_search('expression', $lower);

    // If no sequence header exists, assume the first column contains sequence values.
    if ($seqCol === false) $seqCol = 0;

    while (($row = fgetcsv($handle)) !== false) {
        if (!isset($row[$seqCol])) continue;
        $seq = clean_sequence($row[$seqCol]);
        if ($seq === '') continue;

        $sequences[] = $seq;
        $ids[] = ($idCol !== false && isset($row[$idCol]) && trim($row[$idCol]) !== '')
            ? trim($row[$idCol])
            : 'seq' . count($sequences);
        $expressions[] = ($exprCol !== false && isset($row[$exprCol]) && is_numeric($row[$exprCol]))
            ? (float)$row[$exprCol]
            : null;
    }

    fclose($handle);
    return [$sequences, $expressions, $ids];
}

function get_kmers($sequence, $k) {
    $kmers = [];
    $n = strlen($sequence);
    if ($k <= 0 || $n < $k) return $kmers;
    for ($i = 0; $i <= $n - $k; $i++) {
        $kmers[] = substr($sequence, $i, $k);
    }
    return $kmers;
}

function hamming_distance($a, $b) {
    if (strlen($a) !== strlen($b)) return PHP_INT_MAX;
    $d = 0;
    for ($i = 0; $i < strlen($a); $i++) {
        if ($a[$i] !== $b[$i]) $d++;
    }
    return $d;
}

function all_unique_kmers($sequences, $k, $limit = 1500) {
    $set = [];
    foreach ($sequences as $seq) {
        foreach (get_kmers($seq, $k) as $kmer) {
            $set[$kmer] = true;
            if (count($set) >= $limit) return array_keys($set);
        }
    }
    return array_keys($set);
}

function count_exact_kmers($sequences, $k) {
    $counts = [];
    foreach ($sequences as $seq) {
        foreach (get_kmers($seq, $k) as $kmer) {
            $counts[$kmer] = ($counts[$kmer] ?? 0) + 1;
        }
    }
    arsort($counts);
    return $counts;
}

function coverage_score($sequences, $motif, $maxMismatch = 0) {
    $covered = 0;
    $occ = 0;
    $k = strlen($motif);
    foreach ($sequences as $seq) {
        $found = false;
        foreach (get_kmers($seq, $k) as $kmer) {
            if (hamming_distance($motif, $kmer) <= $maxMismatch) {
                $occ++;
                $found = true;
            }
        }
        if ($found) $covered++;
    }
    return [$covered, $occ];
}

function find_occurrences($sequences, $motif, $maxMismatch = 0) {
    $results = [];
    $k = strlen($motif);
    foreach ($sequences as $seqIndex => $seq) {
        $positions = [];
        for ($i = 0; $i <= strlen($seq) - $k; $i++) {
            $word = substr($seq, $i, $k);
            if (hamming_distance($motif, $word) <= $maxMismatch) {
                $positions[] = ['start' => $i, 'word' => $word];
            }
        }
        $results[$seqIndex] = $positions;
    }
    return $results;
}

function highlight_sequence($sequence, $motif, $maxMismatch = 0) {
    $k = strlen($motif);
    $out = '';
    $i = 0;
    while ($i < strlen($sequence)) {
        if ($i <= strlen($sequence) - $k) {
            $word = substr($sequence, $i, $k);
            if (hamming_distance($motif, $word) <= $maxMismatch) {
                $out .= '<mark>' . htmlspecialchars($word) . '</mark>';
                $i += $k;
                continue;
            }
        }
        $out .= htmlspecialchars($sequence[$i]);
        $i++;
    }
    return $out;
}

function build_pwm($motifs) {
    $motifs = array_values(array_filter($motifs));
    if (empty($motifs)) return [];
    $k = strlen($motifs[0]);
    $bases = ['A', 'C', 'G', 'T'];
    $pwm = [];
    foreach ($bases as $b) $pwm[$b] = array_fill(0, $k, 1); // pseudocount

    foreach ($motifs as $motif) {
        if (strlen($motif) !== $k) continue;
        for ($i = 0; $i < $k; $i++) {
            if (isset($pwm[$motif[$i]])) $pwm[$motif[$i]][$i]++;
        }
    }

    for ($i = 0; $i < $k; $i++) {
        $sum = 0;
        foreach ($bases as $b) $sum += $pwm[$b][$i];
        foreach ($bases as $b) $pwm[$b][$i] = $pwm[$b][$i] / $sum;
    }
    return $pwm;
}

function score_kmer_pwm($kmer, $pwm) {
    $score = 1.0;
    for ($i = 0; $i < strlen($kmer); $i++) {
        $base = $kmer[$i];
        $score *= $pwm[$base][$i] ?? 0.0001;
    }
    return $score;
}

function consensus_from_motifs($motifs) {
    $motifs = array_values(array_filter($motifs));
    if (empty($motifs)) return '';
    $k = strlen($motifs[0]);
    $bases = ['A', 'C', 'G', 'T'];
    $consensus = '';
    for ($i = 0; $i < $k; $i++) {
        $count = array_fill_keys($bases, 0);
        foreach ($motifs as $motif) {
            if (strlen($motif) === $k && isset($count[$motif[$i]])) $count[$motif[$i]]++;
        }
        arsort($count);
        $consensus .= array_key_first($count);
    }
    return $consensus;
}

function infer_expected_motif($sequences, $k) {
    $counts = count_exact_kmers($sequences, $k);
    return array_key_first($counts) ?? '';
}

function pearson_correlation($x, $y) {
    $n = count($x);
    if ($n === 0 || $n !== count($y)) return 0;
    $mx = array_sum($x) / $n;
    $my = array_sum($y) / $n;
    $num = $dx = $dy = 0;
    for ($i = 0; $i < $n; $i++) {
        $a = $x[$i] - $mx;
        $b = $y[$i] - $my;
        $num += $a * $b;
        $dx += $a * $a;
        $dy += $b * $b;
    }
    return ($dx == 0 || $dy == 0) ? 0 : $num / sqrt($dx * $dy);
}

function score_badge_class($score) {
    $score = (float)$score;

    if ($score >= 80) {
        return 'score-badge-excellent';
    }

    if ($score >= 60) {
        return 'score-badge-good';
    }

    if ($score >= 40) {
        return 'score-badge-moderate';
    }

    if ($score >= 20) {
        return 'score-badge-low';
    }

    return 'score-badge-very-low';
}

function runtime_class($milliseconds) {
    $ms = (float)$milliseconds;

    if ($ms < 1000) {
        return 'runtime-badge-ms';
    }

    $seconds = $ms / 1000;

    if ($seconds < 60) {
        return 'runtime-badge-sec';
    }

    $minutes = $seconds / 60;

    if ($minutes < 60) {
        return 'runtime-badge-min';
    }

    return 'runtime-badge-hr';
}

function format_runtime($milliseconds) {
    $ms = (float)$milliseconds;

    if ($ms < 1000) {
        return round($ms, 2) . ' ms';
    }

    $seconds = $ms / 1000;

    if ($seconds < 60) {
        return round($seconds, 2) . ' sec';
    }

    $minutes = $seconds / 60;

    if ($minutes < 60) {
        return round($minutes, 2) . ' min';
    }

    $hours = $minutes / 60;

    return round($hours, 2) . ' hr';
}

function run_algorithm_with_time($callback) {
    $start = microtime(true);
    $result = call_user_func($callback);
    $end = microtime(true);

    $result['runtime_ms'] = round(($end - $start) * 1000, 4);
    $result['runtime_sec'] = round($end - $start, 6);

    return $result;
}

function run_all_algorithms($sequences, $k, $expressions = []) {
    return [
        run_algorithm_with_time(function() use ($sequences, $k) {
            return algo_pwm_scanning($sequences, $k);
        }),
        run_algorithm_with_time(function() use ($sequences, $k) {
            return algo_gibbs_sampler($sequences, $k);
        }),
        run_algorithm_with_time(function() use ($sequences, $k) {
            return algo_meme_simplified($sequences, $k);
        }),
        run_algorithm_with_time(function() use ($sequences, $k) {
            return algo_exhaustive_pattern_driven($sequences, $k);
        }),
        run_algorithm_with_time(function() use ($sequences, $k) {
            return algo_sample_driven($sequences, $k);
        }),
        run_algorithm_with_time(function() use ($sequences, $k) {
            return algo_extended_sample_driven($sequences, $k);
        }),
        run_algorithm_with_time(function() use ($sequences, $k) {
            return algo_suffix_tree_based($sequences, $k);
        }),
        run_algorithm_with_time(function() use ($sequences, $k) {
            return algo_graph_based($sequences, $k);
        }),
        run_algorithm_with_time(function() use ($sequences, $k, $expressions) {
            return algo_reduce($sequences, $k, $expressions);
        }),
        run_algorithm_with_time(function() use ($sequences, $k) {
            return algo_phylogenetic_footprinting($sequences, $k);
        })
    ];
}

function result_pack($config = []) {
    $algorithm = $config['algorithm'] ?? 'Unknown Algorithm';
    $motif = $config['motif'] ?? '';
    $score = $config['score'] ?? 0;
    $sequences = $config['sequences'] ?? [];
    $maxMismatch = $config['maxMismatch'] ?? 0;
    $notes = $config['notes'] ?? '';

    // If no expected motif is passed, infer it from the most frequent k-mer
    // using the same length as the predicted motif.
    $expectedMotif = $config['expectedMotif'] ?? '';
    if ($expectedMotif === '' && $motif !== '' && !empty($sequences)) {
        $expectedMotif = infer_expected_motif($sequences, strlen($motif));
    }

    [$covered, $occ] = coverage_score($sequences, $motif, $maxMismatch);

    return [
        'algorithm' => $algorithm,
        'motif' => $motif,
        'expectedMotif' => $expectedMotif,
        'score' => round((float)$score, 4),
        'accuracy' => calculate_accuracy($motif, $expectedMotif),
        'similarity' => calculate_similarity($motif, $expectedMotif),
        'covered' => $covered,
        'occurrences' => $occ,
        'maxMismatch' => $maxMismatch,
        'notes' => $notes
    ];
}