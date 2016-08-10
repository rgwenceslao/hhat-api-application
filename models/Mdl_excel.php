<?php
class mdl_excel extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
		$this->load->library('excel');
    }
	
	public function download_reports( $data = array() )
	{
		
		if(empty( $data )) return false;
		$letters = range('A', 'Z');
		$x = 0;
		foreach($data[0] as $key => $columns)
		{
			$contents[$letters[$x].'1'] = strtoupper(str_replace('_',' ',$key));
			$this->excel->getActiveSheet()->getColumnDimension($letters[$x])->setAutoSize(true);
			$this->excel->getActiveSheet()->getStyle($letters[$x].'1')->applyFromArray(array('fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'FFFF30'))));
			$x++;
		}

		$i = 2;
		foreach($data as $item)
		{
			$x = 0;
			foreach($item as $DataCell)
			{
				$contents[$letters[$x].$i] = $DataCell;
				$x++;
			}
			$i++;
		}
		$this->excel->getProperties()->setCreator("RocketSpin.ph");
		$this->excel->getProperties()->setLastModifiedBy("Ronald Wenceslao");
		$this->excel->getProperties()->setTitle("HHAT Report");
		$this->excel->getProperties()->setSubject("HHAT Report");
		$this->excel->getProperties()->setDescription("HHAT Mobile Application Report");
		$this->excel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
		$this->excel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);

		foreach( $contents as $key => $item )
		{
			$this->excel->getActiveSheet()->setCellValue($key, $item);
			
		}
		$filename = md5( time().rand( 0, 999999999 ) );
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		
		$ews2 = new \PHPExcel_Worksheet($ea, 'Summary');
		$ea->addSheet($ews2, 0);
		$ews2->setTitle('Summary');
		


		// START Ronald code here
		$dsl=array(
                new \PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$O$2', NULL, 1),
                new \PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$P$2', NULL, 1),
                
            );
		$xal=array(
                new \PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$A$3:$A$44', NULL, 42),
            );
		$dsv=array(
                new \PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$O$3:$O$44', NULL, 42),
                new \PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$P$3:$P$44', NULL, 42),
            );
		$ds=new \PHPExcel_Chart_DataSeries(
                    \PHPExcel_Chart_DataSeries::TYPE_LINECHART,
                    \PHPExcel_Chart_DataSeries::GROUPING_STANDARD,
                    range(0, count($dsv)-1),
                    $dsl,
                    $xal,
                    $dsv
                    );
		// END Ronald code here


		// Do your stuff here
		$writer = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
		// This line will force the file to download
		$writer->save('php://output');
	}
}






