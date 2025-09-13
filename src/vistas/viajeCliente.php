<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Planificar Viaje</title>
  <link rel="stylesheet" href="../assets/css/viajeCliente.css">
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
      <button id="reservarBtn" style="display:none;">Reservar y guardar viaje</button>
      <button id="volverBtn" style="margin-left:10px; background:#555; color:#fff;">Volver a Principal</button>
    </div>

    <div id="resultado" class="result" style="display:none;"></div>
    <div id="infoClick" style="margin-top:16px;"></div>
  </div>

  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4HgNqmymEub5LY68nN-s-ONF-i931SCE&callback=initMap&libraries=geometry"></script>

  <script>
let map, boundsUruguay, origenMarker=null, destinoMarker=null, origenCoords=null, destinoCoords=null;
let clickCount=0, selectedAuto=0, estaciones=[], directionsService=null, directionsRenderer=null;
let paradasMarkers=[], datosViajeReserva=null, geocoder;

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
});

// Calcular ruta
document.getElementById('calcularBtn').addEventListener('click', function() {
  if (!origenCoords || !destinoCoords || !selectedAuto) return;
  directionsRenderer.set('directions', null);
  paradasMarkers.forEach(m => m.setMap(null));
  paradasMarkers = [];

  directionsService.route({
    origin: origenCoords,
    destination: destinoCoords,
    travelMode: google.maps.TravelMode.DRIVING
  }, function(response, status) {
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
        estaciones: [] // sin paradas
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

    if (estacionesSugeridas.length === 0) {
      resultadoDiv.innerHTML = `<b>Distancia total:</b> ${totalDistance.toFixed(1)} km<br>No hay estaciones disponibles en la ruta para hacer paradas preventivas.`;
      document.getElementById('reservarBtn').style.display = 'none';
      datosViajeReserva = null;
    } else {
      resultadoDiv.innerHTML = `<b>Distancia total:</b> ${totalDistance.toFixed(1)} km<br><b>Paradas recomendadas:</b>`;
      estacionesSugeridas.forEach((est,index) => {
        resultadoDiv.innerHTML += `<div class="station"><b>${index+1}. ${est.nombre}</b><br>${est.direccion}<br>${est.departamento}</div>`;
      });
      document.getElementById('reservarBtn').style.display = 'inline-block';
      datosViajeReserva = { 
        id_vehiculo: parseInt(document.getElementById('auto').value,10), 
        estaciones: estacionesSugeridas.map((e, idx) => ({ id_estacion: e.id, orden: idx+1 }))
      };
    }
  });
});

const reservarBtn = document.getElementById('reservarBtn');
reservarBtn.addEventListener('click', function() {
  if (!datosViajeReserva) return;
  guardarViajeYReserva(datosViajeReserva);
});

// 📌 Guardar viaje + reservas + paradas
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
      alert('¡Viaje y reservas guardados!');
      location.reload();
    } else {
      document.getElementById('resultado').innerHTML += `<br><span style='color:red'>Error al guardar: ${data.error || 'Desconocido'}</span>`;
      reservarBtn.textContent = 'Reservar y guardar viaje';
      reservarBtn.disabled = false;
    }
  })
  .catch(err => {
    document.getElementById('resultado').innerHTML += `<br><span style='color:red'>Error de conexión</span>`;
    reservarBtn.textContent = 'Reservar y guardar viaje';
    reservarBtn.disabled = false;
  });
}

window.initMap = initMap;
  </script>
</body>
</html>
