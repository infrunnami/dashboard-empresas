$(document).ready(function () {
  loadRoleItems();
  loadRoles();
  loadItems();
});

function loadRoleItems() {
  axios.get(config.urlBase+"role_items.php")
      .then(response => {
          let table = $('#roleItemsTable').DataTable();
          table.clear();
          response.data.forEach(roleItem => {
              table.row.add([
                  roleItem.role,
                  roleItem.item,
                  `<button class="btn btn-sm btn-dark btn-delete" 
                      data-id="${roleItem.id}">Eliminar</button>`
              ]).draw();
          });
      });
}

function loadRoles() {
    axios.get(config.urlBase+"roles.php")
        .then(response => {
            let select = $("#roleSelect");
            select.empty();
            response.data.forEach(role => {
                select.append(`<option value="${role.id}">${role.nombre}</option>`);
            });
        });
  }

function loadItems() {
  axios.get(config.urlBase+"items.php")
      .then(response => {
          let select = $("#itemSelect");
          select.empty();
          response.data.forEach(item => {
              select.append(`<option value="${item.id}">${item.nombre}</option>`);
          });
      });
}

function openModal() {
  $("#roleItemModal").modal("show");
}

function saveRoleItem() {
  let role_id = $("#roleSelect").val();
  let item_id = $("#itemSelect").val();

  axios.post(config.urlBase+"role_items.php?create", { role_id, item_id })
      .then(() => {
          $("#roleItemModal").modal("hide");
          loadRoleItems();
      });
}

$(document).on("click", ".btn-delete", function () {
  let id = $(this).data("id");

  if (confirm("¿Eliminar este permiso?")) {
      axios.post(config.urlBase+"role_items.php?delete", { id })
          .then(() => loadRoleItems());
  }
});