<?php
// Deze endpoint retourneert altijd JSON zodat de frontend de response eenduidig kan verwerken.
header('Content-Type: application/json');

// Database-instellingen voor de lokale MySQL-verbinding.
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "UrenRegistratieSysteem";
$dbport = 3307;

mysqli_report(MYSQLI_REPORT_OFF);
// Maak een nieuwe databaseverbinding aan met de opgegeven configuratie.
$conn = @new mysqli($servername, $username, $password, $dbname, $dbport);

// Stop direct als de database niet bereikbaar is om vervolgerrors te voorkomen.
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'error' => 'Database niet bereikbaar',
        'details' => $conn->connect_error,
        'code' => $conn->connect_errno
    ]);
    exit;
}

function isNumericColumnType(string $columnType): bool {
    return preg_match('/(int|decimal|float|double|real|numeric|bit)/i', $columnType) === 1;
}

function getAllTables(mysqli $conn): array {
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $row[0] ?? '');
            if ($tableName !== '') {
                $tables[] = $tableName;
            }
        }
    }
    return $tables;
}

function getTableColumns(mysqli $conn, string $table): array {
    $columns = [];
    $columnsResult = $conn->query("SHOW COLUMNS FROM `$table`");
    if ($columnsResult && $columnsResult->num_rows > 0) {
        while ($col = $columnsResult->fetch_assoc()) {
            $field = $col['Field'] ?? '';
            if ($field !== '') {
                $columns[$field] = $col;
            }
        }
    }
    return $columns;
}

function getPrimaryKeyColumn(mysqli $conn, string $table): ?string {
    $columns = getTableColumns($conn, $table);
    foreach ($columns as $columnName => $metadata) {
        if (($metadata['Key'] ?? '') === 'PRI') {
            return $columnName;
        }
    }
    return null;
}

function findColumnName(array $columns, array $candidates): ?string {
    $lowerMap = [];
    foreach (array_keys($columns) as $columnName) {
        $lowerMap[strtolower($columnName)] = $columnName;
    }

    foreach ($candidates as $candidate) {
        if (isset($lowerMap[$candidate])) {
            return $lowerMap[$candidate];
        }
    }

    return null;
}

function getGlobalNextId(mysqli $conn): int {
    $maxId = 0;
    $tables = getAllTables($conn);

    foreach ($tables as $table) {
        $primaryKeyColumn = getPrimaryKeyColumn($conn, $table);
        if ($primaryKeyColumn === null) {
            continue;
        }

        $columns = getTableColumns($conn, $table);
        if (!isset($columns[$primaryKeyColumn])) {
            continue;
        }

        $idColumn = $columns[$primaryKeyColumn];
        if (!isNumericColumnType((string)($idColumn['Type'] ?? ''))) {
            continue;
        }

        $result = $conn->query("SELECT MAX(CAST(`$primaryKeyColumn` AS UNSIGNED)) AS max_id FROM `$table`");
        if ($result) {
            $row = $result->fetch_assoc();
            $tableMax = isset($row['max_id']) ? (int)$row['max_id'] : 0;
            if ($tableMax > $maxId) {
                $maxId = $tableMax;
            }
        }
    }

    return $maxId + 1;
}

function getPersonIdentityFromRow(array $row): ?array {
    $rowLower = [];
    foreach ($row as $key => $value) {
        $rowLower[strtolower((string)$key)] = $value;
    }

    foreach (['werkmail', 'email', 'mail'] as $emailColumn) {
        if (!isset($rowLower[$emailColumn])) {
            continue;
        }

        $emailValue = strtolower(trim((string)$rowLower[$emailColumn]));
        if ($emailValue !== '') {
            return ['mode' => 'email', 'value' => $emailValue];
        }
    }

    $firstName = strtolower(trim((string)($rowLower['voornaam'] ?? '')));
    $middleName = strtolower(trim((string)($rowLower['tussenvoegsel'] ?? '')));
    $lastName = strtolower(trim((string)($rowLower['achternaam'] ?? '')));

    if ($firstName !== '' && $lastName !== '') {
        return [
            'mode' => 'name',
            'first' => $firstName,
            'middle' => $middleName,
            'last' => $lastName
        ];
    }

    return null;
}

function findExistingPersonId(mysqli $conn, string $currentTable, array $identity): ?int {
    $tables = getAllTables($conn);

    foreach ($tables as $table) {
        if ($table === $currentTable) {
            continue;
        }

        $primaryKeyColumn = getPrimaryKeyColumn($conn, $table);
        if ($primaryKeyColumn === null) {
            continue;
        }

        $columns = getTableColumns($conn, $table);
        if (!isset($columns[$primaryKeyColumn])) {
            continue;
        }

        if (($identity['mode'] ?? '') === 'email') {
            $emailColumn = findColumnName($columns, ['werkmail', 'email', 'mail']);
            if ($emailColumn === null) {
                continue;
            }

            $sql = "SELECT `$primaryKeyColumn` FROM `$table` WHERE LOWER(TRIM(`$emailColumn`)) = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                continue;
            }
            $emailValue = $identity['value'];
            $stmt->bind_param('s', $emailValue);
            $stmt->execute();
            $stmt->bind_result($foundId);
            if ($stmt->fetch()) {
                $stmt->close();
                return (int)$foundId;
            }
            $stmt->close();
            continue;
        }

        if (($identity['mode'] ?? '') !== 'name') {
            continue;
        }

        $firstColumn = findColumnName($columns, ['voornaam']);
        $middleColumn = findColumnName($columns, ['tussenvoegsel']);
        $lastColumn = findColumnName($columns, ['achternaam']);
        if ($firstColumn === null || $lastColumn === null) {
            continue;
        }

        if ($middleColumn !== null) {
            $sql = "SELECT `$primaryKeyColumn` FROM `$table` WHERE LOWER(TRIM(`$firstColumn`)) = ? AND LOWER(TRIM(`$middleColumn`)) = ? AND LOWER(TRIM(`$lastColumn`)) = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                continue;
            }
            $stmt->bind_param('sss', $identity['first'], $identity['middle'], $identity['last']);
        } else {
            $sql = "SELECT `$primaryKeyColumn` FROM `$table` WHERE LOWER(TRIM(`$firstColumn`)) = ? AND LOWER(TRIM(`$lastColumn`)) = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                continue;
            }
            $stmt->bind_param('ss', $identity['first'], $identity['last']);
        }

        $stmt->execute();
        $stmt->bind_result($foundId);
        if ($stmt->fetch()) {
            $stmt->close();
            return (int)$foundId;
        }
        $stmt->close();
    }

    return null;
}

function syncCurrentRowIdByEntity(mysqli $conn, string $table, string $idColumn, string $idValue): array {
    $primaryKeyColumn = getPrimaryKeyColumn($conn, $table);
    if ($primaryKeyColumn === null || $idColumn !== $primaryKeyColumn) {
        return ['success' => true];
    }

    $currentId = (int)$idValue;
    if ($currentId <= 0) {
        return ['success' => true];
    }

    $escapedIdValue = $conn->real_escape_string($idValue);
    $rowResult = $conn->query("SELECT * FROM `$table` WHERE `$idColumn` = '$escapedIdValue' LIMIT 1");
    $rowData = ($rowResult && $rowResult->num_rows > 0) ? $rowResult->fetch_assoc() : null;

    if (!$rowData) {
        return ['success' => true];
    }

    $identity = getPersonIdentityFromRow($rowData);
    if (!$identity) {
        return ['success' => true];
    }

    $existingId = findExistingPersonId($conn, $table, $identity);
    if (!$existingId || $existingId === $currentId) {
        return ['success' => true];
    }

    $updateIdStmt = $conn->prepare("UPDATE `$table` SET `$primaryKeyColumn` = ? WHERE `$primaryKeyColumn` = ?");
    if (!$updateIdStmt) {
        return ['success' => false, 'error' => 'Kon ID niet synchroniseren'];
    }

    $existingIdString = (string)$existingId;
    $currentIdString = (string)$currentId;
    $updateIdStmt->bind_param('ss', $existingIdString, $currentIdString);
    $ok = $updateIdStmt->execute();
    $error = $updateIdStmt->error;
    $updateIdStmt->close();

    if (!$ok) {
        return ['success' => false, 'error' => 'ID-synchronisatie mislukt: ' . $error];
    }

    return ['success' => true, 'synced_id' => $existingId];
}

function getLinkedPersonTableConfig(string $table): ?array {
    if ($table === 'medewerkers') {
        return [
            'sourceTable' => 'medewerkers',
            'sourcePk' => 'medewerker_ID',
            'targetTable' => 'werkzaamheden',
            'targetPk' => 'werkzaamheden_ID',
            'sharedColumns' => [
                'voornaam' => 'voornaam',
                'tussenvoegsel' => 'tussenvoegsel',
                'achternaam' => 'achternaam'
            ]
        ];
    }

    if ($table === 'werkzaamheden') {
        return [
            'sourceTable' => 'werkzaamheden',
            'sourcePk' => 'werkzaamheden_ID',
            'targetTable' => 'medewerkers',
            'targetPk' => 'medewerker_ID',
            'sharedColumns' => [
                'voornaam' => 'voornaam',
                'tussenvoegsel' => 'tussenvoegsel',
                'achternaam' => 'achternaam'
            ]
        ];
    }

    return null;
}

function updateLinkedPersonRow(mysqli $conn, string $table, string $column, string $value, int $id): array {
    $config = getLinkedPersonTableConfig($table);
    if (!$config) {
        return ['success' => true];
    }

    if (!isset($config['sharedColumns'][$column])) {
        return ['success' => true];
    }

    $targetColumn = $config['sharedColumns'][$column];
    $targetTable = $config['targetTable'];
    $targetPk = $config['targetPk'];
    $idString = (string)$id;

    $stmt = $conn->prepare("UPDATE `$targetTable` SET `$targetColumn` = ? WHERE `$targetPk` = ?");
    if (!$stmt) {
        return ['success' => false, 'error' => 'Kon gekoppelde rij niet updaten'];
    }

    $stmt->bind_param('ss', $value, $idString);
    $ok = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();

    if (!$ok) {
        return ['success' => false, 'error' => 'Koppeling update mislukt: ' . $error];
    }

    return ['success' => true];
}

function ensureLinkedPersonRowExists(mysqli $conn, string $table, int $id): array {
    $config = getLinkedPersonTableConfig($table);
    if (!$config || $table !== 'medewerkers') {
        return ['success' => true];
    }

    $sourceTable = $config['sourceTable'];
    $sourcePk = $config['sourcePk'];
    $targetTable = $config['targetTable'];
    $targetPk = $config['targetPk'];
    $idString = (string)$id;

    $checkStmt = $conn->prepare("SELECT 1 FROM `$targetTable` WHERE `$targetPk` = ? LIMIT 1");
    if (!$checkStmt) {
        return ['success' => false, 'error' => 'Kon gekoppelde rijcontrole niet doen'];
    }
    $checkStmt->bind_param('s', $idString);
    $checkStmt->execute();
    $checkStmt->store_result();
    $alreadyExists = $checkStmt->num_rows > 0;
    $checkStmt->close();

    if ($alreadyExists) {
        return ['success' => true];
    }

    $rowStmt = $conn->prepare("SELECT `voornaam`, `tussenvoegsel`, `achternaam` FROM `$sourceTable` WHERE `$sourcePk` = ? LIMIT 1");
    if (!$rowStmt) {
        return ['success' => false, 'error' => 'Kon medewerker niet uitlezen'];
    }
    $rowStmt->bind_param('s', $idString);
    $rowStmt->execute();
    $rowStmt->bind_result($voornaam, $tussenvoegsel, $achternaam);
    $hasRow = $rowStmt->fetch();
    $rowStmt->close();

    if (!$hasRow) {
        return ['success' => true];
    }

    $emptyHours = '0';
    $emptyTitle = '';
    $emptyDescription = '';
    $insertStmt = $conn->prepare("INSERT INTO `$targetTable` (`$targetPk`, `voornaam`, `tussenvoegsel`, `achternaam`, `gewerkte_uren`, `opdracht_titel`, `omschrijving_werkzaamheden`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$insertStmt) {
        return ['success' => false, 'error' => 'Kon gekoppelde werkzaamheden niet aanmaken'];
    }

    $insertStmt->bind_param('sssssss', $idString, $voornaam, $tussenvoegsel, $achternaam, $emptyHours, $emptyTitle, $emptyDescription);
    $ok = $insertStmt->execute();
    $error = $insertStmt->error;
    $insertStmt->close();

    if (!$ok) {
        return ['success' => false, 'error' => 'Aanmaken gekoppelde werkzaamheden mislukt: ' . $error];
    }

    return ['success' => true, 'linked_created' => true];
}

function deleteLinkedPersonRow(mysqli $conn, string $table, int $id): array {
    $config = getLinkedPersonTableConfig($table);
    if (!$config) {
        return ['success' => true];
    }

    $targetTable = $config['targetTable'];
    $targetPk = $config['targetPk'];
    $idString = (string)$id;

    $stmt = $conn->prepare("DELETE FROM `$targetTable` WHERE `$targetPk` = ?");
    if (!$stmt) {
        return ['success' => false, 'error' => 'Kon gekoppelde rij niet verwijderen'];
    }

    $stmt->bind_param('s', $idString);
    $ok = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();

    if (!$ok) {
        return ['success' => false, 'error' => 'Verwijderen gekoppelde rij mislukt: ' . $error];
    }

    return ['success' => true];
}

function resequenceGlobalIdsAfterDelete(mysqli $conn, int $deletedId): array {
    if ($deletedId <= 0) {
        return ['success' => true];
    }

    $tables = getAllTables($conn);
    $offset = 1000000;

    foreach ($tables as $table) {
        $primaryKeyColumn = getPrimaryKeyColumn($conn, $table);
        if ($primaryKeyColumn === null) {
            continue;
        }

        $columns = getTableColumns($conn, $table);
        $pkMeta = $columns[$primaryKeyColumn] ?? null;
        if (!$pkMeta || !isNumericColumnType((string)($pkMeta['Type'] ?? ''))) {
            continue;
        }

        $deletedIdString = (string)$deletedId;

        // Stap 1: schuif alle IDs boven de verwijderde waarde tijdelijk omhoog om unieke botsingen te voorkomen.
        $stmtUp = $conn->prepare("UPDATE `$table` SET `$primaryKeyColumn` = `$primaryKeyColumn` + $offset WHERE `$primaryKeyColumn` > ?");
        if (!$stmtUp) {
            return ['success' => false, 'error' => 'Kon resequencing niet voorbereiden (stap 1)'];
        }

        $stmtUp->bind_param('s', $deletedIdString);
        $okUp = $stmtUp->execute();
        $errUp = $stmtUp->error;
        $stmtUp->close();

        if (!$okUp) {
            return ['success' => false, 'error' => 'Resequencing mislukt (stap 1): ' . $errUp];
        }

        // Stap 2: breng alles terug met -1 netto effect.
        $threshold = $deletedId + $offset;
        $thresholdString = (string)$threshold;
        $shiftDown = $offset + 1;

        $stmtDown = $conn->prepare("UPDATE `$table` SET `$primaryKeyColumn` = `$primaryKeyColumn` - $shiftDown WHERE `$primaryKeyColumn` > ?");
        if (!$stmtDown) {
            return ['success' => false, 'error' => 'Kon resequencing niet voorbereiden (stap 2)'];
        }

        $stmtDown->bind_param('s', $thresholdString);
        $okDown = $stmtDown->execute();
        $errDown = $stmtDown->error;
        $stmtDown->close();

        if (!$okDown) {
            return ['success' => false, 'error' => 'Resequencing mislukt (stap 2): ' . $errDown];
        }
    }

    return ['success' => true];
}

function getEquivalentColumnCandidates(string $column): array {
    $columnLower = strtolower($column);
    $groups = [
        ['voornaam'],
        ['tussenvoegsel'],
        ['achternaam'],
        ['email', 'werkmail', 'mail'],
        ['bedrijfsnaam', 'klantnaam'],
        ['titel', 'opdracht_titel'],
        ['omschrijving', 'omschrijving_werkzaamheden']
    ];

    foreach ($groups as $group) {
        if (in_array($columnLower, $group, true)) {
            return $group;
        }
    }

    return [$columnLower];
}

function findMatchingColumnByCandidates(array $columns, array $candidates): ?string {
    $columnLookup = [];
    foreach (array_keys($columns) as $columnName) {
        $columnLookup[strtolower($columnName)] = $columnName;
    }

    foreach ($candidates as $candidate) {
        if (isset($columnLookup[$candidate])) {
            return $columnLookup[$candidate];
        }
    }

    return null;
}

function rowExistsByPrimaryKey(mysqli $conn, string $table, string $primaryKeyColumn, int $id): bool {
    $idString = (string)$id;
    $stmt = $conn->prepare("SELECT 1 FROM `$table` WHERE `$primaryKeyColumn` = ? LIMIT 1");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('s', $idString);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    return $exists;
}

function getPersonCoreFromRow(array $row): ?array {
    $rowLower = [];
    foreach ($row as $key => $value) {
        $rowLower[strtolower((string)$key)] = $value;
    }

    $voornaam = trim((string)($rowLower['voornaam'] ?? ''));
    $tussenvoegsel = trim((string)($rowLower['tussenvoegsel'] ?? ''));
    $achternaam = trim((string)($rowLower['achternaam'] ?? ''));

    if ($voornaam === '' || $achternaam === '') {
        return null;
    }

    return [
        'voornaam' => $voornaam,
        'tussenvoegsel' => $tussenvoegsel,
        'achternaam' => $achternaam
    ];
}

function getDefaultValueForColumn(array $columnMetadata): string {
    $columnType = strtolower((string)($columnMetadata['Type'] ?? ''));
    $defaultValue = $columnMetadata['Default'] ?? null;

    if ($defaultValue !== null && strtoupper((string)$defaultValue) !== 'CURRENT_TIMESTAMP') {
        return (string)$defaultValue;
    }

    if (strpos($columnType, 'int') !== false || strpos($columnType, 'decimal') !== false || strpos($columnType, 'float') !== false || strpos($columnType, 'double') !== false) {
        return '0';
    }

    if (strpos($columnType, 'datetime') !== false || strpos($columnType, 'timestamp') !== false) {
        return '1970-01-01 00:00:00';
    }

    if (strpos($columnType, 'date') !== false) {
        return '1970-01-01';
    }

    if (strpos($columnType, 'time') !== false) {
        return '00:00:00';
    }

    return '';
}

function insertPersonRowIfMissing(mysqli $conn, string $targetTable, string $targetPk, int $id, array $personCore): array {
    if (rowExistsByPrimaryKey($conn, $targetTable, $targetPk, $id)) {
        return ['success' => true];
    }

    $columns = getTableColumns($conn, $targetTable);
    if (findMatchingColumnByCandidates($columns, ['voornaam']) === null || findMatchingColumnByCandidates($columns, ['achternaam']) === null) {
        return ['success' => true];
    }

    $insertColumns = [];
    $insertValues = [];

    foreach ($columns as $columnName => $columnMetadata) {
        $isAutoIncrement = (($columnMetadata['Extra'] ?? '') === 'auto_increment');
        if ($isAutoIncrement) {
            continue;
        }

        $insertColumns[] = $columnName;

        if ($columnName === $targetPk) {
            $insertValues[] = (string)$id;
            continue;
        }

        $columnLower = strtolower($columnName);
        if (isset($personCore[$columnLower])) {
            $insertValues[] = (string)$personCore[$columnLower];
            continue;
        }

        $insertValues[] = getDefaultValueForColumn($columnMetadata);
    }

    if (empty($insertColumns)) {
        return ['success' => true];
    }

    $placeholders = implode(', ', array_fill(0, count($insertColumns), '?'));
    $columnList = implode(', ', array_map(function($col) { return "`$col`"; }, $insertColumns));
    $stmt = $conn->prepare("INSERT INTO `$targetTable` ($columnList) VALUES ($placeholders)");
    if (!$stmt) {
        return ['success' => false, 'error' => 'Kon gekoppelde rij niet invoegen in ' . $targetTable];
    }

    $types = str_repeat('s', count($insertValues));
    $stmt->bind_param($types, ...$insertValues);
    $ok = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();

    if (!$ok) {
        return ['success' => false, 'error' => 'Invoegen gekoppelde rij mislukt in ' . $targetTable . ': ' . $error];
    }

    return ['success' => true];
}

function ensurePersonRowsAcrossConnectedTables(mysqli $conn, string $sourceTable, int $id): array {
    $sourcePk = getPrimaryKeyColumn($conn, $sourceTable);
    if ($sourcePk === null) {
        return ['success' => true];
    }

    $idString = $conn->real_escape_string((string)$id);
    $result = $conn->query("SELECT * FROM `$sourceTable` WHERE `$sourcePk` = '$idString' LIMIT 1");
    $row = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;

    if (!$row) {
        return ['success' => true];
    }

    $personCore = getPersonCoreFromRow($row);
    if (!$personCore) {
        return ['success' => true];
    }

    $tables = getAllTables($conn);
    foreach ($tables as $targetTable) {
        if ($targetTable === $sourceTable) {
            continue;
        }

        $targetPk = getPrimaryKeyColumn($conn, $targetTable);
        if ($targetPk === null) {
            continue;
        }

        $insertResult = insertPersonRowIfMissing($conn, $targetTable, $targetPk, $id, $personCore);
        if (!$insertResult['success']) {
            return $insertResult;
        }
    }

    return ['success' => true];
}

function propagateUpdateToConnectedRows(mysqli $conn, string $sourceTable, int $id, string $sourceColumn, string $value): array {
    $candidates = getEquivalentColumnCandidates($sourceColumn);
    $tables = getAllTables($conn);
    $idString = (string)$id;

    foreach ($tables as $targetTable) {
        if ($targetTable === $sourceTable) {
            continue;
        }

        $targetPk = getPrimaryKeyColumn($conn, $targetTable);
        if ($targetPk === null) {
            continue;
        }

        if (!rowExistsByPrimaryKey($conn, $targetTable, $targetPk, $id)) {
            continue;
        }

        $targetColumns = getTableColumns($conn, $targetTable);
        $targetColumn = findMatchingColumnByCandidates($targetColumns, $candidates);
        if ($targetColumn === null || $targetColumn === $targetPk) {
            continue;
        }

        $stmt = $conn->prepare("UPDATE `$targetTable` SET `$targetColumn` = ? WHERE `$targetPk` = ?");
        if (!$stmt) {
            return ['success' => false, 'error' => 'Kon dynamische update niet voorbereiden'];
        }

        $stmt->bind_param('ss', $value, $idString);
        $ok = $stmt->execute();
        $error = $stmt->error;
        $stmt->close();

        if (!$ok) {
            return ['success' => false, 'error' => 'Dynamische gekoppelde update mislukt: ' . $error];
        }
    }

    return ['success' => true];
}

// Lees de gevraagde actie uit POST; bij ontbreken gebruiken we een lege string.
$action = $_POST['action'] ?? '';

// Verwerk alle beheerdersacties via een centrale switch zodat elke actie afgebakend blijft.
switch ($action) {
    case 'update_cell':
        // Haal doelgegevens op: tabel, kolom, nieuwe waarde en de identifier van de rij.
        $table = $_POST['table'] ?? '';
        $column = $_POST['column'] ?? '';
        $value = $_POST['value'] ?? '';
        $idColumn = $_POST['id_column'] ?? 'id';
        $idValue = $_POST['id_value'] ?? '';
        
        // Filter tabel- en kolomnamen op veilige tekens om SQL-injectie via identifiers te beperken.
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        $idColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $idColumn);

        $primaryKeyColumn = getPrimaryKeyColumn($conn, $table);
        if ($primaryKeyColumn === null) {
            echo json_encode(['success' => false, 'error' => 'Primaire sleutel niet gevonden']);
            break;
        }
        if (strtolower($column) === strtolower($primaryKeyColumn)) {
            echo json_encode(['success' => false, 'error' => 'ID-kolommen zijn niet bewerkbaar']);
            break;
        }
        $idColumn = $primaryKeyColumn;
        
        // Gebruik een prepared statement voor waarden zodat invoer niet als SQL wordt uitgevoerd.
        $stmt = $conn->prepare("UPDATE `$table` SET `$column` = ? WHERE `$idColumn` = ?");
        $stmt->bind_param("ss", $value, $idValue);
        
        // Geef een uniforme succes/fout-response terug aan de frontend.
        if ($stmt->execute()) {
            $syncResult = syncCurrentRowIdByEntity($conn, $table, $idColumn, $idValue);
            if (!$syncResult['success']) {
                echo json_encode(['success' => false, 'error' => $syncResult['error']]);
                $stmt->close();
                break;
            }

            $effectiveId = isset($syncResult['synced_id']) ? (int)$syncResult['synced_id'] : (int)$idValue;
            $linkedResult = updateLinkedPersonRow($conn, $table, $column, $value, $effectiveId);
            if (!$linkedResult['success']) {
                echo json_encode(['success' => false, 'error' => $linkedResult['error']]);
                $stmt->close();
                break;
            }

            $dynamicPropagateResult = propagateUpdateToConnectedRows($conn, $table, $effectiveId, $column, $value);
            if (!$dynamicPropagateResult['success']) {
                echo json_encode(['success' => false, 'error' => $dynamicPropagateResult['error']]);
                $stmt->close();
                break;
            }

            $response = ['success' => true];
            if (isset($syncResult['synced_id'])) {
                $response['synced_id'] = $syncResult['synced_id'];
            }
            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        // Ruim statement-resources op na uitvoering.
        $stmt->close();
        break;
        
    case 'add_row':
        // Voeg een lege rij toe aan de opgegeven tabel (behalve auto-incrementkolommen).
        $table = $_POST['table'] ?? '';
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        
        // Lees alle kolommen op en bepaal dynamisch welke velden we gaan invullen.
        $columnsResult = $conn->query("SHOW COLUMNS FROM `$table`");
        $columns = [];
        $values = [];
        $nextGlobalId = getGlobalNextId($conn);
        $primaryKeyColumn = getPrimaryKeyColumn($conn, $table);
        if ($primaryKeyColumn === null) {
            echo json_encode(['success' => false, 'error' => 'Primaire sleutel niet gevonden']);
            break;
        }

        while ($col = $columnsResult->fetch_assoc()) {
            $fieldName = $col['Field'];
            $isAutoIncrement = ($col['Extra'] ?? '') === 'auto_increment';

            // We vullen de primaire sleutel altijd expliciet met een globaal unieke waarde.
            if ($fieldName === $primaryKeyColumn) {
                $columns[] = $fieldName;
                $values[] = (string)$nextGlobalId;
                continue;
            }

            if (!$isAutoIncrement) {
                $columns[] = $fieldName;
                $values[] = '';
            }
        }

        if (empty($columns)) {
            echo json_encode(['success' => false, 'error' => 'Geen invoegbare kolommen gevonden']);
            break;
        }
        
        // Bouw dynamisch de INSERT-query op op basis van het aantal gevonden kolommen.
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnList = implode(', ', array_map(function($c) { return "`$c`"; }, $columns));
        
        $stmt = $conn->prepare("INSERT INTO `$table` ($columnList) VALUES ($placeholders)");
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => $conn->error]);
            break;
        }
        
        // Bind alle waarden als string; MySQL cast waar nodig.
        $types = str_repeat('s', count($columns));
        $stmt->bind_param($types, ...$values);
        
        // Retourneer bij succes het toegewezen globale ID.
        if ($stmt->execute()) {
            $createdId = in_array($primaryKeyColumn, $columns, true)
                ? (int)$values[array_search($primaryKeyColumn, $columns, true)]
                : (int)$conn->insert_id;

            $linkedCreateResult = ensureLinkedPersonRowExists($conn, $table, $createdId);
            if (!$linkedCreateResult['success']) {
                echo json_encode(['success' => false, 'error' => $linkedCreateResult['error']]);
                $stmt->close();
                break;
            }

            $dynamicCreateResult = ensurePersonRowsAcrossConnectedTables($conn, $table, $createdId);
            if (!$dynamicCreateResult['success']) {
                echo json_encode(['success' => false, 'error' => $dynamicCreateResult['error']]);
                $stmt->close();
                break;
            }

            $response = ['success' => true, 'id' => $createdId];
            if (isset($linkedCreateResult['linked_created'])) {
                $response['linked_created'] = true;
            }
            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
        break;
        
    case 'delete_row':
        // Verwijder exact een rij op basis van de meegegeven primaire sleutel/identifier.
        $table = $_POST['table'] ?? '';
        $idColumn = $_POST['id_column'] ?? 'id';
        $idValue = $_POST['id_value'] ?? '';
        
        // Sanitize identifiers zodat alleen geldige tabel- en kolomnamen overblijven.
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $idColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $idColumn);
        $primaryKeyColumn = getPrimaryKeyColumn($conn, $table);
        if ($primaryKeyColumn === null) {
            echo json_encode(['success' => false, 'error' => 'Primaire sleutel niet gevonden']);
            break;
        }
        $idColumn = $primaryKeyColumn;
        
        // Prepared statement voorkomt injectie in de te verwijderen waarde.
        $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$idColumn` = ?");
        $stmt->bind_param("s", $idValue);
        
        if ($stmt->execute()) {
            $linkedDeleteResult = deleteLinkedPersonRow($conn, $table, (int)$idValue);
            if (!$linkedDeleteResult['success']) {
                echo json_encode(['success' => false, 'error' => $linkedDeleteResult['error']]);
                $stmt->close();
                break;
            }

            $resequenceResult = resequenceGlobalIdsAfterDelete($conn, (int)$idValue);
            if (!$resequenceResult['success']) {
                echo json_encode(['success' => false, 'error' => $resequenceResult['error']]);
                $stmt->close();
                break;
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
        break;
        
    case 'add_column':
        // Voeg een nieuwe kolom toe aan een bestaande tabel via ALTER TABLE.
        $table = $_POST['table'] ?? '';
        $columnName = $_POST['column_name'] ?? '';
        $columnType = $_POST['column_type'] ?? 'VARCHAR(255)';
        
        // Tabel- en kolomnamen filteren; type blijft zoals aangeleverd.
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $columnName = preg_replace('/[^a-zA-Z0-9_]/', '', $columnName);
        
        // Stel de ALTER-query samen voor het toevoegen van de kolom.
        $sql = "ALTER TABLE `$table` ADD COLUMN `$columnName` $columnType";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;
        
    case 'delete_column':
        // Verwijder een kolom uit een tabel.
        $table = $_POST['table'] ?? '';
        $columnName = $_POST['column_name'] ?? '';
        
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $columnName = preg_replace('/[^a-zA-Z0-9_]/', '', $columnName);
        
        // Stel de ALTER-query samen voor het verwijderen van de kolom.
        $sql = "ALTER TABLE `$table` DROP COLUMN `$columnName`";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;
        
    case 'create_table':
        // Maak een nieuwe standaardtabel aan met basisvelden.
        $tableName = $_POST['table_name'] ?? '';
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
        
        // Standaardschema: id, naam en aanmaakdatum.
        $sql = "CREATE TABLE `$tableName` (
            `id` INT PRIMARY KEY,
            `name` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;
        
    case 'delete_table':
        // Verwijder een volledige tabel op basis van de opgegeven naam.
        $tableName = $_POST['table_name'] ?? '';
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
        
        $sql = "DROP TABLE `$tableName`";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;
    
    case 'delete_file':
        // Verwijder een los PHP-bestand dat gekoppeld is aan een tabelweergave.
        $filename = $_POST['filename'] ?? '';

        // Accepteer alleen veilige bestandsnamen met letters/cijfers/underscore en een PHP-extensiepatroon.
        if (!preg_match('/^[a-zA-Z0-9_]+\.phhp$/', $filename)) {
            echo json_encode(['success' => false, 'error' => 'Ongeldige bestandsnaam']);
            break;
        }

        // Bescherm kernbestanden van het systeem tegen verwijdering.
        $protected = ['index.php', 'table_template.php', 'admin_handler.php', 
                      'generate_pages.php', 'get_schema.php'];
        if (in_array($filename, $protected)) {
            echo json_encode(['success' => false, 'error' => 'Beveiligd bestand kan niet worden verwijderd']);
            break;
        }
        
        // Verwijder alleen als het bestand bestaat en verwijderbaar is.
        if (file_exists($filename) && unlink($filename)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Bestand niet gevonden of kon niet worden verwijderd']);
        }
        break;

    default:
        // Fallback als een onbekende actie wordt opgevraagd.
        echo json_encode(['success' => false, 'error' => 'Ongeldige actie']);
}

// Sluit de databaseverbinding netjes af na elke request.
$conn->close();
?>
