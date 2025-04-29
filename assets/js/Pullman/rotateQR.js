// rotateQR.js

function rotateQR() {
  const qrElement = document.querySelector(".img-qr");

  if (qrElement) {
    // Obtener rotación actual del elemento
    const style = window.getComputedStyle(qrElement);
    const matrix = style.transform;

    let currentRotation = 0;
    if (matrix !== "none") {
      const values = matrix.split('(')[1].split(')')[0].split(',');
      const a = values[0];
      const b = values[1];
      const radians = Math.atan2(b, a);
      currentRotation = Math.round(radians * (180 / Math.PI));
    }

    // Nuevo ángulo
    const newRotation = currentRotation + 90;

    qrElement.style.transform = `rotate(${newRotation}deg)`;
    console.log(`QR rotado a ${newRotation} grados`);
  }
}

// Observador de cambios en el DOM
const observer = new MutationObserver(() => {
  const btnBaño = document.querySelector(".btn-genera-baño");
  const btnDucha = document.querySelector(".btn-genera-ducha");

  if (btnBaño && !btnBaño.hasAttribute("data-rotate-attached")) {
    btnBaño.addEventListener("click", rotateQR);
    btnBaño.setAttribute("data-rotate-attached", "true");
    console.log("Botón Baño conectado a rotar QR");
  }

  if (btnDucha && !btnDucha.hasAttribute("data-rotate-attached")) {
    btnDucha.addEventListener("click", rotateQR);
    btnDucha.setAttribute("data-rotate-attached", "true");
    console.log("Botón Ducha conectado a rotar QR");
  }
});

// Empezamos a observar
observer.observe(document.body, { childList: true, subtree: true });
