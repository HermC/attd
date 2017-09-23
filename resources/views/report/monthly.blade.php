<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
		<style>
			td{
				text-aligin:center;
			}
		</style>
	</head>
	<body>
	<table>
		<tr>
			<td rowspan="3"  align="center" valign="middle">
				序号
			</td>
			<td rowspan="3"  align="center" valign="middle">
				部门
			</td>
			<td rowspan="3"  align="center" valign="middle">
				姓名
			</td>	
			<td colspan="{{$daysOfMonth}}"  align="center" valign="middle">
				{{$month}}月
			</td>
			<td rowspan="3" align="center"  valign="middle">
				法定工作天数
			</td>
			<td rowspan="3"  align="center" valign="middle">
				正常工作天数
			</td>
			<td rowspan="3"  align="center" valign="middle">
				迟到天数
			</td>
			<td rowspan="3"  align="center" valign="middle">
				早退天数
			</td>
			<td rowspan="3"  align="center" valign="middle">
				缺勤天数
			</td>
			<td rowspan="3" align="center"  valign="middle">
				请假天数
			</td>
		</tr>
		<tr>
				<td></td>
				<td></td>
				<td></td>
				@for($i = 1; $i <= $daysOfMonth; $i++)
				<td align="center">
					{{$i}}
				</td>
				@endfor
				<td></td>
				<td></td>
				<td></td>
		</tr>
		<tr>
				<td></td>
				<td></td>
				<td></td>
				@for($i = 1; $i <= $daysOfMonth; $i++)
				<td align="center"  width="8">
					{{ getWeek($dt->month($month)->day($i)->dayOfWeek) }}
				</td>
				@endfor
				<td></td>
				<td></td>
				<td></td>
		</tr>
		@foreach($data as $item)
		<tr>
				<td  align="center"  >
					{{$item["序号"]}}
				</td>
				<td  align="center"  >
					{{$item["部门"]}}
				</td>
				<td  align="center"  >
					{{$item["姓名"]}}
				</td>
				@for($i = 1; $i <= $daysOfMonth; $i++)
				<td  align="center"  >
					{{$item[$i]}}
				</td>
				@endfor
				<td  align="center"  >
					{{$item["应出勤天数"]}}
				</td>
				<td  align="center"  >
					{{$item["实际出勤天数"]}}
				</td>
				<td  align="center"  >
					{{$item["迟到"]}}
				</td>
				<td  align="center"  >
					{{$item["早退"]}}
				</td>
				<td  align="center"  >
					{{$item["缺勤"]}}
				</td>
				<td  align="center"  >
					{{$item["病事假天数"]}}
				</td>
				
		</tr>
		@endforeach
	</table>

	</body>
    
	
</html>