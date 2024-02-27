function createCustomerModal() {
    const generateRandomNumber = () => `K${Math.floor(Math.random() * 100000000)}`;
    let standorte = [];

    Swal.fire({
        title: 'Neuen Kunden erstellen',
        html: `
      <form id="create-customer-form">
        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" class="form-control" id="name" required>
        </div>
        <div class="form-group">
          <label for="suchnummer">Suchnummer:</label>
          <span id="suchnummer" class="form-control"></span>
        </div>
        <div class="form-group">
            <label for="standort">Hauptstandort:</label>
            <input type="text" class="form-control" id="standort" required>
        </div>
        <div id="standorte-container">
            <div class="form-group">
                <label for="standorte">Unterstandort:</label>
                <input type="text" class="form-control" id="standorte" required>
            </div>
        </div>
      </form>
    `,
        showCancelButton: true,
        confirmButtonText: 'Erstellen',
        preConfirm: () => {
            const form = document.getElementById('create-customer-form');
            const name = document.getElementById('name').value;
            const suchnummer = document.getElementById('suchnummer').innerText;
            const standort = document.getElementById('standort').value;
            const standorteInput = document.getElementById('standorte');
            const standorteValue = standorteInput.value;

            if (!name || !standort || (!standorteValue && standorte.length === 0)) {
                Swal.showValidationMessage("Bitte fülle alle Pflichtfelder aus!");
            }

            standorte = standorteValue ? [...standorte, standorteValue] : standorte;

            return { name, suchnummer, standort, standorte };
        },
        didOpen: () => {
            const suchnummerElement = document.getElementById('suchnummer');
            suchnummerElement.innerText = generateRandomNumber();

            const standorteContainer = document.getElementById('standorte-container');
            const standorteInput = document.getElementById('standorte');

            const addStandort = () => {
                const div = document.createElement('div');
                div.className = 'form-group';

                const label = document.createElement('label');
                label.htmlFor = `standorte-${standorte.length}`;
                label.innerText = `Unterstandort ${standorte.length + 1}:`;

                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control';
                input.id = `standorte-${standorte.length}`;
                input.name = `standorte[${standorte.length}]`;
                input.required = true;

                div.appendChild(label);
                div.appendChild(input);
                standorteContainer.appendChild(div);

                input.focus();
            };

            const handleStandortInput = (event) => {
                const input = event.target;
                const value = input.value;

                if (value && value.length > 1) {
                    // Use Geoapify to get autocomplete suggestions for the input value
                    const apiKey = 'YOUR_GEOAPIFY_API_KEY';
                    const url = `https://api.geoapify.com/v1/geocode/autocomplete?text=${value}&apiKey=${apiKey}`;

                    fetch(url)
                        .then((response) => response.json())
                        .then((data) => {
                            const suggestions = data.features.map((feature) => feature.properties.address_line1);
                            Swal.fire({
                                title: 'Standort auswählen',
                                input: 'select',
                                inputOptions: suggestions.reduce((acc, suggestion) => {
                                    acc[suggestion] = suggestion;
                                    return acc;
                                }, {}),
                                inputPlaceholder: 'Wähle einen Standort aus...',
                                showCancelButton: true,
                                inputValidator: (value) => {
                                    return new Promise((resolve) => {
                                        if (!value) {
                                            resolve('Bitte wähle einen Standort aus!');
                                        } else {
                                            resolve();
                                        }
                                    });
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    input.value = result.value;
                                    standorte.push(result.value);
                                    addStandort();
                                }
                            });
                        })
                        .catch((error) => {
                            console.error(error);
                        });
                }
            };

            standorteInput.addEventListener('input', handleStandortInput);

            addStandort();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('name', result.value.name);
            formData.append('suchnummer', result.value.suchnummer);
            formData.append('standort', result.value.standort);
            result.value.standorte.forEach((val, idx) => {
                formData.append(`standorte[${idx}]`, val);
            });

            fetch('/doku/create', {
                method: 'POST',
                body: formData
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Erfolgreich',
                            text: 'Der Kunde wurde angelegt.',
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Fehler',
                            text: 'Es ist ein Fehler aufgetreten.',
                            icon: 'error'
                        });
                    }
                })
                .catch((error) => {
                    Swal.fire({
                        title: 'Fehler',
                        text: 'Es ist ein Fehler aufgetreten.',
                        icon: 'error'
                    });
                });
        }
    });
}

function updateCustomerList() {
    fetch('/doku/kundendoku')
        .then(response => response.text())
        .then(html => {
            document.getElementById('customerList').innerHTML = html;
        })
        .catch(error => console.error('Error updating customer list:', error));
}

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