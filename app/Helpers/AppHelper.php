<?php

if (!function_exists('rupiah')) {
    function rupiah(float|int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('status_badge')) {
    function status_badge(string $status, array $statusMap): string
    {
        $badge = $statusMap[$status] ?? ['label' => $status, 'color' => 'secondary'];
        return '<span class="badge bg-' . $badge['color'] . '">' . $badge['label'] . '</span>';
    }
}
