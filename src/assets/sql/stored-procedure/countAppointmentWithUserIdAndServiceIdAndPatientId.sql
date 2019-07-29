DELIMITER $$
DROP PROCEDURE IF EXISTS `countAppointmentsByCondition` $$
CREATE PROCEDURE `countAppointmentsByCondition`(IN `idUserIntegrated` VARCHAR(200), 
                                                                           IN `idServiceIntegrated` VARCHAR(200), 
                                                                           IN `idPatientIntegrated` VARCHAR(200), 
                                                                           IN `startDate` VARCHAR(200), 
                                                                           IN `endDate` VARCHAR(200))
BEGIN
	Declare startOffset Integer;
    #-- Prepare pre-condition before running main query
    IF idServiceIntegrated <> '' THEN
        SET idServiceIntegrated = (SELECT ea_services.id from ea_services WHERE ea_services.id_integrated = idServiceIntegrated);
    END IF;

    IF idUserIntegrated <> '' THEN
        SET idUserIntegrated = (SELECT ea_users.id from ea_users WHERE ea_users.id_integrated = idUserIntegrated);
    END IF;

    IF idPatientIntegrated <> '' THEN
        SET idPatientIntegrated = (SELECT ea_users.id from ea_users WHERE ea_users.id_integrated = idPatientIntegrated);
    END IF;


    # -- Start build final Query with parameter
    SET @finalQuery = 'SELECT COUNT(ea_appointments.id) as "total" FROM ea_appointments WHERE 1 = 1';
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
    SET @finalQuery = CONCAT(@finalQuery, '  ORDER BY ea_appointments.id');

    # this line to debug, uncomment it to see final query.
    #Select @finalQuery; 

    # RUN final Query result 

    PREPARE stmt FROM @finalQuery;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
END$$
DELIMITER ;
