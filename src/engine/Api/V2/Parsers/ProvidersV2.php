<?php
namespace EA\Engine\Api\V2\Parsers;
use \EA\Engine\Api\V1\Parsers\Providers;
class ProvidersV2 extends Providers {
	public function encode(array &$response)
    {
        $id_integrated = $response['id_integrated'];
        
        parent::encode($response);
        
        $response['id_integrated'] = $id_integrated;
	}
	public function decode($request, array $base = NULL)
    {
        if ( ! empty($request['id_integrated']))
        {
            $id_integrated = $request['id_integrated'];
        }
        parent::decode($request);
            
        if(isset($id_integrated)) {
        $request['id_integrated'] = $id_integrated;
        }
	}
}
?>