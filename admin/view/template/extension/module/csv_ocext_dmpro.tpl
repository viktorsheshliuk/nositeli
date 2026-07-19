<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
              <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
              <?php foreach ($breadcrumbs as $breadcrumb) { ?>
              <li><a  href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
              <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
    <style>
        
        .ocext_loading{
            width: 100% !important;;
            height: 2px;
        }
        
        small {
            color: darkcyan !important;
            font-size: 10px !important;
            font-weight: bolder !important;
        }
        .small_text{
            font-size: 9px;
            color: darkgray;
        }
        .small_text:hover{
            font-size: 9px;
            color: black;
        }
        .table_zebra tbody tr td:nth-child(2n+1){
           background: lemonchiffon;
        }
        .table_zebra tbody tr td:nth-child(2){
           background: none;
        }
        
        .alert-box{
            margin-bottom: 5px;
            margin-top: 5px;
            display: none;
        }
        
        .table-abc-analysis tr td:first-child{
            width: 25%;
        }
        
        .field-file{
            font-size: 15px;
            font-weight: bold;
            color: white;
            padding: 5px;
            text-align: center;
            background: #444;
        }
        
        optgroup{
            border-bottom: 1px solid #ccc;
            color:#bbb;
        }
        optgroup option{
            color:#444;
        }
        
        .error-border{
            border:3px solid red;
            background: bisque;
        }
        
        
        .setTemplateDataBtn{
            border:1px solid #dddddd;
            background: #bbbbbb;
        }
        
        .setTemplateDataBtnNeedSave{
            border:1px solid brown;
            background: red;
        }
        
        .field-view-file-data{
            font-size: 9px; color:#bbb;
        }
        
        .field-view-file-data:hover{
            background: white;
            color: black;
        }
        
        .info-box-modal, .info-box-modal2{
        
            background: aliceblue;
            color: #1abc9c;
            padding: 10px;
            max-width: 600px;
            width: auto;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }       
        
        .info-box-modal2{
        
            max-width: 100%;
            margin-bottom: 7px;
            
        }
        
        .arrow{
            width: 0px;
            height: 0px;
            border: 5px solid transparent;
            /*border-top-color:orangered;*/
            margin: 0;
            padding: 0;
            float: left;
        }
        
        .arrow:before{
          content:'';
          width: 0px;
          height: 0px;
          border: 10px solid transparent;
          border-top-color: #888;
          display: inline-block;
          -webkit-transform: translate(20px, -33px);
        }
        .arrow.down{
            transform: rotate(0deg) translate(0px, 25px);
            -webkit-transform: rotate(0deg) translate(0px, 25px);
            -moz-transform: rotate(0deg) translate(0px, 25px);
            -o-transform: rotate(0deg) translate(0px, 25px);
            -ms-transform: rotate(0deg) translate(0px, 25px);
          }
          
          .vert_text {
            -webkit-transform: rotate(-90deg); /* не забываем префиксные свойства */
            -moz-transform: rotate(-90deg);
            -ms-transform: rotate(-90deg);
            -o-transform: rotate(-90deg);
            transform: rotate(-90deg);
          }
          
          #check_row_info{
          
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 0px;
          
          }
          .sh-tab a, .sh-tab a h2{
            background: #1abc9c !important;
            color: black;
            font-size: 18px;
            margin-bottom: 0px;
            border-color: #1abc9c !important;
          }
          
          .sh-tab h2{
              color: white !important;;
          }
          
          .sh-tab-n {
              border-bottom: 3px solid #1abc9c;
          }
          span[data-toggle="tooltip"]:after{
            font-family: FontAwesome;
            color: #1E91CF;
            content: "\f059";
            margin-left: 4px;
          }
          
          .help-box{
          
              padding: 10px;
              font-size: 12px;
              background: ivory;
          
          }
          
          .box-help-csv{
              border: 1px solid #888;
              background: white;
              padding: 3px;
              margin-top: 5px;
              margin-bottom: 5px;
          }
          
          .additional_forms h5{
          
             
          
          }
          
          span[data-toggle="tooltip"]:after {
          
                color: #1abc9c !important;
          
          }
          
          .btn-primary{
              background: #1abc9c !important;
              color: white;
              border-color: lightseagreen;
          }
          
          .fsz14 {
              font-size: 13px !important;
              font-weight: bold;
          }
          
          .alert-danger{
              background: red; color: white;
              border-color: red;
          }

          .alert-info{
              background: orange; color: white;
              border-color: orange;
          }
          
          .orangec, .orangec:hover, .orangec:active, .btn-danger, .btn-danger:hover, .btn-danger:active{
          
               background: orange !important;
               color: white;
               border-color: orange;
          
          }
          
          .alert-success{
              background: forestgreen; color: white;
              border-color: forestgreen;
          }
          
          .bar {
                    height:20px;
                    width:200px;
                    padding:10px;
                    margin:200px auto 0;
                    background-color:rgba(0,0,0,.1);
                    -webkit-border-radius:25px;
                    -moz-border-radius:25px;
                    -ms-border-radius:25px;
                    border-radius:20px;
                    -webkit-box-shadow:0 1px 0 rgba(255,255,255,.03),inset 0 1px 0 rgba(0,0,0,.1);
                    -moz-box-shadow:0 1px 0 rgba(255,255,255,.03),inset 0 1px 0 rgba(0,0,0,.1);
                    -ms-box-shadow:0 1px 0 rgba(255,255,255,.03),inset 0 1px 0 rgba(0,0,0,.1);
                    box-shadow:0 1px 0 rgba(255,255,255,.03),inset 0 1px 0 rgba(0,0,0,.1);
            }

          
          .bar span {
                display:inline-block;
                height:100%;
                width:100%;
                border:1px solid #ff9a1a;
                border-bottom-color:#ff6201;
                background-color:#d3d3d3;
                -webkit-border-radius:20px;
                -moz-border-radius:20px;
                -ms-border-radius:20px;
                border-radius:20px;
                -webkit-box-sizing:border-box;
                -moz-box-sizing:border-box;
                -ms-box-sizing:border-box;
                box-sizing:border-box;
                background-image:
                        -webkit-linear-gradient(
                        -45deg,
                        rgba(255, 154, 26, 1) 25%,
                        transparent 25%,
                        transparent 50%,
                        rgba(255, 154, 26, 1) 50%,
                        rgba(255, 154, 26, 1) 75%,
                        transparent 75%,
                        transparent
                );
                background-image:
                        -moz-linear-gradient(
                        -45deg,
                        rgba(255, 154, 26, 1) 25%,
                        transparent 25%,
                        transparent 50%,
                        rgba(255, 154, 26, 1) 50%,
                        rgba(255, 154, 26, 1) 75%,
                        transparent 75%,
                        transparent
                );
                background-image:
                        -ms-linear-gradient(
                        -45deg,
                        rgba(255, 154, 26, 1) 25%,
                        transparent 25%,
                        transparent 50%,
                        rgba(255, 154, 26, 1) 50%,
                        rgba(255, 154, 26, 1) 75%,
                        transparent 75%,
                        transparent
                );
                background-image:
                        linear-gradient(
                        -45deg,
                        rgba(255, 154, 26, 1) 25%,
                        transparent 25%,
                        transparent 50%,
                        rgba(255, 154, 26, 1) 50%,
                        rgba(255, 154, 26, 1) 75%,
                        transparent 75%,
                        transparent
                );
                -webkit-background-size:50px 50px;
                -moz-background-size:50px 50px;
                -ms-background-size:50px 50px;
                background-size:50px 50px;
                -webkit-animation:move 2s linear infinite;
                -moz-animation:move 2s linear infinite;
                -ms-animation:move 2s linear infinite;
                animation:move 2s linear infinite;
                -webkit-border-radius:20px;
                -moz-border-radius:20px;
                -ms-border-radius:20px;
                border-radius:20px;
                overflow: hidden;
                -webkit-box-shadow:inset 0 10px 0 rgba(255,255,255,.2);
                -moz-box-shadow:inset 0 10px 0 rgba(255,255,255,.2);
                -ms-box-shadow:inset 0 10px 0 rgba(255,255,255,.2);
                box-shadow:inset 0 10px 0 rgba(255,255,255,.2);
        }

        .bar > span:after {
                display: none;
        }

        /*
        Animate the stripes
        */	
        @-webkit-keyframes move{
          0% {
                background-position: 0 0;
          }
          100% {
                background-position: 50px 50px;
          }
        }	
        @-moz-keyframes move{
          0% {
                background-position: 0 0;
          }
          100% {
                background-position: 50px 50px;
          }
        }	
        @-ms-keyframes move{
          0% {
                background-position: 0 0;
          }
          100% {
                background-position: 50px 50px;
          }
        }	
        @keyframes move{
          0% {
                background-position: 0 0;
          }
          100% {
                background-position: 50px 50px;
          }
        }
        
    </style>
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
          <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <?php if ($success) { ?>
        <div class="alert alert-success"><i class="fa fa-exclamation-circle"></i> <?php echo $success; ?>
          <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        
    <div class="panel panel-default">
    
    <div class="panel-body">
        
        <ul  class="nav nav-tabs" >
            <li><a onclick="$('#stepOneSettings_export').empty(); $('#stepTwoSettings_export').empty();$('select[name=\'odmpro_format_data\']').val('0'); getStepOneSettings('csv',0,'import');" data-toggle="tab"  href="#tab_csv_import" ><?php echo $tab_csv_import; ?></a></li>
            
            <li><a onclick="$('#stepOneSettings_import').empty(); $('#stepTwoSettings_import').empty();$('select[name=\'odmpro_format_data\']').val('0'); getStepOneSettings('csv',0,'export');" data-toggle="tab"  href="#tab_csv_export" ><?php echo $tab_csv_export; ?></a></li>
            <?php
                
                if(isset($setting_version_functional['automatization_by_link'])){
                
                    ?>
                        <li><a  data-toggle="tab" href="#tab-setting"  ><?php echo $tab_setting; ?></a></li>
                    <?php
                
                }
                
            ?>
            
            <li onclick="getWelcomeWindow();"><a  data-toggle="tab" href="#tab-welcome-extecom"  ><?php echo $tab_welcome_extecom; ?></a></li>
        </ul>
        
        <div class="tab-content">
            <div id="tab_csv_import" class="tab-pane" >
                <div class="row">
                    <div class="col-sm-12">				
                        <div class="tab-content">
                            
                                <ul  class="nav nav-tabs sh-tab-n" >
                                    <li class="active sh-tab"><a onclick="showHide('.stepOneTempl');" style="cursor: pointer"><h2>Шаг 1. Настройка входных данных</h2></a></li>
                                </ul>
                            <form id="tamplate_data_form_import"  enctype="multipart/form-data" method="post">
                                    <div class="table-responsive">
                                    <table class="table table-bordered table-hover stepOneTempl_last" style="display: none">
                                          <tbody>
                                                <tr>
                                                    <td class="text-left" width="25%">
                                                        <?php echo $entry_odmpro_format_data ?>
                                                    </td>
                                                    <td class="text-left">
                                                        <div class="panel-body">
                                                            <div class="form-group">
                                                                <select onchange="/*getStepOneSettings(this.value,0,'import');*/" name="odmpro_format_data"  class="form-control">
                                                    <option value="csv" ><?php echo $entry_select; ?></option>
                                                                    <?php foreach($odmpro_format_data as $odmpro_format_data_name => $odmpro_format_data_row){ ?>
                                                    <option value="<?php echo $odmpro_format_data_row ?>" ><?php echo $odmpro_format_data_name; ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left" colspan="2" id="stepOneSettings_import_last">

                                                    </td>
                                                </tr>
                                            </tbody>
                                    </table>
                                    </div>
                                    <div  id="stepOneSettings_import" class="stepOneTempl" style="margin-bottom: 25px;">

                                    </div>
                                    <div  id="stepTwoSettings_import">

                                    </div>
                                </form>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div id="tab_csv_export" class="tab-pane" >
                    <div class="row">
                        <div class="col-sm-12">				
                            <div class="tab-content">
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                                    <?php if($entry_odmpro_format_data_empty){ ?>
                                    <div class="alert alert-success">
                                        <?php echo $entry_odmpro_format_data_empty ?>
                                    </div>
                                    <?php }elseif($text_lic_error){ ?>
                                    
                                    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $text_lic_error; ?>
                                      <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    </div>
                                    
                                    <?php }else{ ?>
                                    
                                    
                                    
                                    <ul  class="nav nav-tabs sh-tab-n" >
                                        <li class="active sh-tab"><a onclick="showHide('#stepOneSettings_export');" style="cursor: pointer"><h2><?php echo $text_step_4_setting ?></h2></a></li></ul>
                                    
                                    <form id="tamplate_data_form_export" enctype="multipart/form-data" method="post">
                                    
                                        <div  id="stepOneSettings_export" style="margin-bottom: 25px;">

                                        </div>
                                        <div  id="stepTwoSettings_export">

                                        </div>
                                        
                                    </form>
                                    
                                    
                                    
                                    
                                    
                                    <?php } ?>
                                    
                                    
                                    
                                    
                                    
                                    
                                    
                                    
                                    
                                    
                                    
                                    
                                    
                                
                            </div>
                        </div>
                    </div>
            </div>
            
            <div id="tab-setting" class="tab-pane" >
            
                <div align="right">
                    <a onclick="$('#form-setting').submit();" title="<?php echo $entry_odmpro_tamplate_data_save_tamplate_data; ?>" class="btn btn-danger"><i class="fa fa-tasks"></i>  Сохранить</a>
                </div> 
                
                
                <form action="<?php echo $action_setting; ?>" method="post" enctype="multipart/form-data" id="form-setting">
                    
                    <?php $id = time(); ?>
                    
                    <input name="odmpro_update_csv_link[<?php echo $id ?>][id]" type="hidden" value="<?php echo $id ?>" />
                    
                    <?php if(isset($smart_exchange)){ ?>
                              
                                <?php echo $smart_exchange['link']; ?>
                                <hr>
                    <?php } ?>
                    
                    <h2>Создать задачу автообновления <i  onclick="showHide('.newautoimpexp_task_box');" style="cursor: pointer; color: orange" class="fa fa-edit"></i></h2>
                    <div class="newautoimpexp_task_box" style="display: none" >
                        <div class="info-box-modal2">
                            Создайте задачу автообновления. Автоматический пуск задачи возможен или по прямой ссылке или через компонент smartExchange. Запуск по прямой ссылке предполагает установку данной ссылки на CRON с указанием времени запуска непосредственно в настройках CRON. При использовании smartExchange дата и время запуска задачи указывается ниже в каждой соответстветствующей задаче. 
                        </div>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo $column_update_csv_link_template_data ?></th>
                                    <th><?php echo $column_update_csv_link_token ?></th>
                                    <th><?php echo $column_update_csv_link_status ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                  <tr>
                                      <td class="text-left">
                                          <div class="input-group">
                                          <?php if(!$odmpro_update_csv_link_tamplate_data){ ?>
                                              <select name="odmpro_update_csv_link[<?php echo $id ?>][tamplate_data_id]"  class="form-control">
                                                  <option value="0" ><?php echo $entry_odmpro_tamplate_data_empty; ?></option>
                                              </select>
                                          <?php }else{ ?>
                                              <select name="odmpro_update_csv_link[<?php echo $id ?>][tamplate_data_id]"  class="form-control">

                                                  <option value="0" ><?php echo $entry_select; ?></option>

                                                      <?php foreach($odmpro_update_csv_link_tamplate_data as $tamplate_data_key => $tamplate_data){ ?>

                                                            <option value="<?php echo $tamplate_data_key ?>" ><?php echo $tamplate_data['name']; ?></option>

                                                      <?php } ?>

                                              </select>
                                          <?php } ?>
                                          </div>
                                      </td>
                                      <td>
                                          <input class="form-control" name="odmpro_update_csv_link[<?php echo $id ?>][token]" value="" />
                                      </td>
                                      <td>
                                            <select name="odmpro_update_csv_link[<?php echo $id ?>][status]"  class="form-control">

                                                <option value="0" ><?php echo $entry_update_csv_link_status_0; ?></option>
                                                <option value="1" ><?php echo $entry_update_csv_link_status_1; ?></option>
                                                <option value="3" ><?php echo $entry_update_csv_link_status_3; ?></option>

                                            </select>
                                      </td>
                                  </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                <h2>Редактировать задачу автообновления</h2>
                
                <?php if($odmpro_update_csv_link){ ?>
                
                <table class="table table-bordered"  style="border-left: none !important; border-right: none !important;">
                    <thead>
                        <tr>
                            <th><?php echo $column_update_csv_link_template_data ?></th>
                            <th><?php echo $column_update_csv_link_token ?></th>
                            <th><?php echo $column_update_csv_link_status ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr  style="border: none !important">
                                  <td colspan="3" style="border: none !important">
                                      
                                  </td>
                              </tr>
                        <?php foreach( $odmpro_update_csv_link as $link_data ){ ?>
                        
                            <?php $id = $link_data['id']; ?>
                            
                            <input name="odmpro_update_csv_link[<?php echo $id ?>][id]" type="hidden" value="<?php echo $id ?>" />
                            
                            <tr>
                                <td class="text-left">
                                    <div class="input-group">
                                    <?php if(!$odmpro_update_csv_link_tamplate_data){ ?>
                                        <select name="odmpro_update_csv_link[<?php echo $id ?>][tamplate_data_id]"  class="form-control">
                                            <option value="0" ><?php echo $entry_odmpro_tamplate_data_empty; ?></option>
                                        </select>
                                    <?php }else{ ?>
                                        <select name="odmpro_update_csv_link[<?php echo $id ?>][tamplate_data_id]"  class="form-control">

                                                <?php foreach($odmpro_update_csv_link_tamplate_data as $tamplate_data_key => $tamplate_data){ ?>
                                                
                                                    <?php if($link_data['tamplate_data_id'] && $link_data['tamplate_data_id']==$tamplate_data_key){ ?>

                                                        <option selected="" value="<?php echo $tamplate_data_key ?>" ><?php echo $tamplate_data['name']; ?></option>
                                                      
                                                    <?php }else{ ?>
                                                        
                                                        <option value="<?php echo $tamplate_data_key ?>" ><?php echo $tamplate_data['name']; ?></option>
                                                    
                                                    <?php } ?>

                                                <?php } ?>

                                        </select>
                                    <?php } ?>
                                    </div>
                                </td>
                                <td>
                                    <input class="form-control" name="odmpro_update_csv_link[<?php echo $id ?>][token]" value="<?php echo $link_data['token'] ?>" />
                                </td>
                                <td>
                                      <select name="odmpro_update_csv_link[<?php echo $id ?>][status]"  class="form-control">
                                          
                                          <option value="0" ><?php echo $entry_update_csv_link_status_0; ?></option>
                                          
                                          <?php if($link_data['status'] && $link_data['status']==1){ ?>
                                            
                                                <option selected="" value="1" ><?php echo $entry_update_csv_link_status_1; ?></option>

                                                <option value="3" ><?php echo $entry_update_csv_link_status_3; ?></option>
                                            
                                            <?php }elseif($link_data['status'] && $link_data['status']==3){ ?>
                                            
                                                <option value="1" ><?php echo $entry_update_csv_link_status_1; ?></option>

                                                <option selected=""  value="3" ><?php echo $entry_update_csv_link_status_3; ?></option>
                                            
                                            <?php }else{ ?>
                                            
                                                <option value="1" ><?php echo $entry_update_csv_link_status_1; ?></option>

                                                <option value="3" ><?php echo $entry_update_csv_link_status_3; ?></option>
                                            
                                            <?php } ?>

                                      </select>
                                </td>
                            </tr>
                              <tr>
                                    <td class="text-right"><?php echo $column_update_csv_link_link ?></td>
                                    <td colspan="2">
                                        <input style="width:60%"  class="form-control"  readonly="" onclick="$(this).select()" value="<?php echo HTTP_CATALOG.'index.php?route='.$path_oc_version_feed.'/odmpro_update_csv_link&token='.$link_data['token'] ?>"/>
                                    </td>
                              </tr>
                              <tr>
                                    <td class="text-right"><?php echo $column_update_csv_link_link_export ?></td>
                                    <td colspan="2">
                                        <input style="width:60%"  class="form-control"  readonly="" onclick="$(this).select()" value="<?php echo HTTP_CATALOG.'index.php?route='.$path_oc_version_feed.'/odmpro_update_csv_link&export=1&token='.$link_data['token'] ?>"/>
                                    </td>
                              </tr>
                              
                              
                              <?php if(isset($smart_exchange) && isset($smart_exchange['setting'][$id])){ ?>
                              
                                    <?php echo $smart_exchange['setting'][$id]; ?>
                              
                              <?php } ?>
                              
                              <tr  style="border: none !important">
                                  <td colspan="3" style="border: none !important">
                                      
                                  </td>
                              </tr>
                              
                        <?php } ?>
                    </tbody>
                </table>
                
                <?php } else { ?>
                
                    <div class="alert alert-success"><?php echo $entry_update_csv_link_empty ?></div>
                
                <?php } ?>
                
                </form>
                
            </div>        
            <div id="tab-welcome-extecom" class="tab-pane" >
                
                <h2>Регистрация продукта</h2>
                
                <div class="alert alert-success" style="background: #1abc9c; border: 0px;">
                    <?php if($text_lic_success){ ?>
                        <?php echo $text_lic_success ?>
                    <?php }else{ ?>
                    Для регистрации продукта отправьте запрос на почту welcome@ocext.com. В запросе укажите домены, в количестве купленных лицензий. Для каждого одного домена возможна одна техническая лицензия на техническую версию сайта
                    <?php } ?>
                    <p>Постоянные скидки: скидка на вторую лицензию: 20%. Скидка на 3-ую и последующие лицензии: 30%. Для разработчиков, при покупке 3-ех и более лицензий, действует специальное предложение. Если Вы разработчик, пожалуйста, отправьте запрос на почту welcome@ocext.com, запросив предложение для разработчика</p></div>
                <form method="post" enctype="multipart/form-data" id="reg_anycsvxls_form" >
                    <table class="table table-hover">
                    <tr>
                            
                            <td><?php echo $text_csv_ocext_dmpro_key; ?></td>
                            <td>
                                <input name="csv_ocext_dmpro_key" value="<?php if(isset($csv_ocext_dmpro_key)) { echo $csv_ocext_dmpro_key; } else { echo ''; } ?>" class="form-control" />
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $text_csv_ocext_dmpro_email; ?></td>
                            <td>
                                <input name="csv_ocext_dmpro_email" value="<?php if(isset($csv_ocext_dmpro_email)) { echo $csv_ocext_dmpro_email; } else { echo ''; } ?>" class="form-control" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div align="left">
                                    <a onclick="$('#reg_anycsvxls_form').submit();" class="btn btn-primary"><i class="fa fa-save"></i>  Сохранить</a>
                                    <br><br>
                                </div>
                            </td>
                        </tr>
                        
                </table>
                </form>
                <hr>
                <h2>Рекомендации к хостингу</h2>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <td>Название параметра/расширения</td>
                            <td>О применении</td>
                            <td>Рекомендованное значение или статус</td>
                        </tr>
                    </thead>
                    <tr>
                        <td>max_input_vars</td>
                        <td>Параметр ограничивает количество значений в формах, которые можно передавать на хостинг. Параметр критически важен, если используется функции сопоставления категорий, при этом их количество составляет сотни и тысячи</td>
                        <td>Не менее 1000, рекомендуется 10000</td>
                    </tr>
                    <tr>
                        <td>max_execution_time (и таймауты в NGINX, таймауты работы базы)</td>
                        <td>Параметр ограничивает время работы скриптов в разрезе одного вызова. Критически важен, если данные подаются по ссылкам, но при этом большой размер файла или низкая скорость соединения между хостингом и удаленным сервером поставщика данных</td>
                        <td>Не менее 60 сек.</td>
                    </tr>
                    <tr>
                        <td>max_allowed_packet php</td>
                        <td>Параметр ограничивает размер памяти для вставки в базу. Критически важен, если профили настроек содержат большое количество данных, а число профилей десятки</td>
                        <td>Не менее 4 Мб</td>
                    </tr>
                    <tr>
                        <td>libxml</td>
                        <td>Расширение PHP, которое используется для парсинга файлов XML. Критически важно при работе с XML, содержащих CDATA с большим объемом информации</td>
                        <td>Рекомендуемая версия 2.9.4 выше</td>
                    </tr>
                    <tr>
                        <td>curl</td>
                        <td>Расширение PHP, которое используется получение файлов импорта, и изображений при импорте</td>
                        <td>Должно быть включено</td>
                    </tr>
                    <tr>
                        <td>XMLReader</td>
                        <td>Расширение PHP, которое используется при работе с файлами XML</td>
                        <td>Должно быть включено</td>
                    </tr>
                    <tr>
                        <td>fileinfo</td>
                        <td>Расширение PHP, которое используется при работе с файлами</td>
                        <td>Должно быть включено</td>
                    </tr>
                </table>
                <hr>
                
                <div id="tab-welcome-extecom-window"></div>
                <hr>
                
                <?php if ((!$error_warning) && (!$success)) { ?>
                
                    <div id="ocext_notification" class="alert alert-info"><i class="fa fa-info-circle"></i>
                        
                            <div id="ocext_loading"><img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /></div>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            
                    </div>
                <?php } ?>
                
            </div>
        </div>
        
    </div>        
    </div>
</div>
</div>
<script type="text/javascript"><!--

var token_name_by_version = '<?php echo $token_name; ?>';
var token_value_by_version = '<?php echo ${$token_name}; ?>';
var path_oc_version = '<?php echo $path_oc_version; ?>';

function saveTemplateSetting(tid){

    $.ajax({
            type: 'post',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getTypesDataColumnAdditional&type_process='+type_process+'&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>&field='+field+'&db_table___db_column='+db_table___db_column,
            data: $('#tamplate_data_form_'+type_process+' input:text, #tamplate_data_form_'+type_process+' input:hidden, #tamplate_data_form_'+type_process+' input:checkbox:checked, #tamplate_data_form_'+type_process+' select, #tamplate_data_form_'+type_process+' textarea'),
            dataType: 'html',
            success: function(response) {
                if(response!=''){
                    $(id_td).show(100);
                    $(id_td).html(response);
                }else{
                    $(id_td).hide();
                }
                getTypesDataColumnsAdditional_start = true;
            },
            failure: function(response){
               getTypesDataColumnsAdditional_start = true;     
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                getTypesDataColumnsAdditional_start = true;
            }
    });

}

function setExample(variant){

    if(variant=='group_id_box'){
    
        $('select[name=\'odmpro_tamplate_data[group_id_box][product_data][0][product_field]\']').val('model');
        $('select[name=\'odmpro_tamplate_data[group_id_box][product_data][0][operator]\']').val('like_left');
        $('input[name=\'odmpro_tamplate_data[group_id_box][product_data][0][value]\']').val('suppl5-');
        $('input[name=\'odmpro_tamplate_data[group_id_box][disable_quantity]\']').val('0');
        
    }
    else if(variant=='cat_mapping'){
    
        $('input[name=\'odmpro_tamplate_data[group_id_box][category_matching_csv_delimeter]\']').val('/');
        $('input[name=\'odmpro_tamplate_data[group_id_box][category_matching_csv_column_name]\']').val('Категории');
        
    }

}

function selectFormatSet(format,type_process){

    if(format=='xls'){

        $(".xls_upload_set_"+type_process).show();
        $(".dsv_upload_set_"+type_process).hide();
        $(".xml_upload_set_"+type_process).hide();
        $('input[name=\'odmpro_tamplate_data[anyxls_xls_upload]\']').val('1');
        $('input[name=\'odmpro_tamplate_data[anyyml_yml_upload]\']').val('0');
        $('#export_boxes_selector').val('xls');
        $('#export_boxes_selector').change();

    }
    else if(format=='dsv'){

        $(".xls_upload_set_"+type_process).hide();
        $(".dsv_upload_set_"+type_process).show();
        $(".xml_upload_set_"+type_process).hide();
        $('input[name=\'odmpro_tamplate_data[anyxls_xls_upload]\']').val('0');
        $('input[name=\'odmpro_tamplate_data[anyyml_yml_upload]\']').val('0');
        $('#export_boxes_selector').val('csv');
        $('#export_boxes_selector').change();

    }
    else if(format=='xml'){

        $(".xls_upload_set_"+type_process).hide();
        $(".dsv_upload_set_"+type_process).hide();
        $(".xml_upload_set_"+type_process).show();
        $('input[name=\'odmpro_tamplate_data[anyxls_xls_upload]\']').val('0');
        $('input[name=\'odmpro_tamplate_data[anyyml_yml_upload]\']').val('1');

    }

}

function update_link(id,left,center,right){
    $(id).val(left+center+right);
}
    
function showHide(sel){
    
    if($(sel).is(':visible')){
    
        $(sel).hide();
    
    }else{
    
        $(sel).show();
    
    }

}

var getTypesDataColumnsAdditional_start = true;

function delay(f, ms) {

  return function() {
    var savedThis = this;
    var savedArgs = arguments;

    setTimeout(function() {
      f.apply(savedThis, savedArgs);
    }, ms);
  };

}

var getTypesDataColumnsAdditional_delay = delay(getTypesDataColumnsAdditional, 1100);

function getTypesDataColumnsAdditional(db_table___db_column,field,id_td,type_process){
    
    if(db_table___db_column==0){
        $(id_td).html('');
        $(id_td).hide();
        return;
    
    }
    
    $(id_td).html('<div id="ocext_loading_'+id_td+'"><img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /></div>').show();
    if(getTypesDataColumnsAdditional_start===false){
		
        getTypesDataColumnsAdditional_delay(db_table___db_column,field,id_td,type_process);
        return;
		
    }
    getTypesDataColumnsAdditional_start = false;
    
    $.ajax({
            type: 'post',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getTypesDataColumnAdditional&type_process='+type_process+'&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>&field='+field+'&db_table___db_column='+db_table___db_column,
            data: $('#tamplate_data_form_'+type_process+' input:text, #tamplate_data_form_'+type_process+' input:hidden, #tamplate_data_form_'+type_process+' input:checkbox:checked, #tamplate_data_form_'+type_process+' select, #tamplate_data_form_'+type_process+' textarea'),
            dataType: 'html',
            success: function(response) {
                if(response!=''){
                    $(id_td).show(100);
                    $(id_td).html(response);
                }else{
                    $(id_td).hide();
                }
                getTypesDataColumnsAdditional_start = true;
            },
            failure: function(response){
               getTypesDataColumnsAdditional_start = true;     
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                getTypesDataColumnsAdditional_start = true;
            }
    });
}

function stopProcess(type_process){

    stop_process = true;
    
    if(type_process=='export'){
    
        $('#exportStartMessages').html('<div class="alert alert-success">Процесс экспорта остановлен</div>');
        $('input[name=\'odmpro_tamplate_data[limit]\']').prop('readonly',false);
        $('input[name=\'odmpro_tamplate_data[start]\']').prop('readonly',false);
        $('#startExport').show();
        $('#startExport_stop').hide();
        $("#startExportLoading").html('');
        start = 0;
        total = 0;
        first_start = 0;
        limit = 0;
        finished = 0;
        num_process = num_process+1;
    
    }
    else if(type_process=='import'){
    
        $('#importStartMessages').html('<div class="alert alert-success">Процесс импорта остановлен</div>');
        $('input[name=\'odmpro_tamplate_data[limit]\']').prop('readonly',false);
        $('input[name=\'odmpro_tamplate_data[start]\']').prop('readonly',false);
        $('#startImport').show();
        $('#startImport_stop').hide();
        start = 0;
        total = 0;
        first_start = 0;
        limit = 0;
        finished = 0;
        num_process = num_process+1;
        nfu = 'no_data';
        
    }

}

var stop_process = false;
var start = 0;
var first_start = 0;
var finished = 0;
var limit = 0;
var total = 0;
var nfu = 'no_data';
var num_process = <?php echo time(); ?>;

function startExport(new_start,first_row){
    
    $('#importDemoResult_td').hide();

    $('#importDemoResult').html('');
    
    var errors = false;
    
    if(errors===true){
        return;
    }
    
    if(limit==0 && start==0){
        start = parseInt($('input[name=\'odmpro_tamplate_data[start]\']').val()) - 1;
        first_start = parseInt($('input[name=\'odmpro_tamplate_data[start]\']').val()) - 1;
        if(start<0){
            start = 0;
        }
        if(first_start<0){
            first_start = 0;
        }
        
        limit = parseInt($('input[name=\'odmpro_tamplate_data[limit]\']').val());
        $('input[name=\'odmpro_tamplate_data[limit]\']').prop('readOnly',true);
        $('input[name=\'odmpro_tamplate_data[start]\']').prop('readOnly',true);
        $('#startExport').hide();
        $('#startExport_stop').show();
        $('#importStartMessages').html('<div class="alert alert-info"><i class="fa fa-info-circle"></i>&nbsp;&nbsp;<img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /><button type="button" class="close" data-dismiss="alert">&times;</button>&nbsp;&nbsp;<?php echo $text_import_start ?>: <b><?php echo $text_wite ?></b> / <b><?php echo $text_wite ?></b></div>');
    }else{
        finished = start+limit-first_start;
        new_start = parseInt(new_start);
        start = new_start;
    }
    
    $.ajax({
            type: 'post',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/startExport&num_process='+num_process+'&first_row='+first_row+'&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>&start='+start,
            data: $('#tamplate_data_form_export input:text, #tamplate_data_form_export input:hidden, #tamplate_data_form_export input:checkbox:checked, #tamplate_data_form_export select, #tamplate_data_form_export textarea'),
        <?php if($debug_mode){ ?>
            dataType: 'html',
            success: function(json) {
                $('#exportStartMessages').html('Извините, включен режим отладки. Sorry, debug mode is enabled<br><br>'+json);
                $('#startExport').show();
                $('#startExport_stop').hide();
                $("#startExportLoading").html('');
                start = 0;
                total = 0;
                first_start = 0;
                limit = 0;
                finished = 0;
                num_process = num_process+1;
                stop_process = false;
                return;
        <?php }else{ ?>
            dataType: 'json',
            beforeSend: function(){
                //$("#startExportLoading").html('<img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" />');
            },
            success: function(json) {
        <?php } ?>
    
                if(json['error']!=''){
                    $('#exportStartMessages').html('<div class="alert alert-danger">'+json['error']+'</div>');
                    $('#startExport').show();
                    $('#startExport_stop').hide();
                    $("#startExportLoading").html('');
                    start = 0;
                    total = 0;
                    first_start = 0;
                    limit = 0;
                    finished = 0;
                    num_process = num_process+1;
                    stop_process = false;
                    return;
                }
                
                if(json['success']!=''){
                    $('#exportStartMessages').html('<div class="alert alert-success">'+json['success']+'</div>');
                    $('input[name=\'odmpro_tamplate_data[limit]\']').prop('readonly',false);
                    $('input[name=\'odmpro_tamplate_data[start]\']').prop('readonly',false);
                    $('#startExport').show();
                    $('#startExport_stop').hide();
                    $("#startExportLoading").html('');
                    start = 0;
                    total = 0;
                    first_start = 0;
                    limit = 0;
                    finished = 0;
                    num_process = num_process+1;
                    stop_process = false;
                    return;
                }
                
                if(stop_process==true){
                
                    $('#exportStartMessages').html('<div class="alert alert-success">Процесс экспорта остановлен</div>');
                    $('input[name=\'odmpro_tamplate_data[limit]\']').prop('readonly',false);
                    $('input[name=\'odmpro_tamplate_data[start]\']').prop('readonly',false);
                    $('#startExport').show();
                    $('#startExport_stop').hide();
                    $("#startExportLoading").html('');
                    start = 0;
                    total = 0;
                    first_start = 0;
                    limit = 0;
                    finished = 0;
                    num_process = num_process+1;
                    stop_process = false;
                    return;
                
                }
                
                if(json['total']!=''){
                    total = parseInt(json['total']) - first_start;
                    if(total<0){
                        total = 0;
                    }
                    $('#exportStartMessages').html('<div class="alert alert-info"><i class="fa fa-info-circle"></i>&nbsp;&nbsp;<img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /><button type="button" class="close" data-dismiss="alert">&times;</button>&nbsp;&nbsp;<?php echo $text_import_start ?>: : <b>'+finished+'</b> / <b>'+total+'</b></div>');
                    startExport(start+limit,0);
                }
                
            },
            failure: function(response){
                <?php if($debug_mode){ ?>
                    
                    alert(response);
            
                <?php } ?>
            },
            error: function(xhr, ajaxOptions, thrownError) {
                
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                
            }
    });
    
}

function startImport(new_start){
    
    var errors = false;
    
    $('#importDemoResult_td').hide();

    $('#importDemoResult').html('');
    
    if($("select[name='odmpro_tamplate_data[type_change]']").val()==0){
        $("select[name='odmpro_tamplate_data[type_change]']").addClass('error-border');
        errors = true;
    }else{
        $("select[name='odmpro_tamplate_data[type_change]']").removeClass('error-border');
    }
    if(errors===true){
        return;
    }
    
    if(limit==0 && start==0){
        start = parseInt($('input[name=\'odmpro_tamplate_data[start]\']').val()) - 1;
        first_start = parseInt($('input[name=\'odmpro_tamplate_data[start]\']').val()) - 1;
        if(start<0){
            start = 0;
        }
        if(first_start<0){
            first_start = 0;
        }
        
        limit = parseInt($('input[name=\'odmpro_tamplate_data[limit]\']').val());
        $('input[name=\'odmpro_tamplate_data[limit]\']').prop('readOnly',true);
        $('input[name=\'odmpro_tamplate_data[start]\']').prop('readOnly',true);
        $('#startImport').hide();
        $('#startImport_stop').show();
        $('#importStartMessages').html('<div class="alert alert-info"><i class="fa fa-info-circle"></i>&nbsp;&nbsp;<img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /><button type="button" class="close" data-dismiss="alert">&times;</button>&nbsp;&nbsp;<?php echo $text_import_start ?>: <b><?php echo $text_wite ?></b> / <b><?php echo $text_wite ?></b></div>');
    }else{
        finished = start+limit-first_start;
        new_start = parseInt(new_start);
        start = new_start;
    }
    
    $.ajax({
            type: 'post',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/startImport&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>&num_process='+num_process+'&start='+start+'&nfu='+nfu,
            data: $('#tamplate_data_form_import input:text, #tamplate_data_form_import input:hidden, #tamplate_data_form_import input:checkbox:checked, #tamplate_data_form_import select, #tamplate_data_form_import textarea'),
        <?php if($debug_mode){ ?>
            dataType: 'html',
            success: function(json) {
                $('#importStartMessages').html('Извините, включен режим отладки. Sorry, debug mode is enabled<br><br>'+json);
                $('#startImport').show();
                $('#startImport_stop').hide();
                $("#startExportLoading").html('');
                start = 0;
                total = 0;
                first_start = 0;
                limit = 0;
                finished = 0;
                num_process = num_process+1;
                stop_process = false;
                return;
        <?php }else{ ?>
            dataType: 'json',
            success: function(json) {
        <?php } ?>
    
                if(json['error']!=''){
                    $('#importStartMessages').html('<div class="alert alert-danger">'+json['error']+'</div>');
                    $('#startImport').show();
                    $('#startImport_stop').hide();
                    start = 0;
                    total = 0;
                    first_start = 0;
                    limit = 0;
                    finished = 0;
                    num_process = num_process+1;
                    nfu = 'no_data';
                    $('input[name=\'odmpro_tamplate_data[limit]\']').prop('readonly',false);
                    $('input[name=\'odmpro_tamplate_data[start]\']').prop('readonly',false);
                    $('#startImport').show();
                    $('#startImport_stop').hide();
                    stop_process = false;
                    return;
                }
                
                if(json['success']!=''){
                
                    if(json['result_demo']){

                        $('#importDemoResult_td').show();

                        $('#importDemoResult').html(json['result_demo']);
                        $('#importStartMessages').html('');
                        $('input[name=\'odmpro_tamplate_data[limit]\']').prop('readonly',false);
                        $('input[name=\'odmpro_tamplate_data[start]\']').prop('readonly',false);
                        $('#startImport').show();
                        $('#startImport_stop').hide();
                        start = 0;
                        total = 0;
                        first_start = 0;
                        limit = 0;
                        finished = 0;
                        num_process = num_process+1;
                        nfu = 'no_data';
                        stop_process = false;

                    }else{
                        $('#importStartMessages').html('<div class="alert alert-success">'+json['success']+'</div>');
                        $('input[name=\'odmpro_tamplate_data[limit]\']').prop('readonly',false);
                        $('input[name=\'odmpro_tamplate_data[start]\']').prop('readonly',false);
                        $('#startImport').show();
                        $('#startImport_stop').hide();
                        start = 0;
                        total = 0;
                        first_start = 0;
                        limit = 0;
                        finished = 0;
                        num_process = num_process+1;
                        nfu = 'no_data';
                        stop_process = false;
                           
                    }
                    return;  
                    
                }
                
                if(stop_process==true){
                
                    $('#importStartMessages').html('<div class="alert alert-success">Процесс импорта остановлен</div>');
                    $('input[name=\'odmpro_tamplate_data[limit]\']').prop('readonly',false);
                    $('input[name=\'odmpro_tamplate_data[start]\']').prop('readonly',false);
                    $('#startImport').show();
                    $('#startImport_stop').hide();
                    start = 0;
                    total = 0;
                    first_start = 0;
                    limit = 0;
                    finished = 0;
                    num_process = num_process+1;
                    nfu = 'no_data';
                    stop_process = false;
                    return;
                }
                
                if(json['total']!=''){
                    total = parseInt(json['total']) - first_start;
                    if(total<0){
                        total = 0;
                    }
                    
                    nfu = json['nfu'];
                    
                    var nfu_text = '';
                    
                    if(nfu!='no_data'){
                    
                        nfu_text = ' (порядковый номер файла: '+ nfu +')';
                    
                    }
                    
                    var import_info = '';
                    
                    if(json['memory_usage_txt']!==''){
                        
                        import_info += "<br><b>Макс. затраты ОЗУ:</b> "+json['memory_usage_txt'];
                        
                    }
                    
                    if(json['import_time']!==''){
                        
                        import_info += "<br><b>Затрачено времени на импорт порции:</b> "+json['import_time']+" сек.";
                        
                    }
                    
                    if(json['image_download_interval']!==''){
                        
                        import_info += "<br><b>Максимально время ответа удаленного сервера при закачке 1-ой картинки:</b> "+json['image_download_interval']+" сек.";
                        
                    }
                    
                    new_start = parseInt(start+limit);
                    
                    if(json['new_nfu']=='new_data'){
                    
                        start = parseInt($('input[name=\'odmpro_tamplate_data[start]\']').val()) - 1;
                        first_start = parseInt($('input[name=\'odmpro_tamplate_data[start]\']').val()) - 1;
                        if(start<0){
                            start = 0;
                        }
                        if(first_start<0){
                            first_start = 0;
                        }
                        
                        finished = 0;
                        total = 0;
                        
                        new_start = parseInt(start);
                    
                    }
                    
                    $('#importStartMessages').html('<div class="alert alert-info"><i class="fa fa-info-circle"></i>&nbsp;&nbsp;<img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /><button type="button" class="close" data-dismiss="alert">&times;</button>&nbsp;&nbsp;<?php echo $text_import_start ?>: : <b>'+finished+'</b> / <b>'+total+'</b>'+nfu_text+'</div>'+import_info);
                    
                    if(json['result_demo']){

                        $('#importDemoResult_td').show();

                        $('#importDemoResult').html(json['result_demo']);
                        $('#importStartMessages').html('');
                        $('input[name=\'odmpro_tamplate_data[limit]\']').prop('readonly',false);
                        $('input[name=\'odmpro_tamplate_data[start]\']').prop('readonly',false);
                        $('#startImport').show();
                        $('#startImport_stop').hide();
                        start = 0;
                        total = 0;
                        first_start = 0;
                        limit = 0;
                        finished = 0;
                        num_process = num_process+1;
                        nfu = 'no_data';
                        stop_process = false;

                    }
                    else{
                          
                          startImport(new_start);
                
                    }
                    
                }
            },
            failure: function(response){
                <?php if($debug_mode){ ?>
                    
                    alert(response);
            
                <?php } ?>
            },
            error: function(xhr, ajaxOptions, thrownError) {
                
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                
            }
    });
    
}

function openElementOnNameValue(name_open,value_this,value_open,element){
    if(value_open==value_this){
        $(element + "[name='"+name_open+"']").show();
    }else{
        $(element + "[name='"+name_open+"']").hide();
        $(element + "[name='"+name_open+"'] option[value='0']").prop('selected', true);
    }
}

function setTemplateData(type_action,type_process){
    
    $("#setTemplateData").empty();
    
    $("#setTemplateData").before('<div id="ocext_loading_setTemplateData"><img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /></div>').show();
    
    $.ajax({
            type: 'post',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/setTemplateData&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>&type_action='+type_action,
            data: $('#tamplate_data_form_'+type_process+' input:text, #tamplate_data_form_'+type_process+' input:hidden, #tamplate_data_form_'+type_process+' input:checkbox:checked, #tamplate_data_form_'+type_process+' select, #tamplate_data_form_'+type_process+' textarea'),
            <?php if(!$debug_mode){ ?>
            dataType: 'json',
            <?php }else{ ?>
            dataType: 'html',
            <?php } ?>
            success: function(json) {
                
                <?php if($debug_mode){ ?>
                    alert(json);
                <?php } ?>
                
                if(json['success']!=''){
                    $('#setTemplateData').html(json['success']);
                }
                if(json['error']!=''){
                    $('#setTemplateData').html(json['error']);
                }
                if(type_action=='save'){
                    
                    var new_option = '<option value="'+json['odmpro_tamplate_data_id']+'">'+json['odmpro_tamplate_data_name']+'</option>';
                    $("select[name='odmpro_tamplate_data[id]']").append(new_option);
                    $("select[name='odmpro_tamplate_data[id]'] option[value='"+json['odmpro_tamplate_data_id']+"']").prop('selected', true);
                    $("#setTemplateDataTypeAction option[value='update']").prop('selected', true);
                    
                }
                if(type_action=='delete'){
                    $("select[name='odmpro_tamplate_data[id]'] option[value='"+json['odmpro_tamplate_data_id_delete']+"']").remove();
                    window.location.reload();
                }
                $('#ocext_loading_setTemplateData').remove();
                $("#setTemplateDataBtn").removeClass('setTemplateDataBtnNeedSave');
            },
            failure: function(response){
                
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
    });
}

function setNewTypesDataColumns(field_last,id_td_last,field_new,type_process,this_name,template_id){
    
    id_td = new Date().getTime();
    $("#type_data_column_"+id_td_last).empty();
    $("#type_data_column_"+id_td_last).attr('id',"type_data_column_"+id_td);
    $("select[name='odmpro_tamplate_data[type_data]["+field_last+"]']").attr('onchange',"getTypesDataColumns(this.value,'#type_data_column_"+id_td+"','"+field_new+"','"+type_process+"')");
    $("select[name='odmpro_tamplate_data[type_data]["+field_last+"]']").attr('name',"odmpro_tamplate_data[type_data]["+field_new+"]");
    $("select[name='odmpro_tamplate_data[type_data]["+field_new+"]']").val('0');
    $("input[name='"+this_name+"']").attr('onchange',"setNewTypesDataColumns('"+field_new+"','"+id_td+"',this.value,'"+type_process+"',this.name);");
    $("input[name='"+this_name+"']").attr('name',"odmpro_tamplate_data[export_field_name]["+template_id+"]["+field_new+"]");
    
}

function getTypesDataColumns(type_data,id_td,field,type_process){
    $(id_td).html('<div id="ocext_loading_'+id_td+'"><img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /></div>').show();
    $.ajax({
            type: 'post',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getTypesData&type_process='+type_process+'&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>&field='+field+'&type_data='+type_data,
            data: $('#tamplate_data_form_'+type_process+' input:text, #tamplate_data_form_'+type_process+' input:hidden, #tamplate_data_form_'+type_process+' input:checkbox:checked, #tamplate_data_form_'+type_process+' select, #tamplate_data_form_'+type_process+' textarea'),
            dataType: 'html',
            success: function(response) {
                $(id_td).html(response);
            },
            failure: function(response){
                    
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
    });
    getTypesDataGeneralSetting(type_process);
    if(type_data=='product'){
        $('#product_demoimport_box').show();
    }
    else{
        $('#product_demoimport_box').hide();
    }
}

function getTypesDataSelfColumns(type_data,id_td,self_column_id,type_process){
    $(id_td).html('<div id="ocext_loading_'+id_td+'"><img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /></div>').show();
    $.ajax({
            type: 'post',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getTypesDataSelfColumns&type_process='+type_process+'&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>&self_column_id='+self_column_id+'&type_data='+type_data,
            data: $('#tamplate_data_form_'+type_process+' input:text, #tamplate_data_form_'+type_process+' input:hidden, #tamplate_data_form_'+type_process+' input:checkbox:checked, #tamplate_data_form_'+type_process+' select, #tamplate_data_form_'+type_process+' textarea'),
            dataType: 'html',
            success: function(response) {
                $(id_td).html(response);
            },
            failure: function(response){
                    
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
    });
    getTypesDataGeneralSetting(type_process);
}

function getXPathResult(url_id,x_path_id,xpath_stags_id,result_id){
    
    if($(url_id).val()==''){
        $(result_id).css({'backgroundColor' : 'white', 'padding':'10px'});
        $(result_id).html('Не указана ссылка для проверки xPath запроса');
        return;
    }

    var data = { url: $(url_id).val(), x_path: $(x_path_id).val(), xpath_stags: $(xpath_stags_id).val() };
    $.ajax({
            type: 'post',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getXPathResult&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
            data: data,
            dataType: 'html',
            success: function(response) {
                $(result_id).css({'backgroundColor' : 'white', 'padding':'10px'});
                $(result_id).html(response);
            },
            failure: function(response){
                    
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
    });
}

function getTypesDataGeneralSetting(type_process){
    
    $.ajax({
            type: 'post',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getTypesDataGeneralSetting&type_process='+type_process+'&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
            data: $('#tamplate_data_form_'+type_process+' input:text, #tamplate_data_form_'+type_process+' input:hidden, #tamplate_data_form_'+type_process+' input:checkbox:checked, #tamplate_data_form_'+type_process+' select, #tamplate_data_form_'+type_process+' textarea'),
            dataType: 'html',
            beforeSend: function(){
                $("#typesDataGeneralSetting").html('<div id="ocext_loading_typesDataGeneralSetting"><img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /></div>').show();
            },
            success: function(response) {
                $("#typesDataGeneralSetting").html(response);
            },
            failure: function(response){
                    
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
    });
}

function updateSaveButton(type_process){

    $('#tamplate_data_form_'+type_process+' input:text, #tamplate_data_form_'+type_process+' input:hidden, #tamplate_data_form_'+type_process+' input:checkbox:checked, #tamplate_data_form_'+type_process+' select, #tamplate_data_form_'+type_process+' textarea').change(function(){
        $("#setTemplateDataBtn").addClass('setTemplateDataBtnNeedSave');
        $("#setTemplateData").hide();
    });

}

function changeTypeData(){
    $('.select-type-data').each(function(){
        if(this.value!=0){
            $(this).change();
        }
    });
    
}

function hideColumn(){

    $('.select-type-data').each(function(){
        
        if($("input[name='odmpro_tamplate_data[hide_column]']").is(":checked")){
        
            if(this.value==0 && this.class!='self_column_table_row'){
                
                $(this).parent('div').parent('td').parent('tr').hide();
                
            }else{
            
                $(this).parent('div').parent('td').parent('tr').show(150);
            
            }
        
        }else{
            
            $(this).parent('div').parent('td').parent('tr').show(150);
        
        }
        
        
    });
        

}

function changeTypeDataColumn(data_column_class){
    if(data_column_class===0){
        $('.select-type-data-column').each(function(){
            if(this.value!=0){
                $(this).change();
            }
        });
    }else{
        $('.'+data_column_class).change();
    }
    hideColumn();
}

function getStepTwoSettings(type_process,status_continuation){
    $("#stepTwoSettings_"+type_process).html('<div class="ocext_loading" id="ocext_loading_stepTwoSettings'+type_process+'"><img class="ocext_loading"  src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /></div>');
    
    $.ajax({
            type: 'post',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getStepTwoSettings&status_continuation='+status_continuation+'&type_process='+type_process+'&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
            data: $('#tamplate_data_form_'+type_process+' input:text, #tamplate_data_form_'+type_process+' input:hidden, #tamplate_data_form_'+type_process+' input:checkbox:checked, #tamplate_data_form_'+type_process+' select, #tamplate_data_form_'+type_process+' textarea'),
            dataType: 'html',
            success: function(response) {
                $('#stepTwoSettings_'+type_process).html(response);
            },
            failure: function(response){
                    //alert(response);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
    });
}

function getProcessHistoryStatus(odmpro_tamplate_data_id,supplier_name,type_process){
    return;
    processes_history_status_get = setInterval(function() {
    
        $.ajax({
                type: 'post',
                url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getProcessHistoryStatus&supplier_name='+supplier_name+'&odmpro_tamplate_data_id='+odmpro_tamplate_data_id+'&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
                data: $('#tamplate_data_form_'+type_process+' input:text, #tamplate_data_form_'+type_process+' input:hidden, #tamplate_data_form_'+type_process+' input:checkbox:checked, #tamplate_data_form_'+type_process+' select, #tamplate_data_form_'+type_process+' textarea'),
                dataType: 'html',
                success: function(response) {
                    $('#stepTwoSettings_'+type_process).html(response);
                },
                failure: function(response){
                        //alert(response);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
        });
    
    }, 500);
    
}

    function getLastLogData(){
    
        $.ajax({
                url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getLastLogData&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
                dataType: 'json',
                success: function(json) {
                        $.map(json, function(item) {
                            //console.log(item);
                            $("#task_id_last_log_data_" + item['task_id']).html(item['msg']);
                            
                        });
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
        });
    
    }
    
    function getActionTask(task_id,action_status_id){
    
        $.ajax({
                url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getActionTask&task_id='+task_id+'&action_status_id='+action_status_id+'&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
                dataType: 'html',
                success: function(html) {
                        alert(html);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
        });
    
    }
    
    function getSmartExchangeCheckConnect(){
    
        $.ajax({
                url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getSmartExchangeCheckConnect&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
                dataType: 'html',
                success: function(html) {
                    $("#smart_exchange_check_connect_status").html(html);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
        });
    
    }
    
    

function getStepOneSettings(format_data,tamplate_data,type_process){
    $("#stepOneSettings_"+type_process).html('<div class="ocext_loading"  id="ocext_loading_stepOneSettings'+type_process+'"><img class="ocext_loading"  src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading-line.gif" /></div>');
    
    
    $("#stepTwoSettings_"+type_process).empty();
    $.ajax({
            type: 'post',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getStepOneSettings&type_process='+type_process+'&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
            data: 'tamplate_data='+tamplate_data+'&format_data='+format_data,
            dataType: 'html',
            success: function(response) {
                
                $('#stepOneSettings_'+type_process).html(response);
                
            },
            failure: function(response){
                
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $("#stepTwoSettings_"+type_process).empty();
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                
            }
    });
}    
    

function getUniqueParams(){
    $("#unique_param").hide();
    
    $(".unique_param_type_row").hide();
    $('.select-type-data').each(function(){
        if(this.value!=0){
            $("#unique_param_"+this.value).show(150);
            $("#unique_param").show(150);
        }
    
    });

}
    
$(document).ready(function() {
    $("a[href=\'#<?php echo $open_tab ?>\']").click();
    
    <?php if($demo_mode){ ?>
    
        $("select[name='odmpro_format_data']").val('csv');
        $("select[name='odmpro_format_data']").change();
                    
    <?php } ?>
    
});

function getNotifications() {
	$.ajax({
            type: 'GET',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getNotifications&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
            dataType: 'json',
            success: function(json) {
                    if (json['error']) {
                            $('#ocext_notification').html('<i class="fa fa-info-circle"></i><button type="button" class="close" data-dismiss="alert">&times;</button> '+json['error']);
                    } else if (json['message'] && json['message']!='' ) {
                            $('#ocext_notification').html('<i class="fa fa-info-circle"></i><button type="button" class="close" data-dismiss="alert">&times;</button> '+json['message']);
                    }else{
                        $('#ocext_notification').remove();
                    }
            },
            failure: function(){
                    $('#ocext_notification').remove();
            },
            error: function() {
                    $('#ocext_notification').remove();
            }
    });
}
getNotifications();
function getWelcomeWindow() {
	$.ajax({
            type: 'GET',
            url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/getWelcomeWindow&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
            dataType: 'html',
            success: function(html) {
                $('#tab-welcome-extecom-window').html(html);
            },
            failure: function(){
                $('#tab-welcome-extecom-window').html();
            },
            error: function() {
                $('#tab-welcome-extecom-window').html();
            }
    });
}


//--></script> 
<?php echo $footer; ?>