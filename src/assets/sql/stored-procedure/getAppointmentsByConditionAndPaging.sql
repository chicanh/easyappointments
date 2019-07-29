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

    IF page <= 0 OR page IS NULL THEN
          SET page = 1; #default page = 1
    END IF;

    IF size <= 0 OR size IS NULL THEN
        SET size = 10; #default size = 10
    END IF;

    IF sort IS NULL OR sort <> 'ASC' OR sort <> 'DESC' THEN
        SET sort = 'ASC'; #default size = 10
    END IF;

    SET startOffset = ((page - 1 ) * size);
    
    # -- Start build final Query with parameter
    SET @finalQuery = 'SELECT *  FROM ea_appointments WHERE 1 = 1';
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





