function customernew() {
    Swal.fire({
        title: 'Neuen Kunden anlegen',
        html: `
            <div style="text-align: center;">
                <form id="create-customer-form" style="margin: 0 auto; max-width: 500px;">
                    <div class="form-group">
                        <input type="text" class="form-control" id="name" placeholder="Name/Rechtsform" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="adresse" placeholder="Hauptstandort Adresse" required>
                    </div>
                    <div class="form-group">
                        <select class="form-control" id="technischer-ansprechpartner">
                            <!-- Dynamisch geladene User-Optionen -->
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="vor-ort-ansprechpartner" placeholder="Ansprechpartner vor Ort" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" id="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="number" step="0.01" class="form-control" id="stundensatz" placeholder="Stundensatz" required>
                    </div>
                    <div id="unterstandorte-container">
                        <!-- Container für Unterstandorte -->
                    </div>
                    <button type="button" id="add-unterstandort" class="btn btn-primary mt-2" style="width: 100%;">+ Unterstandort hinzufügen</button>
                </form>
            </div>
        `,
        showCancelButton: false,
        confirmButtonText: 'Erstellen',
        confirmButtonColor: '#3085d6',
        focusConfirm: false,
        onBeforeOpen: () => {
            document.getElementById('name').focus();
        },
        preConfirm: () => {
            const name = document.getElementById('name').value;
            const adresse = document.getElementById('adresse').value;
            const technischerAnsprechpartner = document.getElementById('technischer-ansprechpartner').value;
            const vorOrtAnsprechpartner = document.getElementById('vor-ort-ansprechpartner').value;
            const email = document.getElementById('email').value;
            const stundensatz = document.getElementById('stundensatz').value;
            const unterstandorteContainer = document.getElementById('unterstandorte-container');
            
            const unterstandorte = [];
            document.querySelectorAll('.unterstandort-stadt').forEach(element => {
                unterstandorte.push({ adresse: element.value});
            });

            
            if (!name || !technischerAnsprechpartner || !vorOrtAnsprechpartner || !email || !stundensatz) {
                Swal.showValidationMessage("Bitte fülle alle Pflichtfelder aus!");
                return false;
            }
            
            return { 
                name: document.getElementById('name').value,
                adresse: document.getElementById('adresse').value,
                technischerAnsprechpartner: document.getElementById('technischer-ansprechpartner').value,
                vorOrtAnsprechpartner: document.getElementById('vor-ort-ansprechpartner').value,
                email: document.getElementById('email').value,
                stundensatz: parseFloat(document.getElementById('stundensatz').value),
                unterstandorte: unterstandorte
            };
        },
        didOpen: () => {
            document.getElementById('add-unterstandort').addEventListener('click', () => {
                const container = document.getElementById('unterstandorte-container');
                const unterstandortHTML = `
                    <div class="form-group mt-2">
                        <input type="text" class="form-control unterstandort-stadt" placeholder="Unterstandort Stadt" required>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', unterstandortHTML);
            });
            fetch('/doku/user') // Pfad zur neuen Backend-Route
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('technischer-ansprechpartner');
                // Optionen basierend auf den User-Daten erstellen
                data.forEach(user => {
                    const option = new Option(user.username, user.id);
                    select.appendChild(option);
                });
            })
            .catch(error => console.error('Fehler beim Laden der User:', error));
        }
        }).then((result) => {
        if (result.isConfirmed && result.value) {
            fetch('/doku/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(result.value)
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Erfolgreich',
                            text: 'Der Kunde wurde angelegt.',
                            textcolor: 'green',
                            icon: 'success'
                        }).then(() => {
                            updateCustomerList();
                        });
                    } else {
                        Swal.fire({
                            title: 'Fehler',
                            text: 'Es ist ein Fehler aufgetreten.',
                            textcolor: 'red',
                            icon: 'error'
                        }).then(() => {
                            updateCustomerList();
                        });
                    } 
                })
                .catch((error) => {
                    Swal.fire({
                        title: 'Fehler',
                        text: 'Es ist ein Fehler aufgetreten.',
                        textcolor: 'red',
                        icon: 'error'
                    }).then(() => {
                        updateCustomerList();
                    });
                });
        }
    });
}

function updateCustomerList() {
    history.go(0);
}

    document.addEventListener('DOMContentLoaded', function () {
        const refreshButton = document.getElementById('refreshCustomerList');
        refreshButton.addEventListener('click', function () {
            Swal.fire({
                toast: true,
                title: 'Aktualisierung wird ausgeführt...',
                icon: 'success',
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
            }).then(() => {
                updateCustomerList();
            });
        });
    });

    function searchCustomers(searchTerm) {
        const url = new URL(window.location.origin + '/doku/api/kunden/suche');
        url.searchParams.append('search', searchTerm);
    
        fetch(url)
        .then(response => response.json())
        .then(data => {
            const customerList = document.querySelector('.customer-list');
            // Löscht nur die Kunden-Einträge und behält den Header
            const entries = customerList.querySelectorAll('.customer-entry-link');
            entries.forEach(entry => entry.remove());
    
            if (data.length === 0) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Kunde nicht gefunden',
                    showConfirmButton: false,
                    timer: 800,
                })
            } else {
                data.forEach((customer, index) => {
                    const customerEntryLink = document.createElement('a');
                    customerEntryLink.href = `/doku/customer/${customer.id}`;
                    customerEntryLink.className = 'customer-entry-link';
    
                    const customerEntry = document.createElement('div');
                    customerEntry.className = `customer-entry ${index % 2 === 0 ? '' : 'customer-odd'}`;
    
                    customerEntry.innerHTML = `
                        <div>${customer.id}</div>
                        <div class="name-or-searchnum" data-name="${customer.name}" data-searchnum="${customer.suchnummer}">
                            ${customer.name}
                        </div>
                        <div>${customer.createdAt}</div>
                        <div>${customer.updatedAt}</div>
                        <div>${customer.updatedBy}</div>
                    `;
    
                    customerEntryLink.appendChild(customerEntry);
                    customerList.appendChild(customerEntryLink);
                });
            }
        })
        .catch(error => console.error('Fehler beim Laden der Kunden:', error));
    }
    
    
    

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('addCustomerButton').addEventListener('click', customernew);
});

document.addEventListener('DOMContentLoaded', function () {
    const displayOptionSelect = document.getElementById('displayOption');
    const nameOrSearchnumElements = document.querySelectorAll('.name-or-searchnum');

    // Function to update the display based on the selected option
    function updateDisplay() {
        const selectedOption = displayOptionSelect.value;
        nameOrSearchnumElements.forEach(element => {
            element.textContent = element.dataset[selectedOption];
        });
    }

    // Initial display update
    updateDisplay();
});

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customerSearch');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value;
        searchCustomers(searchTerm);
    });
})

    