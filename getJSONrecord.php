<?php
require_once "geocode.php";

function getJSONcallData($cs1) {
    $curl = curl_init();

    $url = "https://callook.info/" . urlencode($cs1) . "/json";

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => true,
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        throw new Exception("CURL Error #:" . $err);
    }

    $crc = json_decode($response, true);

    if ($crc === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Failed to parse JSON response");
    }

    $status    = $crc['status'] ?? '';
    $cs1       = $crc['current']['callsign'] ?? '';
    $operClass = $crc['current']['operClass'] ?? '';
    $name      = $crc['name'] ?? '';
    $latitude  = $crc['location']['latitude'] ?? '';
    $longitude = $crc['location']['longitude'] ?? '';
    $grid      = $crc['location']['gridsquare'] ?? '';
    $addr1     = $crc['address']['line1'] ?? '';
    $addr2     = $crc['address']['line2'] ?? '';
    $expires   = $crc['otherInfo']['expiryDate'] ?? '';

    $firstLogIn = 1;

    if ($status == 'VALID') {
        $parts = explode(' ', $name);
        $Fname = ucfirst(strtolower(array_shift($parts)));
        $Lname = ucfirst(strtolower(array_pop($parts)));
        $Mname = trim(implode(' ', $parts));

        $koords = geocode("$addr1 $addr2");
        $county = $koords[2] ?? '';
        $state  = $koords[3] ?? '';

        if (empty($state)) {
            $state = $state2 ?? '';
        }

        $home     = "$latitude,$longitude,$grid,$county,$state";
        $comments = "First Log In";

    } elseif ($status == 'INVALID') {
        $comments = "No FCC Record";
        $cs1 = $cs0 ?? '';
    }

    return [
        'status'    => $status,
        'callsign'  => $cs1,
        'operClass' => $operClass,
        'name'      => $name,
        'latitude'  => $latitude,
        'longitude' => $longitude,
        'grid'      => $grid,
        'addr1'     => $addr1,
        'addr2'     => $addr2,
        'expires'   => $expires,
        'firstLogIn' => $firstLogIn,
        'comments'  => $comments,
        'home'      => $home ?? '',
    ];
}
?>