let startTime = null;
let currentTime = null;
let timerId = null;
let timerRunning = false;
let pauseTimerId = null;
let pauseStartTime = null;
let pauseHours = 0;
let pauseMinutes = 0;
let pauseSeconds = 0;
let selectedCustomerId = null;
const selectedMitarbeiter = Array.from(document.getElementById('mitarbeiterDropdown').selectedOptions).map(option => option.value);

function startTimer() {
  if (!timerRunning) {
  startTime = new Date();
  timerId = setInterval(() => {
  currentTime = new Date();
  updateTimerDisplay();
  }, 1000);
  timerRunning = true;
  document.getElementById('start-button').style.display = 'none';
  document.getElementById('stop-button').style.display = 'block';
  }
  }

function stopTimer() {
    if (timerRunning) {
      clearInterval(timerId);
      timerRunning = false;
      const endTime = new Date();
      const totalTimeInMilliseconds = endTime - startTime;
      const hours = Math.floor((totalTimeInMilliseconds % 86400000) / 3600000);
      const minutes = Math.floor((totalTimeInMilliseconds % 3600000) / 60000);
      const seconds = Math.floor((totalTimeInMilliseconds % 60000) / 1000);
      // Hier können Sie die Gesamtzeit speichern oder an einen Server senden
      console.log(`Gesamtzeit: ${hours} Stunden, ${minutes} Minuten und ${seconds} Sekunden`);
      document.getElementById('hours').textContent = '00';
      document.getElementById('minutes').textContent = '00';
      document.getElementById('seconds').textContent = '00';
      document.getElementById('start-button').style.display = 'block';
      document.getElementById('stop-button').style.display = 'none';
    }
}

function pauseTimer() {
    if (timerRunning) {
      clearInterval(timerId);
      timerId = null;
      pauseStartTime = new Date();
      pauseTimerId = setInterval(() => {
        const pauseCurrentTime = new Date();
        const pauseDifference = pauseCurrentTime - pauseStartTime;
        pauseHours = Math.floor(pauseDifference / 3600000);
        pauseMinutes = Math.floor((pauseDifference % 3600000) / 60000);
        pauseSeconds = Math.floor((pauseDifference % 60000) / 1000);
        document.getElementById('pause-hours').textContent = String(pauseHours).padStart(2, '0');
        document.getElementById('pause-minutes').textContent = String(pauseMinutes).padStart(2, '0');
        document.getElementById('pause-seconds').textContent = String(pauseSeconds).padStart(2, '0');
      }, 1000);
      document.getElementById('pause-button').textContent = 'Pause zu Ende';
      document.getElementById('pause-timer-display').style.display = 'block';
      timerRunning = false;
    } else {
      clearInterval(pauseTimerId);
      pauseTimerId = null;
      const pauseDuration = new Date() - pauseStartTime;
      const pauseHours = Math.floor(pauseDuration / 3600000);
      const pauseMinutes = Math.floor((pauseDuration % 3600000) / 60000);
      const pauseSeconds = Math.floor((pauseDuration % 60000) / 1000);
      // speichere die Pausendauer hier
      const totalPauseTimeInMilliseconds = pauseHours * 3600000 + pauseMinutes * 60000 + pauseSeconds * 1000;
      startTime = new Date(startTime.getTime() + totalPauseTimeInMilliseconds);
      currentTime = new Date(startTime.getTime() + totalPauseTimeInMilliseconds);
      timerId = setInterval(() => {
        currentTime = new Date();
        updateTimerDisplay();
      }, 1000);
      timerRunning = true;
      document.getElementById('pause-button').textContent = 'Pause';
      document.getElementById('pause-timer-display').style.display = 'none';
    }
}

function updateTimerDisplay() {
  const diffInMilliseconds = currentTime - startTime;
  const hours = Math.floor((diffInMilliseconds % 86400000) / 3600000);
  const minutes = Math.floor((diffInMilliseconds % 3600000) / 60000);
  const seconds = Math.floor((diffInMilliseconds % 60000) / 1000);
  document.getElementById('hours').textContent = String(hours).padStart(2, '0');
  document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
  document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
}

function updateCustomerList(searchTerm = '') {
  fetch(`/doku/list?search=${encodeURIComponent(searchTerm)}`)
    .then(response => response.json())
    .then(data => {
      const customerListDiv = document.getElementById('customerList');
      if (data.length === 0) {
          customerListDiv.innerHTML = `<div>Kein Kunde für den Namen "${searchTerm}" gefunden.</div>`;
          return;
      }

      const limitedData = data.slice(0, 3); // Begrenz die Anzahl der anzuzeigenden Kunden auf 3

      let tableHtml = `<table class="table" style="width: 100%;"><tbody>`;
      limitedData.forEach(customer => {
        tableHtml += `
          <tr class="customer-selectable" data-customer-id="${customer.id}" style="cursor: pointer;" onclick="selectCustomer(this)">
            <td>${customer.name}</td>
            <td>${customer.suchnummer}</td>
          </tr>
        `;
      });
      tableHtml += `</tbody></table>`;
      customerListDiv.innerHTML = tableHtml;

      // Auswahllogik für Kundenfelder
      document.querySelectorAll('.customer-selectable').forEach(item => {
        item.addEventListener('click', function() {
          // Entferne die Markierung von vorher ausgewählten Feldern
          document.querySelectorAll('.customer-selectable').forEach(div => div.classList.remove('selected'));
          this.classList.add('selected');
          selectedCustomerId = this.dataset.customerId;
          Swal.getConfirmButton().disabled = false; // Aktiviere den Submit-Button
        });
      });
    })
    .catch(error => {
      console.error('Fehler beim Aktualisieren der Kundenliste:', error);
    });
}

function selectCustomer(element) {
  // Entferne die Markierung von allen vorher ausgewählten Kunden
  document.querySelectorAll('.customer-selectable').forEach(div => {
    div.style.backgroundColor = ''; // Entferne den spezifischen Hintergrund
    div.style.color = ''; // Setze die Textfarbe zurück
  });

  // Markiere das ausgewählte Element
  element.style.backgroundColor = 'lightgrey'; // Blauer Hintergrund für ausgewählte Kunden
  element.style.color = 'black'; // Weiße Textfarbe für ausgewählte Kunden

  // Speichere die ID des ausgewählten Kunden für die weitere Verarbeitung
  selectedCustomerId = element.dataset.customerId;
  Swal.getConfirmButton().disabled = false; // Aktiviere den Submit-Button
}

updateCustomerList();

// Debouncing der Suchfunktion
const debouncedSearch = debounce(updateCustomerList, 300);

document.getElementById('searchCustomer').addEventListener('input', (e) => {
  debouncedSearch(e.target.value);
});

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  
  }
}

async function fetchMitarbeiterListe() {
  try {
    const response = await fetch('/doku/user');
    if (!response.ok) {
      throw new Error('Fehler beim Laden der Mitarbeiterliste');
    }
    return await response.json();
  } catch (error) {
    console.error(error);
    throw error;
  }
}


function openCustomerSelectionModal() {
  Swal.fire({
    title: 'Kundenauswahl',
    html: `
    <div style="justify-content: space-between; margin-bottom: 10px;">
    <input type="text" class="swal2-input" id="searchCustomer" placeholder="Suche..." style="width: 40%; height: 30px; margin-left:5px;">
    <span style="color:#243668;">
    <i class="fas fa-user-plus" style="cursor:pointer;" id="addUserButton" style="cursor:pointer;"></i>
    </span>
    </div>
    <div id="customerList" style="max-height: 400px; overflow-y: auto;"></div>
    `,
    width: '600px',
    preConfirm: () => {
      return {
        kundeId: selectedCustomerId,
        mitarbeiter: selectedMitarbeiter
      };
    },
    didOpen: () => {
      document.getElementById('searchCustomer').addEventListener('input', (e) => {
        updateCustomerList(e.target.value);
      });
      Swal.getConfirmButton().disabled = true; // Deaktiviere den Submit-Button
      updateCustomerList(); // Initialisiere die Liste ohne Suchbegriff, um alle Kunden anzuzeigen

      document.getElementById('addUserButton').addEventListener('click', async () => {
        try {
          const mitarbeiter = await fetchMitarbeiterListe();
          const mitarbeiterHtml = mitarbeiter.map(m => 
            `<label><input type="checkbox" name="mitarbeiter" value="${m.id}">${m.name}</label><br>`
          ).join('');
      
          Swal.fire({
            title: 'Mitarbeiter hinzufügen',
            html: `<div>${mitarbeiterHtml}</div>`,
            focusConfirm: false,
            preConfirm: () => {
              const ausgewaehlteMitarbeiter = Array.from(document.querySelectorAll('input[name="mitarbeiter"]:checked')).map(el => el.value);
              return ausgewaehlteMitarbeiter;
            },
            showCancelButton: true,
            confirmButtonText: 'Auswahl bestätigen'
          }).then(result => {
            if (result.isConfirmed && result.value.length > 0) {
              selectedMitarbeiter = result.value;
              console.log('Ausgewählte Mitarbeiter IDs:', selectedMitarbeiter);
              // Zeige einen Toast mit der Auswahl
              Swal.fire({
                title: 'Ausgewählte Mitarbeiter',
                text: `Mitarbeiter IDs: ${selectedMitarbeiter.join(', ')}`,
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
              });
            }
          });
        } catch (error) {
          Swal.showValidationMessage(`Fehler beim Abrufen der Mitarbeiterliste: ${error.message}`);
        }
      });      
    }
  });
}


document.addEventListener('input', function(e) {
  if(e.target && e.target.id === 'searchCustomer') {
    updateCustomerList(e.target.value);
  }
});