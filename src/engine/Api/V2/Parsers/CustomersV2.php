<?php
namespace EA\Engine\Api\V2\Parsers;
use \EA\Engine\Api\V1\Parsers\Customers;
class CustomersV2 extends Customers {
	// public function encode(array &$response)
    // {
    //     $id_integrated = $response['id_integrated'];
        
    //     parent::encode($response);
        
    //     $response['id_integrated'] = $id_integrated;
    // }
    public function encode(array &$response)
    {
        $encodedResponse = [
            'id' => $response['id'] !== NULL ? (int)$response['id'] : NULL,
            'firstName' => $response['first_name'],
            'lastName' => $response['last_name'],
            'email' => $response['email'],
            'phone' => $response['phone_number'],
            'address' => $response['address'],
            'id_integrated' => $response['id_integrated'],
            'gender' => $response['gender'],
            'national_id' => $response['national_id'],
            'birthday' => $response['birthday'],
            'photo_profile' => $response['photo_profile'],
            'cityId' => $response['city_id'],
            'districtId' => $response['district_id'],
            'wardId' => $response['ward_id']
        ];

        $response = $encodedResponse;
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
        if ( ! empty($request['national_id']))
        {
            $national_id = $request['national_id'];
        }
        if ( ! empty($request['gender']))
        {
            $gender = $request['gender'];
        }
        if ( ! empty($request['birthday']))
        {
            $birthday = $request['birthday'];
        }
        parent::decode($request);
            
        if(isset($id_integrated)) {
        $request['id_integrated'] = $id_integrated;
        }
        if(isset($photo_profile)) {
            $request['photo_profile'] = $photo_profile;
        }
        if(isset($national_id)) {
            $request['national_id'] = $national_id;
        }
        if(isset($gender)) {
            $request['gender'] = $gender;
        }
        if(isset($birthday)) {
            $request['birthday'] = $birthday;
        }
        if(!empty($request['cityId'])){
            $request['city_id'] = $request['cityId'];
        }
        if(!empty($request['districtId'])){
            $request['district_id'] = $request['districtId'];
        }
        if(!empty($request['wardId'])){
            $request['ward_id'] = $request['wardId'];
        }
    }
    

    public function customEncode($response)
    {
        $encodedResponse = [
            'id' => $response['id'] !== NULL ? (int)$response['id'] : NULL,
            'firstName' => $response['first_name'],
            'lastName' => $response['last_name'],
            'email' => $response['email'],
            'phone' => $response['phone_number'],
            'address' => $response['address'],
            'id_integrated' => $response['id_integrated'],
            'gender' => $response['gender'],
            'national_id' => $response['national_id'],
            'birthday' => $response['birthday'],
            'photo_profile' => $response['photo_profile'],
            'city' => $response['city_id'],
            'district' => $response['district_id'],
            'ward' => $response['ward_id']
        ];

        return $encodedResponse;
    }
}
?>