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
											<label for="title" class="col-sm-2 control-label">选择部门</label>
											<div class="col-sm-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-pencil"></i></span>
													<select  name="departs"  class="form-control "  id="departs"  v-model="dep">
														@foreach($deps as $dep)
															<option value="{{ $dep->id }}">{{ $dep->name }}</option>
														@endforeach
													</select>
												</div>
											</div>
										</div>
										<div class="form-group 1">
											<label for="email" class="col-sm-2 control-label">选择时间</label>
											<div class="col-sm-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
													<select  name="time"  class="form-control "  id="time"  >
															<option value="1"  >一月</option>
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
										<input type="hidden" name="_token"  value="dZ6IBZuyg3pRhyI8FExCRpC20cejgHHtcaJeleX5">
										<div class="col-sm-2"></div>
										<div class="col-sm-2">
											<div class="btn-group pull-left">
												<button type="reset" class="btn btn-warning pull-right"  @click="loadChart">查询</button>
											</div>
										</div>
										<div class="col-sm-4"  v-if="isQueried">
											<div class="btn-group pull-right">
												<a type="submit" class="btn btn-info pull-right"  :href="excel"  target="_blank">获取报表</a>
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
