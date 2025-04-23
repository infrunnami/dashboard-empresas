$(document).ready(function () {
  loadRoles();
});

function loadRoles() {
  axios.get(config.urlBase+"roles.php")
      .then(response => {
          let table = $('#rolesTable').DataTable();
          table.clear();
          response.data.forEach(rol => {
              let estadoTexto = rol.estado == 1 ? "Activo" : "Inactivo";
              let switchButton = `
                  <div class="form-check form-switch">
                      <input class="form-check-input toggle-estado" type="checkbox" 
                          data-id="${rol.id}" ${rol.estado == 1 ? "checked" : ""}>
                  </div>
              `;
              table.row.add([
                  rol.id,
                  rol.nombre,
                  estadoTexto,
                  `<div class="d-flex justify-content-between align-items-center">
                      <button class="btn btn-sm btn-dark btn-edit me-2" 
                          data-id="${rol.id}" 
                          data-nombre="${rol.nombre}">Editar</button>
                      ${switchButton}
                  </div>`
              ]).draw();
          });
      });
}

$(document).on("click", ".btn-edit", function () {
  let id = $(this).data("id");
  let nombre = $(this).data("nombre");
  openModal(id, nombre);
});

function openModal(id = null, nombre = "") {
  $("#rolId").val(id || "");
  $("#nombre").val(nombre);

  let rolModal = new bootstrap.Modal(document.getElementById("rolModal"));
  rolModal.show();
}

function saveRol() {
  let id = $("#rolId").val();
  let rol = { id: id, nombre: $("#nombre").val() };

  let url = id ? config.urlBase+"roles.php?update" : config.urlBase+"roles.php?create";
  
  axios.post(url, rol)
      .then(() => {
          $("#rolModal").modal("hide");
          loadRoles();
      });
}

$(document).on("change", ".toggle-estado", function () {
  let id = $(this).data("id");
  let estado = $(this).is(":checked") ? 1 : 0;

  axios.post(config.urlBase+"roles.php?toggle", { id, estado })
      .then(() => loadRoles());
});