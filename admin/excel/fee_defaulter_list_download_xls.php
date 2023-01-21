<?php
require_once("PHPExcel/PHPExcel.php");

set_time_limit(7200);

if (!isset($TotalRecords) && $TotalRecords <= 0) {
    die('No Data Found');
}

$FeeHeadList =  array();
$FeeHeadList = FeeHead::GetActiveFeeHeads();
$FeeHeadPaidAmountTotal = array();

if ($Clean['AcademicYearID'] == 1) {
    $DefaulterList = FeeCollection::SearchFeeDefaultersVishnu($TotalRecords, false, $Filters, 0, $TotalRecords);
} else {
    $DefaulterList = FeeCollection::SearchDateWiseDefaulter($TotalRecords, false, $Filters, 0, $TotalRecords);
}

$ReportHeaderText = '';

if ($Clean['AcademicYearID'] != 0) {
    $ReportHeaderText .= ' Session: ' . date('Y', strtotime($AcademicYears[$Clean['AcademicYearID']]['StartDate'])) . ' - ' . date('Y', strtotime($AcademicYears[$Clean['AcademicYearID']]['EndDate'])) . ',';
}

if ($Clean['ClassID'] > 0) {
    $ReportHeaderText .= ' Class : ' . $ClassList[$Clean['ClassID']] . ',';
}

if ($Clean['ClassSectionID'] > 0) {
    $ReportHeaderText .= ' Section : ' . $ClassSectionsList[$Clean['ClassSectionID']] . ',';
}

if ($Clean['StudentID'] > 0) {
    $ReportHeaderText .= ' Student : ' . $StudentsList[$Clean['StudentID']]['FirstName'] . ',';
}

if ($Clean['FeeHeadID'] > 0) {
    $ReportHeaderText .= ' Fee Head : ' . $ActiveFeeHeads[$Clean['FeeHeadID']]['FeeHead'] . ',';
}

if ($Clean['StudentName'] != '') {
    $ReportHeaderText .= ' Student Name : ' . $Clean['StudentName'] . ',';
}

if ($Clean['Status'] != '') {
    $ReportHeaderText .= ' Status: ' . $Clean['Status'] . ' students,';
}

if (count($Clean['MonthList']) > 1) {
    $ReportHeaderText .= ' Months ';
}

foreach ($Clean['MonthList'] as $Key => $MonthID) {
    $ReportHeaderText .= ' ' . $AcademicYearMonths[$MonthID]['MonthName'] . ', ';
}

if ($ReportHeaderText != '') {
    $ReportHeaderText = 'Defaulter List Report For ' . rtrim($ReportHeaderText, ',');
}

$excelWriter = new PHPExcel();
$excelWriter->getProperties()->setCreator("Added")
    ->setLastModifiedBy("Added")
    ->setTitle('Fee Defaulter List')
    ->setSubject('Fee Defaulter List')
    ->setDescription('');
$excelWriter->getActiveSheet()
    ->getStyle('A1:L1')
    ->getFont()->setBold(false)
    ->setSize(16);

$excelWriter->setActiveSheetIndex(0)
    ->setCellValue('A1', $ReportHeaderText, PHPExcel_Cell_DataType::TYPE_STRING);

$excelWriter->getActiveSheet()->mergeCells('A1:L1');

$RowCounter = 'G';
foreach ($FeeHeadList as $FeeHeadID => $FeeHeadDetails) {
    $FeeHeadPaidAmountTotal[$FeeHeadID] = 0;
    $excelWriter->setActiveSheetIndex(0)->setCellValue($RowCounter++ . '1', $FeeHeadDetails['FeeHead']);
}

$excelWriter->setActiveSheetIndex(0)->setCellValue($RowCounter . '1', 'Current Due');
$RowCounter = 'L';
$excelWriter->setActiveSheetIndex(0)->setCellValue($RowCounter . '1', 'Total Due');

$excelWriter->getActiveSheet()->getStyle('A1:' . $RowCounter . '1')->getFont()->setBold(true);
$excelWriter->getActiveSheet()->getStyle('A1:' . $RowCounter . '1')->getFont()->getColor()->setARGB('FFFFFFFF');
$excelWriter->getActiveSheet()->getStyle('A1:' . $RowCounter++ . '1')->applyFromArray(
    array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('argb' => 'FF2F88F0')
        )
    )
);

$excelWriter->setActiveSheetIndex(0)
    ->setCellValue('A2', 'S.No')
    ->setCellValue('B2', 'Sr.No')
    ->setCellValue('C2', 'Student Name')
    ->setCellValue('D2', 'Class')
    ->setCellValue('E2', 'Mobile Number')
    ->setCellValue('F2', 'Previous Due');

$RowCounter = 'G';
foreach ($FeeHeadList as $FeeHeadID => $FeeHeadDetails) {
    $FeeHeadPaidAmountTotal[$FeeHeadID] = 0;
    $excelWriter->setActiveSheetIndex(0)->setCellValue($RowCounter++ . '2', $FeeHeadDetails['FeeHead']);
}

$excelWriter->setActiveSheetIndex(0)->setCellValue($RowCounter . '2', 'Current Due');

$RowCounter = 'M';
$excelWriter->setActiveSheetIndex(0)->setCellValue($RowCounter . '2', 'Total Due');

$excelWriter->getActiveSheet()->getStyle('A1:' . $RowCounter . '2')->getFont()->setBold(true);
$excelWriter->getActiveSheet()->getStyle('A1:' . $RowCounter . '2')->getFont()->getColor()->setARGB('FFFFFFFF');
$excelWriter->getActiveSheet()->getStyle('A1:' . $RowCounter++ . '2')->applyFromArray(
    array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('argb' => 'FF2F88F0')
        )
    )
);

for ($col = 'A'; $col !== $RowCounter; $col++) {
    $excelWriter->getActiveSheet()
        ->getColumnDimension($col)
        ->setAutoSize(true);
}

$index = 2;
$TotalValue = 0;
$TotalPlotSize = 0;
$TotalPreviousYearDue = 0;
$TotalDue = 0;

foreach ($DefaulterList as $StudentID => $Details) {
    $PreviousDefaultedFees = array();
    $PreviousDueAmount = 0;

    if ($Clean['AcademicYearID'] == 2) {
        $PreviousDefaultedFees = FeeCollection::GetFeeDefaulterDues($StudentID, 120, 1, $PreviousYearDue, date('Y-m-d'));

        foreach ($PreviousDefaultedFees as $Month => $FeeDetails) {
            $PreviousDueAmount += array_sum(array_column($FeeDetails, 'FeeHeadAmount'));
        }

        if ($PreviousDueAmount < 0) {
            $PreviousDueAmount = 0;
        }
    }

    $DueMonth = 0;

    $SelectedDueMonths = '';

    if (is_array($Clean['MonthList']) && count($Clean['MonthList']) > 0) {
        $SelectedDueMonths = implode(',', $Clean['MonthList']);
    }

    $FeeDefaulterDues = array();
    $FeeDefaulterDues = FeeCollection::GetFeeDefaulterDuesVishnu($StudentID, $FeePriority, $Clean['AcademicYearID'], $PreviousYearDue, $DueMonth, date('Y-m-d'), $SelectedDueMonths);

    $RowTotalDue = 0;
    $TotalDue += $PreviousYearDue + $PreviousDueAmount;
    $RowTotalDue = $PreviousYearDue + $PreviousDueAmount;
    // $TotalPreviousYearDue += $PreviousYearDue + $PreviousDueAmount;
    if (isset($_SESSION['DB']) && $_SESSION['DB'] == 'addedschools_lucknowips_testing-22-23') {
        $OldPreviousAmountDue = FeeCollection::GetPreviousDefaulterAmount($StudentID);
        $PreviousAmountDue = FeeCollection::GetPrevYear3DefaulterAmount($StudentID);
        $newPreviousAmountDue = $OldPreviousAmountDue + $PreviousAmountDue;
    } else if (isset($_SESSION['DB']) && $_SESSION['DB'] == 'addedschools_lucknowips_testing-21-22') {
        $newPreviousAmountDue = FeeCollection::GetPreviousDefaulterAmount($StudentID);
    } elseif (isset($_SESSION['DB']) && $_SESSION['DB'] == 'addedschools_lucknowips_testing-23-24') {
        $OldPreviousAmountDue = FeeCollection::GetPreviousDefaulterAmount($StudentID);
        $PreviousAmountDue = FeeCollection::GetPrevYear3DefaulterAmount($StudentID);
        $PreviousAmountDue4 = FeeCollection::GetPrevYear4DefaulterAmount($StudentID);
        $newPreviousAmountDue = $OldPreviousAmountDue + $PreviousAmountDue + $PreviousAmountDue4;
    }

    $TotalPreviousYearDue += $newPreviousAmountDue;

    ++$index;

    $excelWriter->setActiveSheetIndex(0)
        ->setCellValueExplicit('A' . $index, $index - 2, PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValueExplicit('B' . $index, $Details['EnrollmentID'], PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValueExplicit('C' . $index, $Details['StudentName'], PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValueExplicit('D' . $index, $Details['Class'], PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValueExplicit('E' . $index, $Details['FatherMobileNumber'], PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValueExplicit('F' . $index, ($newPreviousAmountDue), PHPExcel_Cell_DataType::TYPE_STRING);

    $RowCount = 'G';
    foreach ($FeeHeadList as $FeeHeadID => $FeeHeadDetails) {
        $FeeHeadDueAmount = 0;

        if (isset($FeeDefaulterDues[$FeeHeadID])) {
            $RowTotalDue += $FeeHeadDueAmount;
            $FeeHeadDueAmount = $FeeDefaulterDues[$FeeHeadID]['FeeHeadAmount'];
        }

        foreach ($FeeDefaulterDues as $Month => $DefaulterDetails) {
            if ($Clean['FeeHeadID'] > 0) {
                if ($Clean['FeeHeadID'] == $FeeHeadID  && isset($FeeDefaulterDues[$FeeHeadID])) {
                    $FeeHeadDueAmount = $FeeDefaulterDues[$FeeHeadID]['FeeHeadAmount'];
                }
            } else {
                if (isset($FeeDefaulterDues[$FeeHeadID])) {
                    $FeeHeadDueAmount = $FeeDefaulterDues[$FeeHeadID]['FeeHeadAmount'];
                }
            }
        }

        $TotalDue += $FeeHeadDueAmount;

        $RowTotalDue += $FeeHeadDueAmount;

        $excelWriter->setActiveSheetIndex(0)->setCellValueExplicit($RowCount++ . $index, number_format($FeeHeadDueAmount, 2), PHPExcel_Cell_DataType::TYPE_STRING);

        $FeeHeadPaidAmountTotal[$FeeHeadID] += $FeeHeadDueAmount;
    }

    $excelWriter->setActiveSheetIndex(0)->setCellValueExplicit($RowCount . $index, number_format($RowTotalDue, 2), PHPExcel_Cell_DataType::TYPE_STRING);

    $RowCount = 'M';
    $TotoalDueAmount = $RowTotalDue + $newPreviousAmountDue;
    $excelWriter->setActiveSheetIndex(0)->setCellValueExplicit($RowCount . $index, number_format($TotoalDueAmount, 2), PHPExcel_Cell_DataType::TYPE_STRING);

    if ($index % 2 == 0) {
        $excelWriter->getActiveSheet()->getStyle('A' . $index . ':' . $RowCount . $index)->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'FFE5ECF5')
                )
            )
        );
    }
}

++$index;

$index++;

$excelWriter->setActiveSheetIndex(0)
    ->setCellValueExplicit('A' . $index, '', PHPExcel_Cell_DataType::TYPE_STRING)
    ->setCellValueExplicit('B' . $index, '', PHPExcel_Cell_DataType::TYPE_STRING)
    ->setCellValueExplicit('C' . $index, '', PHPExcel_Cell_DataType::TYPE_STRING)
    ->setCellValueExplicit('D' . $index, '', PHPExcel_Cell_DataType::TYPE_STRING)
    ->setCellValueExplicit('E' . $index, 'Grand Total :', PHPExcel_Cell_DataType::TYPE_STRING)
    ->setCellValueExplicit('F' . $index, number_format($TotalPreviousYearDue, 2), PHPExcel_Cell_DataType::TYPE_STRING);

$LastRowCount = 'G';
foreach ($FeeHeadList as $FeeHeadID => $FeeHeadDetails) {
    $excelWriter->setActiveSheetIndex(0)->setCellValueExplicit($LastRowCount++ . $index, number_format($FeeHeadPaidAmountTotal[$FeeHeadID], 2), PHPExcel_Cell_DataType::TYPE_STRING);
}

$excelWriter->setActiveSheetIndex(0)->setCellValueExplicit($LastRowCount++ . $index, number_format($TotalDue, 2), PHPExcel_Cell_DataType::TYPE_STRING);

$RowCount = 'M';
$GrandTotoalDueAmount = $TotalDue + $TotalPreviousYearDue;
$excelWriter->setActiveSheetIndex(0)->setCellValueExplicit($RowCount . $index, number_format($GrandTotoalDueAmount, 2), PHPExcel_Cell_DataType::TYPE_STRING);

$excelWriter->getActiveSheet()->getStyle('A' . $index .  ':' . $LastRowCount . $index)->getFont()->setBold(true);
$excelWriter->getActiveSheet()->getStyle('A' . $index . ':' . $LastRowCount . $index)->getFont()->getColor()->setARGB('FFFFFFFF');
$excelWriter->getActiveSheet()->getStyle('A' . $index . ':' . $LastRowCount . $index)->applyFromArray(
    array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('argb' => 'FF2F88F0')
        )
    )
);


$excelWriter->getActiveSheet()->getStyle('A1:' . $RowCount . $index)->applyFromArray(
    array(
        'borders' => array(
            'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
        )
    )
);

$excelWriter->getActiveSheet()->setTitle('fee_defaulter_list');
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$excelWriter->setActiveSheetIndex(0);

// Redirect output to a clientâ€™s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=fee_defaulter_list.xls');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($excelWriter, 'Excel5');
$objWriter->save('php://output');
exit;
