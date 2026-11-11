<?php
// JSON endpoints powering the chained (LGA -> Ward -> Polling unit) select boxes.
require __DIR__ . '/inc/db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$lga  = (int) ($_GET['lga_id'] ?? 0);
$ward = (int) ($_GET['ward_id'] ?? 0); // ward.uniqueid

try {
    switch ($type) {
        case 'wards':
            // with_units=1 hides wards that have no polling units (used on the results page)
            $data = query(
                'SELECT w.uniqueid AS id, w.ward_name AS name,
                        (SELECT COUNT(*) FROM polling_unit pu WHERE ' . unit_in_ward() . ') AS units
                   FROM ward w
                  WHERE w.lga_id = ?
                  ORDER BY w.ward_name', [$lga]);
            if (!empty($_GET['with_units'])) {
                $data = array_values(array_filter($data, fn($w) => $w['units'] > 0));
            }
            break;
        case 'units':
            $data = query(
                "SELECT pu.uniqueid AS id,
                        TRIM(COALESCE(NULLIF(pu.polling_unit_name, ''), pu.polling_unit_number, CONCAT('Unit #', pu.uniqueid))) AS name,
                        pu.polling_unit_number AS number,
                        (SELECT COUNT(*) FROM announced_pu_results r WHERE r.polling_unit_uniqueid = CAST(pu.uniqueid AS CHAR)) AS results
                   FROM polling_unit pu
                   JOIN ward w ON " . unit_in_ward() . "
                  WHERE w.lga_id = ? AND w.uniqueid = ?
                  ORDER BY pu.polling_unit_name", [$lga, $ward]);
            break;
        default:
            http_response_code(400);
            $data = ['error' => 'Unknown type'];
    }
    echo json_encode($data);
} catch (Throwable $ex) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
