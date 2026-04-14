<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Bedrijfsgegevens</title>
    <!-- Centrale stylesheet voor layout en componentopmaak. -->
    <link rel="stylesheet" href="style.css">
    <!-- Externe iconbibliotheek voor UI-iconen. -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  
<div class="wrapper">     
            <!-- Navigatiebalk bovenaan de pagina. -->
            <nav class="topnav">
                <div class="left"> 
                    <a href="">
                    </a>
                </div>

                <div class="right"> 

                    <a href="">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>zoeken</span>
                    </a>

                </div>
            </nav>

            <main>
                <!-- Introsectie met paginatitel. -->
                <section class="hero-section">
                    <div class="text">
                        <h2>UrenRegristratieSysteem</h2>
                    </div>
                </section>

            <!-- Datasectie met resultaten uit de medewerkers-tabel van team 2. -->
            <section class="team-data">
        <div class="text">
        <?php
        // Verbind met de database en toon records direct in de inhoudssectie.
        $servername = "127.0.0.1";
        $username   = "root";
        $password   = "root";
        $dbname     = "UrenRegistratieSysteem";
        $dbport     = 3306;

        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @new mysqli($servername, $username, $password, $dbname, $dbport);
        if ($conn->connect_error) {
            echo '<div class="record">Verbinding mislukt: ' . htmlspecialchars($conn->connect_error) . '</div>';
        } else {
            // Statische query voor een specifieke tabel met teamgegevens.
            $sql    = "SELECT id, name, email, hours FROM team_2_medewerkers";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                echo '<section class="Data"><h2>Team 2 Gegevens</h2>';
                while ($row = $result->fetch_assoc()) {
                    // Toon elke database-rij als blok met geescape-te velden.
                    echo '<div class="record">'
                      . '<p><strong>ID:</strong> '   . htmlspecialchars($row['id'])    . '</p>'
                      . '<p><strong>Naam:</strong> ' . htmlspecialchars($row['name'])  . '</p>'
                      . '<p><strong>E-mail:</strong> '. htmlspecialchars($row['email']) . '</p>'
                      . '<p><strong>Uren:</strong> '. htmlspecialchars($row['hours']) . '</p>'
                      . '</div>';
                }
                echo '</section>';      // Sluit de container van de datablokken.
            } else {
                echo '<div class="record">0 resultaten</div>';
            }
            // Ruim de databaseverbinding op na het renderen van de inhoud.
            $conn->close();
        }
        ?>

      </div>
  </section>
            <!-- Voettekst van de pagina. -->

            <footer class="footer">
                <p>©TEAM 2 [2026]</p>
            </footer>
            <br>
        </div>
</body>
</html>