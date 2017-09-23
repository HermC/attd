<!-- Custom tabs (Charts with tabs)-->
  <!-- /.nav-tabs-custom -->
  <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title">查询统计条件</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
					<div class="col-md-12">
						<form  class="form-horizontal"	method="post"   action="/admin/overview/reports/monthly" >
									<div class="box-body fields-group">
										<div class="form-group 1">
											<label for="title" class="col-sm-2 control-label">选择部门</label>
											<div class="col-sm-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-pencil"></i></span>
													<select  name="dep"  class="form-control "  id="dep"  >
														@foreach($deps as $dep)
															@if($dep->id==1)
																<option value="0">所有部门 </option>
															@else
																<option value="{{ $dep->id }}">{{ $dep->name }}</option>
															@endif
															
														@endforeach
													</select>
												</div>
											</div>
										</div>
										<div class="form-group 1">
											<label for="email" class="col-sm-2 control-label">选择月份</label>
											<div class="col-sm-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
													<select  name="month"  class="form-control "  id="month" >
															<option value="1" >一月</option>
															<option value="2">二月</option>
															<option value="3">三月</option>
															<option value="4">四月</option>
															<option value="5">五月</option>
															<option value="6">六月</option>
															<option value="7">七月</option>
															<option value="8">八月</option>
															<option value="9">九月</option>
															<option value="10">十月</option>
															<option value="11">十一月</option>
															<option value="12">十二月</option>
													</select>
												</div>
											</div>
										</div>
									</div>
									<!-- /.box-body -->
									<div class="box-footer">
										{{ csrf_field() }}
										<div class="col-sm-2"></div>
										<div class="col-sm-4"  >
											<div class="btn-group pull-right">
												<button  class="btn btn-info pull-right"   id="print_monthly"  >获取月统计报表</button>
											</div>
										</div>
									</div>
								</form>
					</div>
				</div>
              <!-- /.row -->
            </div>
            <!-- /.box-body -->
            
            <!-- /.footer -->
</div>
<div class="box box-default"  id="overview-area" style="display:none">
            <div class="box-header with-border">
              <h3 class="box-title">人员统计信息</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row" >
                <div class="col-md-6">
                   <div class="nav-tabs-custom">
					            <!-- Tabs within a box -->
					            <ul class="nav nav-tabs pull-right">
					             <!--  <li ><a href="#sales-chart" data-toggle="tab"  >柱状图</a></li> -->
					              <li  class="active"><a href="#revenue-chart"  data-toggle="tab">饼状图</a></li>
					              <li class="pull-left header"><i class="fa fa-inbox"></i> 统计图</li>
					            </ul>
					            <div class="tab-content no-padding">
					              <div class="chart tab-pane active" id="revenue-chart" style="position: relative; height: 400px;">
					              		
					              </div>
					            </div>
					</div>
                  <!-- ./chart-responsive -->
                </div>
                <!-- /.col -->
                <div class="col-md-6">
                			<div class="box-footer no-padding">
						              <ul class="nav nav-pills nav-stacked">
						              	<li>
						                	<a data-toggle="collapse" data-parent="#accordion" href="#fullArea" class="collapsed" aria-expanded="false">全勤 <span class="pull-right text-green"><i class="fa fa-angle-down"></i> <span id="fullTime"></span>人</span></a>
						                	<div id="fullArea" class="panel-collapse collapse user-list" aria-expanded="false"   style="padding:10px 20px;">
						                		  
						                	</div>
						                </li>
						                <li>
						                	<a data-toggle="collapse" data-parent="#accordion" href="#lateArea" class="collapsed" aria-expanded="false">迟到次数 <span class="pull-right text-green"><i class="fa fa-angle-down"></i> <span id="lateCount"></span>人</span></a>
						                	<div id="lateArea" class="panel-collapse collapse user-list" aria-expanded="false"   style="padding:10px 20px;">
						                		
						                	</div>
						                </li>
						                <li><a data-toggle="collapse" data-parent="#accordion" href="#earlyArea" class="collapsed" aria-expanded="false">早退次数 <span class="pull-right text-green"><i class="fa fa-angle-down"></i> <span id="earlyCount"></span>人</span></a>
						                	<div id="earlyArea" class="panel-collapse collapse user-list" aria-expanded="false"   style="padding:10px 20px;">
						                		
						                	</div>
						                </li>
						                <li><a data-toggle="collapse" data-parent="#accordion" href="#abcentArea" class="collapsed" aria-expanded="false">缺勤次数 <span class="pull-right text-green"><i class="fa fa-angle-down"></i> <span id="abcentCount"></span>人</span></a>
						                	<div id="abcentArea" class="panel-collapse collapse user-list" aria-expanded="false"   style="padding:10px 20px;">
						                		
						                	</div>
						                </li>
						                <li><a data-toggle="collapse" data-parent="#accordion" href="#outArea" class="collapsed" aria-expanded="false">外勤次数 <span class="pull-right text-green"><i class="fa fa-angle-down"></i><span id="outCount"></span>人</span></a>
						                	<div id="outArea" class="panel-collapse collapse user-list" aria-expanded="false"   style="padding:10px 20px;">
						                		
						                	</div>
						                </li>
						              </ul>
					       </div>
                </div>
              </div>
              <!-- /.row -->
            </div>
            <!-- /.box-body -->
            <!-- /.footer -->
</div>
