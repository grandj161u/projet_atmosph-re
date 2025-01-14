<?php
// Configuration du proxy pour tout le script
// $opts = array(
//     'http' => array(
//         'proxy' => 'tcp://www-cache:3128',  // Modifié pour utiliser le proxy webetu
//         'request_fulluri' => true
//     ),
//     'ssl' => array(
//         'verify_peer' => false,
//         'verify_peer_name' => false
//     )
// );
// $context = stream_context_create($opts);
// stream_context_set_default($opts);

// Fonction pour obtenir l'adresse IP de l'utilisateur
function fetchUserIP()
{
    $url = "https://api64.ipify.org?format=json";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("IP fetch error: " . curl_error($ch));
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    $data = json_decode($response, true);
    return $data['ip'] ?? null;
}

// Obtenir l'adresse IP du client
$clientIp = fetchUserIP();

// Coordonnées par défaut de l'IUT Charlemagne
$lat = '';
$lon = '';

$address = "2Ter Bd Charlemagne, 54000 Nancy, France";

// Fonction pour obtenir les informations de géolocalisation en XML
function getGeolocation($ip)
{
    $url = "https://ipapi.co/{$ip}/xml";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_PROXY, 'www-cache:3128');
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    if ($response) {
        $xml = simplexml_load_string($response);

        return $xml;
    }

    return false;
}

function getDonneesMeteo($lat, $lon)
{
    $url = "https://www.infoclimat.fr/public-api/gfs/xml?_ll={$lat},{$lon}&_auth=AhhWQQF%2FAyFQfQM0AXdSewVtV2IBdwAnBXkKaQhtVClVPl8%2BBGRdO18xBntXeAQyVHlSMQA7ATFQO1YuCngEZQJoVjoBagNkUD8DZgEuUnkFK1c2ASEAJwVnCmQIZlQpVTNfOwR5XT5fMwZhV3kEMlRnUjEAIAEmUDJWNgpkBGECZlY6AWQDZ1A7A2kBLlJ5BTBXMAE2AD0FYgpvCDFUP1ViX24EM11pXzcGZVd5BDdUZFI7AD4BMFA2VjYKYQR4An5WSwERA3xQfwMjAWRSIAUrV2IBYABs&_c=5c56fd0d26e18569c80798b16aba4ba3";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_PROXY, 'www-cache:3128');
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    if ($response) {
        $xml = simplexml_load_string($response);
        return $xml;
    }

    return false;
}

function getTrafficData()
{
    $url = "https://carto.g-ny.org/data/cifs/cifs_waze_v2.json";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    if ($response) {
        $json = json_decode($response, true);
        return $json;
    }

    return false;
}

function getAirQuality($latitude, $longitude, $apiKey)
{
    $url = "http://api.openweathermap.org/data/2.5/air_pollution?lat=$latitude&lon=$longitude&appid=$apiKey";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    if ($response) {
        return json_decode($response, true);
    } else {
        return false;
    }
}

function geocodeAddress($address)
{
    $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($address) . "&format=json&limit=1";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'projet1/1.0 (matheo.grandjean8@etu.univ-lorraine.fr)');

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    if ($response) {
        $json = json_decode($response, true);
        if (!empty($json)) {
            return $json[0];
        }
    }

    return false;
}

// Géolocaliser l'adresse IP du client
$geoData = getGeolocation($clientIp);

// Traitement de la géolocalisation
if ($geoData && isset($geoData->city) && strtolower((string)$geoData->city) === 'nancy') {
    $lat = (string)$geoData->latitude;
    $lon = (string)$geoData->longitude;

    // Récupérer les données météo
    $meteoData = getDonneesMeteo($lat, $lon);

    // Récupérer les données de qualité de l'air
    // $airQualityData = getAirQuality($lat, $lon, '7f90c153976644c4e25160ecb8e6c547');
} else {
    // Coordonnées par défaut de l'IUT Charlemagne
    echo 'Impossible de géolocaliser l\'adresse IP. Utilisation des coordonnées par défaut.';
    $lat = '48.682298';
    $lon = '6.161118';
}

// Récupérer les données de trafic
$trafficData = getTrafficData();

$geocodedAddress = geocodeAddress($address);
$addressLat = $geocodedAddress ? $geocodedAddress['lat'] : null;
$addressLon = $geocodedAddress ? $geocodedAddress['lon'] : null;

// Récupérer l'heure actuelle
$currentHour = date('H');

// // En-têtes pour éviter les problèmes de cache
// header('Content-Type: text/html; charset=utf-8');
// header('Cache-Control: no-cache, no-store, must-revalidate');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Météo et Circulation - Nancy</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>

<body>
    <?php
    // Intégration de la météo via XSLT
    if ($meteoData && file_exists('feuilleStyleMeteo.xsl')) {
        $xsl = new DOMDocument();
        $xsl->load('feuilleStyleMeteo.xsl');

        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl);

        $proc->setParameter('', 'currentHour', $currentHour);

        // if ($airQualityData) {
        //     var_dump($airQualityData);
        //     $proc->setParameter('', 'airQualityIndex', $airQualityData['list'][0]['main']['aqi']);
        //     $proc->setParameter('', 'airQualityComponents', json_encode($airQualityData['list'][0]['components']));
        // }

        $htmlFragment = $proc->transformToXML($meteoData);
        echo $htmlFragment;
    } else {
        echo '<p>Les données météo ne sont pas disponibles pour le moment.</p>';
    }
    ?>

    <div id="map" style="height: 400px;"></div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([<?php echo $lat; ?>, <?php echo $lon; ?>], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var clientIcon = L.icon({
            iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            shadowSize: [41, 41]
        });

        L.marker([<?php echo $lat; ?>, <?php echo $lon; ?>], {
            icon: clientIcon
        }).addTo(map).bindPopup('Votre position');

        <?php if ($addressLat && $addressLon): ?>
            L.marker([<?php echo $addressLat; ?>, <?php echo $addressLon; ?>]).addTo(map).bindPopup('<?php echo $address; ?>');
        <?php endif; ?>

        <?php if ($trafficData && isset($trafficData['incidents'])): ?>
            var trafficData = <?php echo json_encode($trafficData['incidents']); ?>;
            trafficData.forEach(function(incident) {
                var coords = incident.location.polyline.split(' ');
                var lat = parseFloat(coords[0]);
                var lon = parseFloat(coords[1]);
                var marker = L.marker([lat, lon]).addTo(map);
                marker.bindPopup('<b>Problème de circulation:</b> ' + incident.short_description + '<br><b>Description:</b> ' + incident.description + '<br><b>Dates:</b> ' + incident.starttime + ' - ' + incident.endtime);
            });
        <?php endif; ?>
    </script>

    <br>
    <a href="https://ipapi.co/<?php echo $clientIp ?>/xml">lien localisation IP</a>
    <br>
    <a href="https://www.infoclimat.fr/public-api/gfs/xml?_ll=<?php echo $lat ?>,<?php echo $lon ?>&_auth=AhhWQQF%2FAyFQfQM0AXdSewVtV2IBdwAnBXkKaQhtVClVPl8%2BBGRdO18xBntXeAQyVHlSMQA7ATFQO1YuCngEZQJoVjoBagNkUD8DZgEuUnkFK1c2ASEAJwVnCmQIZlQpVTNfOwR5XT5fMwZhV3kEMlRnUjEAIAEmUDJWNgpkBGECZlY6AWQDZ1A7A2kBLlJ5BTBXMAE2AD0FYgpvCDFUP1ViX24EM11pXzcGZVd5BDdUZFI7AD4BMFA2VjYKYQR4An5WSwERA3xQfwMjAWRSIAUrV2IBYABs&_c=5c56fd0d26e18569c80798b16aba4ba3">lien météo</a>
    <br>
    <a href="https://carto.g-ny.org/data/cifs/cifs_waze_v2.json">lien traffic</a>
    <br>
    <a href="https://nominatim.openstreetmap.org/search?q=<?php echo $address ?>&format=json&limit=1">lien geocode</a>

</body>

</html>