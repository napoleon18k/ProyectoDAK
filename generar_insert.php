
<?php
// Tu API Key de Google
$apiKey = "AIzaSyDVOcB7ciYCCVtohJianbwEu4kB63dfVxo";

// Leemos el archivo JSON
$archivo = 'estaciones.json';
$contenido = file_get_contents($archivo);
$estaciones = json_decode($contenido, true);

// Función para obtener coordenadas usando Google Maps API
function getCoordinates($direccion, $apiKey) {
    $direccion = urlencode($direccion);
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$direccion}&key={$apiKey}";
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'OK') {
        $lat = $json['results'][0]['geometry']['location']['lat'];
        $lng = $json['results'][0]['geometry']['location']['lng'];
        return [$lat, $lng];
    } else {
        return [0, 0]; // Si no encuentra coordenadas
    }
}

// Recorremos todas las estaciones y generamos los INSERT
foreach ($estaciones as $est) {
    $nombre = addslashes($est['nombre']);
    $departamento = addslashes($est['departamento']);
    $direccion = addslashes($est['direccion']);
    $cargadores_json = addslashes(json_encode($est['cargadores'], JSON_UNESCAPED_UNICODE));

    // Obtenemos lat y lng desde la API
    list($lat, $lng) = getCoordinates($direccion, $apiKey);

    echo "INSERT INTO estaciones (nombre, direccion, departamento, lat, lng, cargadores) VALUES ('$nombre', '$direccion', '$departamento', '$lat', '$lng', '$cargadores_json');<br>\n";
}
?>
