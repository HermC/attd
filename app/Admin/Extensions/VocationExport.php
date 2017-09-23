<?php
namespace App\Admin\Extensions;

use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class VocationExport extends AbstractExporter
{
    public function export()
    {
        //$filename = $this->getTable().'.csv';

        // 这里获取数据
        $data = $this->getData();
        $sheetData = [];
        foreach ($data as $item){
        	/*$type = "";
        	switch ($item["type"]){
        		case 1:
        			$type = "全天";
        			break;
        		case 2:
        			$type = "上午";
        			break;
        		case 3:
        			$type = "下午";
        			break;
        	}*/
        	$new = [
				"请假人"=>$item["employee_name"],
				"部门"=>$item["depart_name"],
				"请假日期"=>Carbon::createFromFormat("Y-m-d H:i:s", $item["start_time"])->toDateString(),
				"请假类型"=>$item["rule"]["title"],
				//"请假方式"=>$type,
				"请假天数"=>$item["days"],
				"事由备注"=>$item["memo"],
        	];
        	$sheetData[] = $new;
        }
		Excel::create('请假记录', function($excel)use($sheetData) {
				
			    $excel->sheet('统计情况', function($sheet)use($sheetData){
			    	$sheet->fromArray($sheetData,null, 'A1', true);
			    });
			
		})->download('xls');
        
        // 根据上面的数据拼接出导出数据，
       // $output = '';

        // 在这里控制你想输出的格式,或者使用第三方库导出Excel文件
        /* $headers = [
            'Content-Encoding'    => 'UTF-8',
            'Content-Type'        => 'text/csv;charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]; */

        // 导出文件，
        ///response(rtrim($output, "\n"), 200, $headers)->send();

        exit;
    }
}