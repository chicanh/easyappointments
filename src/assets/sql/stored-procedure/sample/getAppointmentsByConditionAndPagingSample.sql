-- Example:
SET @idUserIntegrated='65a4a7dc-bedd-4d42-8a8f-46b5283f482c';
SET @idServiceIntegrated='81cbf841-1929-4c81-92c3-9ebc343a7282';
SET @idPatientIntegrated='6eb8cde6-9237-47b8-bd27-e0b3b4d6bffb';
SET @page='1';
SET @size='10';
SET @sort='DESC';
SET @startDate='2017-01-01';
SET @endDate='2019-01-01';
CALL `getAppointmentsByConditionAndPaging`(@idUserIntegrated, 
                                           @idServiceIntegrated, 
                                           @idPatientIntegrated,  
                                           @startDate, 
                                           @endDate,
                                           @page, 
                                           @size, 
                                           @sort);