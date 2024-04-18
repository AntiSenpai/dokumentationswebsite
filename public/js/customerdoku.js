document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('#sidebar .sidenav-link');
    const contentSections = document.querySelectorAll('.content-section');
    const backButton = document.getElementById('backButton');
    const editButton = document.getElementById('editButton');
    const customerId = '{{ customer.id }}';
    let editMode = localStorage.getItem('editMode') === 'true';
    let counter = 0;

    document.querySelectorAll('.section .card').forEach(card => {
        const sectionType = card.closest('.section').getAttribute('data-section-type');
        const cardId = card.getAttribute('data-card-id');
        
        // Suche nach spezifischen Daten für diese Karte basierend auf cardId und sectionType
        const specificData = documentationData.find(d => d.cardId === cardId && d.sectionType === sectionType);
        
        // Entscheide, ob es eine Tabelle oder eine editierbare Karte ist, und initialisiere entsprechend
        if (card.classList.contains('editable-card-table')) {
            initializeEditorJSForTables(card, specificData ? specificData.content : getInitialDataForCard(card, {}));
        } else {
            initializeEditorJSForEditableCards(card, specificData ? specificData.content : getInitialDataForCard(card, {}));
        }
    });

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                adjustTableHeight();
            }
        });
    });

    document.querySelectorAll('.section .card').forEach(card => {
            const sectionType = card.closest('.section').getAttribute('data-section-type');
            const cardId = `${customerId}-${sectionType}-${++counter}`;
            card.setAttribute('data-card-id', cardId);
            card.setAttribute('data-section-type', sectionType);

        });

    document.querySelectorAll('.card-body .ce-table__content').forEach(table => {
        observer.observe(table, { childList: true, subtree: true });
    });
    
    function adjustTableSize() {
        document.querySelectorAll('.card-body .ce-table').forEach(table => {
            table.style.width = '100%';
        });
    }
    
    function hideAllSections() {
        contentSections.forEach(section => section.style.display = 'none');
    }

    function showSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.style.display = 'block';
        }
    }

        function adjustTableHeight() {
        document.querySelectorAll('.card-body .ce-table__content').forEach(table => {
            let cardBodyHeight = table.closest('.card-body').clientHeight;
            table.style.height = cardBodyHeight + 'px';

            let tableHeight = table.scrollHeight;
            if(tableHeight > cardBodyHeight) {
                table.closest('.card').style.height = (table.closest('.card').clientHeight + (tableHeight - cardBodyHeight)) + 'px';
            }
        });
    }

    function updateEditModeUI() {
    editButton.textContent = editMode ? 'Bearbeitungsmodus aktiv' : 'Bearbeitungsmodus inaktiv';
    editButton.style.color = editMode ? 'lightgreen' : 'lightcoral';
    document.querySelectorAll('.addCardBtn, .save-btn, .delete-btn').forEach(btn => {
        btn.style.display = editMode ? 'block' : 'none';
    });

    // SweetAlert2 Toast anzeigen
    const message = editMode ? 'Bearbeitungsmodus aktiviert' : 'Bearbeitungsmodus deaktiviert';
    const icon = editMode ? 'success' : 'info';
    const duration = 1000; // Anzeigedauer in Millisekunden

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: message,
        showConfirmButton: false,
        timer: duration
    });
}

    function toggleEditableCards() {
        const editableCards = document.querySelectorAll('.editable-card, .editable-card-table');
        editableCards.forEach(card => {
            if (card.editorInstance && typeof card.editorInstance.readOnly !== 'undefined') {
                card.editorInstance.readOnly.toggle(!editMode);
            }
            // Hier könnte man weitere Aktionen durchführen, die abhängig vom editMode sind
        });
    }

    function saveCardData(card) {
    const cardId = card.getAttribute('data-card-id');
    const customerId = '{{ customer.id }}';
    const sectionType = card.getAttribute('data-section-type');

    card.editorInstance.save().then((outputData) => {
        fetch('/doku/save-card', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                customerId,
                cardId,
                sectionType,
                content: outputData,
            }),
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Gespeichert',
                text: 'Die Daten wurden erfolgreich gespeichert.',
            });
        })
        .catch((error) => {
            console.error('Fehler:', error);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Fehler',
                text: 'Beim Speichern der Daten ist ein Fehler aufgetreten.',
            });
        });
    });
}

      function initializeDeleteButton(card) {
        const cardHeader = card.querySelector('.card-header');
        if (!cardHeader) {
            console.error('Card-Header-Element nicht gefunden in', card);
            return;
        }
        let deleteIcon = cardHeader.querySelector('.delete-btn');
        if (!deleteIcon) {
            deleteIcon = document.createElement('button');
            deleteIcon.className = ' fas fa-trash delete-btn';
            deleteIcon.title = 'Löschen';
            deleteIcon.style.cssText = 'cursor: pointer; float: right; width: 20px; border: none; background-color: transparent; height: 20px; color: #8B0000; display: none;';
            cardHeader.appendChild(deleteIcon);

            deleteIcon.addEventListener('click', function(event) {
                                event.stopPropagation();
                                if (!confirm('Willst du dieses Element löschen?')) return;
                                card.remove();
                            });
                        }
                    }
                    
                    function initializeEditorJSForEditableCards(card, data) {
                        const cardBody = card.querySelector('.card-body');
                        if(!cardBody) { console.error('Karten Element nicht gefunden in', card); return; }

                        card.editorInstance = new EditorJS({
                            holder: cardBody,
                            tools: {
                                header: Header,
                                list: List,
                                image: SimpleImage,
                                paragraph: {
                                    config: {
                                        placeholder: 'Hier Topologie zur Sektion ...'
                                    }
                                }
                            },
                            data: data,
                            readOnly: !editMode,
                        
                        });
                    }

                    function initializeEditorJSForTables(card, data) {
                        const cardBody = card.querySelector('.card-body');
                        if (!cardBody) { console.error('Karten Element nicht gefunden in', card); return; }

                        const initialData = getInitialDataForCard(card);

                        card.editorInstance = new EditorJS({
                            holder: cardBody,
                            tools: { table: Table},
                            data: data,
                            readOnly: !editMode,
                            onReady: function() {

                                const addButton = cardBody.querySelector('.ce-toolbar--opened');
                                if(addButton) {
                                addButton.style.display = "none !important";
                                addButton.parentElement.style.display = "none !important";
                                addButton.style.opacity = "0";
                                addButton.style.position = "absolute";
                                }
                                const emptyBlock = cardBody.querySelector('.ce-block .ce-paragraph');
                                if (emptyBlock && emptyBlock.innerHTML.trim() === '') {
                                    emptyBlock.parentNode.parentNode.remove();
                                }
                            },
                        });
                    }


                    function getInitialDataForCard(card, customerData = {}) {
                    let initialData = { blocks: [] };
                    const section = card.closest('.section');

                    if (!section) return initialData;

                    // Bestimmen Sie die Sektion des Cards und verwenden Sie Kundendaten, wenn vorhanden
                    const sectionType = section.getAttribute('data-section-type');
                    const dataForSection = customerData[sectionType];

                    if (dataForSection) {
                        // Verwenden Sie die vorhandenen Kundendaten, um die Tabelle zu füllen
                        initialData.blocks.push({
                            type: "table",
                            data: {
                                withHeadings: true,
                                content: dataForSection.content // Stellen Sie sicher, dass dataForSection.content das richtige Format hat
                            }
                        });
                    } else {
                        // Standardvorlagen für verschiedene Sektionen
                        switch (sectionType) {
                            case 'allgemein':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                            ["Ansprechpartner", "Telefon", "Mobil", "E-Mail"],
                                            ["Technisch:", "", "", ""],
                                            ["Vor Ort:", "", "", ""]
                                        ]
                                    }
                                });
                                break;
                            case 'netz':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                            ["Netzwerk (IP)", "Domäne", "Gateway", "DNS"],
                                            ["", "", "", ""]
                                        ]
                                    }
                                });
                                break;
                            case 'server':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                            ["Server", "IP", "Admin-User/Passwort", "Zugriff"],
                                            ["", "", "", ""]
                                        ]
                                    }
                                });
                                break;
                            case 'clients':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                            ["Hersteller", "IP-Range/DHCP", "Anzahl/Alter", "Sonstiges"],
                                            ["", "", "", ""]
                                        ]
                                    }
                                });
                                break;
                            case 'userPwd':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                            ["Domänenbenutzer", "Passwort", "Benutzer (falls bekannt)", "Sonstiges"],
                                            ["", "", "", ""]
                                        ]
                                    }
                                });
                                break;
                            case 'routerFirewall':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                            ["Typ", "IP", "Passwörter", "Zugriff"],
                                            ["", "", "", ""]
                                        ]
                                    }
                                });
                                break;
                            case 'provider':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                        ["Provider-DSL", "Benutzername", "Passwort", "Tarif/Sonstiges"],
                                        ["", "", "", ""],
                                        ["Provider-Mail", "Benutzername", "Passwort", "pop3/smtp-Server"],
                                        ["", "", "", ""],
                                        ["Provider-WWW", "Benutzername", "Passwort", "Admin-URL"],
                                        ["", "", "", ""]
                                    ]
                                    }
                                });
                                break;
                            case 'remoteMaintenance':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                        ["VPN-Gateway (IP)", "VPN-Typ", "User / Passwort", "PSK"],
                                        ["", "", "", ""],
                                        ["Typ (RDP/VNC)", "IP / DNS-Name", "Admin-User / Passwort", "Zugriff (VPN/Direkt)"],
                                        ["", "", "", ""]
                                    ]
                                    }
                                });
                                break;
                            case 'backup':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                        ["Software", "Server", "Hardware", "Anmerkungen"],
                                        ["", "", "", ""]
                                    ]
                                    }
                                });
                                break;
                            case 'ups':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                        ["Netzwerk (IP)", "Domäne", "Gateway", "DNS"],
                                        ["", "", "", ""]
                                    ]
                                    }
                                });
                                break;
                            case 'antivirus':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                        ["Software", "Server/Clients", "Admin-User/Passwort", "Version / Sonstiges"],
                                        ["", "", "", ""]
                                    ]
                                    }
                                });
                                break;
                            case 'applicationSoftware':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                        ["Software", "Server/Clients", "Admin-User/Passwort", "Version / Sonstiges"],
                                        ["", "", "", ""]
                                    ]
                                    }
                                });
                                break;
                            case 'otherInfo':
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        withHeadings: true,
                                        content: [
                                        ["Sonstige Anwendungen/Informationen"],
                                        [""]
                                    ]
                                    }
                                });
                                break;
                            default:
                                // Fallback für nicht spezifizierte Sektionen
                                initialData.blocks.push({
                                    type: "table",
                                    data: {
                                        content: [
                                            ["Information nicht verfügbar"]
                                        ]
                                    }
                                });
                        }
                    }

                    return initialData;
                }


    function initializeCardsWithData(card, doc) {
     documentationData.forEach(doc => {
        const cardSelector = `[data-section-type="${doc.sectionType}"][data-card-id="${doc.cardId}"]`;
        const card = document.querySelector(cardSelector);
        if(card) {
            const data = doc.content ? { blocks: doc.content } : null;
            if(card.classList.contains('editable-card-table')) {
                initializeEditorJSForTables(card, data);
            } else {
                initializeEditorJSForEditableCards(card, data);
            }
        }
     });
    }


    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    function isValidIP(ip) {
        const re = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        return re.test(ip);
    }

    function isValidPhoneNumber(phone) {
        const re = /^(\+\d{1,2}\s?)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/;
        return re.test(phone);
    }

    function initializeSaveButton(card) {
        const cardHeader = card.querySelector('.card-header');
        if (!cardHeader) {
            console.error('Card-Header-Element nicht gefunden in', card);
            return;
        }
        let saveIcon = cardHeader.querySelector('.save-btn');
        if(!saveIcon) {
            saveIcon = document.createElement('button');
            saveIcon.className = 'fas fa-save fa-2xl save-btn';
            saveIcon.title = 'Speichern';
            saveIcon.style.cssText = 'cursor: pointer; float: right; margin-left: 20px; border: none; background-color: transparent; height: 20px; display: none; color: #228B22;';
            cardHeader.appendChild(saveIcon);

            saveIcon.addEventListener('click', function() {
                saveCardData(card);
            });
        }
    }
    
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            hideAllSections();
            const sectionId = this.getAttribute('href').substring(1);
            showSection(sectionId);
            sidebarLinks.forEach(lnk => lnk.parentElement.classList.remove('active'));
            this.parentElement.classList.add('active');
        });
    });

    backButton.addEventListener('click', function() {
        editMode = false;
        localStorage.setItem('editMode', editMode);
        updateEditModeUI();
        toggleEditableCards();
    });

    const deleteButtons = document.querySelectorAll('.editable-card-table .editable-card .delete-btn');
        deleteButtons.forEach(btn => {
            btn.style.display = editMode ? 'block' : 'none';
            btn.addEventListener('click', function(event) {
                event.stopPropagation();
                if (!confirm('Confirm deletion')) return;
                btn.closest('.card').remove();
            });
        });

    editButton.addEventListener('click', function() {
        editMode = !editMode;
        localStorage.setItem('editMode', editMode);
        updateEditModeUI();
        toggleEditableCards();
    });

    document.querySelectorAll('.editable-card-table, .editable-card').forEach(card => {
        if(card.classList.contains('editable-card-table')) {
            initializeEditorJSForTables(card);
            console.log("Erfolgreich!");
        } else if(card.classList.contains('editable-card')) {
            initializeEditorJSForEditableCards(card); 
        }
        initializeDeleteButton(card);
        initializeSaveButton(card);
    });


    document.querySelectorAll('.addCardBtn').forEach(addCardBtn => {
        addCardBtn.addEventListener('click', function() {
            if (!editMode) return;

            let cardTitle = prompt("Geben Sie den Titel der neuen Karte ein", "Neue Karte");
            let newCard = document.createElement('div');
            newCard.className = 'card editable-card';
            newCard.innerHTML = `<div class="card-header">${cardTitle}<button class="delete-btn" style="display: none;"><i class="fa-solid fa-circle-xmark"></i></button></div>` +
                                `<div class="card-body"></div>`;

            this.closest('.content-section').querySelector('.section').appendChild(newCard);
            initializeDeleteButton(newCard);
            initializeSaveButton(newCard);

            const editor = new EditorJS({
                holder: newCard.querySelector('.card-body'),
                tools: {
                    header: Header,
                    list: List,
                    image: SimpleImage,
                    table: Table
                }
            });
            newCard.editorInstance = editor;
        });
    });

    hideAllSections();
    updateEditModeUI();
    adjustTableSize();
    adjustTableHeight();
    toggleEditableCards();
    initializeCardsWithData();
    showSection('allgemeinContent');
    document.querySelector('#sidebar .sidenav-link[href="#allgemeinContent"]').parentElement.classList.add('active');
});