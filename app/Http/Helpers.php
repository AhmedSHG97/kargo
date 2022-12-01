 <?php

use Laravel\Passport\Client;

if(!function_exists("getPassportClient")){
    function getPassportClient()
    {
        return Client::where("password_client", 1)->first();
    }
}

if(!function_exists("image_path")){
    function image_path($image)
    {
        return url("storage/" . $image);
    }
}

?>