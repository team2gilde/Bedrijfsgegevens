// Centrale statusvlag voor beheermodus; bepaalt of bewerkingsfuncties actief zijn.
let adminMode = false;

// Deze IIFE houdt hashinglogica lokaal en voorkomt losse globale hulpfuncties.
const verifyAdminPassword = (() => {
    // Zoutwaarde en verwachte hash zijn opgesplitst om niet als een enkele makkelijke string zichtbaar te zijn.
    const s = "sFaZbBfE+QP/" + "A7WRS2SlPw==";
    const h = "sF6dGIjZOZ/L" + "uI3i9u26lVobII1vC3dIo44u183Z3Dc=";
    const it = 210000;

    const b64ToBytes = (b64) => {
        const bin = atob(b64);
        const bytes = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
        return bytes;
    };

    const bytesToB64 = (bytes) => {
        let bin = "";
        bytes.forEach((b) => (bin += String.fromCharCode(b)));
        return btoa(bin);
    };

    const deriveKeyB64 = async (password) => {
        const enc = new TextEncoder();
        const keyMaterial = await crypto.subtle.importKey("raw", enc.encode(password), {name: "PBKDF2"}, false, ["deriveBits"]);

        const bits = await crypto.subtle.deriveBits({
            name: "PBKDF2", hash: "SHA-256", salt: b64ToBytes(s), iterations: it
        }, keyMaterial, 256);

        return bytesToB64(new Uint8Array(bits));
    };

    // Retourneer een controlefunctie die waar/onwaar oplevert op basis van het afgeleide wachtwoord.
    return async (password) => (await deriveKeyB64(password)) === h;
})();

// Initialiseer de beheerknop en herstel eerdere beheerstatus vanuit localStorage.
const initAdminButton = () => {
    const adminBtn = document.getElementById('adminBtn');
    if (!adminBtn) return;

    // Als adminmodus eerder actief was, zet die meteen terug zonder opnieuw te vragen.
    if (localStorage.getItem('adminModeEnabled') === 'true') {
        enableAdminMode(true); // `true` betekent: melding overslaan en direct activeren.
    }

    adminBtn.addEventListener("click", async () => {
        if (!adminMode) {
            const password = prompt("Voer beheerderswachtwoord in:");
            if (!password) return;

            if (await verifyAdminPassword(password)) {
                enableAdminMode();
            } else {
                alert("Onjuist wachtwoord!");
            }
        } else {
            disableAdminMode();
        }
    });
};

// Zet beheermodus aan, pas knopstijl aan en injecteer bewerkingsknoppen in de pagina.
const enableAdminMode = (skipAlert = false) => {
    adminMode = true;
    localStorage.setItem('adminModeEnabled', 'true');
    document.body.classList.add('admin-mode');

    const adminBtn = document.getElementById('adminBtn');
    if (adminBtn) {
        adminBtn.innerHTML = '<i class="fas fa-lock-open"></i>';
        adminBtn.style.background = 'linear-gradient(135deg, #52c234 0%, #27ae60 100%)';
        adminBtn.style.boxShadow = '0 4px 12px rgba(39, 174, 96, 0.4)';
        adminBtn.title = 'Beheermodus verlaten';
    }

    // Maak alle tabelcellen inline bewerkbaar.
    makeTableEditable();

    // Voeg beheeracties toe zoals rij/kolom/tabelbeheer.
    addAdminControls();

    if (!skipAlert) {
        alert('Beheermodus ingeschakeld!');
    }
};
// Zet beheermodus uit en herstel de normale weergave.
const disableAdminMode = () => {
    adminMode = false;
    localStorage.removeItem('adminModeEnabled');
    document.body.classList.remove('admin-mode');

    const adminBtn = document.getElementById('adminBtn');
    if (adminBtn) {
        adminBtn.innerHTML = '<i class="fas fa-lock"></i>';
        adminBtn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        adminBtn.style.boxShadow = '0 4px 12px rgba(102, 126, 234, 0.4)';
        adminBtn.title = 'Beheermodus';
    }

    // Verwijder dynamisch ingevoegde beheerknoppen.
    removeAdminControls();

    // Herlaad pagina om editable status en tijdelijke wijzigingen visueel op te schonen.
    location.reload();
};

// Markeer elke tabelcel als bewerkbaar en sla wijzigingen op bij verlies van focus.
const makeTableEditable = () => {
    const table = document.querySelector('.data-table');
    if (!table) return;

    const isIdColumn = (columnName) => {
        if (!columnName) return false;
        const normalized = String(columnName).toLowerCase();
        return normalized === 'id' || normalized.endsWith('_id') || normalized.endsWith('id');
    };

    const cells = table.querySelectorAll('tbody td');
    cells.forEach(cell => {
        const row = cell.closest('tr');
        const cellIndex = Array.from(row.children).indexOf(cell);
        const headerCell = table.querySelector(`thead th:nth-child(${cellIndex + 1})`);
        const columnName = headerCell?.dataset?.column || '';

        if (isIdColumn(columnName)) {
            cell.contentEditable = false;
            cell.classList.remove('editable-cell');
            return;
        }

        cell.contentEditable = true;
        cell.classList.add('editable-cell');

        cell.addEventListener('blur', async function () {
            const row = this.closest('tr');
            const cellIndex = Array.from(row.children).indexOf(this);
            const headerCell = table.querySelector(`thead th:nth-child(${cellIndex + 1})`);
            const columnName = headerCell.dataset.column;

            // Eerste kolom wordt als rij-id gebruikt voor bijwerken op de server.
            const idCell = row.querySelector('td:first-child');
            const idValue = idCell.textContent.trim();

            const tableName = document.body.dataset.currentTable;
            const newValue = this.textContent.trim();

            const success = await updateCell(tableName, columnName, newValue, idValue);
            if (success) {
                this.classList.add('cell-updated');
                setTimeout(() => this.classList.remove('cell-updated'), 1000);
            } else {
                alert('Bijwerken van cel mislukt');
            }
        });
    });
};

// Verstuur een celwijziging naar de server en geef terug of het bijwerken is geslaagd.
const updateCell = async (table, column, value, idValue) => {
    try {
        const formData = new FormData();
        formData.append('action', 'update_cell');
        formData.append('table', table);
        formData.append('column', column);
        formData.append('value', value);
        formData.append('id_column', 'id');
        formData.append('id_value', idValue);

        const response = await fetch('admin_handler.php', {
            method: 'POST', body: formData
        });

        const result = await response.json();
        return result.success;
    } catch (error) {
        console.error('Update error:', error);
        return false;
    }
};

// Voeg de volledige beheertoolbar in aan het begin van de data-sectie.
const addAdminControls = () => {
    const dataSection = document.querySelector('section.Data');
    if (!dataSection) return;

    const controlsDiv = document.createElement('div');
    controlsDiv.className = 'admin-controls';
    controlsDiv.innerHTML = `
        <div class="admin-buttons">
            <button class="admin-btn" onclick="addNewRow()">
                <i class="fas fa-plus"></i> Rij toevoegen
            </button>
            <button class="admin-btn" onclick="showDeleteRowDialog()">
                <i class="fas fa-trash"></i> Rij verwijderen
            </button>
            <button class="admin-btn" onclick="showAddColumnDialog()">
                <i class="fas fa-columns"></i> Kolom toevoegen
            </button>
            <button class="admin-btn" onclick="showDeleteColumnDialog()">
                <i class="fas fa-minus"></i> Kolom verwijderen
            </button>
            <button class="admin-btn" onclick="showCreateTableDialog()">
                <i class="fas fa-table"></i> Nieuwe tabel
            </button>
            <button class="admin-btn danger" onclick="showDeleteTableDialog()">
                <i class="fas fa-exclamation-triangle"></i> Tabel verwijderen
            </button>
            <button class="admin-btn" onclick="regeneratePages()">
                <i class="fas fa-sync"></i> Pagina's opnieuw genereren
            </button>
        </div>
    `;

    dataSection.insertBefore(controlsDiv, dataSection.firstChild);
};

// Verwijder de beheerknoppen uit de DOM wanneer adminmodus uitgaat.
const removeAdminControls = () => {
    const controls = document.querySelector('.admin-controls');
    if (controls) {
        controls.remove();
    }
};

// Voeg een lege rij toe aan de huidige tabel.
const addNewRow = async () => {
    const tableName = document.body.dataset.currentTable;
    if (!tableName) return;

    const formData = new FormData();
    formData.append('action', 'add_row');
    formData.append('table', tableName);

    try {
        const response = await fetch('admin_handler.php', {
            method: 'POST', body: formData
        });

        const result = await response.json();
        if (result.success) {
            // Beheermodus komt na herladen terug via localStorage.
            location.reload();
        } else {
            alert('Rij toevoegen mislukt: ' + result.error);
        }
    } catch (error) {
        alert('Fout bij toevoegen van rij');
    }
};

// Vraag de gebruiker om een rij-id en bevestig verwijdering.
const showDeleteRowDialog = () => {
    const rowId = prompt('Voer de ID in van de rij die je wilt verwijderen:');
    if (!rowId) return;

    if (confirm(`Weet je zeker dat je de rij met ID ${rowId} wilt verwijderen?`)) {
        deleteRow(rowId);
    }
};

// Verwijder een specifieke rij op basis van de opgegeven id.
const deleteRow = async (idValue) => {
    const tableName = document.body.dataset.currentTable;

    const formData = new FormData();
    formData.append('action', 'delete_row');
    formData.append('table', tableName);
    formData.append('id_column', 'id');
    formData.append('id_value', idValue);

    try {
        const response = await fetch('admin_handler.php', {
            method: 'POST', body: formData
        });

        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Rij verwijderen mislukt: ' + result.error);
        }
    } catch (error) {
        alert('Fout bij verwijderen van rij');
    }
};

// Dialoog voor toevoegen van een nieuwe kolom inclusief datatype.
const showAddColumnDialog = () => {
    const columnName = prompt('Voer een nieuwe kolomnaam in (bijv. email, telefoon):');
    if (!columnName) return;

    const columnType = prompt('Voer kolomtype in (standaard: VARCHAR(255)):', 'VARCHAR(255)');
    if (!columnType) return;

    addColumn(columnName, columnType);
};

// Voeg een kolom toe aan de huidige tabelstructuur via backendactie.
const addColumn = async (columnName, columnType) => {
    const tableName = document.body.dataset.currentTable;

    const formData = new FormData();
    formData.append('action', 'add_column');
    formData.append('table', tableName);
    formData.append('column_name', columnName);
    formData.append('column_type', columnType);

    try {
        const response = await fetch('admin_handler.php', {
            method: 'POST', body: formData
        });

        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Kolom toevoegen mislukt: ' + result.error);
        }
    } catch (error) {
        alert('Fout bij toevoegen van kolom');
    }
};

// Dialoog voor het verwijderen van een kolom met extra bevestiging.
const showDeleteColumnDialog = () => {
    const columnName = prompt('Voer de naam in van de kolom die je wilt verwijderen:');
    if (!columnName) return;

    if (confirm(`Weet je zeker dat je kolom "${columnName}" wilt verwijderen? Dit kan niet ongedaan worden gemaakt!`)) {
        deleteColumn(columnName);
    }
};

// Verwijder kolom uit de huidige tabel.
const deleteColumn = async (columnName) => {
    const tableName = document.body.dataset.currentTable;

    const formData = new FormData();
    formData.append('action', 'delete_column');
    formData.append('table', tableName);
    formData.append('column_name', columnName);

    try {
        const response = await fetch('admin_handler.php', {
            method: 'POST', body: formData
        });

        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Kolom verwijderen mislukt: ' + result.error);
        }
    } catch (error) {
        alert('Fout bij verwijderen van kolom');
    }
};

// Vraag een nieuwe tabelnaam en bevestig de creatie.
const showCreateTableDialog = () => {
    const tableName = prompt('Voer een nieuwe tabelnaam in (bijv. projecten, klanten):');
    if (!tableName) return;

    if (confirm(`Nieuwe tabel "${tableName}" maken met standaardkolommen (id, naam, aangemaakt_op)?`)) {
        createTable(tableName);
    }
};

// Maak een nieuwe tabel aan en navigeer daarna naar de nieuw aangemaakte pagina.
const createTable = async (tableName) => {
    const formData = new FormData();
    formData.append('action', 'create_table');
    formData.append('table_name', tableName);

    try {
        const response = await fetch('admin_handler.php', {
            method: 'POST', body: formData
        });

        const result = await response.json();
        if (result.success) {
            // Regeneratie gebeurt automatisch zodat direct een bijpassend .php-bestand ontstaat.
            await regeneratePagesQuietly();

            alert('Tabel gemaakt! In beheermodus kunnen rijen en kolommen worden beheerd.');
            window.location.href = `${tableName}.php`; // Navigeer direct naar de nieuwe tabelpagina.
        } else {
            alert('Tabel maken mislukt: ' + result.error);
        }
    } catch (error) {
        alert('Fout bij maken van tabel');
    }
};

// Voer paginaregeneratie uit zonder extra meldingen; nuttig voor interne flows.
const regeneratePagesQuietly = async () => {
    try {
        const response = await fetch('generate_pages.php', {
            method: 'POST'
        });
        const result = await response.json();
        return result.success;
    } catch (error) {
        console.error('Stille regeneratiefout:', error);
        return false;
    }
};

    // Bevestigingsstap voor verwijderen van complete tabel.
const showDeleteTableDialog = () => {
    const tableName = document.body.dataset.currentTable;

    const confirmation = prompt(`Typ "${tableName}" om het verwijderen van deze tabel te bevestigen:`);
    if (confirmation !== tableName) {
        alert('Tabelnaam komt niet overeen. Verwijderen geannuleerd.');
        return;
    }

    deleteTable(tableName);
};

// Verwijder de tabel in de database, regenereer pagina's en ga terug naar de startpagina.
const deleteTable = async (tableName) => {
    const formData = new FormData();
    formData.append('action', 'delete_table');
    formData.append('table_name', tableName);

    try {
        const response = await fetch('admin_handler.php', {
            method: 'POST', body: formData
        });

        const result = await response.json();
        if (result.success) {
            await regeneratePagesQuietly();

            await deletePhysicalFile(tableName);

            alert('Tabel succesvol verwijderd!');
            window.location.href = 'index.php';
        } else {
            alert('Tabel verwijderen mislukt: ' + result.error);
        }
    } catch (error) {
        alert('Fout bij verwijderen van tabel');
    }
};

// Probeer het fysieke paginabestand te verwijderen dat bij de tabel hoort.
const deletePhysicalFile = async (tableName) => {
    try {
        const formData = new FormData();
        formData.append('action', 'delete_file');
        formData.append('file_name', `${tableName}.php`);

        await fetch('admin_handler.php', {method: 'POST', body: formData});
    } catch (error) {
        console.error('Fout bij verwijderen van bestand:', error);

    }
};

// Initialiseer beheermodusfunctionaliteit zodra de DOM beschikbaar is.
document.addEventListener('DOMContentLoaded', () => {
    initAdminButton();
});

// Handmatige beheertaak: alle tabelpagina's opnieuw opbouwen en verweesde bestanden opruimen.

const regeneratePages = async () => {
    if (!confirm('Dit genereert alle tabelpagina\'s opnieuw en ruimt ongebruikte bestanden op. Doorgaan?')) {
        return;
    }

    try {
        const response = await fetch('generate_pages.php', {
            method: 'POST'
        });

        const result = await response.json();

        if (result.success) {
            let message = `Gelukt!\n\n${result.count} paginabestand(en) gegenereerd:\n${result.files.join('\n')}`;

            if (result.deletedCount > 0) {
                message += `\n\n${result.deletedCount} ongebruikt(e) bestand(en) opgeschoond:\n${result.deleted.join('\n')}`;
            }

            alert(message);
            location.reload();
        } else {
            alert('Pagina\'s opnieuw genereren mislukt: ' + result.error);
        }
    } catch (error) {
        console.error('Regeneration error:', error);
        alert('Fout bij opnieuw genereren van pagina\'s');
    }
};