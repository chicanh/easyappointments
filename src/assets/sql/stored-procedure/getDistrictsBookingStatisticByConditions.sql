DROP PROCEDURE IF EXISTS `getDistrictsBookingStatisticByConditions`;
CREATE PROCEDURE `getDistrictsBookingStatisticByConditions`(IN `idServiceIntegrated` VARCHAR(200), IN `cityId` INT(11), IN `startDate` VARCHAR(200), IN `endDate` VARCHAR(200), IN `gender` VARCHAR(200), IN `firstTimeBooking` VARCHAR(200), IN `healthInsuranceUsed` VARCHAR(200), IN `idProviderIntegrated` VARCHAR(200))
BEGIN
    SET @finalQuery = CONCAT('SELECT integrated_cities.name as city,integrated_districts.name as district, COUNT(integrated_districts.name) as value  ',
                      'FROM ea_users eau ', 
                      'INNER JOIN integrated_users_patients iup ON eau.id = iup.id_patients ',
                      'INNER JOIN integrated_cities  ON eau.city_id = integrated_cities.id ',
                      'INNER JOIN integrated_districts  ON integrated_districts.id_city = eau.city_id AND integrated_districts.id = eau.district_id ',
                      'INNER JOIN ea_appointments eaa ON eaa.id_users_customer = eau.id ');
    
    IF idProviderIntegrated IS NOT NULL AND idProviderIntegrated <> '' THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND eaa.id_users_provider IN (SELECT id from ea_users WHERE find_in_set(id_integrated,"',idProviderIntegrated,'")) ');
    END IF;

    
    SET @finalQuery = CONCAT(@finalQuery,'WHERE iup.id_service_integrated = "',idServiceIntegrated, '" '
                      'AND eaa.start_datetime >= "',startDate,'" ',
                      'AND eaa.end_datetime <= "',endDate, '"');
                      
    IF cityId IS NOT NULL THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND find_in_set(eau.city_id,', cityId, ') ');
    END IF;

    IF gender IS NOT NULL THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND eau.gender = "',gender,'" ');
    END IF;

    IF healthInsuranceUsed IS NOT NULL AND upper(healthInsuranceUsed) = 'TRUE' THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND eaa.health_insurance_used = TRUE ');
    END IF;

    IF healthInsuranceUsed IS NOT NULL AND upper(healthInsuranceUsed) = 'FALSE' THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND eaa.health_insurance_used = FALSE ');
    END IF;

    IF firstTimeBooking IS NOT NULL AND upper(firstTimeBooking) = 'TRUE' THEN
        SET @finalQuery = CONCAT(@finalQuery, 'AND iup.first_booking_date >= "', startDate, '" AND iup.first_booking_date <= "',endDate,'" ');
    END IF; 

    SET @finalQuery = CONCAT(@finalQuery, 'GROUP BY integrated_districts.name;');
    #SELECT @finalQuery;

    PREPARE stmt FROM @finalQuery;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;