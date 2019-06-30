<?php
namespace EA\Engine\Api\V2\Parsers;
use \EA\Engine\Api\V1\Parsers\Providers;
class ProvidersV2 extends Providers {
	public function encode(array &$response)
    {
        $id_integrated = $response['id_integrated'];
        $photo_profile = $response['photo_profile'];
        $fee = $response['fee'];
        $currency = $response['currency'];
        $default = $response['default'];
        if (array_key_exists('categories', $response))
        {
            $categories = $response['categories'];
        }
        parent::encode($response);
        
        $response['id_integrated'] = $id_integrated;
        $response['photo_profile'] = $photo_profile;
        $response['currency'] = $currency;
        $response['fee'] = $fee;
        $response['default'] = $default;
        $response['categories'] = $categories;
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
        if ( ! empty($request['fee']))
        {
            $fee = $request['fee'];
        }

        if ( ! empty($request['currency']))
        {
            $currency = $request['currency'];
        }

        if ( ! empty($request['default']))
        {
            $default = $request['default'];
        }

        if ( ! empty($request['categories']))
        {
            $categories = $request['categories'];
        }

        parent::decode($request);
            
        if(isset($id_integrated)) {
        $request['id_integrated'] = $id_integrated;
        }
        if(isset($photo_profile)) {
            $request['photo_profile'] = $photo_profile;
        }
        if(isset($fee)) {
            $request['fee'] = $fee;
        }
        if(isset($currency)) {
            $request['currency'] = $currency;
        }

        if(isset($default)) {
            $request['default'] = $default;
        }
        if ( ! empty($request['categories']))
        {
            $request['categories'] = $categories;
        }
	}
}
?>