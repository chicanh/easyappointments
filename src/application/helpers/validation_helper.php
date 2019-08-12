<?php defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('validateInputRequestParamsForStatictis'))
{
    function validateInputRequestParamsForStatictis($requestParams)
    {
        $cities = $requestParams->get('cities');
        $startDate = $requestParams->get('startDate');
        $endDate = $requestParams->get('endDate');
        $gender = $requestParams->get('gender');
        $idProviderIntegrated = $requestParams->get('id_provider_integrated');
        $firstTime = checkIsValidBooleanType($requestParams->get('firstTime')) ? $requestParams->get('firstTime') : null;
        $useHealthInsurance = checkIsValidBooleanType($requestParams->get('useHealthInsurance')) ? $requestParams->get('useHealthInsurance') : null;
        if (!trim($startDate)) {
            $startDate = date("Y-m") . '-01';
        }

        if (!trim($endDate)) {
            $endDate = date("Y-m-t");
        }

        $endDate .= ' 23:59:59';
        if (trim($gender) && $gender !== 'male' && $gender !== 'female') {
            throw new \EA\Engine\Api\V1\Exception('Gender must be male or female', 400);
        }
        if ($cities !== null && $cities !== '' && !containsOnlyNumber($cities)) {
            throw new \EA\Engine\Api\V1\Exception('cities must contains only numbers', 400);
        }
        if (getYearFromDateString($startDate) < 1905) {
            throw new \EA\Engine\Api\V1\Exception('startDate must greater than 1905', 400);
        }
        if (getYearFromDateString($endDate) > date("Y")) {
            throw new \EA\Engine\Api\V1\Exception('endDate must less than current year', 400);
        }

        return ["cities" => $cities,
            "startDate" => $startDate,
            "endDate" => $endDate,
            "gender" => $gender,
            "id_provider_integrated" => $id_provider_integrated,
            "firstTime" => $firstTime,
            "useHealthInsurance" => $useHealthInsurance];
    }

    function checkIsValidBooleanType($value)
    {
        return $value !== null && $value !== '' && $value === 'TRUE' || $value == 'FALSE';
    }

    function getYearFromDateString($dateInString)
    {
        return intval(explode("-", $dateInString)[0]);
    }

    function containsOnlyNumber($array)
    {
        $allNumeric = true;
        foreach ($array as $value) {
            if (!(is_numeric($value))) {
                return false;
            }
        }
        return true;
    }
}