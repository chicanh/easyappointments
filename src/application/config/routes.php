<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "appointments";
$route['404_override'] = 'errors/error404';


/*
| -------------------------------------------------------------------------
| REST API ROUTING
| -------------------------------------------------------------------------
| The following routes will point the API calls into the correct controller
| callback methods. This routes also define the HTTP verbs that they are 
| used for each operation.
|
*/

$resources = [
    'appointments',
    'unavailabilities',
    'customers',
    'services',
    'categories',
    'admins',
    'providers',
    'secretaries',
    'attachment',
    'attendants',
    'patients'
];

foreach ($resources as $resource)
{
    $route['api/v1/' . $resource]['post'] = 'api/v1/' . $resource . '/post';
    $route['api/v1/' . $resource . '/(:num)']['put'] = 'api/v1/' . $resource . '/put/$1';
    $route['api/v1/' . $resource . '/(:num)']['delete'] = 'api/v1/' . $resource . '/delete/$1';
    $route['api/v1/' . $resource]['get'] = 'api/v1/' . $resource . '/get';
    $route['api/v1/' . $resource . '/(:num)']['get'] = 'api/v1/' . $resource . '/get/$1';

    $route['api/v2/' . $resource]['post'] = 'api/v2/' . $resource .'v2'. '/post';
    $route['api/v2/' . $resource . '/(:num)']['put'] = 'api/v2/' . $resource .'v2'. '/put/$1';
    $route['api/v2/' . $resource . '/(:num)']['delete'] = 'api/v2/' . $resource.'v2' . '/delete/$1';
    $route['api/v2/' . $resource]['get'] = 'api/v2/' . $resource.'v2' . '/get';
    $route['api/v2/' . $resource . '/(:num)']['get'] = 'api/v2/' . $resource .'v2'. '/get/$1';
    $route['api/v3/' . $resource]['post'] = 'api/v3/' . $resource .'v3'. '/post';
    $route['api/v3/' . $resource]['get'] = 'api/v3/' . $resource .'v3'. '/get';
    $route['api/v2/' . $resource . '/(:any)']['get'] = 'api/v2/' . $resource .'v2'. '/get/$1';
    $route['api/v2/' . $resource . '/(:any)']['put'] = 'api/v2/' . $resource .'v2'. '/put/$1';
}

$route['api/v1/settings']['get'] = 'api/v1/settings/get';
$route['api/v1/settings/(:any)']['get'] = 'api/v1/settings/get/$1';
$route['api/v1/settings/(:any)']['put'] = 'api/v1/settings/put/$1';
$route['api/v1/settings/(:any)']['delete'] = 'api/v1/settings/delete/$1';

$route['api/v2/appointments/statistic']['get'] = 'api/v2/appointmentsv2/getTotalAppointmentGroupByGender';
$route['api/v1/availabilities']['get'] = 'api/v1/availabilities/get';
$route['api/v2/availabilities']['get'] = 'api/v2/availabilitiesv2/get';
$route['api/v2/appointments/(:any)']['get'] = 'api/v2/appointmentsv2/get/$1';
$route['api/v2/appointments/(:any)']['put'] = 'api/v2/appointmentsv2/updateAppointmentByIdIntegrated/$1';
$route['api/v2/appointments/(:any)']['put'] = 'api/v2/appointmentsv2/updateAppointmentStatus/$1';
$route['api/v2/customers/(:any)']['put'] = 'api/v2/customersv2/updateCustomer/$1';
$route['api/v2/providers/(:any)']['put'] = 'api/v2/providersv2/updateProvider/$1';
$route['api/v2/providers/(:any)/(:any)']['put'] = 'api/v2/providersv2/updateProviderByServiceId/$1/$2';
$route['api/v2/services/(:any)']['put'] = 'api/v2/servicesv2/updateService/$1';
$route['api/v2/providers/update/(:any)/(:any)']['put'] = 'api/v2/providersv2/updateProviderIdIntegrated/$1/$2';
$route['api/v2/appointments/orders/(:any)']['put'] = 'api/v2/appointmentsv2/updateAppointmentByOrderId/$1';
$route['api/v2/appointments/orders/(:any)']['get'] = 'api/v2/appointmentsv2/getAppointmentByOrderId/$1';
$route['api/v2/providers/category/(:any)/(:any)']['get'] = 'api/v2/providersv2/getProvidersWithCategoryAndService/$1/$2';
$route['api/v2/categories/services/(:any)']['get'] = 'api/v2/categoriesv2/getCategoryByServiceIdIntegrated/$1';
$route['api/v2/appointments/services/(:any)']['get'] = 'api/v2/appointmentsv2/getAppointmentWithServiceIntegrated/$1';
$route['api/v2/categories/providers/(:any)']['get'] = 'api/v2/categoriesv2/getCategoryByProviderId/$1';
$route['api/v3/patients/(:any)']['get'] = 'api/v3/patientsv3/getPatient/$1';
$route['api/v3/appointments/(:any)']['get'] = 'api/v3/appointmentsv3/getUserAppointments/$1';
$route['api/v3/appointments/services/(:any)/patients/(:any)']['get'] = 'api/v3/appointmentsv3/getAppointmentWithServiceIdAndPatientId/$1/$2';
$route['api/v2/categories/id']['post'] = 'api/v2/categoriesv2/getCategoryIds';
$route['api/v2/services/(:any)/categories']['put'] = 'api/v2/servicesv2/addCategoryToService/$1';
// $route['api/v2/categories']['get']/(:) = 'api/v2/categoryv2/get';
/* End of file routes.php */
/* Location: ./application/config/routes.php */
