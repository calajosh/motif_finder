# Web-Based DNA Motif Finder and MotifVoter

This is an educational PHP prototype for DNA motif discovery. It accepts pasted DNA sequences or a CSV file, runs 10 simplified motif-finding algorithms, and sends the resulting motifs to MotifVoter.

## CSV Format

```csv
id,sequence,expression
seq1,GCACGCGGTATCGTTAGCTTGACAATGAAGACCCCCGCTCGACAGGAAT,2.1
seq2,GCATACTTTGACACTGACTTCGCTTCTTTAATGTTTAATGAAACATGCG,1.8
```

Required column: `sequence`  
Optional columns: `id`, `expression`

The `expression` column is used by the simplified REDUCE algorithm.

## Algorithms Included

1. PWM Scanning
2. Gibbs Motif Sampler
3. MEME Simplified
4. Exhaustive Pattern-Driven
5. Sample-Driven
6. Extended Sample-Driven
7. Suffix Tree-Based Simplified
8. Graph-Based Simplified
9. REDUCE Simplified
10. Phylogenetic Footprinting Simplified

Final integration: MotifVoter combines the outputs of the 10 algorithms.

## How to Run in XAMPP

1. Copy the `motif_finder` folder to `xampp/htdocs/`.
2. Start Apache from XAMPP Control Panel.
3. Open your browser and go to:

```text
http://localhost/motif_finder/
```

## Scope

This project is for demonstration, learning, and presentation purposes. It is not a replacement for full bioinformatics tools.
