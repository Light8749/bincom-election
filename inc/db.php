<?php
function config(string $key) {
    static $cfg = null;
    $cfg ??= require __DIR__ . '/../config.php';
    return $cfg[$key];
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(config('dsn'), config('user'), config('pass'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function query(string $sql, array $params = []): array {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function e($v): string {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

/** LGAs in the state. With $withUnits, only LGAs that have at least one polling unit. */
function lgas(bool $withUnits = false): array {
    $extra = $withUnits ? ' AND EXISTS (SELECT 1 FROM polling_unit pu WHERE pu.lga_id = lga.lga_id AND pu.lga_id <> 0)' : '';
    return query('SELECT lga_id, lga_name FROM lga WHERE state_id = ?' . $extra . ' ORDER BY lga_name', [config('state_id')]);
}

function parties(): array {
    return query('SELECT partyid, partyname FROM party ORDER BY id');
}

/**
 * SQL condition: polling unit $pu belongs to ward $w.
 * In bincom_test some wards have ward_id = 0, so polling_unit.uniquewardid (= ward.uniqueid) is used first;
 * (lga_id, ward_id) is only a fallback when uniquewardid doesn't point at a ward in that LGA.
 * Wards are identified everywhere by ward.uniqueid, because ward_id repeats across LGAs.
 */
function unit_in_ward(string $pu = 'pu', string $w = 'w'): string {
    return "($pu.lga_id = $w.lga_id AND ($pu.uniquewardid = $w.uniqueid OR ($w.ward_id <> 0 AND $pu.ward_id = $w.ward_id
            AND NOT EXISTS (SELECT 1 FROM ward wx WHERE wx.uniqueid = $pu.uniquewardid AND wx.lga_id = $pu.lga_id))))";
}

/** Party abbreviation as stored in announced_pu_results (column is CHAR(4), e.g. LABOUR -> LABO). */
function party_abbr(string $partyid): string {
    return strtoupper(substr(trim($partyid), 0, 4));
}
