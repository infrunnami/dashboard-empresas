$(document).ready(function () {
    loadTemas(); 
    loadEmpresas();
});


function loadTemas() {
  axios.get(config.urlBase + "temas.php").then(response => {
    let select = $("#tema");
    select.empty();
    response.data.forEach(tema => {
      select.append(`<option value="${tema.id}">${tema.descripcion}</option>`);
    });
  });
}
  
function loadEmpresas() {
  axios.get(config.urlBase + "empresas.php").then(response => {
    let table = $('#empresasTable').DataTable();
    table.clear();
    response.data.forEach(emp => {
      let estadoTexto = emp.estado == 1 ? "Activo" : "Inactivo";
      let switchButton = `
        <div class="form-check form-switch">
          <input class="form-check-input toggle-estado" type="checkbox" 
            data-id="${emp.id}" ${emp.estado == 1 ? "checked" : ""}>
        </div>`;
      table.row.add([
        emp.id,
        emp.nombre,
        estadoTexto,
        `<div class="d-flex justify-content-between align-items-center">
          <button class="btn btn-sm btn-edit me-2" 
            data-id="${emp.id}" 
            data-nombre="${emp.nombre}"
            data-tema="${emp.tema_id || ''}">Editar</button>
          ${switchButton}
        </div>`
      ]).draw();
    });
  });
}


$(document).on("click", ".btn-edit", function () {
  let id = $(this).data("id");
  let nombre = $(this).data("nombre");
  let tema = $(this).data("tema") || "";
  openModal(id, nombre, tema);
});
  
function openModal(id = null, nombre = "", temaId = "") {
  $("#empresaId").val(id || "");
  $("#nombre").val(nombre);
  $("#tema").val(temaId);
  let empresaModal = new bootstrap.Modal(document.getElementById("empresaModal"));
  empresaModal.show();
}
  

function saveEmpresa() {
  let id = $("#empresaId").val();
  let empresa = {
    id: id,
    nombre: $("#nombre").val(),
    tema_id: $("#tema").val()
  };
  let url = id ? config.urlBase + "empresas.php?update" : config.urlBase + "empresas.php?create";

  axios.post(url, empresa)
    .then(() => {
      $("#empresaModal").modal("hide");
      loadEmpresas();
    });
}

$(document).on("change", ".toggle-estado", function () {
  let id = $(this).data("id");
  let estado = $(this).is(":checked") ? 1 : 0;

  axios.post(config.urlBase + "empresas.php?toggle", { id, estado })
    .then(() => loadEmpresas());
});
  
  