SET @idServiceIntegrated='81cbf841-1929-4c81-92c3-9ebc343a7282'; 
SET @cityId='1'; 
SET @startDate='2017-01-01'; 
SET @endDate='2020-12-31 23:59:59'; 
SET @gender= ''; 
SET @firstTimeBooking= ''; 
SET @healthInsuranceUsed= ''; 
SET @idProviderIntegrated = '65a4a7dc-bedd-4d42-8a8f-46b5283f482c';

CALL `getAddressBookingStatisticByConditions`(@idServiceIntegrated, @cityId, @startDate, @endDate, @gender, @firstTimeBooking, @healthInsuranceUsed, @idProviderIntegrated);




SELECT eau.id ,integrated_districts.name as district, COUNT(integrated_districts.name) as 'value'
FROM ea_users eau 
INNER JOIN integrated_users_patients iup ON eau.id = iup.id_patients 
INNER JOIN integrated_districts  ON integrated_districts.id_city = eau.city_id AND integrated_districts.id = eau.district_id
INNER JOIN integrated_cities ic ON ic.id = eau.city 
INNER JOIN ea_appointments eaa ON eaa.id_users_customer = eau.id 
WHERE iup.id_service_integrated = "81cbf841-1929-4c81-92c3-9ebc343a7282" 
    AND eaa.start_datetime >= "2017-01-01" 
    AND eaa.end_datetime <= "2020-12-31 23:59:59"
    AND eau.city = 1

