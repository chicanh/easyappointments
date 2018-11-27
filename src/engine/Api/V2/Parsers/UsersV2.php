<?php
namespace EA\Engine\Api\V2\Parsers;
use EA\Engine\Api\V1\Parsers\Customers;

class UsersV2 extends Customers {
	public function encode(array &$response)
    {
        $encodedResponse = [
            'id' => $response['id'] !== NULL ? (int)$response['id'] : NULL,
            'firstName' => $response['first_name'],
            'lastName' => $response['last_name'],
            'email' => $response['email'],
            'phone' => $response['phone_number'],
            'address' => $response['address'],
            'city' => $response['city'],
            'state' => $response['state'],
            'zip' => $response['zip_code'],
            'notes' => $response['notes'],
            'idIntegrated' => $response['id_integrated']
        ];

        $response = $encodedResponse;
	}
	public function decode(array &$request, array $base = NULL)
    {
        $decodedRequest = $base ?: [];

        if ( ! empty($request['id']))
        {
            $decodedRequest['id'] = $request['id'];
        }

        if ( ! empty($request['firstName']))
        {
            $decodedRequest['first_name'] = $request['firstName'];
        }

        if ( ! empty($request['lastName']))
        {
            $decodedRequest['last_name'] = $request['lastName'];
        }

        if ( ! empty($request['email']))
        {
            $decodedRequest['email'] = $request['email'];
        }

        if ( ! empty($request['phone']))
        {
            $decodedRequest['phone_number'] = $request['phone'];
        }

        if ( ! empty($request['address']))
        {
            $decodedRequest['address'] = $request['address'];
        }

        if ( ! empty($request['city']))
        {
            $decodedRequest['city'] = $request['city'];
        }

        if ( ! empty($request['zip']))
        {
            $decodedRequest['zip_code'] = $request['zip'];
        }

        if ( ! empty($request['notes']))
        {
            $decodedRequest['notes'] = $request['notes'];
        }

        if ( ! empty($request['idIntegrated']))
        {
            $decodedRequest['id_integrated'] = $request['idIntegrated'];
        }

        $request = $decodedRequest;
	}
}
?>