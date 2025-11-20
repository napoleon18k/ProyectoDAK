<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestor de Estaciones</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
    body { padding: 20px; }
    #mapa { height: 95vh; }
    #panel { height: 95vh; overflow-y: auto; }
    .boton-img { width: 100%; margin-bottom: 10px; cursor: pointer; }
    #formAgregar, #formModificar, #listaEstaciones { display: none; }
    .seleccionado { background-color: #d1ecf1; }
    .cargadorDiv { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; border-radius:5px; }
    /* Estilos para etiquetas dentro de cargadorDiv */
    .cargadorDiv label { display: block; margin-top: 5px; font-size: 0.9em; font-weight: bold; }
    
    /* Estilo para el botón de cerrar sesión flotante */
    #logoutButton {
        position: absolute;
        bottom: 30px;
        left: 40px;
        z-index: 1000; /* Asegura que esté por encima del mapa */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }
</style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-9" style="position: relative;">
            <div id="mapa"></div>
            
            <button id="logoutButton" class="btn btn-danger btn-sm" onclick="cerrarSesion()">
                Cerrar Sesión
            </button>
            </div>

        <div class="col-md-3" id="panel">
            <h4>Filtros</h4>
            <div class="mb-2">
                <label>Ubicación:</label>
                <input type="text" id="filtroUbicacion" class="form-control" placeholder="Dirección o Departamento">
            </div>
            <div class="mb-2">
                <label>Tipo de cargador:</label>
                <select id="filtroTipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="Tipo2">Tipo2</option>
                    <option value="CCS2">CCS2</option>
                </select>
            </div>
            <div class="mb-2">
                <label>Estado:</label>
                <select id="filtroEstado" class="form-control">
                    <option value="">Todos</option>
                    <option value="Disponible">Disponible</option>
                    <option value="En uso">En uso</option>
                    <option value="Fuera de servicio">Fuera de servicio</option>
                </select>
            </div>
            
            <hr>

            <h4>Acciones</h4>
            <img src="../assets/imagenes/agregarEstacion.jpg" class="boton-img" onclick="mostrarAgregar()">
            <img src="../assets/imagenes/listarEstacion.jpg" class="boton-img" onclick="mostrarLista()">
            <img src="../assets/imagenes/modificarEstacion.jpg" class="boton-img" onclick="mostrarModificar()">

            <hr>

            <div id="formAgregar">
                <h5>Agregar Estación</h5>
                <form id="formAgregarEstacion">
                    <input type="hidden" id="idAgregar">
                    <div class="mb-2"><input type="text" id="nombreAgregar" class="form-control" placeholder="Nombre" required></div>
                    <div class="mb-2"><input type="text" id="direccionAgregar" class="form-control" placeholder="Dirección" required></div>
                    <div class="mb-2"><input type="text" id="departamentoAgregar" class="form-control" placeholder="Departamento" required></div>
                    <div class="mb-2"><input type="text" id="latAgregar" class="form-control" placeholder="Latitud" readonly required></div>
                    <div class="mb-2"><input type="text" id="lngAgregar" class="form-control" placeholder="Longitud" readonly required></div>

                    <h6>Cargadores</h6>
                    <div id="cargadoresContainerAgregar"></div>
                    <button type="button" class="btn btn-secondary mb-2" onclick="agregarCargador('Agregar')">Agregar Cargador</button>
                    <button type="submit" class="btn btn-primary w-100">Guardar</button>
                </form>
            </div>

            <div id="formModificar">
                <h5>Modificar Estación</h5>
                <form id="formModificarEstacion">
                    <input type="hidden" id="idModificar">
                    <div class="mb-2"><input type="text" id="nombreModificar" class="form-control" placeholder="Nombre" required></div>
                    <div class="mb-2"><input type="text" id="direccionModificar" class="form-control" placeholder="Dirección" required></div>
                    <div class="mb-2"><input type="text" id="departamentoModificar" class="form-control" placeholder="Departamento" required></div>
                    <div class="mb-2"><input type="text" id="latModificar" class="form-control" placeholder="Latitud" readonly required></div>
                    <div class="mb-2"><input type="text" id="lngModificar" class="form-control" placeholder="Longitud" readonly required></div>

                    <h6>Cargadores</h6>
                    <div id="cargadoresContainerModificar"></div>
                    <button type="button" class="btn btn-secondary mb-2" onclick="agregarCargador('Modificar')">Agregar Cargador</button>
                    <button type="submit" class="btn btn-warning w-100">Modificar</button>
                </form>
            </div>

            <div id="listaEstaciones">
                <h5>Lista de Estaciones</h5>
                <p class="text-muted small">Seleccione una estación para modificarla o eliminarla.</p>
                <ul class="list-group" id="ulEstaciones"></ul>
            </div>
        </div>
    </div>
</div>

<script>
let map = L.map('mapa').setView([-34.9, -56.16], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);

let marcadorTemporal = null;
let todosMarkers = [];
let estaciones = [];
let seleccionadaId = null;

// --- Cargar estaciones ---
function cargarEstacionesMapa() {
    todosMarkers.forEach(m=>map.removeLayer(m));
    todosMarkers=[];
    
    fetch("../api/apiCargas.php?accion=listar")
    .then(r=>r.json())
    .then(data=>{
        estaciones = data;
        data.forEach(e=>{
            let m = L.marker([e.lat, e.lng]).addTo(map)
                .bindPopup(`<b>${e.nombre}</b><br>${e.direccion}<br>${e.departamento}<br>Estado: ${e.estado}`);
            todosMarkers.push(m);
        });
        aplicarFiltros();
    });
}
cargarEstacionesMapa();

// --- Click en mapa para nueva estación ---
map.on('click', function(e) {
    let lat = e.latlng.lat.toFixed(6);
    let lng = e.latlng.lng.toFixed(6);

    if(marcadorTemporal) map.removeLayer(marcadorTemporal);

    marcadorTemporal = L.marker([lat,lng]).addTo(map)
        .bindPopup("Nueva estación", { closeButton: true })
        .openPopup();

    marcadorTemporal.on('popupclose', function() {
        if(marcadorTemporal){
            map.removeLayer(marcadorTemporal);
            marcadorTemporal = null;
        }
        limpiarAgregar();
    });

    mostrarAgregar(true); // Forzar mostrar

    document.getElementById("latAgregar").value = lat;
    document.getElementById("lngAgregar").value = lng;
});

// Función auxiliar para ocultar todos los formularios
function ocultarTodos() {
    document.getElementById("formAgregar").style.display="none";
    document.getElementById("formModificar").style.display="none";
    document.getElementById("listaEstaciones").style.display="none";
}

// --- Funciones mostrar paneles con TOGGLE ---
function mostrarAgregar(forzarMostrar = false){
    const form = document.getElementById("formAgregar");
    if (form.style.display === "block" && !forzarMostrar) {
        form.style.display="none";
        limpiarAgregar();
    } else {
        ocultarTodos();
        form.style.display="block";
    }
}

function mostrarLista(){
    const lista = document.getElementById("listaEstaciones");
    if (lista.style.display === "block") {
        lista.style.display="none";
    } else {
        ocultarTodos();
        lista.style.display="block";
        
        const ul = document.getElementById("ulEstaciones");
        ul.innerHTML="";
        
        estaciones.forEach(e=>{
            let li = document.createElement("li");
            li.className="list-group-item d-flex justify-content-between align-items-center";
            
            if (e.id === seleccionadaId) {
                li.classList.add("seleccionado");
            }

            li.innerHTML = `<span>${e.nombre} - ${e.departamento}</span>
                             <img src="../assets/imagenes/eliminarEstacion.jpg" style="width:20px; cursor:pointer; display:none;" class="miniEliminar">`;
            
            li.onclick = function(ev){
                ul.querySelectorAll(".list-group-item").forEach(item => item.classList.remove("seleccionado"));
                li.classList.add("seleccionado");
                
                ul.querySelectorAll(".miniEliminar").forEach(img=>img.style.display="none");
                li.querySelector(".miniEliminar").style.display="inline";
                seleccionadaId = e.id;
            };
            
            li.querySelector(".miniEliminar").onclick = function(ev){
                ev.stopPropagation();
                if(confirm("Eliminar esta estación?")){
                    fetch("../api/apiCargas.php?accion=eliminar", {
                        method:"POST",
                        headers:{ "Content-Type":"application/json" },
                        body: JSON.stringify({ id: e.id })
                    }).then(r=>r.json()).then(resp=>{
                        if(resp.success){
                            alert("Eliminado correctamente");
                            seleccionadaId = null;
                            cargarEstacionesMapa();
                            mostrarLista();
                        }
                    });
                }
            };
            ul.appendChild(li);
        });
    }
}

function mostrarModificar(){
    const form = document.getElementById("formModificar");
    
    if (form.style.display === "block") {
        form.style.display="none";
        return;
    }
    
    if(!seleccionadaId){
        alert("Seleccione una estación de la lista primero");
        ocultarTodos();
        return;
    }
    
    let est = estaciones.find(e=>e.id===seleccionadaId);
    if(!est) return;
    
    ocultarTodos();
    form.style.display="block";

    document.getElementById("idModificar").value = est.id;
    document.getElementById("nombreModificar").value = est.nombre;
    document.getElementById("direccionModificar").value = est.direccion;
    document.getElementById("departamentoModificar").value = est.departamento;
    document.getElementById("latModificar").value = est.lat;
    document.getElementById("lngModificar").value = est.lng;

    let cont = document.getElementById("cargadoresContainerModificar");
    cont.innerHTML = "";
    
    const cargadoresEstacion = Array.isArray(est.cargadores) ? est.cargadores : [];

    cargadoresEstacion.forEach(c=>{
        agregarCargador("Modificar");
        let last = cont.lastElementChild;
        last.querySelector(".cargadorPotencia").value = c.potencia;
        last.querySelector(".cargadorTipo").value = c.tipo;
        last.querySelector(".cargadorConectores").value = c.conectores;
        last.querySelector(".cargadorPrecioKwh").value = c.precio_kWh;
        last.querySelector(".cargadorPrecioBase").value = c.precio_base;
    });
    
    if (cargadoresEstacion.length === 0) {
        agregarCargador("Modificar");
    }
}

// --- Agregar cargador (con botón de eliminar) ---
function agregarCargador(tipo){
    let container = tipo==="Agregar"? document.getElementById("cargadoresContainerAgregar") : document.getElementById("cargadoresContainerModificar");
    let div = document.createElement("div");
    div.classList.add("cargadorDiv");
    div.innerHTML = `
        <label>Potencia (kW)</label>
        <input type="number" class="form-control cargadorPotencia" placeholder="30" required>
        <label>Tipo</label>
        <select class="form-control cargadorTipo">
            <option value="Tipo2">Tipo2</option>
            <option value="CCS2">CCS2</option>
        </select>
        <label>Conectores</label>
        <input type="text" class="form-control cargadorConectores" placeholder="1 (con cable)">
        <label>Precio kWh</label>
        <input type="number" class="form-control cargadorPrecioKwh" placeholder="0">
        <label>Precio base</label>
        <input type="number" class="form-control cargadorPrecioBase" placeholder="0">
        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="this.closest('.cargadorDiv').remove()">Eliminar cargador</button>
    `;
    container.appendChild(div);
}

// --- Formularios submit (Mismos que antes) ---
document.getElementById("formAgregarEstacion").addEventListener("submit", function(e){
    e.preventDefault();
    let datos = {
        nombre: document.getElementById("nombreAgregar").value,
        direccion: document.getElementById("direccionAgregar").value,
        departamento: document.getElementById("departamentoAgregar").value,
        lat: document.getElementById("latAgregar").value,
        lng: document.getElementById("lngAgregar").value,
    };
    let cargadores=[];
    document.querySelectorAll("#cargadoresContainerAgregar .cargadorDiv").forEach(div=>{
        cargadores.push({
            potencia: div.querySelector(".cargadorPotencia").value,
            tipo: div.querySelector(".cargadorTipo").value,
            conectores: div.querySelector(".cargadorConectores").value,
            precio_kWh: div.querySelector(".cargadorPrecioKwh").value,
            precio_base: div.querySelector(".cargadorPrecioBase").value
        });
    });
    fetch("../api/apiCargas.php?accion=crear", {
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({...datos,cargadores})
    }).then(r=>r.json()).then(resp=>{
        if(resp.success){ 
            alert("Guardado"); 
            limpiarAgregar(); 
            cargarEstacionesMapa(); 
            mostrarAgregar();
        } else alert(resp.error||"Error");
    });
});

document.getElementById("formModificarEstacion").addEventListener("submit", function(e){
    e.preventDefault();
    let datos = {
        id: document.getElementById("idModificar").value,
        nombre: document.getElementById("nombreModificar").value,
        direccion: document.getElementById("direccionModificar").value,
        departamento: document.getElementById("departamentoModificar").value,
        lat: document.getElementById("latModificar").value,
        lng: document.getElementById("lngModificar").value,
    };
    let cargadores=[];
    document.querySelectorAll("#cargadoresContainerModificar .cargadorDiv").forEach(div=>{
        cargadores.push({
            potencia: div.querySelector(".cargadorPotencia").value,
            tipo: div.querySelector(".cargadorTipo").value,
            conectores: div.querySelector(".cargadorConectores").value,
            precio_kWh: div.querySelector(".cargadorPrecioKwh").value,
            precio_base: div.querySelector(".cargadorPrecioBase").value
        });
    });
    fetch("../api/apiCargas.php?accion=modificar", {
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({...datos,cargadores})
    }).then(r=>r.json()).then(resp=>{
        if(resp.success){ 
            alert("Modificado"); 
            cargarEstacionesMapa(); 
            mostrarModificar();
            mostrarLista();
        } else alert(resp.error||"Error");
    });
});

// --- Limpiar agregar ---
function limpiarAgregar(){
    document.getElementById("formAgregarEstacion").reset();
    document.getElementById("cargadoresContainerAgregar").innerHTML="";
    agregarCargador("Agregar"); 
    if(marcadorTemporal){ map.removeLayer(marcadorTemporal); marcadorTemporal=null; }
}

// --- Cerrar Sesión ---
function cerrarSesion() {
    if (confirm("¿Estás seguro que deseas cerrar la sesión?")) {
        // 1. Enviar solicitud para cerrar la sesión en el servidor
        fetch("../api/logout.php", {
            method: "POST" // Normalmente POST o GET, dependiendo de cómo maneje el backend el logout. Usamos POST por seguridad.
        })
        .then(response => {
            // No importa la respuesta del servidor (éxito o fallo), intentamos redirigir.
            // Si el servidor falla, es mejor redirigir de todos modos para forzar al usuario a iniciar sesión de nuevo.
            window.location.href = "login.html";
        })
        .catch(error => {
            console.error("Error al intentar cerrar sesión:", error);
            // Redirigir incluso si falla la llamada al API
            window.location.href = "login.html";
        });
    }
}

// --- Inicialización ---
limpiarAgregar(); 

// --- FILTROS ---
function aplicarFiltros(){
    let ubic = document.getElementById("filtroUbicacion").value.toLowerCase();
    let tipo = document.getElementById("filtroTipo").value;
    let estado = document.getElementById("filtroEstado").value;

    todosMarkers.forEach((m,i)=>{
        let e = estaciones[i];
        let mostrar = true;
        
        let direccion = (e.direccion || "").toLowerCase();
        let departamento = (e.departamento || "").toLowerCase();

        if(ubic && !(direccion.includes(ubic) || departamento.includes(ubic))) mostrar=false;
        
        if(tipo && e.cargadores && !e.cargadores.some(c=>c.tipo===tipo)) mostrar=false;
        
        if(estado && e.estado!==estado) mostrar=false;
        
        if(mostrar) m.addTo(map); else map.removeLayer(m);
    });
}

document.getElementById("filtroUbicacion").addEventListener("input", aplicarFiltros);
document.getElementById("filtroTipo").addEventListener("change", aplicarFiltros);
document.getElementById("filtroEstado").addEventListener("change", aplicarFiltros);
</script>
</body>
</html>