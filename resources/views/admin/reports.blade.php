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
						<div  class="form-horizontal"	 >
									<div class="box-body fields-group">
										<div class="form-group 1">
											<label for="title" class="col-sm-2 control-label">部门</label>
											<div class="col-sm-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-pencil"></i></span>
													<select  name="dep"  class="form-control "  id="dep"  >
														@foreach($deps as $dep)
															@if($dep->id==1&&Admin::user()->isAdministrator())
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
											<label for="email" class="col-sm-2 control-label">选择时间范围</label>
											<div class="col-sm-10">
										        <div class="row" style="width: 370px">
										            <div class="col-lg-6">
										                <div class="input-group">
										                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
										                    <input type="text" name="starttime"  id="starttime"  value="" class="form-control  starttime"  style="width: 150px"  />
										                </div>
										            </div>
										            <div class="col-lg-6">
										                <div class="input-group">
										                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
										                    <input type="text"  name="endtime"  id="endtime"  value="" class="form-control  endtime" style="width: 150px"  />
										                </div>
										            </div>
										        </div>
										    </div>
										<!-- <div class="col-sm-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
													<select  name="time"  class="form-control "  id="time"  >
															<option value="1"  >今天</option>
															<option value="2">最近三天</option>
															<option value="3">最近一周</option>
															<option value="4">最近两周</option>
															<option value="5">最近一个月</option>
															<option value="6">最近三个月</option>
													</select>
												</div>
											</div> -->
										</div>
									</div>
									<!-- /.box-body -->
									<div class="box-footer">
										<div class="col-sm-2"></div>
										<div class="col-sm-2">
											<div class="btn-group pull-left">
												<button type="reset" class="btn btn-warning pull-right"  onclick="query()">查询</button>
											</div>
										</div>
										<div class="col-sm-4"  v-if="isQueried">
											<div class="btn-group pull-right">
												<a  class="btn btn-info pull-right"  href="jascript:;"   id="print"  target="_blank" style="display:none">获取当前报表</a>
											</div>
										</div>
									</div>
								</div>
					</div>
				</div>
              <!-- /.row -->
            </div>
            <!-- /.box-body -->
            
            <!-- /.footer -->
</div>
<div class="box box-default"  id="overview-area" style="display:none">
            <div class="box-header with-border">
              <h3 class="box-title">概览</h3>
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
						                	<a data-toggle="collapse" data-parent="#accordion" href="#lateArea" class="collapsed" aria-expanded="false">迟到天数 <span class="pull-right text-green"><i class="fa fa-angle-down"></i> <span id="lateCount"></span>天</span></a>
						                	<div id="lateArea" class="panel-collapse collapse user-list" aria-expanded="false"   style="padding:10px 20px;">
						                		
						                	</div>
						                </li>
						                <li><a data-toggle="collapse" data-parent="#accordion" href="#earlyArea" class="collapsed" aria-expanded="false">早退天数 <span class="pull-right text-green"><i class="fa fa-angle-down"></i> <span id="earlyCount"></span>天</span></a>
						                	<div id="earlyArea" class="panel-collapse collapse user-list" aria-expanded="false"   style="padding:10px 20px;">
						                		
						                	</div>
						                </li>
						                <li><a data-toggle="collapse" data-parent="#accordion" href="#abcentArea" class="collapsed" aria-expanded="false">缺勤天数 <span class="pull-right text-green"><i class="fa fa-angle-down"></i> <span id="abcentCount"></span>天</span></a>
						                	<div id="abcentArea" class="panel-collapse collapse user-list" aria-expanded="false"   style="padding:10px 20px;">
						                		
						                	</div>
						                </li>
						                <li><a data-toggle="collapse" data-parent="#accordion" href="#outArea" class="collapsed" aria-expanded="false">外勤天数 <span class="pull-right text-green"><i class="fa fa-angle-down"></i><span id="outCount"></span>天</span></a>
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
