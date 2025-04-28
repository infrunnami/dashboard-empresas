if (window.pullmanInitialized) {
  console.log("main ya ejecutado");
} else {
  window.pullmanInitialized = true;

console.log("main cargado");

// Ejecutamos directamente ya que la página está cargada dinámicamente
initMain();
}
function initMain() {
  console.log("initMain ejecutado");

  function openResumen() {
    console.log("openResumen llamado correctamente")
    const modal = document.getElementById("resumen-overlay");

    if (!modal) {
      console.error("No se encontró el modal resumen-overlay");
      return;
    }

    showSpinner();
    modal.style.display = "flex";

    cargarTabla()
      .then(() => {
        hideSpinner();
        modal.style.display = "flex";
      })
      .catch((error) => {
        hideSpinner();
        console.error("Error en cargarTabla:", error);
      });
  }

  function crearQRCode(target, text) {
    if (typeof QRCode !== "undefined") {
      target.innerHTML = "";
      new QRCode(target, { text });
    } else {
      console.warn("QRCode no disponible");
    }
  }

  function cargarTabla() {
    const endpointURL = urlBase + "/TerminalCalama/PHP/Restroom/load.php";
    const tableBody = document.getElementById("sales-table-body");
  
    console.log("cargar nuevos datos");
    tableBody.innerHTML = ""; // Asegurar limpiar
  
    return fetch(endpointURL)
      .then((response) => response.json())
      .then((data) => {
  
        const ordenado = data.sort((a, b) => {
          const fechaA = new Date(`${a.date} ${a.time}`);
          const fechaB = new Date(`${b.date} ${b.time}`);
          return fechaB - fechaA;
        });
  
        const ultimos = ordenado.slice(0, 8);

        ultimos.forEach((item) => {
          const row = document.createElement("tr");

          const tipoCell = document.createElement("td");
          tipoCell.textContent = item.tipo;
          row.appendChild(tipoCell);

          const codigoCell = document.createElement("td");
          codigoCell.textContent = item.Codigo;
          row.appendChild(codigoCell);

          const fechaCell = document.createElement("td");
          fechaCell.textContent = item.date;
          row.appendChild(fechaCell);

          const horaCell = document.createElement("td");
          horaCell.textContent = item.time;
          row.appendChild(horaCell);

          const printCell = document.createElement("td");
          printCell.style.textAlign = "center";

          const printButton = document.createElement("button");
          printButton.className = "print-button";
          printButton.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 6 2 18 2 18 9"></polyline>
              <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
              <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
          `;

          printButton.addEventListener("click", async function () {
            const overlay = document.getElementById("ticket-print-overlay");
            const modal = document.getElementById("ticket-print-modal");
            const modalResume = document.getElementById("resumen-overlay");

            if (!overlay || !modal || !modalResume) {
              console.error("No se encontraron elementos para imprimir el ticket");
              return;
            }

            modalResume.style.display = "none";
            overlay.style.display = "none";

            showSpinner();

            const userPin = item.Codigo.slice(0, 6);
            const urlEstado = `${urlBase}/TerminalCalama/PHP/Restroom/estadoBoleto.php?userPin=${userPin}`;

            try {
              const resEstado = await fetch(urlEstado);
              if (!resEstado.ok) {
                throw new Error("Error al obtener estado del boleto.");
              }
              const dataEstado = await resEstado.json();
              let estadoTicket = dataEstado.message || "No encontrado";
              estadoTicket = estadoTicket.toUpperCase().replace(/\.$/, "");

              const infoItems = modal.querySelectorAll(".info-item");
              infoItems.forEach((infoItem) => {
                const label = infoItem.querySelector(".info-label").textContent.trim();
                const value = infoItem.querySelector(".info-value");

                if (label === "ESTADO") {
                  value.textContent = estadoTicket;
                  value.style.fontWeight = "bold";
                  value.style.color = estadoTicket === "EL BOLETO HA SIDO OCUPADO" ? "red" : "green";
                }
                if (label === "TIPO") value.textContent = item.tipo;
                if (label === "CÓDIGO") value.textContent = item.Codigo;
                if (label === "FECHA") value.textContent = item.date;
                if (label === "HORA") value.textContent = item.time;
              });

              const contenedorTicketQR1 = document.getElementById("contenedorTicketQR1");
              crearQRCode(contenedorTicketQR1, item.Codigo);

              overlay.style.display = "flex";
            } catch (err) {
              console.error("Error en impresión de ticket:", err);
            } finally {
              hideSpinner();
            }
          });

          printCell.appendChild(printButton);
          row.appendChild(printCell);
          tableBody.appendChild(row);
        });
      });
  }

  function closeResumen() {
    const modal = document.getElementById("resumen-overlay");
    if (modal) modal.style.display = "none";
  }

  function closeTicketModal() {
    const modal = document.getElementById("ticket-print-overlay");
    if (modal) modal.style.display = "none";
  }

  function showSpinner() {
    const spinner = document.getElementById("spinner");
    if (spinner) spinner.style.display = "flex";
  }

  function hideSpinner() {
    const spinner = document.getElementById("spinner");
    if (spinner) spinner.style.display = "none";
  }


  function initModalTicket(){
    const modal = document.getElementById("ticket-overlay");
    const inputField = document.getElementById("ticketInput");
    const closeBtn = document.querySelector(".close-button");
    const reimprimirBtn2 = document.getElementById("reimprimirBtn2");
    const searchBtn = document.getElementById("searchTicketBtn");

    const tipoEl = modal.querySelector(".info-item:nth-child(1) .info-value");
    const codigoEl = modal.querySelector(".info-item:nth-child(2) .info-value");
    const fechaEl = modal.querySelector(".info-item:nth-child(3) .info-value");
    const horaEl = modal.querySelector(".info-item:nth-child(4) .info-value");
    const estadoEl = modal.querySelector(".info-item:nth-child(5) .info-value");

    searchBtn.addEventListener("click", async function () {
      const codigo = inputField.value.trim();
    
      if (!/^\d{10}$/.test(codigo)) {
        Swal.fire({
          icon: "warning",
          title: "Código inválido",
          text: "El código debe contener exactamente 10 números.",
          customClass: {
            title: "swal-font",
            htmlContainer: "swal-font",
            popup: "alert-card",
            confirmButton: "my-confirm-btn",
          },
          buttonsStyling: false,
        });
        return;
      }
  
      if (!codigo) return;
      
      closeBtn.addEventListener("click", function () {
        modal.style.display = "none";
        inputField.value = ""; // Limpia el input cuando cierran el modal
      });
      
      showSpinner();
  
      const userPin = codigo.slice(0, 6);
  
      const url = `${urlBase}/TerminalCalama/PHP/Restroom/getCodigo.php?codigo=${codigo}`;
      const urlEstado = `${urlBase}/TerminalCalama/PHP/Restroom/estadoBoleto.php?userPin=${userPin}`;
  
      try {
        const res = await fetch(url);
        const data = await res.json();
        const ticket = data.find((t) => t.Codigo === codigo);
        console.log(ticket);
  
        const resEstado = await fetch(urlEstado);
        const dataEstado = await resEstado.json();
        let estadoTicket = dataEstado.message || "No encontrado";
        estadoTicket = estadoTicket.toUpperCase().replace(/\.$/, "");
  
        estadoEl.textContent = estadoTicket;
        estadoEl.style.fontWeight = "bold";
  
        if (estadoTicket === "BOLETO SIN USAR") {
          estadoEl.style.color = "green";
        } else {
          estadoEl.style.color = "red";
        }
  
        if (ticket) {
          tipoEl.textContent = ticket.tipo;
          codigoEl.textContent = ticket.Codigo;
          fechaEl.textContent = ticket.date;
          horaEl.textContent = ticket.time;
  
          const numeroT = ticket.Codigo;
  
          const contenedorTicketQR2 = document.getElementById(
            "contenedorTicketQR2"
          );
          contenedorTicketQR2.innerHTML = "";
  
          const qr = new QRCode(contenedorTicketQR2, {
            text: numeroT,
          });
  
          modal.style.display = "flex";
        } else {
          Swal.fire({
            icon: "error",
            title: "No encontrado",
            text: "No se encontró ningún ticket con ese código.",
            customClass: {
              title: "swal-font",
              htmlContainer: "swal-font",
              popup: "alert-card",
              confirmButton: "my-confirm-btn",
            },
            buttonsStyling: false,
          });
          modal.style.display = "none";
        }
      } catch (err) {
        console.error("Error al buscar ticket:", err);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Ocurrió un error al buscar el ticket. Intenta nuevamente.",
          customClass: {
            title: "swal-font",
            htmlContainer: "swal-font",
            popup: "alert-card",
            confirmButton: "my-confirm-btn",
          },
          buttonsStyling: false,
        });
      } finally {
        hideSpinner();
      }
    });
  }

  // Exponer al window
  window.closeResumen = closeResumen;
  window.closeTicketModal = closeTicketModal;

  // Conectar eventos
  document.getElementById("resumen-button")?.addEventListener("click", openResumen);
  document.getElementById("resumen-overlay")?.addEventListener("click", function (e) {
    if (e.target.id === "resumen-overlay") closeResumen();
  });
  document.getElementById("ticket-print-overlay")?.addEventListener("click", function (e) {
    if (e.target.id === "ticket-print-overlay") closeTicketModal();
  });

  initModalTicket();

  window.addEventListener("click", function (event) {
    const modalTicket = document.getElementById("ticket-overlay");
    if (event.target === modalTicket) {
      modalTicket.style.display = "none";
      const inputField = document.getElementById("ticketInput");
      if (inputField) inputField.value = "";
    }
  });


  // animacion codigo qr
  if (typeof rotation === "undefined") {
    var rotation = 0;
  }

  function rotateQR() {
    rotation += 90;
    document.querySelector(".img-qr").style.transform = `rotate(${rotation}deg)`;
  }

  document.querySelector(".btn-genera-baño").addEventListener("click", rotateQR);
  document.querySelector(".btn-genera-ducha").addEventListener("click", rotateQR);
  
}
