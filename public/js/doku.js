function createCustomerModal() {
    const generateRandomNumber = () => `K${Math.floor(Math.random() * 100000000)}`;
    let unterstandorte = []; // Arry für Unterstandorte

    Swal.fire({
        title: 'Neuen Kunden erstellen',
        html: `
        <form id="create-customer-form">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" id="name" required>
            </div>
            <div class="form-group">
                <label for="standort">Hauptstandort:</label>
                <input type="text" class="form-control" id="standort" required>
            </div>
            <div id="unterstandorte-container">
                <!-- Unterstandorte werden hier eingefügt -->
            </div>
            
        </form>
        <button type="button" id="add-unterstandort" class="btn btn-primary">+ Unterstandort</button>
        `,
        showCancelButton: true,
        confirmButtonText: 'Erstellen',
        preConfirm: () => {
            const name = document.getElementById('name').value;
            const standort = document.getElementById('standort').value;
            unterstandorte = unterstandorte.concat(Array.from(document.querySelectorAll('.unterstandort-input')).map(input => input.value));

            if (!name || !standort) {
                Swal.showValidationMessage("Bitte fülle alle Pflichtfelder aus!");
            }

            return { name, standort, unterstandorte };
        },
        didOpen: () => {
            document.getElementById('add-unterstandort').addEventListener('click', () => {
                const container = document.getElementById('unterstandorte-container');
                const input = document.createElement('input');
                input.type = 'text';
                input.classList.add('form-control', 'unterstandort-input');
                input.required = true;
                container.appendChild(input);
            });
        }
    }).then((result) => {

        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('name', result.value.name);
            formData.append('standort', result.value.standort);
            result.value.unterstandorte.forEach((val, idx) => {
                formData.append(`unterstandorte[${idx}]`, val);
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