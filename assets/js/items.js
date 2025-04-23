$(document).ready(function () {
  loadItems();
});

function loadItems() {
  axios.get(config.urlBase+"items.php")
      .then(response => {
          let table = $('#itemsTable').DataTable();
          table.clear();
          response.data.forEach(item => {
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
      });
}

$(document).on("click", ".btn-edit", function () {
  let id = $(this).data("id");
  let nombre = $(this).data("nombre");
  let tipo = $(this).data("tipo");
  let url = $(this).data("url");
  openModal(id, nombre, tipo, url);
});

function openModal(id = null, nombre = "", tipo = "navbar", url = "") {
  $("#itemId").val(id || "");
  $("#nombre").val(nombre);
  $("#tipo").val(tipo);
  $("#url").val(url);

  let itemModal = new bootstrap.Modal(document.getElementById("itemModal"));
  itemModal.show();
}

function saveItem() {
  let id = $("#itemId").val();
  let item = { id: id, nombre: $("#nombre").val(), tipo: $("#tipo").val(), url: $("#url").val() };

  let url = id ? config.urlBase+"items.php?update" : config.urlBase+"items.php?create";
  
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