<?php
namespace EA\Engine\Api\V2\Parsers;
use \EA\Engine\Api\V1\Parsers\Providers;
class ProvidersV2 extends Providers {
	public function encode(array &$response)
    {
        $id_integrated = $response['id_integrated'];
        $photo_profile = $response['photo_profile'];
        $price = $response['price'];
        $currency = $response['currency'];
        parent::encode($response);
        
        $response['id_integrated'] = $id_integrated;
        $response['photo_profile'] = $photo_profile;
        $response['currency'] = $currency;
        $response['price'] = $price;
	}
	public function decode(array &$request, array $base = NULL)
    {
        if ( ! empty($request['id_integrated']))
        {
            $id_integrated = $request['id_integrated'];
        }
        if ( ! empty($request['photo_profile']))
        {
            $photo_profile = $request['photo_profile'];
        }
        if ( ! empty($request['price']))
        {
            $price = $request['price'];
        }
        if ( ! empty($request['currency']))
        {
            $currency = $request['currency'];
        }
        parent::decode($request);
            
        if(isset($id_integrated)) {
        $request['id_integrated'] = $id_integrated;
        }
        if(isset($photo_profile)) {
            $request['photo_profile'] = $photo_profile;
        }
        if(isset($price)) {
            $request['price'] = $price;
        }
        if(isset($currency)) {
            $request['currency'] = $currency;
        }
	}
}
?>