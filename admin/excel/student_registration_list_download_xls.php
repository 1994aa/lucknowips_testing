<?php
require_once("PHPExcel/PHPExcel.php");

set_time_limit(7200);

if (!isset($RegisteredStudents) && $RegisteredStudents <= 0)
{
    die('No Data Found');
}

$excelWriter = new PHPExcel();
$excelWriter->getProperties()->setCreator("Added")
        ->setLastModifiedBy("Added")
        ->setTitle('Student Registration List')
        ->setSubject('Student Registration List')
        ->setDescription('');
$excelWriter->getActiveSheet()
        ->getStyle('A1:J1')
        ->getFont()->setBold(true)
        ->setSize(16);

$excelWriter->setActiveSheetIndex(0)
        ->setCellValue('A1', 'S. No.')
        ->setCellValue('B1', 'Class')
		->setCellValue('C1', 'Student Name')
        ->setCellValue('D1', 'Gender')
        ->setCellValue('E1', 'Category')
        ->setCellValue('F1', 'Admission Taken')
        ->setCellValue('G1', 'Registration Fee')
        ->setCellValue('H1', 'Aadhar Number')
        ->setCellValue('I1', 'Father Name')
        ->setCellValue('J1', 'Mother Name')
        ->setCellValue('K1', 'Mobile Number')
        ->setCellValue('L1', 'Active')
        ->setCellValue('M1', 'Create User')
        ->setCellValue('N1', 'Create Date');

$excelWriter->getActiveSheet()->getStyle('A1:N1')->getFont()->setBold(true);
$excelWriter->getActiveSheet()->getStyle('A1:N1')->getFont()->getColor()->setARGB('FFFFFFFF');
$excelWriter->getActiveSheet()->getStyle('A1:N1')->applyFromArray(
        array('fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('argb' => 'FF2F88F0')
            )
        )
);

for ($col = 'A'; $col !== 'N'; $col++)
{
    $excelWriter->getActiveSheet()
            ->getColumnDimension($col)
            ->setAutoSize(true);
}

$index = 2;

foreach ($RegisteredStudents as $StudentRegistrationID => $StudentRegistrationDetails)
{
    ++$index;
    
    $excelWriter->setActiveSheetIndex(0)
            ->setCellValueExplicit('A' . $index, $index-2, PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('B' . $index, $StudentRegistrationDetails['ClassName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('C' . $index, $StudentRegistrationDetails['StudentName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('D' . $index, $StudentRegistrationDetails['Gender'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('E' . $index, $StudentRegistrationDetails['Category'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('F' . $index, (($StudentRegistrationDetails['IsAdmissionTaken']) ? 'Yes' : 'No'), PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('G' . $index, (($StudentRegistrationDetails['RegistrationFee']) ? $StudentRegistrationDetails['RegistrationFee'] : ''), PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('H' . $index, ($StudentRegistrationDetails['AadharNumber']) ? $StudentRegistrationDetails['AadharNumber'] : '-', PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('I' . $index, $StudentRegistrationDetails['FatherName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('J' . $index, $StudentRegistrationDetails['MotherName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('K' . $index, $StudentRegistrationDetails['MobileNumber'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('L' . $index, (($StudentRegistrationDetails['IsActive']) ? 'Yes' : 'No'), PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('M' . $index, $StudentRegistrationDetails['CreateUserName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('N' . $index, date('d/m/Y', strtotime($StudentRegistrationDetails['CreateDate'])), PHPExcel_Cell_DataType::TYPE_STRING);

    if ($index % 2 == 0)
    {
        $excelWriter->getActiveSheet()->getStyle('A' . $index . ':N' . $index)->applyFromArray(
                array('fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('argb' => 'FFE5ECF5')
                    )
                )
        );
    }
}

++$index;

$excelWriter->getActiveSheet()->getStyle('A1:N' . $index)->applyFromArray(
        array(
            'borders' => array(
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
            )
        )
);

$excelWriter->getActiveSheet()->setTitle('student_registration');
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$excelWriter->setActiveSheetIndex(0);

// Redirect output to a clientâ€™s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=student_registration.xls');
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