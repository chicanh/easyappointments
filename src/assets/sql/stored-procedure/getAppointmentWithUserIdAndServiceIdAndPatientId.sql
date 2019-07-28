-------------------------------------------------------------------------- STORED PROCEDURE SCRIPT----------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS `getAppointmentsByConditionAndPaging` $$
CREATE PROCEDURE `getAppointmentsByConditionAndPaging`(IN `idUserIntegrated` VARCHAR(200), 
                                                       IN `idServiceIntegrated` VARCHAR(200),
                                                       IN `idPatientIntegrated` VARCHAR(200), 
                                                       IN `startDate` VARCHAR(200), 
                                                       IN `endDate` VARCHAR(200),
                                                       IN `page` INT, 
                                                       IN `size` INT, 
                                                       IN `sort` VARCHAR(200))
BEGIN
	Declare startOffset Integer;
    #-- Prepare pre-condition before running main query
    IF idServiceIntegrated <> '' THEN
        SET idServiceIntegrated = (SELECT ea_services.id from `ea_services` WHERE ea_services.id_integrated = idServiceIntegrated);
    END IF;

    IF idUserIntegrated <> '' THEN
        SET idUserIntegrated = (SELECT ea_users.id from ea_users WHERE ea_users.id_integrated = idUserIntegrated);
    END IF;

    IF idPatientIntegrated <> '' THEN
        SET idPatientIntegrated = (SELECT ea_users.id from ea_users WHERE ea_users.id_integrated = idPatientIntegrated);
    END IF;

    IF page = 0 THEN
          SET page = 1; #default page = 1
    END IF;

    IF size = 0 THEN
        SET size = 10; #default size = 10
    END IF;

    SET startOffset = ((page - 1 ) * size);
    

    # -- Start build final Query with parameter
    SET @finalQuery = 'SELECT *  FROM ea_appointments WHERE 1 = 1';
    IF idServiceIntegrated <> '' THEN
    	SET @finalQuery = CONCAT(@finalQuery,' AND ea_appointments.id_services = "',idServiceIntegrated,'"');
    END IF;
    IF idUserIntegrated <> '' THEN
        SET @finalQuery = CONCAT(@finalQuery,' AND ea_appointments.id_users_provider = "',idUserIntegrated,'"');
    END IF;
    IF idPatientIntegrated <> '' THEN
        SET @finalQuery = CONCAT(@finalQuery,' AND ea_appointments.id_users_customer = "',idPatientIntegrated,'"');
    END IF;
    IF startDate <> '' THEN
    	SET @finalQuery = CONCAT(@finalQuery,' AND ea_appointments.start_datetime >= "',startDate,'"');
	END IF;
    IF endDate <> '' THEN
        SET @finalQuery = CONCAT(@finalQuery,' AND ea_appointments.end_datetime <= "',endDate,'"');
    END IF;
    SET @finalQuery = CONCAT(@finalQuery, '  ORDER BY ea_appointments.id   ', sort);
    SET @finalQuery = CONCAT(@finalQuery, ' LIMIT ', startOffset, ', ', size, ';');

    # this line to debug, uncomment it to see final query.
    # Select @finalQuery 

    # RUN final Query result 
    PREPARE stmt FROM @finalQuery;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$
DELIMITER ;

-------------------------------------------------------------------------- END PROCEDURE ----------------------------------------------------------------



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


