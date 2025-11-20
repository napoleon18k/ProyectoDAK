<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Planificar Viaje</title>
    <link rel="stylesheet" href="../assets/css/viajeCliente.css">
    <style>
        .station {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #f9f9f9; 
        }
        .slot-selector {
            margin-top: 5px;
            padding: 8px; 
            border: 1px solid #007bff;
            border-radius: 3px;
            width: 100%; 
            box-sizing: border-box;
        }
        
        /* ESTILOS DEL MODAL DE PAGO (Asegurando que esté oculto por defecto) */
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
    </style>
</head>
<body>
  <div class="container">
    <h1>Planificar Viaje</h1>

    <div class="select-vehiculo">
      <label for="auto">Selecciona tu vehículo</label>
      <select id="auto" name="auto">
        <option value="">Cargando vehículos...</option>
      </select>
    </div>

    <div id="map"></div>

    <div class="botones">
      <button id="calcularBtn" style="display:none;">Calcular ruta y estaciones</button>
      <button id="cancelarBtn" style="display:none; margin-left:10px; background:#f00; color:#fff;">Cancelar origen/destino</button>
            <button id="reservarBtn" style="display:none;">Ir a Pagar y Guardar Viaje</button> 
      <button id="volverBtn" style="margin-left:10px; background:#555; color:#fff;">Volver a Principal</button>
    </div>

    <div id="resultado" class="result" style="display:none;"></div>
    <div id="infoClick" style="margin-top:16px;"></div>
  </div>

<div class="modal-pago-overlay" id="modalPagoOverlay">
    <div class="modal-pago-contenido">
        <button class="close-btn" id="cerrarModalPago">✕</button>
        <h3>💳 Información de Pago</h3>
                <p>Total de slots a pagar: <strong id="resumenReservasCount">0</strong></p> 
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

            <button type="submit" class="btn-pagar">Pagar y Guardar Viaje</button>
        </form>
    </div>
</div>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4HgNqmymEub5LY68nN-s-ONF-i931SCE&callback=initMap&libraries=geometry"></script>

  <script>
let map, boundsUruguay, origenMarker=null, destinoMarker=null, origenCoords=null, destinoCoords=null;
let clickCount=0, selectedAuto=0, estaciones=[], directionsService=null, directionsRenderer=null;
let paradasMarkers=[], datosViajeReserva=null, geocoder;
let estacionesSugeridasConSlots = []; 
let reservasPendientes = []; 

// Control del estado del formulario de pago: activar/desactivar campos
function setPaymentFormEnabled(enabled) {
	const inputs = document.querySelectorAll('#formularioPago input, #formularioPago select');
	inputs.forEach(i => i.disabled = !enabled);
	const pagarBtn = document.querySelector('#formularioPago button.btn-pagar');
	if (pagarBtn) pagarBtn.disabled = !enabled;
}

function initMap() {
  map = new google.maps.Map(document.getElementById("map"), { zoom: 6 });
  boundsUruguay = new google.maps.LatLngBounds({ lat: -35.5, lng: -58.5 }, { lat: -30.0, lng: -53.0 });
  map.fitBounds(boundsUruguay);

  directionsService = new google.maps.DirectionsService();
  directionsRenderer = new google.maps.DirectionsRenderer({ map: map });
  geocoder = new google.maps.Geocoder();

  // Cargar estaciones desde API
  fetch('../api/getEstaciones.php')
    .then(res => res.json())
    .then(data => {
      estaciones = data;
      estaciones.forEach(est => {
        const pos = { lat: parseFloat(est.lat), lng: parseFloat(est.lng) };
        const marker = new google.maps.Marker({
          position: pos,
          map: map,
          title: est.nombre,
          icon: { url: '../assets/imagenes/iconomapa.png', scaledSize: new google.maps.Size(40, 40) }
        });
        const listaCargadores = est.cargadores_formateados ? est.cargadores_formateados.split('\n').map(linea => `<li>${linea}</li>`).join('') : '';
        const infoWindow = new google.maps.InfoWindow({ content: `<h3>${est.nombre}</h3><p>${est.direccion}</p><p>${est.departamento}</p><ul>${listaCargadores}</ul>` });
        marker.addListener('click', () => infoWindow.open(map, marker));
      });
    }).catch(err => console.error('Error cargando estaciones:', err));

  // Seleccionar origen/destino con clics
  map.addListener('click', function(e) {
    document.getElementById('cancelarBtn').style.display = 'block';
    if (!document.getElementById('auto').value) {
      document.getElementById('infoClick').textContent = 'Selecciona tu vehículo primero.';
      return;
    }
    if (clickCount === 0) {
      if (origenMarker) origenMarker.setMap(null);
      origenCoords = e.latLng;
      geocoder.geocode({ location: origenCoords }, (results, status) => {
        let dep = "Desconocido";
        if (status === "OK" && results[0]) {
          results[0].address_components.forEach(c => {
            if (c.types.includes("administrative_area_level_2") || c.types.includes("administrative_area_level_1")) {
              dep = c.long_name;
            }
          });
        }
        origenCoords.departamento = dep;
        document.getElementById('infoClick').textContent = `Origen seleccionado: ${dep}. Haz click en el destino.`;
      });
      origenMarker = new google.maps.Marker({
        position: origenCoords,
        map: map,
        label: 'O',
        icon: { path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: '#00f', fillOpacity: 1, strokeWeight: 2, strokeColor: '#fff' }
      });
      clickCount++;
    } else if (clickCount === 1) {
      if (destinoMarker) destinoMarker.setMap(null);
      destinoCoords = e.latLng;
      geocoder.geocode({ location: destinoCoords }, (results, status) => {
        let dep = "Desconocido";
        if (status === "OK" && results[0]) {
          results[0].address_components.forEach(c => {
            if (c.types.includes("administrative_area_level_2") || c.types.includes("administrative_area_level_1")) {
              dep = c.long_name;
            }
          });
        }
        destinoCoords.departamento = dep;
        document.getElementById('infoClick').textContent = `Destino seleccionado: ${dep}. Pulsa "Calcular ruta y estaciones".`;
      });
      destinoMarker = new google.maps.Marker({
        position: destinoCoords,
        map: map,
        label: 'D',
        icon: { path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: '#f00', fillOpacity: 1, strokeWeight: 2, strokeColor: '#fff' }
      });
      document.getElementById('calcularBtn').style.display = 'block';
      clickCount++;
    }
  });

  // Cancelar selección
  document.getElementById('cancelarBtn').addEventListener('click', function() {
    if (origenMarker) origenMarker.setMap(null);
    if (destinoMarker) destinoMarker.setMap(null);
    origenMarker = destinoMarker = origenCoords = destinoCoords = null;
    document.getElementById('calcularBtn').style.display = 'none';
    document.getElementById('cancelarBtn').style.display = 'none';
    document.getElementById('infoClick').textContent = '';
    clickCount = 0;
    directionsRenderer.set('directions', null);
    document.getElementById('resultado').style.display = 'none';
    document.getElementById('resultado').innerHTML = '';
    paradasMarkers.forEach(m => m.setMap(null));
    paradasMarkers = [];
    estacionesSugeridasConSlots = []; 
    document.getElementById('reservarBtn').style.display = 'none'; 
  });

  // Botón volver
  document.getElementById('volverBtn').addEventListener('click', function() { window.location.href = 'PrincipalCliente.html'; });
}

// Cargar vehículos al iniciar
window.addEventListener('DOMContentLoaded', () => {
  fetch('../api/apiVehiculos.php?listar=1')
    .then(res => res.json())
    .then(autos => {
      const autoSelect = document.getElementById('auto');
      autoSelect.innerHTML = '<option value="">Selecciona tu vehículo</option>';
      autos.forEach(a => autoSelect.innerHTML += `<option value="${a.id}" data-autonomia="${a.autonomia}">${a.marca} ${a.modelo} (${a.autonomia} km)</option>`);
    });
  document.getElementById('auto').addEventListener('change', function() {
    const val = this.options[this.selectedIndex].getAttribute('data-autonomia');
    selectedAuto = val ? parseFloat(val) : 0;
  });
	// Inicialmente deshabilitar el formulario de pago hasta que existan reservas pendientes
	setPaymentFormEnabled(false);
	// Asegurar que el overlay del modal esté oculto al inicio (evita que se muestre como contenido abajo)
	const overlayInit = document.getElementById('modalPagoOverlay');
	if (overlayInit) {
			overlayInit.classList.remove('open');
			overlayInit.style.display = 'none';
	}
});

// Función simulada para cargar slots
async function cargarSlotsDisponibles(idEstacion) {
    const datosSimulados = {
        '1': ['08:00', '09:00', '10:00', '11:00'],
        '2': ['14:30', '15:30', '16:30', '17:30'],
        '3': ['06:00', '12:00', '18:00', '21:00'],
        '4': ['No hay slots hoy'], 
    };
    return datosSimulados[idEstacion] || ['No hay slots hoy']; 
}

// Calcular ruta
document.getElementById('calcularBtn').addEventListener('click', async function() { 
  if (!origenCoords || !destinoCoords || !selectedAuto) return;
  directionsRenderer.set('directions', null);
  paradasMarkers.forEach(m => m.setMap(null));
  paradasMarkers = [];
  estacionesSugeridasConSlots = []; 
  document.getElementById('reservarBtn').style.display = 'none'; 
  document.getElementById('resultado').innerHTML = '<p>Calculando ruta y buscando estaciones...</p>';


  directionsService.route({
    origin: origenCoords,
    destination: destinoCoords,
    travelMode: google.maps.TravelMode.DRIVING
  }, async function(response, status) { 
    const resultadoDiv = document.getElementById('resultado');
    resultadoDiv.style.display = 'block';
    if (status !== 'OK') {
      resultadoDiv.innerHTML = `<b>Error:</b> No se pudo calcular la ruta.`;
      return;
    }

    directionsRenderer.setDirections(response);
    const routePath = response.routes[0].overview_path;
    const totalDistance = response.routes[0].legs[0].distance.value / 1000;

    let estacionesSugeridas = [];

    if (totalDistance <= selectedAuto) {
      resultadoDiv.innerHTML = `<b>Distancia total:</b> ${totalDistance.toFixed(1)} km<br>Autonomía suficiente para llegar al destino. No es necesario parar.`;
      document.getElementById('reservarBtn').style.display = 'inline-block';
      datosViajeReserva = { 
        id_vehiculo: parseInt(document.getElementById('auto').value,10), 
        estaciones: [] 
      };
      return;
    }

    const margenSeguridad = 0.9;
    let autonomiaRestante = selectedAuto * margenSeguridad;
    let ultimaPos = routePath[0];
    let ultimaEstacion = null;

    for (let i = 1; i < routePath.length; i++) {
      const curr = routePath[i];
      const tramo = google.maps.geometry.spherical.computeDistanceBetween(ultimaPos, curr)/1000;
      autonomiaRestante -= tramo;
      ultimaPos = curr;

      if (autonomiaRestante <= 0) {
        let estCercana = null;
        let minDist = Infinity;
        estaciones.forEach(est => {
          const estPos = new google.maps.LatLng(est.lat, est.lng);
          const dEst = google.maps.geometry.spherical.computeDistanceBetween(curr, estPos)/1000;
          if (dEst <= 10 && dEst < minDist) { minDist=dEst; estCercana=est; }
        });
        if (!estCercana && ultimaEstacion) estCercana = ultimaEstacion;
        if (estCercana && (estacionesSugeridas.length === 0 || estacionesSugeridas[estacionesSugeridas.length-1].id !== estCercana.id)) {
          estacionesSugeridas.push(estCercana);
          autonomiaRestante = selectedAuto * margenSeguridad;
          ultimaPos = new google.maps.LatLng(estCercana.lat, estCercana.lng);
          ultimaEstacion = estCercana;

          const marker = new google.maps.Marker({
            position: ultimaPos,
            map: map,
            label: `${estacionesSugeridas.length}`,
            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: '#0a0', fillOpacity: 1, strokeWeight: 2, strokeColor: '#fff' }
          });
          paradasMarkers.push(marker);
        }
      }
    }

    resultadoDiv.innerHTML = ''; 

    if (estacionesSugeridas.length === 0) {
      resultadoDiv.innerHTML = `<b>Distancia total:</b> ${totalDistance.toFixed(1)} km<br>No hay estaciones disponibles en la ruta para hacer paradas preventivas.`;
      document.getElementById('reservarBtn').style.display = 'none';
      datosViajeReserva = null;
    } else {
      resultadoDiv.innerHTML = `<b>Distancia total:</b> ${totalDistance.toFixed(1)} km<br><b>Paradas recomendadas:</b> <span style="font-size: 0.9em; display:block; margin-top:5px; color:#f00;">(Selecciona un horario para cada parada requerida)</span>`;
        
        estacionesSugeridasConSlots = []; // Limpiar antes de llenar

        for (let index = 0; index < estacionesSugeridas.length; index++) {
            const est = estacionesSugeridas[index];
            const slots = await cargarSlotsDisponibles(est.id); 
            
            let optionsHtml = '';
            let isAvailable = true;

            if (slots.includes('No hay slots hoy')) {
                optionsHtml = `<option value="" disabled selected>${slots[0]}</option>`;
                isAvailable = false;
            } else {
                optionsHtml = '<option value="" disabled selected>Elige hora</option>';
                slots.forEach(slot => {
                    optionsHtml += `<option value="${slot}">${slot}</option>`;
                });
            }

            resultadoDiv.innerHTML += `
                <div class="station" data-estacion-id="${est.id}">
                    <b>${index+1}. ${est.nombre}</b><br>
                    ${est.direccion}, ${est.departamento}<br>
                    <label for="slot_${est.id}">Hora de Reserva:</label>
                    <select id="slot_${est.id}" class="slot-selector" data-estacion-id="${est.id}" ${isAvailable ? '' : 'disabled'}>
                        ${optionsHtml}
                    </select>
                </div>
                <hr style="margin: 5px 0;">
            `;

            estacionesSugeridasConSlots.push({ 
                id_estacion: est.id, 
                orden: index + 1,
                slot_horario: null 
            });
        }
        
      document.getElementById('reservarBtn').style.display = 'inline-block';
    }
  });
});

const reservarBtn = document.getElementById('reservarBtn');
reservarBtn.addEventListener('click', function() {
    let paradasSeleccionadas = [];
    let todoSeleccionado = true;
    let totalReservas = 0;
    
    const slotSelectors = document.querySelectorAll('.slot-selector');
    
    slotSelectors.forEach(select => {
        const id_estacion = parseInt(select.getAttribute('data-estacion-id'), 10);
        const slot_horario = select.value;
        
        const paradaData = {
            id_estacion: id_estacion,
            orden: estacionesSugeridasConSlots.find(e => e.id_estacion == id_estacion).orden,
            slot_horario: (slot_horario === "") ? null : slot_horario 
        };

        // Solo revisamos y contamos si el selector no está deshabilitado
        if (!select.disabled) {
            if (slot_horario === "") {
                todoSeleccionado = false;
            } else {
                totalReservas++; 
            }
        }

        paradasSeleccionadas.push(paradaData);
    });

    if (!document.getElementById('auto').value) {
        alert('Debes seleccionar un vehículo.');
        return;
    }

    // Si hay paradas requeridas y no se eligió un slot para alguna.
    if (estacionesSugeridasConSlots.length > 0 && !todoSeleccionado) {
        alert('Por favor, selecciona un horario de reserva válido para todas las paradas requeridas que lo permitan.');
        return;
    }
    
    // 1. Prepara el objeto final de la reserva
    datosViajeReserva = {
        id_vehiculo: parseInt(document.getElementById('auto').value, 10),
        estaciones: paradasSeleccionadas
    };

    // 2. Almacena las reservas que se enviarán (solo las que tienen slot_horario)
    reservasPendientes = paradasSeleccionadas.filter(p => p.slot_horario !== null);

    // 3. Si no hay reservas con horario, confirma si quiere guardar solo el viaje
    if (reservasPendientes.length === 0) {
        if (confirm('No se seleccionaron horarios de reserva. ¿Deseas guardar solo el plan de viaje sin reservas de hora?')) {
            guardarViajeYReserva(datosViajeReserva); // Llama a la función de guardado directo
        }
        return;
    }
    
	// 4. Si hay reservas pendientes, mostrar resumen en el modal y abrirlo.
	// Solo el botón 'Ir a Pagar y Guardar Viaje' puede abrir el modal.
	if (reservasPendientes.length > 0) {
		document.getElementById('resumenReservasCount').textContent = `${reservasPendientes.length} slot(s)`;
		console.log('reservar: reservasPendientes=', reservasPendientes);
		openPaymentModal();
	}
});


// Lógica del Modal de Pago
document.getElementById('cerrarModalPago').addEventListener('click', () => {
	const overlay = document.getElementById('modalPagoOverlay');
	if (overlay) {
		overlay.classList.remove('open');
		overlay.style.display = 'none';
	}
	// Al cerrar, deshabilitar el formulario por seguridad
	setPaymentFormEnabled(false);
});

// Función para abrir el modal de pago de forma controlada
function openPaymentModal() {
	const overlay = document.getElementById('modalPagoOverlay');
	console.log('openPaymentModal called, reservasPendientes=', reservasPendientes);
	if (overlay) {
		// Forzar que el overlay esté como hijo directo del body (evita que estilos del contenedor lo muestren dentro del flujo)
		if (overlay.parentNode !== document.body) {
			document.body.appendChild(overlay);
			console.log('overlay moved to document.body');
		}
		// Forzar estilos inline para centrar en pantalla
		overlay.classList.add('open');
		overlay.style.position = 'fixed';
		overlay.style.top = '0';
		overlay.style.left = '0';
		overlay.style.width = '100%';
		overlay.style.height = '100%';
		overlay.style.display = 'flex';
		overlay.style.justifyContent = 'center';
		overlay.style.alignItems = 'center';
		overlay.style.padding = '20px';
		overlay.style.zIndex = '99999';
		// Asegurar que el contenido no desborde
		const contenido = overlay.querySelector('.modal-pago-contenido');
		if (contenido) {
			contenido.style.maxHeight = '90%';
			contenido.style.overflow = 'auto';
			contenido.style.margin = '0 auto';
		}
	} else {
		console.warn('openPaymentModal: overlay element not found');
	}
	// Al abrir el modal, habilitar el formulario solo si hay reservas pendientes
	const enable = reservasPendientes && reservasPendientes.length > 0;
	setPaymentFormEnabled(enable);
}

// Evento para PROCESAR el Pago (Simulado) y luego enviar las reservas
document.getElementById('formularioPago').addEventListener('submit', (e) => {
    e.preventDefault();
	// Protección: sólo permitir submit si hay reservas pendientes
	if (!reservasPendientes || reservasPendientes.length === 0) {
		alert('No hay reservas pendientes para procesar el pago.');
		setPaymentFormEnabled(false);
		document.getElementById('modalPagoOverlay').classList.remove('open');
		return;
	}

	// Simulación de éxito de pago
	document.getElementById('modalPagoOverlay').classList.remove('open');
	alert('Pago procesado correctamente (Simulado). Confirmando viaje y reservas...');

	// Llama a la función que guarda el viaje y las reservas en la DB
	guardarViajeYReserva(datosViajeReserva);
});


// 📌 Función de Guardado Final (Llamada después del pago o si no hay reservas)
function guardarViajeYReserva(datos) {
  reservarBtn.disabled = true;
  reservarBtn.textContent = 'Guardando...';
  fetch('../api/apiViaje.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ 
      id_vehiculo: datos.id_vehiculo,
      estaciones: datos.estaciones, 
      origen: origenCoords && origenCoords.departamento ? origenCoords.departamento : "N/A",
      destino: destinoCoords && destinoCoords.departamento ? destinoCoords.departamento : "N/A"
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('✅ ¡Viaje y reservas guardados con éxito!');
      location.reload();
    } else {
      document.getElementById('resultado').innerHTML += `<br><span style='color:red'>❌ Error al guardar: ${data.error || 'Desconocido'}</span>`;
      reservarBtn.textContent = 'Ir a Pagar y Guardar Viaje';
      reservarBtn.disabled = false;
    }
  })
  .catch(err => {
    document.getElementById('resultado').innerHTML += `<br><span style='color:red'>❌ Error de conexión al servidor.</span>`;
    reservarBtn.textContent = 'Ir a Pagar y Guardar Viaje';
    reservarBtn.disabled = false;
  });
}

window.initMap = initMap;
  </script>
</body>
</html>