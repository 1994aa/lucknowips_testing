<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.db_connect.php');
error_reporting(E_ALL);

class FeeDiscount
{
	// CLASS MEMBERS ARE DEFINED HERE	//
	private $LastErrorCode;
	private $DBObject; // VARIABLE TO HOLD THE DB CONNECTION //

	private $FeeDiscountID;
	private $FeeDiscountType;

	private $FeeGroupID;
	private $StudentID;
	private $FeeStructureDetailID;

	private $DiscountType;
	private $DiscountValue;

	private $DiscountDetails = array();

	// PUBLIC METHODS START HERE	//
	public function __construct($FeeDiscountID = 0)
	{
		$this->DBObject = new DBConnect;
		$this->LastErrorCode = 0;

		if ($FeeDiscountID != 0) {
			$this->FeeDiscountID = $FeeDiscountID;
			// SET THE VALUES FROM THE DATABASE.
			$this->GetFeeDiscountByID();
		} else {
			//SET THE DEFAULT VALUES TO LOOK ATTRIBUTES
			$this->FeeDiscountID = 0;
			$this->FeeDiscountType = '';

			$this->FeeGroupID = 0;
			$this->StudentID = 0;
			$this->FeeStructureDetailID = 0;

			$this->DiscountType = '';
			$this->DiscountValue = 0;

			$this->DiscountDetails = array();
		}
	}

	// GETTER AND SETTER FUNCTIONS START HERE	//
	public function GetFeeDiscountID()
	{
		return $this->FeeDiscountID;
	}

	public function GetFeeDiscountType()
	{
		return $this->FeeDiscountType;
	}
	public function SetFeeDiscountType($FeeDiscountType)
	{
		$this->FeeDiscountType = $FeeDiscountType;
	}

	public function GetFeeGroupID()
	{
		return $this->FeeGroupID;
	}
	public function SetFeeGroupID($FeeGroupID)
	{
		$this->FeeGroupID = $FeeGroupID;
	}

	public function GetStudentID()
	{
		return $this->StudentID;
	}
	public function SetStudentID($StudentID)
	{
		$this->StudentID = $StudentID;
	}

	public function GetFeeStructureDetailID()
	{
		return $this->FeeStructureDetailID;
	}
	public function SetFeeStructureDetailID($FeeStructureDetailID)
	{
		$this->FeeStructureDetailID = $FeeStructureDetailID;
	}

	public function GetDiscountType()
	{
		return $this->DiscountType;
	}
	public function SetDiscountType($DiscountType)
	{
		$this->DiscountType = $DiscountType;
	}

	public function GetDiscountValue()
	{
		return $this->DiscountValue;
	}
	public function SetDiscountValue($DiscountValue)
	{
		$this->DiscountValue = $DiscountValue;
	}

	public function GetDiscountDetails()
	{
		return $this->DiscountDetails;
	}
	public function SetDiscountDetails($DiscountDetails)
	{
		$this->DiscountDetails = $DiscountDetails;
	}

	public function GetLastErrorCode()
	{
		return $this->LastErrorCode;
	}

	//  END OF GETTER AND SETTER FUNCTIONS 	//

	public function Save()
	{
		try {
			return $this->SaveDetails();
		} catch (ApplicationDBException $e) {
			$this->LastErrorCode = $e->getCode();
			return false;
		} catch (ApplicationARException $e) {
			$this->LastErrorCode = $e->getCode();
			return false;
		} catch (Exception $e) {
			$this->LastErrorCode = APP_ERROR_UNDEFINED_ERROR;
			return false;
		}
	}

	public function SetFeeStructureDiscount()
	{
		try {
			$DBConnObject = new DBConnect();

			if (count($this->DiscountDetails) > 0) {
				foreach ($this->DiscountDetails as $FeeHeadID => $Details) {
					foreach ($Details['FeeStructureDetailID'] as $FeeStructureDetailID) {
						$RSSearchFeeDiscountID = $this->DBObject->Prepare('SELECT feeDiscountID FROM afm_fee_discounts WHERE feeGroupID = :|1 AND feeStructureDetailID = :|2 AND studentID = :|3;');
						$RSSearchFeeDiscountID->Execute($this->FeeGroupID, $FeeStructureDetailID, $this->StudentID);

						if ($RSSearchFeeDiscountID->Result->num_rows > 0) {
							$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts
																SET	feeDiscountType = :|1,
																	feeGroupID = :|2,
																	studentID = :|3,
																	feeStructureDetailID = :|4,
																	discountType = :|5,
																	discountValue = :|6,
																	discountDateTime = NOW()
																WHERE feeDiscountID = :|7;');

							$RSUpdate->Execute($this->FeeDiscountType, $this->FeeGroupID, $this->StudentID, $FeeStructureDetailID, $Details['DiscountType'], $Details['DiscountValue'], $RSSearchFeeDiscountID->FetchRow()->feeDiscountID);
						} else {
							$RSSave = $DBConnObject->Prepare('INSERT INTO afm_fee_discounts (feeDiscountType, feeGroupID, studentID, feeStructureDetailID, discountType, discountValue, concessionAmount, discountDateTime)
															VALUES (:|1, :|2, :|3, :|4, :|5, :|6, :|7, NOW());');

							$RSSave->Execute($this->FeeDiscountType, $this->FeeGroupID, $this->StudentID, $FeeStructureDetailID, $Details['DiscountType'], $Details['DiscountValue'], 0);
						}
					}

					if (array_key_exists('RemoveDiscount', $Details)) {
						foreach ($Details['RemoveDiscount'] as $FeeStructureDetailID) {
							$RSSearchFeeDiscountID = $this->DBObject->Prepare('SELECT feeDiscountID, concessionAmount, waveOffAmount FROM afm_fee_discounts WHERE feeGroupID = :|1 AND feeStructureDetailID = :|2 AND studentID = :|3;');
							$RSSearchFeeDiscountID->Execute($this->FeeGroupID, $FeeStructureDetailID, $this->StudentID);

							if ($RSSearchFeeDiscountID->Result->num_rows > 0) {
								$SearchFeeDiscountIDRow = $RSSearchFeeDiscountID->FetchRow();

								if ($SearchFeeDiscountIDRow->concessionAmount > 0 || $SearchFeeDiscountIDRow->waveOffAmount > 0) {
									$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts
																		SET	discountValue = :|1
																		WHERE feeDiscountID = :|2;');

									$RSUpdate->Execute(0, $SearchFeeDiscountIDRow->feeDiscountID);
								} else {
									$RSDelete = $this->DBObject->Prepare('DELETE FROM afm_fee_discounts WHERE feeGroupID = :|1 AND feeStructureDetailID = :|2 AND studentID = :|3;');
									$RSDelete->Execute($this->FeeGroupID, $FeeStructureDetailID, $this->StudentID);
								}
							}
						}
					}
				}
			} else if ($this->StudentID > 0) {
				$RSDelete = $this->DBObject->Prepare('DELETE FROM afm_fee_discounts WHERE studentID = :|1 AND concessionAmount = 0;');
				$RSDelete->Execute($this->StudentID);

				$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts
													SET	discountValue = :|1
													WHERE studentID = :|2;');

				$RSUpdate->Execute(0, $this->StudentID);
			}

			return true;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeDiscount::SetFeeStructureDiscount(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeDiscount::SetFeeStructureDiscount(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	// END OF PUBLIC METHODS	//

	// START OF STATIC METHODS	//

	static function GetFeeStructureDiscount($FeeGroupID, $ClassID, $StudentID)
	{
		$AllFeeDiscountDetails = array();
		try {

			$DBConnObject = new DBConnect();

			$RSFeeDiscountDetails = $DBConnObject->Prepare('SELECT afd.*, afsd.feeHeadID, afsd.feeAmount, afsd.academicYearMonthID FROM afm_fee_discounts afd 
																INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = afd.feeStructureDetailID
																INNER JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID
																WHERE afd.feeGroupID = :|1 AND afs.classID = :|2 AND afd.studentID = :|3;');
			$RSFeeDiscountDetails->Execute($FeeGroupID, $ClassID, $StudentID);

			if ($RSFeeDiscountDetails->Result->num_rows <= 0) {
				return $AllFeeDiscountDetails;
			}

			while ($SearchRow = $RSFeeDiscountDetails->FetchRow()) {
				$AllFeeDiscountDetails[$SearchRow->feeHeadID][$SearchRow->feeStructureDetailID]['AcademicYearMonthID'] = $SearchRow->academicYearMonthID;
				$AllFeeDiscountDetails[$SearchRow->feeHeadID][$SearchRow->feeStructureDetailID]['DiscountType'] = $SearchRow->discountType;
				$AllFeeDiscountDetails[$SearchRow->feeHeadID][$SearchRow->feeStructureDetailID]['DiscountValue'] = $SearchRow->discountValue;
				$AllFeeDiscountDetails[$SearchRow->feeHeadID][$SearchRow->feeStructureDetailID]['ConcessionAmount'] = $SearchRow->concessionAmount;
				$AllFeeDiscountDetails[$SearchRow->feeHeadID][$SearchRow->feeStructureDetailID]['WaveOffAmount'] = $SearchRow->waveOffAmount;
			}

			return $AllFeeDiscountDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeDiscount::GetFeeStructureDiscount(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeDiscount::GetFeeStructureDiscount(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function SetFeeConcession($StudentID, $ConcessionDetails, $FeeTransactionID)
	{
		$AllFeeDiscountDetails = array();
		try {
			$DBConnObject = new DBConnect();

			if (count($ConcessionDetails) > 0) {
				$RSSearchFeeGroupID = $DBConnObject->Prepare('SELECT feeGroupID FROM afm_fee_group_assigned_records WHERE recordID = :|1;');
				$RSSearchFeeGroupID->Execute($StudentID);

				$FeeGroupID = 0;
				if ($RSSearchFeeGroupID->Result->num_rows > 0) {
					$FeeGroupID = $RSSearchFeeGroupID->FetchRow()->feeGroupID;
				}

				foreach ($ConcessionDetails as $StudentFeeStructureID => $ConcessionAmount) {
					if ($ConcessionAmount) {
						$RSSearchFeeStructureDetailID = $DBConnObject->Prepare('SELECT feeStructureDetailID FROM afm_student_fee_structure WHERE studentFeeStructureID = :|1 AND studentID = :|2;');
						$RSSearchFeeStructureDetailID->Execute($StudentFeeStructureID, $StudentID);

						$FeeStructureDetailID = $RSSearchFeeStructureDetailID->FetchRow()->feeStructureDetailID;

						$RSSearchFeeDiscount = $DBConnObject->Prepare('SELECT * FROM afm_fee_discounts WHERE studentID = :|1 AND feeStructureDetailID = :|2;');
						$RSSearchFeeDiscount->Execute($StudentID, $FeeStructureDetailID);

						if ($RSSearchFeeDiscount->Result->num_rows > 0) {
							$SearchFeeDiscountRow = $RSSearchFeeDiscount->FetchRow();

							$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts
																SET concessionAmount = :|1,
																    transactionDateTime = NOW()
																WHERE feeDiscountID = :|2;');

							$RSUpdate->Execute($ConcessionAmount, $SearchFeeDiscountRow->feeDiscountID);

							//Creating log from date 10-10-2020 (log table: afm_fee_discounts_log)
							$RSSave = $DBConnObject->Prepare('INSERT INTO afm_fee_discounts_log (feeDiscountType, feeGroupID, studentID, feeStructureDetailID, discountType, discountValue, concessionAmount, feeTransactionID, transactionDateTime)
															VALUES (:|1, :|2, :|3, :|4, :|5, :|6, :|7, :|8, NOW());');

							$RSSave->Execute($SearchFeeDiscountRow->feeDiscountType, $SearchFeeDiscountRow->feeGroupID, $StudentID, $FeeStructureDetailID, $SearchFeeDiscountRow->discountType, $SearchFeeDiscountRow->discountValue, $ConcessionAmount, $FeeTransactionID);
						} else {
							$RSSave = $DBConnObject->Prepare('INSERT INTO afm_fee_discounts (feeDiscountType, feeGroupID, studentID, feeStructureDetailID, discountType, discountValue, concessionAmount, feeTransactionID, transactionDateTime)
															VALUES (:|1, :|2, :|3, :|4, :|5, :|6, :|7, :|8, NOW());');

							$RSSave->Execute('Student', $FeeGroupID, $StudentID, $FeeStructureDetailID, 'Absolute', 0, $ConcessionAmount, $FeeTransactionID);

							//Creating log from date 10-10-2020 (log table: afm_fee_discounts_log)
							$RSSave = $DBConnObject->Prepare('INSERT INTO afm_fee_discounts_log (feeDiscountType, feeGroupID, studentID, feeStructureDetailID, discountType, discountValue, concessionAmount, feeTransactionID, transactionDateTime)
																VALUES (:|1, :|2, :|3, :|4, :|5, :|6, :|7, :|8, NOW());');

							$RSSave->Execute('Student', $FeeGroupID, $StudentID, $FeeStructureDetailID, 'Absolute', 0, $ConcessionAmount, $FeeTransactionID);
						}
					}
				}
			}

			return true;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeDiscount::SetFeeConcession(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeDiscount::SetFeeConcession(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function SetWaveOffFee($StudentID, $WaveOffList, $FeeTransactionID)
	{
		$AllFeeDiscountDetails = array();
		try {
			$DBConnObject = new DBConnect();

			if (count($WaveOffList) > 0) {
				$RSSearchFeeGroupID = $DBConnObject->Prepare('SELECT feeGroupID FROM afm_fee_group_assigned_records WHERE recordID = :|1;');
				$RSSearchFeeGroupID->Execute($StudentID);

				$FeeGroupID = 0;
				if ($RSSearchFeeGroupID->Result->num_rows > 0) {
					$FeeGroupID = $RSSearchFeeGroupID->FetchRow()->feeGroupID;
				}

				foreach ($WaveOffList as $StudentFeeStructureID => $WaveOffAmount) {
					if ($StudentFeeStructureID) {
						$RSSearchFeeStructureDetailID = $DBConnObject->Prepare('SELECT feeStructureDetailID, amountPayable FROM afm_student_fee_structure WHERE studentFeeStructureID = :|1 AND studentID = :|2;');
						$RSSearchFeeStructureDetailID->Execute($StudentFeeStructureID, $StudentID);

						$SearchRow = $RSSearchFeeStructureDetailID->FetchRow();

						$FeeStructureDetailID = $SearchRow->feeStructureDetailID;
						// $WaveOffAmount = $SearchRow->amountPayable;

						$RSSearchFeeDiscount = $DBConnObject->Prepare('SELECT * FROM afm_fee_discounts WHERE studentID = :|1 AND feeStructureDetailID = :|2 LIMIT 1;');
						$RSSearchFeeDiscount->Execute($StudentID, $FeeStructureDetailID);

						if ($RSSearchFeeDiscount->Result->num_rows > 0) {
							$SearchFeeDiscountRow = $RSSearchFeeDiscount->FetchRow();

							$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts
																SET waveOffAmount = :|1,
																    transactionDateTime = NOW()
																WHERE feeDiscountID = :|2;');

							$RSUpdate->Execute($WaveOffAmount, $SearchFeeDiscountRow->feeDiscountID);

							//Creating log from date 10-10-2020 (log table: afm_fee_discounts_log)
							$RSSave = $DBConnObject->Prepare('INSERT INTO afm_fee_discounts_log (feeDiscountType, feeGroupID, studentID, feeStructureDetailID, discountType, discountValue, waveOffAmount, feeTransactionID, transactionDateTime)
															VALUES (:|1, :|2, :|3, :|4, :|5, :|6, :|7, :|8, NOW());');

							$RSSave->Execute($SearchFeeDiscountRow->feeDiscountType, $SearchFeeDiscountRow->feeGroupID, $StudentID, $FeeStructureDetailID, $SearchFeeDiscountRow->discountType, $SearchFeeDiscountRow->discountValue, $WaveOffAmount, $FeeTransactionID);
						} else {
							$RSSave = $DBConnObject->Prepare('INSERT INTO afm_fee_discounts (feeDiscountType, feeGroupID, studentID, feeStructureDetailID, discountType, discountValue, waveOffAmount, feeTransactionID, transactionDateTime)
															VALUES (:|1, :|2, :|3, :|4, :|5, :|6, :|7, :|8, NOW());');

							$RSSave->Execute('Student', $FeeGroupID, $StudentID, $FeeStructureDetailID, 'Absolute', 0, $WaveOffAmount, $FeeTransactionID);

							//Creating log from date 10-10-2020 (log table: afm_fee_discounts_log)
							$RSSave = $DBConnObject->Prepare('INSERT INTO afm_fee_discounts_log (feeDiscountType, feeGroupID, studentID, feeStructureDetailID, discountType, discountValue, waveOffAmount, feeTransactionID, transactionDateTime)
															VALUES (:|1, :|2, :|3, :|4, :|5, :|6, :|7, :|8, NOW());');

							$RSSave->Execute('Student', $FeeGroupID, $StudentID, $FeeStructureDetailID, 'Absolute', 0, $WaveOffAmount, $FeeTransactionID);
						}
					}
				}
			}

			return true;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeDiscount::SetWaveOffFee(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeDiscount::SetWaveOffFee(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function SearchStudentDiscountDetails(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $Start = 0, $Limit = 100)
	{
		$StudentDiscountDetails = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();

			$StudentTable = 'asa_students';

			if ($Filters['DiscountForAYID'] == 1) {
				$StudentTable = 'asa_students_19';
			}

			$DiscountFromDate = '';
			$DiscountToDate = '';
			if ($_SESSION['DB'] == 'addedschools_lucknowips_testing') {
				if ($Filters['DiscountINAYID'] == 1) {
					$DiscountFromDate = '2019-03-01';
					$DiscountToDate = '2020-03-01';
				} else if ($Filters['DiscountINAYID'] == 2) {
					$DiscountFromDate = '2020-03-01';
					$DiscountToDate = '2021-03-01';
				} else if ($Filters['DiscountINAYID'] == 3) {
					$DiscountFromDate = '2021-03-01';
					$DiscountToDate = '2022-03-01';
				}
			} else {
				if ($Filters['DiscountINAYID'] == 1) {
					$DiscountFromDate = '2020-03-01';
					$DiscountToDate = '2021-03-01';
				} else if ($Filters['DiscountINAYID'] == 2) {
					$DiscountFromDate = '2021-03-01';
					$DiscountToDate = '2022-03-01';
				}
			}

			if (!empty($Filters['DiscountFromDate'])) {
				if (!empty($Filters['Discount'])) {
					$Conditions[] = '(DATE(afd.discountDateTime) BETWEEN ' . $DBConnObject->RealEscapeVariable($DiscountFromDate) . ' AND ' . $DBConnObject->RealEscapeVariable($DiscountToDate) . ')';
					$Conditions[] = '(DATE(afd.discountDateTime) BETWEEN ' . $DBConnObject->RealEscapeVariable($Filters['DiscountFromDate']) . ' AND ' . $DBConnObject->RealEscapeVariable($Filters['DiscountToDate']) . ')';
				} else {
					$Conditions[] = '(DATE(afd.transactionDateTime) BETWEEN ' . $DBConnObject->RealEscapeVariable($DiscountFromDate) . ' AND ' . $DBConnObject->RealEscapeVariable($DiscountToDate) . ')';
					$Conditions[] = '(DATE(afd.transactionDateTime) BETWEEN ' . $DBConnObject->RealEscapeVariable($Filters['DiscountFromDate']) . ' AND ' . $DBConnObject->RealEscapeVariable($Filters['DiscountToDate']) . ')';
				}
			}

			if (count($Filters) > 0) {
				$Conditions[] = 'fsd.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['DiscountForAYID']);
				$Conditions[] = 'afd.feeDiscountType = "Student"';

				if (!empty($Filters['ClassID'])) {
					$Conditions[] = 'ac.classID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassID']);
				}

				if (!empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'ass.classSectionID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']);
				}

				if (!empty($Filters['StudentID'])) {
					$Conditions[] = 'asd.studentID = ' . $DBConnObject->RealEscapeVariable($Filters['StudentID']);
				}

				if (!empty($Filters['StudentName'])) {
					$Conditions[] = 'CONCAT(asd.firstName, " ", asd.lastName) LIKE ' . $DBConnObject->RealEscapeVariable('%' . $Filters['StudentName'] . '%');
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = 'asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']);
				}

				if (count($Filters['MonthList']) > 0) {
					$Conditions[] = 'fsd.academicYearMonthID IN (' . implode(', ', $Filters['MonthList']) . ')';
				}

				if (!empty($Filters['Discount'])) {
					$Conditions[] = 'afd.discountValue > 0';
				}

				if (!empty($Filters['Concession'])) {
					$Conditions[] = 'afd.concessionAmount > 0';
				}

				if (!empty($Filters['WaveOff'])) {
					$Conditions[] = 'afd.waveOffAmount > 0';
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = implode(' AND ', $Conditions);

				$QueryString = ' WHERE ' . $QueryString;
			}

			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts T1
													INNER JOIN afm_student_fee_structure T2 ON T1.feeStructureDetailID = T2.feeStructureDetailID
													SET T1.academicYearID = T2.academicYearID
													WHERE T1.academicYearID = 0;');
			$RSUpdate->Execute();

			if ($GetTotalsOnly) {
				$RSTotal = $DBConnObject->Prepare('SELECT COUNT(DISTINCT afd.feeDiscountID) AS totalRecords 
																				FROM afm_fee_discounts afd
																				INNER JOIN afm_fee_structure_details fsd ON afd.feeStructureDetailID = fsd.feeStructureDetailID
																				INNER JOIN afm_fee_heads fh ON fh.feeHeadID = fsd.feeHeadID
																				INNER JOIN asa_academic_year_months aym ON aym.academicYearMonthID = fsd.academicYearMonthID
																				INNER JOIN asa_student_details asd ON asd.studentID = afd.studentID
																				INNER JOIN ' . $StudentTable . ' ass ON ass.studentID = asd.studentID
																				INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID
																				INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID
																				INNER JOIN asa_classes ac ON ac.classID = acs.classID 
																				INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
																				' . $QueryString . ';');
				$RSTotal->Execute();

				$TotalRecords = $RSTotal->FetchRow()->totalRecords;
				return;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT afd.transactionDateTime, afd.discountDateTime, afd.feeDiscountID, afd.feeStructureAmount, afd.concessionAmount, afd.waveOffAmount, afd.calculatedDiscountAmount, asd.studentID, asd.firstName, asd.lastName, ac.classID, ac.className, asm.sectionName,
												apd.fatherMobileNumber, apd.motherMobileNumber, asd.mobileNumber, aym.monthName, fh.feeHeadID, fh.feeHead
												FROM afm_fee_discounts afd
												INNER JOIN afm_fee_structure_details fsd ON afd.feeStructureDetailID = fsd.feeStructureDetailID
												INNER JOIN afm_fee_heads fh ON fh.feeHeadID = fsd.feeHeadID
												INNER JOIN asa_academic_year_months aym ON aym.academicYearMonthID = fsd.academicYearMonthID
												INNER JOIN asa_student_details asd ON asd.studentID = afd.studentID
												INNER JOIN ' . $StudentTable . ' ass ON ass.studentID = asd.studentID
												INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID
												INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID
												INNER JOIN asa_classes ac ON ac.classID = acs.classID 
												INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
												' . $QueryString . '
												GROUP BY afd.feeDiscountID
												ORDER BY ac.priority, acs.priority, asd.firstName, asd.lastName LIMIT ' . (int) $Start . ', ' . (int) $Limit . ';');
			$RSSearch->Execute();

			if ($RSSearch->Result->num_rows <= 0) {
				return $StudentDiscountDetails;
			}

			while ($SearchRow = $RSSearch->FetchRow()) {
				$StudentDiscountDetails[$SearchRow->feeDiscountID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;
				$StudentDiscountDetails[$SearchRow->feeDiscountID]['ClassID'] = $SearchRow->classID;
				$StudentDiscountDetails[$SearchRow->feeDiscountID]['ClassName'] = $SearchRow->className;
				$StudentDiscountDetails[$SearchRow->feeDiscountID]['SectionName'] = $SearchRow->sectionName;

				$StudentDiscountDetails[$SearchRow->feeDiscountID]['MonthName'] = $SearchRow->monthName;
				$StudentDiscountDetails[$SearchRow->feeDiscountID]['FeeHead'] = $SearchRow->feeHead;

				$StudentDiscountDetails[$SearchRow->feeDiscountID]['TotalAmount'] = $SearchRow->feeStructureAmount;
				$StudentDiscountDetails[$SearchRow->feeDiscountID]['DiscountAmount'] = 0;
				$StudentDiscountDetails[$SearchRow->feeDiscountID]['TotalConcession'] = 0;
				$StudentDiscountDetails[$SearchRow->feeDiscountID]['TotalWaveOff'] = 0;

				$StudentDiscountDetails[$SearchRow->feeDiscountID]['TransactionDate'] = '';
				$StudentDiscountDetails[$SearchRow->feeDiscountID]['DiscountDate'] = '';

				if (!empty($Filters['Discount'])) {
					$StudentDiscountDetails[$SearchRow->feeDiscountID]['DiscountAmount'] = $SearchRow->calculatedDiscountAmount - ($SearchRow->concessionAmount + $SearchRow->waveOffAmount);
					$StudentDiscountDetails[$SearchRow->feeDiscountID]['DiscountDate'] = $SearchRow->discountDateTime;
				} else {
					$StudentDiscountDetails[$SearchRow->feeDiscountID]['TransactionDate'] = $SearchRow->transactionDateTime;
					$StudentDiscountDetails[$SearchRow->feeDiscountID]['TotalConcession'] = $SearchRow->concessionAmount;
					$StudentDiscountDetails[$SearchRow->feeDiscountID]['TotalWaveOff'] = $SearchRow->waveOffAmount;
				}
			}

			return $StudentDiscountDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeDiscount::SearchStudentDiscountDetails(). Stack Trace: ' . $e->getTraceAsString());
			return $StudentDiscountDetails;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeDiscount::SearchStudentDiscountDetails(). Stack Trace: ' . $e->getTraceAsString());
			return $StudentDiscountDetails;
		}
	}

	static function RemoveDiscount($DiscountType, $FeeDiscountID)
	{
		try {
			$DBConnObject = new DBConnect();

			$json = array();
			$jsonRecordCounter = 0;

			$RSSearch = $DBConnObject->Prepare('SELECT * FROM afm_fee_discounts WHERE feeDiscountID = :|1;');
			$RSSearch->Execute($FeeDiscountID);

			if ($RSSearch->Result->num_rows > 0) {
				$json[$jsonRecordCounter]['table'] = 'afm_fee_discounts';
				$json[$jsonRecordCounter]['records'] = json_encode($RSSearch->FetchRow());
				$json[$jsonRecordCounter]['action'] = 'UPDATE';

				$jsonRecordCounter++;
			}

			if ($DiscountType == 'WaveOff') {
				$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts
													SET waveOffAmount = 0
													WHERE feeDiscountID = :|1;');

				$RSUpdate->Execute($FeeDiscountID);
			} else if ($DiscountType == 'Concession') {
				$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts
													SET concessionAmount = 0
													WHERE feeDiscountID = :|1;');

				$RSUpdate->Execute($FeeDiscountID);
			} else if ($DiscountType == 'Discount') {
				$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts
													SET discountValue = 0
													WHERE feeDiscountID = :|1;');

				$RSUpdate->Execute($FeeDiscountID);
			}

			foreach ($json as $data) {
				$RSInsertLog = $DBConnObject->Prepare('INSERT INTO change_log (userName, data_for, table_name, records, action_name) 
    												VALUES (:|1, :|2, :|3, :|4, :|5);');

				$RSInsertLog->Execute($_SESSION['ValidUser'], 'FeeDiscountID: ' . $FeeDiscountID, $data['table'], $data['records'], $data['action']);
			}

			return true;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeDiscount::RemoveDiscount(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeDiscount::RemoveDiscount(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}
	// END OF STATIC METHODS	//

	// START OF PRIVATE METHODS	//

	private function GetFeeDiscountByID()
	{
		$RSFeeDiscount = $this->DBObject->Prepare('SELECT * FROM afm_fee_discounts WHERE feeDiscountID = :|1 LIMIT 1;');
		$RSFeeDiscount->Execute($this->FeeDiscountID);

		$FeeDiscountRow = $RSFeeDiscount->FetchRow();

		$this->SetAttributesFromDB($FeeDiscountRow);
	}

	private function SetAttributesFromDB($FeeDiscountRow)
	{
		$this->FeeDiscountID = $FeeDiscountRow->feeDiscountID;
		$this->FeeDiscountType = $FeeDiscountRow->feeDiscountType;

		$this->StudentID = $FeeDiscountRow->studentID;
		$this->FeeStructureDetailID = $FeeDiscountRow->feeStructureDetailID;

		$this->DiscountType = $FeeDiscountRow->discountType;
		$this->DiscountValue = $FeeDiscountRow->discountValue;
	}
}
