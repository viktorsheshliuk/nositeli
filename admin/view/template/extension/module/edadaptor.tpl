<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        </div>
      <h1><?php echo $heading_title_edadaptor_setting; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
    <div class="container-fluid">
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
    
    <style>
    
    #edadaptor .btn{
        box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
    }    
        
        
    #edadaptor .header_text_white, #edadaptor  .btn-primary{
        background: #201600 !important;
        color: white !important;
        border: 1px solid #201600 !important;
        border-radius: 0px !important;
    }
    
    #edadaptor .header_text_white, #edadaptor  .btn-secondary{
        background: #00b527;
        color: white !important;
        border: 1px solid #00b527 !important;
        border-radius: 0px !important;
    }

    #tab-information .nav-pills > li.active > a,
    #edadaptor .nav-pills > li.active > a {
        background: #201600 !important;
        color: white !important;
        border: 1px solid #201600;
        border-radius: 0px !important;
    }
    #tab-information .nav-pills > li > a,
    #edadaptor .nav-pills > li > a{
        border-radius: 0px !important;
    }

    #edadaptor a{

        color: #201600 !important;

    }

    #edadaptor .alert-info{
        background: lavender;
        border: none;
        color: black;
    }
    
    #edadaptor .alert-info2{
        background: none !important;
        border: 1px solid lavender;
    }

    #edadaptor .disable-status:hover{
        background: #1978ab !important;
    }

    .edadaptor-logo span{
        font-size: 15px;
        font-weight: bold;
        color: #201600 !important;
    }

    span.edadaptor-brand-name{
        background: #201600 !important;
        color: white  !important;
        padding: 1px;
        font-size: 9px;
        font-weight: normal;
        margin-bottom: 5px;
    }

    .edadaptor-system{
        line-height: 32px;
        padding-bottom: 5px;
    }

    #edadaptor .nav-pills ul li div:first-child{
        font-weight: bold !important;
    }

    #edadaptor .my_product_list_information{
        display: none;
    }

    #edadaptor .opnhide_a {
        cursor: pointer;
    }

    #edadaptor .btn-save {
        background: #00b527 !important;
        border: 1px solid #00b527 !important;
        color: white  !important;
    }

    #edadaptor .btn-bro {
        background: #c94a1a !important;
        border: 1px solid #c94a1a !important;
        color: white  !important;
        margin-top: 5px !important;
    }

    #edadaptor .btn-bro2 {
        background: #f24941 !important;
        border: 1px solid #f24941 !important;
        color: white  !important;
        margin-top: 5px !important;
    }

    #edadaptor .result-message {
        color: #00b527 !important;
        font-size: 13px !important;
        font-weight: bold;
    }

    .btn-saving{
        opacity: 0.5;
        cursor:progress;
    }
    
    /* Скрываем реальный чекбокс */
    .custom-forms input[type="checkbox"] {
            display: none;
    }
    /* Задаем внешний вид для нашего кастомного чекбокса. Все обязательные свойства прокомментированы, остальные же свойства меняйте по вашему усмотрению */
    .custom-forms .checkbox-custom {
            position: relative;      /* Обязательно задаем, чтобы мы могли абсолютным образом позиционировать псевдоэлемент внютри нашего кастомного чекбокса */
            min-width: 20px;             /* Обязательно задаем ширину */
            height: 20px;            /* Обязательно задаем высоту */
            border: 2px solid #ccc;
            padding: 2px;
    }
    /* Кастомный чекбокс и лейбл центрируем по вертикали. Если вам это не требуется, то вы можете убрать свойство vertical-align: middle из данного правила, но свойство display: inline-block обязательно должно быть */
    .custom-forms .checkbox-custom,
    .custom-forms .label {
            display: inline-block;
            vertical-align: middle;
    }
    /* Если реальный чекбокс у нас отмечен, то тогда добавляем данный признак и к нашему кастомному чекбоксу  */
    .custom-forms input.checkbox,
    .custom-forms input.radio {
            display: none;
    }
    .custom-forms .checkbox-custom,
    .custom-forms .radio-custom {
            min-width: 20px;
            min-height: 20px;
            border: 2px solid #ccc;
            position: relative;
            padding: 2px;
            padding-top: 0px;
            cursor: pointer;
    }
    .custom-forms .checkbox-custom,
    .custom-forms .radio-custom,
    .custom-forms .label {
            display: inline-block;
            vertical-align: middle;
    }
    .custom-forms .checkbox:checked + .checkbox-custom,
    .custom-forms .radio:checked + .radio-custom {

            background: #009933;
            color: white;
    }
    .custom-forms .checkbox:checked ,
    .custom-forms .radio:checked  {
            color: white;
    }
    .custom-forms .radio-custom,
    .custom-forms .radio:checked + .radio-custom {
            border-radius: 50%;
    }
    
    
    
</style>
    
    <div class="panel panel-default" id="edadaptor">
        
        <div class="panel-heading">
            
            <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title; ?></h3>
            
        </div>
    
    <div class="panel-body">
        
        <ul class="nav nav-tabs">
            
            <li class="active"><a href="#tab-api" data-toggle="tab"><?php echo $tab_api; ?></a></li>
            
            <li><a href="#tab-adaptation" data-toggle="tab"><?php echo $tab_adaptation; ?></a></li>
            
            <li><a href="#tab-mapping" data-toggle="tab"><?php echo $tab_mapping; ?></a></li>
            
            <li><a href="#tab-information" data-toggle="tab"><?php echo $tab_information; ?></a></li>
            
        </ul>
        
        <div class="tab-content">
            
            <div class="tab-pane active" id="tab-api">
                
                <div align="right">
                    
                    <button type="submit" form="form-api" onclick="$(this).hide()" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $button_save; ?></button>
                    
                </div>
                
                <br>
                
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-api" class="form-horizontal">
                
                    
                    <?php
                    
                        $field_name = 'api';
                    
                    ?>
                
                    <?php foreach ($api_fields as $field => $field_param){ ?>
                    
                        <?php

                            $readonly = '';

                            if(isset($field_param['readonly']) && $field_param['readonly']){

                                $readonly = 'readonly=""';

                            }

                            $default = '';

                            if(isset($field_param['default']) && $field_param['default']){

                                $default = $field_param['default'];

                            }

                        ?>

                        <?php if ($field_param['type']=='input'){ ?>

                                <div class="form-group">
                                    
                                    <label class="col-sm-2 control-label" for="<?php echo $field_name.$field; ?>">
                                        <?php echo ${'entry_'.$field}; ?>
                                        <?php if ($field=='private_key'){ ?>
                                            <a onclick="generatePrivateKey()" style="border-bottom: 1px dashed; color: #00b527 !important; cursor: pointer" ><?php echo $entry_generate_private_key ?></a>
                                        <?php } ?>
                                    </label>
                                    
                                    <div class="col-sm-10">
                                        
                                        <input <?php echo $readonly; ?> type="text" name="<?php echo $field_name; ?>[<?php echo $field; ?>]" value="<?php echo isset(${$field_name}[$field]) ? ${$field_name}[$field] : $default; ?>" placeholder="<?php echo ${'entry_'.$field}; ?>" id="<?php echo $field_name.$field; ?>" class="form-control" />
                                            
                                    </div>
                                        
                                </div>

                        <?php }elseif ($field_param['type']=='select' && ${$field.'s'}){ ?>
                        
                                <div class="form-group">
                                    
                                    <label class="col-sm-2 control-label" for="<?php echo $field_name.$field; ?>"><?php echo ${'entry_'.$field}; ?></label>
                                    
                                    <div class="col-sm-10">
                                        
                                        <select class="form-control" name="<?php echo $field_name; ?>[<?php echo $field; ?>]" id="<?php echo $field_name.$field; ?>">
                                            
                                            <?php foreach(${$field.'s'} as $field_option){ ?>

                                                <?php if(isset(${$field_name}[$field]) && ${$field_name}[$field] == $field_option){ ?>
                                                
                                                    <option selected="" value="<?php echo $field_option; ?>"><?php echo $field_option; ?></option>
                                                    
                                                <?php }elseif(!isset(${$field_name}[$field]) && $default == $field_option){ ?>
                                                
                                                    <option selected="" value="<?php echo $field_option; ?>"><?php echo $field_option; ?></option>
                                                    
                                                <?php }else{ ?>
                                                
                                                    <option value="<?php echo $field_option; ?>"><?php echo $field_option; ?></option>
                                                    
                                                <?php } ?>

                                            <?php } ?>
                                            
                                        </select>
                                                    
                                    </div>
                                                    
                                </div>

                        <?php } ?>

                    <?php } ?>
                
                    <div class="form-group">

                        <label class="col-sm-2 control-label"><?php echo $text_local_error ?></label>

                        <div class="col-sm-10">

                            <div class="alert alert-info"><?php echo $status['local_error'] ?></div>

                        </div>

                        <label class="col-sm-2 control-label"><?php echo $text_edadapter_error ?></label>

                        <div class="col-sm-10">

                            <div class="alert alert-info"><?php echo $status['edadapter_error'] ?></div>

                        </div>

                        <label class="col-sm-2 control-label"><?php echo $text_api_welcome ?></label>

                        <div class="col-sm-10">

                            <div class="alert alert-info2"><?php echo $status['api.welcome'] ?></div>

                        </div>

                    </div>

                </form>                       
                                                
            </div>
            
            <div class="tab-pane" id="tab-mapping">

                <h3><?php $title_mapping ?></h3>
                
                <div class='alert alert-info'><?php echo $text_mapping; ?></div>
                
            </div>
                                                
            <div class="tab-pane" id="tab-information">

                <?php echo $information; ?>
                
            </div>
                                                
            <div class="tab-pane" id="tab-adaptation">
            
                <div align="right">
                    
                    <button  onclick="$(this).hide()"  type="submit" form="form-adaptation" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $button_save; ?></button>
                    
                </div>
                
                <br>
                
                <form id="form-adaptation" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" class="form-horizontal">
            
                    <div class="row">

                        <div class="col-sm-2">

                            <ul class="nav nav-pills nav-stacked">

                                <li onclick="getAdaptation(0,0); $('#form-adaptation').attr('action','<?php echo $action; ?>'+'&adaptation_id=0')" class="active"><a data-toggle="tab" id='tab-adaptation-nav0' href="#tab-adaptation0"> <?php echo $entry_new_adaptation ?></a></li>

                                <?php if($adaptations){ ?>

                                    <?php foreach($adaptations as $adaptation_id => $param){ ?>

                                        <?php

                                            $css_adaptation = '';

                                            if($param['setting']['status'] && $param['setting']['status']==1){

                                                $css_adaptation = 'border-left:3px solid #50aa0a;';

                                            }else{

                                                $css_adaptation = 'border-left:3px solid #cccccc;'; 

                                            }

                                        ?>

                                        <li onclick="getAdaptation('<?php echo $adaptation_id ?>',0); $('#form-adaptation').attr('action','<?php echo $action; ?>'+'&adaptation_id=<?php echo $adaptation_id ?>')"><a data-toggle="tab" id='tab-adaptation-nav<?php echo $adaptation_id;?>' style="<?php echo $css_adaptation; ?>" href="#tab-adaptation<?php echo $adaptation_id;?>"> <?php echo $param['setting']['title']; ?></a></li>

                                    <?php } ?>

                                <?php } ?>

                            </ul>

                        </div>

                        <div class="col-sm-10">	
                            
                            <div class="tab-content">
                                
                                <?php $adaptation_id = 0; ?>

                                <?php $param = array(); ?>

                                <div id="tab-adaptation<?php echo $adaptation_id ?>" class="tab-pane tab-adaptation active" >
                                    
                                    <?php if(!$adaptations){ ?>
                                    
                                        <?php echo $text_no_adaptations ?>
                                    
                                    <?php }else{ ?>   
                                        
                                        <?php echo $text_yes_adaptations ?>
                                        
                                    <?php } ?>
                                    
                                </div>

                                <?php if($adaptations){ ?>

                                    <?php foreach($adaptations as $adaptation_id => $param){ ?>

                                        <div id="tab-adaptation<?php echo $adaptation_id ?>" class="tab-pane tab-adaptation" >

                                        </div>

                                    <?php } ?>

                                <?php } ?>
                                
                            </div>
                            
                        </div>
                        
                    </div>
                    
                </form>
                
            </div>
        
        </div>
    </div>
    </div>
    </div>
<div  style="text-align: center"><div class="edadaptor-logo" style="display: inline"><span>EDAdaptor</span> ©, v.<?php echo $edadaptor_version ?></div></div>
</div>
<script type="text/javascript"><!--  

        $(document).ready(function() {

            $("a[href=\'#<?php echo $open_tab ?>\']").click();

        });
    
        function getAdaptation(adaptation_id,sample_adaptation_id){
            $('#tab-adaptation'+adaptation_id).html('<img src="/admin/view/image/edadaptor_loading.gif" />');
            $.ajax({
                type: 'GET',
                url: 'index.php?route=<?php echo $path_opencart_version ?>/edadaptor/getAdaptation&adaptation_id='+adaptation_id+'&sample_adaptation_id='+sample_adaptation_id+'&<?php echo $token_name ?>=<?php echo ${$token_name}; ?>', 
                dataType: 'html',
                success: function(response) {
                    $('.tab-adaptation').empty();
                    $('#tab-adaptation'+adaptation_id).html(response);
                },
                <?php if ($debug_mode) { ?>
                    error: function(xhr, ajaxOptions, thrownError) {
                            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                <?php } ?>
            });
        
        }
        
        function getParamDataAdaptations(adaptation_id,sample_adaptation_id,data_adaptation_id,direction,validation){
            $('#'+direction+'_data_adaptation_box'+adaptation_id).html('<img src="/admin/view/image/edadaptor_loading.gif" />');
            $.ajax({
                type: 'post',
                data: $('#form-adaptation').serialize(),
                url: 'index.php?route=<?php echo $path_opencart_version ?>/edadaptor/getParamDataAdaptations&sample_adaptation_id='+sample_adaptation_id+'&adaptation_id='+adaptation_id+'&data_adaptation_id='+data_adaptation_id+'&validation='+validation+'&direction='+direction+'&<?php echo $token_name ?>=<?php echo ${$token_name}; ?>', 
                dataType: 'html',
                success: function(response) {
                    $('#'+direction+'_data_adaptation_box'+adaptation_id).html(response);
                },
                <?php if ($debug_mode) { ?>
                    error: function(xhr, ajaxOptions, thrownError) {
                            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                <?php } ?>
            });

        }
        
        function generatePrivateKey(){
        
            var private_key = Math.floor(Math.random() * (99999999 - 10000000) + 10000000);
            
            $('input[name=\'api[private_key]\']').val(private_key);

        }
    
//--></script> 
<?php echo $footer; ?>