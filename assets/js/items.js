var rolUsuario = "";
var empresaUsuario = null;

$(document).ready(function () {
  axios.get(config.urlBase + "verSession.php").then(response => {
    rolUsuario = response.data.rol || "";
    empresaUsuario = response.data.empresa_id || null;

    loadItems();

    if (rolUsuario === "SuperAdmin") {
      loadEmpresas();
    }
  });
});

function loadEmpresas() {
  axios.get(config.urlBase + "listar_empresas.php").then(response => {
      let select = $("#empresaSelect");
      select.empty();
      response.data.forEach(emp => {
          select.append(`<option value="${emp.id}">${emp.nombre}</option>`);
      });
  });
}

function loadItems() {
  axios.get(config.urlBase+"items.php")
    .then(response => {
      let data = response.data;

      if (Array.isArray(data)) {
          let table = $('#itemsTable').DataTable();
          table.clear();
          data.forEach(item => {
              table.row.add([
                  item.id,
                  item.nombre,
                  item.tipo,
                  item.url,
                  `<button class="btn btn-sm btn-secondary btn-edit" 
                      data-id="${item.id}" 
                      data-nombre="${item.nombre}" 
                      data-tipo="${item.tipo}" 
                      data-url="${item.url}">Editar</button>
                  <button class="btn btn-sm btn-dark btn-delete" 
                      data-id="${item.id}">Eliminar</button>`
              ]).draw();
          });
      } else {
          console.warn("Respuesta inesperada del backend:", data);
          alert(data.error || "Ocurrió un error al cargar los ítems.");
      }
  })
  .catch(error => {
      console.error("Error al cargar ítems:", error);
      alert("No se pudo cargar la lista de ítems.");
  });
}

$(document).on("click", ".btn-edit", function () {
  let id = $(this).data("id");
  let nombre = $(this).data("nombre");
  let tipo = $(this).data("tipo");
  let url = $(this).data("url");
  openModal(id, nombre, tipo, url);
});

function openModal(id = null, nombre = "", tipo = "navbar", url = "", empresa_id = "") {
  $("#itemId").val(id || "");
  $("#nombre").val(nombre);
  $("#tipo").val(tipo);
  $("#url").val(url);

  if (rolUsuario === "SuperAdmin") {
      loadEmpresas();
      $("#empresaContainer").show();
      $("#empresaSelect").val(empresa_id || empresaUsuario || "");
  } else {
      $("#empresaContainer").hide();
  }

  let itemModal = new bootstrap.Modal(document.getElementById("itemModal"));
  itemModal.show();
}

function saveItem() {
  let id = $("#itemId").val();
  let item = {
      id: id,
      nombre: $("#nombre").val(),
      tipo: $("#tipo").val(),
      url: $("#url").val(),
      empresa_id: rolUsuario === "SuperAdmin" ? $("#empresaSelect").val() : null
  };

  let url = id ? config.urlBase + "items.php?update" : config.urlBase + "items.php?create";

  axios.post(url, item)
      .then(() => {
          $("#itemModal").modal("hide");
          loadItems();
      });
}

$(document).on("click", ".btn-delete", function () {
  let id = $(this).data("id");

  if (confirm("¿Eliminar este ítem?")) {
      axios.post(config.urlBase+"items.php?delete", { id })
          .then(() => loadItems());
  }
});