<?php
class Image
{
    public static function uploadImage($formname, $query, $params)
    {
        $image = base64_encode(file_get_contents($_FILES[$formname]['tmp_name']));

        $options = array('http' => array(
            'method'  => "POST",
            'header'  => "Authorization: Bearer dc4a6a66bede08b2eecd5b6bb1e20a270fcf06e8\n" .
            "Content-Type: application/x-www-form-urlencoded",
            'content' => $image,
        ));

        $context = stream_context_create($options);

        $imgurURL = "https://api.imgur.com/3/image";
        $response = file_get_contents($imgurURL, false, $context);
        $response = json_decode($response);

        $preparams = array($formname => $response->data->link);
        $params    = $preparams + $params;

        Database::runQuery($query, $params);
    }
}
// access_token=dc4a6a66bede08b2eecd5b6bb1e20a270fcf06e8
// refresh_token=c8765e21790fdbffabadcc51dbdc3741d2922023