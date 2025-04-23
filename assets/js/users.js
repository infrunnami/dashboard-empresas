axios.defaults.withCredentials = true;
loadEmpresas();

function loadEmpresas() {
    axios.get(config.urlBase+"listar_empresas.php").then(response => {
        let select = $("#empresa");
        select.empty();
        response.data.forEach(emp => {
            select.append(`<option value="${emp.id}">${emp.nombre}</option>`);
        });
    });
}

function loadRoles() {

    axios.get(config.urlBase + "listar_roles.php").then(response => {

        const select = document.getElementById("roles");
        select.innerHTML = ""; // Limpiar cualquier opción anterior

        response.data.forEach(rol => {
            console.log(`opciones: ${rol.nombre}`);
            const option = document.createElement("option");
            option.value = rol.id;
            option.textContent = rol.nombre;
            select.appendChild(option);
        });
    }).catch(error => {
        console.error("Error al cargar roles:", error);
    });
}
function loadUsers() {
    axios.get(config.urlBase +"users.php")
        .then(response => {
            let table = $('#usersTable').DataTable();
            table.clear();
            response.data.forEach(user => {
                table.row.add([
                    user.id,
                    user.username,
                    user.email,
                    user.rol,
                    user.empresa,
                    `<button class="btn btn-sm btn-secondary" onclick="openModal(${user.id}, '${user.username}', '${user.email}',${user.empresa_id},${user.rol_id})">Editar</button>
             <button class="btn btn-sm btn-dark" onclick="deleteUser(${user.id})">Eliminar</button>`
                ]).draw();

                console.log (user.rol)
            });
        });
}



function openModal(id = null, username = "", email = "", empresa_id="", role_id="") {

    loadRoles(); // ← IMPORTANTE: siempre cargar antes de abrir

    $("#userId").val(id || "");
    $("#username").val(username);
    $('#email').val(email);
    $("#roles").val(parseInt(role_id));
    $("#empresa").val(parseInt(empresa_id));
    $("#password").val("");
    $("#userModal").modal("show");

    console.log(empresa_id);
}

function saveUser() {
    let id = $("#userId").val();
    let user = {
        id: id,
        username: $("#username").val(),
        password: $("#password").val(),
        role_id: $("#roles").val(),
        email: $('#email').val(),
        empresa_id: $('#empresa').val()
    };

    let url = id ? config.urlBase +"users.php?update" : config.urlBase +"users.php?create";

    axios.post(url, user)
        .then(() => {
            $("#userModal").modal("hide");
            loadUsers();
        });
}

function deleteUser(id) {
    if (confirm("¿Eliminar este usuario?")) {
        axios.post(config.urlBase +"users.php?delete", { id })
            .then(() => loadUsers());
    }
}