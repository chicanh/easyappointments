DROP PROCEDURE IF EXISTS `getAddressBookingStatisticByConditions`;
CREATE PROCEDURE `getAddressBookingStatisticByConditions`(IN `idServiceIntegrated` VARCHAR(200), IN `cityId` INT(11), IN `startDate` VARCHAR(200), IN `endDate` VARCHAR(200), IN `gender` VARCHAR(200), IN `first_time` VARCHAR(200), IN `bhyt` VARCHAR(200))
BEGIN
    IF startDate IS NULL OR startDate = '' THEN
        SET startDate = CONCAT(DATE_FORMAT(CURRENT_DATE(),'%Y-%m'),'-01');
    END IF;
    IF endDate IS NULL OR endDate = '' THEN
        SET endDate = CONCAT(LAST_DAY(CURRENT_DATE()),' 23:59:59');
    END IF;

    SET @finalQuery = CONCAT('SELECT integrated_districts.name as district, COUNT(integrated_districts.name) as value  ',
                      'FROM ea_users eau INNER JOIN integrated_users_patients iup ON eau.id = iup.id_patients ',
                      'INNER JOIN integrated_districts  ON integrated_districts.id_city = eau.city_id AND integrated_districts.id = eau.district_id ',
                      'INNER JOIN ea_appointments eaa ON eaa.id_users_customer = eau.id ',
                      'WHERE iup.id_service_integrated = "',idServiceIntegrated, '" '
                      'AND eaa.start_datetime >= "',startDate,'" ',
                      'AND eaa.end_datetime <= "',endDate, '"');
    
    IF cityId IS NOT NULL OR cityId <= 0 OR cityId = '' THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND eau.city = ', cityId, ' ');
    END IF;

    IF gender IS NOT NULL AND gender = 'male' THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND eau.gender = "male" ');
    END IF;

    IF gender IS NOT NULL AND gender = 'female' THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND eau.gender = "female" ');
    END IF;

    IF bhyt IS NOT NULL AND bhyt <> '' AND upper(bhyt) = 'TRUE' THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND eaa.health_insurance_used = TRUE ');
    END IF;

    IF bhyt IS NOT NULL AND bhyt <> '' AND upper(bhyt) = 'FALSE' THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND eaa.health_insurance_used = FALSE ');
    END IF;

    IF first_time IS NOT NULL AND first_time <> '' AND upper(first_time) = 'TRUE' THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND iup.first_booking_date >= "', startDate, '" AND iup.first_booking_date <= "',endDate,'" ');
    END IF; 

    SET @finalQuery = CONCAT(@finalQuery, 'GROUP BY integrated_districts.name');
    #SELECT @finalQuery;

    PREPARE stmt FROM @finalQuery;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

END;