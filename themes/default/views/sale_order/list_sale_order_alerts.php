<?php 
	
	$v = "";
	/* if($this->input->post('name')){
	  $v .= "&product=".$this->input->post('product');
	  } */
	if ($this->input->post('reference_no')) {
		$v .= "&reference_no=" . $this->input->post('reference_no');
	}
	if ($this->input->post('customer')) {
		$v .= "&customer=" . $this->input->post('customer');
	}
	if ($this->input->post('saleman')) {
		$v .= "&saleman=" . $this->input->post('saleman');
	}
	if ($this->input->post('biller')) {
		$v .= "&biller=" . $this->input->post('biller');
	}
	if ($this->input->post('warehouse')) {
		$v .= "&warehouse=" . $this->input->post('warehouse');
	}
	if ($this->input->post('user')) {
		$v .= "&user=" . $this->input->post('user');
	}
	if ($this->input->post('serial')) {
		$v .= "&serial=" . $this->input->post('serial');
	}
	if ($this->input->post('start_date')) {
		$v .= "&start_date=" . $this->input->post('start_date');
	}
	if ($this->input->post('end_date')) {
		$v .= "&end_date=" . $this->input->post('end_date');
	}
	if ($this->input->post('product_id')) {
		$v .= "&product_id=" . $this->input->post('product_id');
	}
	if(isset($date)){
		$v .= "&d=" . $date;
	}
	
?>
<style type="text/css">
    .order_invoice_link {
        cursor: pointer;
    }
</style>
<script>
    $(document).ready(function () {
        var oTable = $('#SLData').dataTable({
			"aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
			'bServerSide': true,
			"bStateSave": true,
			"fnStateSave": function (oSettings, oData) {
				__setItem('DataTables_' + window.location.pathname, JSON.stringify(oData));
			},
			"fnStateLoad": function (oSettings) {
				var data = __getItem('DataTables_' + window.location.pathname);
				return JSON.parse(data);
			},
            'sAjaxSource': '<?=site_url('sale_order/getSaleOrderAlerts' . ($warehouse_id ? '/' . $warehouse_id : '')).'/?v=1'.$v?>',
			"bAutoWidth": false ,
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                //$("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                
				var authorize = aData[11];
				
				var action = $('td:eq(12)', nRow);
				if(authorize == 'pending') {
					action.find('.unapproved').remove();
					action.find('.add').remove();
					// action.find('.edit').remove();
                    action.find('.delete').remove();
                    action.find('.adeposit').remove();
					action.find('.vdeposit').remove();
				}else if(authorize == 'completed') {
					action.find('.approved').remove();
                    action.find('.edit').remove();
				}else{
					action.find('.rejected').remove();
				}
				
				
				if(aData[7] != 'order' && aData[7] != 'delivery'){
					action.find('.unapproved').remove();
					action.find('.rejected').remove();
				}
                if (aData[7] == 'sale') {
                    action.find('.add').remove();
                    action.find('.edit').remove();
                    action.find('.delete').remove();
                    action.find('.adeposit').remove();
                }
                if (aData[9] == 'completed') {
                    action.find('.edit').remove();
                    action.find('.delete').remove();
					action.find('.assign').remove();
                }
				if (aData[11] == 'rejected')
				{
					action.find('.assign').remove();
					action.find('.add').remove();
					action.find('.edit').remove();
                    action.find('.delete').remove();
					action.find('.adeposit').remove();
                    action.find('.vdeposit').remove();
				}
                if (aData[9] > 0) {
                    action.find('.unapproved').remove();
                    action.find('.rejected').remove();
                }
				
				nRow.id = aData[0];
					nRow.className = "order_invoice_link";
				/*
				if(aData[5] == 'returned'){
					nRow.id = aData[0];
					nRow.className = "return_link";
				}else{
					nRow.id = aData[0];
					nRow.className = "invoice_link";
				}
				*/
                
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
				
				
                return nRow;
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, {"mRender": fld}, null, null, null, null, null, {"mRender":  issue_status }, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": so_status}/* , {"bSortable": false} */],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
					gtotal += parseFloat(aaData[aiDisplay[i]][8]);
					//paid += parseFloat(aaData[aiDisplay[i]][9]);
					//balance += parseFloat(aaData[aiDisplay[i]][10]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[8].innerHTML = currencyFormat(parseFloat(gtotal));
                //nCells[9].innerHTML = currencyFormat(parseFloat(paid));
                //nCells[10].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('quotation_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('so_no');?>]", filter_type: "text", data: []},
            
            {column_number: 4, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('saleman');?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('order_status');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('deposit');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('balance');?>]", filter_type: "text", data: []},
			{column_number: 11, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
            
        ], "footer");
		
		function so_status(x) {
			if(x == 'pending') {
				return '<div class="text-center"><span class="label label-warning">'+lang['order']+'</span></div>';
			}else if(x == 'completed') {
				return '<div class="text-center"><span class="label label-success">'+lang['approved']+'</span></div>';
			}else {
				return '<div class="text-center"><span class="label label-danger">'+lang['rejected']+'</span></div>';
			}
		}
		function issue_status(x) {
			if(x == 'order') {
				return '<div class="text-center"><span class="label label-warning">'+lang['sale_order']+'</span></div>';
			}else if(x == 'sale') {
				return '<div class="text-center"><span class="label label-success">'+lang['sale']+'</span></div>';
			}else if(x == 'purchase_order') {
				return '<div class="text-center"><span class="label label-success">'+lang['purchase_order']+'</span></div>';
			}else if(x == 'purchase') {
				return '<div class="text-center"><span class="label label-success">'+lang['purchase']+'</span></div>';
			}else {
				return '<div class="text-center"><span class="label label-success">'+lang['delivery']+'</span></div>';
			}
		}
		
        if (__getItem('remove_slls')) {
            if (__getItem('slitems')) {
                __removeItem('slitems');
            }
            if (__getItem('sldiscount')) {
                __removeItem('sldiscount');
            }
            if (__getItem('sltax2')) {
                __removeItem('sltax2');
            }
            if (__getItem('slref')) {
                __removeItem('slref');
            }
            if (__getItem('slshipping')) {
                __removeItem('slshipping');
            }
            if (__getItem('slwarehouse')) {
                __removeItem('slwarehouse');
            }
            if (__getItem('slnote')) {
                __removeItem('slnote');
            }
            if (__getItem('slinnote')) {
                __removeItem('slinnote');
            }
            if (__getItem('slcustomer')) {
                __removeItem('slcustomer');
            }
            if (__getItem('slbiller')) {
                __removeItem('slbiller');
            }
            if (__getItem('slcurrency')) {
                __removeItem('slcurrency');
            }
            if (__getItem('sldate')) {
                __removeItem('sldate');
            }
            if (__getItem('slsale_status')) {
                __removeItem('slsale_status');
            }
            if (__getItem('slpayment_status')) {
                __removeItem('slpayment_status');
            }
            if (__getItem('paid_by')) {
                __removeItem('paid_by');
            }
            if (__getItem('amount_1')) {
                __removeItem('amount_1');
            }
            if (__getItem('paid_by_1')) {
                __removeItem('paid_by_1');
            }
            if (__getItem('pcc_holder_1')) {
                __removeItem('pcc_holder_1');
            }
            if (__getItem('pcc_type_1')) {
                __removeItem('pcc_type_1');
            }
            if (__getItem('pcc_month_1')) {
                __removeItem('pcc_month_1');
            }
            if (__getItem('pcc_year_1')) {
                __removeItem('pcc_year_1');
            }
            if (__getItem('pcc_no_1')) {
                __removeItem('pcc_no_1');
            }
            if (__getItem('cheque_no_1')) {
                __removeItem('cheque_no_1');
            }
            if (__getItem('slpayment_term')) {
                __removeItem('slpayment_term');
            }
            __removeItem('remove_slls');
        }

        <?php if ($this->session->userdata('remove_slls')) {?>
        if (__getItem('slitems')) {
            __removeItem('slitems');
        }
        if (__getItem('sldiscount')) {
            __removeItem('sldiscount');
        }
        if (__getItem('sltax2')) {
            __removeItem('sltax2');
        }
        if (__getItem('slref')) {
            __removeItem('slref');
        }
        if (__getItem('slshipping')) {
            __removeItem('slshipping');
        }
        if (__getItem('slwarehouse')) {
            __removeItem('slwarehouse');
        }
        if (__getItem('slnote')) {
            __removeItem('slnote');
        }
        if (__getItem('slinnote')) {
            __removeItem('slinnote');
        }
        if (__getItem('slcustomer')) {
            __removeItem('slcustomer');
        }
        if (__getItem('slbiller')) {
            __removeItem('slbiller');
        }
        if (__getItem('slcurrency')) {
            __removeItem('slcurrency');
        }
        if (__getItem('sldate')) {
            __removeItem('sldate');
        }
        if (__getItem('slsale_status')) {
            __removeItem('slsale_status');
        }
        if (__getItem('slpayment_status')) {
            __removeItem('slpayment_status');
        }
        if (__getItem('paid_by')) {
            __removeItem('paid_by');
        }
        if (__getItem('amount_1')) {
            __removeItem('amount_1');
        }
        if (__getItem('paid_by_1')) {
            __removeItem('paid_by_1');
        }
        if (__getItem('pcc_holder_1')) {
            __removeItem('pcc_holder_1');
        }
        if (__getItem('pcc_type_1')) {
            __removeItem('pcc_type_1');
        }
        if (__getItem('pcc_month_1')) {
            __removeItem('pcc_month_1');
        }
        if (__getItem('pcc_year_1')) {
            __removeItem('pcc_year_1');
        }
        if (__getItem('pcc_no_1')) {
            __removeItem('pcc_no_1');
        }
        if (__getItem('cheque_no_1')) {
            __removeItem('cheque_no_1');
        }
        if (__getItem('slpayment_term')) {
            __removeItem('slpayment_term');
        }
        <?php $this->erp->unset_data('remove_slls');}
        ?>

        $(document).on('click', '.sledit', function (e) {
            if (__getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
    });

</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
        $("#product").autocomplete({
            source: '<?= site_url('reports/suggestions'); ?>',
            select: function (event, ui) {
                $('#product_id').val(ui.item.id);
                //$(this).val(ui.item.label);
            },
            minLength: 1,
            autoFocus: false,
            delay: 300,
        });
    });
</script>
<?php
    echo form_open('sale_order/sale_order_actions'.($warehouse_id ? '/'.$warehouse_id : ''), 'id="action-form"');
?>

<div class="box">
    <div class="box-header">

        <!-- <h2 class="blue">
			<i class="fa-fw fa fa-heart"></i><?=lang('sale_order_list') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')';?>
        </h2> -->
        <?php if ($warehouse_id) { ?>
            <h2 class="blue">
                <i class="fa-fw fa fa-barcode"></i>
                <?= lang('sale_order_list'); ?>
                (
                    <?php
                        if (count($warehouse) > 1) {
                            echo lang('all_warehouses');
                        } else {
                            foreach ($warehouse as $ware) {
                                echo $ware->name;
                            }
                            echo $warehouse->name;
                        }
                    ?>
                )
            </h2>
        <?php } else { ?>
            <h2 class="blue">
                <i class="fa-fw fa fa-barcode"></i>
                <?= lang('sale_order_list') . ' (' . lang('all_warehouses') . ')'; ?>
            </h2>
        <?php } ?>

		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
            <?php if ($Owner || $Admin || $GP['sale_order-add'] || $GP['sale_order-export'] || $GP['sale_order-combine_pdf']) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
						<?php if ($Owner || $Admin || $GP['sale_order-add']) { ?>
							<li>
								<a href="<?=site_url('sale_order/add_sale_order')?>">
									<i class="fa fa-plus-circle"></i> <?=lang('add_sale_order')?>
								</a>
							</li>
						<?php } ?>
						<?php if ($Owner || $Admin || $GP['sale_order-export']) { ?>
							<li>
								<a href="#" id="excel" data-action="export_excel">
									<i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
								</a>
							</li>
							<li>
								<a href="#" id="pdf" data-action="export_pdf">
									<i class="fa fa-file-pdf-o"></i> <?=lang('export_to_pdf')?>
								</a>
							</li>
						<?php } ?>
						<!-- <?php if($Owner || $Admin || $GP['sale_order-import']) { ?>
							<li>
								<a href="<?= site_url('sale_order/sale_by_csv'); ?>">
									<i class="fa fa-plus-circle"></i>
									<span class="text"> <?= lang('add_sale_by_csv'); ?></span>
								</a>
							</li>
						<?php }?> -->
						<?php if($Owner || $Admin || $GP['sale_order-combine_pdf']) { ?>
							<li>
								<a href="#" id="combine" data-action="combine">
									<i class="fa fa-file-pdf-o"></i> <?=lang('combine_to_pdf')?>
								</a>
							</li>
						<?php }?>
						
						<!-- <?php if ($Owner || $Admin || $GP['sale_order-delete']) { ?>
							<li class="divider"></li>
							<li>
								<a href="#" class="bpo"
								title="<?=$this->lang->line("delete_sales")?>"
								data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
								data-html="true" data-placement="left">
								<i class="fa fa-trash-o"></i> <?=lang('delete_sales')?>
								</a>
							</li>
						<?php } ?> -->
                    </ul>
                </li>
            <?php } ?>
                <?php /* if (!empty($warehouses)) {
                    ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?=lang("projects")?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?=site_url('sale_order/list_sale_order')?>"><i class="fa fa-building-o"></i> <?=lang('all_projects')?></a></li>
                            <li class="divider"></li>
                            <?php
                            	foreach ($warehouses as $warehouse) {
                            	        echo '<li><a href="' . site_url('sale_order/list_sale_order/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            	    }
                                ?>
                        </ul>
                    </li>
                <?php } */
                ?>
            </ul>
        </div>
    </div>

    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?= form_close()?>

	<div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?=lang('list_results');?></p>
				<div id="form">

                    <?php echo form_open("sale_order/list_sale_order"); ?>
                    <div class="row">
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="product_id"><?= lang("product"); ?></label>
                                <?php
                                $pr[""] = "";
                                foreach ($products as $product) {
                                    $pr[$product->id] = $product->name . " | " . $product->code ;
                                }
                                echo form_dropdown('product_id', $pr, (isset($_POST['product_id']) ? $_POST['product_id'] : ""), 'class="form-control" id="product_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("product") . '"');
								
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
						
						<div class="col-md-4">
							<div class="form-group">
							<?= lang("saleman", "saleman"); ?>
								<?php 
									$salemans['0'] = lang("all");
									foreach($agencies as $agency){
										$salemans[$agency->id] = $agency->username;
									}
									echo form_dropdown('saleman', $salemans, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'id="saleman" class="form-control saleman"');
								?>
							</select>
							<?php
							/*$sm[''] = '';
							foreach($agencies as $agency){
								$sm[$agency->id] = $agency->username;
							}
							echo form_dropdown('saleman', $sm, (isset($_POST['saleman']) ? $_POST['saleman'] : ''), 'id="slsaleman" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("saleman") . '" style="width:100%;" ');*/
							?>
							</div>
						</div>
						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = "";
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="project"><?= lang("project"); ?></label>
                                <?php
                                if ($Owner || $Admin) {
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                } else {
                                    $user_pro[""] = "";
                                    foreach ($user_billers as $user_biller) {
                                        $user_pro[$user_biller->id] = $user_biller->company;
                                    }
                                    echo form_dropdown('project', $user_pro, (isset($_POST['project']) ? $_POST['project'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("project") . '"');
                                }
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = "";
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        <?php if($this->Settings->product_serial) { ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('serial_no', 'serial'); ?>
                                    <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?php echo $this->lang->line("date"); ?></th>
							<th><?php echo $this->lang->line("quotation_no"); ?></th>
                            <th><?php echo $this->lang->line("so_no"); ?></th>
                            <th><?php echo $this->lang->line("project"); ?></th>
                            <th><?php echo $this->lang->line("customer"); ?></th>
							<th><?php echo $this->lang->line("saleman"); ?></th>
							<th><?php echo $this->lang->line("order_status"); ?></th>
                            <th><?php echo $this->lang->line("grand_total"); ?></th>
                            <th><?php echo $this->lang->line("deposit"); ?></th>
                            <th><?php echo $this->lang->line("balance"); ?></th>
                            <th><?php echo $this->lang->line("status"); ?></th>
                           <!-- <th style="width:80px; text-align:center;"><?php echo $this->lang->line("actions"); ?></th>-->
						</tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="13"
                                class="dataTables_empty"><?php echo $this->lang->line("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
							<th></th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
                            <th></th>
                            <!--<th style="width:80px; text-align:center;"><?php echo $this->lang->line("actions"); ?></th>-->
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
