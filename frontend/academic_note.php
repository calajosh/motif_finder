<div class="academic-note">

    <h4>Proposed Confidence Metric</h4>

    <p>
        Since the implemented motif-finding algorithms use different
        internal scoring systems, a custom confidence metric was proposed
        to provide a unified comparative evaluation framework.
    </p>

    <p>
        The metric incorporates:
        <strong>consensus similarity</strong>,
        <strong>sequence coverage</strong>,
        <strong>normalized relative score</strong>,
        and <strong>runtime efficiency</strong>.
    </p>

    <div class="formula-box">
        C = (S × 0.35) + (Cov × 0.30) + (R × 0.25) − (P × 0.10)
    </div>

    <div class="formula-legend">

        <div class="formula-card card-blue">
            <strong>S</strong>
            <span>=</span>
            Similarity to MotifVoter
        </div>

        <div class="formula-card card-green">
            <strong>Cov</strong>
            <span>=</span>
            Coverage Percentage
        </div>

        <div class="formula-card card-orange">
            <strong>R</strong>
            <span>=</span>
            Relative Internal Score
        </div>

        <div class="formula-card card-red">
            <strong>P</strong>
            <span>=</span>
            Runtime Penalty
        </div>

    </div>

    <?php require_once __DIR__ . '/weight.php'; ?>
    
</div>