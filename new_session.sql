TRUNCATE `asa_class_attendence_details`;
TRUNCATE `afm_student_fee_structure`;
TRUNCATE `afm_fee_collection_details`;
TRUNCATE `asa_school_session_class_period_timings`;
TRUNCATE `afm_fee_discounts`;
TRUNCATE `afm_fee_collection`;
TRUNCATE `afm_fee_payment_mode_details`;
TRUNCATE `afm_fee_transactions`;
TRUNCATE `asa_class_attendence`;
TRUNCATE `afm_fee_discounts_log`;
TRUNCATE `asa_student_status_change_log`;
TRUNCATE `asa_student_previous_academic_year_details`;
TRUNCATE `asa_students_19`;
TRUNCATE `change_log`;
TRUNCATE `asa_academic_calendar`;
TRUNCATE `afm_previous_year_fee_details_21_22`;
TRUNCATE `aad_student_registrations`;
TRUNCATE `aad_enquiries`;
TRUNCATE `afm_advance_fee`;
TRUNCATE `teacher_clock_in_out_log`;
TRUNCATE `afm_fee_transactions`;

afm_fee_transactions innodb


INSERT INTO `asa_academic_years` 
(`academicYearID`, `startDate`, `endDate`, `isCurrentYear`, `createUserID`, `createDate`) 
VALUES (NULL, '2022-04-01', '2023-03-31', '1', '1000001', NOW());

INSERT INTO atm_student_vehicle
(`areaWiseFeeID`, `vehicleID`, `studentID`, `academicYearID`, `isActive`, `createUserID`, `createDate`)
SELECT `areaWiseFeeID`, `vehicleID`, `studentID`, 3, `isActive`, `createUserID`, now() FROM atm_student_vehicle


UPDATE `asa_academic_years` SET `isCurrentYear` = '0' WHERE `asa_academic_years`.`academicYearID` = 2;

ALTER TABLE `afm_fee_structure` ADD `feeStructureIDOld` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `createDate`;


INSERT into afm_fee_structure
(academicYearID, classID, feeGroupID, isActive, createUserID, createDate, feeStructureIDOld)
SELECT 3, classID, feeGroupID, isActive, createUserID, NOW(), feeStructureID FROM afm_fee_structure WHERE academicYearID = 2;

INSERT INTO afm_fee_structure_details
(academicYearID, feeStructureID, feeHeadID, academicYearMonthID, feePriority, feeAmount)
SELECT 3, (SELECT f1.feeStructureID FROM afm_fee_structure as f1 WHERE f1.feeStructureIDOld = f2.feeStructureID AND f1.academicYearID = 3), feeHeadID, academicYearMonthID, feePriority, feeAmount 
FROM afm_fee_structure_details f2
WHERE f2.academicYearID = 2;