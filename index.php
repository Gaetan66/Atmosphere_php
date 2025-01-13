<?php
$opts = array(
    'http' => array(
        'proxy' => 'tcp://127.0.0.1:8080',
        'request_fulluri' => true
    ),
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false
    )
);
$context = stream_context_create($opts);

try {
    $latlongResponse = file_get_contents('https://ipapi.co/xml/', false, $context);
    if ($latlongResponse === false) {
        throw new Exception("Impossible de contacter l'API IPAPI.");
    }

    $latlongData = new SimpleXMLElement($latlongResponse);
    $latitude = (string) $latlongData->latitude;
    $longitude = (string) $latlongData->longitude;

    if (empty($latitude) || empty($longitude)) {
        throw new Exception("Les données de géolocalisation sont invalides.");
    }

    $iutLatitude = 48.693722;
    $iutLongitude = 6.18341;

    function isNearby($lat1, $lon1, $lat2, $lon2, $tolerance = 0.01) {
        return (abs($lat1 - $lat2) < $tolerance) && (abs($lon1 - $lon2) < $tolerance);
    }

    if (!isNearby($latitude, $longitude, $iutLatitude, $iutLongitude)) {
        $latitude = $iutLatitude;
        $longitude = $iutLongitude;
    }

    $apiUrl = "https://carto.g-ny.org/data/cifs/cifs_waze_v2.json";
    $data = file_get_contents($apiUrl, false, $context);
    if ($data === false) {
        throw new Exception("Impossible de contacter l'API des incidents.");
    }
    $incidents = json_decode($data, true)['incidents'] ?? [];

    $meteoDataUrl = "https://www.infoclimat.fr/public-api/gfs/xml?_ll=" . $latitude . "," . $longitude . "&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2";
    $meteoResponse = file_get_contents($meteoDataUrl, false, $context);
    if ($meteoResponse === false) {
        throw new Exception("Impossible de contacter l'API météo d'Infoclimat.");
    }

    $xmlMeteo = new SimpleXMLElement($meteoResponse);

    $xsl = new DOMDocument();
    if (!$xsl->load('meteoDuJour.xsl')) {
        throw new Exception("Le fichier XSL n'a pas pu être chargé.");
    }
    $proc = new XSLTProcessor();
    $proc->importStylesheet($xsl);
    $meteoHtml = $proc->transformToXML($xmlMeteo);

    $url = "https://services3.arcgis.com/Is0UwT37raQYl9Jj/arcgis/rest/services/ind_grandest/FeatureServer/0/query?where=lib_zone%3D'Nancy'&outFields=date_ech,lib_zone,lib_qual&f=pjson";
    $air = file_get_contents($url, false, $context);
    if ($air === false) {
        die("Erreur : Impossible de récupérer les données de l'API.");
    }
    $airJson = json_decode($air, true);

    $latestFeature = null;
    $today = (new DateTime())->format('Y-m-d');

    if (isset($airJson["features"])) {
        foreach ($airJson["features"] as $feature) {
            if (isset($feature["attributes"]["date_ech"], $feature["attributes"]["lib_zone"], $feature["attributes"]["lib_qual"])) {
                $timestamp = $feature["attributes"]["date_ech"] / 1000; // L'API utilise des millisecondes
                $featureDate = (new DateTime("@$timestamp"))->format('Y-m-d');

                if ($feature["attributes"]["lib_zone"] === 'Nancy' && $featureDate === $today) {
                    $latestFeature = $feature;
                    break;
                }
            }
        }
    }

    if ($latestFeature) {
        $pollution = $latestFeature["attributes"]["lib_qual"];
    } else {
        $pollution = "Aucune donnée de pollution disponible pour Nancy aujourd'hui.";
    }

} catch (Exception $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>
