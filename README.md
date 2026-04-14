# Bedrijfsgegevens

## 1. Inleiding
Dit verslag legt de hele website uit in eenvoudige taal. Het doel is dat iemand zonder technische achtergrond toch goed snapt hoe de site werkt.

De website heet **Bedrijfsgegevens**. Je gebruikt deze site om gegevens van een bedrijf bij te houden, zoals:
- klanten
- medewerkers
- projecten
- uren

De site bestaat uit meerdere pagina's en scripts die samenwerken. Aan de voorkant zie je knoppen, zoekvelden en tabellen. Aan de achterkant praat de site met een database.

## 2. Wat is het doel van de site?
Het doel van deze site is:
- gegevens overzichtelijk tonen
- makkelijk zoeken in gegevens
- in beheermodus gegevens aanpassen
- tabellen en kolommen aanmaken of verwijderen
- automatisch pagina's maken voor nieuwe tabellen

Met andere woorden: dit is een soort beheersysteem voor data.

## 3. Hoe zit de site in elkaar?
Je kunt de site zien als 3 lagen:

1. De pagina's die de gebruiker ziet (HTML + CSS)
2. De slimme acties in de browser (JavaScript)
3. De serverkant die met de database praat (PHP + MySQL)

Alles samen zorgt ervoor dat je data kunt lezen en beheren.

## 4. Belangrijkste bestanden
Hieronder de belangrijkste bestanden en wat ze doen.

### 4.1 `index.php`
Dit is de startpagina.

Wat gebeurt hier:
- Er wordt een navigatiebalk getoond.
- De site leest alle tabellen uit de database.
- Voor elke tabel wordt automatisch een link gemaakt (bijvoorbeeld `klanten.php`, `uren.php`).
- Je ziet ook:
  - zoekveld
  - zoekknop
  - printknop (PDF)
  - beheerknop (slot-icoon)

Kort gezegd: index is de ingang en het hoofdmenu.

### 4.2 `table_template.php`
Dit is de belangrijkste template voor tabelpagina's.

Wat deze template doet:
- Controleert welke tabel moet worden getoond (`$currentTable`).
- Maakt verbinding met de database.
- Leest kolommen van die tabel.
- Bouwt automatisch de tabelkop en rijen op.
- Toont netjes een melding als er nog geen data is.
- Sluit de databaseverbinding.

Deze file zorgt ervoor dat je niet voor elke tabel alles dubbel hoeft te programmeren.

### 4.3 `klanten.php`, `medewerkers.php`, `projecten.php`, `uren.php`
Dit zijn kleine pagina's die alleen zeggen: "toon deze tabel in de template".

Voorbeeldidee:
- `uren.php` zet `$currentTable = "uren"`
- Daarna wordt `table_template.php` ingeladen

Dus: deze bestanden zijn als doorverwijzers naar de centrale template.

### 4.4 `admin.js` en `admin.og.js`
Deze bestanden regelen beheermodus aan de voorkant.

Wat je ermee kunt:
- beheermodus aan/uit zetten
- cellen direct aanpassen
- rij toevoegen
- rij verwijderen
- kolom toevoegen
- kolom verwijderen
- nieuwe tabel maken
- tabel verwijderen
- pagina's opnieuw genereren

Er zit ook wachtwoordcontrole in voor beheerders.

### 4.5 `searchfunction.js`
Dit bestand regelt zoeken op de site.

Wat kan de zoekfunctie:
- zoeken op tabelnaam
- zoeken op kolomnaam
- zoeken op ID
- suggesties tonen bij typfouten
- een rij/kolom markeren als resultaat

De zoekfunctie is slim omdat hij eerst het databaseschema ophaalt.

### 4.6 `admin_handler.php`
Dit is de server-endpoint voor admin-acties.

JavaScript stuurt acties naar dit bestand, bijvoorbeeld:
- `update_cell`
- `add_row`
- `delete_row`
- `add_column`
- `delete_column`
- `create_table`
- `delete_table`
- `delete_file`

`admin_handler.php` voert deze acties uit op de database en stuurt JSON terug met success of error.

### 4.7 `get_schema.php`
Dit bestand geeft alle tabellen en kolommen terug in JSON.

Waarom nodig:
- zoekfunctie weet dan welke kolommen en tabellen bestaan
- daardoor kan zoeken dynamisch en flexibel werken

### 4.8 `generate_pages.php`
Dit bestand maakt en verwijdert tabelpagina's automatisch.

Het doet 2 grote stappen:
1. Oude verweesde `.php` bestanden opruimen (die geen tabel meer hebben)
2. Voor elke huidige tabel een verse pagina maken

Zo blijft de site synchroon met de database.

## 5. Hoe werkt een normale gebruiker op de site?
Stel: iemand opent de site.

Stap voor stap:
1. Gebruiker opent `index.php`
2. Site toont knoppen naar beschikbare tabellen
3. Gebruiker klikt op `uren`
4. `uren.php` laadt `table_template.php`
5. Data uit tabel `uren` wordt getoond
6. Gebruiker kan zoeken, printen en naar boven scrollen

Zonder beheermodus is het vooral lezen en navigeren.

## 6. Hoe werkt beheermodus?
De beheerknop is een sloticoon.

### 6.1 Beheermodus aanzetten
- Gebruiker klikt op slot
- Er komt een wachtwoordprompt
- Bij juist wachtwoord wordt beheermodus actief
- Cellen worden bewerkbaar
- Extra beheerknoppen verschijnen

### 6.2 Wat verandert er visueel?
- Knop verandert van gesloten slot naar open slot
- Stijl van knop verandert
- Extra admin-knoppen komen in beeld

### 6.3 Wat kan je dan doen?
- waarden in tabel direct aanpassen
- rij toevoegen
- rij verwijderen op ID
- kolom toevoegen
- kolom verwijderen
- tabel maken
- tabel verwijderen
- pagina's opnieuw opbouwen

## 7. Uitleg per admin-actie in simpele woorden

### 7.1 Cel aanpassen
Als je tekst in een cel aanpast en wegklikt:
- JavaScript leest tabelnaam, kolomnaam, id en nieuwe waarde
- stuurt dit naar `admin_handler.php`
- backend voert `UPDATE` uit
- bij succes zie je korte highlight

### 7.2 Rij toevoegen
- JavaScript stuurt `add_row`
- backend kijkt naar alle kolommen
- maakt een lege rij aan
- pagina herlaadt

### 7.3 Rij verwijderen
- beheerder voert ID in
- bevestigt de keuze
- backend doet `DELETE` op dat ID
- pagina herlaadt

### 7.4 Kolom toevoegen
- beheerder geeft kolomnaam en type
- backend doet `ALTER TABLE ADD COLUMN`
- pagina herlaadt

### 7.5 Kolom verwijderen
- beheerder geeft kolomnaam
- backend doet `ALTER TABLE DROP COLUMN`
- pagina herlaadt

### 7.6 Nieuwe tabel maken
- beheerder geeft tabelnaam
- backend maakt tabel met standaardkolommen
- daarna worden pagina's opnieuw gegenereerd
- gebruiker wordt naar de nieuwe tabelpagina gestuurd

### 7.7 Tabel verwijderen
- beheerder moet tabelnaam typen als bevestiging
- backend verwijdert tabel
- pagina's worden opnieuw gegenereerd
- site gaat terug naar index

## 8. Hoe werkt de zoekfunctie precies?
De zoekfunctie is een sterk onderdeel van de site.

### 8.1 Eerst schema laden
Bij opstarten van de pagina:
- `searchfunction.js` vraagt `get_schema.php` op
- tabellen en kolommen worden in mappen gezet

### 8.2 Wat kan je typen?
Je kunt verschillende dingen typen, bijvoorbeeld:
- een tabelnaam (`klanten`)
- een kolomnaam met waarde
- een ID-opdracht zoals `id 5`

### 8.3 Resultaatgedrag
Afhankelijk van de invoer:
- direct navigeren naar tabel
- rij markeren
- kolom markeren
- suggestie tonen als er geen exacte match is

### 8.4 Fuzzy zoeken
De site gebruikt Levenshtein-afstand.
Dat betekent:
- ook als je iets verkeerd typt
- kan de site zeggen: "Bedoelde je ...?"

Dat maakt zoeken gebruiksvriendelijker.

## 9. Printen en terug naar boven
In `searchfunction.js` zitten ook 2 handige UI-functies.

### 9.1 Printknop
- Gebruiker klikt op printknop
- site zet tijdelijke printmodus aan
- browser printdialoog gaat open
- daarna gaat normale weergave terug

### 9.2 Terug-naar-boven knop
- knop verschijnt na scrollen
- klik zet pagina soepel terug naar boven

Kleine functie, maar fijn voor lange tabellen.

## 10. Databasekant in eenvoudige taal
De site gebruikt MySQL-database `UrenRegistratieSysteem`.

Belangrijk idee:
- tabellen zijn dynamisch
- pagina's worden op basis van tabellen aangemaakt
- daardoor kan de site meegroeien met de database

Veel SQL-queries worden dynamisch opgebouwd, zoals:
- `SHOW TABLES`
- `SHOW COLUMNS`
- `SELECT ... FROM ...`
- `UPDATE`
- `INSERT`
- `DELETE`
- `ALTER TABLE`
- `CREATE TABLE`
- `DROP TABLE`

## 11. Veiligheid: wat is goed geregeld?
Er zijn meerdere beveiligingsstappen:

1. Tabel- en kolomnamen worden geschoond met regex
2. Waarden gaan vaak via prepared statements
3. Output in HTML gaat via `htmlspecialchars`
4. Beheermodus vereist wachtwoord
5. Kernbestanden staan op beschermde lijst

Dit helpt tegen veel voorkomende fouten en aanvallen.

## 12. Sterke punten van deze website
De site heeft veel sterke punten:

- centrale template (minder dubbel werk)
- adminfuncties direct in de UI
- dynamische schema-zoekfunctie
- automatische paginageneratie
- simpele maar duidelijke workflow
- foutmeldingen in JSON voor frontend

Voor een praktisch beheersysteem is dit een sterke basis.

## 13. Waar moet je op letten bij onderhoud?
Belangrijke onderhoudspunten:

1. `admin.js` en `admin.og.js` moeten gelijk blijven
2. `admin.min.js` is build/minified versie
3. Bij nieuwe tabelpagina's altijd via generator werken
4. Let op dat database- en bestandsnamen consistent zijn
5. Test altijd na schemawijzigingen

## 14. Voorbeeld van een complete gebruikersflow
Hier een simpele end-to-end flow:

1. Admin opent index
2. Admin klikt op slot en logt in
3. Admin gaat naar `projecten`
4. Admin voegt kolom `budget` toe
5. Admin voegt nieuwe rij toe
6. Admin vult gegevens in cellen
7. Wijzigingen worden direct opgeslagen
8. Admin gebruikt zoekveld om project terug te vinden
9. Admin print overzicht
10. Admin klaar en verlaat beheermodus

Alles gebeurt in dezelfde omgeving zonder aparte beheerapp.

## 15. Waarom deze aanpak slim is
De architectuur is slim omdat:
- de template hergebruikt wordt
- data en pagina's automatisch koppelen
- adminfuncties direct in bestaande pagina werken
- zoekfunctie meebeweegt met schema

Dit geeft flexibiliteit zonder heel veel losse codebestanden.

## 16. Simpele samenvatting
In makkelijke woorden:

- De site laat bedrijfsdata zien in tabellen.
- Je kunt makkelijk zoeken, printen en navigeren.
- In beheermodus kun je data en structuur aanpassen.
- De backend voert databaseacties uit.
- De site maakt zelf pagina's voor tabellen.

Dus: dit is een compleet data-beheersysteem dat zowel voor dagelijks gebruik als voor beheer gemaakt is.

## 17. Extra uitleg voor niet-technische lezer
Stel je voor dat de database een kast is met lades.

- Elke lade = een tabel (bijv. klanten)
- Elk papier in lade = een rij
- Vakken op het papier = kolommen

Deze website is dan:
- een scherm om die lades te bekijken
- een knopenset om papieren te veranderen
- een zoekhulp om snel het juiste papier te vinden

Beheermodus is de "sleutel" van de kast:
- zonder sleutel kan je vooral kijken
- met sleutel kan je aanpassen, toevoegen en verwijderen

## 18. Praktische eindconclusie
Deze website is functioneel, duidelijk en uitbreidbaar.

Voor gebruikers:
- snel overzicht van gegevens
- eenvoudige zoekmogelijkheden

Voor beheerders:
- direct beheer van inhoud en structuur
- automatische paginageneratie

Voor ontwikkeling:
- centrale opzet met template
- logische verdeling tussen frontend en backend

Het systeem is daarmee geschikt als basis voor urenregistratie en bredere databeheer-taken binnen een team of klein bedrijf.
