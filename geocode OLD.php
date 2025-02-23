<?php
// geocode.php
// Function to geocode address, it will return false if unable to geocode address
// V2 Updated: 2024-06-03

require_once "dbConnectDtls.php";
require_once "ENV_SETUP.php";

function geocode($address) {
    // URL encode the address
    $address = urlencode($address);
    
    $google_api_key = getenv('GOOGLE_MAPS_API_KEY');
    
    // Google Maps Geocoding API URL
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$google_api_key}";
    
    // Initialize cURL session
    $curl = curl_init();
    
    // Set cURL options
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
    ]);
    
    // Execute cURL request
    $resp_json = curl_exec($curl);
    
    // Check for cURL errors
    if ($resp_json === false) {
        $error = curl_error($curl);
        error_log("cURL Error: $error");
        curl_close($curl);
        return false;
    }
    
    // Close cURL session
    curl_close($curl);
    
    // Decode the JSON response
    $resp = json_decode($resp_json, true);
    
    // Check for JSON decoding errors
    if ($resp === null && json_last_error() !== JSON_ERROR_NONE) {
        $error = json_last_error_msg();
        error_log("JSON Decoding Error: $error");
        return false;
    }
    
    // Check the response status
    if ($resp['status'] === 'OK') {
        // Get the important data
        $lati = $resp['results'][0]['geometry']['location']['lat'] ?? null;
        $longi = $resp['results'][0]['geometry']['location']['lng'] ?? null;
        
        // Initialize variables
        $county = '';
        $state = '';
        
        // Find the county and state from address components
        foreach ($resp['results'][0]['address_components'] as $comp) {
            foreach ($comp['types'] as $currType) {
                if ($currType === 'administrative_area_level_2') {
                    $county = str_replace('County', '', $comp['long_name']);
                } elseif ($currType === 'administrative_area_level_1') {
                    $state = $comp['short_name'];
                }
            }
        }
        
        $formatted_address = $resp['results'][0]['formatted_address'] ?? '';
        
        // Verify if data is complete
        if ($lati && $longi && $formatted_address) {
            $koords = [$lati, $longi, $county, $state];
            return $koords;
        } else {
            error_log("Incomplete geocoding data for address: $address");
            return false;
        }
    } else {
        error_log("Geocoding failed for address: $address");
        return false;
    }
}
?>