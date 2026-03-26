<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>UrenRegistratieSysteem</title>
    <script src="searchfunction.js" defer></script>
    <script src="admin.js" defer></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php
    // `$currentTable` wordt gezet door de gegenereerde tabelbestanden (zoals `klanten.php` en `uren.php`).
    // Fallback-beveiliging: als deze template direct wordt geopend zonder context, stoppen we meteen.
    if (!isset($currentTable)) {
        die('error: template moet worden geincluceerd met een tabelnaam');
    }

    // Veiligheidsfilter op identifier-niveau, ook als invoer uit interne bestanden komt.
    $currentTableName = preg_replace('/[^a-zA-Z0-9_]/', '', $currentTable);

    // Als de tabelnaam na filtering leeg is, stuur terug naar de homepage.
    if (empty($currentTableName)) {
        header('Location: index.php');
        exit;
    }
    ?>
</head>
<body data-current-table="<?php echo htmlspecialchars($currentTableName); ?>">

<div class="wrapper">
    <nav class="topnav">
        <div class="left">
            <a href="index.php"></a>
        </div>

        <div class="right">
            <?php
            // Maak opnieuw een DB-verbinding om dynamische navigatielinks op te bouwen.
            $servername = "127.0.0.1";
            $username   = "root";
            $password   = "";
            $dbname     = "UrenRegistratieSysteem";
            $dbport     = 3307;

            mysqli_report(MYSQLI_REPORT_OFF);
            $conn = @new mysqli($servername, $username, $password, $dbname, $dbport);
            if (!$conn->connect_error) {
                $tablesResult = $conn->query("SHOW TABLES");
                if ($tablesResult && $tablesResult->num_rows > 0) {
                    while ($tableRow = $tablesResult->fetch_array()) {
                        $tableName = $tableRow[0];
                        // Toon een leesbare knopnaam voor elke tabel.
                        $displayName = ucwords(str_replace('_', ' ', $tableName));
                        echo '<a class="table-link" href="' . htmlspecialchars($tableName) . '.php">' . htmlspecialchars($displayName) . '</a>';
                    }
                }
            }
            ?>

            <div class="search-wrapper">
                <div class="search-container" role="search" aria-label="Zoek categorieën">
                    <input id="searchInput" class="search-input" type="search" placeholder="Zoeken (bijv. medewerkers, uren, etc)...">
                    <button id="searchBtn" class="search-btn" aria-label="Zoeken">Zoeken</button>
                </div>
                <span id="suggestion" class="suggestion" aria-live="polite"></span>
                <div class="search-help" aria-label="Uitleg zoekfunctie">
                    <span class="search-help-icon" title="Typ een tabelnaam (bijv. medewerkers), een kolom + waarde (bijv. voornaam jan), of een ID (bijv. id 12). Klik op Zoeken om direct naar het juiste resultaat te gaan." aria-label="Zoekhulp">?</span>
                </div>
            </div>
            <button id="printBtn" class="print-btn" type="button" aria-label="Afdrukken naar PDF" title="Afdrukken naar PDF">
                <i class="fas fa-file-pdf"></i>
            </button>
            <button id="adminBtn" class="admin-btn-toggle" type="button" aria-label="Beheermodus" title="Beheermodus">
                <i class="fas fa-lock"></i>
            </button>
        </div>
    </nav>

    <main>
        <section class="hero-section">
            <div class="text">
                <h2>UrenRegistratieSysteem</h2>
            </div>
        </section>

        <section class="team-data">
            <div class="text">
                <?php
                // Toon foutmelding als er geen databaseverbinding beschikbaar is.
                if ($conn->connect_error) {
                    echo '<div class="record">Verbinding mislukt: ' . htmlspecialchars($conn->connect_error) . '</div>';
                } else {
                    // Gebruik de tabelnaam uit de pagina-context als primaire bron.
                    $tableParam = $currentTableName;

                    // Lees nogmaals alle tabellen; hiermee valideren we of de gekozen tabel bestaat.
                    $tables = [];
                    $tablesResult = $conn->query("SHOW TABLES");
                    if ($tablesResult && $tablesResult->num_rows > 0) {
                        while ($tableRow = $tablesResult->fetch_array()) {
                            $tables[] = $tableRow[0];
                        }
                    }

                    // Fallback naar de eerste beschikbare tabel als er geen specifieke tabel is gezet.
                    if (!$tableParam && count($tables) > 0) {
                        $tableParam = $tables[0];
                    }

                    // Ga alleen verder als de tabel ook echt in de databaselijst voorkomt.
                    if ($tableParam && in_array($tableParam, $tables, true)) {
                        $displayName = ucwords(str_replace('_', ' ', $tableParam));

                        // Haal alle kolommen op zodat de tabelkop en SELECT-query dynamisch zijn.
                        $columnsResult = $conn->query("SHOW COLUMNS FROM `$tableParam`");
                        $columns = [];
                        $primaryKeyColumn = null;

                        if ($columnsResult && $columnsResult->num_rows > 0) {
                            while ($columnRow = $columnsResult->fetch_assoc()) {
                                $columns[] = $columnRow['Field'];
                                if (($columnRow['Key'] ?? '') === 'PRI') {
                                    $primaryKeyColumn = $columnRow['Field'];
                                }
                            }
                        }

                        if (!empty($columns)) {
                            // Bouw dynamisch een veilige kolomlijst op voor de SELECT-query.
                            $columnList = implode(', ', array_map(function($col) { return "`$col`"; }, $columns));
                            $sql = "SELECT $columnList FROM `$tableParam`";
                            if (!empty($primaryKeyColumn)) {
                                $sql .= " ORDER BY `$primaryKeyColumn` ASC";
                            }
                            $result = $conn->query($sql);

                            // Toon altijd de tabelstructuur, ook als er nog geen records zijn.
                            echo '<section class="Data"><h2>' . htmlspecialchars($displayName) . '</h2>';
                            echo '<div class="table-wrapper">';
                            echo '<table class="data-table">';
                            echo '<thead>';
                            echo '<tr>';

                            foreach ($columns as $column) {
                                $columnId = strtolower($column) . '-column';
                                $displayColumn = ucfirst(str_replace('_', ' ', $column));
                                echo '<th id="' . htmlspecialchars($columnId) . '" data-column="' . htmlspecialchars($column) . '">' . htmlspecialchars($displayColumn) . '</th>';
                            }

                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    foreach ($columns as $column) {
                                        // Escapen van celwaarden voorkomt XSS bij gebruikersinvoer.
                                        echo '<td>' . htmlspecialchars($row[$column] ?? '') . '</td>';
                                    }
                                    echo '</tr>';
                                }
                            } else {
                                // Toon een duidelijke leegstatus als er nog geen data bestaat.
                                echo '<tr><td colspan="' . count($columns) . '" style="text-align: center; padding: 20px; color: #888;">Nog geen gegevens. Klik op "Rij toevoegen" in beheermodus om data toe te voegen.</td></tr>';
                            }

                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                            echo '</section>';
                        } else {
                            // Er is wel een tabel, maar geen uitleesbare kolomdefinitie.
                            echo '<div class="record">Geen kolommen gevonden voor deze tabel</div>';
                        }
                    } else {
                        // De gevraagde tabel bestaat niet (meer) of is ongeldig.
                        echo '<div class="record">Tabel niet gevonden</div>';
                    }

                    // Sluit databaseverbinding zodra de renderlogica klaar is.
                    $conn->close();
                }
                ?>
            </div>
        </section>

        <footer class="footer">
            <p>©TEAM 2 [2026]</p>
        </footer>
        <br>
        <button id="backToTop" class="back-to-top" type="button" aria-label="Terug naar boven" title="Terug naar boven">
            <i class="fas fa-arrow-up"></i>
        </button>
    </main>
</div>
</body>
</html>
