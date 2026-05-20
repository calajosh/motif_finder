<div class="weight-explanation">

    <h4>Weight Distribution Rationale</h4>

    <p>
        The confidence metric uses weighted components to balance
        motif agreement, biological support, relative algorithm quality,
        and computational efficiency.
    </p>

    <div class="weight-grid">

        <div class="weight-card card-blue">
            <div class="weight-percent">35%</div>
            <div class="weight-title">Similarity to MotifVoter</div>

            <p>
                Assigned the highest weight because agreement with the
                MotifVoter consensus indicates strong consistency
                among multiple motif-finding algorithms.
            </p>
        </div>

        <div class="weight-card card-green">
            <div class="weight-percent">30%</div>
            <div class="weight-title">Coverage Percentage</div>

            <p>
                Coverage reflects how many sequences support the motif,
                making it an important indicator of motif conservation
                and biological relevance.
            </p>
        </div>

        <div class="weight-card card-orange">
            <div class="weight-percent">25%</div>
            <div class="weight-title">Normalized Relative Score</div>

            <p>
                Relative scores provide cross-algorithm comparison by
                normalizing different internal scoring systems into
                a unified evaluation scale.
            </p>
        </div>

        <div class="weight-card card-red">
            <div class="weight-percent">10%</div>
            <div class="weight-title">Runtime Penalty</div>

            <p>
                Runtime efficiency was given a smaller weight since
                motif quality and sequence support are prioritized
                over execution speed in this prototype system.
            </p>
        </div>

    </div>

</div>