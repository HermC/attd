<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stoneworld\Wechat\Group;
use App\Models\Department;
use App\Models\Employee;

class ApiController extends Controller
{
	
	
	public function employee(Request $request)
	{
		$q = $request->get('q');
	
		return Employee::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
	}
	
	public function syncRemote(Request $request)
	{
		$type = $request->get('type');
		$appId  = config("ent.appId");
		$secret = config("ent.secret");
		
		switch ($type){
			case 1:
				Department::truncate();
				$group = new Group($appId, $secret);
				$gs = $group->lists(0);
				foreach ($gs as $gvo ){
					//首先检查id是否存在，不存在生成
					/* $dep = Department::find($gvo["id"]);
					if($dep){
						$dep->name=
					}else{
						
					} */
					Department::create([
						"id"=>$gvo["id"],
						"name"=>$gvo["name"],
						"parentid"=>$gvo["parentid"],
						"order"=>$gvo["order"],
					]);
				}
				return ["res"=>1];
			break;
			case 2:
				Employee::truncate();
				$em = new Employee();
				$em->synchFromRemote();
				return ["res"=>1];
				/* try{
					
				}catch(\Exception $ex){
					//dd($ex);
					//return ["res"=>0,"msg"=>$ex->message];
				} */
				
			break;
			default:
			break;
		}
		//return CarType::where('exhibition_id', $q)->get(['id', \DB::raw('title as text')]);
	}
    	
}
