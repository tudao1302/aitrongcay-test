<?php
$url = 'https://across-reliable-openings-priest.trycloudflare.com/api/frame.jpeg?src=vuon1_snap';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'WordPress/6.0');
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
file_put_contents('cf_test.log', "HTTP Code: $httpcode\nResponse: " . substr($response, 0, 500));
