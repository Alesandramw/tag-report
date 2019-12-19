<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
session_start(); 
?>
<!DOCTYPE html>
<html>
<head>
  <title>PHP File Upload</title>
</head>
<body>
  <?php
    if (isset($_SESSION['message']) && $_SESSION['message'])
    {
		printf('<b>%s</b>', $_SESSION['message']);
		// printf('<b>%s</b>', $_SESSION['filename']);
		printf('<br/>');
		unset($_SESSION['message']);

		$txt_file = file_get_contents('./uploaded_files/'.$_SESSION['filename']);
		$rows = explode("\n", $txt_file);
		$tags = array();
		$details = array();
		$difference = array();
		$labelDisplay = '';
		$key = '';
		// $ttDisplay, $labelDisplay, $screen;
		foreach($rows as $row => $data)
		{
			// printf $data;
			// printf '<br/>';
			//get row data
			if (trim($data) == '' || empty($data)) {
				// echo $key;
				// printf '<br/>';
				if ($key != '') {
					// printf $key;
					// printf '<br />';
					// printf $labelDisplay;
					// printf '<br />';
					// printf $ttDisplay;
					// printf '<br />';
					if (!array_key_exists($key, $details)) { //Doesn't exist in details array
						$details[$key] = new stdClass;
						$details[$key]->screen = $screen;
						$details[$key]->label = $labelDisplay;
						$details[$key]->tooltip = $ttDisplay;
						// break;
					}
					else { //Exists in details array
						// echo $key;
						// echo '<br />';
						// echo 'label ' . $labelDisplay;
						// echo '<br />';
						// echo $ttDisplay;
						// echo '<br />';
						// printf '<br />';
						
						if ($details[$key]->label != $labelDisplay || $details[$key]->tooltip != $ttDisplay) {
							$tags[$key]->differences = $tags[$key]->differences + 1;
							if (!array_key_exists($key, $difference)) {
								$difference[$key] = array();
							}
							$temp = new stdClass();
							$temp->label = trim($labelDisplay);
							$temp->tooltip = trim($ttDisplay);
							$temp->screen = trim($screen);
							if ($details[$key]->label != $labelDisplay && $details[$key]->tooltip != $ttDisplay) {
								$temp->difference = 'Both';
							}
							else if ($details[$key]->label != $labelDisplay && $details[$key]->tooltip == $ttDisplay) {
								$temp->difference = 'Label';
							}
							else if ($details[$key]->label == $labelDisplay && $details[$key]->tooltip != $ttDisplay) {
								$temp->difference = 'Tooltip';
							}
							
							$found = false;
							foreach($difference as $keyvalue => $value) {
								foreach($value as $arrvalue) {
									if ($arrvalue->label == $labelDisplay && $arrvalue->tooltip == $ttDisplay) {
										$arrvalue->screen = $arrvalue->screen . ', ' . $screen;
										$found = true;
									}
								}
							}
							if (!$found) {
								array_push($difference[$key] , $temp);
							}
							
							
							// printf '<br />';
							// printf $key;
							// printf '<br />';
							// printf $tags[$key]->differences;
							// printf '<br />';
							// $labelDisplay = '';
							// $key = '';
							// $ttDisplay = '';
						}
					}
				}
				$labelDisplay = '';
				$key = '';
				$ttDisplay = '';
			}
			else if (strpos($data, 'View=') === 0) {
				$screen = substr($data, strpos($data, '=')+1);
				// printf $screen;
				// printf '<br />';
			}
			else if (strpos($data, 'StringId') === 0) {
				if (strpos($data, 'Analog') != false) {
					// printf strpos($data, 'Analog');
					// printf $data;
					// printf '<br/>';
					$key = trim(substr($data,strpos($data,'.')+1));
					if (!array_key_exists($key, $tags)) {
						$tags[$key] = new stdClass;
						$tags[$key]->instances = 1;
						$tags[$key]->differences = 0;
					}
					else {
						$tags[$key]->instances = $tags[$key]->instances + 1;
					}
				}
			}
			else if (strpos($data, 'TooltipDisplayText=') === 0 || strpos($data, 'ToolTipDisplayText=') === 0) {
				$ttDisplay = trim(substr($data, strpos($data, '=')+1));
			}
			else if (strpos($data, 'LabelDisplayText=') === 0) {
				$labelDisplay = trim(substr($data,strpos($data, '=')+1));
				// printf $labelDisplay;
			}
			// $counting++;
			// if ($counting == 10) {
				// break;
			// }
		}
		
		// foreach($tags as $keyvalue => $value) {
			// printf($keyvalue);
			// printf ('<br />');
			// printf ('	Instances: ' . $value->instances);
			// printf ('<br />');
			// printf ('	Differences: ' . $value->differences);
			// printf ('<br />');
			// printf ('<br />');
		// }
		foreach($difference as $keyvalue => $value) {
			echo $keyvalue;
			echo '<br />';
			printf ('	Instances: ' . $tags[$keyvalue]->instances);
			printf ('<br />');
			printf ('	Differences: ' . $tags[$keyvalue]->differences);
			printf ('<br />');
			printf ('	<strong>Master</strong> Label: ' . $details[$keyvalue]->label . ', Tooltip: ' . $details[$keyvalue]->tooltip);
			printf ('<br />');
			// print_r($value);
			foreach($value as $arrvalue) {
				print_r('<strong>Label</strong>: ' . $arrvalue->label . ' | ' . '<strong>Tooltip</strong>: ' . $arrvalue->tooltip . ' | ' . '<strong>Screen</strong>: ' . $arrvalue->screen . ' | ' . '<strong>Difference</strong>: ' . $arrvalue->difference);
				echo '<br />';
			}
			// echo $keyvalue;
			echo '<br />';
			echo '<br />';
		}
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$styleArray = [
			'alignment' => [
				'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
			]
		];
		$sheet->setCellValue('C1', 'Label');
		$sheet->setCellValue('D1', 'Tooltip');
		$sheet->setCellValue('E1', 'Screen');
		$sheet->setCellValue('F1', 'Difference');
		$sheet->getStyle('C1:F1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('C1:F1')->getFont()->setBold(true);
		$traverse = 2;
		
		foreach($difference as $keyvalue => $value) {
			$cellLabel = 'C' . $traverse;
			$cellTooltip = 'D' . $traverse;
			$cellScreen = 'E' . $traverse;
			$cellDifference = 'F' . $traverse;
			$sheet->setCellValue('A'.$traverse, $keyvalue);
			$sheet->setCellValue($cellLabel, $details[$keyvalue]->label);
			$sheet->setCellValue($cellTooltip, $details[$keyvalue]->tooltip);
			$sheet->setCellValue($cellScreen, $details[$keyvalue]->screen);
			$sheet->getStyle('A'.$traverse.':'.$cellDifference)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$traverse = $traverse + 1;
			foreach($value as $arrvalue) {
				$cellLabel = 'C' . $traverse;
				$cellTooltip = 'D' . $traverse;
				$cellScreen = 'E' . $traverse;
				$cellDifference = 'F' . $traverse;
				$sheet->setCellValue($cellLabel, $arrvalue->label);
				$sheet->setCellValue($cellTooltip, $arrvalue->tooltip);
				$sheet->setCellValue($cellScreen, $arrvalue->screen);
				$sheet->setCellValue($cellDifference, $arrvalue->difference);
				$styleArray = [
					'fill' => [
						'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
						'startColor' => [
							'argb' => 'ADD8E6'
						]
					],
				];
				$sheet->getStyle($cellLabel.':'.$cellDifference)->applyFromArray($styleArray);
				if ($arrvalue->difference == 'Both' || $arrvalue->difference == 'Tooltip') {
					$styleArray = [
						'fill' => [
							'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
							'startColor' => [
								'argb' => 'FFFF0000'
							]
						],
					];
					$sheet->getStyle($cellDifference.':'.$cellDifference)->applyFromArray($styleArray);
				}
				$sheet->getStyle('C'.$traverse.':'.$cellDifference)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
				$traverse = $traverse + 1;
			}
			$traverse = $traverse + 2;
		}
		
		// $styleArray = [
			// 'borders' => [
				// 'outline' => [
					// 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
					// 'color' => ['argb' => 'FFFF0000'],
				// ],
			// ],
		// ];

		$sheet->getStyle('C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getColumnDimension('A')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->getColumnDimension('D')->setWidth(52);
		$sheet->getColumnDimension('E')->setWidth(86);
		$sheet->getColumnDimension('F')->setAutoSize(true);

		$writer = new Xlsx($spreadsheet);
		$writer->save('testinginging.xlsx');
	}
  ?>
  <form method="POST" action="upload.php" enctype="multipart/form-data">
    <div>
      <span>Upload a File:</span>
      <input type="file" name="uploadedFile" />
    </div>

    <input type="submit" name="uploadBtn" value="Upload" />
  </form>
</body>
</html>