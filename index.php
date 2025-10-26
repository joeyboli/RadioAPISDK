<?php

require_once 'vendor/autoload.php';

use RadioAPI\RadioAPI;

$rp = new RadioAPI();
$rp->setBaseUrl('https://core-api.streamafrica.net')
    ->setThrowOnApiErrors(false);

$colorthief = $rp->colorThief();

try {
    // Fetch colors and get the full response
    $response = $colorthief->getColors('https://i.ibb.co/1fM9C56b/album-art-1735237013.png');

    // Get specific colors
    $textColorHex = $colorthief->getTextColorHex();
    $dominantColorHex = $colorthief->getDominantColorHex();

    echo "Text Color: " . $textColorHex . "\n";
    echo "Dominant Color: " . $dominantColorHex . "\n";

    // Or get all data
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}