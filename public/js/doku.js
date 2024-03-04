function customernew() {
    Swal.fire({
        title: 'Neuen Kunden anlegen',
        html: `
            <form id="create-customer-form">
                <div class="form-group">
                    <label for="name">Name/Rechtsform:</label>
                    <input type="text" class="form-control" id="name" required>
                </div>
                <div class="form-group">
                    <label for="adresse">Hauptstandort Adresse:</label>
                    <input type="text" class="form-control" id="adresse" required>
                </div>
                <div class="form-group">
                    <label for="technischer-ansprechpartner">Ansprechpartner technisch:</label>
                    <select class="form-control" id="technischer-ansprechpartner">
                        <!-- Dynamisch geladene User-Optionen -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="vor-ort-ansprechpartner">Ansprechpartner vor Ort:</label>
                    <input type="text" class="form-control" id="vor-ort-ansprechpartner" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" required>
                </div>
                <div class="form-group">
                    <label for="stundensatz">Stundensatz:</label>
                    <input type="number" step="0.01" class="form-control" id="stundensatz" required>
                </div>
                <div id="unterstandorte-container">
                <!-- Container für Unterstandorte -->
            </div>
            </form>
            <button type="button" id="add-unterstandort" class="btn btn-info mt-2">+ Unterstandort hinzufügen</button>
        `,
        showCancelButton: true,
        confirmButtonText: 'Erstellen',
        confirmButtonColor: '#3085d6',
        cancelButtonText: 'Abbrechen',
        cancelButtonColor: '#d33',
        focusConfirm: false,
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
    fetch('/doku/api/customers')
        .then(response => response.json())
        .then(data => {
            const listContainer = document.getElementById('customerList');
            if (!listContainer) {
                console.error('Element mit der ID "customer-list" wurde nicht gefunden.');
                return;
            }

            // Leeren der aktuellen Liste vor dem Hinzufügen neuer Elemente
            listContainer.innerHTML = '';

            data.forEach((customer, index) => {
                // Erstellen des Links
                const customerLink = document.createElement('a');
                customerLink.href = `/customer_detail/${customer.id}`; // Pfad anpassen, falls nötig

                // Erstellen des Kunden-Divs
                const customerDiv = document.createElement('div');
                customerDiv.className = `customer-entry ${index % 2 !== 0 ? 'customer-odd' : ''}`;

                // Formatieren des Datums
                const createdAt = new Date(customer.createdAt).toLocaleDateString('de-DE');
                const updatedAt = new Date(customer.updatedAt).toLocaleString('de-DE');

                // Setzen des inneren HTMLs des Kunden-Divs
                customerDiv.innerHTML = `
                    <div>${customer.id}</div>
                    <div class="name-or-searchnum" data-name="${customer.name}" data-searchnum="${customer.suchnummer}">
                        ${customer.name}
                    </div>
                    <div>${createdAt}</div>
                    <div>${updatedAt}</div>
                    <div>${customer.updatedBy.username}</div>
                `;

                // Hinzufügen des Kunden-Divs zum Link und dann zum Container
                customerLink.appendChild(customerDiv);
                listContainer.appendChild(customerLink);
            });
        })
        .catch(error => console.error('Fehler beim Aktualisieren der Kundenliste', error));
}

      


    document.addEventListener('DOMContentLoaded', function () {
        const refreshButton = document.getElementById('refreshCustomerList');
        refreshButton.addEventListener('click', function () {
            updateCustomerList();
            Swal.fire({
                toast: true,
                title: 'Kundenliste aktualisiert',
                icon: 'success',
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            })
        });
    });

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

    // Attach an event listener to the select dropdown
    displayOptionSelect.addEventListener('change', function() {
        updateDisplay();
    }); 

    // Initial display update
    updateDisplay();
    });

    document.getElementById('customerSearch').addEventListener('input', function(e) {
        var searchValue = e.target.value.toLowerCase();
        document.querySelectorAll('.customer-entry').forEach(function(customer) {
            var name = customer.querySelector('.customer-name').textContent.toLowerCase();
            customer.style.display = name.includes(searchValue) ? 'block' : 'none';
        });
    });