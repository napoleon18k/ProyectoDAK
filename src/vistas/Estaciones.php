<?php
// Estaciones.php - Página con Mapa + Reservas
session_start(); // Aseguramos la sesión
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estaciones de Carga</title>
    <link rel="stylesheet" href="../assets/css/cliente.css"> 
    <style>
        /* Header fijo */
        .header-modificado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            height: 70px;
        }
        .header-modificado .logo-container img {
            height: 50px;
            width: auto;
        }
        .header-modificado h2 {
            margin: 0;
            font-size: 1.5em;
            color: #333;
            text-transform: uppercase;
        }
        .header-modificado .btn-volver {
            padding: 10px 15px;
            background-color: #00a1ff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1em;
            transition: background-color 0.3s;
        }
        .header-modificado .btn-volver:hover {
            background-color: #007bb5;
        }
        /* Contenedor del mapa */
        #mapa {
            margin-top: 70px;
            height: calc(100vh - 70px) !important;
            width: 100%;
            display: block;
            background-color: #eaeaea; /* Fondo mientras carga */
        }
        /* Loader mientras carga el mapa */
        #mapaLoader {
            position: absolute;
            top: 70px;
            left: 0;
            width: 100%;
            height: calc(100vh - 70px);
            display: flex;
            justify-content: center;
            align-items: center;
            background: #eaeaea;
            z-index: 500;
            font-size: 1.2em;
            color: #555;
        }
        /* Panel de reservas */
        #panelReserva {
            position: fixed;
            top: 80px;
            right: 20px;
            width: 300px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            padding: 15px;
            display: none;
            z-index: 1100;
        }
        #panelReserva.open { display: block; }
        .cargador-item.selected, .slot-btn.selected {
            background-color: #00a1ff;
            color: white;
        }
        #carritoFlotante {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 250px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            padding: 10px;
            display: none;
            z-index: 1100;
        }

        /* ESTILOS DEL MODAL DE PAGO */
        .modal-pago-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none; /* Inicialmente oculto */
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }
        .modal-pago-overlay.open {
            display: flex;
        }
        .modal-pago-contenido {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 450px;
            position: relative;
        }
        .modal-pago-contenido h3 {
            margin-top: 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .modal-pago-contenido label {
            display: block;
            margin-top: 10px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .modal-pago-contenido input, .modal-pago-contenido select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .modal-pago-contenido .row {
            display: flex;
            gap: 15px;
        }
        .modal-pago-contenido .row > div {
            flex: 1;
        }
        .modal-pago-contenido button.btn-pagar {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .modal-pago-contenido button.btn-pagar:hover {
            background-color: #1e7e34;
        }
        .modal-pago-contenido button.close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #333;
        }
        .modal-pago-contenido button.close-btn:hover {
            color: #ff0000;
        }
        /* Nuevo estilo para slots ocupados */
        .slot-btn.ocupado {
            background-color: #f1f1f1 !important; 
            color: #666; 
            cursor: not-allowed;
            text-decoration: line-through;
            opacity: 0.7;
        }
    </style>
</head>
<body>
<div class="header-modificado">
    <div class="logo-container">
        <img src="../assets/imagenes/logoPrincipal.jpg" alt="Logo de la Página"> 
    </div>
    <h2>Estaciones de carga</h2>
    
    <a href="PrincipalCliente.html" class="btn-volver">Volver a PrincipalCliente.html</a>
</div>
<div id="mapa"></div>
<div id="mapaLoader">Cargando mapa...</div>
<div id="panelReserva" aria-hidden="true">
    <button id="cerrarPanel" title="Cerrar">✕</button>
    <h2>Reservar estación</h2>
    <p><strong>Estación:</strong> <span id="reservaEstacion">—</span></p>
    <p><strong>Dirección:</strong> <span id="reservaDireccion">—</span></p>
    <h3>Cargadores</h3>
    <div id="listaCargadoresPanel"></div>
    <h3>Seleccioná horario</h3>
    <div id="contenedorSlots"></div>
    <button id="btnConfirmarReserva" disabled>Confirmar reserva</button>
</div>
<div id="carritoFlotante" aria-hidden="true">
    <h4>Carrito de reservas</h4>
    <div id="listaCarrito"></div>
    <div style="display:flex; justify-content:flex-end; gap:8px; margin-top: 10px;">
        <button class="btnVaciar" id="vaciarCarrito">Vaciar</button>
        <button class="btnConfirmarCarrito" id="confirmarCarrito">Reservar</button>
    </div>
</div>

<div class="modal-pago-overlay" id="modalPagoOverlay">
    <div class="modal-pago-contenido">
        <button class="close-btn" id="cerrarModalPago">✕</button>
        <h3>💳 Información de Pago</h3>
        <form id="formularioPago">
            <label for="tarjetaNumero">Número de Tarjeta</label>
            <input type="text" id="tarjetaNumero" name="tarjetaNumero" placeholder="XXXX XXXX XXXX XXXX" required maxlength="16">

            <div class="row">
                <div>
                    <label for="fechaExpiracion">Expiración (MM/AA)</label>
                    <input type="text" id="fechaExpiracion" name="fechaExpiracion" placeholder="MM/AA" required maxlength="5">
                </div>
                <div>
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv" placeholder="123" required maxlength="3">
                </div>
            </div>

            <label for="nombreTitular">Nombre del Titular</label>
            <input type="text" id="nombreTitular" name="nombreTitular" required>

            <button type="submit" class="btn-pagar">Pagar y Confirmar Reservas</button>
        </form>
    </div>
</div>
<script>
let map;
let boundsUruguay;
function initMap() {
    map = new google.maps.Map(document.getElementById("mapa"), { zoom: 6 });
    boundsUruguay = new google.maps.LatLngBounds(
        { lat: -35.5, lng: -58.5 },
        { lat: -30.0, lng: -53.0 }
    );
    map.fitBounds(boundsUruguay);
    fetch('../api/getEstaciones.php')
        .then(res => res.json())
        .then(estaciones => {
            estaciones.forEach(est => {
                const pos = { lat: parseFloat(est.lat), lng: parseFloat(est.lng) };
                const marker = new google.maps.Marker({
                    position: pos,
                    map: map,
                    title: est.nombre,
                    icon: {
                        url: '../assets/imagenes/iconomapa.png',
                        scaledSize: new google.maps.Size(40, 40)
                    }
                });
                const cargadoresInfoHTML = est.cargadores_formateados 
                    ? `<h4 style="margin: 5px 0 2px 0;">Cargadores:</h4><pre style="margin:0; font-family:inherit; white-space: pre-wrap; font-size:0.9em;">${est.cargadores_formateados}</pre>`
                    : '<p>Cargadores no listados.</p>';
                const infoWindow = new google.maps.InfoWindow({
                    content: `<h3>${est.nombre}</h3>
                              <p><strong>Dirección:</strong> ${est.direccion}</p>
                              <p><strong>Departamento:</strong> ${est.departamento}</p>
                              ${cargadoresInfoHTML}
                              <button onclick='abrirPanelReserva(${JSON.stringify(est)})'>Reservar aquí</button>`
                });
                marker.addListener('click', () => infoWindow.open(map, marker));
            });
        })
        .catch(err => console.error(err))
        .finally(() => {
            document.getElementById('mapaLoader').style.display = 'none';
        });
}
window.addEventListener('load', initMap);

// ===== LÓGICA DE RESERVAS =====
const carritoReservas = [];
const panel = document.getElementById('panelReserva');
const listaCargadoresPanel = document.getElementById('listaCargadoresPanel');
const contenedorSlots = document.getElementById('contenedorSlots');
const reservaEstacionSpan = document.getElementById('reservaEstacion');
const reservaDireccionSpan = document.getElementById('reservaDireccion');
const btnConfirmarReserva = document.getElementById('btnConfirmarReserva');
let estacionActual = null;
let cargadorSeleccionado = null;
let slotsSeleccionados = [];

function abrirPanelReserva(est) {
    estacionActual = est;
    reservaEstacionSpan.textContent = est.nombre;
    reservaDireccionSpan.textContent = est.direccion;
    listaCargadoresPanel.innerHTML = '';
    contenedorSlots.innerHTML = '';
    cargadorSeleccionado = null;
    slotsSeleccionados = []; 
    btnConfirmarReserva.disabled = true;

    // Llamada para obtener cargadores de la estación
    fetch(`../modelo/getCargadores.php?estacionId=${est.id}`)
        .then(res => res.json())
        .then(resp => {
            if(!resp.ok) {
                listaCargadoresPanel.innerHTML = `<p style="color:red;">Error: ${resp.message || 'Fallo de API.'}</p>`;
                return;
            }
            const cargadores = resp.cargadores;
            cargadores.forEach(c => {
                const div = document.createElement('div');
                div.className = 'cargador-item';
                div.textContent = `${c.tipo} — ${c.potencia}`;
                div.onclick = () => {
                    document.querySelectorAll('.cargador-item').forEach(x => x.classList.remove('selected'));
                    div.classList.add('selected');
                    cargadorSeleccionado = c;
                    cargarSlotsDisponibles(); // Llama a la función al seleccionar cargador
                };
                listaCargadoresPanel.appendChild(div);
            });
        })
        .catch(err => {
            console.error('Error cargando cargadores:', err);
            listaCargadoresPanel.innerHTML = '<p style="color:red;">Error de conexión al cargar cargadores.</p>';
        });
    panel.classList.add('open');
    panel.setAttribute('aria-hidden', 'false');
}

document.getElementById('cerrarPanel').addEventListener('click', () => {
    panel.classList.remove('open');
    panel.setAttribute('aria-hidden', 'true');
});

// =======================================================
// FUNCIÓN CORREGIDA CON LLAMADA A LA API
// =======================================================
function cargarSlotsDisponibles() {
    contenedorSlots.innerHTML = '';
    slotsSeleccionados = [];
    btnConfirmarReserva.disabled = true;

    if (!cargadorSeleccionado) {
        contenedorSlots.innerHTML = '<p>Seleccione un cargador primero.</p>';
        return;
    }

    const hoy = new Date();
    const fechaStr = hoy.toISOString().slice(0, 10); // Formato YYYY-MM-DD
    
    contenedorSlots.innerHTML = '<p>Cargando disponibilidad...</p>';

    // Llama al nuevo endpoint para obtener los slots ocupados
    fetch(`../api/getSlots.php?cargador=${cargadorSeleccionado.id}&fecha=${fechaStr}`)
        .then(res => res.json())
        .then(resp => {
            contenedorSlots.innerHTML = ''; // Limpiar mensaje de carga

            if (!resp.ok) {
                contenedorSlots.innerHTML = `<p style="color:red;">Error al obtener slots: ${resp.error || 'Fallo de API.'}</p>`;
                return;
            }

            // Lista de slots ocupados (ej: ["2025-11-20 08:00:00", "2025-11-20 09:00:00"])
            const slotsOcupados = resp.ocupados || [];
            
            // Generar todos los slots posibles (8:00 a 21:00)
            for (let h = 8; h <= 21; h++) {
                const slotCompleto = `${fechaStr} ${String(h).padStart(2, '0')}:00:00`;
                const horaVisible = slotCompleto.slice(11, 16); // 08:00

                const btn = document.createElement('button');
                btn.className = 'slot-btn';
                btn.textContent = horaVisible;
                btn.setAttribute('data-slot', slotCompleto);

                // Verificar si el slot está ocupado usando la lista de la DB
                const estaOcupado = slotsOcupados.includes(slotCompleto);

                if (estaOcupado) {
                    btn.disabled = true;
                    btn.classList.add('ocupado');
                    btn.textContent += ' (Ocupado)';
                } else {
                    // Si está libre, permitir selección
                    btn.onclick = function() {
                        const slot = this.getAttribute('data-slot');
                        if (this.classList.contains('selected')) {
                            // deseleccionar
                            this.classList.remove('selected');
                            slotsSeleccionados = slotsSeleccionados.filter(s => s !== slot);
                        } else {
                            // seleccionar
                            this.classList.add('selected');
                            slotsSeleccionados.push(slot);
                        }
                        btnConfirmarReserva.disabled = slotsSeleccionados.length === 0;
                    };
                }
                
                contenedorSlots.appendChild(btn);
            }
        })
        .catch(err => {
            console.error('Error de conexión al cargar slots:', err);
            contenedorSlots.innerHTML = '<p style="color:red;">Error al comunicarse con el servidor.</p>';
        });
}
// =======================================================
// FIN FUNCIÓN CORREGIDA
// =======================================================

btnConfirmarReserva.addEventListener('click', () => {
    if (!estacionActual || !cargadorSeleccionado || slotsSeleccionados.length === 0) return;

    slotsSeleccionados.forEach(slot => {
        carritoReservas.push({
            estacionId: estacionActual.id,
            estacionNombre: estacionActual.nombre,
            direccion: estacionActual.direccion,
            cargadorId: cargadorSeleccionado.id,
            cargadorTexto: `${cargadorSeleccionado.tipo} — ${cargadorSeleccionado.potencia}`,
            slot: slot
        });
    });

    actualizarCarritoUI();
    panel.classList.remove('open');
    panel.setAttribute('aria-hidden', 'true');
    cargadorSeleccionado = null;
    slotsSeleccionados = [];
});

function actualizarCarritoUI() {
    const carritoDiv = document.getElementById('carritoFlotante');
    const lista = document.getElementById('listaCarrito');
    lista.innerHTML = '';

    if (carritoReservas.length === 0) {
        carritoDiv.style.display = 'none';
        carritoDiv.setAttribute('aria-hidden', 'true');
        return;
    }

    carritoDiv.style.display = 'block';
    carritoDiv.setAttribute('aria-hidden', 'false');

    carritoReservas.forEach((it, idx) => {
        const div = document.createElement('div');
        div.className = 'itemCarrito';
        div.style.borderBottom = '1px dashed #eee';
        div.style.paddingBottom = '5px';
        div.style.marginBottom = '5px';
        div.innerHTML = `
            <strong>${it.estacionNombre}</strong><br>
            <small>${it.cargadorTexto} — ${it.direccion}</small><br>
            <span style="font-weight:bold; color:#00a1ff;">${it.slot.slice(11,16)}</span>
            <button onclick="quitarItemCarrito(${idx})" style="float:right; background:none; border:none; color:red; cursor:pointer;">Eliminar</button>
        `;
        lista.appendChild(div);
    });
}

function quitarItemCarrito(i) {
    carritoReservas.splice(i,1);
    actualizarCarritoUI();
}

document.getElementById('vaciarCarrito').addEventListener('click', () => {
    carritoReservas.length = 0;
    actualizarCarritoUI();
});

// Evento para ABRIR el Modal de Pago
document.getElementById('confirmarCarrito').addEventListener('click', () => {
    if (carritoReservas.length === 0) {
        alert('El carrito está vacío.');
        return;
    }
    document.getElementById('modalPagoOverlay').classList.add('open');
});

// Evento para CERRAR el Modal de Pago
document.getElementById('cerrarModalPago').addEventListener('click', () => {
    document.getElementById('modalPagoOverlay').classList.remove('open');
});

// Evento para PROCESAR el Pago (Simulado) y luego enviar las reservas
document.getElementById('formularioPago').addEventListener('submit', (e) => {
    e.preventDefault();
    document.getElementById('modalPagoOverlay').classList.remove('open'); // Cerramos el modal
    
    // Simulación de éxito de pago
    alert('Pago procesado correctamente (Simulado). Confirmando reservas...');

    // Lógica para enviar las reservas al servidor
    fetch('../api/crearReservasBatch.php', {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify({ reservas: carritoReservas })
    }).then(r => r.json())
      .then(resp => {
        if (resp.ok) {
            alert('✅ Reservas confirmadas con éxito. ¡Revisa tu correo!');
            carritoReservas.length = 0;
            actualizarCarritoUI();
            
            // IMPORTANTE: Después de confirmar la reserva, recargar la lista de slots
            // para que las horas reservadas ya no aparezcan disponibles.
            if (cargadorSeleccionado) {
                 cargarSlotsDisponibles(); 
            }

        } else {
            alert('❌ Error al confirmar reservas: '+resp.message);
        }
      })
      .catch(err => {
        console.error('Error al enviar reservas:', err);
        alert('❌ Error de conexión al intentar confirmar las reservas.');
      });
});

window.abrirPanelReserva = abrirPanelReserva;
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4HgNqmymEub5LY68nN-s-ONF-i931SCE&callback=initMap"></script>
</body>
</html>