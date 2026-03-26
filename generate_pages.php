<?php
header('Content-Type: application/json');

// Dit script genereert automatisch tabelpagina's op basis van de huidige database-structuur.
// Belangrijk: dit bestand is bedoeld voor beheeracties en niet voor normaal gebruikersverkeer.

// Databaseconfiguratie voor de lokale MySQL-instantie.
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "UrenRegistratieSysteem";
$dbport = 3307;

mysqli_report(MYSQLI_REPORT_OFF);
// Open een verbinding; bij fout wordt direct een JSON-fout teruggegeven.
$conn = @new mysqli($servername, $username, $password, $dbname, $dbport);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'error' => 'Database niet bereikbaar',
        'details' => $conn->connect_error,
        'code' => $conn->connect_errno
    ]));
}

// Kernbestanden die nooit automatisch verwijderd mogen worden.
$protectedFiles = [
    'index.php',
    'table_template.php',
    'admin_handler.php',
    'generate_pages.php',
    'get_schema.php',
    'searchfunction.js',
    'admin.js',
    'style.css',
    'Team_3.php'
];

// Lees alle huidige tabellen uit de database.
$tablesResult = $conn->query("SHOW TABLES");
$databaseTables = [];
$generatedFiles = [];
$deletedFiles = [];

if ($tablesResult && $tablesResult->num_rows > 0) {
    while ($tableRow = $tablesResult->fetch_array()) {
        $tableName = $tableRow[0];
        
        // Veiligheidscontrole: accepteer alleen tabelnamen met letters, cijfers en underscore.
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            continue; // Sla ongeldige of verdachte tabelnamen over.
        }
        
        $databaseTables[] = $tableName;
    }
}

// Stap 1: verwijder verweesde PHP-bestanden (bestanden zonder bijbehorende databasetabel).
$files = scandir('.');
foreach ($files as $file) {
    // Controleer alleen PHP-bestanden.
    if (!preg_match('/\.php$/', $file)) {
        continue;
    }
    
    // Sla beschermde kernbestanden altijd over.
    if (in_array($file, $protectedFiles)) {
        continue;
    }
    
    // Haal de vermoedelijke tabelnaam uit de bestandsnaam, bijvoorbeeld "klanten.php" -> "klanten".
    $possibleTableName = str_replace('.php', '', $file);
    
    // Verwijder het bestand als er geen gelijknamige tabel meer bestaat.
    if (!in_array($possibleTableName, $databaseTables)) {
        if (unlink($file)) {
            $deletedFiles[] = $file;
        }
    }
}

// Stap 2: maak voor elke tabel een vers pagina-bestand op basis van de template-aanpak.
foreach ($databaseTables as $tableName) {
    $filename = $tableName . '.php';
    
    // Inhoud van elk gegenereerd bestand: stel de tabel in en include de centrale template.
    $content = '<?php
// Automatisch gegenereerd bestand voor tabel: ' . $tableName . '
// Gegenereerd op: ' . date('Y-m-d H:i:s') . '
// Wijzig dit bestand niet handmatig; gebruik het beheerpaneel om opnieuw te genereren.

$currentTable = "' . $tableName . '";
include "table_template.php";
?>';
    
    // Schrijf het bestand naar disk; bij succes registreren we het in de respons.
    if (file_put_contents($filename, $content)) {
        $generatedFiles[] = $filename;
    }
}

$conn->close();

// Geef een complete JSON-samenvatting terug voor de admin-frontend (AJAX-call).
echo json_encode([
    'success' => true, 
    'files' => $generatedFiles,
    'deleted' => $deletedFiles,
    'count' => count($generatedFiles),
    'deletedCount' => count($deletedFiles)
]);
?>