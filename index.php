<?php
try {
    // Récupération des données de géolocalisation
    $latlongResponse = file_get_contents('https://ipapi.co/xml/');
    if ($latlongResponse === false) {
        throw new Exception("Impossible de contacter l'API IPAPI.");
    }

    $latlongData = new SimpleXMLElement($latlongResponse);
    $latitude = (string) $latlongData->latitude;
    $longitude = (string) $latlongData->longitude;

    if (empty($latitude) || empty($longitude)) {
        throw new Exception("Les données de géolocalisation sont invalides.");
    }

    // Récupération des incidents
    $apiUrl = "https://carto.g-ny.org/data/cifs/cifs_waze_v2.json";
    $data = file_get_contents($apiUrl);
    if ($data === false) {
        throw new Exception("Impossible de contacter l'API des incidents.");
    }
    $incidents = json_decode($data, true)['incidents'] ?? [];

    // Récupération des données météo
    $meteoDataUrl = "https://www.infoclimat.fr/public-api/gfs/xml?_ll=" . $latitude . "," . $longitude . "&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2";
    $meteoResponse = file_get_contents($meteoDataUrl);
    if ($meteoResponse === false) {
        throw new Exception("Impossible de contacter l'API météo d'Infoclimat.");
    }

    $xmlMeteo = new SimpleXMLElement($meteoResponse);

    // Transformation XSLT des données météo
    $xsl = new DOMDocument();
    if (!$xsl->load('meteoDuJour.xsl')) {
        throw new Exception("Le fichier XSL n'a pas pu être chargé.");
    }
    $proc = new XSLTProcessor();
    $proc->importStylesheet($xsl);
    $meteoHtml = $proc->transformToXML($xmlMeteo);
} catch (Exception $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Prévisions Météo</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }

        img {
            height: 30px;
            vertical-align: middle;
        }

        #map {
            height: 100vh;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div>
        <h1>Prévisions météo du jour</h1>
        <?php if (!empty($meteoHtml)): ?>
            <?= $meteoHtml ?>
        <?php else: ?>
            <p><?= $error ?? "Aucune donnée météo disponible." ?></p>
        <?php endif; ?>
    </div>

    <div>
        <h2>Carte des incidents</h2>
        <div id="map"></div>
    </div>
</body>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Initialisation de la carte avec la position de l'utilisateur
    const userLatitude = <?= json_encode($latitude ?? 48.688) ?>;
    const userLongitude = <?= json_encode($longitude ?? 6.18) ?>;

    const map = L.map('map').setView([userLatitude, userLongitude], 13);

    // Ajout de la couche OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Ajout des incidents sur la carte
    const incidents = <?= json_encode($incidents); ?>;

    incidents.forEach(incident => {
        const {
            location,
            description
        } = incident;
        if (location && location.polyline) {
            const [lat, lng] = location.polyline.split(' ').map(Number);

            L.marker([lat, lng])
                .addTo(map)
                .bindPopup(`
                    <strong>${location.location_description || 'Description indisponible'}</strong><br>
                    ${description || 'Aucune information'}
                `);
        }
    });
</script>

</html>