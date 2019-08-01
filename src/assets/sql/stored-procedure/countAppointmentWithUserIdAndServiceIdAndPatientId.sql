DELIMITER $$
DROP PROCEDURE IF EXISTS `countAppointmentsByCondition` $$
CREATE PROCEDURE `countAppointmentsByCondition`(IN `idUserIntegrated` VARCHAR(200), 
                                                                           IN `idServiceIntegrated` VARCHAR(200), 
                                                                           IN `idPatientIntegrated` VARCHAR(200), 
                                                                           IN `startDate` VARCHAR(200), 
                                                                           IN `endDate` VARCHAR(200))
BEGIN
    # -- Start build final Query with parameter
    SET @finalQuery = 'SELECT COUNT(ea_appointments.id) as "total" FROM ea_appointments WHERE 1 = 1';
    IF idServiceIntegrated <> '' OR idServiceIntegrated IS NOT NULL THEN
    	SET @finalQuery = CONCAT(@finalQuery,' AND ea_appointments.id_services = (SELECT id from ea_services WHERE id_integrated = "',idServiceIntegrated,'")');
    END IF;
    IF idUserIntegrated <> '' OR idUserIntegrated IS NOT NULL THEN
        SET @finalQuery = CONCAT(@finalQuery,' AND ea_appointments.id_users_provider = (SELECT id from ea_users WHERE id_integrated = "',idUserIntegrated,'")');
    END IF;
    IF idPatientIntegrated <> '' OR idPatientIntegrated IS NOT NULL THEN
        SET @finalQuery = CONCAT(@finalQuery,' AND ea_appointments.id_users_customer = (SELECT id from ea_users WHERE id_integrated = "',idPatientIntegrated,'")');
    END IF;
    IF startDate <> '' OR startDate IS NOT NULL THEN
    	SET @finalQuery = CONCAT(@finalQuery,' AND ea_appointments.start_datetime >= "',startDate,'"');
	END IF;
    IF endDate <> '' OR endDate IS NOT NULL THEN
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
