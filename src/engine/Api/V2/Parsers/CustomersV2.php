<?php
namespace EA\Engine\Api\V2\Parsers;
use \EA\Engine\Api\V1\Parsers\Customers;
class CustomersV2 extends Customers {
	public function encode(array &$response)
    {
        $id_integrated = $response['id_integrated'];
        
        parent::encode($response);
        
        $response['id_integrated'] = $id_integrated;
	}
	public function decode(array &$request, array $base = NULL)
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