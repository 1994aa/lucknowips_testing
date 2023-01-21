<?php
require_once('/home/addedschools/public_html/lucknowips/classes/class.db_connect.php');

error_reporting(E_ALL);

class FeeCollection
{
	// CLASS MEMBERS ARE DEFINED HERE	//
	private $LastErrorCode;
	private $DBObject; // VARIABLE TO HOLD THE DB CONNECTION //

	private $FeeCollectionID;
	private $FeeTransactionID;
	private $StudentID;
	private $FeeDate;

	private $TransactionAmount;
	private $TotalAmount;
	private $TotalDiscount;
	private $AmountPaid;
	private $PaymentMode;
	private $ChequeReferenceNo;

	private $CreateUserID;
	private $CreateDate;

	private $ParentID;
	private $Description;

	private $PaymentModeDetails = array();
	private $FeeCollectionDetails = array();
	private $OtherChargesDetails = array();
	private $AdvanceFeeDetails = array();

	private $CurrentTransactionID;

	// PUBLIC METHODS START HERE	//
	public function __construct($FeeCollectionID = 0)
	{
		$this->DBObject = new DBConnect;
		$this->LastErrorCode = 0;

		if ($FeeCollectionID != 0) {
			$this->FeeCollectionID = $FeeCollectionID;
			// SET THE VALUES FROM THE DATABASE.
			$this->GetFeeCollectionByID();
		} else {
			//SET THE DEFAULT VALUES TO LOOK ATTRIBUTES
			$this->FeeCollectionID = 0;
			$this->FeeTransactionID = 0;
			$this->StudentID = 0;
			$this->FeeDate = '0000-00-00';

			$this->TransactionAmount = 0;
			$this->TotalAmount = 0;
			$this->TotalDiscount = 0;
			$this->AmountPaid = 0;
			$this->PaymentMode = 0;
			$this->ChequeReferenceNo = '';

			$this->CreateUserID = 0;
			$this->CreateDate = '0000-00-00 00:00:00';

			$this->PaymentModeDetails = array();

			$this->ParentID;
			$this->Description;

			$this->FeeCollectionDetails = array();
			$this->OtherChargesDetails = array();
			$this->AdvanceFeeDetails = array();

			$this->CurrentTransactionID = 0;
		}
	}

	// GETTER AND SETTER FUNCTIONS START HERE	//
	public function GetFeeCollectionID()
	{
		return $this->FeeCollectionID;
	}

	public function GetFeeTransactionID()
	{
		return $this->FeeTransactionID;
	}

	public function GetStudentID()
	{
		return $this->StudentID;
	}
	public function SetStudentID($StudentID)
	{
		$this->StudentID = $StudentID;
	}

	public function GetFeeDate()
	{
		return $this->FeeDate;
	}
	public function SetFeeDate($FeeDate)
	{
		$this->FeeDate = $FeeDate;
	}

	public function GetTransactionAmount()
	{
		return $this->TransactionAmount;
	}
	public function SetTransactionAmount($TransactionAmount)
	{
		$this->TransactionAmount = $TransactionAmount;
	}

	public function GetTotalAmount()
	{
		return $this->TotalAmount;
	}
	public function SetTotalAmount($TotalAmount)
	{
		$this->TotalAmount = $TotalAmount;
	}

	public function GetTotalDiscount()
	{
		return $this->TotalDiscount;
	}
	public function SetTotalDiscount($TotalDiscount)
	{
		$this->TotalDiscount = $TotalDiscount;
	}

	public function GetAmountPaid()
	{
		return $this->AmountPaid;
	}
	public function SetAmountPaid($AmountPaid)
	{
		$this->AmountPaid = $AmountPaid;
	}

	public function GetPaymentMode()
	{
		return $this->PaymentMode;
	}
	public function SetPaymentMode($PaymentMode)
	{
		$this->PaymentMode = $PaymentMode;
	}

	public function GetChequeReferenceNo()
	{
		return $this->ChequeReferenceNo;
	}
	public function SetChequeReferenceNo($ChequeReferenceNo)
	{
		$this->ChequeReferenceNo = $ChequeReferenceNo;
	}

	public function GetCreateUserID()
	{
		return $this->CreateUserID;
	}
	public function SetCreateUserID($CreateUserID)
	{
		$this->CreateUserID = $CreateUserID;
	}

	public function GetPaymentModeDetails()
	{
		return $this->PaymentModeDetails;
	}
	public function SetPaymentModeDetails($PaymentModeDetails)
	{
		$this->PaymentModeDetails = $PaymentModeDetails;
	}

	public function GetParentID()
	{
		return $this->ParentID;
	}
	public function SetParentID($ParentID)
	{
		$this->ParentID = $ParentID;
	}

	public function GetDescription()
	{
		return $this->Description;
	}
	public function SetDescription($Description)
	{
		$this->Description = $Description;
	}

	public function GetFeeCollectionDetails()
	{
		return $this->FeeCollectionDetails;
	}
	public function SetFeeCollectionDetails($FeeCollectionDetails)
	{
		$this->FeeCollectionDetails = $FeeCollectionDetails;
	}

	public function GetOtherChargesDetails()
	{
		return $this->OtherChargesDetails;
	}
	public function SetOtherChargesDetails($OtherChargesDetails)
	{
		$this->OtherChargesDetails = $OtherChargesDetails;
	}

	public function GetAdvanceFeeDetails()
	{
		return $this->AdvanceFeeDetails;
	}
	public function SetAdvanceFeeDetails($AdvanceFeeDetails)
	{
		$this->AdvanceFeeDetails = $AdvanceFeeDetails;
	}

	public function GetCurrentTransactionID()
	{
		return $this->CurrentTransactionID;
	}

	public function GetCreateDate()
	{
		return $this->CreateDate;
	}

	public function GetLastErrorCode()
	{
		return $this->LastErrorCode;
	}

	//  END OF GETTER AND SETTER FUNCTIONS 	//

	public function Save()
	{
		try {
			$this->DBObject->BeginTransaction();
			if ($this->SaveDetails()) {
				$this->DBObject->CommitTransaction();
				return true;
			}

			$this->DBObject->RollBackTransaction();
			return false;
		} catch (ApplicationDBException $e) {
			$this->DBObject->RollBackTransaction();
			$this->LastErrorCode = $e->getCode();
			return false;
		} catch (ApplicationARException $e) {
			$this->DBObject->RollBackTransaction();
			$this->LastErrorCode = $e->getCode();
			return false;
		} catch (Exception $e) {
			$this->DBObject->RollBackTransaction();
			$this->LastErrorCode = APP_ERROR_UNDEFINED_ERROR;
			return false;
		}
	}

	public function Remove()
	{
		try {
			$this->RemoveFeeCollection();
			return true;
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

	// END OF PUBLIC METHODS	//

	// START OF STATIC METHODS	//
	static function GetUpdateDB()
	{
		try {

			$DBConnObject = new DBConnect();

			//23-24
			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-23-24`.afm_fee_collection_details SET academicYearID = 4 WHERE academicYearID = 0 ;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare("UPDATE `addedschools_lucknowips_testing-23-24`.afm_fee_transactions SET academicYearID = '4' WHERE academicYearID = 0;");
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-23-24`.afm_fee_discounts fd
                                                    INNER JOIN `addedschools_lucknowips_testing-23-24`.afm_fee_structure_details fsd ON fd.feeStructureDetailID = fsd.feeStructureDetailID
                                                    SET fd.feeStructureAmount = fsd.feeAmount
                                                    WHERE fd.feeStructureAmount = 0;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-23-24`.afm_fee_discounts 
                                                SET calculatedDiscountAmount = (feeStructureAmount * discountValue / 100) + waveOffAmount + concessionAmount
                                                WHERE discountType = "Percentage";');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-23-24`.afm_fee_discounts 
                                                SET calculatedDiscountAmount = (waveOffAmount + concessionAmount + discountValue)
                                                WHERE discountType = "Absolute";');
			$RSUpdate->Execute();

			//22-23
			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-22-23`.afm_fee_collection_details SET academicYearID = 2 WHERE academicYearID = 0 ;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare("UPDATE `addedschools_lucknowips_testing-22-23`.afm_fee_transactions SET academicYearID = '2' WHERE academicYearID = 0;");
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-22-23`.afm_fee_discounts fd
                                                    INNER JOIN `addedschools_lucknowips_testing-22-23`.afm_fee_structure_details fsd ON fd.feeStructureDetailID = fsd.feeStructureDetailID
                                                    SET fd.feeStructureAmount = fsd.feeAmount
                                                    WHERE fd.feeStructureAmount = 0;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-22-23`.afm_fee_discounts 
                                                SET calculatedDiscountAmount = (feeStructureAmount * discountValue / 100) + waveOffAmount + concessionAmount
                                                WHERE discountType = "Percentage";');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-22-23`.afm_fee_discounts 
                                                SET calculatedDiscountAmount = (waveOffAmount + concessionAmount + discountValue)
                                                WHERE discountType = "Absolute";');
			$RSUpdate->Execute();

			//21-22
			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-21-22`.afm_fee_collection_details SET academicYearID = 2 WHERE academicYearID = 0 ;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare("UPDATE `addedschools_lucknowips_testing-21-22`.afm_fee_transactions SET academicYearID = '2' WHERE academicYearID = 0;");
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-21-22`.afm_fee_discounts fd
                                                    INNER JOIN `addedschools_lucknowips_testing-21-22`.afm_fee_structure_details fsd ON fd.feeStructureDetailID = fsd.feeStructureDetailID
                                                    SET fd.feeStructureAmount = fsd.feeAmount
                                                    WHERE fd.feeStructureAmount = 0;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-21-22`.afm_fee_discounts 
                                                SET calculatedDiscountAmount = (feeStructureAmount * discountValue / 100) + waveOffAmount + concessionAmount
                                                WHERE discountType = "Percentage";');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE `addedschools_lucknowips_testing-21-22`.afm_fee_discounts 
                                                SET calculatedDiscountAmount = (waveOffAmount + concessionAmount + discountValue)
                                                WHERE discountType = "Absolute";');
			$RSUpdate->Execute();

			//old db
			$RSUpdate = $DBConnObject->Prepare("UPDATE addedschools_lucknowips_testing.afm_fee_discounts fd
                                                INNER JOIN addedschools_lucknowips_testing.afm_fee_transactions ft ON fd.feeTransactionID = ft.feeTransactionID
                                                INNER JOIN addedschools_lucknowips_testing.afm_fee_collection fc ON ft.feeTransactionID = fc.feeTransactionID
                                                SET fd.transactionDateTime = CONCAT(fc.feeDate ,' ', time(fd.transactionDateTime)) 
                                                WHERE DATE(fd.transactionDateTime) != fc.feeDate;");
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare("UPDATE addedschools_lucknowips_testing.afm_fee_transactions ft
                                                INNER JOIN addedschools_lucknowips_testing.afm_fee_collection fc ON ft.feeTransactionID = fc.feeTransactionID
                                                SET ft.createDate = fc.feeDate
                                                WHERE date(ft.createDate) != fc.feeDate");
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare("UPDATE addedschools_lucknowips_testing.afm_fee_transactions SET academicYearID = '2' WHERE academicYearID = 0;");
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE addedschools_lucknowips_testing.afm_fee_discounts fd
                                                    INNER JOIN addedschools_lucknowips_testing.afm_fee_structure_details fsd ON fd.feeStructureDetailID = fsd.feeStructureDetailID
                                                    SET fd.feeStructureAmount = fsd.feeAmount
                                                    WHERE fd.feeStructureAmount = 0;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE addedschools_lucknowips_testing.afm_fee_discounts 
                                                SET calculatedDiscountAmount = (feeStructureAmount * discountValue / 100) + waveOffAmount + concessionAmount
                                                WHERE discountType = "Percentage";');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE addedschools_lucknowips_testing.afm_fee_collection_details SET academicYearID = 2 WHERE academicYearID = 0 ;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE addedschools_lucknowips_testing.afm_fee_discounts 
                                                SET calculatedDiscountAmount = (waveOffAmount + concessionAmount + discountValue)
                                                WHERE discountType = "Absolute";');
			$RSUpdate->Execute();

			return true;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetUpdateDB(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetUpdateDB(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function GetPrevTableAmount($StudentID)
	{
		$Amount = 0;
		try {

			$DBConnObject = new DBConnect();

			$RSSearchPrev = $DBConnObject->Prepare('SELECT (payableAmount - paidAmount - waveOffDue) as totalPreviousYearDue
												FROM addedschools_lucknowips_testing.afm_previous_year_fee_details
												WHERE studentID = :|1;');

			$RSSearchPrev->Execute($StudentID);

			if ($RSSearchPrev->Result->num_rows > 0) {
				$Amount = $RSSearchPrev->FetchRow()->totalPreviousYearDue;
			}

			return $Amount;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetPrevTableAmount(). Stack Trace: ' . $e->getTraceAsString());
			return $Amount;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetPrevTableAmount(). Stack Trace: ' . $e->getTraceAsString());
			return $Amount;
		}
	}

	static function GetPrevYearDefaulterAmount($StudentID, $Year = 1)
	{
		$Amount = 0;
		try {

			$DBConnObject = new DBConnect();

			$Condition = '';
			if ($Year == 2) {
				$RSSearch = $DBConnObject->Prepare('SELECT feePriority 
											FROM addedschools_lucknowips_testing.asa_student_status_change_log 
											WHERE newStatus = "InActive" 
											AND studentID = :|1 
											AND academicYearID = 2 
											AND isLastByAcademicYearID = 1;');
				$RSSearch->Execute($StudentID);

				$Condition = '';

				$FeePriority = 0;
				if ($RSSearch->Result->num_rows > 0) {
					$FeePriority = $RSSearch->FetchRow()->feePriority;
				}

				if ($FeePriority) {
					$Condition = ' AND aaym.feePriority <= ' . $FeePriority;
				} else {
					$Condition = ' AND asfs.studentID IN (SELECT studentID FROM addedschools_lucknowips_testing.asa_students WHERE status = "Active")';
				}
			}

			$RSFeeDefaulterDues = $DBConnObject->Prepare('SELECT afh.feeHead, afh.feeHeadID, afsd.feeAmount, aaym.monthName, 
														asfs.amountPayable,
														(
															SELECT IFNULL(SUM(fcd.amountPaid), 0)
															FROM addedschools_lucknowips_testing.afm_student_fee_structure sfss
															INNER JOIN addedschools_lucknowips_testing.afm_fee_collection fc ON (sfss.studentID = fc.studentID)
															INNER JOIN addedschools_lucknowips_testing.afm_fee_collection_details fcd ON (fc.feeCollectionID = fcd.feeCollectionID AND sfss.studentFeeStructureID = fcd.studentFeeStructureID)
															INNER JOIN addedschools_lucknowips_testing.afm_fee_structure_details fsds ON (sfss.feeStructureDetailID = fsds.feeStructureDetailID)
															WHERE sfss.studentID = ' . $StudentID . ' AND sfss.studentFeeStructureID = asfs.studentFeeStructureID
															AND sfss.academicYearID = ' . $Year . ' AND fcd.academicYearID != 0
															AND fsds.academicYearID = ' . $Year . ') AS totalAmountPaid,
														(
															SELECT IFNULL(SUM(fd.calculatedDiscountAmount), 0)
															FROM addedschools_lucknowips_testing.afm_fee_discounts fd
															INNER JOIN addedschools_lucknowips_testing.afm_fee_structure_details fsds ON fsds.feeStructureDetailID = fd.feeStructureDetailID
															WHERE fd.studentID = ' . $StudentID . ' AND fsds.feeStructureDetailID = afsd.feeStructureDetailID
															AND fsds.academicYearID = ' . $Year . '
														) As totalDiscountAmount
														FROM addedschools_lucknowips_testing.afm_student_fee_structure asfs
														INNER JOIN addedschools_lucknowips_testing.afm_fee_structure_details afsd ON asfs.feeStructureDetailID = afsd.feeStructureDetailID
														INNER JOIN addedschools_lucknowips_testing.afm_fee_heads afh ON afh.feeHeadID = afsd.feeHeadID
														INNER JOIN addedschools_lucknowips_testing.asa_academic_year_months aaym ON aaym.academicYearMonthID = afsd.academicYearMonthID
														WHERE asfs.studentID = :|1 ' . $Condition . ' AND asfs.academicYearID = ' . $Year . '
														GROUP BY afsd.feeHeadID, afsd.academicYearMonthID
														ORDER BY aaym.feePriority;');

			$RSFeeDefaulterDues->Execute($StudentID);

			if ($RSFeeDefaulterDues->Result->num_rows <= 0) {
				return $Amount;
			}

			while ($SearchRow = $RSFeeDefaulterDues->FetchRow()) {
				$DueAmount = $SearchRow->amountPayable - ($SearchRow->totalAmountPaid + $SearchRow->totalDiscountAmount);

				if ($DueAmount <= 0) {
					continue;
				}

				$Amount += $DueAmount;
			}

			return $Amount;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetPrevYearDefaulterAmount(). Stack Trace: ' . $e->getTraceAsString());
			return $Amount;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetPrevYearDefaulterAmount(). Stack Trace: ' . $e->getTraceAsString());
			return $Amount;
		}
	}

	static function GetPreviousDefaulterAmount($StudentID)
	{
		$PrevTableAmount = self::GetPrevTableAmount($StudentID);
		$PrevYear1Amount = self::GetPrevYearDefaulterAmount($StudentID, 1);
		$PrevYear2Amount = self::GetPrevYearDefaulterAmount($StudentID, 2);

		// echo "$PrevTableAmount + $PrevYear1Amount + $PrevYear2Amount id $StudentID";exit;
		$totalAmount = $PrevTableAmount + $PrevYear1Amount + $PrevYear2Amount;

		return $totalAmount;
	}

	static function GetAllPreviousYearAmount($StudentID)
	{
		try {

			$DBConnObject = new DBConnect();

			$RSSearchStudent = $DBConnObject->Prepare('SELECT SUM(payableAmount) AS totalAmount FROM afm_previous_year_fee_details_21_22 WHERE studentID = :|1;');
			$RSSearchStudent->Execute($StudentID);

			if ($RSSearchStudent->Result->num_rows <= 0) {
				return 0;
			}

			return $RSSearchStudent->FetchRow()->totalAmount;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::searchAllPreviousYearDue(). Stack Trace: ' . $e->getTraceAsString());
			return 0;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::searchAllPreviousYearDue(). Stack Trace: ' . $e->getTraceAsString());
			return 0;
		}
	}

	//pending for review
	static function searchAllPreviousYearDue($Filters = array())
	{
		if ($_SESSION['DB'] == 'addedschools_lucknowips_testing') {
			return;
		}

		$Amount = 0;
		try {

			$DBConnObject = new DBConnect();

			$RDRecord = $DBConnObject->Prepare('TRUNCATE `addedschools_lucknowips_testing-21-22`.afm_previous_year_fee_details_21_22;');
			$RDRecord->Execute();

			$RSSearchStudent = $DBConnObject->Prepare('SELECT studentID FROM `addedschools_lucknowips_testing`.asa_students;');
			$RSSearchStudent->Execute();

			if ($RSSearchStudent->Result->num_rows <= 0) {
				return true;
			}

			while ($SearchRow = $RSSearchStudent->FetchRow()) {
				$TotalPreviousAmount = FeeCollection::GetPreviousDefaulterAmount($SearchRow->studentID);

				if ($TotalPreviousAmount > 0) {
					$RSInsertRow = $DBConnObject->Prepare('INSERT INTO afm_previous_year_fee_details_21_22
																								(academicYearID, studentID, payableAmount)
																								VALUES (2, :|1, :|2);');
					$RSInsertRow->Execute($SearchRow->studentID, $TotalPreviousAmount);
				}
			}

			return true;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::searchAllPreviousYearDue(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::searchAllPreviousYearDue(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function SearchChequeTransactionDetails(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $Start = 0, $Limit = 100)
	{
		$ChequeTransactionDetails = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();

			$Conditions[] = 'afpmd.paymentMode = 2';

			$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID ';

			if (count($Filters) > 0) {
				if (!empty($Filters['AcademicYearID'])) {
					if ($Filters['AcademicYearID'] > 0 && $Filters['AcademicYearID'] < 2) {
						$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = spyd.previousClassSectionID ';
					}

					$Conditions[] = 'afs.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
				}

				if (!empty($Filters['TransactionDate'])) {
					$Conditions[] = 'afc.feeDate = ' . $DBConnObject->RealEscapeVariable($Filters['TransactionDate']);
				}

				if (!empty($Filters['TransactionFromDate'])) {
					$Conditions[] = 'afc.feeDate BETWEEN ' . $DBConnObject->RealEscapeVariable($Filters['TransactionFromDate']) . 'AND' . $DBConnObject->RealEscapeVariable($Filters['TransactionToDate']);
				}

				if (!empty($Filters['ClassID'])) {
					$Conditions[] = 'ac.classID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassID']);
				}

				if (!empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'ass.classSectionID =  ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']) . ' OR spyd.previousClassSectionID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']);
				}

				if (!empty($Filters['StudentID'])) {
					$Conditions[] = 'asd.studentID = ' . $DBConnObject->RealEscapeVariable($Filters['StudentID']);
				}

				if (!empty($Filters['StudentName'])) {
					$Conditions[] = 'CONCAT(asd.firstName, " ", asd.lastName) LIKE ' . $DBConnObject->RealEscapeVariable('%' . $Filters['StudentName'] . '%');
				}

				if (!empty($Filters['ChequeReferenceNo'])) {
					$Conditions[] = 'afpmd.chequeReferenceNo = ' . $DBConnObject->RealEscapeVariable($Filters['ChequeReferenceNo']);
				}

				if (!empty($Filters['ChequeStatus'])) {
					$Conditions[] = 'afpmd.chequeStatus = ' . $DBConnObject->RealEscapeVariable($Filters['ChequeStatus']);
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = implode(') AND (', $Conditions);

				$QueryString = ' WHERE (' . $QueryString . ')';
			}

			if ($GetTotalsOnly) {
				$RSTotal = $DBConnObject->Prepare('SELECT COUNT(Distinct afpmd.feePaymentModeDetailID) AS totalRecords 
													FROM afm_fee_payment_mode_details afpmd
													INNER JOIN afm_fee_transactions aft ON aft.feeTransactionID = afpmd.feeTransactionID
													INNER JOIN afm_fee_collection afc ON afc.feeTransactionID = aft.feeTransactionID

													LEFT JOIN afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID
                                                    LEFT JOIN afm_fee_collection_other_charges afcoc ON afcoc.feeCollectionID = afc.feeCollectionID
                                                    
                                                    LEFT JOIN afm_student_fee_structure asfs ON asfs.studentFeeStructureID = afcd.studentFeeStructureID 
                                                    
                                                    LEFT JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
                                                    LEFT JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
                                                    
                                                    LEFT JOIN asa_academic_years ay ON ay.academicYearID = afs.academicYearID 
                                                    
                                                    INNER JOIN asa_student_details asd ON asd.studentID = afc.studentID 
                                                    LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID 
                                                    INNER JOIN asa_students ass ON ass.studentID = asd.studentID 
                                                    ' . $JoinClassSectionTable . ' 
                                                    INNER JOIN asa_classes ac ON ac.classID = acs.classID 
                                                    INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID 
                                                    LEFT JOIN users u ON afpmd.statusChangedBy = u.userID
													' . $QueryString . ';');
				$RSTotal->Execute();

				$TotalRecords = $RSTotal->FetchRow()->totalRecords;
				return;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT afpmd.*, afc.feeDate, u.userName AS statusChangedBy, 
												asd.firstName, asd.lastName, ac.className, asm.sectionName
												FROM afm_fee_payment_mode_details afpmd
												INNER JOIN afm_fee_transactions aft ON aft.feeTransactionID = afpmd.feeTransactionID
												INNER JOIN afm_fee_collection afc ON afc.feeTransactionID = aft.feeTransactionID

												LEFT JOIN afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID 
												LEFT JOIN afm_fee_collection_other_charges afcoc ON afcoc.feeCollectionID = afc.feeCollectionID
												
												LEFT JOIN afm_student_fee_structure asfs ON asfs.studentFeeStructureID = afcd.studentFeeStructureID 
												LEFT JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
												LEFT JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
												
												LEFT JOIN asa_academic_years ay ON ay.academicYearID = afs.academicYearID 
												
												INNER JOIN asa_student_details asd ON asd.studentID = afc.studentID 
												LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID 
												INNER JOIN asa_students ass ON ass.studentID = asd.studentID 
												' . $JoinClassSectionTable . ' 
												INNER JOIN asa_classes ac ON ac.classID = acs.classID 
												INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID 
												LEFT JOIN users u ON afpmd.statusChangedBy = u.userID
												' . $QueryString . '
												GROUP BY afpmd.feePaymentModeDetailID
												ORDER BY afpmd.feePaymentModeDetailID LIMIT ' . (int) $Start . ', ' . (int) $Limit . ';');
			$RSSearch->Execute();

			if ($RSSearch->Result->num_rows <= 0) {
				return $ChequeTransactionDetails;
			}

			while ($SearchRow = $RSSearch->FetchRow()) {
				$ChequeTransactionDetails[$SearchRow->feePaymentModeDetailID]['FeeTransactionID'] = $SearchRow->feeTransactionID;
				$ChequeTransactionDetails[$SearchRow->feePaymentModeDetailID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;

				$ChequeTransactionDetails[$SearchRow->feePaymentModeDetailID]['ClassName'] = $SearchRow->className;
				$ChequeTransactionDetails[$SearchRow->feePaymentModeDetailID]['SectionName'] = $SearchRow->sectionName;

				$ChequeTransactionDetails[$SearchRow->feePaymentModeDetailID]['FeeDate'] = $SearchRow->feeDate;

				$ChequeTransactionDetails[$SearchRow->feePaymentModeDetailID]['Amount'] = $SearchRow->amount;
				$ChequeTransactionDetails[$SearchRow->feePaymentModeDetailID]['PaymentMode'] = $SearchRow->paymentMode;
				$ChequeTransactionDetails[$SearchRow->feePaymentModeDetailID]['ChequeReferenceNo'] = $SearchRow->chequeReferenceNo;
				$ChequeTransactionDetails[$SearchRow->feePaymentModeDetailID]['ChequeStatus'] = $SearchRow->chequeStatus;

				$ChequeTransactionDetails[$SearchRow->feePaymentModeDetailID]['StatusChangedBy'] = $SearchRow->statusChangedBy;
				$ChequeTransactionDetails[$SearchRow->feePaymentModeDetailID]['StatusChangedDate'] = $SearchRow->statusChangedDate;
			}

			return $ChequeTransactionDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchChequeTransactionDetails(). Stack Trace: ' . $e->getTraceAsString());
			return $ChequeTransactionDetails;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchChequeTransactionDetails(). Stack Trace: ' . $e->getTraceAsString());
			return $ChequeTransactionDetails;
		}
	}

	static function UpdateChequeStatus($FeePaymentModeDetailID, $ChequeStatus, $StatusChangedBy, $ChequeBouncedDescription = '')
	{
		try {
			$DBConnObject = new DBConnect();
			$DBConnObject->BeginTransaction();

			if ($ChequeStatus == 'Bounced') {
				$RSSearch = $DBConnObject->Prepare('SELECT afpmd.amount, afc.feeCollectionID, afcd.feeCollectionDetailID, afcd.studentFeeStructureID, afcd.amountPaid, afcoc.amount AS otherAmount
													FROM afm_fee_payment_mode_details afpmd
													INNER JOIN afm_fee_transactions aft ON aft.feeTransactionID = afpmd.feeTransactionID 
													INNER JOIN afm_fee_collection afc ON afc.feeTransactionID = aft.feeTransactionID 

													LEFT JOIN afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID 
													LEFT JOIN afm_fee_collection_other_charges afcoc ON afcoc.feeCollectionID = afc.feeCollectionID

													WHERE afpmd.feePaymentModeDetailID = :|1
													ORDER BY afcd.feeCollectionDetailID DESC;');
				$RSSearch->Execute($FeePaymentModeDetailID);

				if ($RSSearch->Result->num_rows > 0) {
					$ChequeAmount = 0;
					$AdjustedAmount = 0;

					while ($SearchRow = $RSSearch->FetchRow()) {
						$UpdateCollectionAmount = 0;

						$ChequeAmount = $SearchRow->amount - $AdjustedAmount;

						if ($SearchRow->amountPaid > $ChequeAmount) {
							$UpdateCollectionAmount = $SearchRow->amountPaid - $ChequeAmount;
						}

						$AdjustedAmount += $SearchRow->amountPaid;

						if ($ChequeAmount > 0) {
							$RSUpdateCollectionAmount = $DBConnObject->Prepare('UPDATE afm_fee_collection_details
																SET	amountPaid = :|1
																WHERE feeCollectionDetailID = :|2 LIMIT 1;');
							$RSUpdateCollectionAmount->Execute($UpdateCollectionAmount, $SearchRow->feeCollectionDetailID);
						}
					}
				}
			}

			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_payment_mode_details
												SET	chequeStatus = :|1,
													statusChangedBy = :|2, 
													statusChangedDate = NOW(),
													chequeBouncedDescription = :|3
												WHERE feePaymentModeDetailID = :|4 LIMIT 1;');
			$RSUpdate->Execute($ChequeStatus, $StatusChangedBy, $ChequeBouncedDescription, $FeePaymentModeDetailID);

			$DBConnObject->CommitTransaction();

			return true;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::UpdateChequeStatus(). Stack Trace: ' . $e->getTraceAsString());
			$DBConnObject->RollBackTransaction();
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::UpdateChequeStatus(). Stack Trace: ' . $e->getTraceAsString());
			$DBConnObject->RollBackTransaction();
			return false;
		}
	}

	static function SearchFeeTransactions(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $Start = 0, $Limit = 100)
	{
		$FeeTransactionDetails = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();

			if (count($Filters) > 0) {
				if (!empty($Filters['AcademicYearID'])) {
					$Conditions[] = 'afs.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
				}

				if (!empty($Filters['ClassID'])) {
					$Conditions[] = 'ac.classID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassID']);
				}

				if (!empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'ass.classSectionID =  ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']) . ' OR spyd.previousClassSectionID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']);
				}

				if (!empty($Filters['StudentID'])) {
					$Conditions[] = 'asd.studentID = ' . $DBConnObject->RealEscapeVariable($Filters['StudentID']);
				}

				if (!empty($Filters['TransactionDate'])) {
					$Conditions[] = 'afc.feeDate = ' . $DBConnObject->RealEscapeVariable($Filters['TransactionDate']);
				}

				if (!empty($Filters['TransactionFromDate'])) {
					$Conditions[] = 'afc.feeDate BETWEEN ' . $DBConnObject->RealEscapeVariable($Filters['TransactionFromDate']) . 'AND' . $DBConnObject->RealEscapeVariable($Filters['TransactionToDate']);
				}

				// if (!empty($Filters['FeeHeadID']))
				// {
				// 	$Conditions[] = 'fsd.feeHeadID = '. $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']);
				// }

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = 'asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']);
				}

				if (!empty($Filters['PaymentMode'])) {
					$Conditions[] = 'afpmd.paymentMode = ' . $DBConnObject->RealEscapeVariable($Filters['PaymentMode']);
				}

				if (!empty($Filters['Description'])) {
					$Conditions[] = 'aft.description != "" ';
				}

				if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'New') {
					$Conditions[] = 'ass.studentRegistrationID != 0';
				}

				if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'Old') {
					$Conditions[] = 'ass.studentRegistrationID = 0';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'Active') {
					$Conditions[] = 'ass.status = \'Active\'';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'InActive') {
					$Conditions[] = 'ass.status != \'Active\'';
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = implode(') AND (', $Conditions);

				$QueryString = ' WHERE (' . $QueryString . ')';
			}

			if ($GetTotalsOnly) {
				$RSTotal = $DBConnObject->Prepare('SELECT COUNT(DISTINCT aft.feeTransactionID) AS totalRecords 
													FROM afm_fee_transactions aft
													INNER JOIN afm_fee_payment_mode_details afpmd ON afpmd.feeTransactionID = aft.feeTransactionID
													INNER JOIN afm_fee_collection afc ON afc.feeTransactionID = aft.feeTransactionID

													LEFT JOIN afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID
	                                                LEFT JOIN afm_student_fee_structure asfs ON asfs.studentFeeStructureID = afcd.studentFeeStructureID 
	                                                LEFT JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
	                                                LEFT JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
                                                    
                                                    INNER JOIN asa_student_details asd ON asd.studentID = afc.studentID     
													INNER JOIN asa_students ass ON ass.studentID = afc.studentID
													INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID
													LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID
													LEFT JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID
													LEFT JOIN asa_classes ac ON ac.classID = acs.classID
													
                                                    INNER JOIN users u ON aft.createUserID = u.userID
													' . $QueryString . ';');
				$RSTotal->Execute();

				$TotalRecords = $RSTotal->FetchRow()->totalRecords;
				return;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT aft.feeTransactionID, aft.description, aft.createDate, aft.transactionAmount AS totalTransactionAmount, afc.feeDate, apd.fatherFirstName, apd.fatherLastName, u.userName AS createUserName, apd.fatherMobileNumber, apd.motherMobileNumber, asd.mobileNumber 
												FROM afm_fee_transactions aft
												INNER JOIN afm_fee_payment_mode_details afpmd ON afpmd.feeTransactionID = aft.feeTransactionID
												INNER JOIN afm_fee_collection afc ON afc.feeTransactionID = aft.feeTransactionID

												LEFT JOIN afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID
                                                LEFT JOIN afm_student_fee_structure asfs ON asfs.studentFeeStructureID = afcd.studentFeeStructureID 
                                                LEFT JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
                                                LEFT JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
                                                
                                                INNER JOIN asa_student_details asd ON asd.studentID = afc.studentID 
												INNER JOIN asa_students ass ON ass.studentID = afc.studentID
												INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID
												LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID
												LEFT JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID
												LEFT JOIN asa_classes ac ON ac.classID = acs.classID
												
                                                INNER JOIN users u ON aft.createUserID = u.userID
												' . $QueryString . '

												GROUP BY aft.feeTransactionID
												ORDER BY afc.feeDate LIMIT ' . (int) $Start . ', ' . (int) $Limit . ';');
			$RSSearch->Execute();

			if ($RSSearch->Result->num_rows <= 0) {
				return $FeeTransactionDetails;
			}

			while ($SearchRow = $RSSearch->FetchRow()) {
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['FatherName'] = $SearchRow->fatherFirstName . ' ' . $SearchRow->fatherLastName;
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['FatherMobileNumber'] = $SearchRow->fatherMobileNumber;
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['FeeDate'] = $SearchRow->feeDate;

				$FeeTransactionDetails[$SearchRow->feeTransactionID]['TransactionAmount'] = $SearchRow->totalTransactionAmount;
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['Description'] = $SearchRow->description;

				$FeeTransactionDetails[$SearchRow->feeTransactionID]['CreateUserName'] = $SearchRow->createUserName;
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['CreateDate'] = $SearchRow->createDate;
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['PaymentModeDetails'] = array();
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['StudentDetails'] = array();

				$RSSearchStudents = $DBConnObject->Prepare('SELECT sd.studentID, sd.firstName, sd.lastName FROM asa_student_details sd
				                                            INNER JOIN afm_fee_collection fc ON fc.studentID = sd.studentID
				                                            WHERE fc.feeTransactionID = :|1;');
				$RSSearchStudents->Execute($SearchRow->feeTransactionID);

				if ($RSSearchStudents->Result->num_rows > 0) {
					while ($SearchStudentRow = $RSSearchStudents->FetchRow()) {
						$FeeTransactionDetails[$SearchRow->feeTransactionID]['StudentDetails'][$SearchStudentRow->studentID]['FirstName'] = $SearchStudentRow->firstName;
						$FeeTransactionDetails[$SearchRow->feeTransactionID]['StudentDetails'][$SearchStudentRow->studentID]['LastName'] = $SearchStudentRow->lastName;
					}
				}

				$RSSearchPaymentMode = $DBConnObject->Prepare('SELECT * FROM afm_fee_payment_mode_details WHERE feeTransactionID = :|1;');
				$RSSearchPaymentMode->Execute($SearchRow->feeTransactionID);

				if ($RSSearchPaymentMode->Result->num_rows > 0) {
					while ($SearchPaymentModeRow = $RSSearchPaymentMode->FetchRow()) {
						$FeeTransactionDetails[$SearchRow->feeTransactionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode] = $SearchPaymentModeRow->amount;
					}
				}
			}

			return $FeeTransactionDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchFeeTransactions(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeTransactionDetails;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchFeeTransactions(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeTransactionDetails;
		}
	}

	static function SearchFeeTransactionsNew_2021(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $Start = 0, $Limit = 100)
	{
		$FeeTransactionDetails = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();

			if (count($Filters) > 0) {
				if (!empty($Filters['AcademicYearID'])) {
					$Conditions[] = 'afs.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
				}

				if (!empty($Filters['TransactionDate'])) {
					$Conditions[] = 'afc.feeDate = ' . $DBConnObject->RealEscapeVariable($Filters['TransactionDate']);
				}

				if (!empty($Filters['TransactionFromDate'])) {
					$Conditions[] = 'afc.feeDate BETWEEN ' . $DBConnObject->RealEscapeVariable($Filters['TransactionFromDate']) . 'AND' . $DBConnObject->RealEscapeVariable($Filters['TransactionToDate']);
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = 'asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']);
				}

				$Conditions[] = 'afc.feeDate >= "2021-04-01"';
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = implode(') AND (', $Conditions);

				$QueryString = ' WHERE (' . $QueryString . ')';
			}

			if ($GetTotalsOnly) {
				$RSTotal = $DBConnObject->Prepare('SELECT COUNT(DISTINCT aft.feeTransactionID) AS totalRecords 
													FROM addedschools_lucknowips_testing.afm_fee_transactions aft
													INNER JOIN addedschools_lucknowips_testing.afm_fee_payment_mode_details afpmd ON afpmd.feeTransactionID = aft.feeTransactionID
													INNER JOIN addedschools_lucknowips_testing.afm_fee_collection afc ON afc.feeTransactionID = aft.feeTransactionID

													LEFT JOIN addedschools_lucknowips_testing.afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID
	                                                LEFT JOIN addedschools_lucknowips_testing.afm_student_fee_structure asfs ON asfs.studentFeeStructureID = afcd.studentFeeStructureID 
	                                                LEFT JOIN addedschools_lucknowips_testing.afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
	                                                LEFT JOIN addedschools_lucknowips_testing.afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
                                                    
                                                    INNER JOIN addedschools_lucknowips_testing.asa_student_details asd ON asd.studentID = afc.studentID     
													INNER JOIN addedschools_lucknowips_testing.asa_students ass ON ass.studentID = afc.studentID
													INNER JOIN addedschools_lucknowips_testing.asa_parent_details apd ON apd.parentID = ass.parentID
													LEFT JOIN addedschools_lucknowips_testing.asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID
													LEFT JOIN addedschools_lucknowips_testing.asa_class_sections acs ON acs.classSectionID = ass.classSectionID
													LEFT JOIN addedschools_lucknowips_testing.asa_classes ac ON ac.classID = acs.classID
													
                                                    INNER JOIN addedschools_lucknowips_testing.users u ON aft.createUserID = u.userID
													' . $QueryString . ';');
				$RSTotal->Execute();

				$TotalRecords = $RSTotal->FetchRow()->totalRecords;
				return;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT aft.feeTransactionID, aft.description, aft.createDate, aft.transactionAmount AS totalTransactionAmount, afc.feeDate, apd.fatherFirstName, apd.fatherLastName, u.userName AS createUserName, apd.fatherMobileNumber, apd.motherMobileNumber, asd.mobileNumber 
												FROM addedschools_lucknowips_testing.afm_fee_transactions aft
												INNER JOIN addedschools_lucknowips_testing.afm_fee_payment_mode_details afpmd ON afpmd.feeTransactionID = aft.feeTransactionID
												INNER JOIN addedschools_lucknowips_testing.afm_fee_collection afc ON afc.feeTransactionID = aft.feeTransactionID

												LEFT JOIN addedschools_lucknowips_testing.afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID
                                                LEFT JOIN addedschools_lucknowips_testing.afm_student_fee_structure asfs ON asfs.studentFeeStructureID = afcd.studentFeeStructureID 
                                                LEFT JOIN addedschools_lucknowips_testing.afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
                                                LEFT JOIN addedschools_lucknowips_testing.afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
                                                
                                                INNER JOIN addedschools_lucknowips_testing.asa_student_details asd ON asd.studentID = afc.studentID 
												INNER JOIN addedschools_lucknowips_testing.asa_students ass ON ass.studentID = afc.studentID
												INNER JOIN addedschools_lucknowips_testing.asa_parent_details apd ON apd.parentID = ass.parentID
												LEFT JOIN addedschools_lucknowips_testing.asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID
												LEFT JOIN addedschools_lucknowips_testing.asa_class_sections acs ON acs.classSectionID = ass.classSectionID
												LEFT JOIN addedschools_lucknowips_testing.asa_classes ac ON ac.classID = acs.classID
												
                                                INNER JOIN addedschools_lucknowips_testing.users u ON aft.createUserID = u.userID
												' . $QueryString . '

												GROUP BY aft.feeTransactionID
												ORDER BY afc.feeDate LIMIT ' . (int) $Start . ', ' . (int) $Limit . ';');
			$RSSearch->Execute();

			if ($RSSearch->Result->num_rows <= 0) {
				return $FeeTransactionDetails;
			}

			while ($SearchRow = $RSSearch->FetchRow()) {
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['FatherName'] = $SearchRow->fatherFirstName . ' ' . $SearchRow->fatherLastName;
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['FatherMobileNumber'] = $SearchRow->fatherMobileNumber;
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['FeeDate'] = $SearchRow->feeDate;

				$FeeTransactionDetails[$SearchRow->feeTransactionID]['TransactionAmount'] = $SearchRow->totalTransactionAmount;
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['Description'] = $SearchRow->description;

				$FeeTransactionDetails[$SearchRow->feeTransactionID]['CreateUserName'] = $SearchRow->createUserName;
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['CreateDate'] = $SearchRow->createDate;
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['PaymentModeDetails'] = array();
				$FeeTransactionDetails[$SearchRow->feeTransactionID]['StudentDetails'] = array();

				$RSSearchStudents = $DBConnObject->Prepare('SELECT sd.studentID, sd.firstName, sd.lastName 
																										FROM addedschools_lucknowips_testing.asa_student_details sd
				                                            INNER JOIN addedschools_lucknowips_testing.afm_fee_collection fc ON fc.studentID = sd.studentID
				                                            WHERE fc.feeTransactionID = :|1;');
				$RSSearchStudents->Execute($SearchRow->feeTransactionID);

				if ($RSSearchStudents->Result->num_rows > 0) {
					while ($SearchStudentRow = $RSSearchStudents->FetchRow()) {
						$FeeTransactionDetails[$SearchRow->feeTransactionID]['StudentDetails'][$SearchStudentRow->studentID]['FirstName'] = $SearchStudentRow->firstName;
						$FeeTransactionDetails[$SearchRow->feeTransactionID]['StudentDetails'][$SearchStudentRow->studentID]['LastName'] = $SearchStudentRow->lastName;
					}
				}

				$RSSearchPaymentMode = $DBConnObject->Prepare('SELECT * FROM addedschools_lucknowips_testing.afm_fee_payment_mode_details WHERE feeTransactionID = :|1;');
				$RSSearchPaymentMode->Execute($SearchRow->feeTransactionID);

				if ($RSSearchPaymentMode->Result->num_rows > 0) {
					while ($SearchPaymentModeRow = $RSSearchPaymentMode->FetchRow()) {
						$FeeTransactionDetails[$SearchRow->feeTransactionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode] = $SearchPaymentModeRow->amount;
					}
				}
			}

			return $FeeTransactionDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchFeeTransactionsNew_2021(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeTransactionDetails;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchFeeTransactionsNew_2021(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeTransactionDetails;
		}
	}

	static function MonthlyFeeDueDetails(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), &$OverAllSummary = array(), $Start = 0, $Limit = 100)
	{
		$MonthlyFeeDueDetails = array();
		$OverAllSummary = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();
			$HavingCondition = '';

			$TableForStudent = ' asa_students ';

			if (count($Filters) > 0) {
				if ($Filters['AcademicYearID'] == 1) {
					$TableForStudent = ' asa_students_19 ';
				}

				$Conditions[] = 'fs.academicYearID = ' . (int) $Filters['AcademicYearID'];

				if (!empty($Filters['ClassID'])) {
					$Conditions[] = 'ac.classID = ' . (int) $Filters['ClassID'];
				}

				if (!empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'ass.classSectionID = ' . (int)  $Filters['ClassSectionID'];
				}

				if (!empty($Filters['FeeHeadID'])) {
					$Conditions[] = 'fsd.feeHeadID = ' . (int) $Filters['FeeHeadID'];
				}

				if (!empty($Filters['TransactionDate'])) {
					$Conditions[] = 'fc.feeDate = ' . $DBConnObject->RealEscapeVariable($Filters['TransactionDate']);
				}

				if (!empty($Filters['TransactionFromDate'])) {
					$Conditions[] = 'fc.feeDate BETWEEN ' . $DBConnObject->RealEscapeVariable($Filters['TransactionFromDate']) . 'AND' . $DBConnObject->RealEscapeVariable($Filters['TransactionToDate']);
				}

				if (count($Filters['MonthList']) > 0) {
					$Conditions[] = 'fsd.academicYearMonthID IN (' . implode(', ', $Filters['MonthList']) . ')';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'Active') {
					$Conditions[] = 'ass.status = \'Active\'';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'InActive') {
					$Conditions[] = 'ass.status != \'Active\'';

					$Conditions[] = 'fsd.academicYearMonthID IN (
											SELECT academicYearMonthID
											FROM asa_academic_year_months
											WHERE feePriority <= (SELECT feePriority FROM asa_student_status_change_log WHERE studentID = ass.studentID AND isLastByAcademicYearID = 1 AND newStatus = "InActive" AND academicYearID = ' . $Filters['AcademicYearID'] . ')
										)';
				}

				if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'New') {
					$Conditions[] = 'ass.studentRegistrationID != 0';
				}

				if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'Old') {
					$Conditions[] = 'ass.studentRegistrationID = 0';
				}

				if ($Filters['ReportBy'] == 1) {
					$HavingCondition = 'HAVING (secondDiscountValue IS NULL AND firstDiscountValue IS NULL AND totalConcession IS NULL AND totalWaveOff IS NULL AND totalAmountPaid > 0) OR (CASE WHEN secondDiscountValue > 0 THEN ((totalAmountPayable - (secondDiscountValue + totalConcession + totalWaveOff)) >= 0) ELSE (CASE WHEN firstDiscountValue > 0 THEN ((totalAmountPayable - (firstDiscountValue + totalConcession + totalWaveOff)) >= 0) ELSE (totalAmountPayable - (totalConcession + totalWaveOff) >= 0) END) END)
                                        AND ( (totalAmountPaid = (CASE WHEN secondDiscountValue > 0 THEN (totalAmountPayable - (secondDiscountValue + totalConcession + totalWaveOff)) ELSE (CASE WHEN firstDiscountValue > 0 THEN (totalAmountPayable - (firstDiscountValue + totalConcession + totalWaveOff)) ELSE (totalAmountPayable - (totalConcession + totalWaveOff)) END) END))) AND totalAmountPaid > 0';
				}

				if ($Filters['ReportBy'] == 2) {
					$HavingCondition = 'HAVING totalAmountPaid > 0';
				}

				if ($Filters['ReportBy'] == 3) {
					$HavingCondition = 'HAVING (secondDiscountValue IS NULL AND firstDiscountValue IS NULL AND totalConcession IS NULL AND totalWaveOff IS NULL AND totalAmountPaid IS NULL) OR (CASE WHEN secondDiscountValue > 0 THEN ((totalAmountPayable - (secondDiscountValue + totalConcession + totalWaveOff)) > 0) ELSE (CASE WHEN firstDiscountValue > 0 THEN ((totalAmountPayable - (firstDiscountValue + totalConcession + totalWaveOff)) > 0) ELSE (totalAmountPayable - (totalConcession + totalWaveOff) > 0) END) END)
                                        AND (totalAmountPaid IS NULL OR (totalAmountPaid < (CASE WHEN secondDiscountValue > 0 THEN (totalAmountPayable - (secondDiscountValue + totalConcession + totalWaveOff)) ELSE (CASE WHEN firstDiscountValue > 0 THEN (totalAmountPayable - (firstDiscountValue + totalConcession + totalWaveOff)) ELSE (totalAmountPayable - (totalConcession + totalWaveOff)) END) END)))';
				}

				if ($Filters['ReportBy'] == 4) {
					$HavingCondition = 'HAVING secondDiscountValue > 0 OR firstDiscountValue > 0';
				}

				if ($Filters['ReportBy'] == 5) {
					$HavingCondition = 'HAVING totalConcession > 0';
				}

				if ($Filters['ReportBy'] == 6) {
					$HavingCondition = 'HAVING totalWaveOff > 0';
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = 'asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']);
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = implode(') AND (', $Conditions);

				$QueryString = ' WHERE (' . $QueryString . ')';
			}

			if ($GetTotalsOnly) {
				//self::UpdateIsLastInStudentStatus();
				$RSTotal = $DBConnObject->Prepare('SELECT COUNT(asd.studentID) AS totalRecords,
				                                    SUM(sfs.amountPayable) AS totalAmountPayable, 
													SUM(fcd.amountPaid) As totalAmountPaid,
													IFNULL(SUM(CASE WHEN afd.discountType = "Absolute" THEN afd.discountValue ELSE (sfs.amountPayable * afd.discountValue) / 100 END), 0) AS firstDiscountValue, 
													IFNULL(SUM(CASE WHEN afd1.discountType = "Absolute" THEN afd1.discountValue ELSE (sfs.amountPayable * afd1.discountValue) / 100 END), 0) AS secondDiscountValue,
													IFNULL(SUM(afd1.concessionAmount), 0) AS totalConcession,
													IFNULL(SUM(afd1.waveOffAmount), 0) AS totalWaveOff

													FROM afm_student_fee_structure sfs

													INNER JOIN afm_fee_structure_details fsd ON fsd.feeStructureDetailID = sfs.feeStructureDetailID 
													INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID 
													INNER JOIN asa_academic_years ay ON ay.academicYearID = fs.academicYearID 

													LEFT JOIN (SELECT feeCollectionDetailID, feeCollectionID, studentFeeStructureID, SUM(amountPaid) AS amountPaid FROM afm_fee_collection_details GROUP BY studentFeeStructureID) AS fcd ON fcd.studentFeeStructureID = sfs.studentFeeStructureID
													LEFT JOIN afm_fee_collection fc ON fc.feeCollectionID = fcd.feeCollectionID 

													LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = fs.feeGroupID AND afd.feeStructureDetailID = fsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
													LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = sfs.studentID AND afd1.feeStructureDetailID = fsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 

													INNER JOIN asa_student_details asd ON asd.studentID = sfs.studentID 
													LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID
													INNER JOIN ' . $TableForStudent . ' ass ON ass.studentID = asd.studentID 
													
													INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID 
													INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID
													INNER JOIN asa_classes ac ON ac.classID = acs.classID 
													INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
													' . $QueryString . '
													GROUP BY asd.studentID
													' . $HavingCondition . ';');
				$RSTotal->Execute();

				$TotalRecords = $RSTotal->Result->num_rows;
				return;
			}


			$RSSearchSummary = $DBConnObject->Prepare('SELECT fh.feeHeadID, fh.feeHead,
																SUM(sfs.amountPayable) AS totalAmountPayable, 
																SUM(fcd.amountPaid) As totalAmountPaid,
																IFNULL(SUM(CASE WHEN afd.discountType = "Absolute" THEN afd.discountValue ELSE (sfs.amountPayable * afd.discountValue) / 100 END), 0) AS firstDiscountValue, 
																IFNULL(SUM(CASE WHEN afd1.discountType = "Absolute" THEN afd1.discountValue ELSE (sfs.amountPayable * afd1.discountValue) / 100 END), 0) AS secondDiscountValue,
																IFNULL(SUM(afd1.concessionAmount), 0) AS totalConcession,
																IFNULL(SUM(afd1.waveOffAmount), 0) AS totalWaveOff
																FROM afm_student_fee_structure sfs

																INNER JOIN afm_fee_structure_details fsd ON fsd.feeStructureDetailID = sfs.feeStructureDetailID 
																INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID 
																INNER JOIN afm_fee_heads fh ON fh.feeHeadID = fsd.feeHeadID
																INNER JOIN asa_academic_years ay ON ay.academicYearID = fs.academicYearID 

																LEFT JOIN (SELECT feeCollectionDetailID, feeCollectionID, studentFeeStructureID, SUM(amountPaid) AS amountPaid FROM afm_fee_collection_details GROUP BY studentFeeStructureID) AS fcd ON fcd.studentFeeStructureID = sfs.studentFeeStructureID
																LEFT JOIN afm_fee_collection fc ON fc.feeCollectionID = fcd.feeCollectionID 

																LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = fs.feeGroupID AND afd.feeStructureDetailID = fsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
																LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = sfs.studentID AND afd1.feeStructureDetailID = fsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 

																INNER JOIN asa_student_details asd ON asd.studentID = sfs.studentID 
																LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID
																INNER JOIN ' . $TableForStudent . ' ass ON ass.studentID = asd.studentID 
																INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID 
																INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID
																INNER JOIN asa_classes ac ON ac.classID = acs.classID 
																INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
																' . $QueryString . '
																GROUP BY fh.feeHeadID
																ORDER BY fh.priority;');
			$RSSearchSummary->Execute();

			if ($RSSearchSummary->Result->num_rows > 0) {
				while ($SearchSummaryRow = $RSSearchSummary->FetchRow()) {
					$DiscountValue = 0;

					if ($SearchSummaryRow->secondDiscountValue > 0) {
						$DiscountValue = $SearchSummaryRow->secondDiscountValue;
					} else {
						$DiscountValue = $SearchSummaryRow->firstDiscountValue;
					}

					$OverAllSummary[$SearchSummaryRow->feeHeadID]['FeeHead'] = $SearchSummaryRow->feeHead;
					$OverAllSummary[$SearchSummaryRow->feeHeadID]['TotalAmount'] = $SearchSummaryRow->totalAmountPayable;

					$OverAllSummary[$SearchSummaryRow->feeHeadID]['TotalDiscount'] = $DiscountValue;
					$OverAllSummary[$SearchSummaryRow->feeHeadID]['TotalConcessionAmount'] = $SearchSummaryRow->totalConcession;
					$OverAllSummary[$SearchSummaryRow->feeHeadID]['TotalWaveOffAmount'] = $SearchSummaryRow->totalWaveOff;

					$OverAllSummary[$SearchSummaryRow->feeHeadID]['TotalPaidAmount'] = $SearchSummaryRow->totalAmountPaid;
					$OverAllSummary[$SearchSummaryRow->feeHeadID]['TotalDueAmount'] = $SearchSummaryRow->totalAmountPayable - $SearchSummaryRow->totalAmountPaid - $DiscountValue - $SearchSummaryRow->totalConcession - $SearchSummaryRow->totalWaveOff;
				}
			}

			$RSSearchStudentFeeDetails = $DBConnObject->Prepare('SELECT apd.fatherMobileNumber, asd.studentID, asd.firstName, asd.lastName, ac.classID, ac.className, asm.sectionName, CONCAT( YEAR(ay.startDate), \'-\', DATE_FORMAT(ay.endDate, \'%y\')) AS academicYearName,
																SUM(sfs.amountPayable) AS totalAmountPayable, 
																SUM(fcd.amountPaid) As totalAmountPaid,
																IFNULL(SUM(CASE WHEN afd.discountType = "Absolute" THEN afd.discountValue ELSE (sfs.amountPayable * afd.discountValue) / 100 END), 0) AS firstDiscountValue, 
																IFNULL(SUM(CASE WHEN afd1.discountType = "Absolute" THEN afd1.discountValue ELSE (sfs.amountPayable * afd1.discountValue) / 100 END), 0) AS secondDiscountValue,
																IFNULL(SUM(afd1.concessionAmount), 0) AS totalConcession,
																IFNULL(SUM(afd1.waveOffAmount), 0) AS totalWaveOff

																FROM afm_student_fee_structure sfs

																INNER JOIN afm_fee_structure_details fsd ON fsd.feeStructureDetailID = sfs.feeStructureDetailID 
																INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID 
																INNER JOIN asa_academic_years ay ON ay.academicYearID = fs.academicYearID 

																LEFT JOIN (SELECT feeCollectionDetailID, feeCollectionID, studentFeeStructureID, SUM(amountPaid) AS amountPaid FROM afm_fee_collection_details GROUP BY studentFeeStructureID) AS fcd ON fcd.studentFeeStructureID = sfs.studentFeeStructureID
																LEFT JOIN afm_fee_collection fc ON fc.feeCollectionID = fcd.feeCollectionID 

																LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = fs.feeGroupID AND afd.feeStructureDetailID = fsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
																LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = sfs.studentID AND afd1.feeStructureDetailID = fsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 

																INNER JOIN asa_student_details asd ON asd.studentID = sfs.studentID 
																LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID
																INNER JOIN ' . $TableForStudent . ' ass ON ass.studentID = asd.studentID 
																INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID
																INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID
																INNER JOIN asa_classes ac ON ac.classID = acs.classID 
																INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
																' . $QueryString . '
																GROUP BY asd.studentID
																' . $HavingCondition . '
																ORDER BY ac.priority, acs.priority, asd.firstName, asd.lastName;');
			$RSSearchStudentFeeDetails->Execute();

			if ($RSSearchStudentFeeDetails->Result->num_rows > 0) {
				// $StudentList = array();
				while ($SearchRow = $RSSearchStudentFeeDetails->FetchRow()) {
					$DiscountValue = 0;

					if ($SearchRow->secondDiscountValue > 0) {
						$DiscountValue = $SearchRow->secondDiscountValue;
					} else {
						$DiscountValue = $SearchRow->firstDiscountValue;
					}

					$MonthlyFeeDueDetails[$SearchRow->studentID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;
					$MonthlyFeeDueDetails[$SearchRow->studentID]['ClassID'] = $SearchRow->classID;
					$MonthlyFeeDueDetails[$SearchRow->studentID]['ClassName'] = $SearchRow->className;
					$MonthlyFeeDueDetails[$SearchRow->studentID]['SectionName'] = $SearchRow->sectionName;
					$MonthlyFeeDueDetails[$SearchRow->studentID]['FatherMobileNumber'] = $SearchRow->fatherMobileNumber;

					$MonthlyFeeDueDetails[$SearchRow->studentID]['TotalAmount'] = $SearchRow->totalAmountPayable;
					$MonthlyFeeDueDetails[$SearchRow->studentID]['DiscountAmount'] = $DiscountValue;
					$MonthlyFeeDueDetails[$SearchRow->studentID]['TotalConcession'] = $SearchRow->totalConcession;
					$MonthlyFeeDueDetails[$SearchRow->studentID]['TotalWaveOff'] = $SearchRow->totalWaveOff;

					$MonthlyFeeDueDetails[$SearchRow->studentID]['PaidAmount'] = $SearchRow->totalAmountPaid;
					$MonthlyFeeDueDetails[$SearchRow->studentID]['DueAmount'] = $SearchRow->totalAmountPayable - $SearchRow->totalAmountPaid - $DiscountValue - $SearchRow->totalConcession - $SearchRow->totalWaveOff;

					// $StudentList[$SearchRow->studentID] = $SearchRow->totalAmountPaid;
				}

				// echo '<pre>';
				// ksort($StudentList);
				// print_r($StudentList);exit;
			}

			return $MonthlyFeeDueDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::MonthlyFeeDueDetails(). Stack Trace: ' . $e->getTraceAsString());
			return $MonthlyFeeDueDetails;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::MonthlyFeeDueDetails(). Stack Trace: ' . $e->getTraceAsString());
			return $MonthlyFeeDueDetails;
		}
	}

	static function GetFeeDetailsByStudent($StudentID, $AcademicYearID, $MonthList = array())
	{
		$FeeDetails = array();
		try {
			$DBConnObject = new DBConnect();

			$MonthString = '';

			if (count($MonthList) > 0) {
				$MonthString = ' AND fsd.academicYearMonthID IN (' . implode(', ', $MonthList) . ')';
			}

			$RSFeeDetails = $DBConnObject->Prepare('SELECT fh.feeHead, fh.feeHeadID, aym.monthName, 
													SUM(sfs.amountPayable) AS totalAmountPayable, 
													SUM(fcd.amountPaid) As totalAmountPaid,
													SUM(CASE WHEN afd.discountType = "Absolute" THEN afd.discountValue ELSE (sfs.amountPayable * afd.discountValue) / 100 END) AS firstDiscountValue, 
													SUM(CASE WHEN afd1.discountType = "Absolute" THEN afd1.discountValue ELSE (sfs.amountPayable * afd1.discountValue) / 100 END) AS secondDiscountValue,
													SUM(afd1.concessionAmount) AS totalConcession,
													SUM(afd1.waveOffAmount) AS totalWaveOff

													FROM afm_student_fee_structure sfs

													INNER JOIN afm_fee_structure_details fsd ON fsd.feeStructureDetailID = sfs.feeStructureDetailID 
													INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID 
													INNER JOIN afm_fee_heads fh ON fh.feeHeadID = fsd.feeHeadID
													INNER JOIN asa_academic_year_months aym ON aym.academicYearMonthID = fsd.academicYearMonthID
													INNER JOIN asa_academic_years ay ON ay.academicYearID = fs.academicYearID 

													LEFT JOIN (SELECT feeCollectionDetailID, feeCollectionID, studentFeeStructureID, SUM(amountPaid) AS amountPaid FROM afm_fee_collection_details GROUP BY studentFeeStructureID) AS fcd ON fcd.studentFeeStructureID = sfs.studentFeeStructureID
													LEFT JOIN afm_fee_collection fc ON fc.feeCollectionID = fcd.feeCollectionID 

													LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = fs.feeGroupID AND afd.feeStructureDetailID = fsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
													LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = sfs.studentID AND afd1.feeStructureDetailID = fsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 

													WHERE sfs.studentID = :|1 AND fs.academicYearID = :|2 ' . $MonthString . '
													GROUP BY fh.feeHeadID, fsd.academicYearMonthID
													ORDER BY aym.feePriority, fh.priority;');
			$RSFeeDetails->Execute($StudentID, $AcademicYearID);

			if ($RSFeeDetails->Result->num_rows <= 0) {
				return $FeeDetails;
			}

			while ($SearchRow = $RSFeeDetails->FetchRow()) {
				$DiscountValue = 0;

				if ($SearchRow->secondDiscountValue > 0) {
					$DiscountValue = $SearchRow->secondDiscountValue;
				} else {
					$DiscountValue = $SearchRow->firstDiscountValue;
				}

				$FeeDetails[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHead'] = $SearchRow->feeHead;
				$FeeDetails[$SearchRow->monthName][$SearchRow->feeHeadID]['TotalAmount'] = $SearchRow->totalAmountPayable;
				$FeeDetails[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = $DiscountValue;
				$FeeDetails[$SearchRow->monthName][$SearchRow->feeHeadID]['TotalConcession'] = $SearchRow->totalConcession;
				$FeeDetails[$SearchRow->monthName][$SearchRow->feeHeadID]['TotalWaveOff'] = $SearchRow->totalWaveOff;

				$FeeDetails[$SearchRow->monthName][$SearchRow->feeHeadID]['PaidAmount'] = $SearchRow->totalAmountPaid;
				$FeeDetails[$SearchRow->monthName][$SearchRow->feeHeadID]['DueAmount'] = $SearchRow->totalAmountPayable - $SearchRow->totalAmountPaid - $DiscountValue - $SearchRow->totalConcession - $SearchRow->totalWaveOff;
			}

			return $FeeDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetFeeDetailsByStudent(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeDetails;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetFeeDetailsByStudent(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeDetails;
		}
	}

	static function UpdateFeeCollectionDetailsColumn()
	{
		try {
			$DBConnObject = new DBConnect();

			$RSDROPView = $DBConnObject->Prepare('DROP VIEW IF EXISTS afm_fee_collection_details_view;');
			$RSDROPView->Execute();

			$RSCreateView = $DBConnObject->Prepare('CREATE VIEW afm_fee_collection_details_view
													(minFeeCollectionDetailID) AS 
													SELECT MIN(feeCollectionDetailID) FROM afm_fee_collection_details GROUP BY studentFeeStructureID;');
			$RSCreateView->Execute();

			$RSUpdate1 = $DBConnObject->Prepare('UPDATE afm_fee_collection_details fcd
												INNER JOIN afm_student_fee_structure sfs ON fcd.studentFeeStructureID = sfs.studentFeeStructureID
												SET totalDue = sfs.amountPayable;');
			$RSUpdate1->Execute();

			$RSUpdate2 = $DBConnObject->Prepare('UPDATE afm_fee_collection_details fcd
												SET fcd.currentDue = fcd.totalDue
												WHERE fcd.feeCollectionDetailID  = (
												SELECT minFeeCollectionDetailID FROM afm_fee_collection_details_view WHERE minFeeCollectionDetailID = fcd.feeCollectionDetailID
												);');
			$RSUpdate2->Execute();

			//update totalDiscount in afm_fee_collection_details
			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_collection_details fcd
												INNER JOIN afm_student_fee_structure sfs ON fcd.studentFeeStructureID = sfs.studentFeeStructureID
												INNER JOIN afm_fee_discounts fd ON (sfs.studentID = fd.studentID AND sfs.feeStructureDetailID = fd.feeStructureDetailID)
												SET totalDiscount = fd.calculatedDiscountAmount
												WHERE fcd.feeCollectionDetailID  = (SELECT minFeeCollectionDetailID FROM afm_fee_collection_details_view WHERE minFeeCollectionDetailID = fcd.feeCollectionDetailID);');
			$RSUpdate->Execute();

			$RSSearch = $DBConnObject->Prepare('SELECT COUNT(*) AS totalRecords, studentFeeStructureID 
												FROM afm_fee_collection_details 
												GROUP by studentFeeStructureID
												HAVING totalRecords > 1;');
			$RSSearch->Execute();

			while ($SearchRow = $RSSearch->FetchRow()) {
				$RSSearchStructure = $DBConnObject->Prepare('SELECT * FROM afm_fee_collection_details 
															WHERE studentFeeStructureID = :|1
															ORDER BY feeCollectionDetailID ASC;');

				$RSSearchStructure->Execute($SearchRow->studentFeeStructureID);

				$CurrentDue = 0;
				while ($SearchRowForUpdate = $RSSearchStructure->FetchRow()) {
					if ($CurrentDue == 0) {
						$CurrentDue = $SearchRowForUpdate->totalDue;
					}

					$RSUpdateStructure = $DBConnObject->Prepare('UPDATE afm_fee_collection_details 
																	SET currentDue = :|1 
																	WHERE feeCollectionDetailID = :|2;');
					$RSUpdateStructure->Execute($CurrentDue, $SearchRowForUpdate->feeCollectionDetailID);

					$CurrentDue = $CurrentDue - ($SearchRowForUpdate->totalDiscount + $SearchRowForUpdate->amountPaid);
				}
			}

			$RSDROPView = $DBConnObject->Prepare('DROP VIEW IF EXISTS afm_fee_collection_details_view;');
			$RSDROPView->Execute();

			return true;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::UpdateFeeCollectionDetailsColumn(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function SearchFeeCollectionDetailsNew_2021(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $Start = 0, $Limit = 100)
	{
		$FeeCollectionDetails = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();

			$StudentTable = 'asa_students';
			$CollectionAmountCondition = '';

			if (count($Filters) > 0) {
				if ($Filters['FeeAcademicYearID'] == 1) {
					$StudentTable = 'asa_students_19';
					$Conditions[] = 'asfs.academicYearID = 1';
				} else if ($Filters['FeeAcademicYearID'] == 2) {
					$Conditions[] = 'asfs.academicYearID = 2';
				}

				if (!empty($Filters['TransactionDate'])) {
					$Conditions[] = 'afc.feeDate = ' . $DBConnObject->RealEscapeVariable($Filters['TransactionDate']);
				}

				if (!empty($Filters['TransactionFromDate'])) {
					$Conditions[] = 'afc.feeDate BETWEEN ' . $DBConnObject->RealEscapeVariable($Filters['TransactionFromDate']) . 'AND' . $DBConnObject->RealEscapeVariable($Filters['TransactionToDate']);
				}

				if (!empty($Filters['StudentName'])) {
					$Conditions[] = 'CONCAT(asd.firstName, " ", asd.lastName) LIKE ' . $DBConnObject->RealEscapeVariable('%' . $Filters['StudentName'] . '%');
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = '(asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ')';
				}

				$Conditions[] = 'afc.feeDate >= "2021-04-01"';
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = ' WHERE ' . implode(' AND ', $Conditions);
			}

			if ($GetTotalsOnly) {
				$RSSearch = $DBConnObject->Prepare('SELECT afc.feeTransactionID, afc.feeCollectionID, afc.feeDate, 
													(SELECT SUM(amountPaid) FROM addedschools_lucknowips_testing.afm_fee_collection_details WHERE feeCollectionID = afc.feeCollectionID ' . $CollectionAmountCondition . ') AS amountPaid, 
													(SELECT SUM(amount) FROM addedschools_lucknowips_testing.afm_fee_collection_other_charges WHERE feeCollectionID = afc.feeCollectionID) AS previousYearAmountPaid, 
													afc.createDate, afpmd.paymentMode, CONCAT( YEAR(ay.startDate), \'-\', DATE_FORMAT(ay.endDate, \'%y\')) AS academicYearName, u.userName AS createUserName, 
													asd.firstName, asd.lastName, ac.className, asm.sectionName, aft.description, apd.fatherMobileNumber, apd.motherMobileNumber, asd.mobileNumber 
													FROM addedschools_lucknowips_testing.afm_fee_collection afc
													INNER JOIN addedschools_lucknowips_testing.afm_fee_transactions aft ON aft.feeTransactionID = afc.feeTransactionID
													INNER JOIN addedschools_lucknowips_testing.afm_fee_payment_mode_details afpmd ON afpmd.feeTransactionID = afc.feeTransactionID
													
													LEFT JOIN addedschools_lucknowips_testing.afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID
													LEFT JOIN addedschools_lucknowips_testing.afm_student_fee_structure asfs ON (asfs.studentID = afc.studentID AND asfs.studentFeeStructureID = afcd.studentFeeStructureID) 
													
													LEFT JOIN addedschools_lucknowips_testing.afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
													LEFT JOIN addedschools_lucknowips_testing.afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
													
													INNER JOIN addedschools_lucknowips_testing.asa_academic_years ay ON ay.academicYearID = afs.academicYearID 
													
													INNER JOIN addedschools_lucknowips_testing.asa_student_details asd ON asd.studentID = afc.studentID 
													INNER JOIN addedschools_lucknowips_testing.' . $StudentTable . ' ass ON ass.studentID = asd.studentID 
													INNER JOIN addedschools_lucknowips_testing.asa_parent_details apd ON apd.parentID = ass.parentID 
													
													INNER JOIN addedschools_lucknowips_testing.asa_class_sections acs ON acs.classSectionID = ass.classSectionID
													INNER JOIN addedschools_lucknowips_testing.asa_classes ac ON ac.classID = acs.classID 
													INNER JOIN addedschools_lucknowips_testing.asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID 
													LEFT JOIN addedschools_lucknowips_testing.users u ON afc.createUserID = u.userID
													' . $QueryString . '
													GROUP BY afc.feeCollectionID
													;');
				$RSSearch->Execute();

				$TotalRecords = $RSSearch->Result->num_rows;
				return;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT asd.studentID, afc.feeTransactionID, afc.feeCollectionID, afc.feeDate, 
												(SELECT SUM(amountPaid) FROM addedschools_lucknowips_testing.afm_fee_collection_details WHERE feeCollectionID = afc.feeCollectionID ' . $CollectionAmountCondition . ') AS amountPaid, 
												(SELECT SUM(amount) FROM addedschools_lucknowips_testing.afm_fee_collection_other_charges WHERE feeCollectionID = afc.feeCollectionID) AS previousYearAmountPaid, 
												afc.createDate, afpmd.paymentMode, CONCAT( YEAR(ay.startDate), \'-\', DATE_FORMAT(ay.endDate, \'%y\')) AS academicYearName, u.userName AS createUserName, 
    											asd.firstName, asd.lastName, ac.className, asm.sectionName, aft.description, apd.fatherMobileNumber, apd.motherMobileNumber, asd.mobileNumber 
    											FROM addedschools_lucknowips_testing.afm_fee_collection afc
												INNER JOIN addedschools_lucknowips_testing.afm_fee_transactions aft ON aft.feeTransactionID = afc.feeTransactionID
												INNER JOIN addedschools_lucknowips_testing.afm_fee_payment_mode_details afpmd ON afpmd.feeTransactionID = afc.feeTransactionID
												
												LEFT JOIN addedschools_lucknowips_testing.afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID
												LEFT JOIN addedschools_lucknowips_testing.afm_student_fee_structure asfs ON (asfs.studentID = afc.studentID AND asfs.studentFeeStructureID = afcd.studentFeeStructureID) 
												
												LEFT JOIN addedschools_lucknowips_testing.afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
												LEFT JOIN addedschools_lucknowips_testing.afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
												
												INNER JOIN addedschools_lucknowips_testing.asa_academic_years ay ON ay.academicYearID = afs.academicYearID 
												
												INNER JOIN addedschools_lucknowips_testing.asa_student_details asd ON asd.studentID = afc.studentID 
												INNER JOIN addedschools_lucknowips_testing.' . $StudentTable . ' ass ON ass.studentID = asd.studentID 
												INNER JOIN addedschools_lucknowips_testing.asa_parent_details apd ON apd.parentID = ass.parentID 
												
												INNER JOIN addedschools_lucknowips_testing.asa_class_sections acs ON acs.classSectionID = ass.classSectionID
												INNER JOIN addedschools_lucknowips_testing.asa_classes ac ON ac.classID = acs.classID 
												INNER JOIN addedschools_lucknowips_testing.asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID 
												LEFT JOIN addedschools_lucknowips_testing.users u ON afc.createUserID = u.userID
    											' . $QueryString . '
												GROUP BY afc.feeCollectionID
												ORDER BY afc.feeDate LIMIT ' . (int) $Start . ', ' . (int) $Limit . ';');
			$RSSearch->Execute();

			// $StudentList = array();
			while ($SearchRow = $RSSearch->FetchRow()) {
				// if (!isset($StudentList[$SearchRow->studentID]))
				// {
				// 	$StudentList[$SearchRow->studentID] = 0;
				// }

				// $StudentList[$SearchRow->studentID] += $SearchRow->amountPaid;

				$FeeCollectionDetails['AcademicYearName'] = $SearchRow->academicYearName;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['FeeTransactionID'] = $SearchRow->feeTransactionID;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['StudentID'] = $SearchRow->studentID;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['ClassName'] = $SearchRow->className;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['SectionName'] = $SearchRow->sectionName;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['FeeDate'] = $SearchRow->feeDate;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['PreviousYearAmountPaid'] = 0;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaid'] = $SearchRow->amountPaid;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaidForAYID1'] = 0;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaidForAYID2'] = 0;

				$RSSearchAmount1 = $DBConnObject->Prepare('SELECT SUM(amountPaid) AS totalAmount 
															FROM addedschools_lucknowips_testing.afm_fee_collection_details 
															WHERE feeCollectionID = :|1 AND academicYearID = 1;');
				$RSSearchAmount1->Execute($SearchRow->feeCollectionID);

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaidForAYID1'] = $RSSearchAmount1->FetchRow()->totalAmount;

				$RSSearchAmount2 = $DBConnObject->Prepare('SELECT SUM(amountPaid) AS totalAmount 
															FROM addedschools_lucknowips_testing.afm_fee_collection_details 
															WHERE feeCollectionID = :|1 AND academicYearID = 2;');
				$RSSearchAmount2->Execute($SearchRow->feeCollectionID);

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaidForAYID2'] = $RSSearchAmount2->FetchRow()->totalAmount;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentMode'] = $SearchRow->paymentMode;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['Description'] = $SearchRow->description;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['CreateUserName'] = $SearchRow->createUserName;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['CreateDate'] = $SearchRow->createDate;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'] = array();

				$RSSearchPaymentMode = $DBConnObject->Prepare('SELECT * FROM addedschools_lucknowips_testing.afm_fee_payment_mode_details WHERE feeTransactionID = :|1;');
				$RSSearchPaymentMode->Execute($SearchRow->feeTransactionID);

				if ($RSSearchPaymentMode->Result->num_rows > 0) {
					while ($SearchPaymentModeRow = $RSSearchPaymentMode->FetchRow()) {
						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['Amount'] = $SearchPaymentModeRow->amount;
						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['ChequeReferenceNo'] = $SearchPaymentModeRow->chequeReferenceNo;

						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['PaymentMode'] = $SearchPaymentModeRow->paymentMode;
						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['ChequeStatus'] = $SearchPaymentModeRow->chequeStatus;
					}
				}
			}

			// echo '<pre>';
			// ksort($StudentList);
			// print_r($StudentList);exit;

			return $FeeCollectionDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchFeeCollectionDetailsNew_2021(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeCollectionDetails;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchFeeCollectionDetailsNew_2021(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeCollectionDetails;
		}
	}

	//pending for review
	static function SearchFeeCollectionDetailsNew(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $Start = 0, $Limit = 100)
	{
		$FeeCollectionDetails = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();

			$StudentTable = 'asa_students';
			$CollectionAmountCondition = '';

			if (count($Filters) > 0) {
				if ($Filters['FeeAcademicYearID'] == 1) {
					$StudentTable = 'asa_students_19';
					$Conditions[] = 'asfs.academicYearID = 1';
				} else if ($Filters['FeeAcademicYearID'] == 2) {
					$Conditions[] = 'asfs.academicYearID = 2';
				}

				if ($Filters['CollectionAcademicYearID'] == 1) {
					//$StudentTable = 'asa_students_19';
					$Conditions[] = 'afc.feeDate BETWEEN "2019-04-01" AND "2020-03-31"';
				} else if ($Filters['CollectionAcademicYearID'] == 2 && $_SESSION['DB'] == 'addedschools_lucknowips_testing') {
					$Conditions[] = 'afc.feeDate BETWEEN "2020-04-01" AND "2021-03-31"';
				} else if ($Filters['CollectionAcademicYearID'] == 3 && $_SESSION['DB'] == 'addedschools_lucknowips_testing') {
					$Conditions[] = 'afc.feeDate BETWEEN "2021-04-01" AND "2022-03-31"';
				} else if ($Filters['CollectionAcademicYearID'] == 2 && $_SESSION['DB'] == 'addedschools_lucknowips_testing-21-22') {
					//$Conditions[] = 'afc.feeDate BETWEEN "2021-04-01" AND "2022-03-31"';
				}

				if (!empty($Filters['TransactionDate'])) {
					$Conditions[] = 'afc.feeDate = ' . $DBConnObject->RealEscapeVariable($Filters['TransactionDate']);
				}

				if (!empty($Filters['TransactionFromDate'])) {
					$Conditions[] = 'afc.feeDate BETWEEN ' . $DBConnObject->RealEscapeVariable($Filters['TransactionFromDate']) . 'AND' . $DBConnObject->RealEscapeVariable($Filters['TransactionToDate']);
				}

				if (!empty($Filters['ClassID'])) {
					$Conditions[] = 'ac.classID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassID']);
				}

				if (!empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'ass.classSectionID =  ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']);
				}

				if (!empty($Filters['StudentID'])) {
					$Conditions[] = 'asd.studentID = ' . $DBConnObject->RealEscapeVariable($Filters['StudentID']);
				}

				$HavingPreviousYearAmount = '';
				if ($Filters['PreviousYearAmountPaid'] > 0) {
					$HavingPreviousYearAmount = ' HAVING previousYearAmountPaid > 0';
				}

				if (!empty($Filters['FeeHeadID'])) {
					$Conditions[] = 'afsd.feeHeadID IN (' . $Filters['FeeHeadID'] . ')';
					$Conditions[] = 'afcd.amountPaid > 0';
					$CollectionAmountCondition = ' AND studentFeeStructureID IN (SELECT studentFeeStructureID FROM afm_student_fee_structure WHERE feeStructureDetailID IN (SELECT feeStructureDetailID FROM afm_fee_structure_details WHERE feeHeadID IN (' . $Filters['FeeHeadID'] . ')';

					if (!empty($Filters['FeeAcademicYearID'])) {
						$CollectionAmountCondition .= ' AND academicYearID = ' . $Filters['FeeAcademicYearID'];
					}

					$CollectionAmountCondition .= '))';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'Active') {
					$Conditions[] = 'ass.status = \'Active\'';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'InActive') {
					$Conditions[] = 'ass.status != \'Active\'';
				}

				if (!empty($Filters['StudentName'])) {
					$Conditions[] = 'CONCAT(asd.firstName, " ", asd.lastName) LIKE ' . $DBConnObject->RealEscapeVariable('%' . $Filters['StudentName'] . '%');
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = '(asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ')';
				}

				if (!empty($Filters['ChequeStatus'])) {
					$Conditions[] = 'afpmd.chequeStatus = ' . $DBConnObject->RealEscapeVariable($Filters['ChequeStatus']);
				}

				if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'New') {
					$Conditions[] = 'ass.studentRegistrationID != 0';
				}

				if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'Old') {
					$Conditions[] = 'ass.studentRegistrationID = 0';
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = ' WHERE ' . implode(' AND ', $Conditions);
			}

			if ($GetTotalsOnly) {
				$RSSearch = $DBConnObject->Prepare('SELECT afc.feeTransactionID, afc.feeCollectionID, afc.feeDate, 
													(SELECT SUM(amountPaid) FROM afm_fee_collection_details WHERE feeCollectionID = afc.feeCollectionID ' . $CollectionAmountCondition . ') AS amountPaid, 
													(SELECT SUM(amount) FROM afm_fee_collection_other_charges WHERE feeCollectionID = afc.feeCollectionID) AS previousYearAmountPaid, 
													afc.createDate, afpmd.paymentMode, CONCAT( YEAR(ay.startDate), \'-\', DATE_FORMAT(ay.endDate, \'%y\')) AS academicYearName, u.userName AS createUserName, 
													asd.firstName, asd.lastName, ac.className, asm.sectionName, aft.description, apd.fatherMobileNumber, apd.motherMobileNumber, asd.mobileNumber 
													FROM afm_fee_collection afc
													INNER JOIN afm_fee_transactions aft ON aft.feeTransactionID = afc.feeTransactionID
													INNER JOIN afm_fee_payment_mode_details afpmd ON afpmd.feeTransactionID = afc.feeTransactionID
													
													LEFT JOIN afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID
													LEFT JOIN afm_student_fee_structure asfs ON (asfs.studentID = afc.studentID AND asfs.studentFeeStructureID = afcd.studentFeeStructureID) 
													
													LEFT JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
													LEFT JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
													
													INNER JOIN asa_academic_years ay ON ay.academicYearID = afs.academicYearID 
													
													INNER JOIN asa_student_details asd ON asd.studentID = afc.studentID 
													INNER JOIN ' . $StudentTable . ' ass ON ass.studentID = asd.studentID 
													INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID 
													
													INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID
													INNER JOIN asa_classes ac ON ac.classID = acs.classID 
													INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID 
													LEFT JOIN users u ON afc.createUserID = u.userID
													' . $QueryString . '
													GROUP BY afc.feeCollectionID
													' . $HavingPreviousYearAmount . ';');
				$RSSearch->Execute();

				$TotalRecords = $RSSearch->Result->num_rows;
				return;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT asd.studentID, afc.feeTransactionID, afc.feeCollectionID, afc.feeDate, 
												(SELECT SUM(amountPaid) FROM afm_fee_collection_details WHERE feeCollectionID = afc.feeCollectionID ' . $CollectionAmountCondition . ') AS amountPaid, 
												(SELECT SUM(amount) FROM afm_fee_collection_other_charges WHERE feeCollectionID = afc.feeCollectionID) AS previousYearAmountPaid, 
												afc.createDate, afpmd.paymentMode, CONCAT( YEAR(ay.startDate), \'-\', DATE_FORMAT(ay.endDate, \'%y\')) AS academicYearName, u.userName AS createUserName, 
    											asd.firstName, asd.lastName, ac.className, asm.sectionName, aft.description, apd.fatherMobileNumber, apd.motherMobileNumber, asd.mobileNumber 
    											FROM afm_fee_collection afc
												INNER JOIN afm_fee_transactions aft ON aft.feeTransactionID = afc.feeTransactionID
												INNER JOIN afm_fee_payment_mode_details afpmd ON afpmd.feeTransactionID = afc.feeTransactionID
												
												LEFT JOIN afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID
												LEFT JOIN afm_student_fee_structure asfs ON (asfs.studentID = afc.studentID AND asfs.studentFeeStructureID = afcd.studentFeeStructureID) 
												
												LEFT JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
												LEFT JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
												
												INNER JOIN asa_academic_years ay ON ay.academicYearID = afs.academicYearID 
												
												INNER JOIN asa_student_details asd ON asd.studentID = afc.studentID 
												INNER JOIN ' . $StudentTable . ' ass ON ass.studentID = asd.studentID 
												INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID 
												
												INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID
												INNER JOIN asa_classes ac ON ac.classID = acs.classID 
												INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID 
												LEFT JOIN users u ON afc.createUserID = u.userID
    											' . $QueryString . '
												GROUP BY afc.feeCollectionID
												' . $HavingPreviousYearAmount . '
    											ORDER BY afc.feeDate LIMIT ' . (int) $Start . ', ' . (int) $Limit . ';');
			$RSSearch->Execute();

			// $StudentList = array();
			while ($SearchRow = $RSSearch->FetchRow()) {
				// if (!isset($StudentList[$SearchRow->studentID]))
				// {
				// 	$StudentList[$SearchRow->studentID] = 0;
				// }

				// $StudentList[$SearchRow->studentID] += $SearchRow->amountPaid;

				$FeeCollectionDetails['AcademicYearName'] = $SearchRow->academicYearName;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['FeeTransactionID'] = $SearchRow->feeTransactionID;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['StudentID'] = $SearchRow->studentID;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['ClassName'] = $SearchRow->className;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['SectionName'] = $SearchRow->sectionName;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['FeeDate'] = $SearchRow->feeDate;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['PreviousYearAmountPaid'] = 0;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaid'] = $SearchRow->amountPaid;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaidForAYID1'] = 0;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaidForAYID2'] = 0;

				$RSSearchAmount1 = $DBConnObject->Prepare('SELECT SUM(amountPaid) AS totalAmount 
															FROM afm_fee_collection_details 
															WHERE feeCollectionID = :|1 AND academicYearID = 1;');
				$RSSearchAmount1->Execute($SearchRow->feeCollectionID);

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaidForAYID1'] = $RSSearchAmount1->FetchRow()->totalAmount;

				$RSSearchAmount2 = $DBConnObject->Prepare('SELECT SUM(amountPaid) AS totalAmount 
															FROM afm_fee_collection_details 
															WHERE feeCollectionID = :|1 AND academicYearID = 2;');
				$RSSearchAmount2->Execute($SearchRow->feeCollectionID);

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaidForAYID2'] = $RSSearchAmount2->FetchRow()->totalAmount;

				if ($HavingPreviousYearAmount) {
					$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaid'] = 0;
					$FeeCollectionDetails[$SearchRow->feeCollectionID]['PreviousYearAmountPaid'] = $SearchRow->previousYearAmountPaid;
				}

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentMode'] = $SearchRow->paymentMode;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['Description'] = $SearchRow->description;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['CreateUserName'] = $SearchRow->createUserName;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['CreateDate'] = $SearchRow->createDate;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'] = array();

				$RSSearchPaymentMode = $DBConnObject->Prepare('SELECT * FROM afm_fee_payment_mode_details WHERE feeTransactionID = :|1;');
				$RSSearchPaymentMode->Execute($SearchRow->feeTransactionID);

				if ($RSSearchPaymentMode->Result->num_rows > 0) {
					while ($SearchPaymentModeRow = $RSSearchPaymentMode->FetchRow()) {
						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['Amount'] = $SearchPaymentModeRow->amount;
						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['ChequeReferenceNo'] = $SearchPaymentModeRow->chequeReferenceNo;

						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['PaymentMode'] = $SearchPaymentModeRow->paymentMode;
						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['ChequeStatus'] = $SearchPaymentModeRow->chequeStatus;
					}
				}
			}

			// echo '<pre>';
			// ksort($StudentList);
			// print_r($StudentList);exit;

			return $FeeCollectionDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchFeeCollectionDetailsNew(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeCollectionDetails;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchFeeCollectionDetailsNew(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeCollectionDetails;
		}
	}

	static function SearchFeeCollectionDetails(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $Start = 0, $Limit = 100)
	{
		$FeeCollectionDetails = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();

			$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID ';

			if (count($Filters) > 0) {
				if (!empty($Filters['AcademicYearID'])) {
					/*if ($Filters['AcademicYearID'] > 0 && $Filters['AcademicYearID'] < 2 && !empty($Filters['Status']) && $Filters['Status'] == 'Active')  
					{
						$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = spyd.previousClassSectionID ';
					}*/

					if ($Filters['AcademicYearID'] == 2) {
						$Conditions[] = 'afc.feeDate > "2020-03-31" OR ass.studentRegistrationID != 0';
					} else {
						$Conditions[] = 'afc.feeDate <= "2020-03-31" AND ass.studentRegistrationID = 0';
					}


					//$Conditions[] = 'afs.academicYearID = '. $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
				}

				if (!empty($Filters['TransactionDate'])) {
					$Conditions[] = 'afc.feeDate = ' . $DBConnObject->RealEscapeVariable($Filters['TransactionDate']);
				}

				if (!empty($Filters['TransactionFromDate'])) {
					$Conditions[] = 'afc.feeDate BETWEEN ' . $DBConnObject->RealEscapeVariable($Filters['TransactionFromDate']) . 'AND' . $DBConnObject->RealEscapeVariable($Filters['TransactionToDate']);
				}

				if (!empty($Filters['ClassID'])) {
					$Conditions[] = 'ac.classID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassID']);
				}

				if (!empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'ass.classSectionID =  ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']) . ' OR spyd.previousClassSectionID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']);
				}

				if (!empty($Filters['StudentID'])) {
					$Conditions[] = 'asd.studentID = ' . $DBConnObject->RealEscapeVariable($Filters['StudentID']);
				}

				if (!empty($Filters['FeeHeadID'])) {
					$Conditions[] = 'afsd.feeHeadID = ' . $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']);
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'Active') {
					$Conditions[] = 'ass.status = \'Active\'';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'InActive') {
					$Conditions[] = 'ass.status != \'Active\'';
				}

				if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'New') {
					$Conditions[] = 'ass.studentRegistrationID != 0';
				}

				if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'Old') {
					$Conditions[] = 'ass.studentRegistrationID = 0';
				}

				if (!empty($Filters['StudentName'])) {
					$Conditions[] = 'CONCAT(asd.firstName, " ", asd.lastName) LIKE ' . $DBConnObject->RealEscapeVariable('%' . $Filters['StudentName'] . '%');
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = 'asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']);
				}

				if (!empty($Filters['ChequeStatus'])) {
					$Conditions[] = 'afpmd.chequeStatus = ' . $DBConnObject->RealEscapeVariable($Filters['ChequeStatus']);
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = implode(') AND (', $Conditions);

				$QueryString = ' WHERE (' . $QueryString . ')';
			}

			if ($GetTotalsOnly) {
				$RSTotal = $DBConnObject->Prepare('SELECT COUNT(Distinct afc.feeCollectionID) AS totalRecords 
													FROM afm_fee_collection afc
													INNER JOIN afm_fee_transactions aft ON aft.feeTransactionID = afc.feeTransactionID
												    INNER JOIN afm_fee_payment_mode_details afpmd ON afpmd.feeTransactionID = afc.feeTransactionID
													LEFT JOIN afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID
                                                    LEFT JOIN afm_fee_collection_other_charges afcoc ON afcoc.feeCollectionID = afc.feeCollectionID
                                                    
                                                    LEFT JOIN afm_student_fee_structure asfs ON asfs.studentFeeStructureID = afcd.studentFeeStructureID 
                                                    
                                                    LEFT JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
                                                    LEFT JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
                                                    
                                                    LEFT JOIN asa_academic_years ay ON ay.academicYearID = afs.academicYearID 
                                                    
                                                    INNER JOIN asa_student_details asd ON asd.studentID = afc.studentID 
                                                    LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID 
                                                    INNER JOIN asa_students ass ON ass.studentID = asd.studentID 
                                                    INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID 
                                                    
                                                    ' . $JoinClassSectionTable . ' 
                                                    INNER JOIN asa_classes ac ON ac.classID = acs.classID 
                                                    INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID 
                                                    INNER JOIN users u ON afc.createUserID = u.userID
													' . $QueryString . ';');
				$RSTotal->Execute();

				$TotalRecords = $RSTotal->FetchRow()->totalRecords;
				return;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT afc.feeTransactionID, afc.feeCollectionID, afc.feeDate, afc.totalAmount, afc.totalDiscount, afc.amountPaid, afc.createDate, afpmd.paymentMode, CONCAT( YEAR(ay.startDate), \'-\', DATE_FORMAT(ay.endDate, \'%y\')) AS academicYearName, u.userName AS createUserName, 
												asd.firstName, asd.lastName, ac.className, asm.sectionName, afcoc.amount, aft.description, apd.fatherMobileNumber, apd.motherMobileNumber, asd.mobileNumber 
												FROM afm_fee_collection afc
                                                INNER JOIN afm_fee_transactions aft ON aft.feeTransactionID = afc.feeTransactionID
												INNER JOIN afm_fee_payment_mode_details afpmd ON afpmd.feeTransactionID = afc.feeTransactionID
												LEFT JOIN afm_fee_collection_details afcd ON afcd.feeCollectionID = afc.feeCollectionID 
												
												LEFT JOIN afm_fee_collection_other_charges afcoc ON afcoc.feeCollectionID = afc.feeCollectionID
												
												LEFT JOIN afm_student_fee_structure asfs ON asfs.studentFeeStructureID = afcd.studentFeeStructureID 
												LEFT JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
												LEFT JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
												
												LEFT JOIN asa_academic_years ay ON ay.academicYearID = afs.academicYearID 
												
												INNER JOIN asa_student_details asd ON asd.studentID = afc.studentID 
												LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID 
												INNER JOIN asa_students ass ON ass.studentID = asd.studentID 
												INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID 
												
												' . $JoinClassSectionTable . ' 
												INNER JOIN asa_classes ac ON ac.classID = acs.classID 
												INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID 
												INNER JOIN users u ON afc.createUserID = u.userID
												' . $QueryString . '
												GROUP BY afc.feeCollectionID
												ORDER BY afc.feeDate LIMIT ' . (int) $Start . ', ' . (int) $Limit . ';');
			$RSSearch->Execute();

			if ($RSSearch->Result->num_rows <= 0) {
				return $FeeCollectionDetails;
			}

			while ($SearchRow = $RSSearch->FetchRow()) {
				$FeeCollectionDetails['AcademicYearName'] = $SearchRow->academicYearName;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['FeeTransactionID'] = $SearchRow->feeTransactionID;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['ClassName'] = $SearchRow->className;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['SectionName'] = $SearchRow->sectionName;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['FeeDate'] = $SearchRow->feeDate;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['TotalAmount'] = $SearchRow->totalAmount;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['TotalDiscount'] = $SearchRow->totalDiscount;

				// $FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaid'] = $SearchRow->amountPaid + $SearchRow->amount;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['AmountPaid'] = $SearchRow->amountPaid;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentMode'] = $SearchRow->paymentMode;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['Description'] = $SearchRow->description;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['CreateUserName'] = $SearchRow->createUserName;
				$FeeCollectionDetails[$SearchRow->feeCollectionID]['CreateDate'] = $SearchRow->createDate;

				$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'] = array();

				$RSSearchPaymentMode = $DBConnObject->Prepare('SELECT * FROM afm_fee_payment_mode_details WHERE feeTransactionID = :|1;');
				$RSSearchPaymentMode->Execute($SearchRow->feeTransactionID);

				if ($RSSearchPaymentMode->Result->num_rows > 0) {
					while ($SearchPaymentModeRow = $RSSearchPaymentMode->FetchRow()) {
						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['Amount'] = $SearchPaymentModeRow->amount;
						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['ChequeReferenceNo'] = $SearchPaymentModeRow->chequeReferenceNo;

						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['PaymentMode'] = $SearchPaymentModeRow->paymentMode;
						$FeeCollectionDetails[$SearchRow->feeCollectionID]['PaymentModeDetails'][$SearchPaymentModeRow->paymentMode]['ChequeStatus'] = $SearchPaymentModeRow->chequeStatus;
					}
				}
			}

			return $FeeCollectionDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchFeeCollectionDetails(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeCollectionDetails;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchFeeCollectionDetails(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeCollectionDetails;
		}
	}

	static function GetFeeTransactionDetails($FeeCollectionID, $AcademicYearID = 0, $FeeHeadID = 0)
	{
		$FeeCollectionDetails = array();
		try {
			$DBConnObject = new DBConnect();

			$Condition = '';

			if ($AcademicYearID > 0) {
				$Condition .= ' AND afs.academicYearID = ' . $AcademicYearID;
			}

			if ($FeeHeadID > 0) {
				$Condition .= ' AND afh.feeHeadID = ' . $FeeHeadID;
			}

			$RSFeeTransactionDetails = $DBConnObject->Prepare('SELECT afcd.*, afs.academicYearID, afh.feeHead, afh.feeHeadID, asfs.amountPayable, aaym.monthName, afd.discountType AS firstDiscountType, afd.discountValue AS firstDiscountValue, afd.concessionAmount AS firstConcessionAmount, afd.waveOffAmount AS firstWaveOffAmount, afd1.discountType AS secondDiscountType, afd1.discountValue AS secondDiscountValue, afd1.concessionAmount AS secondConcessionAmount, afd1.waveOffAmount AS secondWaveOffAmount  
															FROM afm_fee_collection_details afcd
															INNER JOIN afm_student_fee_structure asfs ON asfs.studentFeeStructureID = afcd.studentFeeStructureID
															INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID
															INNER JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID
															INNER JOIN afm_fee_heads afh ON afh.feeHeadID = afsd.feeHeadID
															INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = afsd.academicYearMonthID
															LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = afs.feeGroupID AND afd.feeStructureDetailID = afsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
							 								LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = asfs.studentID AND afd1.feeStructureDetailID = afsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 
															WHERE afcd.feeCollectionID = :|1 ' . $Condition . ';');
			$RSFeeTransactionDetails->Execute($FeeCollectionID);

			if ($RSFeeTransactionDetails->Result->num_rows <= 0) {
				return $FeeCollectionDetails;
			}

			$TotalMonthlyFeeAmount = 0;

			while ($SearchRow = $RSFeeTransactionDetails->FetchRow()) {
				$FeeAmount = 0;
				$FeeHeadDiscountAmount = 0;
				$FirstDiscountAmount = 0;
				$SecondDiscountAmount = 0;

				$FeeAmount = $SearchRow->amountPayable;

				$FeeCollectionDetails[$SearchRow->academicYearID][$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHead'] = $SearchRow->feeHead;
				$FeeCollectionDetails[$SearchRow->academicYearID][$SearchRow->monthName][$SearchRow->feeHeadID]['FeeAmount'] = $FeeAmount;

				if ($SearchRow->firstDiscountType == 'Percentage') {
					$FirstDiscountAmount = (($FeeAmount * $SearchRow->firstDiscountValue) / 100) + $SearchRow->firstConcessionAmount + $SearchRow->firstWaveOffAmount;
				} else if ($SearchRow->firstDiscountType == 'Absolute') {
					$FirstDiscountAmount = $SearchRow->firstDiscountValue + $SearchRow->firstConcessionAmount + $SearchRow->firstWaveOffAmount;
				}

				if ($SearchRow->secondDiscountType == 'Percentage') {
					$SecondDiscountAmount = (($FeeAmount * $SearchRow->secondDiscountValue) / 100) + $SearchRow->secondConcessionAmount + $SearchRow->firstWaveOffAmount;
				} else if ($SearchRow->secondDiscountType == 'Absolute') {
					$SecondDiscountAmount = $SearchRow->secondDiscountValue + $SearchRow->secondConcessionAmount + $SearchRow->secondWaveOffAmount;
				}

				if ($SecondDiscountAmount > 0) {
					$FeeHeadDiscountAmount = $SecondDiscountAmount;

					$FeeCollectionDetails[$SearchRow->academicYearID][$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = $SearchRow->secondDiscountType;
					$FeeCollectionDetails[$SearchRow->academicYearID][$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] = $SearchRow->secondDiscountValue;
					$FeeCollectionDetails[$SearchRow->academicYearID][$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = $SecondDiscountAmount;
				} else {
					$FeeHeadDiscountAmount = $FirstDiscountAmount;

					$FeeCollectionDetails[$SearchRow->academicYearID][$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = $SearchRow->firstDiscountType;
					$FeeCollectionDetails[$SearchRow->academicYearID][$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] = $SearchRow->firstDiscountValue;
					$FeeCollectionDetails[$SearchRow->academicYearID][$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = $FirstDiscountAmount;
				}

				$TotalMonthlyFeeAmount = $TotalMonthlyFeeAmount + $FeeAmount - $FeeHeadDiscountAmount;

				$RSPreviousSubmitedFeeOfMonth = $DBConnObject->Prepare('SELECT SUM(amountPaid) AS totalSubmittedFee FROM afm_fee_collection_details afcd
    														        	WHERE studentFeeStructureID = :|1 AND feeCollectionID < :|2;');
				$RSPreviousSubmitedFeeOfMonth->Execute($SearchRow->studentFeeStructureID, $FeeCollectionID);

				$TotalSubmittedFee = 0;
				if ($RSPreviousSubmitedFeeOfMonth->Result->num_rows > 0) {
					$TotalSubmittedFee = $RSPreviousSubmitedFeeOfMonth->FetchRow()->totalSubmittedFee;
					$FeeCollectionDetails[$SearchRow->academicYearID][$SearchRow->monthName][$SearchRow->feeHeadID]['FeeAmount'] = $FeeAmount - $TotalSubmittedFee;
				}

				$FeeCollectionDetails[$SearchRow->academicYearID][$SearchRow->monthName][$SearchRow->feeHeadID]['PaidAmount'] = $SearchRow->amountPaid;
				$FeeCollectionDetails[$SearchRow->academicYearID][$SearchRow->monthName][$SearchRow->feeHeadID]['RestAmount'] = $FeeAmount - $FeeHeadDiscountAmount - $SearchRow->amountPaid - $TotalSubmittedFee;
			}

			// 			echo '<pre>';
			// 			print_r($FeeCollectionDetails);exit;

			return $FeeCollectionDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetFeeTransactionDetails(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetFeeTransactionDetails(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function UpdateStudentStatusFor19()
	{
		try {
			$DBConnObject = new DBConnect();

			$RSUpdate = $DBConnObject->Prepare('UPDATE asa_students_19 st
												INNER JOIN asa_student_status_change_log stcl ON st.studentID = stcl.studentID
												SET st.status = stcl.newStatus
												WHERE stcl.isLastByAcademicYearID = 1 AND stcl.academicYearID = 1;');
			$RSUpdate->Execute();
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::UpdateStudentStatusFor19(). Stack Trace: ' . $e->getTraceAsString());
			return;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::UpdateStudentStatusFor19(). Stack Trace: ' . $e->getTraceAsString());
			return;
		}
	}

	static function UpdateIsLastInStudentStatus()
	{
		try {
			$DBConnObject = new DBConnect();

			$RSInsert = $DBConnObject->Prepare('INSERT INTO asa_student_status_change_log 
												(studentID, academicYearID, dateFrom, oldStatus, newStatus, isLast, isLastByAcademicYearID, createUserID, createDate)
												SELECT studentID, 1, admissionDate, "Active", "Active", 1, 1, 1000005, NOW()
												FROM asa_students_19 
												WHERE studentID NOT IN (SELECT studentID FROM asa_student_status_change_log WHERE academicYearID = 1);');
			$RSInsert->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE asa_student_status_change_log SET isLast = 0;');
			$RSUpdate->Execute();

			$RSSearch = $DBConnObject->Prepare('SELECT MAX(statusChangeLogID) AS lastStatusChangeLogID, studentID 
			                                    FROM asa_student_status_change_log
			                                    GROUP BY studentID;');
			$RSSearch->Execute();

			if ($RSSearch->Result->num_rows > 0) {
				while ($SearchRow = $RSSearch->FetchRow()) {
					$RSUpdate = $DBConnObject->Prepare('UPDATE asa_student_status_change_log SET isLast = 1 WHERE statusChangeLogID = :|1 AND studentID = :|2;');
					$RSUpdate->Execute($SearchRow->lastStatusChangeLogID, $SearchRow->studentID);
				}
			}

			$RSUpdate = $DBConnObject->Prepare('UPDATE asa_student_status_change_log SET isLastByAcademicYearID = 0;');
			$RSUpdate->Execute();

			$RSSearch = $DBConnObject->Prepare('SELECT MAX(statusChangeLogID) AS lastStatusChangeLogID, studentID, academicYearID
			                                    FROM asa_student_status_change_log
			                                    GROUP BY studentID, academicYearID;');
			$RSSearch->Execute();

			if ($RSSearch->Result->num_rows > 0) {
				while ($SearchRow = $RSSearch->FetchRow()) {
					$RSUpdate = $DBConnObject->Prepare('UPDATE asa_student_status_change_log SET isLastByAcademicYearID = 1 WHERE statusChangeLogID = :|1 AND studentID = :|2;');
					$RSUpdate->Execute($SearchRow->lastStatusChangeLogID, $SearchRow->studentID);
				}
			}

			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_structure_details afsd
                                                INNER JOIN afm_fee_structure afs ON afsd.feeStructureID = afs.feeStructureID
                                                SET afsd.academicYearID = afs.academicYearID;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_student_fee_structure afsd
                                                INNER JOIN afm_fee_structure_details afs ON afsd.feeStructureDetailID = afs.feeStructureDetailID
                                                SET afsd.academicYearID = afs.academicYearID;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_transactions aft
                                                INNER JOIN afm_fee_collection afc ON aft.feeTransactionID = afc.feeTransactionID
                                                SET aft.academicYearID = 1
                                                WHERE afc.feeDate <= "2020-03-31";');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_transactions aft
                                                INNER JOIN afm_fee_collection afc ON aft.feeTransactionID = afc.feeTransactionID
                                                SET aft.academicYearID = 2
                                                WHERE afc.feeDate > "2020-03-31";');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_collection_details afsd
                                                INNER JOIN afm_student_fee_structure afs ON afsd.studentFeeStructureID = afs.studentFeeStructureID
                                                SET afsd.academicYearID = afs.academicYearID;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE asa_student_status_change_log
                                                SET academicYearID = 1
                                                WHERE dateFrom <= "2020-03-31";');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE asa_student_status_change_log
                                                SET academicYearID = 2
                                                WHERE dateFrom > "2020-03-31";');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE asa_student_status_change_log
                                                SET monthName = MONTHNAME(dateFrom);');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE asa_student_status_change_log afsd
                                                INNER JOIN asa_academic_year_months afs ON afsd.monthName = afs.monthName
                                                SET afsd.feePriority = afs.feePriority;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE asa_student_status_change_log afsd
                                                INNER JOIN asa_academic_year_months afs ON afsd.monthName = afs.monthName AND afsd.feePriority = afs.feePriority
                                                SET afsd.academicYearMonthID = afs.academicYearMonthID;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts fd
                                                INNER JOIN afm_student_fee_structure afs ON fd.feeStructureDetailID = afs.feeStructureDetailID AND fd.studentID = afs.studentID
                                                SET fd.feeStructureAmount = afs.amountPayable;');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts 
                                                SET calculatedDiscountAmount = (waveOffAmount + concessionAmount + discountValue)
                                                WHERE discountType = "Absolute";');
			$RSUpdate->Execute();

			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_discounts 
                                                SET calculatedDiscountAmount = (feeStructureAmount * discountValue / 100) + waveOffAmount + concessionAmount
                                                WHERE discountType = "Percentage";');
			$RSUpdate->Execute();

			return;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::UpdateIsLastInStudentStatus(). Stack Trace: ' . $e->getTraceAsString());
			return;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::UpdateIsLastInStudentStatus(). Stack Trace: ' . $e->getTraceAsString());
			return;
		}
	}

	static function SearchFeeDefaultersVishnu(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $Start = 0, $Limit = 100)
	{
		$DefaulterList = array();

		try {

			$DBConnObject = new DBConnect();

			$Conditions = array();

			$Conditions[] = 'ass.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
			$Conditions[] = 'fs.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
			$Conditions[] = 'fsd.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
			$Conditions[] = 'sfs.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);

			$InActiveStatusCondition = '';
			$JoinClassSectionTable = '';
			$ConditionForCollectionAndDiscount = '';

			$ConditionForOnlyCollection = '';
			$ConditionForOnlyDiscount = '';

			$HavingConditions = ' ((totalFeestructureAmount + previousYearPayable) > (previousYearPaid + previousYearWaveOff + totalAmountPaid + totalDiscountAmount))';

			$JoinTableForStudent = ' asa_students ';

			$AcademicYearID = 2;

			//$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID ';

			if (count($Filters) > 0) {
				if ($Filters['AcademicYearID'] == 1) {
					$AcademicYearID = 1;
					$JoinTableForStudent = ' asa_students_19 ';
				}

				if (!empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'ass.classSectionID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']);
				}

				if (!empty($Filters['ClassID'])) {
					$Conditions[] = 'ac.classID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassID']);
				}

				if (!empty($Filters['StudentID'])) {
					$Conditions[] = 'asd.studentID = ' . $DBConnObject->RealEscapeVariable($Filters['StudentID']);
				}

				if (!empty($Filters['FeeHeadID'])) {
					$Conditions[] = 'fsd.feeHeadID = ' . $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']);
					$ConditionForCollectionAndDiscount .= ' AND fsds.feeHeadID = ' . $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']);

					$HavingConditions = ' (totalFeestructureAmount - (totalAmountPaid + totalDiscountAmount)) > 0 ';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'Active') {
					$Conditions[] = 'ass.status = "Active"';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] != 'Active') {
					$Conditions[] = 'ass.status != "Active"';

					$Conditions[] = 'fsd.academicYearMonthID IN (
					                                                SELECT academicYearMonthID
					                                                FROM asa_academic_year_months
					                                                WHERE feePriority <= (SELECT feePriority FROM asa_student_status_change_log WHERE studentID = ass.studentID AND isLastByAcademicYearID = 1 AND newStatus = "InActive" AND academicYearID = ' . $AcademicYearID . ')
				                                                )';

					$ConditionForCollectionAndDiscount .= ' AND fsds.academicYearMonthID IN (
					                                                SELECT academicYearMonthID
					                                                FROM asa_academic_year_months
					                                                WHERE feePriority <= (SELECT feePriority FROM asa_student_status_change_log WHERE studentID = ass.studentID AND isLastByAcademicYearID = 1 AND newStatus = "InActive"  AND academicYearID = ' . $AcademicYearID . ')
				                                                )';
				}

				if ($AcademicYearID == 2) {
					if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'New') {
						$Conditions[] = 'ass.studentRegistrationID != 0';
					}

					if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'Old') {
						$Conditions[] = 'ass.studentRegistrationID = 0';
					}
				}

				if (!empty($Filters['StudentName'])) {
					$Conditions[] = 'CONCAT(asd.firstName, " ", asd.lastName) LIKE ' . $DBConnObject->RealEscapeVariable('%' . $Filters['StudentName'] . '%');
				}

				if (!empty($Filters['FeePriority'])) {
					$Conditions[] = 'aaym.feePriority <= ' . $Filters['FeePriority'];

					$ConditionForCollectionAndDiscount .= ' AND aaymm.feePriority <= ' . $Filters['FeePriority'];
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = '(asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ')';
				}

				if (!empty($Filters['AcademicYearMonthID'])) {
					$Conditions[] = 'fsd.academicYearMonthID IN (' . $Filters['AcademicYearMonthID'] . ')';
					$ConditionForCollectionAndDiscount .= ' AND fsds.academicYearMonthID IN (' . $Filters['AcademicYearMonthID'] . ')';
				}

				if (!empty($Filters['DueFromDate']) && !empty($Filters['DueToDate'])) {
					$Conditions[] = 'aaym.feePriority >= 
										(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($Filters['DueFromDate']) . ', \'%M\')) 
										AND aaym.feePriority <= 
										(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($Filters['DueToDate']) . ', \'%M\'))';

					//$ConditionForOnlyCollection .= ' AND fc.feeDate BETWEEN '.$DBConnObject->RealEscapeVariable($Filters['DueFromDate']).' AND '.$DBConnObject->RealEscapeVariable($Filters['DueToDate']);
					//$ConditionForOnlyDiscount .= '  AND (DATE(fd.discountDateTime) BETWEEN '.$DBConnObject->RealEscapeVariable($Filters['DueFromDate']).' AND '.$DBConnObject->RealEscapeVariable($Filters['DueToDate']).' OR DATE(fd.transactionDateTime) BETWEEN '.$DBConnObject->RealEscapeVariable($Filters['DueFromDate']).' AND '.$DBConnObject->RealEscapeVariable($Filters['DueToDate']).')';
					$ConditionForOnlyCollection .= ' AND fc.amountPaid > 0 AND fc.feeDate <= ' . $DBConnObject->RealEscapeVariable($Filters['DueToDate']);
					$ConditionForOnlyDiscount .= '  AND (DATE(fd.discountDateTime) <= ' . $DBConnObject->RealEscapeVariable($Filters['DueToDate']) . ' OR DATE(fd.transactionDateTime) <= ' . $DBConnObject->RealEscapeVariable($Filters['DueToDate']) . ')';
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = ' WHERE ' . implode(' AND ', $Conditions);
			}

			if ($GetTotalsOnly) {
				//self::UpdateStudentStatusFor19();
				//self::UpdateIsLastInStudentStatus();

				$RSTotal = $DBConnObject->Prepare('SELECT * FROM (SELECT ass.enrollmentID, asd.firstName, asd.lastName, ac.className, asm.sectionName, sfs.studentID, apd.fatherMobileNumber, apd.motherMobileNumber, 
													SUM(sfs.amountPayable) as totalFeestructureAmount,
													IFNULL(pyfd.payableAmount, 0) AS previousYearPayable, 
													IFNULL(pyfd.paidAmount, 0) AS previousYearPaid, 
													IFNULL(pyfd.waveOffDue, 0) AS previousYearWaveOff,
													(
														SELECT IFNULL(SUM(fcd.amountPaid), 0)
														FROM afm_student_fee_structure sfss
														INNER JOIN afm_fee_collection fc ON (sfss.studentID = fc.studentID)
														INNER JOIN afm_fee_collection_details fcd ON (fc.feeCollectionID = fcd.feeCollectionID AND sfss.studentFeeStructureID = fcd.studentFeeStructureID)
														INNER JOIN afm_fee_structure_details fsds ON (sfss.feeStructureDetailID = fsds.feeStructureDetailID)
														INNER JOIN asa_academic_year_months aaymm ON aaymm.academicYearMonthID = fsds.academicYearMonthID
														WHERE sfss.studentID = ass.studentID 
														' . $ConditionForCollectionAndDiscount . '
														' . $ConditionForOnlyCollection . '
														AND sfss.academicYearID = ' . $AcademicYearID . ' AND fsds.academicYearID = ' . $AcademicYearID . '
													) As totalAmountPaid,
													(
														SELECT IFNULL(SUM(fd.calculatedDiscountAmount), 0)
														FROM afm_fee_discounts fd
														INNER JOIN afm_fee_structure_details fsds ON fsds.feeStructureDetailID = fd.feeStructureDetailID
														INNER JOIN asa_academic_year_months aaymm ON aaymm.academicYearMonthID = fsds.academicYearMonthID
														WHERE fd.studentID = ass.studentID 
														' . $ConditionForCollectionAndDiscount . '
														' . $ConditionForOnlyDiscount . '
														AND fsds.academicYearID = ' . $AcademicYearID . '
													) As totalDiscountAmount

													FROM ' . $JoinTableForStudent . ' ass
													INNER JOIN asa_student_details asd ON ass.studentID = asd.studentID 
													INNER JOIN asa_class_sections acs ON ass.classSectionID = acs.classSectionID
													INNER JOIN asa_classes ac ON ac.classID = acs.classID 
													INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
													INNER JOIN asa_parent_details apd ON ass.parentID = apd.parentID
													
													LEFT JOIN afm_previous_year_fee_details pyfd ON (ass.studentID = pyfd.studentID AND pyfd.academicYearID = ' . $Filters['AcademicYearID'] . ')

													INNER JOIN afm_student_fee_structure sfs ON (ass.studentID = sfs.studentID)
													INNER JOIN afm_fee_structure_details fsd ON (sfs.feeStructureDetailID = fsd.feeStructureDetailID)
													INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = fsd.academicYearMonthID
													INNER JOIN afm_fee_structure fs ON (fsd.feeStructureID = fs.feeStructureID)

													' . $QueryString . '
													GROUP BY ass.studentID
													) AS temp
													WHERE ' . $HavingConditions);

				$RSTotal->Execute();

				$TotalRecords = $RSTotal->Result->num_rows;
				return;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT * FROM (SELECT ass.enrollmentID, asd.firstName, asd.lastName, ac.className, asm.sectionName, sfs.studentID, apd.fatherMobileNumber, apd.motherMobileNumber, 
													SUM(sfs.amountPayable) as totalFeestructureAmount,
													IFNULL(pyfd.payableAmount, 0) AS previousYearPayable, 
                                                    IFNULL(pyfd.paidAmount, 0) AS previousYearPaid, 
                                                    IFNULL(pyfd.waveOffDue, 0) AS previousYearWaveOff,
													(
														SELECT IFNULL(SUM(fcd.amountPaid), 0)
														FROM afm_student_fee_structure sfss
														INNER JOIN afm_fee_collection fc ON (sfss.studentID = fc.studentID)
														INNER JOIN afm_fee_collection_details fcd ON (fc.feeCollectionID = fcd.feeCollectionID AND sfss.studentFeeStructureID = fcd.studentFeeStructureID)
														INNER JOIN afm_fee_structure_details fsds ON (sfss.feeStructureDetailID = fsds.feeStructureDetailID)
														INNER JOIN asa_academic_year_months aaymm ON aaymm.academicYearMonthID = fsds.academicYearMonthID
														WHERE sfss.studentID = ass.studentID 
														' . $ConditionForCollectionAndDiscount . '
														' . $ConditionForOnlyCollection . '
														AND sfss.academicYearID = ' . $AcademicYearID . ' AND fsds.academicYearID = ' . $AcademicYearID . '
													) As totalAmountPaid,
													(
														SELECT IFNULL(SUM(fd.calculatedDiscountAmount), 0)
														FROM afm_fee_discounts fd
														INNER JOIN afm_fee_structure_details fsds ON fsds.feeStructureDetailID = fd.feeStructureDetailID
														INNER JOIN asa_academic_year_months aaymm ON aaymm.academicYearMonthID = fsds.academicYearMonthID
														WHERE fd.studentID = ass.studentID 
														' . $ConditionForCollectionAndDiscount . '
														' . $ConditionForOnlyDiscount . '
														AND fsds.academicYearID = ' . $AcademicYearID . '
													) As totalDiscountAmount

													FROM ' . $JoinTableForStudent . ' ass
													INNER JOIN asa_student_details asd ON ass.studentID = asd.studentID 
													INNER JOIN asa_class_sections acs ON ass.classSectionID = acs.classSectionID
													INNER JOIN asa_classes ac ON ac.classID = acs.classID 
													INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
													INNER JOIN asa_parent_details apd ON ass.parentID = apd.parentID
													
													LEFT JOIN afm_previous_year_fee_details pyfd ON (ass.studentID = pyfd.studentID AND pyfd.academicYearID = ' . $Filters['AcademicYearID'] . ')

													INNER JOIN afm_student_fee_structure sfs ON (ass.studentID = sfs.studentID)
													INNER JOIN afm_fee_structure_details fsd ON (sfs.feeStructureDetailID = fsd.feeStructureDetailID)
													INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = fsd.academicYearMonthID
													INNER JOIN afm_fee_structure fs ON (fsd.feeStructureID = fs.feeStructureID)

													' . $QueryString . '
													GROUP BY ass.studentID
													) AS temp
													WHERE ' . $HavingConditions . '
													ORDER BY firstName, lastName ASC LIMIT ' . (int) $Start . ', ' . (int) $Limit . ';');
			$RSSearch->Execute();

			if ($RSSearch->Result->num_rows <= 0) {
				return $DefaulterList;
			}

			while ($SearchRow = $RSSearch->FetchRow()) {
				$DueMonths = 0;

				$DefaulterList[$SearchRow->studentID]['EnrollmentID'] = $SearchRow->enrollmentID;
				$DefaulterList[$SearchRow->studentID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;
				$DefaulterList[$SearchRow->studentID]['Class'] = $SearchRow->className . ' ( ' . $SearchRow->sectionName . ' )';
				$DefaulterList[$SearchRow->studentID]['FatherMobileNumber'] = $SearchRow->fatherMobileNumber;
				$DefaulterList[$SearchRow->studentID]['MotherMobileNumber'] = $SearchRow->motherMobileNumber;

				$TotalDue = 0;
				$TotalDue = ($SearchRow->totalFeestructureAmount + $SearchRow->previousYearPayable) - ($SearchRow->previousYearPaid + $SearchRow->previousYearWaveOff + $SearchRow->totalAmountPaid + $SearchRow->totalDiscountAmount);

				$DefaulterList[$SearchRow->studentID]['TotalDue'] = $TotalDue;
				$DefaulterList[$SearchRow->studentID]['DueMonths'] = $DueMonths;
			}

			return $DefaulterList;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchFeeDefaultersVishnu(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchFeeDefaultersVishnu(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		}
	}

	static function SearchFeeDefaultersVishnuYear2(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $Start = 0, $Limit = 100)
	{
		$DefaulterList = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();

			$Conditions[] = 'ass.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
			$Conditions[] = 'fs.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
			$Conditions[] = 'fsd.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
			$Conditions[] = 'sfs.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);

			//$Conditions[] = 'asd.studentID NOT IN (SELECT studentID FROM asa_student_status_change_log WHERE isLast = 1 AND newStatus = "InActive" AND academicYearID != 2)';

			$InActiveStatusCondition = '';
			$JoinClassSectionTable = '';
			$ConditionForCollectionAndDiscount = '';
			$ConditionForCollectionAndDiscountForYear1 = '';

			$FeeHeadConditionForPreviousYear1Due = '';

			$HavingConditions = '';

			$JoinTableForStudent = ' asa_students ';

			$AcademicYearID = 2;

			//$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID ';

			if (count($Filters) > 0) {
				if ($Filters['AcademicYearID'] == 1) {
					$AcademicYearID = 1;
					$JoinTableForStudent = ' asa_students_19 ';
				}

				if (!empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'ass.classSectionID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']);
				}

				if (!empty($Filters['ClassID'])) {
					$Conditions[] = 'ac.classID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassID']);
				}

				if (!empty($Filters['StudentID'])) {
					$Conditions[] = 'asd.studentID = ' . $DBConnObject->RealEscapeVariable($Filters['StudentID']);
				}

				if (!empty($Filters['FeeHeadID'])) {
					$Conditions[] = 'fsd.feeHeadID = ' . $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']);
					$ConditionForCollectionAndDiscount .= ' AND fsds.feeHeadID = ' . $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']);
					$ConditionForCollectionAndDiscountForYear1 .= ' AND fsds1.feeHeadID = ' . $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']);

					$HavingConditions .= ' AND (totalFeestructureAmount - (totalAmountPaid + totalDiscountAmount)) > 0 ';

					$FeeHeadConditionForPreviousYear1Due .= ' AND 1 = 2 '; // 1 = 2 means not search in previous due table by fee head id
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'Active') {
					$Conditions[] = 'ass.status = "Active"';
					//$Conditions[] = 'ass.studentID IN (SELECT studentID FROM asa_students WHERE status = '. $DBConnObject->RealEscapeVariable($Filters['Status']).')';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] != 'Active') {
					$Conditions[] = 'ass.status != "Active"';
					//$Conditions[] = 'asd.studentID NOT IN (SELECT studentID FROM asa_student_status_change_log WHERE isLastByAcademicYearID = 1 AND academicYearID != '.$Filters['AcademicYearID'].')';

					$Conditions[] = 'fsd.academicYearMonthID IN (
					                                                SELECT academicYearMonthID
					                                                FROM asa_academic_year_months
					                                                WHERE feePriority <= (SELECT feePriority FROM asa_student_status_change_log WHERE studentID = ass.studentID AND newStatus = "InActive" AND isLastByAcademicYearID = 1 AND academicYearID = ' . $Filters['AcademicYearID'] . ')
				                                                )';

					$ConditionForCollectionAndDiscount .= ' AND fsds.academicYearMonthID IN (
					                                                SELECT academicYearMonthID
					                                                FROM asa_academic_year_months
					                                                WHERE feePriority <= (SELECT feePriority FROM asa_student_status_change_log WHERE studentID = ass.studentID AND newStatus = "InActive" AND isLastByAcademicYearID = 1 AND academicYearID = ' . $Filters['AcademicYearID'] . ')
				                                                )';
				}

				if ($AcademicYearID == 2) {
					if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'New') {
						$Conditions[] = 'ass.studentRegistrationID != 0';
					}

					if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'Old') {
						$Conditions[] = 'ass.studentRegistrationID = 0';
					}
				}

				if (!empty($Filters['StudentName'])) {
					$Conditions[] = 'CONCAT(asd.firstName, " ", asd.lastName) LIKE ' . $DBConnObject->RealEscapeVariable('%' . $Filters['StudentName'] . '%');
				}

				if (!empty($Filters['FeePriority'])) {
					$Conditions[] = 'aaym.feePriority <= ' . $Filters['FeePriority'];

					$ConditionForCollectionAndDiscount .= ' AND aaymm.feePriority <= ' . $Filters['FeePriority'];
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = '(asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ')';
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = ' WHERE ' . implode(' AND ', $Conditions);
			}

			if ($GetTotalsOnly) {
				$RSTotal = $DBConnObject->Prepare('SELECT ass.studentID, 
													SUM(sfs.amountPayable) as totalFeestructureAmount,
													(
														SELECT IFNULL(SUM(fcd.amountPaid), 0)
														FROM afm_student_fee_structure sfss
														INNER JOIN afm_fee_collection fc ON (sfss.studentID = fc.studentID)
														INNER JOIN afm_fee_collection_details fcd ON (fc.feeCollectionID = fcd.feeCollectionID AND sfss.studentFeeStructureID = fcd.studentFeeStructureID)
														INNER JOIN afm_fee_structure_details fsds ON (sfss.feeStructureDetailID = fsds.feeStructureDetailID)
														INNER JOIN asa_academic_year_months aaymm ON aaymm.academicYearMonthID = fsds.academicYearMonthID
														WHERE sfss.studentID = ass.studentID 
														' . $ConditionForCollectionAndDiscount . '
														AND sfss.academicYearID = ' . $AcademicYearID . ' AND fsds.academicYearID = ' . $AcademicYearID . '
													) As totalAmountPaid,
													(
														SELECT IFNULL(SUM(fd.calculatedDiscountAmount), 0)
														FROM afm_fee_discounts fd
														INNER JOIN afm_fee_structure_details fsds ON fsds.feeStructureDetailID = fd.feeStructureDetailID
														INNER JOIN asa_academic_year_months aaymm ON aaymm.academicYearMonthID = fsds.academicYearMonthID
														WHERE fd.studentID = ass.studentID 
														' . $ConditionForCollectionAndDiscount . '
														AND fsds.academicYearID = ' . $AcademicYearID . '
													) As totalDiscountAmount,
													(
													    SELECT IFNULL(SUM(payableAmount - (paidAmount + waveOffDue)), 0)
                                                        FROM afm_previous_year_fee_details
                                                        WHERE studentID = ass.studentID
                                                        AND academicYearID = 1 ' . $FeeHeadConditionForPreviousYear1Due . '
													) AS previousYearPayable1,
													(
													    SELECT IFNULL(SUM(payableAmount - (paidAmount + waveOffDue)), 0)
																FROM afm_previous_year_fee_details_21_22
																WHERE studentID = ass.studentID
																AND academicYearID = 2 ' . $FeeHeadConditionForPreviousYear1Due . '
													) AS totalPreviousYearPayable,
													(
													    SELECT IFNULL(SUM(sfss1.amountPayable), 0)
                                                        FROM afm_student_fee_structure sfss1
                                                        INNER JOIN asa_students_19 ass_19 ON (sfss1.studentID = ass_19.studentID)
                                                        INNER JOIN afm_fee_structure_details fsds1 ON (sfss1.feeStructureDetailID = fsds1.feeStructureDetailID)
                                                        INNER JOIN asa_academic_year_months aaym2 ON aaym2.academicYearMonthID = fsds1.academicYearMonthID
                                                        INNER JOIN afm_fee_structure fs1 ON (fsds1.feeStructureID = fs1.feeStructureID)
                                                        WHERE sfss1.studentID = ass.studentID
                                                        AND fsds1.academicYearID = 1
                                                        AND fs1.academicYearID = 1
                                                        AND sfss1.academicYearID = 1
                                                        ' . $ConditionForCollectionAndDiscountForYear1 . '
                                                        AND fsds1.academicYearMonthID IN (
                                                    	SELECT academicYearMonthID
                                                    	FROM asa_academic_year_months
                                                    	)
													) AS totalFeestructureAmount1,
													(
														SELECT IFNULL(SUM(fcd1.amountPaid), 0)
														FROM afm_student_fee_structure sfss1
														INNER JOIN afm_fee_collection fc1 ON (sfss1.studentID = fc1.studentID)
														INNER JOIN afm_fee_collection_details fcd1 ON (fc1.feeCollectionID = fcd1.feeCollectionID AND sfss1.studentFeeStructureID = fcd1.studentFeeStructureID)
														INNER JOIN afm_fee_structure_details fsds1 ON (sfss1.feeStructureDetailID = fsds1.feeStructureDetailID)
														INNER JOIN asa_academic_year_months aaymm1 ON aaymm1.academicYearMonthID = fsds1.academicYearMonthID
														WHERE sfss1.studentID = ass.studentID 
														AND sfss1.academicYearID = 1 
														' . $ConditionForCollectionAndDiscountForYear1 . '
														AND fsds1.academicYearID = 1
													) As totalAmountPaid1,
													(
														SELECT IFNULL(SUM(fd1.calculatedDiscountAmount), 0)
														FROM afm_fee_discounts fd1
														INNER JOIN afm_fee_structure_details fsds1 ON fsds1.feeStructureDetailID = fd1.feeStructureDetailID
														INNER JOIN asa_academic_year_months aaymm1 ON aaymm1.academicYearMonthID = fsds1.academicYearMonthID
														WHERE fd1.studentID = ass.studentID 
														AND fsds1.academicYearID = 1
														' . $ConditionForCollectionAndDiscountForYear1 . '
													) As totalDiscountAmount1

													FROM ' . $JoinTableForStudent . ' ass
													INNER JOIN asa_student_details asd ON ass.studentID = asd.studentID 
													INNER JOIN asa_class_sections acs ON ass.classSectionID = acs.classSectionID
													INNER JOIN asa_classes ac ON ac.classID = acs.classID 
													INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
													INNER JOIN asa_parent_details apd ON ass.parentID = apd.parentID

													INNER JOIN afm_student_fee_structure sfs ON (ass.studentID = sfs.studentID)
													INNER JOIN afm_fee_structure_details fsd ON (sfs.feeStructureDetailID = fsd.feeStructureDetailID)
													INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = fsd.academicYearMonthID
													INNER JOIN afm_fee_structure fs ON (fsd.feeStructureID = fs.feeStructureID)

													' . $QueryString . '
													GROUP BY ass.studentID
													HAVING ((totalFeestructureAmount + totalFeestructureAmount1 + previousYearPayable1) > (totalAmountPaid + totalAmountPaid1 + totalDiscountAmount + totalDiscountAmount1) OR totalPreviousYearPayable > 0)
													' . $HavingConditions . ';');
				$RSTotal->Execute();

				$TotalRecords = $RSTotal->Result->num_rows;
				return;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT * FROM 
			                                        (
			                                        SELECT ass.enrollmentID, asd.firstName, asd.lastName, ac.className, asm.sectionName, sfs.studentID, apd.fatherMobileNumber, apd.motherMobileNumber, 
													SUM(sfs.amountPayable) as totalFeestructureAmount,
													(
														SELECT IFNULL(SUM(fcd.amountPaid), 0)
														FROM afm_student_fee_structure sfss
														INNER JOIN afm_fee_collection fc ON (sfss.studentID = fc.studentID)
														INNER JOIN afm_fee_collection_details fcd ON (fc.feeCollectionID = fcd.feeCollectionID AND sfss.studentFeeStructureID = fcd.studentFeeStructureID)
														INNER JOIN afm_fee_structure_details fsds ON (sfss.feeStructureDetailID = fsds.feeStructureDetailID)
														INNER JOIN asa_academic_year_months aaymm ON aaymm.academicYearMonthID = fsds.academicYearMonthID
														WHERE sfss.studentID = ass.studentID 
														' . $ConditionForCollectionAndDiscount . '
														AND sfss.academicYearID = ' . $AcademicYearID . ' AND fsds.academicYearID = ' . $AcademicYearID . '
													) As totalAmountPaid,
													(
														SELECT IFNULL(SUM(fd.calculatedDiscountAmount), 0)
														FROM afm_fee_discounts fd
														INNER JOIN afm_fee_structure_details fsds ON fsds.feeStructureDetailID = fd.feeStructureDetailID
														INNER JOIN asa_academic_year_months aaymm ON aaymm.academicYearMonthID = fsds.academicYearMonthID
														WHERE fd.studentID = ass.studentID 
														' . $ConditionForCollectionAndDiscount . '
														AND fsds.academicYearID = ' . $AcademicYearID . '
													) As totalDiscountAmount,
													(
													    SELECT IFNULL(SUM(payableAmount - (paidAmount + waveOffDue)), 0)
                                                        FROM afm_previous_year_fee_details
                                                        WHERE studentID = ass.studentID
                                                        AND academicYearID = 1 ' . $FeeHeadConditionForPreviousYear1Due . '
													) AS previousYearPayable1,
													(
															SELECT IFNULL(SUM(payableAmount - (paidAmount + waveOffDue)), 0)
																FROM afm_previous_year_fee_details_21_22
																WHERE studentID = ass.studentID
																AND academicYearID = 2 ' . $FeeHeadConditionForPreviousYear1Due . '
													) AS totalPreviousYearPayable,
													(
													    SELECT IFNULL(SUM(sfss1.amountPayable), 0)
                                                        FROM afm_student_fee_structure sfss1
                                                        INNER JOIN asa_students_19 ass_19 ON (sfss1.studentID = ass_19.studentID)
                                                        INNER JOIN afm_fee_structure_details fsds1 ON (sfss1.feeStructureDetailID = fsds1.feeStructureDetailID)
                                                        INNER JOIN asa_academic_year_months aaym2 ON aaym2.academicYearMonthID = fsds1.academicYearMonthID
                                                        INNER JOIN afm_fee_structure fs1 ON (fsds1.feeStructureID = fs1.feeStructureID)
                                                        WHERE sfss1.studentID = ass.studentID
                                                        AND fsds1.academicYearID = 1
                                                        AND fs1.academicYearID = 1
                                                        AND sfss1.academicYearID = 1
                                                        ' . $ConditionForCollectionAndDiscountForYear1 . '
                                                        AND fsds1.academicYearMonthID IN (
                                                    	SELECT academicYearMonthID
                                                    	FROM asa_academic_year_months
                                                    	)
													) AS totalFeestructureAmount1,
													(
														SELECT IFNULL(SUM(fcd1.amountPaid), 0)
														FROM afm_student_fee_structure sfss1
														INNER JOIN afm_fee_collection fc1 ON (sfss1.studentID = fc1.studentID)
														INNER JOIN afm_fee_collection_details fcd1 ON (fc1.feeCollectionID = fcd1.feeCollectionID AND sfss1.studentFeeStructureID = fcd1.studentFeeStructureID)
														INNER JOIN afm_fee_structure_details fsds1 ON (sfss1.feeStructureDetailID = fsds1.feeStructureDetailID)
														INNER JOIN asa_academic_year_months aaymm1 ON aaymm1.academicYearMonthID = fsds1.academicYearMonthID
														WHERE sfss1.studentID = ass.studentID 
														AND sfss1.academicYearID = 1 
														' . $ConditionForCollectionAndDiscountForYear1 . '
														AND fsds1.academicYearID = 1
													) As totalAmountPaid1,
													(
														SELECT IFNULL(SUM(fd1.calculatedDiscountAmount), 0)
														FROM afm_fee_discounts fd1
														INNER JOIN afm_fee_structure_details fsds1 ON fsds1.feeStructureDetailID = fd1.feeStructureDetailID
														INNER JOIN asa_academic_year_months aaymm1 ON aaymm1.academicYearMonthID = fsds1.academicYearMonthID
														WHERE fd1.studentID = ass.studentID 
														AND fsds1.academicYearID = 1
														' . $ConditionForCollectionAndDiscountForYear1 . '
													) As totalDiscountAmount1

													FROM ' . $JoinTableForStudent . ' ass
													INNER JOIN asa_student_details asd ON ass.studentID = asd.studentID 
													INNER JOIN asa_class_sections acs ON ass.classSectionID = acs.classSectionID
													INNER JOIN asa_classes ac ON ac.classID = acs.classID 
													INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
													INNER JOIN asa_parent_details apd ON ass.parentID = apd.parentID

													INNER JOIN afm_student_fee_structure sfs ON (ass.studentID = sfs.studentID)
													INNER JOIN afm_fee_structure_details fsd ON (sfs.feeStructureDetailID = fsd.feeStructureDetailID)
													INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = fsd.academicYearMonthID
													INNER JOIN afm_fee_structure fs ON (fsd.feeStructureID = fs.feeStructureID)

													' . $QueryString . '
													GROUP BY ass.studentID
													HAVING ((totalFeestructureAmount + totalFeestructureAmount1 + previousYearPayable1) > (totalAmountPaid + totalAmountPaid1 + totalDiscountAmount + totalDiscountAmount1) OR totalPreviousYearPayable > 0) 
													' . $HavingConditions . '
													) AS temp
													ORDER BY firstName, lastName ASC LIMIT ' . (int) $Start . ', ' . (int) $Limit . ';');
			$RSSearch->Execute();

			if ($RSSearch->Result->num_rows <= 0) {
				return $DefaulterList;
			}

			while ($SearchRow = $RSSearch->FetchRow()) {
				$DueMonths = 0;

				$DefaulterList[$SearchRow->studentID]['EnrollmentID'] = $SearchRow->enrollmentID;
				$DefaulterList[$SearchRow->studentID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;
				$DefaulterList[$SearchRow->studentID]['Class'] = $SearchRow->className . ' ( ' . $SearchRow->sectionName . ' )';
				$DefaulterList[$SearchRow->studentID]['FatherMobileNumber'] = $SearchRow->fatherMobileNumber;
				$DefaulterList[$SearchRow->studentID]['MotherMobileNumber'] = $SearchRow->motherMobileNumber;

				$TotalDue = 0;
				$TotalDue = ($SearchRow->totalFeestructureAmount + $SearchRow->totalFeestructureAmount1 + $SearchRow->previousYearPayable1 + $SearchRow->totalPreviousYearPayable) - ($SearchRow->totalAmountPaid + $SearchRow->totalAmountPaid1 + $SearchRow->totalDiscountAmount + $SearchRow->totalDiscountAmount1);

				$DefaulterList[$SearchRow->studentID]['TotalDue'] = $TotalDue;
				$DefaulterList[$SearchRow->studentID]['DueMonths'] = $DueMonths;
			}

			return $DefaulterList;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchFeeDefaultersVishnuYear2(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchFeeDefaultersVishnuYear2(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		}
	}

	static function SearchFeeDefaulters(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $FeePriority = 0, $Start = 0, $Limit = 100)
	{
		$DefaulterList = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();
			$InActiveStatusCondition = '';

			//$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID ';
			$JoinClassSectionTable = ' ';

			if (count($Filters) > 0) {
				if (!empty($Filters['AcademicYearID'])) {
					if ($Filters['AcademicYearID'] == 1) {
						$AcademicYearID = 1;
						// If current session is active then condition is applied on asa_students
						$Conditions[] = 'spyd.academicYearID IS NULL OR spyd.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);

						$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON ((spyd.academicYearID IS NOT NULL AND acs.classSectionID = spyd.previousClassSectionID) OR (spyd.academicYearID IS NULL AND acs.classSectionID = ass.classSectionID)) ';
					} else {
						$AcademicYearID = 2;
						// If current session is not active then condition is applied on student previous details
						$Conditions[] = 'spyd.academicYearID IS NULL OR ass.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);

						$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID ';
					}

					if ($Filters['AcademicYearID'] > 0 && $Filters['AcademicYearID'] < 2) {
						$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = spyd.previousClassSectionID ';
					}

					$Conditions[] = 'fs.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
				}

				if (!empty($Filters['ClassID'])) {
					$Conditions[] = 'ac.classID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassID']);
				}

				if (!empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'ass.classSectionID =  ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']) . ' OR spyd.previousClassSectionID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']);
				}

				if (!empty($Filters['StudentID'])) {
					$Conditions[] = 'asd.studentID = ' . $DBConnObject->RealEscapeVariable($Filters['StudentID']);
				}

				if (!empty($Filters['FeeHeadID'])) {
					$Conditions[] = 'fsd.feeHeadID = ' . $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']);
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'Active') {
					$Conditions[] = 'ass.status = \'Active\'';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'InActive') {
					$Conditions[] = 'ass.status != \'Active\'';

					$InActiveStatusCondition = ' AND (feePriority <= (SELECT aym.feePriority FROM asa_students ast
                                                INNER JOIN (SELECT studentID,  MAX(statusChangeLogID) AS statusChangeLogID, MAX(dateFrom) AS dateFrom, MONTHNAME(MAX(dateFrom)) AS inActiveMonthName FROM asa_student_status_change_log GROUP BY studentID) sscl ON sscl.studentID = ast.studentID
                                                INNER JOIN asa_academic_year_months aym ON aym.monthName = CONVERT(sscl.inActiveMonthName USING latin1)
                                                WHERE ast.status = \'InActive\' AND sscl.studentID = ass.studentID))';
				}

				if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'New') {
					$Conditions[] = 'ass.studentRegistrationID != 0';
				}

				if (!empty($Filters['AdmissionStatus']) && $Filters['AdmissionStatus'] == 'Old') {
					$Conditions[] = 'ass.studentRegistrationID = 0';
				}

				if (!empty($Filters['StudentName'])) {
					$Conditions[] = 'CONCAT(asd.firstName, " ", asd.lastName) LIKE ' . $DBConnObject->RealEscapeVariable('%' . $Filters['StudentName'] . '%');
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = 'asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']);
				}

				if (!empty($Filters['TillDate'])) {
					$Conditions[] = 'fc.createDate <= ' . $DBConnObject->RealEscapeVariable($Filters['TillDate']);
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = implode(') AND (', $Conditions);

				$QueryString = ' AND (' . $QueryString . ')';
			}

			if ($GetTotalsOnly) {
				$RSTotal = $DBConnObject->Prepare('SELECT ass.enrollmentID, asd.firstName, asd.lastName, ac.className, asm.sectionName, sfs.studentID, 
    												SUM(sfs.amountPayable) AS totalAmountPayable, 
    												SUM(fcd.amountPaid) As totalAmountPaid,
    												sum(CASE WHEN afd.discountType = "Absolute" THEN (afd.discountValue + afd.concessionAmount + afd.waveOffAmount) ELSE (((sfs.amountPayable * afd.discountValue) / 100) + afd.concessionAmount + afd.waveOffAmount) END) AS firstDiscountValue,
													sum(CASE WHEN afd1.discountType = "Absolute" THEN (afd1.discountValue + afd1.concessionAmount + afd1.waveOffAmount) ELSE (((sfs.amountPayable * afd1.discountValue) / 100) + afd1.concessionAmount + afd1.waveOffAmount) END) AS secondDiscountValue, 
    												(pyfd.payableAmount - pyfd.paidAmount - pyfd.waveOffDue) AS previousYearDue 
    
    												FROM afm_fee_structure_details fsd 
    												INNER JOIN afm_student_fee_structure sfs ON sfs.feeStructureDetailID = fsd.feeStructureDetailID 
    												INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID
    
    												LEFT JOIN (SELECT feeCollectionDetailID, feeCollectionID, studentFeeStructureID, SUM(amountPaid) AS amountPaid FROM afm_fee_collection_details GROUP BY studentFeeStructureID) AS fcd ON fcd.studentFeeStructureID = sfs.studentFeeStructureID
    												LEFT JOIN afm_fee_collection fc ON fc.feeCollectionID = fcd.feeCollectionID 
    												LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = fs.feeGroupID AND afd.feeStructureDetailID = fsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
    												LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = sfs.studentID AND afd1.feeStructureDetailID = fsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 
    												LEFT JOIN afm_previous_year_fee_details pyfd ON pyfd.studentID = sfs.studentID 
    
    												INNER JOIN asa_student_details asd ON asd.studentID = sfs.studentID 
    												LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID
    												INNER JOIN asa_students ass ON ass.studentID = asd.studentID 
    												INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID 
    												' . $JoinClassSectionTable . ' 
    												INNER JOIN asa_classes ac ON ac.classID = acs.classID 
    												INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID 
    
    												WHERE fsd.academicYearMonthID IN (SELECT academicYearMonthID FROM asa_academic_year_months WHERE feePriority <= :|1 ' . $InActiveStatusCondition . ')
    												' . $QueryString . '
    
    												GROUP BY sfs.studentID
    												HAVING (CASE WHEN secondDiscountValue > 0 THEN ((totalAmountPayable - secondDiscountValue) > 0) ELSE (CASE WHEN firstDiscountValue > 0 THEN ((totalAmountPayable - firstDiscountValue) > 0) ELSE (totalAmountPayable > 0) END) END)
    												    AND (totalAmountPaid IS NULL OR (totalAmountPaid < (CASE WHEN secondDiscountValue > 0 THEN (totalAmountPayable - secondDiscountValue) ELSE (CASE WHEN firstDiscountValue > 0 THEN (totalAmountPayable - firstDiscountValue) ELSE totalAmountPayable END) END)))
    												OR previousYearDue > 0 
    												ORDER BY asd.firstName;');
				$RSTotal->Execute($FeePriority);

				$TotalRecords = $RSTotal->Result->num_rows;
				return;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT * FROM (SELECT ass.enrollmentID, asd.firstName, asd.lastName, ac.className, asm.sectionName, sfs.studentID, apd.fatherMobileNumber, apd.motherMobileNumber, 
												SUM(sfs.amountPayable) AS totalAmountPayable, 
												SUM(fcd.amountPaid) As totalAmountPaid,
												sum(CASE WHEN afd.discountType = "Absolute" THEN (afd.discountValue + afd.concessionAmount + afd.waveOffAmount) ELSE (((sfs.amountPayable * afd.discountValue) / 100) + afd.concessionAmount + afd.waveOffAmount) END) AS firstDiscountValue,
												sum(CASE WHEN afd1.discountType = "Absolute" THEN (afd1.discountValue + afd1.concessionAmount + afd1.waveOffAmount) ELSE (((sfs.amountPayable * afd1.discountValue) / 100) + afd1.concessionAmount + afd1.waveOffAmount) END) AS secondDiscountValue, 
												(pyfd.payableAmount - pyfd.paidAmount - pyfd.waveOffDue) AS previousYearDue 

												FROM afm_fee_structure_details fsd 
												INNER JOIN afm_student_fee_structure sfs ON sfs.feeStructureDetailID = fsd.feeStructureDetailID 
												INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID

												LEFT JOIN (SELECT feeCollectionDetailID, feeCollectionID, studentFeeStructureID, SUM(amountPaid) AS amountPaid FROM afm_fee_collection_details GROUP BY studentFeeStructureID) AS fcd ON fcd.studentFeeStructureID = sfs.studentFeeStructureID
												LEFT JOIN afm_fee_collection fc ON fc.feeCollectionID = fcd.feeCollectionID 
												LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = fs.feeGroupID AND afd.feeStructureDetailID = fsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
												LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = sfs.studentID AND afd1.feeStructureDetailID = fsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 
												LEFT JOIN afm_previous_year_fee_details pyfd ON pyfd.studentID = sfs.studentID 

												INNER JOIN asa_student_details asd ON asd.studentID = sfs.studentID 
												LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID
												INNER JOIN asa_students ass ON ass.studentID = asd.studentID 
												INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID
												' . $JoinClassSectionTable . ' 
												INNER JOIN asa_classes ac ON ac.classID = acs.classID 
												INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID 

												WHERE fsd.academicYearMonthID IN (SELECT academicYearMonthID FROM asa_academic_year_months WHERE feePriority <= :|1 ' . $InActiveStatusCondition . ')
												' . $QueryString . '

												GROUP BY sfs.studentID
												HAVING (CASE WHEN secondDiscountValue > 0 THEN ((totalAmountPayable - secondDiscountValue) > 0) ELSE (CASE WHEN firstDiscountValue > 0 THEN ((totalAmountPayable - firstDiscountValue) > 0) ELSE (totalAmountPayable > 0) END) END)
    												    AND (totalAmountPaid IS NULL OR (totalAmountPaid < (CASE WHEN secondDiscountValue > 0 THEN (totalAmountPayable - secondDiscountValue) ELSE (CASE WHEN firstDiscountValue > 0 THEN (totalAmountPayable - firstDiscountValue) ELSE totalAmountPayable END) END)))
    												OR previousYearDue > 0) TEMP 
												ORDER BY firstName, lastName ASC LIMIT ' . (int) $Start . ', ' . (int) $Limit . ';');
			$RSSearch->Execute($FeePriority);

			if ($RSSearch->Result->num_rows <= 0) {
				return $DefaulterList;
			}

			$DiscountValue = 0;

			while ($SearchRow = $RSSearch->FetchRow()) {
				$DueMonths = 0;

				$RSDueMonthsByStudent = $DBConnObject->Prepare('SELECT afsd.academicYearMonthID,
    			                                                    SUM(asfs.amountPayable) AS totalAmountPayable, 
                                                                    SUM(afcd.amountPaid) As totalAmountPaid,
                                                                    sum(CASE WHEN afd.discountType = "Absolute" THEN (afd.discountValue + afd.concessionAmount + afd.waveOffAmount) ELSE (((asfs.amountPayable * afd.discountValue) / 100) + afd.concessionAmount + afd.waveOffAmount) END) AS firstDiscountValue,
                                                                    sum(CASE WHEN afd1.discountType = "Absolute" THEN (afd1.discountValue + afd1.concessionAmount + afd1.waveOffAmount) ELSE (((asfs.amountPayable * afd1.discountValue) / 100) + afd1.concessionAmount + afd1.waveOffAmount) END) AS secondDiscountValue, 
                                                                    (pyfd.payableAmount - pyfd.paidAmount - pyfd.waveOffDue) AS previousYearDue 
                                                                    
    															FROM afm_student_fee_structure asfs
    															INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID
    															LEFT JOIN (SELECT feeCollectionDetailID, feeCollectionID, studentFeeStructureID, SUM(amountPaid) AS amountPaid FROM afm_fee_collection_details GROUP BY studentFeeStructureID) afcd ON afcd.studentFeeStructureID = asfs.studentFeeStructureID
    															INNER JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID
    															INNER JOIN afm_fee_heads afh ON afh.feeHeadID = afsd.feeHeadID
    															INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = afsd.academicYearMonthID
    															INNER JOIN asa_students ass ON ass.studentID = asfs.studentID
    															
    															LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = afs.feeGroupID AND afd.feeStructureDetailID = afsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
    							 								LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = asfs.studentID AND afd1.feeStructureDetailID = afsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 
    							 								LEFT JOIN afm_previous_year_fee_details pyfd ON pyfd.studentID = asfs.studentID 
    															
    															WHERE asfs.studentID = :|1 AND afs.academicYearID = :|2 AND afsd.academicYearMonthID IN (SELECT academicYearMonthID FROM asa_academic_year_months WHERE feePriority <= :|3 ' . $InActiveStatusCondition . ')
    															GROUP BY afsd.academicYearMonthID
    															HAVING (CASE WHEN secondDiscountValue > 0 THEN ((totalAmountPayable - secondDiscountValue) > 0) ELSE (CASE WHEN firstDiscountValue > 0 THEN ((totalAmountPayable - firstDiscountValue) > 0) ELSE (totalAmountPayable > 0) END) END)
                                                                    AND (totalAmountPaid IS NULL OR (totalAmountPaid < (CASE WHEN secondDiscountValue > 0 THEN (totalAmountPayable - secondDiscountValue) ELSE (CASE WHEN firstDiscountValue > 0 THEN (totalAmountPayable - firstDiscountValue) ELSE totalAmountPayable END) END)))
    															ORDER BY aaym.feePriority;');

				$RSDueMonthsByStudent->Execute($SearchRow->studentID, $Filters['AcademicYearID'], $FeePriority);

				if ($RSDueMonthsByStudent->Result->num_rows > 0) {
					$DueMonths = $RSDueMonthsByStudent->Result->num_rows;
				}

				if (!empty($Filters['DueMonths']) && ($DueMonths >= $Filters['DueMonths'])) {
					$DefaulterList[$SearchRow->studentID]['EnrollmentID'] = $SearchRow->enrollmentID;
					$DefaulterList[$SearchRow->studentID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;
					$DefaulterList[$SearchRow->studentID]['Class'] = $SearchRow->className . ' ( ' . $SearchRow->sectionName . ' )';
					$DefaulterList[$SearchRow->studentID]['FatherMobileNumber'] = $SearchRow->fatherMobileNumber;
					$DefaulterList[$SearchRow->studentID]['MotherMobileNumber'] = $SearchRow->motherMobileNumber;

					if ($SearchRow->secondDiscountValue > 0) {
						$DiscountValue = $SearchRow->secondDiscountValue;
					} else {
						$DiscountValue = $SearchRow->firstDiscountValue;
					}

					// $DefaulterList[$SearchRow->studentID]['TotalAmountPayable'] = $SearchRow->totalAmountPayable - $DiscountValue;
					// $DefaulterList[$SearchRow->studentID]['TotalAmountPaid'] = $SearchRow->totalAmountPaid - $SearchRow->totalOtherAmountPaid;
					$DefaulterList[$SearchRow->studentID]['TotalDue'] = $SearchRow->totalAmountPayable - $SearchRow->totalAmountPaid - $DiscountValue + $SearchRow->previousYearDue;
					$DefaulterList[$SearchRow->studentID]['DueMonths'] = $DueMonths;
				} else {
					$DefaulterList[$SearchRow->studentID]['EnrollmentID'] = $SearchRow->enrollmentID;
					$DefaulterList[$SearchRow->studentID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;
					$DefaulterList[$SearchRow->studentID]['Class'] = $SearchRow->className . ' ( ' . $SearchRow->sectionName . ' )';
					$DefaulterList[$SearchRow->studentID]['FatherMobileNumber'] = $SearchRow->fatherMobileNumber;
					$DefaulterList[$SearchRow->studentID]['MotherMobileNumber'] = $SearchRow->motherMobileNumber;

					if ($SearchRow->secondDiscountValue > 0) {
						$DiscountValue = $SearchRow->secondDiscountValue;
					} else {
						$DiscountValue = $SearchRow->firstDiscountValue;
					}

					// $DefaulterList[$SearchRow->studentID]['TotalAmountPayable'] = $SearchRow->totalAmountPayable - $DiscountValue;
					// $DefaulterList[$SearchRow->studentID]['TotalAmountPaid'] = $SearchRow->totalAmountPaid - $SearchRow->totalOtherAmountPaid;
					$DefaulterList[$SearchRow->studentID]['TotalDue'] = $SearchRow->totalAmountPayable - $SearchRow->totalAmountPaid - $DiscountValue + $SearchRow->previousYearDue;
					$DefaulterList[$SearchRow->studentID]['DueMonths'] = $DueMonths;
				}
			}

			return $DefaulterList;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchFeeDefaulters(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchFeeDefaulters(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		}
	}

	static function GetPreviousYeatFeePaidByDate($StudentID, $EndDate)
	{
		try {
			$DBConnObject = new DBConnect();

			$RSSearch = $DBConnObject->Prepare('SELECT SUM(afcd.amountPaid) AS totalAmountPaidByDate 
	                                            FROM afm_fee_collection_details afcd 
	                                            INNER JOIN afm_fee_collection afc ON afc.feeCollectionID = afcd.feeCollectionID 
	                                            INNER JOIN afm_student_fee_structure asfs ON asfs.studentFeeStructureID = afcd.studentFeeStructureID 
	                                            INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
	                                            INNER JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID 
	                                            WHERE afc.studentID = :|1 AND afs.academicYearID = 1 AND afc.feeDate < :|2 AND afc.feeDate >= \'2020-04-01\';');

			$RSSearch->Execute($StudentID, $EndDate);

			if ($RSSearch->Result->num_rows > 0) {
				return $RSSearch->FetchRow()->totalAmountPaidByDate;
			}

			return 0;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetPreviousYeatFeePaidByDate(). Stack Trace: ' . $e->getTraceAsString());
			return 0;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetPreviousYeatFeePaidByDate(). Stack Trace: ' . $e->getTraceAsString());
			return 0;
		}
	}

	static function SearchDateWiseDefaulterPrevYear($StudentID, $AcademicYearID, $DueToDate, &$PreviousYearDueAmount = 0)
	{
		$DefaulterList = array();

		try {
			$DBConnObject = new DBConnect();

			$SearchStudentFee = $DBConnObject->Prepare('SELECT
														asfs.studentFeeStructureID, asfs.feeStructureDetailID, asfs.amountPayable, afh.feeHeadID, afh.feeHead, aaym.monthName
														FROM afm_student_fee_structure asfs
														INNER JOIN afm_fee_structure_details afsd ON asfs.feeStructureDetailID = afsd.feeStructureDetailID
														INNER JOIN asa_academic_year_months aaym ON afsd.academicYearMonthID = aaym.academicYearMonthID
														INNER JOIN afm_fee_heads afh ON afsd.feeHeadID = afh.feeHeadID
														WHERE asfs.studentID = :|1 AND asfs.academicYearID = :|2;');

			$SearchStudentFee->Execute($StudentID, $AcademicYearID);

			$TotalPaid = 0;
			$TotalDiscount = 0;
			$TotalWaveOff = 0;
			$TotalConcession = 0;

			$TotalFeeAmount = 0;
			if ($SearchStudentFee->Result->num_rows > 0) {
				while ($SearchStudentFeeRow = $SearchStudentFee->FetchRow()) {
					$TotalFeeAmount += $SearchStudentFeeRow->amountPayable;

					$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['StudentFeeStructureID'] = $SearchStudentFeeRow->studentFeeStructureID;
					$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Head'] = $SearchStudentFeeRow->feeHead;
					$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Amount'] = $SearchStudentFeeRow->amountPayable;

					$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Paid'] = 0;
					$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Discount'] = 0;
					$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['WaveOff'] = 0;
					$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Concession'] = 0;
					$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Due'] = 0;

					$Paid = 0;
					$Discount = 0;
					$WaveOff = 0;
					$Concession = 0;
					$SearchStudentFeePaid = $DBConnObject->Prepare('SELECT SUM(fcd.amountPaid) AS totalPaid
    																	FROM afm_fee_collection fc
    																	INNER JOIN afm_fee_collection_details fcd ON fc.feeCollectionID = fcd.feeCollectionID
    																	WHERE fc.studentID = :|1 
    																	AND fcd.studentFeeStructureID = :|2
    																	AND DATE(fc.feeDate) <= :|3 AND fcd.amountPaid > 0
    																	GROUP BY fcd.studentFeeStructureID;');

					$SearchStudentFeePaid->Execute($StudentID, $SearchStudentFeeRow->studentFeeStructureID, $DueToDate);

					if ($SearchStudentFeePaid->Result->num_rows > 0) {
						$Paid = $SearchStudentFeePaid->FetchRow()->totalPaid;
						$TotalPaid += $Paid;
						$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Paid'] = $Paid;
					}

					$SearchStudentFeeDiscount = $DBConnObject->Prepare('SELECT SUM(fd.calculatedDiscountAmount - (fd.waveOffAmount + fd.concessionAmount)) AS totalDiscount
    																	FROM afm_fee_discounts fd
    																	WHERE fd.studentID = :|1 
    																	AND fd.discountDateTime IS NOT NULL
    																	AND fd.feeStructureDetailID = :|2
    																	AND DATE(fd.discountDateTime) <= :|3 AND (fd.calculatedDiscountAmount - (fd.waveOffAmount + fd.concessionAmount)) > 0
    																	GROUP BY fd.feeStructureDetailID;');

					$SearchStudentFeeDiscount->Execute($StudentID, $SearchStudentFeeRow->feeStructureDetailID, $DueToDate);

					if ($SearchStudentFeeDiscount->Result->num_rows > 0) {
						$Discount = $SearchStudentFeeDiscount->FetchRow()->totalDiscount;
						$TotalDiscount += $Discount;
						$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Discount'] = $Discount;
					}

					$SearchStudentFeeWaveOff = $DBConnObject->Prepare('SELECT SUM(fd.waveOffAmount) AS totalWaveOff
    																	FROM afm_fee_discounts fd
    																	WHERE fd.studentID = :|1 
    																	AND fd.transactionDateTime IS NOT NULL
    																	AND fd.feeStructureDetailID = :|2
    																	AND DATE(fd.transactionDateTime) <= :|3 AND fd.waveOffAmount > 0
    																	GROUP BY fd.feeStructureDetailID;');

					$SearchStudentFeeWaveOff->Execute($StudentID, $SearchStudentFeeRow->feeStructureDetailID, $DueToDate);

					if ($SearchStudentFeeWaveOff->Result->num_rows > 0) {
						$WaveOff = $SearchStudentFeeWaveOff->FetchRow()->totalWaveOff;
						$TotalWaveOff += $WaveOff;
						$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['WaveOff'] = $WaveOff;
					}

					$SearchStudentFeeConcession = $DBConnObject->Prepare('SELECT SUM(fd.concessionAmount) AS totalConcession
    																	FROM afm_fee_discounts fd
    																	WHERE fd.studentID = :|1 
    																	AND fd.transactionDateTime IS NOT NULL
    																	AND fd.feeStructureDetailID = :|2
    																	AND DATE(fd.transactionDateTime) <= :|3 AND fd.concessionAmount > 0
    																	GROUP BY fd.feeStructureDetailID;');

					$SearchStudentFeeConcession->Execute($StudentID, $SearchStudentFeeRow->feeStructureDetailID, $DueToDate);

					if ($SearchStudentFeeConcession->Result->num_rows > 0) {
						$Concession = $SearchStudentFeeConcession->FetchRow()->totalConcession;
						$TotalConcession += $Concession;
						$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Concession'] = $Concession;
					}

					$DefaulterList['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Due'] = $SearchStudentFeeRow->amountPayable - ($Paid + $Discount + $WaveOff + $Concession);
				}
			}

			$DefaulterList['TotalFeeAmount'] = $TotalFeeAmount;
			// echo "$TotalFeeAmount - ($TotalPaid + $TotalDiscount + $TotalWaveOff + $TotalConcession)";
			// exit;
			$PreviousYearDueAmount = $TotalFeeAmount - ($TotalPaid + $TotalDiscount + $TotalWaveOff + $TotalConcession);

			$SearchPYFD = $DBConnObject->Prepare('SELECT (pyfd.payableAmount - pyfd.paidAmount - pyfd.waveOffDue) as previouYearDue
																					FROM afm_previous_year_fee_details pyfd
																					WHERE pyfd.studentID = :|1;');
			$SearchPYFD->Execute($StudentID);

			if ($SearchPYFD->Result->num_rows == 1) {
				$PreviousYearDueAmount += $SearchPYFD->FetchRow()->previouYearDue;
			}

			$DefaulterList['Due'] = $PreviousYearDueAmount;

			// 		echo '<pre>';
			// 		print_r($DefaulterList);exit;

			return $DefaulterList;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchDateWiseDefaulterPrevYear(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchDateWiseDefaulterPrevYear(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		}
	}

	//pending for review
	static function SearchDateWiseDefaulter(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $FeePriority = 0, $Start = 0, $Limit = 100)
	{
		$DefaulterList = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();
			$QueryStringCondition = '';

			if (count($Filters) > 0) {
				// $Conditions[] = 'asfs.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);

				if ($Filters['AcademicYearID'] == 1 && $_SESSION['DB'] == 'addedschools_lucknowips_testing' && !empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'asd.studentID IN (SELECT studentID FROM asa_students_19 WHERE classSectionID = ' . $Filters['ClassSectionID'] . ')';
				} else {
					if (!empty($Filters['ClassID'])) {
						$Conditions[] = 'ac.classID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassID']);
					}

					if (!empty($Filters['ClassSectionID'])) {
						$Conditions[] = 'acs.classSectionID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']);
					}
				}

				if (!empty($Filters['StudentID'])) {
					$Conditions[] = 'asd.studentID = ' . $DBConnObject->RealEscapeVariable($Filters['StudentID']);
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'Active') {
					$Conditions[] = 'ass.status = \'Active\'';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'InActive') {
					$Conditions[] = 'ass.status != \'Active\'';
				}

				if (!empty($Filters['StudentName'])) {
					$Conditions[] = 'CONCAT(asd.firstName, " ", asd.lastName) LIKE ' . $DBConnObject->RealEscapeVariable('%' . $Filters['StudentName'] . '%');
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = 'asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']);
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = 'WHERE ' . implode(' AND ', $Conditions);
			}

			// 			echo '<pre>';
			// 			print_r($QueryString);exit;

			$RSUpdate = $DBConnObject->Prepare('UPDATE afm_fee_structure_details afsd
                                                INNER JOIN asa_academic_year_months acym ON afsd.academicYearMonthID = acym.academicYearMonthID
                                                SET afsd.feePriority = acym.feePriority;');
			$RSUpdate->Execute();

			$RSSearch = $DBConnObject->Prepare('SELECT asd.studentID, asd.firstName, asd.lastName, ac.className, asm.sectionName, apd.fatherMobileNumber, apd.motherMobileNumber
													FROM asa_student_details asd
													INNER JOIN asa_students ass ON ass.studentID = asd.studentID 
													INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID
													INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID
													LEFT JOIN afm_student_fee_structure asfs ON ass.studentID = asfs.studentID 
													LEFT JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
													INNER JOIN asa_classes ac ON ac.classID = acs.classID
													INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
													' . $QueryString . '
													' . $QueryStringCondition . '
													GROUP BY asd.studentID
													ORDER BY asd.firstName, asd.lastName;');
			$RSSearch->Execute();

			if ($GetTotalsOnly) {
				$TotalRecords = $RSSearch->Result->num_rows;
				return;
			}

			if ($RSSearch->Result->num_rows <= 0) {
				return $DefaulterList;
			}

			while ($SearchRow = $RSSearch->FetchRow()) {

				$query_condition = '';

				if (!empty($Filters['DueFromDate'])) {
					$query_condition .= ' afsd.feePriority >= 
													(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($Filters['DueFromDate']) . ', \'%M\')) AND ';
				}

				if (!empty($Filters['DueToDate'])) {
					$query_condition .= ' afsd.feePriority <= 
													(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($Filters['DueToDate']) . ', \'%M\')) AND ';
				}

				if ($_SESSION['DB'] != 'addedschools_lucknowips_testing') {
					// $Conditions[] = ' afsd.feePriority <= (SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(CURRENT_DATE, "%M")) ';
					$query_condition .= ' afsd.feePriority <= (SELECT feePriority FROM asa_academic_year_months WHERE monthName = "March") AND ';
				}

				if (!empty($Filters['AcademicYearMonthID'])) {
					$query_condition .= ' afsd.academicYearMonthID IN (' . $Filters['AcademicYearMonthID'] . ') AND ';
				}

				if ($FeePriority > 0) {
					$query_condition .= ' afsd.feePriority <= ' . $FeePriority . ' AND ';
				}

				if (!empty($Filters['FeeHeadID'])) {
					$query_condition = ' afsd.feeHeadID = ' . $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']) . ' AND ';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'InActive') {

					if ($_SESSION['DB'] == 'addedschools_lucknowips_testing') {
						$query_condition .= ' afsd.feePriority <= (SELECT feePriority FROM asa_student_status_change_log WHERE studentID = asfs.studentID AND isLastByAcademicYearID = 1 AND newStatus = "InActive" AND academicYearID = ' . $Filters['AcademicYearID'] . ' AND dateFrom BETWEEN "2020-04-01" AND "2021-03-31") AND ';
					} else {
						$query_condition .= ' afsd.feePriority <= (SELECT feePriority FROM asa_student_status_change_log WHERE studentID = asfs.studentID AND isLastByAcademicYearID = 1 AND newStatus = "InActive" AND academicYearID = ' . $Filters['AcademicYearID'] . ' AND dateFrom BETWEEN "2021-04-01" AND "2022-03-31") AND ';
					}
				}

				if ($_SESSION['DB'] != 'addedschools_lucknowips_testing') {
					// $query_condition = ' afsd.feePriority <= (SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(CURRENT_DATE, "%M")) AND ';
					$query_condition .= ' afsd.feePriority <= (SELECT feePriority FROM asa_academic_year_months WHERE monthName = "March") AND ';
				}

				if (!empty($Filters['AcademicYearMonthID'])) {
					$query_condition .= 'afsd.academicYearMonthID IN (' . $Filters['AcademicYearMonthID'] . ') AND ';
				}

				if (!empty($Filters['FeeHeadID'])) {
					$query_condition .= 'afsd.feeHeadID = ' . $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']) . ' AND ';
				}

				$SearchStudentFee = $DBConnObject->Prepare('SELECT
														asfs.studentFeeStructureID, asfs.feeStructureDetailID, asfs.amountPayable, afh.feeHeadID, afh.feeHead, aaym.monthName
														FROM afm_student_fee_structure asfs
														INNER JOIN afm_fee_structure_details afsd ON asfs.feeStructureDetailID = afsd.feeStructureDetailID
														INNER JOIN asa_academic_year_months aaym ON afsd.academicYearMonthID = aaym.academicYearMonthID
														INNER JOIN afm_fee_heads afh ON afsd.feeHeadID = afh.feeHeadID
														WHERE ' . $query_condition . ' asfs.studentID = :|1 AND asfs.academicYearID = :|2;');

				$SearchStudentFee->Execute($SearchRow->studentID, $Filters['AcademicYearID']);

				$DefaulterList[$SearchRow->studentID]['StudentName'] = trim($SearchRow->firstName . ' ' . $SearchRow->lastName);
				$DefaulterList[$SearchRow->studentID]['Class'] = $SearchRow->className . '(' . $SearchRow->sectionName . ')';
				$DefaulterList[$SearchRow->studentID]['FatherMobileNumber'] = $SearchRow->fatherMobileNumber;
				$DefaulterList[$SearchRow->studentID]['MotherMobileNumber'] = $SearchRow->motherMobileNumber;
				$DefaulterList[$SearchRow->studentID]['TotalFeeAmount'] = 0;
				$DefaulterList[$SearchRow->studentID]['PreviousYearDueAmount'] = 0;
				$DefaulterList[$SearchRow->studentID]['FeeDetails'] = array();
				$DefaulterList[$SearchRow->studentID]['FeeHeadDetails'] = array();

				$PreviousYearDueAmount = 0;

				$PrevAcademicYearID = $Filters['AcademicYearID'] - 1;

				if ($PrevAcademicYearID > 0) {

					$PrevDueToDate = date('Y-m-d');
					if (!empty($Filters['DueToDate'])) {
						$PrevDueToDate = $Filters['DueToDate'];
					}

					self::SearchDateWiseDefaulterPrevYear($SearchRow->studentID, $PrevAcademicYearID, $PrevDueToDate, $PreviousYearDueAmount);
					// echo '$PreviousYearDueAmount: ' . $PreviousYearDueAmount;
					// exit;
					$PreviousDueAmount = 0;
					if (isset($_SESSION['DB']) && $_SESSION['DB'] == 'addedschools_lucknowips_testing-21-22') {
						$PreviousDueAmount = FeeCollection::GetPreviousDefaulterAmount($SearchRow->studentID);
						$PreviousYearDueAmount += $PreviousDueAmount;
					}
					$DefaulterList[$SearchRow->studentID]['PreviousYearDueAmount'] = $PreviousYearDueAmount;
				}

				$TotalPaid = 0;
				$TotalDiscount = 0;
				$TotalWaveOff = 0;
				$TotalConcession = 0;

				$TotalFeeAmount = 0;
				if ($SearchStudentFee->Result->num_rows > 0) {
					while ($SearchStudentFeeRow = $SearchStudentFee->FetchRow()) {
						$TotalFeeAmount += $SearchStudentFeeRow->amountPayable;

						$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['StudentFeeStructureID'] = $SearchStudentFeeRow->studentFeeStructureID;
						$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Head'] = $SearchStudentFeeRow->feeHead;
						$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Amount'] = $SearchStudentFeeRow->amountPayable;

						$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Paid'] = 0;
						$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Discount'] = 0;
						$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['WaveOff'] = 0;
						$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Concession'] = 0;
						$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Due'] = 0;

						if (!isset($DefaulterList[$SearchRow->studentID]['FeeHeadDetails'][$SearchStudentFeeRow->feeHeadID])) {
							$DefaulterList[$SearchRow->studentID]['FeeHeadDetails'][$SearchStudentFeeRow->feeHeadID] = 0;
						}

						$DueToDate = date('Y-m-d');
						if (!empty($Filters['DueToDate'])) {
							$DueToDate = $Filters['DueToDate'];
						}


						$Paid = 0;
						$Discount = 0;
						$WaveOff = 0;
						$Concession = 0;
						$SearchStudentFeePaid = $DBConnObject->Prepare('SELECT SUM(fcd.amountPaid) AS totalPaid
    																	FROM afm_fee_collection fc
    																	INNER JOIN afm_fee_collection_details fcd ON fc.feeCollectionID = fcd.feeCollectionID
    																	WHERE fc.studentID = :|1 
    																	AND fcd.studentFeeStructureID = :|2
    																	AND fc.feeDate <= :|3 AND fcd.amountPaid > 0
    																	GROUP BY fcd.studentFeeStructureID;');

						$SearchStudentFeePaid->Execute($SearchRow->studentID, $SearchStudentFeeRow->studentFeeStructureID, $DueToDate);

						if ($SearchStudentFeePaid->Result->num_rows > 0) {
							$Paid = $SearchStudentFeePaid->FetchRow()->totalPaid;
							$TotalPaid += $Paid;
							$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Paid'] = $Paid;
						}

						$SearchStudentFeeDiscount = $DBConnObject->Prepare('SELECT SUM(fd.calculatedDiscountAmount - (fd.waveOffAmount + fd.concessionAmount)) AS totalDiscount
    																	FROM afm_fee_discounts fd
    																	WHERE fd.studentID = :|1 
    																	AND fd.discountDateTime IS NOT NULL
    																	AND fd.feeStructureDetailID = :|2
    																	AND date(fd.discountDateTime) <= :|3 AND (fd.calculatedDiscountAmount - (fd.waveOffAmount + fd.concessionAmount)) > 0
    																	GROUP BY fd.feeStructureDetailID;');

						$SearchStudentFeeDiscount->Execute($SearchRow->studentID, $SearchStudentFeeRow->feeStructureDetailID, $DueToDate);

						if ($SearchStudentFeeDiscount->Result->num_rows > 0) {
							$Discount = $SearchStudentFeeDiscount->FetchRow()->totalDiscount;
							$TotalDiscount += $Discount;
							$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Discount'] = $Discount;
						}

						$SearchStudentFeeWaveOff = $DBConnObject->Prepare('SELECT SUM(fd.waveOffAmount) AS totalWaveOff
    																	FROM afm_fee_discounts fd
    																	WHERE fd.studentID = :|1 
    																	AND fd.transactionDateTime IS NOT NULL
    																	AND fd.feeStructureDetailID = :|2
    																	AND date(fd.transactionDateTime) <= :|3 AND fd.waveOffAmount > 0
    																	GROUP BY fd.feeStructureDetailID;');

						$SearchStudentFeeWaveOff->Execute($SearchRow->studentID, $SearchStudentFeeRow->feeStructureDetailID, $DueToDate);

						if ($SearchStudentFeeWaveOff->Result->num_rows > 0) {
							$WaveOff = $SearchStudentFeeWaveOff->FetchRow()->totalWaveOff;
							$TotalWaveOff += $WaveOff;
							$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['WaveOff'] = $WaveOff;
						}

						$SearchStudentFeeConcession = $DBConnObject->Prepare('SELECT SUM(fd.concessionAmount) AS totalConcession
    																	FROM afm_fee_discounts fd
    																	WHERE fd.studentID = :|1 
    																	AND fd.transactionDateTime IS NOT NULL
    																	AND fd.feeStructureDetailID = :|2
    																	AND date(fd.transactionDateTime) <= :|3 AND fd.concessionAmount > 0
    																	GROUP BY fd.feeStructureDetailID;');

						$SearchStudentFeeConcession->Execute($SearchRow->studentID, $SearchStudentFeeRow->feeStructureDetailID, $DueToDate);

						if ($SearchStudentFeeConcession->Result->num_rows > 0) {
							$Concession = $SearchStudentFeeConcession->FetchRow()->totalConcession;
							$TotalConcession += $Concession;
							$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Concession'] = $Concession;
						}


						$CurrentFeeHeadDue = $SearchStudentFeeRow->amountPayable;
						$CurrentFeeHeadDue -= $Paid;
						$CurrentFeeHeadDue -= $Discount;
						$CurrentFeeHeadDue -= $WaveOff;
						$CurrentFeeHeadDue -= $Concession;

						$DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]['Due'] = $CurrentFeeHeadDue;
						if ($CurrentFeeHeadDue <= 0) {
							unset($DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName][$SearchStudentFeeRow->feeHeadID]);

							if (empty($DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName])) {
								unset($DefaulterList[$SearchRow->studentID]['FeeDetails'][$SearchStudentFeeRow->monthName]);
							}
						} else {
							// var_dump("$CurrentFeeHeadDue = $SearchStudentFeeRow->amountPayable - ($Paid + $Discount + $WaveOff + $Concession)");
							$DefaulterList[$SearchRow->studentID]['FeeHeadDetails'][$SearchStudentFeeRow->feeHeadID] += $CurrentFeeHeadDue;
						}
					}
				}

				$DefaulterList[$SearchRow->studentID]['TotalFeeAmount'] = $TotalFeeAmount;

				$TotalDue = ($PreviousYearDueAmount + $TotalFeeAmount) - ($TotalPaid + $TotalDiscount + $TotalWaveOff + $TotalConcession);

				if ($TotalDue <= 0) {
					unset($DefaulterList[$SearchRow->studentID]);
				} else {
					$DefaulterList[$SearchRow->studentID]['Due'] = $TotalDue;
				}
			}

			// 			echo '<pre>';
			// 			print_r($DefaulterList);
			// 			exit;

			return $DefaulterList;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchDateWiseDefaulter(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchDateWiseDefaulter(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		}
	}

	static function SearchFeeDefaulters1(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $FeePriority = 0, $Start = 0, $Limit = 100)
	{
		$DefaulterList = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();
			$QueryStringCondition = '';

			$TableForStudent = ' asa_students ';

			if (count($Filters) > 0) {
				if (!empty($Filters['AcademicYearID'])) {
					if ($Filters['AcademicYearID'] == 1) {
						$TableForStudent = ' asa_students_19 ';
					}
				}

				$Conditions[] = 'asfs.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);

				if (!empty($Filters['ClassID'])) {
					$Conditions[] = 'ac.classID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassID']);
				}

				if (!empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'acs.classSectionID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']);
				}

				if (!empty($Filters['StudentID'])) {
					$Conditions[] = 'asd.studentID = ' . $DBConnObject->RealEscapeVariable($Filters['StudentID']);
				}

				if (!empty($Filters['FeeHeadID'])) {
					$Conditions[] = 'fsd.feeHeadID = ' . $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']);
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'Active') {
					$Conditions[] = 'ass.status = \'Active\'';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'InActive') {
					$Conditions[] = 'ass.status != \'Active\'';

					$Conditions[] = 'afsd.academicYearMonthID IN (
											SELECT academicYearMonthID
											FROM asa_academic_year_months
											WHERE feePriority <= (SELECT feePriority FROM asa_student_status_change_log WHERE studentID = ass.studentID AND isLastByAcademicYearID = 1 AND newStatus = "InActive" AND academicYearID = ' . $Filters['AcademicYearID'] . ')
										)';
				}

				if ($FeePriority > 0) {
					$Conditions[] = 'afsd.academicYearMonthID IN (
											SELECT academicYearMonthID
											FROM asa_academic_year_months
											WHERE feePriority <= ' . $FeePriority . ')';
				}

				if (!empty($Filters['StudentName'])) {
					$Conditions[] = 'CONCAT(asd.firstName, " ", asd.lastName) LIKE ' . $DBConnObject->RealEscapeVariable('%' . $Filters['StudentName'] . '%');
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = 'asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']);
				}

				//$Filters['DueFromDate'] = '2019-12-01';
				//$Filters['DueToDate'] = '2020-02-01';

				if (!empty($Filters['DueFromDate']) && !empty($Filters['DueToDate'])) {
					$QueryStringCondition = 'AND (
										afsd.academicYearMonthID >= 
										(SELECT academicYearMonthID FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($Filters['DueFromDate']) . ', \'%M\')) 
										AND afsd.academicYearMonthID <= 
										(SELECT academicYearMonthID FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($Filters['DueToDate']) . ', \'%M\')) 
									)';
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = 'WHERE ' . implode(' AND ', $Conditions);
			}

			$RSSearch = $DBConnObject->Prepare('SELECT asd.studentID, asd.firstName, asd.lastName, ac.className, asm.sectionName, apd.fatherMobileNumber, apd.motherMobileNumber
												FROM asa_student_details asd
												INNER JOIN asa_students ass ON ass.studentID = asd.studentID 
												INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID
												INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID
												INNER JOIN afm_student_fee_structure asfs ON ass.studentID = asfs.studentID 
												INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
												INNER JOIN asa_classes ac ON ac.classID = acs.classID
												INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
												' . $QueryString . '
												GROUP BY asd.studentID
												ORDER BY asd.firstName, asd.lastName;');
			$RSSearch->Execute();

			if ($GetTotalsOnly) {
				$TotalRecords = $RSSearch->Result->num_rows;
				return;
			}

			if ($RSSearch->Result->num_rows <= 0) {
				return $DefaulterList;
			}

			while ($SearchRow = $RSSearch->FetchRow()) {
				$DueMonths = 0;

				$RSDueMonthsByStudent = $DBConnObject->Prepare('SELECT afsd.academicYearMonthID,
    			                                                    SUM(asfs.amountPayable) AS totalAmountPayable, 
                                                                    SUM(afcd.amountPaid) As totalAmountPaid,
                                                                    sum(CASE WHEN afd.discountType = "Absolute" THEN (afd.discountValue + afd.concessionAmount + afd.waveOffAmount) ELSE (((asfs.amountPayable * afd.discountValue) / 100) + afd.concessionAmount + afd.waveOffAmount) END) AS firstDiscountValue,
                                                                    sum(CASE WHEN afd1.discountType = "Absolute" THEN (afd1.discountValue + afd1.concessionAmount + afd1.waveOffAmount) ELSE (((asfs.amountPayable * afd1.discountValue) / 100) + afd1.concessionAmount + afd1.waveOffAmount) END) AS secondDiscountValue, 
                                                                    (pyfd.payableAmount - pyfd.paidAmount - pyfd.waveOffDue) AS previousYearDue 
                                                                    
    															FROM afm_student_fee_structure asfs
    															INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID
    															LEFT JOIN (SELECT feeCollectionDetailID, feeCollectionID, studentFeeStructureID, SUM(amountPaid) AS amountPaid FROM afm_fee_collection_details GROUP BY studentFeeStructureID) afcd ON afcd.studentFeeStructureID = asfs.studentFeeStructureID
																LEFT JOIN afm_fee_collection afc ON afc.feeCollectionID = afcd.feeCollectionID 
    															INNER JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID
    															INNER JOIN afm_fee_heads afh ON afh.feeHeadID = afsd.feeHeadID
    															INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = afsd.academicYearMonthID
    															INNER JOIN asa_students ass ON ass.studentID = asfs.studentID
    															
    															LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = afs.feeGroupID AND afd.feeStructureDetailID = afsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
    							 								LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = asfs.studentID AND afd1.feeStructureDetailID = afsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 
    							 								
    							 								LEFT JOIN afm_previous_year_fee_details pyfd ON pyfd.studentID = asfs.studentID 
    															
    															WHERE asfs.studentID = :|1 AND afs.academicYearID = :|2
    															' . $QueryStringCondition . '
    															GROUP BY afsd.academicYearMonthID
    															HAVING (CASE WHEN secondDiscountValue > 0 THEN ((totalAmountPayable - secondDiscountValue) > 0) ELSE (CASE WHEN firstDiscountValue > 0 THEN ((totalAmountPayable - firstDiscountValue) > 0) ELSE (totalAmountPayable > 0) END) END)
                                                                    AND (totalAmountPaid IS NULL OR (totalAmountPaid < (CASE WHEN secondDiscountValue > 0 THEN (totalAmountPayable - secondDiscountValue) ELSE (CASE WHEN firstDiscountValue > 0 THEN (totalAmountPayable - firstDiscountValue) ELSE totalAmountPayable END) END)))
    															ORDER BY aaym.feePriority;');

				$RSDueMonthsByStudent->Execute($SearchRow->studentID, $Filters['AcademicYearID']);

				if ($RSDueMonthsByStudent->Result->num_rows > 0) {
					$DueMonths = $RSDueMonthsByStudent->Result->num_rows;
				}

				$RSSearchTotalAmountPayble = $DBConnObject->Prepare('SELECT SUM(sfs.amountPayable) AS totalAmountPayble
																	FROM afm_student_fee_structure sfs 
																	INNER JOIN afm_fee_structure_details fsd ON fsd.feeStructureDetailID = sfs.feeStructureDetailID
																	INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID
																	WHERE sfs.studentID = :|1 AND fs.academicYearID = :|2
																	GROUP by studentID;');

				$RSSearchTotalAmountPayble->Execute($SearchRow->studentID, $Filters['AcademicYearID']);

				$TotalAmountPayble = 0;
				if ($RSSearchTotalAmountPayble->Result->num_rows > 0) {
					$TotalAmountPayble = $RSSearchTotalAmountPayble->FetchRow()->totalAmountPayble;
				}
				//var_dump($TotalAmountPayble);
				$RSSearchTotalAmountPaid = $DBConnObject->Prepare('SELECT SUM(fcd.amountPaid) AS totalAmountPaid
																	FROM afm_fee_collection_details fcd
																	INNER JOIN afm_fee_collection fc ON fc.feeCollectionID = fcd.feeCollectionID
																	INNER JOIN afm_student_fee_structure sfs ON sfs.studentFeeStructureID = fcd.studentFeeStructureID
																	INNER JOIN afm_fee_structure_details fsd ON fsd.feeStructureDetailID = sfs.feeStructureDetailID
																	INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID
																	WHERE sfs.studentID = :|1 AND fs.academicYearID = :|2 AND date(fc.createDate) BETWEEN 
																		(SELECT startDate FROM asa_academic_years WHERE academicYearID = :|3) 
																		AND (SELECT endDate FROM asa_academic_years WHERE academicYearID = :|4)
																	GROUP by fc.studentID;');

				$RSSearchTotalAmountPaid->Execute($SearchRow->studentID, $Filters['AcademicYearID'], $Filters['AcademicYearID'], $Filters['AcademicYearID']);

				$TotalAmountPaid = 0;
				if ($RSSearchTotalAmountPaid->Result->num_rows > 0) {
					$TotalAmountPaid = $RSSearchTotalAmountPaid->FetchRow()->totalAmountPaid;
				}

				$RSSearchDiscount = $DBConnObject->Prepare('SELECT fd.*, fsd.feeAmount, (pyfd.payableAmount - pyfd.paidAmount - pyfd.waveOffDue) AS previousYearDue
															FROM afm_fee_discounts fd
															INNER JOIN afm_fee_structure_details fsd ON fsd.feeStructureDetailID = fd.feeStructureDetailID
															INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID
															LEFT JOIN afm_previous_year_fee_details pyfd ON pyfd.studentID = fd.studentID
															WHERE fs.academicYearID = :|1 AND fd.studentID = :|2 AND (DATE(fd.discountDateTime) BETWEEN ' . $DBConnObject->RealEscapeVariable($Filters['DueFromDate']) . ' AND ' . $DBConnObject->RealEscapeVariable($Filters['DueToDate']) . ' OR DATE(fd.transactionDateTime) BETWEEN ' . $DBConnObject->RealEscapeVariable($Filters['DueFromDate']) . ' AND ' . $DBConnObject->RealEscapeVariable($Filters['DueToDate']) . ');');

				$RSSearchDiscount->Execute($Filters['AcademicYearID'], $SearchRow->studentID);

				$PreviousYearDue = 0;
				$TotalDiscount = 0;
				$TotalDue = 0;

				if ($RSSearchDiscount->Result->num_rows > 0) {
					while ($DiscountRow = $RSSearchDiscount->FetchRow()) {
						$PreviousYearDue = $DiscountRow->previousYearDue;
						if ($DiscountRow->discountType == 'Percentage') {
							$TotalDiscount += ($DiscountRow->feeAmount * $DiscountRow->discountValue) / 100;
							$TotalDiscount += $DiscountRow->concessionAmount;
							$TotalDiscount += $DiscountRow->waveOffAmount;
						} else if ($DiscountRow->discountType == 'Absolute') {
							$TotalDiscount += $DiscountRow->discountValue;
							$TotalDiscount += $DiscountRow->concessionAmount;
							$TotalDiscount += $DiscountRow->waveOffAmount;
						}
					}
				}

				//var_dump($TotalDiscount);exit;

				$TotalDue = $TotalAmountPayble - $TotalAmountPaid - $TotalDiscount + $PreviousYearDue;

				# get previous year total due amount and add into $TotalDue varaiable
				$TotalDue += 1;

				if ($TotalDue > 0) {
					$DefaulterList[$SearchRow->studentID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;
					$DefaulterList[$SearchRow->studentID]['Class'] = $SearchRow->className . ' ( ' . $SearchRow->sectionName . ' )';
					$DefaulterList[$SearchRow->studentID]['FatherMobileNumber'] = $SearchRow->fatherMobileNumber;
					$DefaulterList[$SearchRow->studentID]['MotherMobileNumber'] = $SearchRow->motherMobileNumber;

					// $DefaulterList[$SearchRow->studentID]['TotalAmountPayable'] = $SearchRow->totalAmountPayable - $DiscountValue;
					// $DefaulterList[$SearchRow->studentID]['TotalAmountPaid'] = $SearchRow->totalAmountPaid - $SearchRow->totalOtherAmountPaid;
					$DefaulterList[$SearchRow->studentID]['TotalDue'] = $TotalDue;
					$DefaulterList[$SearchRow->studentID]['DueMonths'] = $DueMonths;
				}
			}

			return $DefaulterList;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchFeeDefaulters1(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchFeeDefaulters1(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		}
	}

	static function SearchFeeDefaulters2(&$TotalRecords = 0, $GetTotalsOnly = false, $Filters = array(), $FeePriority = 0, $Start = 0, $Limit = 100)
	{
		$DefaulterList = array();

		try {
			$DBConnObject = new DBConnect();

			$Conditions = array();
			$InActiveStatusCondition = '';
			$QueryStringCondition = '';

			$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID ';

			if (count($Filters) > 0) {
				if (!empty($Filters['AcademicYearID'])) {
					if (!empty($Filters['Status']) && $Filters['Status'] == 'Active') {
						$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = spyd.previousClassSectionID ';
					}

					$Conditions[] = 'spyd.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']);
					//$Conditions[] = '(ass.academicYearID =  ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']) . ' OR spyd.academicYearID = ' . $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']) . ')';
					//$Conditions[] = 'fc.createDate BETWEEN (SELECT startDate FROM asa_academic_years WHERE academicYearID = '. $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']) .') 															AND (SELECT endDate FROM asa_academic_years WHERE academicYearID = '. $DBConnObject->RealEscapeVariable($Filters['AcademicYearID']) .')';
				}

				if (!empty($Filters['ClassID'])) {
					$Conditions[] = 'ac.classID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassID']);
				}

				if (!empty($Filters['ClassSectionID'])) {
					$Conditions[] = 'spyd.previousClassSectionID = ' . $DBConnObject->RealEscapeVariable($Filters['ClassSectionID']);
				}

				if (!empty($Filters['StudentID'])) {
					$Conditions[] = 'asd.studentID = ' . $DBConnObject->RealEscapeVariable($Filters['StudentID']);
				}

				if (!empty($Filters['FeeHeadID'])) {
					$Conditions[] = 'fsd.feeHeadID = ' . $DBConnObject->RealEscapeVariable($Filters['FeeHeadID']);
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'Active') {
					$Conditions[] = 'ass.status = \'Active\'';
				}

				if (!empty($Filters['Status']) && $Filters['Status'] == 'InActive') {
					$Conditions[] = 'ass.status != \'Active\'';

					$InActiveStatusCondition = ' AND (feePriority <= (SELECT aym.feePriority FROM asa_students ast
                                                INNER JOIN (SELECT studentID,  MAX(statusChangeLogID) AS statusChangeLogID, MAX(dateFrom) AS dateFrom, MONTHNAME(MAX(dateFrom)) AS inActiveMonthName FROM asa_student_status_change_log GROUP BY studentID) sscl ON sscl.studentID = ast.studentID
                                                INNER JOIN asa_academic_year_months aym ON aym.monthName = sscl.inActiveMonthName
                                                WHERE ast.status = \'InActive\' AND sscl.studentID = ass.studentID))';
				}

				if (!empty($Filters['StudentName'])) {
					$Conditions[] = 'CONCAT(asd.firstName, " ", asd.lastName) LIKE ' . $DBConnObject->RealEscapeVariable('%' . $Filters['StudentName'] . '%');
				}

				if (!empty($Filters['MobileNumber'])) {
					$Conditions[] = 'asd.mobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.fatherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']) . ' OR apd.motherMobileNumber = ' . $DBConnObject->RealEscapeVariable($Filters['MobileNumber']);
				}

				//$Filters['DueFromDate'] = '2019-12-01';
				//$Filters['DueToDate'] = '2020-02-01';

				if (!empty($Filters['DueFromDate']) && !empty($Filters['DueToDate'])) {
					$QueryStringCondition = 'AND (
										afsd.academicYearMonthID >= 
										(SELECT academicYearMonthID FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($Filters['DueFromDate']) . ', \'%M\')) 
										AND afsd.academicYearMonthID <= 
										(SELECT academicYearMonthID FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($Filters['DueToDate']) . ', \'%M\')) 
									)';
				}
			}

			$QueryString = '';

			if (count($Conditions) > 0) {
				$QueryString = 'WHERE ' . implode('AND ', $Conditions);
			}

			$RSSearch = $DBConnObject->Prepare('SELECT asd.studentID, asd.firstName, asd.lastName, ac.className, asm.sectionName, apd.fatherMobileNumber, apd.motherMobileNumber
												FROM asa_student_details asd
												LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asd.studentID
												INNER JOIN asa_students ass ON ass.studentID = asd.studentID 
												INNER JOIN asa_parent_details apd ON apd.parentID = ass.parentID
												' . $JoinClassSectionTable . ' 

												
												INNER JOIN asa_classes ac ON ac.classID = acs.classID
												INNER JOIN asa_section_master asm ON asm.sectionMasterID = acs.sectionMasterID
												
												' . $QueryString . '
												GROUP BY asd.studentID
												ORDER BY asd.firstName;');
			$RSSearch->Execute();

			if ($GetTotalsOnly) {
				$TotalRecords = $RSSearch->Result->num_rows;
				return;
			}

			if ($RSSearch->Result->num_rows <= 0) {
				return $DefaulterList;
			}

			while ($SearchRow = $RSSearch->FetchRow()) {
				$DueMonths = 0;

				$RSDueMonthsByStudent = $DBConnObject->Prepare('SELECT afsd.academicYearMonthID,
    			                                                    SUM(asfs.amountPayable) AS totalAmountPayable, 
                                                                    SUM(afcd.amountPaid) As totalAmountPaid,
                                                                    sum(CASE WHEN afd.discountType = "Absolute" THEN (afd.discountValue + afd.concessionAmount + afd.waveOffAmount) ELSE (((asfs.amountPayable * afd.discountValue) / 100) + afd.concessionAmount + afd.waveOffAmount) END) AS firstDiscountValue,
                                                                    sum(CASE WHEN afd1.discountType = "Absolute" THEN (afd1.discountValue + afd1.concessionAmount + afd1.waveOffAmount) ELSE (((asfs.amountPayable * afd1.discountValue) / 100) + afd1.concessionAmount + afd1.waveOffAmount) END) AS secondDiscountValue, 
                                                                    (pyfd.payableAmount - pyfd.paidAmount - pyfd.waveOffDue) AS previousYearDue 
                                                                    
    															FROM afm_student_fee_structure asfs
    															INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID
    															LEFT JOIN (SELECT feeCollectionDetailID, feeCollectionID, studentFeeStructureID, SUM(amountPaid) AS amountPaid FROM afm_fee_collection_details GROUP BY studentFeeStructureID) afcd ON afcd.studentFeeStructureID = asfs.studentFeeStructureID
																LEFT JOIN afm_fee_collection afc ON afc.feeCollectionID = afcd.feeCollectionID 
    															INNER JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID
    															INNER JOIN afm_fee_heads afh ON afh.feeHeadID = afsd.feeHeadID
    															INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = afsd.academicYearMonthID
    															INNER JOIN asa_students ass ON ass.studentID = asfs.studentID
    															
    															LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = afs.feeGroupID AND afd.feeStructureDetailID = afsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
    							 								LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = asfs.studentID AND afd1.feeStructureDetailID = afsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 
    							 								LEFT JOIN afm_previous_year_fee_details pyfd ON pyfd.studentID = asfs.studentID 
    															
    															WHERE asfs.studentID = :|1 AND afs.academicYearID = :|2 AND afsd.academicYearMonthID IN (SELECT academicYearMonthID FROM asa_academic_year_months WHERE feePriority <= :|3 ' . $InActiveStatusCondition . ')
    															' . $QueryStringCondition . '
    															GROUP BY afsd.academicYearMonthID
    															HAVING (CASE WHEN secondDiscountValue > 0 THEN ((totalAmountPayable - secondDiscountValue) > 0) ELSE (CASE WHEN firstDiscountValue > 0 THEN ((totalAmountPayable - firstDiscountValue) > 0) ELSE (totalAmountPayable > 0) END) END)
                                                                    AND (totalAmountPaid IS NULL OR (totalAmountPaid < (CASE WHEN secondDiscountValue > 0 THEN (totalAmountPayable - secondDiscountValue) ELSE (CASE WHEN firstDiscountValue > 0 THEN (totalAmountPayable - firstDiscountValue) ELSE totalAmountPayable END) END)))
    															ORDER BY aaym.feePriority;');

				$RSDueMonthsByStudent->Execute($SearchRow->studentID, $Filters['AcademicYearID'], $FeePriority);

				if ($RSDueMonthsByStudent->Result->num_rows > 0) {
					$DueMonths = $RSDueMonthsByStudent->Result->num_rows;
				}

				$RSSearchTotalAmountPayble = $DBConnObject->Prepare('SELECT SUM(sfs.amountPayable) AS totalAmountPayble
																	FROM afm_student_fee_structure sfs 
																	INNER JOIN afm_fee_structure_details fsd ON fsd.feeStructureDetailID = sfs.feeStructureDetailID
																	INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID
																	WHERE sfs.studentID = :|1 AND fs.academicYearID = :|2
																	GROUP by studentID;');

				$RSSearchTotalAmountPayble->Execute($SearchRow->studentID, $Filters['AcademicYearID']);

				$TotalAmountPayble = 0;
				if ($RSSearchTotalAmountPayble->Result->num_rows > 0) {
					$TotalAmountPayble = $RSSearchTotalAmountPayble->FetchRow()->totalAmountPayble;
				}

				$RSSearchTotalAmountPaid = $DBConnObject->Prepare('SELECT SUM(fcd.amountPaid) AS totalAmountPaid
																	FROM afm_fee_collection_details fcd
																	INNER JOIN afm_fee_collection fc ON fc.feeCollectionID = fcd.feeCollectionID
																	INNER JOIN afm_student_fee_structure sfs ON sfs.studentFeeStructureID = fcd.studentFeeStructureID
																	INNER JOIN afm_fee_structure_details fsd ON fsd.feeStructureDetailID = sfs.feeStructureDetailID
																	INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID
																	WHERE sfs.studentID = :|1 AND fs.academicYearID = :|2 AND date(fc.createDate) BETWEEN 
																		(SELECT startDate FROM asa_academic_years WHERE academicYearID = :|3) 
																		AND (SELECT endDate FROM asa_academic_years WHERE academicYearID = :|4)
																	GROUP by fc.studentID;');

				$RSSearchTotalAmountPaid->Execute($SearchRow->studentID, $Filters['AcademicYearID'], $Filters['AcademicYearID'], $Filters['AcademicYearID']);

				$TotalAmountPaid = 0;
				if ($RSSearchTotalAmountPaid->Result->num_rows > 0) {
					$TotalAmountPaid = $RSSearchTotalAmountPaid->FetchRow()->totalAmountPaid;
				}

				$RSSearchDiscount = $DBConnObject->Prepare('SELECT fd.*, fsd.feeAmount, (pyfd.payableAmount - pyfd.paidAmount - pyfd.waveOffDue) AS previousYearDue
															FROM afm_fee_discounts fd
															INNER JOIN afm_fee_structure_details fsd ON fsd.feeStructureDetailID = fd.feeStructureDetailID
															INNER JOIN afm_fee_structure fs ON fs.feeStructureID = fsd.feeStructureID
															LEFT JOIN afm_previous_year_fee_details pyfd ON pyfd.studentID = fd.studentID
															WHERE fs.academicYearID = :|1 AND fd.studentID = :|2;');

				$RSSearchDiscount->Execute($Filters['AcademicYearID'], $SearchRow->studentID);

				$PreviousYearDue = 0;
				$TotalDiscount = 0;
				$TotalDue = 0;

				if ($RSSearchDiscount->Result->num_rows > 0) {
					while ($DiscountRow = $RSSearchDiscount->FetchRow()) {
						$PreviousYearDue = $DiscountRow->previousYearDue;
						if ($DiscountRow->discountType == 'Percentage') {
							$TotalDiscount += ($DiscountRow->feeAmount * $DiscountRow->discountValue) / 100;
							$TotalDiscount += $DiscountRow->concessionAmount;
							$TotalDiscount += $DiscountRow->waveOffAmount;
						} else if ($DiscountRow->discountType == 'Absolute') {
							$TotalDiscount += $DiscountRow->discountValue;
							$TotalDiscount += $DiscountRow->concessionAmount;
							$TotalDiscount += $DiscountRow->waveOffAmount;
						}
					}
				}

				//var_dump($TotalDiscount);exit;

				$TotalDue = $TotalAmountPayble - $TotalAmountPaid - $TotalDiscount + $PreviousYearDue;

				if ($TotalDue > 0) {
					$DefaulterList[$SearchRow->studentID]['StudentName'] = $SearchRow->firstName . ' ' . $SearchRow->lastName;
					$DefaulterList[$SearchRow->studentID]['Class'] = $SearchRow->className . ' ( ' . $SearchRow->sectionName . ' )';
					$DefaulterList[$SearchRow->studentID]['FatherMobileNumber'] = $SearchRow->fatherMobileNumber;
					$DefaulterList[$SearchRow->studentID]['MotherMobileNumber'] = $SearchRow->motherMobileNumber;

					// $DefaulterList[$SearchRow->studentID]['TotalAmountPayable'] = $SearchRow->totalAmountPayable - $DiscountValue;
					// $DefaulterList[$SearchRow->studentID]['TotalAmountPaid'] = $SearchRow->totalAmountPaid - $SearchRow->totalOtherAmountPaid;
					$DefaulterList[$SearchRow->studentID]['TotalDue'] = $TotalDue;
					$DefaulterList[$SearchRow->studentID]['DueMonths'] = $DueMonths;
				}
			}

			return $DefaulterList;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::SearchFeeDefaulters2(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::SearchFeeDefaulters2(). Stack Trace: ' . $e->getTraceAsString());
			return $DefaulterList;
		}
	}

	static function GetFeeDefaulterDues($StudentID, $FeePriority, $AcademicYearID, &$PreviousYearDue = 0, $EndDate)
	{
		$PreviousYearDue = 0;

		$FeeDefaulterDues = array();

		try {

			$DBConnObject = new DBConnect();

			/*$RSSearch = $DBConnObject->Prepare('SELECT aym.feePriority FROM asa_students ast 
                                    		    INNER JOIN (SELECT studentID, MAX(statusChangeLogID) AS statusChangeLogID, MAX(dateFrom) AS dateFrom, MONTHNAME(MAX(dateFrom)) AS inActiveMonthName FROM asa_student_status_change_log GROUP BY studentID) sscl ON sscl.studentID = ast.studentID 
                                        			INNER JOIN asa_academic_year_months aym ON aym.monthName = sscl.inActiveMonthName 
                                                    WHERE ast.status = \'InActive\' AND sscl.studentID = :|1
                                                ;');
			$RSSearch->Execute($StudentID);*/

			$RSSearch = $DBConnObject->Prepare('SELECT feePriority FROM asa_student_status_change_log WHERE newStatus = "InActive" AND studentID = :|1 AND academicYearID = :|2 AND isLastByAcademicYearID = 1;');
			$RSSearch->Execute($StudentID, $AcademicYearID);

			$FeePriority = 0;
			if ($RSSearch->Result->num_rows > 0) {
				$FeePriority = $RSSearch->FetchRow()->feePriority;
			}

			$QueryCondition = '';
			if ($FeePriority > 0) {
				$QueryCondition = ' AND afsd.academicYearMonthID IN (SELECT academicYearMonthID FROM asa_academic_year_months WHERE feePriority <= ' . $FeePriority . ')';
			}

			$RSFeeDefaulterDues = $DBConnObject->Prepare('SELECT afd.feeDiscountID AS feeDiscountIDGroup, afd1.feeDiscountID AS feeDiscountIDStudent, afh.feeHead, afh.feeHeadID, afsd.feeAmount, aaym.monthName, SUM(afcd.amountPaid) AS amountPaid, asfs.amountPayable, afd.discountType AS firstDiscountType, afd.discountValue AS firstDiscountValue, afd.concessionAmount AS firstConcessionAmount, afd.waveOffAmount AS firstWaveOffAmount, afd1.discountType AS secondDiscountType, afd1.discountValue AS secondDiscountValue, afd1.concessionAmount AS secondConcessionAmount, afd1.waveOffAmount AS secondWaveOffAmount, 
			                                                (pyfd.payableAmount - pyfd.paidAmount - pyfd.waveOffDue) AS previousYearDue 
															FROM afm_student_fee_structure asfs
															INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID
															LEFT JOIN afm_fee_collection_details afcd ON afcd.studentFeeStructureID = asfs.studentFeeStructureID AND afcd.feeCollectionID IN (SELECT feeCollectionID FROM afm_fee_collection WHERE feeDate <= :|1)
															INNER JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID
															INNER JOIN afm_fee_heads afh ON afh.feeHeadID = afsd.feeHeadID
															INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = afsd.academicYearMonthID
															LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = afs.feeGroupID AND afd.feeStructureDetailID = afsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\'
							 								LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = asfs.studentID AND afd1.feeStructureDetailID = afsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\'
							 								LEFT JOIN afm_previous_year_fee_details pyfd ON pyfd.studentID = asfs.studentID
															WHERE asfs.studentID = :|2 AND afs.academicYearID = :|3
															' . $QueryCondition . '
															GROUP BY afsd.feeHeadID, afsd.academicYearMonthID
															ORDER BY aaym.feePriority;');

			$RSFeeDefaulterDues->Execute($EndDate, $StudentID, $AcademicYearID);

			if ($RSFeeDefaulterDues->Result->num_rows <= 0) {
				return $FeeDefaulterDues;
			}

			while ($SearchRow = $RSFeeDefaulterDues->FetchRow()) {
				$FeeHeadAmount = 0;
				$FeeAmount = 0;
				$FeeHeadDiscountAmount = 0;
				$FirstDiscountAmount = 0;
				$SecondDiscountAmount = 0;

				// $FeeAmount = $SearchRow->feeAmount;
				$FeeAmount = $SearchRow->amountPayable;

				$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHead'] = $SearchRow->feeHead;

				if ($SearchRow->firstDiscountType == 'Percentage') {
					$FirstDiscountAmount = (($FeeAmount * $SearchRow->firstDiscountValue) / 100) + $SearchRow->firstConcessionAmount + $SearchRow->firstWaveOffAmount;
				} else if ($SearchRow->firstDiscountType == 'Absolute') {
					$FirstDiscountAmount = $SearchRow->firstDiscountValue + $SearchRow->firstConcessionAmount + $SearchRow->firstWaveOffAmount;
				}

				if ($SearchRow->secondDiscountType == 'Percentage') {
					$SecondDiscountAmount = (($FeeAmount * $SearchRow->secondDiscountValue) / 100) + $SearchRow->secondConcessionAmount + $SearchRow->secondWaveOffAmount;
				} else if ($SearchRow->secondDiscountType == 'Absolute') {
					$SecondDiscountAmount = $SearchRow->secondDiscountValue + $SearchRow->secondConcessionAmount + $SearchRow->secondWaveOffAmount;
				}

				$PlusInHead = 0;

				if ($SearchRow->feeDiscountIDGroup) {
					$RSSearch = $DBConnObject->Prepare('SELECT SUM(concessionAmount + waveOffAmount) as totalConcession 
				                                        FROM afm_fee_discounts 
				                                        WHERE DATE(transactionDateTime) > :|1 AND feeDiscountID = :|2');

					$RSSearch->Execute($EndDate, $SearchRow->feeDiscountIDGroup);

					if ($SearchRow->secondConcessionAmount || $SearchRow->secondWaveOffAmount) {
						$PlusInHead += $RSSearch->FetchRow()->totalConcession;
					} else if ($SearchRow->firstConcessionAmount || $SearchRow->firstWaveOffAmount) {
						$PlusInHead += $RSSearch->FetchRow()->totalConcession;
					}
				} else if ($SearchRow->feeDiscountIDStudent) {
					$RSSearch = $DBConnObject->Prepare('SELECT SUM(concessionAmount + waveOffAmount) as totalConcession 
				                                        FROM afm_fee_discounts 
				                                        WHERE DATE(transactionDateTime) > :|1 AND feeDiscountID = :|2');

					$RSSearch->Execute($EndDate, $SearchRow->feeDiscountIDStudent);

					if ($SearchRow->secondConcessionAmount || $SearchRow->secondWaveOffAmount) {
						$PlusInHead += $RSSearch->FetchRow()->totalConcession;
					} else if ($SearchRow->firstConcessionAmount || $SearchRow->firstWaveOffAmount) {
						$PlusInHead += $RSSearch->FetchRow()->totalConcession;
					}
				}

				if ($SecondDiscountAmount > 0) {
					$FeeHeadDiscountAmount = $SecondDiscountAmount;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = $SearchRow->secondDiscountType;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] = $SearchRow->secondDiscountValue;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = $SecondDiscountAmount;
				} else {
					$FeeHeadDiscountAmount = $FirstDiscountAmount;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = $SearchRow->firstDiscountType;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] = $SearchRow->firstDiscountValue;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = $FirstDiscountAmount;
				}

				$AmountPayable = 0;
				$AmountPaid = 0;

				if ($SearchRow->amountPayable) {
					$AmountPayable = $SearchRow->amountPayable;
				}

				if ($SearchRow->amountPaid) {
					$AmountPaid = $SearchRow->amountPaid;
				}

				if (($AmountPayable - $FeeHeadDiscountAmount) == $AmountPaid) {
					unset($FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]);
				} else {
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeAmount'] = $FeeAmount;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['AmountPaid'] = $SearchRow->amountPaid;

					$FeeHeadAmount += $FeeAmount - $FeeHeadDiscountAmount - $SearchRow->amountPaid + $PlusInHead;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHeadAmount'] = $FeeHeadAmount;
				}

				$PreviousYearDue = $SearchRow->previousYearDue;
			}

			// 			echo '<pre>';
			// 			var_dump($FeeDefaulterDues);exit;

			return $FeeDefaulterDues;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetFeeDefaulterDues(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetFeeDefaulterDues(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function GetFeeDefaulterDuesVishnu($StudentID, $FeePriority, $AcademicYearID, &$PreviousYearDue = 0, &$DueMonth = 0, $EndDate, $AcademicYearMonthID = '', $DueFromDate = '', $DueToDate = '')
	{
		$PreviousYearDue = 0;

		$FeeDefaulterDues = array();

		try {

			$DBConnObject = new DBConnect();

			$RSSearchPrev = $DBConnObject->Prepare('SELECT (payableAmount - paidAmount - waveOffDue) as totalPreviousYearDue
												FROM afm_previous_year_fee_details
												WHERE studentID = :|1;');

			$RSSearchPrev->Execute($StudentID);

			if ($RSSearchPrev->Result->num_rows > 0) {
				$PreviousYearDue = $RSSearchPrev->FetchRow()->totalPreviousYearDue;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT feePriority FROM asa_student_status_change_log WHERE newStatus = "InActive" AND studentID = :|1 AND academicYearID = :|2 AND isLastByAcademicYearID = 1;');
			$RSSearch->Execute($StudentID, $AcademicYearID);

			$Condition = '';

			if ($RSSearch->Result->num_rows > 0) {
				$FeePriority = $RSSearch->FetchRow()->feePriority;
			}

			if ($FeePriority) {
				$Condition = ' AND aaym.feePriority <= ' . $FeePriority;
			}

			if ($AcademicYearMonthID) {
				$Condition .= ' AND afsd.academicYearMonthID IN (' . $AcademicYearMonthID . ')';
			}

			$ConditionForOnlyCollection = '';
			$ConditionForOnlyDiscount = '';
			if ($DueFromDate != '' && $DueToDate != '') {
				$Condition .= ' AND aaym.feePriority >= 
            						(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($DueFromDate) . ', \'%M\')) 
            						AND aaym.feePriority <= 
            						(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($DueToDate) . ', \'%M\'))';

				//$ConditionForOnlyCollection .= ' AND fc.feeDate BETWEEN '.$DBConnObject->RealEscapeVariable($DueFromDate).' AND '.$DBConnObject->RealEscapeVariable($DueToDate);
				//$ConditionForOnlyDiscount .= '  AND (DATE(fd.discountDateTime) BETWEEN '.$DBConnObject->RealEscapeVariable($DueFromDate).' AND '.$DBConnObject->RealEscapeVariable($DueToDate).' OR DATE(fd.transactionDateTime) BETWEEN '.$DBConnObject->RealEscapeVariable($DueFromDate).' AND '.$DBConnObject->RealEscapeVariable($DueToDate).')';
				$ConditionForOnlyCollection .= ' AND fc.amountPaid > 0 AND fc.feeDate <= ' . $DBConnObject->RealEscapeVariable($DueToDate);
				$ConditionForOnlyDiscount .= '  AND (DATE(fd.discountDateTime) <= ' . $DBConnObject->RealEscapeVariable($DueToDate) . ' OR DATE(fd.transactionDateTime) <= ' . $DBConnObject->RealEscapeVariable($DueToDate) . ')';
			}

			$RSFeeDefaulterDues = $DBConnObject->Prepare('SELECT afh.feeHead, afh.feeHeadID, afsd.feeAmount, aaym.monthName, 
															asfs.amountPayable,
															(
																SELECT SUM(fcd.amountPaid)
																FROM afm_student_fee_structure sfss
																INNER JOIN afm_fee_collection fc ON (sfss.studentID = fc.studentID)
																INNER JOIN afm_fee_collection_details fcd ON (fc.feeCollectionID = fcd.feeCollectionID AND sfss.studentFeeStructureID = fcd.studentFeeStructureID)
																INNER JOIN afm_fee_structure_details fsds ON (sfss.feeStructureDetailID = fsds.feeStructureDetailID)
																WHERE sfss.studentID = ' . $StudentID . ' AND sfss.studentFeeStructureID = asfs.studentFeeStructureID
																AND sfss.academicYearID = ' . $AcademicYearID . ' AND fcd.academicYearID = ' . $AcademicYearID . '
																AND fsds.academicYearID = ' . $AcademicYearID .
				$ConditionForOnlyCollection . '
															) AS totalAmountPaid,
															(
																SELECT SUM(fd.calculatedDiscountAmount)
																FROM afm_fee_discounts fd
																INNER JOIN afm_fee_structure_details fsds ON fsds.feeStructureDetailID = fd.feeStructureDetailID
																WHERE fd.studentID = ' . $StudentID . ' AND fsds.feeStructureDetailID = afsd.feeStructureDetailID
																AND fsds.academicYearID = ' . $AcademicYearID .
				$ConditionForOnlyDiscount . '
															) As totalDiscountAmount
															FROM afm_student_fee_structure asfs
															INNER JOIN afm_fee_structure_details afsd ON asfs.feeStructureDetailID = afsd.feeStructureDetailID
															INNER JOIN afm_fee_heads afh ON afh.feeHeadID = afsd.feeHeadID
															INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = afsd.academicYearMonthID
															WHERE asfs.studentID = :|1 AND asfs.academicYearID = :|2
															' . $Condition . '
															GROUP BY afsd.feeHeadID, afsd.academicYearMonthID
															ORDER BY aaym.feePriority;');

			$RSFeeDefaulterDues->Execute($StudentID, $AcademicYearID);

			if ($RSFeeDefaulterDues->Result->num_rows <= 0) {
				return $FeeDefaulterDues;
			}

			$FeeDefaulterDuesOld = array();
			while ($SearchRow = $RSFeeDefaulterDues->FetchRow()) {
				$DueAmount = 0;
				$DueAmount = $SearchRow->amountPayable - ($SearchRow->totalAmountPaid + $SearchRow->totalDiscountAmount);

				if ($DueAmount <= 0) {
					continue;
				}

				if (!isset($FeeDefaulterDuesOld[$SearchRow->monthName]) && $DueAmount > 0) {
					$FeeDefaulterDuesOld[$SearchRow->monthName] = 0;

					$DueMonth += 1;
				}

				if (!isset($FeeDefaulterDues[$SearchRow->feeHeadID])) {
					$FeeDefaulterDues[$SearchRow->feeHeadID]['FeeHead'] = 0;
					$FeeDefaulterDues[$SearchRow->feeHeadID]['FeeAmount'] = 0;
					$FeeDefaulterDues[$SearchRow->feeHeadID]['AmountPaid'] = 0;
					$FeeDefaulterDues[$SearchRow->feeHeadID]['FeeHeadAmount'] = 0;

					$FeeDefaulterDues[$SearchRow->feeHeadID]['DiscountType'] = '';
					$FeeDefaulterDues[$SearchRow->feeHeadID]['DiscountValue'] = 0;
					$FeeDefaulterDues[$SearchRow->feeHeadID]['DiscountAmount'] = 0;
				}

				$FeeDefaulterDues[$SearchRow->feeHeadID]['FeeHead'] = $SearchRow->feeHead;
				$FeeDefaulterDues[$SearchRow->feeHeadID]['FeeAmount'] += $SearchRow->amountPayable;
				$FeeDefaulterDues[$SearchRow->feeHeadID]['AmountPaid'] += $SearchRow->totalAmountPaid;
				$FeeDefaulterDues[$SearchRow->feeHeadID]['FeeHeadAmount'] += $DueAmount;

				$FeeDefaulterDues[$SearchRow->feeHeadID]['DiscountType'] = '';
				$FeeDefaulterDues[$SearchRow->feeHeadID]['DiscountValue'] += 0;
				$FeeDefaulterDues[$SearchRow->feeHeadID]['DiscountAmount'] += $SearchRow->totalDiscountAmount;
			}

			return $FeeDefaulterDues;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetFeeDefaulterDuesVishnu(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetFeeDefaulterDuesVishnu(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function GetFeeDefaulterDuesDetailsVishnu($StudentID, $FeePriority, $AcademicYearID, &$PreviousYearDue = 0, $EndDate, $AcademicYearMonthID = '', $DueFromDate = '', $DueToDate = '')
	{
		$PreviousYearDue = 0;

		$FeeDefaulterDues = array();

		try {

			$DBConnObject = new DBConnect();

			$RSSearchPrev = $DBConnObject->Prepare('SELECT (payableAmount - paidAmount - waveOffDue) as totalPreviousYearDue
												FROM afm_previous_year_fee_details
												WHERE studentID = :|1 AND academicYearID = :|2;');

			$RSSearchPrev->Execute($StudentID, $AcademicYearID);

			if ($RSSearchPrev->Result->num_rows > 0) {
				$PreviousYearDue = $RSSearchPrev->FetchRow()->totalPreviousYearDue;
			}

			$RSSearch = $DBConnObject->Prepare('SELECT feePriority FROM asa_student_status_change_log WHERE newStatus = "InActive" AND studentID = :|1 AND academicYearID = :|2 AND isLastByAcademicYearID = 1;');
			$RSSearch->Execute($StudentID, $AcademicYearID);

			$Condition = '';

			if ($RSSearch->Result->num_rows > 0) {
				$FeePriority = $RSSearch->FetchRow()->feePriority;
			}

			if ($FeePriority) {
				$Condition = ' AND aaym.feePriority <= ' . $FeePriority;
			}

			if ($AcademicYearMonthID) {
				$Condition .= ' AND afsd.academicYearMonthID IN (' . $AcademicYearMonthID . ')';
			}

			$ConditionForOnlyCollection = '';
			$ConditionForOnlyDiscount = '';
			if ($DueFromDate != '' && $DueToDate != '') {
				$Condition .= ' AND aaym.feePriority >= 
            						(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($DueFromDate) . ', \'%M\')) 
            						AND aaym.feePriority <= 
            						(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(' . $DBConnObject->RealEscapeVariable($DueToDate) . ', \'%M\'))';

				//$ConditionForOnlyCollection .= ' AND fc.feeDate BETWEEN '.$DBConnObject->RealEscapeVariable($DueFromDate).' AND '.$DBConnObject->RealEscapeVariable($DueToDate);
				//$ConditionForOnlyDiscount .= '  AND (DATE(fd.discountDateTime) BETWEEN '.$DBConnObject->RealEscapeVariable($DueFromDate).' AND '.$DBConnObject->RealEscapeVariable($DueToDate).' OR DATE(fd.transactionDateTime) BETWEEN '.$DBConnObject->RealEscapeVariable($DueFromDate).' AND '.$DBConnObject->RealEscapeVariable($DueToDate).')';
				$ConditionForOnlyCollection .= ' AND fc.amountPaid > 0 AND fc.feeDate <= ' . $DBConnObject->RealEscapeVariable($DueToDate);
				$ConditionForOnlyDiscount .= '  AND (DATE(fd.discountDateTime) <= ' . $DBConnObject->RealEscapeVariable($DueToDate) . ' OR DATE(fd.transactionDateTime) <= ' . $DBConnObject->RealEscapeVariable($DueToDate) . ')';
			}

			$RSFeeDefaulterDues = $DBConnObject->Prepare('SELECT afh.feeHead, afh.feeHeadID, afsd.feeAmount, aaym.monthName, 
															asfs.amountPayable,
															(
																SELECT SUM(fcd.amountPaid)
																FROM afm_student_fee_structure sfss
																INNER JOIN afm_fee_collection fc ON (sfss.studentID = fc.studentID)
																INNER JOIN afm_fee_collection_details fcd ON (fc.feeCollectionID = fcd.feeCollectionID AND sfss.studentFeeStructureID = fcd.studentFeeStructureID)
																INNER JOIN afm_fee_structure_details fsds ON (sfss.feeStructureDetailID = fsds.feeStructureDetailID)
																WHERE sfss.studentID = ' . $StudentID . ' AND sfss.studentFeeStructureID = asfs.studentFeeStructureID
																AND sfss.academicYearID = ' . $AcademicYearID . ' AND fcd.academicYearID = ' . $AcademicYearID . ' 
																AND fsds.academicYearID = ' . $AcademicYearID . $ConditionForOnlyCollection . '
															) AS totalAmountPaid,
															(
																SELECT SUM(fd.calculatedDiscountAmount)
																FROM afm_fee_discounts fd
																INNER JOIN afm_fee_structure_details fsds ON fsds.feeStructureDetailID = fd.feeStructureDetailID
																WHERE fd.studentID = ' . $StudentID . ' AND fsds.feeStructureDetailID = afsd.feeStructureDetailID
																AND fsds.academicYearID = ' . $AcademicYearID . $ConditionForOnlyDiscount . '
															) As totalDiscountAmount
															FROM afm_student_fee_structure asfs
															INNER JOIN afm_fee_structure_details afsd ON asfs.feeStructureDetailID = afsd.feeStructureDetailID
															INNER JOIN afm_fee_heads afh ON afh.feeHeadID = afsd.feeHeadID
															INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = afsd.academicYearMonthID
															WHERE asfs.studentID = :|1 AND asfs.academicYearID = :|2
															' . $Condition . '
															GROUP BY afsd.feeHeadID, afsd.academicYearMonthID
															ORDER BY asfs.academicYearID, aaym.feePriority;');

			$RSFeeDefaulterDues->Execute($StudentID, $AcademicYearID);

			if ($RSFeeDefaulterDues->Result->num_rows <= 0) {
				return $FeeDefaulterDues;
			}

			$FeeDefaulterDuesOld = array();
			while ($SearchRow = $RSFeeDefaulterDues->FetchRow()) {
				$DueAmount = 0;
				$DueAmount = $SearchRow->amountPayable - ($SearchRow->totalAmountPaid + $SearchRow->totalDiscountAmount);

				if ($DueAmount <= 0) {
					continue;
				}

				if (!isset($FeeDefaulterDues[$SearchRow->feeHeadID])) {
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHead'] = '';
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeAmount'] = 0;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['AmountPaid'] = 0;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHeadAmount'] = 0;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = '';
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] = 0;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = 0;
				}

				$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHead'] = $SearchRow->feeHead;
				$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeAmount'] += $SearchRow->amountPayable;
				$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['AmountPaid'] += $SearchRow->totalAmountPaid;
				$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHeadAmount'] += $DueAmount;

				$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = '';
				$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] += 0;
				$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] += $SearchRow->totalDiscountAmount;
			}

			return $FeeDefaulterDues;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetFeeDefaulterDuesDetailsVishnu(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetFeeDefaulterDuesDetailsVishnu(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}


	# this function is currently being used to calculate amount in bitween loop
	static function GetFeeDefaulterDues1($StudentID, $FeePriority, $AcademicYearID, &$PreviousYearDue = 0, $DueFromDate, $DueToDate)
	{
		$PreviousYearDue = 0;

		$FeeDefaulterDues = array();

		try {

			$DBConnObject = new DBConnect();

			$QueryCondition = '';
			$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID ';

			if ($AcademicYearID == 1) {
				$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = spyd.previousClassSectionID ';
				$QueryCondition = ' AND spyd.academicYearID = ' . $DBConnObject->RealEscapeVariable($AcademicYearID);
			} else {
				$QueryCondition = ' AND afs.academicYearID = ' . $DBConnObject->RealEscapeVariable($AcademicYearID);
			}

			/*$RSSearch = $DBConnObject->Prepare('SELECT aym.feePriority FROM asa_students ast 
                                    		    INNER JOIN (SELECT studentID, MAX(statusChangeLogID) AS statusChangeLogID, MAX(dateFrom) AS dateFrom, MONTHNAME(MAX(dateFrom)) AS inActiveMonthName FROM asa_student_status_change_log GROUP BY studentID) sscl ON sscl.studentID = ast.studentID 
                                    			INNER JOIN asa_academic_year_months aym ON aym.monthName = sscl.inActiveMonthName 
                                                WHERE ast.status = \'InActive\' AND sscl.studentID = :|1;');
			$RSSearch->Execute($StudentID);*/

			$RSSearch = $DBConnObject->Prepare('SELECT feePriority FROM asa_student_status_change_log WHERE studentID = :|1 AND academicYearID = :|2 AND isLastByAcademicYearID = 1 AND oldStatus != newStatus;');
			$RSSearch->Execute($StudentID, $AcademicYearID);

			$FeePriority = 0;
			if ($RSSearch->Result->num_rows > 0) {
				$FeePriority = $RSSearch->FetchRow()->feePriority;
			}

			if ($FeePriority > 0) {
				$QueryCondition .= ' AND aaym.feePriority <= ' . $FeePriority;
			}

			$RSAcademicYearDetails = $DBConnObject->Prepare('SELECT startDate, endDate FROM asa_academic_years WHERE academicYearID = :|1');
			$RSAcademicYearDetails->Execute($AcademicYearID);

			$AcademicYearDetails = $RSAcademicYearDetails->FetchRow();

			$StartDate = $AcademicYearDetails->startDate;
			$EndDate = $AcademicYearDetails->endDate;

			$RSFeeDefaulterDues = $DBConnObject->Prepare('SELECT afd.feeDiscountID AS feeDiscountIDGroup, afd1.feeDiscountID AS feeDiscountIDStudent, afh.feeHead, afh.feeHeadID, afsd.feeAmount, aaym.monthName, SUM(afcd.amountPaid) AS amountPaid, 
															asfs.amountPayable, afd.discountType AS firstDiscountType, afd.discountValue AS firstDiscountValue, afd.concessionAmount AS firstConcessionAmount, 
															afd.waveOffAmount AS firstWaveOffAmount, afd1.discountType AS secondDiscountType, afd1.discountValue AS secondDiscountValue, afd1.concessionAmount AS secondConcessionAmount, 
															afd1.waveOffAmount AS secondWaveOffAmount, 
															pyfd.payableAmount, pyfd.waveOffDue, pyfdt.paidDate, pyfdt.totalDuePaid
															
															FROM afm_student_fee_structure asfs
															LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asfs.studentID
															INNER JOIN asa_students aas ON aas.studentID = asfs.studentID
															INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID
															LEFT JOIN afm_fee_collection_details afcd ON afcd.studentFeeStructureID = asfs.studentFeeStructureID AND 
															afcd.feeCollectionID IN 
															(
																SELECT feeCollectionID FROM afm_fee_collection WHERE studentID = :|1 AND (feeDate BETWEEN (:|2) AND (:|3) OR feeDate < :|4 ) 
															)
															INNER JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID AND afs.academicYearID = :|5 
															INNER JOIN afm_fee_heads afh ON afh.feeHeadID = afsd.feeHeadID
															INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = afsd.academicYearMonthID
															
															LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = afs.feeGroupID AND afd.feeStructureDetailID = afsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
							 								LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = asfs.studentID AND afd1.feeStructureDetailID = afsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 
							 								
							 								LEFT JOIN afm_previous_year_fee_details pyfd ON pyfd.studentID = asfs.studentID 
							 								LEFT JOIN (SELECT previousYearFeeDetailTransactionID, previousYearFeeDetailID, SUM(paidAmount) AS totalDuePaid, paidDate FROM afm_previous_year_fee_details_transactions WHERE paidDate < (:|6) GROUP BY previousYearFeeDetailID) pyfdt ON pyfdt.previousYearFeeDetailID = pyfd.previousYearFeeDetailID
							 								
															WHERE asfs.studentID = :|7
															' . $QueryCondition . '
															AND 
															aaym.feePriority BETWEEN 
	
                                                        	(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(:|8, "%M")) 
                                                        		AND 
                                                        	(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(:|9, "%M"))
															
															GROUP BY afsd.feeHeadID, afsd.academicYearMonthID
															ORDER BY aaym.feePriority;');

			$RSFeeDefaulterDues->Execute($StudentID, $DueFromDate, $DueToDate, $DueFromDate, $AcademicYearID, $DueToDate, $StudentID, $DueFromDate, $DueToDate);

			if ($RSFeeDefaulterDues->Result->num_rows <= 0) {
				return $FeeDefaulterDues;
			}

			while ($SearchRow = $RSFeeDefaulterDues->FetchRow()) {
				$FeeHeadAmount = 0;
				$FeeAmount = 0;
				$FeeHeadDiscountAmount = 0;
				$FirstDiscountAmount = 0;
				$SecondDiscountAmount = 0;

				//$FeeAmount = $SearchRow->feeAmount;
				$FeeAmount = $SearchRow->amountPayable;

				$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHead'] = $SearchRow->feeHead;

				if ($SearchRow->firstDiscountType == 'Percentage') {
					$FirstDiscountAmount = (($FeeAmount * $SearchRow->firstDiscountValue) / 100) + $SearchRow->firstConcessionAmount + $SearchRow->firstWaveOffAmount;
				} else if ($SearchRow->firstDiscountType == 'Absolute') {
					$FirstDiscountAmount = $SearchRow->firstDiscountValue + $SearchRow->firstConcessionAmount + $SearchRow->firstWaveOffAmount;
				}

				if ($SearchRow->secondDiscountType == 'Percentage') {
					$SecondDiscountAmount = (($FeeAmount * $SearchRow->secondDiscountValue) / 100) + $SearchRow->secondConcessionAmount + $SearchRow->secondWaveOffAmount;
				} else if ($SearchRow->secondDiscountType == 'Absolute') {
					$SecondDiscountAmount = $SearchRow->secondDiscountValue + $SearchRow->secondConcessionAmount + $SearchRow->secondWaveOffAmount;
				}

				$PlusInHead = 0;

				if ($SearchRow->feeDiscountIDGroup) {
					$RSSearch = $DBConnObject->Prepare('SELECT SUM(concessionAmount + waveOffAmount) as totalConcession 
				                                        FROM afm_fee_discounts 
				                                        WHERE DATE(transactionDateTime) > :|1 AND feeDiscountID = :|2');

					$RSSearch->Execute($DueToDate, $SearchRow->feeDiscountIDGroup);

					if ($SearchRow->secondConcessionAmount || $SearchRow->secondWaveOffAmount) {
						$PlusInHead += $RSSearch->FetchRow()->totalConcession;
					} else if ($SearchRow->firstConcessionAmount || $SearchRow->firstWaveOffAmount) {
						$PlusInHead += $RSSearch->FetchRow()->totalConcession;
					}
				} else if ($SearchRow->feeDiscountIDStudent) {
					$RSSearch = $DBConnObject->Prepare('SELECT SUM(concessionAmount + waveOffAmount) as totalConcession 
				                                        FROM afm_fee_discounts 
				                                        WHERE DATE(transactionDateTime) > :|1 AND feeDiscountID = :|2');

					$RSSearch->Execute($DueToDate, $SearchRow->feeDiscountIDStudent);

					// if ($SearchRow->secondConcessionAmount || $SearchRow->secondWaveOffAmount) {
					// 	$PlusInHead += $RSSearch->FetchRow()->totalConcession;
					// } else if ($SearchRow->firstConcessionAmount || $SearchRow->firstWaveOffAmount) {
					// 	$PlusInHead += $RSSearch->FetchRow()->totalConcession;
					// }
				}

				if ($SecondDiscountAmount > 0) {
					$FeeHeadDiscountAmount = $SecondDiscountAmount;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = $SearchRow->secondDiscountType;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] = $SearchRow->secondDiscountValue;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = $SecondDiscountAmount;
				} else {
					$FeeHeadDiscountAmount = $FirstDiscountAmount;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = $SearchRow->firstDiscountType;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] = $SearchRow->firstDiscountValue;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = $FirstDiscountAmount;
				}

				if (($SearchRow->amountPayable - $FeeHeadDiscountAmount) === $SearchRow->amountPaid) {
					unset($FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]);
				} else {
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeAmount'] = $FeeAmount;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['AmountPaid'] = $SearchRow->amountPaid;

					//echo "$FeeAmount - $FeeHeadDiscountAmount - $SearchRow->amountPaid";
					$FeeHeadAmount += $FeeAmount - $FeeHeadDiscountAmount - $SearchRow->amountPaid + $PlusInHead;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHeadAmount'] = $FeeHeadAmount;
					//$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHeadDiscountAmount'] = $FeeHeadDiscountAmount;
				}

				if ($SearchRow->paidDate < $EndDate) {
					$PreviousYearDue = ($SearchRow->payableAmount - $SearchRow->totalDuePaid - $SearchRow->waveOffDue);
				} else {
					$PreviousYearDue = ($SearchRow->payableAmount - $SearchRow->waveOffDue);
				}
			}

			//var_dump($FeeDefaulterDues['March'][5]);exit;


			return $FeeDefaulterDues;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetFeeDefaulterDues1(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeDefaulterDues;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetFeeDefaulterDues1(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeDefaulterDues;
		}
	}

	static function GetPrevoiusYearDue($StudentID, $FeePriority = 30, $AcademicYearID = 1, $DueFromDate = '2019-04-01', $DueToDate = '2020-03-31')
	{
		# this function will calculate due for year - 2019-2020
		$PreviousYearDue = 0;

		$FeeDefaulterDues = array();

		try {

			$DBConnObject = new DBConnect();

			$QueryCondition = '';
			$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = ass.classSectionID ';

			if ($AcademicYearID == 1) {
				$JoinClassSectionTable = ' INNER JOIN asa_class_sections acs ON acs.classSectionID = spyd.previousClassSectionID ';
				$QueryCondition = ' AND spyd.academicYearID = ' . $DBConnObject->RealEscapeVariable($AcademicYearID);
				$QueryCondition .= ' AND asfs.academicYearID = ' . $DBConnObject->RealEscapeVariable($AcademicYearID);
			} else {
				$QueryCondition = ' AND afs.academicYearID = ' . $DBConnObject->RealEscapeVariable($AcademicYearID);
			}

			$RSSearch = $DBConnObject->Prepare('SELECT aym.feePriority FROM asa_students ast 
                                    		    INNER JOIN (SELECT studentID, MAX(statusChangeLogID) AS statusChangeLogID, MAX(dateFrom) AS dateFrom, MONTHNAME(MAX(dateFrom)) AS inActiveMonthName FROM asa_student_status_change_log GROUP BY studentID) sscl ON sscl.studentID = ast.studentID 
                                    			INNER JOIN asa_academic_year_months aym ON aym.monthName = sscl.inActiveMonthName 
                                                WHERE ast.status = \'InActive\' AND sscl.studentID = :|1;');
			$RSSearch->Execute($StudentID);

			if ($RSSearch->Result->num_rows > 0) {
				$FeePriority = $RSSearch->FetchRow()->feePriority;
			}

			$RSAcademicYearDetails = $DBConnObject->Prepare('SELECT startDate, endDate FROM asa_academic_years WHERE academicYearID = :|1');
			$RSAcademicYearDetails->Execute($AcademicYearID);

			$AcademicYearDetails = $RSAcademicYearDetails->FetchRow();

			$StartDate = $AcademicYearDetails->startDate;
			$EndDate = $AcademicYearDetails->endDate;

			$RSFeeDefaulterDues = $DBConnObject->Prepare('SELECT afh.feeHead, afh.feeHeadID, afsd.feeAmount, aaym.monthName, SUM(afcd.amountPaid) AS amountPaid, 
															asfs.amountPayable, afd.discountType AS firstDiscountType, afd.discountValue AS firstDiscountValue, afd.concessionAmount AS firstConcessionAmount, 
															afd.waveOffAmount AS firstWaveOffAmount, afd1.discountType AS secondDiscountType, afd1.discountValue AS secondDiscountValue, afd1.concessionAmount AS secondConcessionAmount, 
															afd1.waveOffAmount AS secondWaveOffAmount, 
															pyfd.payableAmount, pyfd.waveOffDue, pyfdt.paidDate, pyfdt.totalDuePaid
			                                                
															FROM afm_student_fee_structure asfs
															LEFT JOIN asa_student_previous_academic_year_details spyd ON spyd.studentID = asfs.studentID
															INNER JOIN asa_students aas ON aas.studentID = spyd.studentID
															INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID
															LEFT JOIN afm_fee_collection_details afcd ON afcd.studentFeeStructureID = asfs.studentFeeStructureID AND 
															afcd.feeCollectionID IN 
															(
																SELECT feeCollectionID FROM afm_fee_collection WHERE studentID = :|1 AND (feeDate BETWEEN (:|2) AND (:|3) OR feeDate < :|4 ) 
															)
															INNER JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID
															INNER JOIN afm_fee_heads afh ON afh.feeHeadID = afsd.feeHeadID
															INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = afsd.academicYearMonthID
															LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = afs.feeGroupID AND afd.feeStructureDetailID = afsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
							 								LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = asfs.studentID AND afd1.feeStructureDetailID = afsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 
							 								LEFT JOIN afm_previous_year_fee_details pyfd ON pyfd.studentID = asfs.studentID 
							 								LEFT JOIN (SELECT previousYearFeeDetailTransactionID, previousYearFeeDetailID, SUM(paidAmount) AS totalDuePaid, paidDate FROM afm_previous_year_fee_details_transactions WHERE paidDate < (:|5) GROUP BY previousYearFeeDetailID) pyfdt ON pyfdt.previousYearFeeDetailID = pyfd.previousYearFeeDetailID
							 								
															WHERE asfs.studentID = :|6
															' . $QueryCondition . '
															AND 
															aaym.feePriority BETWEEN 
	
                                                        	(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(:|7, "%M")) 
                                                        		AND 
                                                        	(SELECT feePriority FROM asa_academic_year_months WHERE monthName = DATE_FORMAT(:|8, "%M"))
															
															GROUP BY afsd.feeHeadID, afsd.academicYearMonthID
															ORDER BY aaym.feePriority;');

			$RSFeeDefaulterDues->Execute($StudentID, $DueFromDate, $DueToDate, $DueFromDate, $DueToDate, $StudentID, $DueFromDate, $DueToDate);

			if ($RSFeeDefaulterDues->Result->num_rows <= 0) {
				return $FeeDefaulterDues;
			}

			while ($SearchRow = $RSFeeDefaulterDues->FetchRow()) {
				$FeeHeadAmount = 0;
				$FeeAmount = 0;
				$FeeHeadDiscountAmount = 0;
				$FirstDiscountAmount = 0;
				$SecondDiscountAmount = 0;

				//$FeeAmount = $SearchRow->feeAmount;
				$FeeAmount = $SearchRow->amountPayable;

				$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHead'] = $SearchRow->feeHead;

				if ($SearchRow->firstDiscountType == 'Percentage') {
					$FirstDiscountAmount = (($FeeAmount * $SearchRow->firstDiscountValue) / 100) + $SearchRow->firstConcessionAmount + $SearchRow->firstWaveOffAmount;
				} else if ($SearchRow->firstDiscountType == 'Absolute') {
					$FirstDiscountAmount = $SearchRow->firstDiscountValue + $SearchRow->firstConcessionAmount + $SearchRow->firstWaveOffAmount;
				}

				if ($SearchRow->secondDiscountType == 'Percentage') {
					$SecondDiscountAmount = (($FeeAmount * $SearchRow->secondDiscountValue) / 100) + $SearchRow->secondConcessionAmount + $SearchRow->secondWaveOffAmount;
				} else if ($SearchRow->secondDiscountType == 'Absolute') {
					$SecondDiscountAmount = $SearchRow->secondDiscountValue + $SearchRow->secondConcessionAmount + $SearchRow->secondWaveOffAmount;
				}

				if ($SecondDiscountAmount > 0) {
					$FeeHeadDiscountAmount = $SecondDiscountAmount;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = $SearchRow->secondDiscountType;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] = $SearchRow->secondDiscountValue;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = $SecondDiscountAmount;
				} else {
					$FeeHeadDiscountAmount = $FirstDiscountAmount;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = $SearchRow->firstDiscountType;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] = $SearchRow->firstDiscountValue;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = $FirstDiscountAmount;
				}

				if (($SearchRow->amountPayable - $FeeHeadDiscountAmount) == $SearchRow->amountPaid) {
					unset($FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]);
				} else {
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeAmount'] = $FeeAmount;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['AmountPaid'] = $SearchRow->amountPaid;

					$FeeHeadAmount += $FeeAmount - $FeeHeadDiscountAmount - $SearchRow->amountPaid;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHeadAmount'] = $FeeHeadAmount;
				}

				if ($SearchRow->paidDate < $EndDate) {
					$PreviousYearDue = ($SearchRow->payableAmount - $SearchRow->totalDuePaid - $SearchRow->waveOffDue);
				} else {
					$PreviousYearDue = ($SearchRow->payableAmount - $SearchRow->waveOffDue);
				}
			}

			return $FeeDefaulterDues;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetPrevoiusYearDue(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeDefaulterDues;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetPrevoiusYearDue(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeDefaulterDues;
		}
	}

	static function GetFeeDefaulterDues2($StudentID, $FeePriority, $AcademicYearID, &$PreviousYearDue = 0)
	{
		$PreviousYearDue = 0;

		$FeeDefaulterDues = array();

		try {

			$DBConnObject = new DBConnect();

			$RSSearch = $DBConnObject->Prepare('SELECT aym.feePriority FROM asa_students ast 
                                    		    INNER JOIN (SELECT studentID, MAX(statusChangeLogID) AS statusChangeLogID, MAX(dateFrom) AS dateFrom, MONTHNAME(MAX(dateFrom)) AS inActiveMonthName FROM asa_student_status_change_log GROUP BY studentID) sscl ON sscl.studentID = ast.studentID 
                                        			INNER JOIN asa_academic_year_months aym ON aym.monthName = sscl.inActiveMonthName 
                                                    WHERE ast.status = \'InActive\' AND sscl.studentID = :|1;');
			$RSSearch->Execute($StudentID);

			if ($RSSearch->Result->num_rows > 0) {
				$FeePriority = $RSSearch->FetchRow()->feePriority;
			}

			$RSAcademicYearDetails = $DBConnObject->Prepare('SELECT startDate, endDate FROM asa_academic_years WHERE academicYearID = :|1');
			$RSAcademicYearDetails->Execute($AcademicYearID);

			$AcademicYearDetails = $RSAcademicYearDetails->FetchRow();

			$StartDate = $AcademicYearDetails->startDate;
			$EndDate = $AcademicYearDetails->endDate;

			$RSFeeDefaulterDues = $DBConnObject->Prepare('SELECT afh.feeHead, afh.feeHeadID, afsd.feeAmount, aaym.monthName, SUM(afcd.amountPaid) AS amountPaid, 
															asfs.amountPayable, afd.discountType AS firstDiscountType, afd.discountValue AS firstDiscountValue, afd.concessionAmount AS firstConcessionAmount, 
															afd.waveOffAmount AS firstWaveOffAmount, afd1.discountType AS secondDiscountType, afd1.discountValue AS secondDiscountValue, afd1.concessionAmount AS secondConcessionAmount, 
															afd1.waveOffAmount AS secondWaveOffAmount, 
															pyfd.payableAmount, pyfd.waveOffDue, pyfdt.paidDate, pyfdt.totalDuePaid
			                                                
															FROM afm_student_fee_structure asfs
															INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID
															LEFT JOIN afm_fee_collection_details afcd ON afcd.studentFeeStructureID = asfs.studentFeeStructureID AND 
															afcd.feeCollectionID IN 
															(
																SELECT feeCollectionID FROM afm_fee_collection WHERE studentID = :|1 AND createDate BETWEEN (SELECT startDate FROM asa_academic_years WHERE academicYearID = :|2) AND (SELECT endDate FROM asa_academic_years WHERE academicYearID = :|3) 
															)
															INNER JOIN afm_fee_structure afs ON afs.feeStructureID = afsd.feeStructureID
															INNER JOIN afm_fee_heads afh ON afh.feeHeadID = afsd.feeHeadID
															INNER JOIN asa_academic_year_months aaym ON aaym.academicYearMonthID = afsd.academicYearMonthID
															LEFT JOIN afm_fee_discounts afd ON afd.feeGroupID = afs.feeGroupID AND afd.feeStructureDetailID = afsd.feeStructureDetailID AND afd.feeDiscountType = \'Group\' 
							 								LEFT JOIN afm_fee_discounts afd1 ON afd1.studentID = asfs.studentID AND afd1.feeStructureDetailID = afsd.feeStructureDetailID AND afd1.feeDiscountType = \'Student\' 
							 								LEFT JOIN afm_previous_year_fee_details pyfd ON pyfd.studentID = asfs.studentID 
							 								LEFT JOIN (SELECT previousYearFeeDetailTransactionID, previousYearFeeDetailID, SUM(paidAmount) AS totalDuePaid, paidDate FROM afm_previous_year_fee_details_transactions WHERE paidDate < (SELECT endDate FROM asa_academic_years WHERE academicYearID = :|5)GROUP BY previousYearFeeDetailID) pyfdt ON pyfdt.previousYearFeeDetailID = pyfd.previousYearFeeDetailID
							 								
															WHERE asfs.studentID = :|4 
																AND afsd.academicYearMonthID IN (SELECT academicYearMonthID FROM asa_academic_year_months WHERE academicYearID = :|6)
																
															GROUP BY afsd.feeHeadID, afsd.academicYearMonthID
															ORDER BY aaym.feePriority;');

			$RSFeeDefaulterDues->Execute($StudentID, $AcademicYearID, $AcademicYearID, $StudentID, $AcademicYearID, $AcademicYearID);

			if ($RSFeeDefaulterDues->Result->num_rows <= 0) {
				return $FeeDefaulterDues;
			}

			while ($SearchRow = $RSFeeDefaulterDues->FetchRow()) {
				$FeeHeadAmount = 0;
				$FeeAmount = 0;
				$FeeHeadDiscountAmount = 0;
				$FirstDiscountAmount = 0;
				$SecondDiscountAmount = 0;

				//$FeeAmount = $SearchRow->feeAmount;
				$FeeAmount = $SearchRow->amountPayable;

				$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHead'] = $SearchRow->feeHead;

				if ($SearchRow->firstDiscountType == 'Percentage') {
					$FirstDiscountAmount = (($FeeAmount * $SearchRow->firstDiscountValue) / 100) + $SearchRow->firstConcessionAmount + $SearchRow->firstWaveOffAmount;
				} else if ($SearchRow->firstDiscountType == 'Absolute') {
					$FirstDiscountAmount = $SearchRow->firstDiscountValue + $SearchRow->firstConcessionAmount + $SearchRow->firstWaveOffAmount;
				}

				if ($SearchRow->secondDiscountType == 'Percentage') {
					$SecondDiscountAmount = (($FeeAmount * $SearchRow->secondDiscountValue) / 100) + $SearchRow->secondConcessionAmount + $SearchRow->secondWaveOffAmount;
				} else if ($SearchRow->secondDiscountType == 'Absolute') {
					$SecondDiscountAmount = $SearchRow->secondDiscountValue + $SearchRow->secondConcessionAmount + $SearchRow->secondWaveOffAmount;
				}

				if ($SecondDiscountAmount > 0) {
					$FeeHeadDiscountAmount = $SecondDiscountAmount;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = $SearchRow->secondDiscountType;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] = $SearchRow->secondDiscountValue;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = $SecondDiscountAmount;
				} else {
					$FeeHeadDiscountAmount = $FirstDiscountAmount;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountType'] = $SearchRow->firstDiscountType;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountValue'] = $SearchRow->firstDiscountValue;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['DiscountAmount'] = $FirstDiscountAmount;
				}

				if (($SearchRow->amountPayable - $FeeHeadDiscountAmount) == $SearchRow->amountPaid) {
					unset($FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]);
				} else {
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeAmount'] = $FeeAmount;
					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['AmountPaid'] = $SearchRow->amountPaid;

					$FeeHeadAmount += $FeeAmount - $FeeHeadDiscountAmount - $SearchRow->amountPaid;

					$FeeDefaulterDues[$SearchRow->monthName][$SearchRow->feeHeadID]['FeeHeadAmount'] = $FeeHeadAmount;
				}

				if ($SearchRow->paidDate < $EndDate) {
					$PreviousYearDue = ($SearchRow->payableAmount - $SearchRow->totalDuePaid - $SearchRow->waveOffDue);
				} else {
					$PreviousYearDue = ($SearchRow->payableAmount - $SearchRow->waveOffDue);
				}
			}


			return $FeeDefaulterDues;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetFeeDefaulterDues2(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetFeeDefaulterDues2(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function GetDefaultedFeeMonths($StudentID, $FeePriority)
	{
		$DefaultedFeeMonths = array();
		try {
			$DBConnObject = new DBConnect();

			$RSDefaultedFeeMonths = $DBConnObject->Prepare('SELECT aaym.academicYearMonthID, aaym.feePriority FROM asa_academic_year_months aaym 
														WHERE aaym.academicYearMonthID NOT IN 
														(SELECT afsd.academicYearMonthID FROM afm_student_fee_structure asfs 
														INNER JOIN afm_fee_structure_details afsd ON afsd.feeStructureDetailID = asfs.feeStructureDetailID 
														INNER JOIN afm_fee_collection_details afcd ON afcd.studentFeeStructureID = asfs.studentFeeStructureID
														WHERE asfs.studentID = :|1) 
														AND aaym.feePriority <= :|2
														ORDER BY aaym.feePriority;');
			$RSDefaultedFeeMonths->Execute($StudentID, $FeePriority);

			if ($RSDefaultedFeeMonths->Result->num_rows <= 0) {
				return $DefaultedFeeMonths;
			}

			while ($SearchRow = $RSDefaultedFeeMonths->FetchRow()) {
				$DefaultedFeeMonths[$SearchRow->academicYearMonthID] = $SearchRow->feePriority;
			}

			return $DefaultedFeeMonths;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetDefaultedFeeMonths(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetDefaultedFeeMonths(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function GetFeeTransactionOtherChargesDetails($FeeCollectionID)
	{
		$OtherChargesDetails = array();
		try {

			$DBConnObject = new DBConnect();

			$RSOtherChargesDetails = $DBConnObject->Prepare('SELECT * FROM afm_fee_collection_other_charges
															WHERE feeCollectionID = :|1;');
			$RSOtherChargesDetails->Execute($FeeCollectionID);

			if ($RSOtherChargesDetails->Result->num_rows <= 0) {
				return $OtherChargesDetails;
			}

			while ($SearchRow = $RSOtherChargesDetails->FetchRow()) {

				$OtherChargesDetails[$SearchRow->feeCollectionOtherChargeID]['FeeType'] = $SearchRow->feeType;
				$OtherChargesDetails[$SearchRow->feeCollectionOtherChargeID]['FeeDescription'] = $SearchRow->feeDescription;
				$OtherChargesDetails[$SearchRow->feeCollectionOtherChargeID]['Amount'] = $SearchRow->amount;
			}

			return $OtherChargesDetails;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetFeeTransactionOtherChargesDetails(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetFeeTransactionOtherChargesDetails(). Stack Trace: ' . $e->getTraceAsString());
			return false;
		}
	}

	static function GetFeeCollectionIDsByTransactionID($FeeTransactionID)
	{
		$FeeCollectionIDs = array();
		try {

			$DBConnObject = new DBConnect();

			$RSFeeCollectionIDs = $DBConnObject->Prepare('SELECT feeCollectionID FROM afm_fee_collection
															WHERE feeTransactionID = :|1;');
			$RSFeeCollectionIDs->Execute($FeeTransactionID);

			if ($RSFeeCollectionIDs->Result->num_rows <= 0) {
				return $FeeCollectionIDs;
			}

			while ($SearchRow = $RSFeeCollectionIDs->FetchRow()) {
				$FeeCollectionIDs[$SearchRow->feeCollectionID] = $SearchRow->feeCollectionID;
			}

			return $FeeCollectionIDs;
		} catch (ApplicationDBException $e) {
			error_log('DEBUG: ApplicationDBException at FeeCollection::GetFeeCollectionIDsByTransactionID(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeCollectionIDs;
		} catch (Exception $e) {
			error_log('DEBUG: Exception at FeeCollection::GetFeeCollectionIDsByTransactionID(). Stack Trace: ' . $e->getTraceAsString());
			return $FeeCollectionIDs;
		}
	}
	// END OF STATIC METHODS	//

	// START OF PRIVATE METHODS	//

	private function SaveDetails()
	{
		if ($this->FeeCollectionID == 0) {
			$TransactionCode = '';

			$RSSaveFeeTransaction = $this->DBObject->Prepare('INSERT INTO afm_fee_transactions (transactionCode, transactionAmount, description, createUserID, createDate)
															VALUES (:|1, :|2, :|3, :|4, NOW());');

			$RSSaveFeeTransaction->Execute($TransactionCode, $this->TransactionAmount, $this->Description, $this->CreateUserID);

			$this->FeeTransactionID = $RSSaveFeeTransaction->LastID;

			$this->CurrentTransactionID = $this->FeeTransactionID;

			foreach ($this->PaymentModeDetails as $Counter => $PaymentModeDetails) {
				$ChequeStatus = 'Cleared';

				if ($PaymentModeDetails['PaymentMode'] == 2) //if payment mode is cheque
				{
					$ChequeStatus = 'Pending';
				}

				$RSSavePaymentMode = $this->DBObject->Prepare('INSERT INTO afm_fee_payment_mode_details (feeTransactionID, amount, paymentMode, chequeReferenceNo, chequeStatus)
																VALUES (:|1, :|2, :|3, :|4, :|5);');

				$RSSavePaymentMode->Execute($this->FeeTransactionID, $PaymentModeDetails['Amount'], $PaymentModeDetails['PaymentMode'], $PaymentModeDetails['ChequeReferenceNo'], $ChequeStatus);

				if ($PaymentModeDetails['PaymentMode'] == 6) {
					$RSUpdate = $this->DBObject->Prepare('UPDATE asa_parent_details
															SET	walletAmount = walletAmount - :|1
															WHERE parentID = :|2 LIMIT 1;');

					$RSUpdate->Execute($PaymentModeDetails['Amount'], $this->ParentID);
				}
			}

			foreach ($this->FeeCollectionDetails as $StudentID => $Details) {
				$RSSaveFeeCollection = $this->DBObject->Prepare('INSERT INTO afm_fee_collection (feeTransactionID, studentID, feeDate, totalAmount, totalDiscount, amountPaid, paymentMode, chequeReferenceNo, createUserID, createDate)
																VALUES (:|1, :|2, :|3, :|4, :|5, :|6, :|7, :|8, :|9, NOW());');

				$RSSaveFeeCollection->Execute($this->FeeTransactionID, $StudentID, $this->FeeDate, $Details['StudentAmountPayable'], $Details['TotalDiscount'], $Details['StudentAmountPaid'], $this->PaymentMode, $this->ChequeReferenceNo, $this->CreateUserID);

				$this->FeeCollectionID = $RSSaveFeeCollection->LastID;

				if (array_key_exists('StudentFeeCollectionDetails', $Details)) {
					foreach ($Details['StudentFeeCollectionDetails'] as $StudentFeeStructureID => $AmountPaid) {
						$RSSaveFeeCollectionDetails = $this->DBObject->Prepare('INSERT INTO afm_fee_collection_details (feeCollectionID, studentFeeStructureID, amountPaid)
    														VALUES (:|1, :|2, :|3);');

						$RSSaveFeeCollectionDetails->Execute($this->FeeCollectionID, $StudentFeeStructureID, $AmountPaid);
					}
				}
			}

			foreach ($this->OtherChargesDetails as $StudentID => $OtherDetails) {
				$RSSearchFeeCollectionID = $this->DBObject->Prepare('SELECT feeCollectionID FROM afm_fee_collection WHERE studentID = :|1 AND feeDate = :|2 AND createDate = NOW();');

				$RSSearchFeeCollectionID->Execute($StudentID, $this->FeeDate);

				if ($RSSearchFeeCollectionID->Result->num_rows <= 0) {
					$RSSaveFeeCollection = $this->DBObject->Prepare('INSERT INTO afm_fee_collection (feeTransactionID, studentID, feeDate, totalAmount, totalDiscount, amountPaid, paymentMode, chequeReferenceNo, createUserID, createDate)
																	VALUES (:|1, :|2, :|3, :|4, :|5, :|6, :|7, :|8, :|9, NOW());');

					$RSSaveFeeCollection->Execute($this->FeeTransactionID, $StudentID, $this->FeeDate, $this->TotalAmount, $this->TotalDiscount, $this->AmountPaid, $this->PaymentMode, $this->ChequeReferenceNo, $this->CreateUserID);

					$FeeCollectionID = $RSSaveFeeCollection->LastID;
				} else {
					$FeeCollectionID = $RSSearchFeeCollectionID->FetchRow()->feeCollectionID;
				}

				foreach ($OtherDetails as $key => $Details) {
					$RSSaveOtherChargesDetails = $this->DBObject->Prepare('INSERT INTO afm_fee_collection_other_charges (feeCollectionID, feeType, feeDescription, amount)
														VALUES (:|1, :|2, :|3, :|4);');

					$RSSaveOtherChargesDetails->Execute($FeeCollectionID, $Details['FeeType'], $Details['FeeDescription'], $Details['Amount']);

					if ($Details['FeeType'] == 'PreviousYearDue') {
						$RSSearchPreviousYearFeeDetailID = $this->DBObject->Prepare('SELECT previousYearFeeDetailID FROM afm_previous_year_fee_details WHERE studentID = :|1;');

						$RSSearchPreviousYearFeeDetailID->Execute($StudentID);

						$PreviousYearFeeDetailID = $RSSearchPreviousYearFeeDetailID->FetchRow()->previousYearFeeDetailID;

						$RSUpdate = $this->DBObject->Prepare('UPDATE afm_previous_year_fee_details
																SET	paidAmount = paidAmount + :|1,
																	waveOffDue = waveOffDue + :|2
																WHERE studentID = :|3 LIMIT 1;');

						$RSUpdate->Execute($Details['Amount'], $Details['WaveOffAmount'], $StudentID);

						$RSSavePreviousYearFeeDetailTransaction = $this->DBObject->Prepare('INSERT INTO afm_previous_year_fee_details_transactions (previousYearFeeDetailID, paidAmount, paidDate)
        															                            VALUES (:|1, :|2, :|3);');

						$RSSavePreviousYearFeeDetailTransaction->Execute($PreviousYearFeeDetailID, $Details['Amount'], $this->FeeDate);
					}
				}
			}

			if (count($this->AdvanceFeeDetails) > 0) {
				$RSSaveAdvance = $this->DBObject->Prepare('INSERT INTO afm_advance_fee (parentID, feeTransactionID, advanceAmount)
															VALUES (:|1, :|2, :|3);');

				$RSSaveAdvance->Execute($this->AdvanceFeeDetails['ParentID'], $this->FeeTransactionID, $this->AdvanceFeeDetails['AdvanceFee']);

				$RSUpdate = $this->DBObject->Prepare('UPDATE asa_parent_details
														SET	walletAmount = walletAmount + :|1
														WHERE parentID = :|2 LIMIT 1;');

				$RSUpdate->Execute($this->AdvanceFeeDetails['AdvanceFee'], $this->ParentID);
			}
		} else {
			$RSUpdate = $this->DBObject->Prepare('UPDATE afm_fee_collection
													SET	studentID = :|1,
														feeDate = :|2
													WHERE feeCollectionID = :|3 LIMIT 1;');

			$RSUpdate->Execute($this->StudentID, $this->FeeDate, $this->FeeCollectionID);
		}

		// 		self::UpdateFeeCollectionDetailsColumn();

		return true;
	}

	private function RemoveFeeCollection()
	{
		if (!isset($this->FeeCollectionID)) {
			throw new ApplicationARException('', APP_AR_ERROR_DELETE_WITHOUT_ID);
		}

		$RSDeleteFeeCollectionDetails = $this->DBObject->Prepare('DELETE FROM afm_fee_collection_details WHERE feeCollectionID = :|1;');
		$RSDeleteFeeCollectionDetails->Execute($this->FeeCollectionID);

		$RSSelectOtherFeeType = $this->DBObject->Prepare('SELECT feeCollectionOtherChargeID, feeType, amount FROM afm_fee_collection_other_charges WHERE feeCollectionID = :|1;');
		$RSSelectOtherFeeType->Execute($this->FeeCollectionID);

		if ($RSSelectOtherFeeType->Result->num_rows > 0) {
			while ($SearchRow = $RSSelectOtherFeeType->FetchRow()) {
				if ($SearchRow->feeType == 'PreviousYearDue') {
					$RSSearchPreviousYearFeeDetailID = $this->DBObject->Prepare('SELECT previousYearFeeDetailID FROM afm_previous_year_fee_details WHERE studentID = :|1;');

					$RSSearchPreviousYearFeeDetailID->Execute($StudentID);

					$PreviousYearFeeDetailID = $RSSearchPreviousYearFeeDetailID->FetchRow()->previousYearFeeDetailID;

					$RSUpdate = $this->DBObject->Prepare('UPDATE afm_previous_year_fee_details
    														SET	paidAmount = paidAmount - :|1
    														WHERE studentID = :|2 LIMIT 1;');

					$RSUpdate->Execute($SearchRow->amount, $this->StudentID);

					$RSSavePreviousYearFeeDetailTransaction = $this->DBObject->Prepare('DELETE FROM afm_previous_year_fee_details_transactions WHERE previousYearFeeDetailID = :|1 AND paidAmount = :|2;');

					$RSSavePreviousYearFeeDetailTransaction->Execute($PreviousYearFeeDetailID, $SearchRow->amount);
				}
			}
		}

		$RSDeleteFeeCollectionOtherCharges = $this->DBObject->Prepare('DELETE FROM afm_fee_collection_other_charges WHERE feeCollectionID = :|1;');
		$RSDeleteFeeCollectionOtherCharges->Execute($this->FeeCollectionID);

		$RSDeleteFeeCollection = $this->DBObject->Prepare('DELETE FROM afm_fee_collection WHERE feeCollectionID = :|1;');
		$RSDeleteFeeCollection->Execute($this->FeeCollectionID);

		return true;
	}

	private function GetFeeCollectionByID()
	{
		$RSFeeCollection = $this->DBObject->Prepare('SELECT * FROM afm_fee_collection WHERE feeCollectionID = :|1;');
		$RSFeeCollection->Execute($this->FeeCollectionID);

		$FeeCollectionRow = $RSFeeCollection->FetchRow();

		$this->SetAttributesFromDB($FeeCollectionRow);
	}

	private function SetAttributesFromDB($FeeCollectionRow)
	{
		$this->FeeCollectionID = $FeeCollectionRow->feeCollectionID;
		$this->StudentID = $FeeCollectionRow->studentID;
		$this->FeeDate = $FeeCollectionRow->feeDate;

		$this->TotalAmount = $FeeCollectionRow->totalAmount;
		$this->TotalDiscount = $FeeCollectionRow->totalDiscount;
		$this->AmountPaid = $FeeCollectionRow->amountPaid;
		$this->PaymentMode = $FeeCollectionRow->paymentMode;
		$this->ChequeReferenceNo = $FeeCollectionRow->chequeReferenceNo;

		$this->CreateUserID = $FeeCollectionRow->createUserID;
		$this->CreateDate = $FeeCollectionRow->createDate;
	}
}
