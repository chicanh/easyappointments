DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `getAppointmentWithUserIdAndServiceIdAndPatientId`(IN `idUserIntegrated` VARCHAR(200), IN `idServiceIntegrated` VARCHAR(200), IN `idPatientIntegrated` VARCHAR(200), IN `page` INT, IN `size` INT, IN `sort` VARCHAR(200), IN `startDate` VARCHAR(200), IN `endDate` VARCHAR(200))
BEGIN
	Declare serviceId, userId, patientId, startOffset Integer;

    SET serviceId = (SELECT ea_services.id from `ea_services` WHERE ea_services.id_integrated = idServiceIntegrated);
	  SET userId = (SELECT ea_users.id from ea_users WHERE ea_users.id_integrated = idUserIntegrated);
    SET patientId = (SELECT ea_users.id from ea_users WHERE ea_users.id_integrated = idPatientIntegrated);

    IF page = 0 THEN
          SET page = 1; #default page = 1
    END IF;
    IF size = 0 THEN
        SET size = 10; #default size = 10
    END IF;
    SET startOffset = ((page - 1 ) * size);

    # Start final Query result
    SET @finalQuery = CONCAT('SELECT * FROM ea_appointments WHERE id_users_provider = ',userId,' AND id_services =',serviceId,' AND id_users_customer =',patientId);

    IF startDate <> '' THEN
    	SET @finalQuery = CONCAT(@finalQuery,' AND ea_appointments.start_datetime >= "',startDate,'"');
	  END IF;

    IF endDate <> '' THEN
        SET @finalQuery = CONCAT(@finalQuery,' AND ea_appointments.end_datetime <= "',endDate,'"');
    END IF;

    SET @finalQuery = CONCAT(@finalQuery, '  ORDER BY ea_appointments.id   ', sort);
    SET @finalQuery = CONCAT(@finalQuery, ' LIMIT ', startOffset, ', ', size, ';');

    # Select @finalQuery # this line to debug, uncomment it to see final query.

    # RUN final Query result 

    PREPARE stmt FROM @finalQuery;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$
DELIMITER ;





-- Example:
SET @idUserIntegrated='65a4a7dc-bedd-4d42-8a8f-46b5283f482c';
SET @idServiceIntegrated='81cbf841-1929-4c81-92c3-9ebc343a7282';
SET @idPatientIntegrated='6eb8cde6-9237-47b8-bd27-e0b3b4d6bffb';
SET @page='1';
SET @size='2';
SET @sort='DESC';
SET @startDate='2017-01-01';
SET @endDate='2019-01-01';
CALL `getAppointmentWithUserIdAndServiceIdAndPatientId`(@idUserIntegrated, 
                                                        @idServiceIntegrated, 
                                                        @idPatientIntegrated, 
                                                        @page, 
                                                        @size, 
                                                        @sort, 
                                                        @startDate, 
                                                        @endDate);

