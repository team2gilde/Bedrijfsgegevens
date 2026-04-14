<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Bedrijfsgegevens</title>
    <!-- JavaScript voor zoeken, navigatiehulp en interactieve UI-functies. -->
    <script src="searchfunction.js" defer></script>
    <script src="admin.js" defer></script>
    <!-- Centrale stylesheet voor layout, typografie en componentstijlen. -->
    <link rel="stylesheet" href="style.css">
    <!-- Externe iconset voor knoppen en visuele indicatoren. -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  
<div class="wrapper">     
            <!-- Bovenste navigatiebalk met dynamische tabellinks en hulpmiddelen. -->
            <nav class="topnav">
                <div class="left"> 
                    <a href="">
                    </a>
                </div>

                <div class="right"> 
                    <?php
                    // Bouw navigatieknoppen op basis van alle tabellen die in de database bestaan.
                    $servername = "127.0.0.1";
                    $username   = "root";
                    $password   = "";
                    $dbname     = "UrenRegistratieSysteem";
                    $dbport     = 3307;

                    // Maak verbinding; als dat lukt tonen we alle tabelpagina-links.
                    mysqli_report(MYSQLI_REPORT_OFF);
                    $conn = @new mysqli($servername, $username, $password, $dbname, $dbport);
                    if (!$conn->connect_error) {
                        $tablesResult = $conn->query("SHOW TABLES");
                        if ($tablesResult && $tablesResult->num_rows > 0) {
                            while ($tableRow = $tablesResult->fetch_array()) {
                                $tableName = $tableRow[0];
                                // Maak een nette weergavenaam, bijvoorbeeld "team_leden" -> "Team Leden".
                                $displayName = ucwords(str_replace('_', ' ', $tableName));
                                echo '<a class="table-link" href="' . htmlspecialchars($tableName) . '.php">' . htmlspecialchars($displayName) . '</a>';
                            }
                        }
                        // Sluit de databaseverbinding zodra de links zijn opgebouwd.
                        $conn->close();
                    } else {
                        // Toon direct een zichtbare statusmelding als de database niet bereikbaar is.
                        echo '<span class="table-link" style="opacity:0.8; cursor:default;">Database offline (fout ' . (int)$conn->connect_errno . ')</span>';
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
                <!-- Introsectie met systeemtitel. -->
                <section class="hero-section">
                    <div class="text">
                        <h2>Bedrijfsgegevens</h2>
                    </div>
                </section>
            <!-- Voettekst met team- en jaaraanduiding. -->

            <footer class="footer">
                <p>©TEAM 2 [2026]</p>
            </footer>
            <br>
            <button id="backToTop" class="back-to-top" type="button" aria-label="Terug naar boven" title="Terug naar boven">
                <i class="fas fa-arrow-up"></i>
            </button>
        </div>
</body>
</html>