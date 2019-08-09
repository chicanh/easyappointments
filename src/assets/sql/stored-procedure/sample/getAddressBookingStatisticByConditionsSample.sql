SET @idServiceIntegrated='81cbf841-1929-4c81-92c3-9ebc343a7282'; 
SET @cityId='1'; 
SET @startDate='2017-01-01'; 
SET @endDate='2020-12-31 23:59:59'; 
SET @gender= 'female'; 
SET @firstTimeBooking= null; 
SET @healthInsuranceUsed= null; 
SET @idProviderIntegrated = '65a4a7dc-bedd-4d42-8a8f-46b5283f482c';
CALL `getAddressBookingStatisticByConditions`(@idServiceIntegrated, @cityId, @startDate, @endDate, @gender, @firstTimeBooking, @healthInsuranceUsed, @idProviderIntegrated);


