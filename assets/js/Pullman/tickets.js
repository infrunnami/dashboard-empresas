if (typeof urlBase === "undefined") {
  var urlBase = "https://andenes.terminal-calama.com";
}

if (typeof url === "undefined") {
  var url = urlBase + "/TerminalCalama/PHP/Restroom/save.php";
}

function crearQRCode(texto) {
  const contenedorQR = document.getElementById("contenedorQR");
  if (contenedorQR) {
    contenedorQR.innerHTML = "";
    new QRCode(contenedorQR, {
      text: texto
    });
  }
}

function initTickets() {
  const parrafoCodigo = document.getElementById("codigo");
  const parrafoFecha = document.getElementById("fecha");
  const parrafoHora = document.getElementById("hora");
  const parrafoTipo = document.getElementById("tipo");
  const botonesQR = document.querySelectorAll(".generarQR");

  if (!parrafoCodigo || !parrafoFecha || !parrafoHora || !parrafoTipo || !botonesQR.length) {
    console.error("No se encontraron elementos del DOM necesarios para Tickets.");
    return;
  }

  // generar código QR inicial por defecto
  crearQRCode("wit");

  botonesQR.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      btn.disabled = true;
      btn.classList.add("disabled");

      const fechaHoraAct = new Date();
      const horaStr = `${fechaHoraAct.getHours().toString().padStart(2, '0')}:${fechaHoraAct.getMinutes().toString().padStart(2, '0')}:${fechaHoraAct.getSeconds().toString().padStart(2, '0')}`;
      const fechaStr = fechaHoraAct.toISOString().split("T")[0];
      const tipoStr = btn.dataset.tipo;

      const numeroT = generarTokenNumerico();

      parrafoFecha.textContent = fechaStr;
      parrafoHora.textContent = horaStr;
      parrafoTipo.textContent = tipoStr;
      parrafoCodigo.textContent = numeroT;

      const datos = {
        Codigo: numeroT,
        hora: horaStr,
        fecha: fechaStr,
        tipo: tipoStr,
      };

      callApi(datos).then((res) => {
        crearQRCode(numeroT);
        btn.disabled = false;
        btn.classList.remove("disabled");
        addUser(numeroT);

        setTimeout(() => {
          let name = numeroT.substring(0, 6);
          addUserAccessLevel(name);
        }, 1000);
      });
    });
  });
}

function generarTokenNumerico() {
  let token = (Math.floor(Math.random() * 9) + 1).toString();
  for (let i = 1; i < 10; i++) {
    token += Math.floor(Math.random() * 10);
  }
  return token;
}

function escribirTexto() {
  contenedorContador.innerHTML = "texto";
}

async function callApi(datos) {
  let ret = await fetch(url, {
    method: "POST",
    mode: "cors",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(datos),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Error en la solicitud");
      }
      return response.text();
    })
    .then((result) => {
      console.log("Respuesta del servidor:", result);
    })
    .catch((error) => {
      console.error("Error al enviar la solicitud:", error);
    });
  return ret;
}

function printQR() {
  const ventanaImpr = window.open("", "_blank");

  // Obtenemos la fecha y hora actual
  const dateAct = new Date();
  const horaStr =
    dateAct.getHours().toString().padStart(2, "0") +
    ":" +
    dateAct.getMinutes().toString().padStart(2, "0") +
    ":" +
    dateAct.getSeconds().toString().padStart(2, "0");
  const fechaStr = dateAct.toISOString().split("T")[0];

  // Obtener el código QR generado
  const codigoQR = document.getElementById("keycont").value;
  const tipoSeleccionado = document.querySelector(
    'input[name="tipo"]:checked'
  ).value;

  if (!codigoQR) {
    alert("No hay código QR generado para imprimir.");
    return;
  }

  // Obtener el precio desde restroom.js
  const precio =
    restroom[tipoSeleccionado] !== undefined
      ? `$${restroom[tipoSeleccionado]}`
      : "No definido";

  ventanaImpr.document.write(`
        <html>
            <head>
                <title>Imprimir QR</title>
                <style>
                    body { text-align: center; font-family: Arial, sans-serif; }
                    h1, h3 { margin: 5px; }
                    .qr-container { display: flex; justify-content: center; margin-top: 10px; }
                    .close-btn {
                        background-color: red;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        font-size: 16px;
                        cursor: pointer;
                        margin-top: 20px;
                        border-radius: 5px;
                    }
                    .close-btn:hover {
                        background-color: darkred;
                    }
                    @media print {
                        .close-btn {
                            display: none;
                        }
                    }
                </style>
            </head>
            <body onload="window.print(); setTimeout(() => window.close(), 500);">
                <h1>Ticket de Acceso</h1>
                <h3>Fecha: ${fechaStr}</h3>
                <h3>Hora: ${horaStr}</h3>
                <h3>Tipo: ${tipoSeleccionado}</h3>
                <h3>Precio: ${precio}</h3>
                <h3>Código: ${codigoQR}</h3>
                <div class="qr-container">
                    ${document.getElementById("contenedorQR").innerHTML}
                </div>
                <button type="button" class="close-btn" onclick="window.close();">Cerrar</button>
            </body>
        </html>
    `);
  ventanaImpr.document.close();
}

async function addUser(token) {
  const url = urlBase + "/TerminalCalama/PHP/Restroom/addUser.php";

  const userData = { pin: token, idNo: token };

  try {
    let response = await fetch(url, {
      method: "POST",
      mode: "cors",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(userData),
    });

    let result = await response.text(); // Esperar a que la respuesta se convierta en texto
    console.log("Respuesta de addUser:", result);
  } catch (error) {
    console.error("Error al agregar usuario:", error);
  }
}

// Función para asignar niveles de acceso al usuario
async function addUserAccessLevel(token) {
  const url = urlBase + "/TerminalCalama/PHP/Restroom/addLevelUser.php";
  const accessData = { pin: token };

  try {
    let response = await fetch(url, {
      method: "POST",
      mode: "cors",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(accessData),
    });

    let result = await response.text(); // Esperar la respuesta
    console.log("Respuesta de addLevelUser:", result);
  } catch (error) {
    console.error("Error al asignar niveles de acceso:", error);
  }
}
