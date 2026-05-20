<?php

function render_sequence_logo($motifs) {
    $motifs = array_values(array_filter($motifs));

    if (empty($motifs)) {
        return '<p>No motifs available for sequence logo.</p>';
    }

    $pwm = [];
    $k = strlen($motifs[0]);
    $bases = ['A', 'C', 'G', 'T'];

    for ($i = 0; $i < $k; $i++) {
        $counts = ['A' => 0, 'C' => 0, 'G' => 0, 'T' => 0];

        foreach ($motifs as $motif) {
            if (strlen($motif) === $k && isset($counts[$motif[$i]])) {
                $counts[$motif[$i]]++;
            }
        }

        $pwm[] = $counts;
    }

    $pwmJson = json_encode($pwm);

    return '
    <div class="canvas-container">
        <h3 class="canvas-title">Sequence Logo Visualization</h3>

        <canvas id="logoCanvas"></canvas>

        <div class="logo-caption-box">
            <p class="logo-caption">
                Sequence logos visualize nucleotide conservation using information content measured in bits.
            </p>
        </div>

        <div class="legend">
            <span class="legend-item"><span class="legend-color" style="background:#43a047"></span> A</span>
            <span class="legend-item"><span class="legend-color" style="background:#1e88e5"></span> C</span>
            <span class="legend-item"><span class="legend-color" style="background:#fbc02d"></span> G</span>
            <span class="legend-item"><span class="legend-color" style="background:#e53935"></span> T</span>
        </div>

        <div id="tooltip" class="tooltip"></div>
    </div>

    <script>
        const colors = { A: "#43a047", C: "#1e88e5", G: "#fbc02d", T: "#e53935" };
        const letters = ["A", "C", "G", "T"];
        const pwm = ' . $pwmJson . ';

        const scaleMultiplier = 150;

        const motifLength = pwm.length;
        const baseY = 390;
        const maxBits = 2.0;
        const leftAxisX = 70;
        const baseX = 80;

        const minColWidth = 120;
        const maxColWidth = 240;
        let colWidth = Math.max(minColWidth, Math.min(maxColWidth, Math.floor(900 / motifLength)));

        const canvasWidth = baseX + (motifLength + 1) * colWidth + 90;
        const canvasHeight = 520;

        const canvas = document.getElementById("logoCanvas");
        canvas.width = canvasWidth;
        canvas.height = canvasHeight;

        canvas.style.width = "100%";
        canvas.style.maxWidth = canvasWidth + "px";
        canvas.style.height = "auto";

        const ctx = canvas.getContext("2d");
        const tooltip = document.getElementById("tooltip");

        const isDark = document.body.classList.contains("dark-mode");

        const axisColor = isDark ? "#cbd5e1" : "axisColor";
        const titleColor = isDark ? "#f8fafc" : "titleColor";
        const lineColor = isDark ? "rgba(255,255,255,0.22)" : "lineColor";
        const axisLineColor = isDark ? "rgba(255,255,255,0.35)" : "#bbb";
                
        // Move tooltip outside canvas container
        if (tooltip.parentElement !== document.body) {
            document.body.appendChild(tooltip);
        }

        function log2(x) {
            return Math.log(x) / Math.log(2);
        }

        function drawYAxis() {
            ctx.save();

            ctx.beginPath();
            ctx.moveTo(leftAxisX, 35);
            ctx.lineTo(leftAxisX, baseY);
            ctx.strokeStyle = axisLineColor;
            ctx.lineWidth = 1.5;
            ctx.stroke();

            ctx.beginPath();
            ctx.moveTo(leftAxisX, baseY);
            ctx.lineTo(canvas.width - 45, baseY);
            ctx.strokeStyle = axisLineColor;
            ctx.stroke();

            ctx.fillStyle = axisColor;
            ctx.font = "30px Segoe UI, Arial";
            ctx.fillText("Bits", 15, 35);

            for (let i = 0; i <= maxBits; i++) {
                let y = baseY - i * scaleMultiplier;

                ctx.strokeStyle = lineColor;
                ctx.beginPath();
                ctx.moveTo(leftAxisX, y);
                ctx.lineTo(canvas.width - 45, y);
                ctx.stroke();

                ctx.strokeStyle = axisLineColor;
                ctx.beginPath();
                ctx.moveTo(leftAxisX - 8, y);
                ctx.lineTo(leftAxisX, y);
                ctx.stroke();

                ctx.fillStyle = axisColor;
                ctx.font = "18px Segoe UI, Arial";
                ctx.textAlign = "right";
                ctx.fillText(i.toString(), leftAxisX - 18, y + 6);
            }

            ctx.restore();
        }

        let letterBoxes = [];

        function drawBases() {
            letterBoxes = [];

            pwm.forEach(function(position, idx) {
                const total = letters.reduce(function(sum, l) {
                    return sum + position[l];
                }, 0);

                const freqs = letters.map(function(l) {
                    return {
                        base: l,
                        freq: position[l] / total
                    };
                });

                let entropy = freqs.reduce(function(H, item) {
                    return item.freq > 0 ? H - item.freq * log2(item.freq) : H;
                }, 0);

                const info = maxBits - entropy;

                const scaled = freqs.map(function(f) {
                    return {
                        base: f.base,
                        freq: f.freq,
                        height: f.freq * info
                    };
                }).sort(function(a, b) {
                    return a.height - b.height;
                });

                let x = baseX + (idx + 1) * colWidth;
                let yOffset = baseY;

                scaled.forEach(function(item) {
                    let pxHeight = item.height * scaleMultiplier;

                    // Do not show bases with zero frequency
                    if (item.freq <= 0 || item.height <= 0) {
                        return;
                    }

                    // Do not force tiny bases to appear
                    if (pxHeight < 4) {
                        return;
                    }

                    const fontSize = pxHeight * 1.1;

                    ctx.fillStyle = colors[item.base];
                    ctx.font = "900 " + fontSize + "px Segoe UI, Arial";
                    ctx.textAlign = "center";
                    ctx.textBaseline = "bottom";

                    ctx.save();
                    ctx.shadowColor = "rgba(0,0,0,0.10)";
                    ctx.shadowBlur = 2;
                    ctx.fillText(item.base, x, yOffset);
                    ctx.restore();

                    letterBoxes.push({
                        x: x - colWidth / 2,
                        y: yOffset - pxHeight,
                        w: colWidth,
                        h: pxHeight,
                        base: item.base,
                        pos: idx + 1,
                        info: info.toFixed(2),
                        freq: item.freq.toFixed(2)
                    });

                    yOffset -= pxHeight;
                });

                ctx.fillStyle = axisColor;
                ctx.font = "18px Segoe UI, Arial";
                ctx.textAlign = "center";
                ctx.textBaseline = "top";
                ctx.fillText((idx + 1).toString(), x, baseY + 18);
            });

            ctx.fillStyle = titleColor;
            ctx.font = "24px Segoe UI, Arial";

            ctx.textAlign = "left";
            ctx.fillText("5′", leftAxisX + 15, baseY + 18);

            ctx.textAlign = "right";
            ctx.fillText("3′", canvas.width - 25, baseY + 18);

            ctx.textAlign = "center";
            ctx.font = "30px Segoe UI, Arial";
            ctx.fillText("Position", canvas.width / 2, baseY + 65);
        }

        function redraw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            drawYAxis();
            drawBases();
        }

        canvas.addEventListener("mousemove", function(e) {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;

            const x = (e.clientX - rect.left) * scaleX;
            const y = (e.clientY - rect.top) * scaleY;

            let found = false;

            for (let i = 0; i < letterBoxes.length; i++) {
                const box = letterBoxes[i];

                if (x >= box.x && x <= box.x + box.w && y >= box.y && y <= box.y + box.h) {
                    tooltip.style.display = "block";
                    const tooltipOffset = 14;

                    let tooltipX = e.clientX + tooltipOffset;
                    let tooltipY = e.clientY + tooltipOffset;

                    const tooltipWidth = 180;
                    const tooltipHeight = 110;

                    if (tooltipX + tooltipWidth > window.innerWidth) {
                        tooltipX = e.clientX - tooltipWidth - 12;
                    }

                    if (tooltipY + tooltipHeight > window.innerHeight) {
                        tooltipY = e.clientY - tooltipHeight - 12;
                    }

                    tooltip.style.left = tooltipX + "px";
                    tooltip.style.top  = tooltipY + "px";
                    tooltip.style.opacity = "1";
                    const color = colors[box.base];

                    tooltip.innerHTML =
                        "<span style=\"color:" + color + "; font-weight:700;\">Base:</span> " + box.base + "<br>" +
                        "<span style=\"color:" + color + "; font-weight:700;\">Position:</span> " + box.pos + "<br>" +
                        "<span style=\"color:" + color + "; font-weight:700;\">Info:</span> " + box.info + " bits<br>" +
                        "<span style=\"color:" + color + "; font-weight:700;\">Freq:</span> " + box.freq;
                        
                    found = true;
                    break;
                }
            }

            if (!found) {
                tooltip.style.display = "none";
            }
        });

        canvas.addEventListener("mouseleave", function() {
            tooltip.style.display = "none";
        });

        redraw();
    </script>';
}