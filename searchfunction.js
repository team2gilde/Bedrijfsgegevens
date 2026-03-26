// Dynamische mappen voor tabellen en kolommen; deze worden gevuld vanuit het databaseschema.
let columnMap = {};
let tableMap = {};
let schemaLoaded = false;

const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const suggestion = document.getElementById('suggestion');

// Laad het schema op runtime zodat zoeken niet hardcoded hoeft te zijn.
const loadDatabaseSchema = async () => {
    try {
        const response = await fetch('get_schema.php');
        const schema = await response.json();
        
        if (schema.error) {
            console.error('Schema error:', schema.error);
            return;
        }
        
        // Bouw een tabelmap op basis van technische naam en weergavenaam.
        tableMap = {};
        if (schema.tables && Array.isArray(schema.tables)) {
            schema.tables.forEach(table => {
                const tableName = table.name.toLowerCase();
                tableMap[tableName] = table;
                
                // Voeg ook de zichtbare naam toe voor flexibel zoeken op gebruikersinvoer.
                const displayName = table.displayName.toLowerCase();
                tableMap[displayName] = table;
            });
        }
        
        // Bouw kolommapping dynamisch op voor alle tabellen.
        columnMap = {};
        if (schema.columns) {
            for (const tableName in schema.columns) {
                const columns = schema.columns[tableName];
                
                columns.forEach(column => {
                    const columnName = column.name.toLowerCase();
                    const columnId = `${columnName}-column`;
                    const compactName = columnName.replace(/[^a-z0-9]/g, '');
                    const underscoreName = columnName.replace(/_/g, '');
                    
                    // Koppel varianten van de kolomnaam aan het corresponderende header-id.
                    columnMap[columnName] = columnId;
                    columnMap[compactName] = columnId;
                    columnMap[underscoreName] = columnId;
                    
                    // Voeg veelvoorkomende tikfouten/varianten toe voor toleranter zoeken.
                    if (columnName === 'id') {
                        columnMap['di'] = columnId;
                    }

                    // Extra aliassen voor telefoonkolommen.
                    if (columnName.includes('phone') || columnName.includes('tel')) {
                        columnMap['phone'] = columnId;
                        columnMap['phonenumber'] = columnId;
                        columnMap['tel'] = columnId;
                        columnMap['telephone'] = columnId;
                    }
                });
            }
        }
        
        schemaLoaded = true;
        console.log('Schema loaded - Tables:', tableMap, 'Columns:', columnMap);
    } catch (error) {
        console.error('Failed to load schema:', error);
    }
};

// Start schemalading direct bij scriptstart zodat zoekacties snel kunnen werken.
loadDatabaseSchema();

const highlightCell = (cell) => {
    if (!cell) {
        return;
    }

    cell.scrollIntoView({ behavior: 'smooth', block: 'center' });
    cell.classList.add('highlight-column');

    setTimeout(() => {
        cell.classList.remove('highlight-column');
    }, 1000);
};

// Markeer een volledige rij, bijvoorbeeld wanneer een zoekresultaat precies matcht.
const highlightRow = (row) => {
    if (!row) {
        return;
    }

    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    row.classList.add('highlight-row');

    setTimeout(() => {
        row.classList.remove('highlight-row');
    }, 1500);
};

const highlightColumn = (columnId) => {
    const columnHeader = document.getElementById(columnId);
    if (!columnHeader) {
        return;
    }

    columnHeader.scrollIntoView({ behavior: 'smooth', block: 'center' });

    const columnIndex = Array.from(columnHeader.parentElement.children).indexOf(columnHeader) + 1;
    const table = columnHeader.closest('table');
    const cells = table ? table.querySelectorAll(`tbody td:nth-child(${columnIndex})`) : [];

    columnHeader.classList.add('highlight-column');
    cells.forEach((cell) => cell.classList.add('highlight-column'));

    setTimeout(() => {
        columnHeader.classList.remove('highlight-column');
        cells.forEach((cell) => cell.classList.remove('highlight-column'));
    }, 1000);
};

const normalizeForSearch = (value) => {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, ' ')
        .trim();
};

const getNameColumnIndexes = (table) => {
    if (!table) {
        return [];
    }

    const headerCells = Array.from(table.querySelectorAll('thead tr th'));
    const getIndex = (candidates) => {
        const idx = headerCells.findIndex((header) => {
            const columnName = normalizeForSearch(header.dataset?.column || '');
            return candidates.includes(columnName);
        });
        return idx >= 0 ? idx + 1 : -1;
    };

    const fullNameIndex = getIndex(['name', 'naam', 'medewerker', 'employee', 'persoon', 'person']);
    const firstNameIndex = getIndex(['voornaam', 'firstname', 'first_name']);
    const middleNameIndex = getIndex(['tussenvoegsel', 'middle', 'middlename', 'middle_name']);
    const lastNameIndex = getIndex(['achternaam', 'lastname', 'last_name', 'surname']);

    if (fullNameIndex > 0) {
        return [fullNameIndex];
    }

    const indexes = [firstNameIndex, middleNameIndex, lastNameIndex].filter((idx) => idx > 0);
    if (indexes.length) {
        return indexes;
    }

    return [2];
};

const getRowCombinedName = (row, nameColumnIndexes) => {
    if (!row || !nameColumnIndexes?.length) {
        return '';
    }

    const parts = nameColumnIndexes
        .map((index) => row.querySelector(`td:nth-child(${index})`)?.textContent || '')
        .map((text) => normalizeForSearch(text))
        .filter(Boolean);

    return parts.join(' ').trim();
};

const findNameMatchesInTable = (table, nameQuery) => {
    if (!table || !nameQuery) {
        return [];
    }

    const normalizedQuery = normalizeForSearch(nameQuery);
    const queryParts = normalizedQuery.split(' ').filter(Boolean);
    if (!queryParts.length) {
        return [];
    }

    const nameColumnIndexes = getNameColumnIndexes(table);
    const rows = table.querySelectorAll('tbody tr');
    const matches = [];

    rows.forEach((row) => {
        const combinedName = getRowCombinedName(row, nameColumnIndexes);
        if (!combinedName) {
            return;
        }

        const exactCombined = combinedName === normalizedQuery;
        const exactNoSpace = combinedName.replace(/\s+/g, '') === normalizedQuery.replace(/\s+/g, '');
        const includesAllParts = queryParts.every((part) => combinedName.includes(part));

        if (exactCombined || exactNoSpace || includesAllParts) {
            const section = row.closest('section.Data');
            const heading = section?.querySelector('h2');
            matches.push({
                table,
                row,
                tableName: heading ? heading.textContent.trim() : 'tabel',
                combinedName
            });
        }
    });

    return matches;
};

const findNameMatchesInAllTables = (nameQuery) => {
    const tables = document.querySelectorAll('.data-table');
    const allMatches = [];

    tables.forEach((table) => {
        const matches = findNameMatchesInTable(table, nameQuery);
        allMatches.push(...matches);
    });

    return allMatches;
};

const findRowByName = (table, nameQuery) => {
    if (!table || !nameQuery) {
        return null;
    }

    const parts = nameQuery
        .toLowerCase()
        .split(' ')
        .map((part) => part.trim())
        .filter(Boolean);

    if (!parts.length) {
        return null;
    }

    const rows = table.querySelectorAll('tbody tr');
    for (const row of rows) {
        const nameCell = row.querySelector('td:nth-child(2)');
        if (!nameCell) {
            continue;
        }

        const nameText = nameCell.textContent.toLowerCase();
        const matches = parts.every((part) => nameText.includes(part));
        if (matches) {
            return row;
        }
    }

    return null;
};

// Zoek op naam door alle zichtbare tabellen en geef de eerste match terug.
const findRowInAllTables = (nameQuery) => {
    if (!nameQuery) {
        return null;
    }

    const parts = nameQuery
        .toLowerCase()
        .split(' ')
        .map((part) => part.trim())
        .filter(Boolean);

    if (!parts.length) {
        return null;
    }

    const allTables = document.querySelectorAll('.data-table');
    for (const table of allTables) {
        const rows = table.querySelectorAll('tbody tr');
        for (const row of rows) {
            const nameCell = row.querySelector('td:nth-child(2)');
            if (!nameCell) {
                continue;
            }

            const nameText = nameCell.textContent.toLowerCase();
            const matches = parts.every((part) => nameText.includes(part));
            if (matches) {
                return { table, row };
            }
        }
    }

    return null;
};

// Bepaal de beste benadering op basis van Levenshtein-afstand.
const getClosestNameInAllTables = (nameQuery) => {
    if (!nameQuery) {
        return null;
    }

    const allTables = document.querySelectorAll('.data-table');
    let bestMatch = null;
    let bestScore = Infinity;

    for (const table of allTables) {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row) => {
            const nameCell = row.querySelector('td:nth-child(2)');
            if (!nameCell) {
                return;
            }

            const nameText = nameCell.textContent.trim().toLowerCase();
            const score = levenshtein(nameQuery.toLowerCase(), nameText);

            if (score < bestScore) {
                bestScore = score;
                bestMatch = nameText;
            }
        });
    }

    return bestMatch;
};

// Zoek een rij op ID binnen een specifiek benoemde tabel.
const findRowByIdInTable = (tableName, idValue) => {
    if (!tableName || !idValue) {
        return null;
    }

    const sections = document.querySelectorAll('section.Data');
    for (const section of sections) {
        const heading = section.querySelector('h2');
        if (!heading || heading.textContent.toLowerCase() !== tableName.toLowerCase()) {
            continue;
        }

        const table = section.querySelector('.data-table');
        if (!table) {
            continue;
        }

        const rows = table.querySelectorAll('tbody tr');
        for (const row of rows) {
            const idCell = row.querySelector('td:nth-child(1)');
            if (!idCell) {
                continue;
            }

            const idText = idCell.textContent.trim();
            if (idText === String(idValue)) {
                return row;
            }
        }
    }

    return null;
};

// Zoek een rij op ID met flexibele tabelmatching (heading bevat trefwoord).
const findRowByIdInTableFlexible = (tableKeyword, idValue) => {
    if (!tableKeyword || !idValue) {
        return null;
    }

    const sections = document.querySelectorAll('section.Data');
    for (const section of sections) {
        const heading = section.querySelector('h2');
        if (!heading) {
            continue;
        }

        // Controleer of de sectietitel het gezochte tabeltrefwoord bevat.
        const headingText = heading.textContent.toLowerCase();
        if (!headingText.includes(tableKeyword.toLowerCase())) {
            continue;
        }

        const table = section.querySelector('.data-table');
        if (!table) {
            continue;
        }

        const rows = table.querySelectorAll('tbody tr');
        for (const row of rows) {
            const idCell = row.querySelector('td:nth-child(1)');
            if (!idCell) {
                continue;
            }

            const idText = idCell.textContent.trim();
            if (idText === String(idValue)) {
                return { row, tableName: heading.textContent };
            }
        }
    }

    return null;
};

// Parse invoer voor ID-zoekopdrachten, zoals "id 22 klanten" of "id klanten 22".
const parseIdSearch = (words) => {
    // Controleer of het sleutelwoord `id` aanwezig is.
    const idIndex = words.findIndex(word => word === 'id');
    if (idIndex === -1) {
        return null;
    }

    // Zoek een numerieke waarde en optioneel een tabeltrefwoord.
    let idNumber = null;
    let tableSearchKeyword = null;

    for (let i = 0; i < words.length; i++) {
        if (i === idIndex) continue;

        if (!isNaN(words[i]) && words[i] !== '') {
            idNumber = words[i];
        } else if (words[i] !== 'id') {
            // Gebruik een niet-numeriek woord als mogelijke tabelreferentie.
            tableSearchKeyword = words[i];
        }
    }

    if (!idNumber) {
        return null;
    }

    if (tableSearchKeyword) {
        // Probeer eerst een exacte map-hit op bekende tabellen.
        if (tableMap[tableSearchKeyword]) {
            return {
                id: idNumber,
                tableName: tableMap[tableSearchKeyword].displayName
            };
        }

        // Bij geen exacte hit: geef trefwoord terug voor flexibele matching.
        return {
            id: idNumber,
            tableSearchKeyword: tableSearchKeyword
        };
    }

    // Zonder tabeltrefwoord zoeken we alleen op ID in de huidige tabelcontext.
    return {
        id: idNumber
    };
};

// Bereken de Levenshtein-afstand tussen twee strings voor fuzzy matching.
const levenshtein = (a, b) => {
    const matrix = Array.from({ length: a.length + 1 }, () => Array(b.length + 1).fill(0));

    for (let i = 0; i <= a.length; i += 1) {
        matrix[i][0] = i;
    }
    for (let j = 0; j <= b.length; j += 1) {
        matrix[0][j] = j;
    }

    for (let i = 1; i <= a.length; i += 1) {
        for (let j = 1; j <= b.length; j += 1) {
            const cost = a[i - 1] === b[j - 1] ? 0 : 1;
            matrix[i][j] = Math.min(
                matrix[i - 1][j] + 1,
                matrix[i][j - 1] + 1,
                matrix[i - 1][j - 1] + cost
            );
        }
    }

    return matrix[a.length][b.length];
};

// Zoek binnen een tabel de dichtstbijzijnde naamtekst bij de gebruikersinvoer.
const getClosestName = (table, nameQuery) => {
    if (!table || !nameQuery) {
        return null;
    }

    const rows = table.querySelectorAll('tbody tr');
    let bestMatch = null;
    let bestScore = Infinity;

    rows.forEach((row) => {
        const nameCell = row.querySelector('td:nth-child(2)');
        if (!nameCell) {
            return;
        }

        const nameText = nameCell.textContent.trim().toLowerCase();
        const score = levenshtein(nameQuery.toLowerCase(), nameText);

        if (score < bestScore) {
            bestScore = score;
            bestMatch = nameText;
        }
    });

    return bestMatch;
};

// Zet exact een suggestieknop in de UI.
const setSuggestion = (text, onClick) => {
    if (!suggestion) {
        return;
    }

    suggestion.textContent = '';

    if (!text) {
        return;
    }

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'suggestion-button';
    button.textContent = text;
    button.addEventListener('click', onClick);
    suggestion.appendChild(button);
};

// Zet meerdere suggestieknoppen in de UI.
const setSuggestions = (items) => {
    if (!suggestion) {
        return;
    }

    suggestion.textContent = '';

    if (!items || !items.length) {
        return;
    }

    items.forEach(({ text, onClick }) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'suggestion-button';
        button.textContent = text;
        button.addEventListener('click', onClick);
        suggestion.appendChild(button);
    });
};

// Lees de huidige tabelcontext uit het body-datasetattribuut.
const getCurrentTableName = () => {
    const body = document.body;
    return body?.dataset?.currentTable ? body.dataset.currentTable.toLowerCase() : '';
};

// Helper: geef de huidige zichtbare datatabel terug.
const getCurrentTableElement = () => document.querySelector('.data-table');

// Vertaal een kolom-id naar 1-based kolomindex binnen de tabel.
const getColumnIndexById = (table, columnId) => {
    if (!table || !columnId) {
        return -1;
    }

    const headerCells = table.querySelectorAll('thead tr th');
    const index = Array.from(headerCells).findIndex((header) => header.id === columnId);
    return index + 1;
};

// Bepaal een leesbare rijnaam voor suggesties en resultaten.
const getRowDisplayName = (row) => {
    if (!row) {
        return '';
    }

    const headerRow = row.closest('table')?.querySelector('thead tr');
    if (!headerRow) {
        return row.textContent.trim();
    }

    const nameHeaderIndex = Array.from(headerRow.children)
        .findIndex((header) => header.id === (columnMap.name || 'name-column'));

    if (nameHeaderIndex >= 0) {
        const nameCell = row.querySelector(`td:nth-child(${nameHeaderIndex + 1})`);
        if (nameCell) {
            return nameCell.textContent.trim();
        }
    }

    return row.textContent.trim();
};

// Vind alle rijen in de huidige tabel die op de opgegeven kolom en query matchen.
const findMatchesInCurrentTable = (columnId, query) => {
    if (!columnId || !query) {
        return [];
    }

    const parts = query
        .toLowerCase()
        .split(' ')
        .map((part) => part.trim())
        .filter(Boolean);

    if (!parts.length) {
        return [];
    }

    const matches = [];
    const table = getCurrentTableElement();
    if (!table) {
        return [];
    }

    const columnIndex = getColumnIndexById(table, columnId);
    if (columnIndex <= 0) {
        return [];
    }

    const rows = table.querySelectorAll('tbody tr');
    rows.forEach((row) => {
        const cell = row.querySelector(`td:nth-child(${columnIndex})`);
        if (!cell) {
            return;
        }

        const text = cell.textContent.toLowerCase();
        const matchesAll = parts.every((part) => text.includes(part));
        if (matchesAll) {
            const section = row.closest('section.Data');
            const heading = section?.querySelector('h2');
            matches.push({
                table,
                row,
                cell,
                tableName: heading ? heading.textContent.trim() : 'tabel',
                displayName: getRowDisplayName(row),
                cellText: cell.textContent.trim()
            });
        }
    });

    return matches;
};

// Vind de dichtstbijzijnde match in de huidige tabel als exacte hits ontbreken.
const getClosestMatchInCurrentTable = (columnId, query) => {
    if (!columnId || !query) {
        return null;
    }

    const table = getCurrentTableElement();
    if (!table) {
        return null;
    }
    let bestMatch = null;
    let bestScore = Infinity;

    const columnIndex = getColumnIndexById(table, columnId);
    if (columnIndex <= 0) {
        return null;
    }

    const rows = table.querySelectorAll('tbody tr');
    rows.forEach((row) => {
        const cell = row.querySelector(`td:nth-child(${columnIndex})`);
        if (!cell) {
            return;
        }

        const cellText = cell.textContent.trim().toLowerCase();
        const score = levenshtein(query.toLowerCase(), cellText);

        if (score < bestScore) {
            const section = row.closest('section.Data');
            const heading = section?.querySelector('h2');
            bestScore = score;
            bestMatch = {
                table,
                row,
                cell,
                tableName: heading ? heading.textContent.trim() : 'tabel',
                displayName: getRowDisplayName(row),
                cellText: cell.textContent.trim()
            };
        }
    });

    return bestMatch;
};

// Centrale zoekflow: interpreteert invoer als tabel-, kolom- of ID-zoekopdracht.
const runSearch = () => {
    if (!searchInput) {
        return;
    }
    
    // Wacht op schema-initialisatie voordat er gezocht wordt.
    if (!schemaLoaded) {
        setSuggestion('Database-schema wordt geladen...', () => {});
        setTimeout(runSearch, 100);
        return;
    }

    const searchTerm = searchInput.value.toLowerCase().trim();
    const words = searchTerm.split(' ').filter(Boolean);
    const firstWord = words[0] || '';
    const lastWord = words[words.length - 1] || '';
    
    // Controleer of invoer een ID-zoekopdracht is.
    const idSearch = parseIdSearch(words);
    if (idSearch) {
        let result = null;
        const currentTableName = getCurrentTableName();

        if (currentTableName) {
            if (idSearch.tableName && idSearch.tableName.toLowerCase() !== currentTableName) {
                setSuggestion(`Zoeken is beperkt tot deze pagina (${currentTableName}).`, () => {});
                return;
            }

            result = findRowByIdInTable(currentTableName, idSearch.id);
        }
        
        // Probeer eerst exacte tabelmatch.
        if (!result && idSearch.tableName) {
            result = findRowByIdInTable(idSearch.tableName, idSearch.id);
        }
        
        // Als exacte match faalt, probeer flexibele tabelmatching via trefwoord.
        if (!result && idSearch.tableSearchKeyword && !currentTableName) {
            result = findRowByIdInTableFlexible(idSearch.tableSearchKeyword, idSearch.id);
        }
        
        if (result) {
            const row = result.row || result;
            const tableName = result.tableName || idSearch.tableName || currentTableName || 'de tabel';
            highlightRow(row);
            setSuggestion(`ID ${idSearch.id} gevonden in ${tableName}.`, () => {});
            return;
        } else {
            const searchTarget = currentTableName || idSearch.tableName || idSearch.tableSearchKeyword;
            setSuggestion(`ID ${idSearch.id} niet gevonden in ${searchTarget}.`, () => {});
            return;
        }
    }
    
    // Als er op tabelnaam wordt gezocht, navigeer direct naar de tabelpagina.
    const tableKey = tableMap[firstWord] || tableMap[lastWord];
    if (tableKey) {
        window.location.href = `${encodeURIComponent(tableKey.name)}.php`;
        return;
    }
    
    // Anders interpreteren we de input als kolom + zoekterm.
    const columnKey = columnMap[firstWord]
        ? firstWord
        : (columnMap[lastWord] ? lastWord : '');
    const columnId = columnKey ? columnMap[columnKey] : undefined;
    const nameQuery = columnKey
        ? words.filter((word, index) => word !== columnKey || (columnKey === firstWord ? index !== 0 : index !== words.length - 1)).join(' ').trim()
        : '';

    setSuggestion('');

    if (!columnId) {
        const currentTable = getCurrentTableElement();
        const nameMatches = currentTable
            ? findNameMatchesInTable(currentTable, searchTerm)
            : findNameMatchesInAllTables(searchTerm);

        if (nameMatches.length === 1) {
            highlightRow(nameMatches[0].row);
            return;
        }

        if (nameMatches.length > 1) {
            const options = nameMatches.slice(0, 10).map((match) => ({
                text: `${match.combinedName} (${match.tableName})`,
                onClick: () => highlightRow(match.row)
            }));

            setSuggestions(options);
            return;
        }

        // Toon beschikbare tabellen/kolommen om de gebruiker te helpen bij invoer.
        const availableColumns = Object.keys(columnMap).filter(key => key !== 'di').join(', ');
        const availableTables = Object.keys(tableMap).map(key => tableMap[key].displayName).filter((v, i, a) => a.indexOf(v) === i).join(', ');
        setSuggestion(`Niet gevonden. Tabellen: ${availableTables} | Kolommen: ${availableColumns}`, () => {});
        return;
    }

    if (!nameQuery) {
        highlightColumn(columnId);
        return;
    }

    // Zoek matches binnen de huidige tabel op de gekozen kolom.
    const matches = findMatchesInCurrentTable(columnId, nameQuery);
    if (!matches.length) {
        const closestMatch = getClosestMatchInCurrentTable(columnId, nameQuery);
        if (closestMatch) {
            const didYouMean = `Bedoelde je: ${closestMatch.cellText}?`;
            setSuggestion(didYouMean, () => {
                searchInput.value = `${firstWord} ${closestMatch.cellText}`;
                runSearch();
            });
        } else {
            setSuggestion('Geen overeenkomst gevonden in deze tabel.', () => {});
        }
        return;
    }

    if (matches.length > 1) {
        const options = matches.slice(0, 10).map((match) => ({
            text: `${match.displayName || match.cellText} (${match.tableName})`,
            onClick: () => {
                highlightRow(match.row);
                highlightCell(match.cell);
            }
        }));

        setSuggestions(options);
        return;
    }

    const match = matches[0];
    highlightRow(match.row);
    highlightCell(match.cell);
};

// Initialiseert de knop "terug naar boven" inclusief scrollgedrag en zichtbaarheid.
const setupBackToTop = () => {
    const backToTopBtn = document.getElementById('backToTop');
    if (!backToTopBtn) {
        return;
    }

    const toggleVisibility = () => {
        if (window.scrollY > 200) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    };

    window.addEventListener('scroll', toggleVisibility);
    toggleVisibility();

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
};

// Initialiseert de printknop en tijdelijk printvriendelijke paginastatus.
const setupPrintButton = () => {
    const printBtn = document.getElementById('printBtn');
    if (!printBtn) {
        return;
    }

    printBtn.addEventListener('click', () => {
        const dataSection = document.querySelector('section.Data');
        
        if (!dataSection) {
            alert('Geen tabel om af te drukken. Ga eerst naar een tabelpagina.');
            return;
        }

        // Verberg niet-relevante elementen tijdelijk tijdens afdrukmodus.
        document.body.classList.add('print-mode');
        
        // Open de browser printdialoog.
        window.print();
        
        // Herstel de normale zichtbaarheid na het sluiten van de printdialoog.
        setTimeout(() => {
            document.body.classList.remove('print-mode');
        }, 100);
    });
};

// Start UI-initialisatie zodra de DOM volledig geladen is.
document.addEventListener('DOMContentLoaded', () => {
    setupBackToTop();
    setupPrintButton();
});

if (searchBtn) {
    searchBtn.addEventListener('click', runSearch);
}

if (searchInput) {
    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            runSearch();
        }
    });
}