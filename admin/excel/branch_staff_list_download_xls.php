<?php
require_once("PHPExcel/PHPExcel.php");

set_time_limit(7200);

if (!isset($AllBranchStaff) && $AllBranchStaff <= 0)
{
    die('No Data Found');
}

$excelWriter = new PHPExcel();
$excelWriter->getProperties()->setCreator("Added")
        ->setLastModifiedBy("Added")
        ->setTitle('Branch Staff List')
        ->setSubject('Branch Staff List')
        ->setDescription('');
$excelWriter->getActiveSheet()
        ->getStyle('A1:J1')
        ->getFont()->setBold(true)
        ->setSize(16);

$excelWriter->setActiveSheetIndex(0)
        ->setCellValue('A1', 'S. No.')
        ->setCellValue('B1', 'First Name')
		->setCellValue('C1', 'Last Name')
        ->setCellValue('D1', 'Staff Category')
        ->setCellValue('E1', 'DOB')
        ->setCellValue('F1', 'Contact Number')
        ->setCellValue('G1', 'Address')
        ->setCellValue('H1', 'Joining Date')
        ->setCellValue('I1', 'User Name')
        ->setCellValue('J1', 'Active');

$excelWriter->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);
$excelWriter->getActiveSheet()->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');
$excelWriter->getActiveSheet()->getStyle('A1:J1')->applyFromArray(
        array('fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('argb' => 'FF2F88F0')
            )
        )
);

for ($col = 'A'; $col !== 'J'; $col++)
{
    $excelWriter->getActiveSheet()
            ->getColumnDimension($col)
            ->setAutoSize(true);
}

$index = 2;

foreach ($AllBranchStaff as $BranchStaffID => $BranchStaffDetails)
{
    ++$index;
    
    $MobileNumber = '';
    
    if ($BranchStaffDetails['MobileNumber1'] != '' && $BranchStaffDetails['MobileNumber1'] != 0)
    {
        $MobileNumber = $BranchStaffDetails['MobileNumber1'];
    }
    
    if ($BranchStaffDetails['MobileNumber2'])
    {
        if ($MobileNumber)
        {
            $MobileNumber = ', ';
        }
        
        $MobileNumber .= $BranchStaffDetails['MobileNumber2'];
    }
    
    $MobileNumber = ltrim($MobileNumber, ', ');
    
    $DOB = '';
    
    if ($BranchStaffDetails['DOB'] != '')
    {
        $DOB = $BranchStaffDetails['DOB'];
    }
    
    $excelWriter->setActiveSheetIndex(0)
            ->setCellValueExplicit('A' . $index, $index-2, PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('B' . $index, $BranchStaffDetails['FirstName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('C' . $index, $BranchStaffDetails['LastName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('D' . $index, $BranchStaffDetails['StaffCategory'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('E' . $index, date('d/m/Y',strtotime($DOB)), PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('F' . $index, $MobileNumber, PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('G' . $index, $BranchStaffDetails['Address1'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('H' . $index, date('d/m/Y',strtotime($BranchStaffDetails['JoiningDate'])), PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('I' . $index, $BranchStaffDetails['UserName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('J' . $index, (($BranchStaffDetails['IsActive']) ? 'Yes' : 'No'), PHPExcel_Cell_DataType::TYPE_STRING);

    if ($index % 2 == 0)
    {
        $excelWriter->getActiveSheet()->getStyle('A' . $index . ':J' . $index)->applyFromArray(
                array('fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('argb' => 'FFE5ECF5')
                    )
                )
        );
    }
}

++$index;

$excelWriter->getActiveSheet()->getStyle('A1:J' . $index)->applyFromArray(
        array(
            'borders' => array(
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
            )
        )
);

$excelWriter->getActiveSheet()->setTitle('branch_staff');
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$excelWriter->setActiveSheetIndex(0);

// Redirect output to a clientâ€™s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=branch_staff.xls');
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
?>