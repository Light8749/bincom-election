<?php
/** Renders a party => score table with share bars. Expects $rows: [['party'=>..,'score'=>..], ...] */
function results_table(array $rows, ?array $compare = null, string $compareLabel = ''): void {
    $total = array_sum(array_column($rows, 'score'));
    $max = $rows ? max(array_column($rows, 'score')) : 0;
    echo '<table class="results"><thead><tr><th>Party</th><th class="num">Score</th><th class="share">Share</th>';
    if ($compare !== null) echo '<th class="num">' . e($compareLabel) . '</th><th class="num">Difference</th>';
    echo '</tr></thead><tbody>';
    foreach ($rows as $r) {
        $pct = $total ? $r['score'] / $total * 100 : 0;
        $lead = $r['score'] == $max && $max > 0 ? ' lead' : '';
        echo '<tr class="' . trim($lead) . '"><td><span class="party">' . e($r['party']) . '</span></td>';
        echo '<td class="num">' . number_format($r['score']) . '</td>';
        echo '<td class="share"><div class="bar"><i style="width:' . round($pct, 1) . '%"></i></div><small>' . number_format($pct, 1) . '%</small></td>';
        if ($compare !== null) {
            $c = $compare[$r['party']] ?? null;
            echo '<td class="num">' . ($c === null ? '—' : number_format($c)) . '</td>';
            $d = $c === null ? null : $r['score'] - $c;
            echo '<td class="num ' . ($d ? 'diff' : '') . '">' . ($d === null ? '—' : ($d > 0 ? '+' : '') . number_format($d)) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody><tfoot><tr><th>Total</th><th class="num">' . number_format($total) . '</th><th></th>';
    if ($compare !== null) {
        $ct = array_sum($compare);
        echo '<th class="num">' . number_format($ct) . '</th><th class="num">' . ($total - $ct > 0 ? '+' : '') . number_format($total - $ct) . '</th>';
    }
    echo '</tr></tfoot></table>';
}
