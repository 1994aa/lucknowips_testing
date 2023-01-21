<?php
require_once("PHPExcel/PHPExcel.php");

set_time_limit(7200);

if (!isset($AllAdmittedStudents) && $AllAdmittedStudents <= 0)
{
    die('No Data Found');
}

$excelWriter = new PHPExcel();
$excelWriter->getProperties()->setCreator("Added")
        ->setLastModifiedBy("Added")
        ->setTitle('New Admission List')
        ->setSubject('New Admission List')
        ->setDescription('');
$excelWriter->getActiveSheet()
        ->getStyle('A1:M1')
        ->getFont()->setBold(true)
        ->setSize(16);

$excelWriter->setActiveSheetIndex(0)
        ->setCellValue('A1', 'S. No.')
        ->setCellValue('B1', 'Sr. No')
		->setCellValue('C1', 'Student Name')
        ->setCellValue('D1', 'Class')
        ->setCellValue('E1', 'Father Name')
        ->setCellValue('F1', 'Mother Name')
        ->setCellValue('G1', 'Gender')
        ->setCellValue('H1', 'Category')
        ->setCellValue('I1', 'Admission Date')
        ->setCellValue('J1', 'Registration Fee')
        ->setCellValue('K1', 'Mobile Number')
        ->setCellValue('L1', 'Create User')
        ->setCellValue('M1', 'Create Date');

$excelWriter->getActiveSheet()->getStyle('A1:M1')->getFont()->setBold(true);
$excelWriter->getActiveSheet()->getStyle('A1:M1')->getFont()->getColor()->setARGB('FFFFFFFF');
$excelWriter->getActiveSheet()->getStyle('A1:M1')->applyFromArray(
        array('fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('argb' => 'FF2F88F0')
            )
        )
);

for ($col = 'A'; $col !== 'M'; $col++)
{
    $excelWriter->getActiveSheet()
            ->getColumnDimension($col)
            ->setAutoSize(true);
}

$index = 2;

foreach ($AllAdmittedStudents as $StudentID => $Details)
{
    ++$index;
    
    $excelWriter->setActiveSheetIndex(0)
            ->setCellValueExplicit('A' . $index, $index-2, PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('B' . $index, $Details['EnrollmentID'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('C' . $index, $Details['StudentName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('D' . $index, $Details['ClassName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('E' . $index, $Details['FatherName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('F' . $index, $Details['MotherName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('G' . $index, $Details['Gender'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('H' . $index, $Details['Category'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('I' . $index, ($Details['AdmissionDate'] != '0000-00-00') ? date('d/m/Y', strtotime($Details['AdmissionDate'])) : '', PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('J' . $index, (($Details['RegistrationFee']) ? $Details['RegistrationFee'] : ''), PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('K' . $index, $Details['MobileNumber'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('L' . $index, $Details['CreateUserName'], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValueExplicit('M' . $index, date('d/m/Y', strtotime($Details['CreateDate'])), PHPExcel_Cell_DataType::TYPE_STRING);

    if ($index % 2 == 0)
    {
        $excelWriter->getActiveSheet()->getStyle('A' . $index . ':M' . $index)->applyFromArray(
                array('fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('argb' => 'FFE5ECF5')
                    )
                )
        );
    }
}

++$index;

$excelWriter->getActiveSheet()->getStyle('A1:M' . $index)->applyFromArray(
        array(
            'borders' => array(
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
            )
        )
);

$excelWriter->getActiveSheet()->setTitle('new_admission_list');
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$excelWriter->setActiveSheetIndex(0);

// Redirect output to a clientâ€™s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=new_admission_list.xls');
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