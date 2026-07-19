<?php if(!$errors){ ?>

<?php if($format_data=='csv'){ ?>
<ul  class="nav nav-tabs sh-tab-n" >
    <li class="active sh-tab"><a onclick="showHide('.stepTwoTempl');" style="cursor: pointer"><h2><?php echo $text_step_2_synchronization ?></h2></a></li></ul>
    <div class="stepTwoTempl">
<?php if($type_process=='import'){ ?>
<a onclick="showHide('#check_row_info');$('#check_row_info_2').hide();" class="btn btn-danger" style="margin-bottom: 25px;">
            <span><?php echo $text_check_row ?></span>
        </a>

<?php if(isset($tamplate_data_selected['getCSVAsHTML']) && $tamplate_data_selected['getCSVAsHTML'] && isset($anyxml_link_on_file) && $anyxml_link_on_file){ ?>

        
    <a onclick="showHide('#check_row_info_2');$('#check_row_info').hide();"  class="btn btn-danger" style="margin-bottom: 25px;">
            <span><?php echo $entry_anyxml_link_on_file ?></span>
        </a>
<a target="_blank" href="<?php echo $tamplate_data_selected['getCSVAsHTML']; ?>" class="btn btn-danger" style="margin-bottom: 25px;">
            <span>Посмотреть в формате HTML</span>
        </a>
<div id="check_row_info_2" class="well" style="display: none;margin-bottom: 25px;">
            <table  class="table table-bordered table-hover">
    <tr>
                    <td class="text-left">
                        <?php echo $entry_anyxml_link_on_file ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $anyxml_link_on_file; ?>
                        </div>
                    </td>
                </tr>
    <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_delimiter ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_delimiter']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_enclosure ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_enclosure']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_escape ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_escape']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_encoding ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                        <?php echo  $tamplate_data_selected['encoding'] ?>
                        </div>
                    </td>
                </tr>
    
</table>
</div>
<?php }

elseif(isset($tamplate_data_selected['getCSVAsHTML']) && $tamplate_data_selected['getCSVAsHTML'] && isset($anyxls_link_on_file) && $anyxls_link_on_file){ ?>

        
<a onclick="showHide('#check_row_info_2');$('#check_row_info').hide()"  class="btn btn-danger" style="margin-bottom: 25px;">
            <span><?php echo $entry_anyxls_link_on_file ?></span>
        </a>
<a target="_blank" href="<?php echo $tamplate_data_selected['getCSVAsHTML']; ?>" class="btn btn-danger" style="margin-bottom: 25px;">
            <span>Посмотреть в формате HTML</span>
        </a>
<div id="check_row_info_2" class="well" style="display: none;margin-bottom: 25px;">
            <table  class="table table-bordered table-hover">
    <tr>
                    <td class="text-left">
                        <?php echo $entry_anyxls_link_on_file ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            Ссылка на cache-файл в формате DSV
                        </div>
                    </td>
                </tr>
    <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_delimiter ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_delimiter']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_enclosure ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_enclosure']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_escape ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_escape']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_encoding ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                        <?php echo  $tamplate_data_selected['encoding'] ?>
                        </div>
                    </td>
                </tr>
    
</table>
</div>
<?php } elseif(isset($tamplate_data_selected['getCSVAsHTML']) && $tamplate_data_selected['getCSVAsHTML'] && isset($anyyml_link_on_file) && $anyyml_link_on_file && !is_array($anyyml_link_on_file)){ ?>

        
    <a onclick="showHide('#check_row_info_2');$('#check_row_info').hide()"  class="btn btn-danger" style="margin-bottom: 25px;">
            <span><?php echo $entry_anyxml_link_on_file ?></span>
        </a>
<a target="_blank" href="<?php echo $tamplate_data_selected['getCSVAsHTML']; ?>" class="btn btn-danger" style="margin-bottom: 25px;">
            <span>Посмотреть в формате HTML</span>
        </a>
<div id="check_row_info_2" class="well" style="display: none;margin-bottom: 25px;">
            <table  class="table table-bordered table-hover">
    <tr>
                    <td class="text-left">
                        Ссылка на cache-файл в формате DSV
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $anyyml_link_on_file; ?>
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <td class="text-left">
                        Затраты ресурсов на подготовку файла (время/ОЗУ (включая ОЗУ на работу фреймворка OpenCart))
                        <br><small>Это время потребуется при импорте файла при запуске импорта</small>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $anyyml_max_exe_time; ?> / <?php echo $anyyml_memory_usage; ?> <?php echo $anyyml_time_on_item; ?>
                        </div>
                    </td>
                </tr>
                
    <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_delimiter ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_delimiter']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_enclosure ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_enclosure']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_escape ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_escape']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_encoding ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                        <?php echo  $tamplate_data_selected['encoding'] ?>
                        </div>
                    </td>
                </tr>
    
</table>
</div>
<?php } elseif(isset($tamplate_data_selected['getCSVAsHTML']) && $tamplate_data_selected['getCSVAsHTML'] && isset($anyyml_link_on_file) && $anyyml_link_on_file && is_array($anyyml_link_on_file)){ ?>

        
    <a onclick="showHide('#check_row_info_2');$('#check_row_info').hide()"  class="btn btn-danger" style="margin-bottom: 25px;">
            <span><?php echo $entry_anyxls_link_on_file ?></span>
        </a>
<a target="_blank" href="<?php echo $tamplate_data_selected['getCSVAsHTML']; ?>" class="btn btn-danger" style="margin-bottom: 25px;">
            <span>Посмотреть в формате HTML</span>
        </a>
<div id="check_row_info_2" class="well" style="display: none;margin-bottom: 25px;">
            <table  class="table table-bordered table-hover">
    <tr>
                    <td class="text-left">
                        Ссылки на файлы
                    </td>
                    <td class="text-left">
                        
                        <?php foreach($anyyml_link_on_file as $anyyml_link_on_file_row){ ?>
                        
                        <div class="input-group">
                            <?php echo $anyyml_link_on_file_row; ?>
                        </div>
                        
                        <?php } ?>
                        
                    </td>
                </tr>
                
                <tr>
                    <td class="text-left">
                        Затраты ресурсов на подготовку файла (время/ОЗУ (включая ОЗУ на работу фреймворка OpenCart))
                        <br><small>Это время потребуется при импорте файла при запуске импорта</small>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $anyyml_max_exe_time; ?> / <?php echo $anyyml_memory_usage; ?> <?php echo $anyyml_time_on_item; ?>
                        </div>
                    </td>
                </tr>
                
    <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_delimiter ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_delimiter']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_enclosure ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_enclosure']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_escape ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <?php echo $tamplate_data_selected['csv_escape']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_encoding ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                        <?php echo  $tamplate_data_selected['encoding'] ?>
                        </div>
                    </td>
                </tr>
    
</table>
</div>
<?php } ?>
        
        <div id="check_row_info" class="well" style="display: none;margin-bottom: 25px;">
        
            <p class="alert alert-success"><?php echo $text_count_fields ?>: <b><?php echo $count_fields ?></b>, <?php echo $text_count_rows ?>: <b><?php echo $count_rows ?></b></p>
            
                <?php if($anycsv_sinch_link_on_file){ ?>
                    <p>Ссылка на файл: <?php echo $anycsv_sinch_link_on_file ?></p>
                <?php } ?>
                
                <input type="hidden" name="odmpro_tamplate_data[anycsv_sinch_file_upload]" value="<?php echo $anycsv_sinch_file_upload; ?>" />
        
        <p>
            <?php echo $text_check_row_info ?>
        </p>

        <div class="well well-sm" style="overflow-x: auto; background: white; margin-bottom: 0px;">

                <?php if($csv_data_last_row_for_view){ ?>

                    <table class="table table-bordered table-hover">
                        <tr>
                            <?php foreach($data_rows as $field){ ?>
                                <td>
                                    <?php echo $field ?>
                                </td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <?php foreach($csv_data_last_row_for_view as $csv_data_last_row_for_view_row){ ?>
                                <td>
                                    <?php echo $csv_data_last_row_for_view_row ?>
                                </td>
                            <?php } ?>
                        </tr>
                    </table>

                <?php }else{ ?>

                    <div class="alert alert-info"><?php echo $text_check_row_empty ?></div>

                <?php } ?>

            </div>

        </div>

<?php } ?>

<?php if(isset($yandex_market_categories) && $yandex_market_categories && isset($yandex_market_categories_site_cats) && $yandex_market_categories_site_cats){ ?> 
                                
<h3>Сопоставление категорий файла XML и категорий сайта</h3>
   
<div class="well" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-bordered table-hover">
                                <tbody>

                                <thead>
                                    <tr>
                                        <td class="text-center">

                                                  Категория файла

                                              </td>
                                              <td class="text-center">

                                                  Категория сайта

                                              </td>
                                    </tr>
                                </thead>

                                      <?php foreach($yandex_market_categories as $yandex_market_category_path){ ?>

                                          <tr>

                                              <td class="text-center">

                                                  <?php echo $yandex_market_category_path; ?>

                                              </td>

                                              <td class="text-left">

                                                  <input type="text" name='category' ya_market_category_id='<?php echo md5($yandex_market_category_path); ?>' id="input-category<?php echo md5($yandex_market_category_path); ?>" value=""  class="form-control" />

                                                  <div id="my-ya_market_category_id<?php echo md5($yandex_market_category_path); ?>" class="my-product-list-category-box">


                                                          <?php foreach($yandex_market_categories_site_cats as $site_cats_category_id => $site_cats_value){ ?>

                                                              <?php if(isset($tamplate_data_selected['yandex_market_category_sich'][ md5($yandex_market_category_path) ][$site_cats_category_id] )){ ?>

                                                                  <div id="my-ya_market_category_id<?php echo md5($yandex_market_category_path); ?><?php echo $site_cats_category_id ?>" class="my-product-list-category-box">
                                                                       <i class="fa fa-minus-circle"></i> <?php echo $site_cats_value['name'] ?>
                                                                       <input type="hidden" name="odmpro_tamplate_data[yandex_market_category_sich][<?php echo md5($yandex_market_category_path) ?>][<?php echo $site_cats_category_id;?>]" value="<?php echo $site_cats_category_id; ?>" />
                                                                  </div>

                                                              <?php } ?>

                                                          <?php } ?>


                                                  </div>

                                              </td>

                                          </tr>

                                      <?php } ?> 

                                </tbody>
                              </table>

</div>

                                <script type="text/javascript"><!--

                                $('input[name=\'category\']').autocomplete({
                                            'source': function(request, response) {
                                                $.ajax({
                                                        url: 'index.php?route=catalog/category/autocomplete&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>&filter_name=' +  encodeURIComponent(request),
                                                        dataType: 'json',
                                                                success: function(json) {
                                                                        response($.map(json, function(item) {
                                                                                return {
                                                                                        label: item['name'],
                                                                                        value: item['category_id']
                                                                                }
                                                                        }));
                                                                }
                                                        });
                                                },
                                            'select': function(item) {

                                                var ya_market_category_id =  $(this).attr('ya_market_category_id');

                                                $('input[name=\'category\']').val('');

                                                $('#my-ya_market_category_id' + ya_market_category_id + item['value']).remove();

                                                $('#my-ya_market_category_id'+ya_market_category_id).append('<div id="my-product-list-category' + ya_market_category_id + item['value'] + '" class="my-product-list-category-box"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="odmpro_tamplate_data[yandex_market_category_sich]['+ya_market_category_id+'][' + item['value'] + ']" value="' + item['value'] + '" /></div>');

                                            }
                                        });

                                $('.my-product-list-category-box').delegate('.fa-minus-circle', 'click', function() {
                                        $(this).parent().remove();
                                });

                                        //--></script> 
                                
                                
                                <?php } ?> 







<table class="table table-bordered table-hover">
    
        <thead>
            <tr>
                <td><?php echo $text_column_field_to_file; ?></td>
                
                <?php if($type_process=='export'){ ?>
                    <td colspan="3"><?php echo $text_column_param_field_to_file; ?></td>
                <?php }else{ ?>
                    <td>
                        
                        <?php 
                        
                        
                        echo $text_column_param_field_to_file; ?>
                        
                        
                        <label style="font-weight: normal; font-size: 10px;">
                            <?php 
                            
                            if(isset($tamplate_data_selected['hide_column']) && $tamplate_data_selected['hide_column']){ ?>
                                <input onclick="hideColumn()" type="checkbox" value="1" name="odmpro_tamplate_data[hide_column]" checked="">
                            <?php }else{ ?>
                                <input onclick="hideColumn()" type="checkbox" value="1" name="odmpro_tamplate_data[hide_column]" >
                            <?php } ?>
                            
                            
                            <?php echo $text_type_data_hide_column; ?>
                        </label>
                    </td>
                    <td colspan="2"><?php echo $text_column_param_field_to_file_param; ?></td>
                <?php } ?>
                
            </tr>
        </thead>
        <tbody>
            <?php
              
                $position_field_to_file = 0;
                
                foreach($data_rows as $field){
              
            ?>
                  <tr id="type_data_column_<?php echo md5($field) ?>_table_row">
                      <td class="text-left" width="20%">
                          
                            <?php if($type_process=='export'){ ?>

                                <input onchange="setNewTypesDataColumns('<?php echo $field ?>','<?php echo md5($field) ?>',this.value,'<?php echo $type_process ?>',this.name,'<?php echo $tamplate_data_selected['id'] ?>');" value="<?php echo $field ?>" class="form-control" name="odmpro_tamplate_data[export_field_name][<?php echo $tamplate_data_selected['id'] ?>][<?php echo $field ?>]" />
                                
                                <input type="hidden" value="<?php echo $excel_column_name_by_column_name[$field] ?>" class="form-control" name="odmpro_tamplate_data[excel_column_name_by_column_name][<?php echo $field ?>]" />
                                
                            <?php }elseif($type_process=='import'){ ?>
                          
                            <div class="field-file">
                                <?php echo $field ?><br>
                                <div style="font-size: 8px;">Для трансформации: <span style="color: orange; font-size: 8px;">[[<?php echo $field ?>]]</span></div>
                                    <div style="font-size: 8px;">Для EXCEL-трансформации: <span style="color: greenyellow; font-size: 8px;"><?php echo $excel_column_name_by_column_name[$field] ?></span></div>
                            </div>
                                
                                <input type="hidden" value="<?php echo $field ?>" class="form-control" name="odmpro_tamplate_data[export_field_name][<?php echo $tamplate_data_selected['id'] ?>][<?php echo $field ?>]" />
                          
                                <input type="hidden" value="<?php echo $excel_column_name_by_column_name[$field] ?>" class="form-control" name="odmpro_tamplate_data[excel_column_name_by_column_name][<?php echo $field ?>]" />
                                
                                <div style="margin-top: 5px; max-height: 100px; overflow-y: auto; " >

                                    <?php foreach($csv_data_for_view['data'] as $csv_data_for_view_row){ ?>

                                        <?php foreach($csv_data_for_view_row as $position_field_to_file_this => $csv_data_for_view_row_field){ ?>

                                            <?php if($position_field_to_file_this == $position_field_to_file){ ?>

                                            <table class='table table-bordered table-hover' style="margin-bottom: 0px; margin-top: -1px; ">
                                                  <tr>
                                                      <td  style="padding: 3px;"><div class="field-view-file-data"><?php echo $csv_data_for_view_row_field; ?></div></td>
                                                  </tr>
                                            </table>

                                            <?php } ?>

                                        <?php } ?>

                                    <?php

                                    }

                                    $position_field_to_file++;

                                    ?>

                                </div>
                          
                          <?php } ?>
                          
                      </td>
                      <td class="text-left" width="20%">
                          <div class="input-group" >
                              <select onchange="getTypesDataColumns(this.value,'#type_data_column_<?php echo md5($field) ?>','<?php echo $field; ?>','<?php echo $type_process ?>');" name="odmpro_tamplate_data[type_data][<?php echo $field ?>]"  class="form-control select-type-data">
                                  <option value="0" ><?php echo $text_type_data_ignor; ?></option>
                                      <?php foreach($types_data as $type => $columns){ ?>
                                          <?php if(isset($tamplate_data_selected['type_data'][$field]) && $tamplate_data_selected['type_data'][$field] == $type ){ ?>
                                  <option value="<?php echo $type ?>" selected="" ><?php echo ${'text_type_data_'.$type}; ?></option>
                                          <?php }else{ ?>
                                  <option value="<?php echo $type ?>" ><?php echo ${'text_type_data_'.$type}; ?></option> 
                                          <?php } ?>
                                      <?php } ?>
                              </select>
                          </div>
                      </td>
                      <td class="text-left" id="type_data_column_<?php echo md5($field) ?>" colspan="2">
                          
                      </td>
                      <?php if($type_process=='export'){ ?>
                      
                            <td class="text-left"><button type="button" onclick="$('#type_data_column_<?php echo md5($field) ?>_table_row').remove();" data-toggle="tooltip" title="" class="btn btn-danger" ><i class="fa fa-minus-circle"></i></button></td>
                      
                      <?php } ?>
                  </tr>
                  
                  
                
                  
                  
            <?php } ?>
            
            
            <?php if(isset($self_column) && $self_column){ ?>
            
                    <?php echo $self_column; ?>

            <?php } ?>
            
            <?php if($type_process=='export'){ ?>
            
            <tr id="addTypeDataColumnTableRow_<?php echo $type_process ?>">
                <td colspan="4">
                    <a onclick="addTypeDataColumnTableRow('<?php echo $type_process ?>','<?php echo $tamplate_data_selected['id'] ?>')" data-toggle="tooltip" title="" class="btn btn-primary"><i class="fa fa-plus"></i> Добавить обычную колонку</a>
                </td>
            </tr>
            
            <?php } ?>
            
          </tbody>
          
</table>
                                          
                                          
                                          
    <div id="typesDataGeneralSetting">
        
        

    </div>

                                          <h3><?php echo $entry_odmpro_tamplate_data ?></h3>



                                          <div class="well">
<table class="table table-bordered table-hover">
        <tbody>
            <?php if($type_process=='export'){ ?>
            
            
            <tr style="display: none;">
                    
                    
            <?php }else{ ?>
            
            <tr>
            
            <?php } ?>
                    <td class="text-left" width="20%">
                        <?php echo $entry_type_change; ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <select onchange="if(this.value=='new_data' && !confirm('ВНИМАНИЕ! Используйте этот режим добавления один раз. Второй импорт в этом режиме снова создаст все данные, как новые, что может привести к созданию дублей. Используйте остальные режимы обмена (с идентификацией), чтобы избежать создание дублей')){ this.value = 0; }" name="odmpro_tamplate_data[type_change]"  class="form-control">
                                  <option value="0" ><?php echo $entry_select; ?></option>
                                      <?php foreach($types_change as $type_change => $type_change_title){ ?>
                                          <?php if(isset($tamplate_data_selected['type_change']) && $tamplate_data_selected['type_change'] == $type_change ){ ?>
                                  <option value="<?php echo $type_change ?>" selected="" ><?php echo $type_change_title; ?></option>
                                          <?php }else{ ?>
                                  <option value="<?php echo $type_change ?>" ><?php echo $type_change_title; ?></option> 
                                          <?php } ?>
                                      <?php } ?>
                              </select>
                        </div>
                    </td>
                </tr>
            
              <tr>
                      <td class="text-left" width="20%">
                          <?php echo $entry_odmpro_tamplate_data_name ?>
                      </td>
                      <td class="text-left">
                          <?php if($tamplate_data_selected['id']){ ?>
                            <input placeholder="<?php echo $entry_odmpro_tamplate_data_name ?>" name="odmpro_tamplate_data[name]" value="<?php echo $tamplate_data_selected['name'] ?>"  class="form-control" />
                          <?php }else{ ?>
                            <input placeholder="<?php echo $entry_odmpro_tamplate_data_name ?>" name="odmpro_tamplate_data[name]" value="<?php echo $tamplate_data_name_new; ?>"  class="form-control" />
                          <?php } ?>
                      </td>
                </tr>
                <tr>
                    
                      <td class="text-left" width="20%">
                          <?php echo $entry_odmpro_tamplate_type_save ?>
                      </td>
                    <td class="text-left"  >
                          <div class="input-group">
                              <select class="form-control" id="setTemplateDataTypeAction">
                                    
                                  <?php if($tamplate_data_selected['id']){ ?>
                                    <option value="save" ><?php echo $entry_odmpro_tamplate_data_save; ?></option>
                                    <option value="update" selected=""><?php echo $entry_odmpro_tamplate_data_update; ?></option>
                                    <option value="delete" ><?php echo $entry_odmpro_tamplate_data_delete; ?></option>
                                  <?php }else{ ?>
                                    <option value="save" ><?php echo $entry_odmpro_tamplate_data_save; ?></option>
                                    <option style="color: #ccc " value="update" ><?php echo $entry_odmpro_tamplate_data_update; ?></option>
                                    <option style="color: #ccc " value="delete" ><?php echo $entry_odmpro_tamplate_data_delete; ?></option>
                                  <?php } ?>
                                  
                              </select>
                              
                          </div>
                          
                      </td>
                </tr>
          </tbody>
          
</table>
                                          </div>
       <a id="setTemplateDataBtn" onclick="setTemplateData($('#setTemplateDataTypeAction').val(),'<?php echo $type_process ?>')" class="btn btn-primary setTemplateDataBtn"><?php echo $entry_odmpro_tamplate_data_save_tamplate_data ?></a>
                          <span id="setTemplateData" class="alert alert-info" style="display: none"></span>       
                          
                          <hr>
                          
                          
                          
                          <?php if($text_lic_error){ ?>
                          
                          <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $text_lic_error; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                          </div>
                          
<?php }elseif($type_process=='import'){ ?>
  

</div>

<ul  class="nav nav-tabs sh-tab-n" >
    <li class="active sh-tab"><a onclick="showHide('.stepThreeTempl');" style="cursor: pointer"><h2><?php echo $text_step_3_ending ?></h2></a></li></ul>

<table class="table table-bordered table-hover stepThreeTempl">
        <tbody>
              <tr>
                      <td class="text-center" width="20%">
                          <a id="startImport" onclick="startImport($('input[name=\'odmpro_tamplate_data[start]\']').val());" class="btn btn-primary orangec" style="width: 100%"><?php echo $text_step_3_start_import; ?></a><a style="display: none"  id="startImport_stop"  class="btn btn-danger" onclick="stopProcess('import')"><i class="fa fa-stop"></i> Остановить</a>
                          <div id="product_demoimport_box" style="display: none;     margin-top: 5px;border: 1px solid orange;border-radius: 5px 5px 5px 5px;"><input type="checkbox" name="odmpro_tamplate_data[get_result_demo]" value="1" /> Демоимпорт<span data-toggle="tooltip" title="" data-original-title="Включите этот режим, чтобы посмотреть, какие именно данные будут размещаться при работе с товарами. ВНИМАНИЕ! При включении товары не будут заведены, но справочники: категории, производители, опции, атрибуты и пр., которые присутствуют в данных, в выбранном числе строк за проход - эти данные будут заведены"></span></div>
                          <input type="hidden" name="type_process" value="import"  />
                      </td>
                      <td class="text-left">
                          <label><?php echo $text_step_3_start ?></label>
                          <input placeholder="<?php echo $text_step_3_start ?>" name="odmpro_tamplate_data[start]" value="<?php echo $tamplate_data_selected['start'] ?>"  class="form-control" />
                      </td>
                      <td class="text-left">
                          <label><?php echo $text_step_3_limit ?></label>
                          <input placeholder="<?php echo $text_step_3_limit ?>" name="odmpro_tamplate_data[limit]" value="<?php echo $tamplate_data_selected['limit'] ?>"  class="form-control" />
                      </td>
              </tr>
              <tr >
                  <td colspan="3" id="importStartMessages">
                      
                  </td>
              </tr>
              <tr >
                  <td colspan="3" id="importDemoResult_td" style="display: none"  >
                      <h2>Результат демоимпорта</h2>
                      <div id="importDemoResult" class="well" style="max-height: 700px; overflow-y: auto">
                          
                      </div>
                  </td>
              </tr>
        </tbody>
</table>


<?php if(isset($tamplate_data_selected['new_file_upload']) && !is_array($tamplate_data_selected['new_file_upload'])){ ?>
                                          
    <input type="hidden" value="<?php echo $tamplate_data_selected['new_file_upload'] ?>" class="form-control" name="odmpro_tamplate_data[new_file_upload]" />
                  
<?php }elseif( isset($tamplate_data_selected['new_file_upload']) && is_array($tamplate_data_selected['new_file_upload']) ){ ?>

    <?php foreach($tamplate_data_selected['new_file_upload'] as $new_file_upload){ ?>
    
        <input type="hidden" value="<?php echo $new_file_upload; ?>" class="form-control" name="odmpro_tamplate_data[new_file_upload][]" />
    
    <?php } ?>

<?php } ?>

<?php }elseif($type_process=='export'){ ?>

<ul  class="nav nav-tabs sh-tab-n" >
    <li class="active sh-tab"><a><h2><?php echo $text_step_3_ending_export ?></h2></a></li></ul>

<table class="table table-bordered table-hover">
        <tbody>
              <tr>
                      <td class="text-center" width="20%">
                          <a id="startExport" onclick="startExport($('input[name=\'odmpro_tamplate_data[start]\']').val(),1);" class="btn btn-primary orangec"><i class="fa fa-play-circle"></i> <?php echo $text_step_3_start_export; ?></a><a style="display: none"  id="startExport_stop"  class="btn btn-danger" onclick="stopProcess('export')"><i class="fa fa-stop"></i> Остановить</a>
                          <input type="hidden" name="type_process" value="export"  />
                          <span id="startExportLoading" ></span>
                      </td>
                      <td class="text-left">
                          <input placeholder="<?php echo $text_step_3_start ?>" name="odmpro_tamplate_data[start]" value="<?php echo $tamplate_data_selected['start'] ?>"  class="form-control" />
                      </td>
                      <td class="text-left">
                          <input placeholder="<?php echo $text_step_3_limit ?>" name="odmpro_tamplate_data[limit]" value="<?php echo $tamplate_data_selected['limit'] ?>"  class="form-control" />
                      </td>
              </tr>
              <tr >
                  <td colspan="3" id="exportStartMessages">
                      
                  </td>
              </tr>
        </tbody>
</table>

<?php } ?>

<?php }else{ ?>

    <?php echo $entry_odmpro_format_data_empty; ?>

<?php } ?>


<?php if(isset($data_rows_array)){ ?>

    <input type="hidden" value="<?php echo $data_rows_array; ?>" class="form-control" name="odmpro_tamplate_data[data_rows_array]" />

<?php } ?>

<script type="text/javascript"><!--
    
var self_column_name_num = 1;

function addSelfTypeDataColumnTableRow(type_process,template_id){

    var self_column_id = new Date().getTime();
    
    var column_name = 'Новая колонка '+self_column_name_num;
    
    self_column_name_num += 1;
    
    var html = '';
        html += '<tr id="type_data_self_column_'+self_column_id+'_table_row" class="self_column_table_row">';
        html +=  '<td class="text-left" width="20%">';
        
        html +=    '<input value="'+column_name+'" class="form-control" name="odmpro_tamplate_data[self_column]['+self_column_id+'][import_self_column_name]" />';
        html +=    '<input type="hidden" value="'+self_column_id+'" class="form-control" name="odmpro_tamplate_data[self_column]['+self_column_id+'][import_self_column_id]" />';
        
        html +=  '</td>';
        html +=  '<td class="text-left" width="20%">';
	html += 	  '<div class="input-group">';
        html += '<select onchange="getTypesDataSelfColumns(this.value,\'#type_data_column_'+self_column_id+'\',\''+self_column_id+'\',\''+type_process+'\');" name="odmpro_tamplate_data[type_data]['+self_column_id+']"  class="form-control select-type-data">';
        html += '<option value="0" >Не использовать</option>';
                
        <?php foreach($types_data as $type => $columns){ ?>
            html += '<option value="<?php echo $type ?>" ><?php echo ${'text_type_data_'.$type}; ?></option>';
        <?php } ?>
        
        html +=          '</select>';
        html += 	  '</div>';
        html +=  '</td>';
        html +=  '<td class="text-left" id="type_data_column_'+self_column_id+'">';
        html +=  '</td>';
	html += '<td class="text-left"><button type="button" onclick="$(\'#type_data_self_column_'+self_column_id+'_table_row\').remove();" data-toggle="tooltip" title="" class="btn btn-danger" ><i class="fa fa-minus-circle"></i></button></td>';
        html += '</tr>';
        
        $('#addSelfTypeDataColumnTableRow_'+type_process).before(html);
        
}
    
//--></script> 

<script type="text/javascript"><!--
    
    
var column_name_num = <?php echo $column_name_num; ?>;

function addTypeDataColumnTableRow(type_process,template_id){

    var id_td = new Date().getTime();
    
    var column_name = 'column_'+column_name_num;
    
    column_name_num += 1;
    
    var html = '';
        html += '<tr id="type_data_column_'+id_td+'_table_row">';
        html +=  '<td class="text-left" width="20%">';
        html +=    '<input onchange="setNewTypesDataColumns(\''+column_name+'\',\''+id_td+'\',this.value,\''+type_process+'\',this.name,\''+template_id+'\');" value="'+column_name+'" class="form-control" name="odmpro_tamplate_data[export_field_name]['+template_id+']['+column_name+']">';
        html +=  '</td>';
        html +=  '<td class="text-left" width="20%">';
	html += 	  '<div class="input-group">';
        html += '<select onchange="getTypesDataColumns(this.value,\'#type_data_column_'+id_td+'\',\''+column_name+'\',\''+type_process+'\');" name="odmpro_tamplate_data[type_data]['+column_name+']"  class="form-control select-type-data">';
        html += '<option value="0" ><?php echo $text_type_data_ignor; ?></option>';
                
        <?php foreach($types_data as $type => $columns){ ?>
            html += '<option value="<?php echo $type ?>" ><?php echo ${'text_type_data_'.$type}; ?></option>';
        <?php } ?>
        
        html +=          '</select>';
        html += 	  '</div>';
        html +=  '</td>';
        html +=  '<td class="text-left" id="type_data_column_'+id_td+'">';
        html +=  '</td>';
	html += '<td class="text-left"><button type="button" onclick="$(\'#type_data_column_'+id_td+'_table_row\').remove();" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
        html += '</tr>';
        
        $('#addTypeDataColumnTableRow_'+type_process).before(html);
        
}
    
//--></script> 





<script type="text/javascript"><!--
    
    $(document).ready(function() {
        changeTypeData();
        showHide('.stepOneTempl');
        hideColumn();
        getTypesDataGeneralSetting("<?php echo $type_process ?>");
    });
    
//--></script> 
<?php }else{ ?>
    <h3>Ошибка проверки файла</h3>
    <?php foreach($errors as $error){ ?>
        <div class="alert alert-danger"><?php echo $error ?></div>
    <?php } ?>
<?php } ?>