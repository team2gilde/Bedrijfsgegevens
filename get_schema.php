<?php
// Deze endpoint levert een JSON-overzicht van alle tabellen en kolommen voor de zoekfunctionaliteit.
header('Content-Type: application/json');

// Databaseconfiguratie voor schema-uitlezing.
$servername = "127.0.0.1";
$username   = "root";
$password   = "";
$dbname     = "UrenRegistratieSysteem";
$dbport     = 3307;

mysqli_report(MYSQLI_REPORT_OFF);
// Maak verbinding met de database.
$conn = @new mysqli($servername, $username, $password, $dbname, $dbport);

// Bij verbindingsfout direct een JSON-fout uitsturen.
if ($conn->connect_error) {
    echo json_encode([
        'error' => 'Database niet bereikbaar',
        'details' => $conn->connect_error,
        'code' => $conn->connect_errno
    ]);
    exit;
}

// Haal alle tabellen op die in de geselecteerde database bestaan.
$tablesQuery = "SHOW TABLES";
$tablesResult = $conn->query($tablesQuery);

// Het response-object bevat zowel tabellen als kolommen per tabel.
$schema = [
    'tables' => [],
    'columns' => []
];

if ($tablesResult && $tablesResult->num_rows > 0) {
    while ($tableRow = $tablesResult->fetch_array()) {
        $tableName = $tableRow[0];
        
        // Voeg tabelmetadata toe, inclusief leesbare weergavenaam voor UI-gebruik.
        $displayName = ucwords(str_replace('_', ' ', $tableName));
        $schema['tables'][] = [
            'name' => $tableName,
            'displayName' => $displayName
        ];
        
        // Lees alle kolommen van deze tabel uit.
        $columnsQuery = "SHOW COLUMNS FROM `$tableName`";
        $columnsResult = $conn->query($columnsQuery);
        
        $columns = [];
        if ($columnsResult && $columnsResult->num_rows > 0) {
            while ($columnRow = $columnsResult->fetch_assoc()) {
                // Bewaar per kolom minimaal naam en datatype, voldoende voor dynamische zoekmapping.
                $columns[] = [
                    'name' => $columnRow['Field'],
                    'type' => $columnRow['Type']
                ];
            }
        }
        
        // Koppel de kolomlijst aan de tabelnaam als sleutel.
        $schema['columns'][$tableName] = $columns;
    }
}

// Sluit verbinding en stuur het complete schema terug naar de frontend.
$conn->close();

echo json_encode($schema);
?>
