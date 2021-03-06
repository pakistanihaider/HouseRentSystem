{{extends file='adminLayout.tpl'}}
{{block name="header"}}
    <title>Manage Tabs</title>
{{/block}}
{{block name="content"}}
    <div class="outer">
        <div class="inner">
            {{*DataTables Grid Start Here*}}
            <div class="row ui-sortable">
                <div class="col-lg-12">
                    <div class="box">
                        <header>
                            <div class="icons">
                                <i class="fa fa-table"></i>
                            </div>
                            <h5>Manage Forms</h5>
                            <div style="float:right; margin-right:10px; margin-top: 5px;"><a title="" id="addNewTabFunc" data-original-title="" href="#addNewTabModal_ManageTabs" data-toggle="modal" class="btn btn-metis-5 btn-sm btn-grad btn-rect">Add New Form</a></div>
                        </header>
                        <div class="body" id="collapse4">
                            <table id="ManageTabs" class="table table-bordered table-condensed table-hover table-striped">
                                <thead>
                                <tr>
                                    <th>Tab ID</th>
                                    <th data-class="expand">TabName</th>
                                    <th data-hide="phone">Tab Order</th>
                                    <th data-hide="phone,tablet">Tab Desc</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {{*End of DataTables Grid Coding*}}
        </div>
    </div>

 {{*Edit Button Modal*}}
    <div id="editBtnModal_ManageTabs" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i style='color: #666666' class='fa fa-edit fa-fw fa-1x'></i>Edit</h4>
                </div>
                <div class="modal-body">

                    <div class="body collapse in" id="div-1">
                        <form class="form-horizontal" id="editTabModelForm">
                            <input type="hidden" id="tabID"> {{*This field is for hidden ID, as HiddenID will be needed to update the form*}}
                            <div class="form-group">
                                <label class="control-label col-lg-4" for="text1">Tab Name</label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control required" name="TabName" placeholder="Tab Name" id="tabName">
                                </div>
                            </div><!-- /.form-group -->
                            <div class="form-group">
                                <label class="control-label col-lg-4" for="pass1">Tab Order</label>
                                <div class="col-lg-8">
                                    <input type="text" data-placement="top" name="validNumber" placeholder="Tab Order" id="tabOrder" class="form-control required">
                                </div>
                            </div><!-- /.form-group -->
                            <div class="form-group">
                                <label class="control-label col-lg-4">Tab Desc</label>
                                <div class="col-lg-8">
                                    <textarea class="form-control" id="tabDesc" placeholder="Form CI Path"></textarea>
                                </div>
                            </div><!-- /.form-group -->
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="updateTabBtn">Update</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal --><!-- /#Edit Button Modal -->

    {{*Create New Tab Button Modal*}}
    <div id="addNewTabModal_ManageTabs" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i style='color: #666666' class='fa fa-edit fa-fw fa-1x'></i>Edit</h4>
                </div>
                <div class="modal-body">

                    <div class="body collapse in" id="div-1">
                        <form class="form-horizontal" id="createTabModelForm">
                            <div class="form-group">
                                <label class="control-label col-lg-4" for="text1">Tab Name</label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control required" name="TabName" placeholder="Tab Name" id="cTabName">
                                </div>
                            </div><!-- /.form-group -->
                            <div class="form-group">
                                <label class="control-label col-lg-4" for="pass1">Tab Order</label>
                                <div class="col-lg-8">
                                    <input type="text" data-placement="top" name="validNumber" placeholder="Tab Order" id="cTabOrder" class="form-control required">
                                </div>
                            </div><!-- /.form-group -->
                            <div class="form-group">
                                <label class="control-label col-lg-4">Tab Desc</label>
                                <div class="col-lg-8">
                                    <textarea class="form-control" id="cTabDesc" name="TabDesc" placeholder="Tab Desc"></textarea>
                                </div>
                            </div><!-- /.form-group -->
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="createTabBtn">Create</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal --><!-- /#Create New Tab Button Modal -->

{{/block}}
{{block name="scripts"}}
    {{js('datatables/fnReloadAjax.js')}}
    <script>
        /**
         * @var oTable will be Global variable.
         *
         **/
        var oTable;
        $(document).ready(function(e){
            oTable = '';
            //Data Tables Script Here.
            var selector = $('#ManageTabs');
            var url = "{{base_url()}}admin/configurations/listTabs_DT/";
            var aoColumns =  [
                /* ID */   {
                    "bVisible":    false,
                    "bSortable":   false,
                    "bSearchable": false
                },
                /* Tab Name */  null,
                /* Tab Order */  null,
                /* Tab Desc */  null,
                /* Actions */  null
            ];
            commonDataTables(selector,url,aoColumns);
            //End Of dataTables Script..

    {{* ------------------Code Related to Edit/Update Modal----------------------- *}}
            //Edit Button in DataTables
            $('#ManageTabs').on('click', '.editBtnFunc_ManageTabs', function(e){
                e.preventDefault();
                var tabID = $(this).closest('tr').attr('data-id');
                //console.log(FormID);

                $.ajax({
                    type:"post",
                    url:"{{base_url()}}admin/configurations/GetTabData/"+tabID,
                    dataType:"json",
                    success:function(response){
                        if(!($.isEmptyObject(response))){
                            $.each(response,function(key,value){
                                $("#tabName").val(value.TabName);
                                $("#tabOrder").val(value.TabOrder);
                                $("#tabDesc").val(value.TabDesc);
                            });
                        }
                        $("#tabID").val(tabID);
                    }
                }); //---  End of $.ajax  ---//
            });

            //Update Button in the Edit/Update Modal.
            $('#updateTabBtn').on('click', function(e){
                e.preventDefault();
                var editModalFormsSelector = $('#editTabModelForm');
                HRS.formValidation(editModalFormsSelector);
                if (editModalFormsSelector.valid()){
                    var formData = {
                        TabID :     $("#tabID").val(),
                        TabName :   $("#tabName").val(),
                        TabOrder :   $("#tabOrder").val(),
                        TabDesc : $("#tabDesc").val()
                    };
                    $.ajax({
                        type:"post",
                        url:"{{base_url()}}admin/configurations/UpdateTabData/",
                        data: formData,
                        success: function(output){
                            var data = output.split('::');
                            if (data[0] == "OK"){
                                oTable.fnReloadAjax();
                                HRS.notification(data[1],data[2]);
                            }
                        }
                    });
                    $('#editBtnModal_ManageTabs').modal('hide');
                }
                //console.log(FormName);
            });
   {{* ------------------End of Code Related to Edit/Update Modal----------------------- *}}


  {{* ------------------Code Related to Create new Tab Modal----------------------- *}}
            $("#addNewTabFunc").on('click', function(e){
                //$('.select2-container').css("width","100%");
            });

            $('#createTabBtn').on('click', function(e){
                //e.stopImmediatePropagation();
                e.preventDefault();
                var selector = $('#createTabModelForm');
                HRS.formValidation(selector);
                if(selector.valid()){
                    var formData = {
                        TabName : $("#cTabName").val(),
                        TabOrder : $("#cTabOrder").val(),
                        TabDesc :   $("#cTabDesc").val()
                    };
                    $.ajax({
                        type:"post",
                        url:"{{base_url()}}admin/configurations/addNewTab/",
                        data: formData,
                        success: function(output){
                            var data = output.split('::');
                            //console.log(data,data[0],data[1],data[2]);
                            if (data[0] == "OK"){
                                oTable.fnReloadAjax();
                                HRS.notification(data[1],data[2]);
                            }
                            else if(data[0] == "FAIL"){
                                HRS.notification(data[1],data[2]);
                            }
                        }
                    });
                    //Do Stuff After pressing the Create Button.
//                    Close the Modal
                    $('#addNewTabModal_ManageTabs').modal('hide');
//                    Reset All the TextBoxes and CheckBoxes
                    $("#createTabModelForm")[0].reset();
//                    Reset/Empty All the Select2 Dropdowns
                    //jQuery('.select2-offscreen').select2('val', '');
                }
                else{
                    //The Else Portion if you want Something else to Happen if not validated Form
                }

            });
     {{* ------------------End of Code Related to Create new Tab Modal----------------------- *}}

    {{* ------------------Code Related to Delete Tab from DB----------------------- *}}
            $('#ManageTabs').on('click', '.deleteBtnFunc', function(e){
                e.preventDefault();
                var tabID = $(this).closest('tr').attr('data-id');
                //console.log(FormID);

                $.ajax({
                    type:"post",
                    url:"{{base_url()}}admin/configurations/deleteTabData/"+tabID,
                    success: function(output){
                        var data = output.split('::');
                        if (data[0] == "OK"){
                            oTable.fnReloadAjax();
                            HRS.notification(data[1],data[2]);
                        }
                        else if(data[0]="FAIL")
                        {
                            HRS.notification(data[1],data[2]);
                        }
                    }
                }); //---  End of $.ajax  ---//

            });
    {{* ------------------End of Code Related to Delete Tab From DB----------------------- *}}
        });
    </script>
+{{/block}}