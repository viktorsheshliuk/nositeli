<?php if($format_data=='csv'){ ?>

<input name="odmpro_tamplate_data[format_data]" type="hidden" value="<?php echo $format_data ?>" />

<input name="odmpro_tamplate_data[anycsv_sinch_supplier_setting_id]" type="hidden" value="<?php echo $anycsv_sinch_supplier_setting_id ?>" />

<table class="table table-bordered table-hover">
          <tbody>
                <tr>
                    <td class="text-left" style="width:25%">
                        Профиль настроек
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            
                            <?php if($demo_mode && $type_process=='import'){ ?>
                            <div class="info-box-modal" style="margin-bottom: 5px;"><?php echo $text_info_box_modal_step_1_import_csv ?></div>
                            <?php } ?>
                            
                        <?php if(!$tamplates_data){ ?>
                            <select name="odmpro_tamplate_data[id]"  class="form-control">
                                <option value="0" ><?php echo $entry_odmpro_tamplate_data_empty; ?></option>
                            </select>
                        <?php }else{ ?>
                        <select onchange="getStepOneSettings('<?php echo $format_data ?>',this.value,'<?php echo $type_process ?>');" name="odmpro_tamplate_data[id]"  class="form-control" style="width: 60%; margin-right: 10px;">
                                            <option value="0"  class="fsz14"><?php echo $entry_odmpro_tamplate_data_new; ?></option>
                                    <?php foreach($tamplates_data as $tamplate_data_key => $tamplate_data){ ?>
                                        <?php if( (isset($tamplate_data_selected['id']) && $tamplate_data_selected['id'] && $tamplate_data_selected['id'] == $tamplate_data_key) || (!$tamplate_data_selected['id'] && isset($anycsv_sinch_supplier_setting_id) && $anycsv_sinch_supplier_setting_id == $tamplate_data_key )  ){ ?>
                                            <option value="<?php echo $tamplate_data_key ?>" selected="" ><?php echo $tamplate_data['name']; ?></option>
                                        <?php }else{ ?>
                                            <option value="<?php echo $tamplate_data_key ?>" ><?php echo $tamplate_data['name']; ?></option> 
                                        <?php } ?>
                                    <?php } ?>
                            </select>
                        <?php } ?>
                        
                        
                        
                        <?php if(isset($tamplate_data_selected['id']) && $tamplate_data_selected['id'] || (isset($anycsv_sinch_supplier_setting_id) && $anycsv_sinch_supplier_setting_id) ){ ?>
                            <a style="float: left;" data-toggle="tooltip" title="" class="btn btn-primary" href="<?php echo $save_template_setting_link ?>" target="_blank" data-original-title="Сохранить"><i class="fa fa-save"></i></a>&nbsp;
                            
                            <button type="button" data-toggle="tooltip" title="" class="btn btn-danger" onclick="confirm('Данное действие необратимо. Вы уверены?') ? setTemplateData('delete','<?php echo $type_process ?>') : false;" data-original-title="Удалить"><i class="fa fa-trash-o"></i></button>
                            
                        <?php }else{ ?>
                        
                        <button style="float: left;" id="button-upload2" type="button" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="Загрузить"><i class="fa fa-folder-open"></i></button>
                        
                        <?php } ?>
                        
                        
                        
                        
                        </div>
                    </td>
                </tr>
                
                <?php if($type_process=='import'){ ?>
                        
                        <?php

                        $import_css = "";
                        $import_css2 = "";
                        $export_css = " style='display:none' ";

                        ?>
                        
                        
                       <script type="text/javascript">
                        $(document).ready(function() {

                                var format_set = 'dsv';

                                <?php if(isset($tamplate_data_selected['anyxls_xls_upload']) && $tamplate_data_selected['anyxls_xls_upload']){ ?>

                                    format_set = 'xls';

                                <?php }elseif(isset($tamplate_data_selected['anyyml_yml_upload']) && $tamplate_data_selected['anyyml_yml_upload']){ ?>

                                    format_set = 'xml';

                                <?php } ?>
                            
                                selectFormatSet(format_set,'<?php echo $type_process; ?>');
                            
                            });
                    </script>
                        
                        
                        

                   <?php }elseif($type_process=='export'){ ?>

                       <?php

                        $import_css = " style='display:none' ";
                        $import_css2 = " display:none; ";
                        $export_css = "";

                        ?>

                   <?php } ?>
                
                <tr  >
                    <td class="text-left" style="width:25%">
                        Формат
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            
                            
                            
                        <select  class="form-control fsz14" onchange="selectFormatSet(this.value,'<?php echo $type_process; ?>');" name="odmpro_tamplate_data[input_format]" >
                                <?php if(isset($tamplate_data_selected['anyxls_xls_upload']) && $tamplate_data_selected['anyxls_xls_upload']){ ?>
                                <option value="dsv" style="font-weight: bold" >CSV, DSV формат</option>
                                <option value="xls" style="color: forestgreen; font-weight: bold" selected="" >Формат EXCEL</option>
                                
                                <option value="xml" style="color: crimson; font-weight: bold; <?php echo $import_css2; ?>" >XML (в т.ч. YML)</option>
                                <?php }elseif(isset($tamplate_data_selected['anyyml_yml_upload']) && $tamplate_data_selected['anyyml_yml_upload']){ ?>
                                <option value="dsv"  style="font-weight: bold">CSV, DSV формат</option>    
                                <option value="xls" style="color: darkcyan; font-weight: bold">Формат EXCEL</option>
                                    
                                    <option value="xml" selected=""   style="color: crimson; font-weight: bold; <?php echo $import_css2; ?>">XML (в т.ч. YML)</option>
                                <?php }else{ ?>
                                    <option value="dsv" style="font-weight: bold" selected=""  >CSV, DSV формат</option>
                                    <option value="xls" style="color: forestgreen; font-weight: bold">Формат EXCEL</option>
                                    
                                    <option value="xml" style="color: crimson; font-weight: bold; <?php echo $import_css2; ?>">XML (в т.ч. YML)</option>
                                <?php } ?>
                        </select>
                        
                        
                        
                        
                        </div>
                    </td>
                </tr>
                
                <tr class="dsv_upload_set_import dsv_upload_set_export">
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_delimiter ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <input name="odmpro_tamplate_data[csv_delimiter]" value="<?php echo $tamplate_data_selected['csv_delimiter']; ?>"  class="form-control" />
                        </div>
                    </td>
                </tr>
                <tr class="dsv_upload_set_import dsv_upload_set_export">
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_enclosure ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <input name="odmpro_tamplate_data[csv_enclosure]" value='<?php if(isset($tamplate_data_selected['csv_enclosure'])){ echo $tamplate_data_selected['csv_enclosure']; }else{ echo '"'; } ?>'  class="form-control" />
                        </div>
                    </td>
                </tr>
                <tr class="dsv_upload_set_import dsv_upload_set_export">
                    <td class="text-left">
                        <?php echo $entry_odmpro_csv_escape ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                            <input name="odmpro_tamplate_data[csv_escape]" value='<?php if(isset($tamplate_data_selected['csv_escape'])){ echo $tamplate_data_selected['csv_escape']; }else{ echo '\\'; } ?>'  class="form-control" />
                        </div>
                    </td>
                </tr>
                <tr class="dsv_upload_set_import dsv_upload_set_export">
                    <td class="text-left">
                        <?php echo $entry_odmpro_encoding ?>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                        <select name="odmpro_tamplate_data[encoding]" class="form-control">
                                <option value="0" ><?php echo $entry_select; ?></option>
                                    <?php foreach($encodings as $encoding){ ?>
                                        <?php if($tamplate_data_selected['encoding']== $encoding){ ?>
                                <option value="<?php echo $encoding ?>" selected="" ><?php echo $encoding; ?></option>
                                        <?php }else{ ?>
                                <option value="<?php echo $encoding ?>" ><?php echo $encoding; ?></option> 
                                        <?php } ?>
                                    <?php } ?>
                        </select>
                        </div>
                    </td>
                </tr>
                
                
                
                <tr  <?php echo $import_css ?> class="dsv_upload_set_import dsv_upload_set_export">
                    <td class="text-left">
                        Добавить первую строку к файлу<span data-toggle="tooltip" title="" data-original-title="Если в файле отсутствуют названия колонок и данные начинаются с первой строки файла, то для настроек будет создана техническая строка с номерами колонок"></span>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                        <select name="odmpro_tamplate_data[add_first_row]" class="form-control">
                                    <?php if(isset($tamplate_data_selected['add_first_row']) && $tamplate_data_selected['add_first_row']){ ?>
                                        <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                        <option value="0" ><?php echo $entry_disable; ?></option>
                                    <?php }else{ ?>
                                        <option value="1"  ><?php echo $entry_enable; ?></option>
                                        <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                    <?php } ?>
                        </select>
                        </div>
                    </td>
                </tr>
                <!--
                <tr  <?php echo $import_css ?>>
                    <td class="text-left">
                        Вырезать колонки во входящем файле (если несколько, укажите через разделитель вертикальная черта: |)
                    </td>
                    <td class="text-left">
                         <div class="input-group">
                             <textarea name="odmpro_tamplate_data[cut_columns]" class="form-control" ><?php if(isset($tamplate_data_selected['cut_columns'])){ echo $tamplate_data_selected['cut_columns']; }else{ echo ''; } ?></textarea>
                        </div>
                    </td>
                </tr>
                -->
                <?php 
                
                $h_or_s = "display: none";
                
                if(isset($setting_version_functional['log'])){
                
                    $h_or_s = "";
                
                }
                
                ?>
                
                <tr style="<?php echo $h_or_s; ?>">
                    <td class="text-left">
                        <?php echo $text_log_title ?>
                    </td>
                    <td class="text-left">
                        <a class="btn btn-primary" onclick="showHide('#log_setting_box'); $(this).remove()" >Показать настройки</a>
                        <div id="log_setting_box" style="display: none">
                        <div class="input-group">
                            <select name="odmpro_tamplate_data[log_status]" class="form-control">
                                    <?php if($tamplate_data_selected['log_status']){ ?>
                                        <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                        <option value="0" ><?php echo $entry_disable; ?></option>
                                    <?php }else{ ?>
                                        <option value="1"  ><?php echo $entry_enable; ?></option>
                                        <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                    <?php } ?>
                            </select>
                        </div>
                        <br>
                        <div class="input-group">
                            <label><?php echo $text_log_details; ?></label><br>
                            <select name="odmpro_tamplate_data[log_details]" class="form-control">
                                    <?php if($tamplate_data_selected['log_details']){ ?>
                                        <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                        <option value="0" ><?php echo $entry_disable; ?></option>
                                    <?php }else{ ?>
                                        <option value="1"  ><?php echo $entry_enable; ?></option>
                                        <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                    <?php } ?>
                            </select>
                        </div><br>
                        <div class="input-group">
                            <label><?php echo $text_log_update; ?></label><br>
                            <select name="odmpro_tamplate_data[log_update]" class="form-control">
                                    <?php if($tamplate_data_selected['log_update']){ ?>
                                        <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                        <option value="0" ><?php echo $entry_disable; ?></option>
                                    <?php }else{ ?>
                                        <option value="1"  ><?php echo $entry_enable; ?></option>
                                        <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                    <?php } ?>
                            </select>
                        </div><br>
                        <div class="input-group">
                            <label><?php echo $text_log_html; ?></label><br>
                            <select onchange="if(this.value=='1'){ $('#log_file_name_link_type').text('.htm'); }else{ $('#log_file_name_link_type').text('.txt'); } $('input[name=\'odmpro_tamplate_data[log_file_name]\']').change(); " name="odmpro_tamplate_data[log_html]" class="form-control">
                                    <?php if($tamplate_data_selected['log_update']){ ?>
                                        <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                        <option value="0" ><?php echo $entry_disable; ?></option>
                                    <?php }else{ ?>
                                        <option value="1"  ><?php echo $entry_enable; ?></option>
                                        <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                    <?php } ?>
                            </select>
                        </div><br>
                        <div class="input-group" style="width: 100%">
                            <label><?php echo $text_log_file_name; ?></label><br>
                            <table >
                                <tr>
                                    <td><?php echo HTTPS_CATALOG.'image/' ?></td>
                                    <td><input style="display: inline-block; margin-bottom: 5px;" onchange="update_link('#log_file_name_link','<?php echo HTTPS_CATALOG.'image/' ?>',this.value,$('#log_file_name_link_type').text())" name="odmpro_tamplate_data[log_file_name]" value='<?php if(isset($tamplate_data_selected["log_file_name"])){ echo $tamplate_data_selected["log_file_name"]; }else{ echo ""; } ?>'  class="form-control" /></td>
                                    <td id="log_file_name_link_type">.htm</td>
                                </tr>
                            </table>
                            
                            <table class="table table-bordered table-hover">
                                    <tr>  
                                        <td >
                                            <input   class="form-control" id="log_file_name_link"  readonly="" onclick="$(this).select()" value="<?php echo HTTPS_CATALOG.'image/'.$tamplate_data_selected["log_file_name"]; ?>"/>
                                        </td>
                                    </tr>
                                    
                            </table>
                            
                        </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_file ?>
                    </td>
                    <td class="text-left">
                        
                        
                        
                        
                        <div  <?php echo $import_css; ?> >
                            
                            <?php if(isset($anycsv_sinch_supplier_setting_id) && $anycsv_sinch_supplier_setting_id){ ?>
                            
                            <div style="display: none">
                            
                            <?php } ?>
                            
                            <?php if($entry_odmpro_file_upload_error_type){ ?>
                                <div class="alert alert-danger"><?php echo $entry_odmpro_file_upload_error_type ?></div>
                            <?php } ?>
                            <div class="input-group">
                                <div style="width: 100%"><b>Загрузить файл с данными</b><span data-toggle="tooltip" title="" data-original-title="Если данные будут передаваться по файлу, загрузите этот файл здесь"></span></div>
                                <input type="text" name="odmpro_tamplate_data[file_upload]" value="<?php echo $tamplate_data_selected['file_upload'] ?>" placeholder="<?php echo $entry_odmpro_file_upload ?>" id="input-filename" class="form-control" />
                                <span class="input-group-btn" style="padding-top: 18px;">
                                    <button type="button" id="button-upload" data-loading-text="<?php echo $text_wite; ?>" class="btn btn-primary"><i class="fa fa-upload"></i> <?php echo $entry_odmpro_file_upload ?></button>
                                </span>
                            </div>
                            <div class="input-group" style="margin-top: 10px; margin-bottom: 5px;">
                                <b>Ссылка на данные (http(s) или ftp)</b><span data-toggle="tooltip" title="" data-original-title="Если данные будут передаваться по ссылке, укажите полную ссылку, в т.ч. протокол: http://, https:// или ftp://"></span>
                                <input type="text" name="odmpro_tamplate_data[file_url]" value="<?php echo $tamplate_data_selected['file_url'] ?>" placeholder="<?php echo $entry_odmpro_file_url ?>" id="input-filename" class="form-control" style="margin-top: 5px;" />
                            </div>
                            
                            <div class="input-group" style="margin-top: 7px;">
                                    Базовая аутентификация HTTP<span data-toggle="tooltip" title="" data-original-title="Если ссылка требует базовой аунтификации, у Вас есть логин и пароль, введите их здесь"></span>
                                    <input type="text" name="odmpro_tamplate_data[ba_login]" value="<?php echo $tamplate_data_selected['ba_login'] ?>" placeholder="Login" id="input-" class="form-control col-sm-3" />
                                    <input type="text" name="odmpro_tamplate_data[ba_password]" value="<?php echo $tamplate_data_selected['ba_password'] ?>" placeholder="Password" id="input-" class="form-control col-sm-3" />
                            </div>
                            
                            <div class="input-group" style="margin-top: 7px;">
                                    Данные для авторизации по FTP<span data-toggle="tooltip" title="" data-original-title="Если данные передаются по FTP, укажите дополнительные данные для авторизации"></span>
                                    <input type="text" name="odmpro_tamplate_data[ftp_login]" value="<?php echo $tamplate_data_selected['ftp_login'] ?>" placeholder="Ftp Login" id="input-" class="form-control col-sm-3" />
                                    <input type="text" name="odmpro_tamplate_data[ftp_password]" value="<?php echo $tamplate_data_selected['ftp_password'] ?>" placeholder="Ftp Password" id="input-" class="form-control col-sm-3" />
                                    <input type="text" name="odmpro_tamplate_data[ftp_dir]" value="<?php echo $tamplate_data_selected['ftp_dir'] ?>" placeholder="файл (вместе с папкой, если есть)" id="input-" class="form-control col-sm-6" />
                            </div>
                        
                            <?php if(isset($anycsv_sinch_supplier_setting_id) && $anycsv_sinch_supplier_setting_id){ ?>
                            
                            </div>
                            
                            <?php } ?>
                            
                            <?php if($type_process=='import'){ ?>
                            
                                <?php if(!isset($anycsv_sinch_supplier_setting_id) || !$anycsv_sinch_supplier_setting_id){ ?>

                                                                                                                                                                                                                        <?php if(FALSE){ ?>

                                                                                                                                                                                                                        <h3 style="margin-bottom: 5px; margin-top: 15px; ">Доп. настройки для: XLS, XML, YML файлов/ссылок или адаптации (через adaptor.e-ditributer.com)</h3>

                                                                                                                                                                                                                        <div class="well" style="background: lightcyan; margin-bottom: 0px; padding: 7px;">
                                                                                                                                                                                                                        <ul  class="nav nav-tabs" >
                                                                                                                                                                                                                            <li><a data-toggle="tab"  href="#tab_anyxml_import" style="font-size: 11px;" ><?php echo $text_anyxml_xml_upload; ?></a></li>
                                                                                                                                                                                                                            <li><a data-toggle="tab"  href="#tab_anyxls_import" style="font-size: 11px;" ><?php echo $text_anyxls_xls_upload; ?></a></li>

                                                                                                                                                                                                                            <?php if(isset($yml_setting)){ ?>

                                                                                                                                                                                                                            <li><a data-toggle="tab"  href="#tab_anyyml_import" style="font-size: 11px;" >Импорт YML/XML</a></li>

                                                                                                                                                                                                                            <?php } ?>

                                                                                                                                                                                                                            <?php if(isset($edistributier_adaptor)){ ?>

                                                                                                                                                                                                                            <li><a data-toggle="tab"  href="#tab_edistributier_adaptor" style="font-size: 11px;" >Адаптация</a></li>

                                                                                                                                                                                                                            <?php } ?>

                                                                                                                                                                                                                        </ul>

                                                                                                                                                                                                                        <?php 

                                                                                                                                                                                                                        if((isset($tamplate_data_selected['anyxml_xml_upload']) && $tamplate_data_selected['anyxml_xml_upload']) || (isset($tamplate_data_selected['anyyml_yml_upload']) && $tamplate_data_selected['anyyml_yml_upload']) || (isset($tamplate_data_selected['edistributier_adaptor']['status']) && $tamplate_data_selected['edistributier_adaptor']['status']) || (isset($tamplate_data_selected['anyxls_xls_upload']) && $tamplate_data_selected['anyxls_xls_upload'])){ ?>
                                                                                                                                                                                                                        <script type="text/javascript">
                                                                                                                                                                                                                                $(document).ready(function() {

                                                                                                                                                                                                                            <?php if(isset($tamplate_data_selected['anyxls_xls_upload']) && $tamplate_data_selected['anyxls_xls_upload']){ ?>

                                                                                                                                                                                                                                $("a[href='#tab_anyxls_import']").click();

                                                                                                                                                                                                                            <?php }elseif(isset($tamplate_data_selected['anyxml_xml_upload']) && $tamplate_data_selected['anyxml_xml_upload']){ ?>

                                                                                                                                                                                                                                $("a[href='#tab_anyxml_import']").click();

                                                                                                                                                                                                                            <?php }elseif(isset($tamplate_data_selected['edistributier_adaptor']['status']) && $tamplate_data_selected['edistributier_adaptor']['status']){ ?>

                                                                                                                                                                                                                                $("a[href='#tab_edistributier_adaptor']").click();

                                                                                                                                                                                                                            <?php }else{ ?>

                                                                                                                                                                                                                                $("a[href='#tab_anyyml_import']").click();

                                                                                                                                                                                                                            <?php } ?>

                                                                                                                                                                                                                            });
                                                                                                                                                                                                                        </script>
                                                                                                                                                                                                                        <?php } ?>

                                                                                                                                                                                                                        <div class="tab-content">

                                                                                                                                                                                                                            <div id="tab_anyxml_import" class="tab-pane" style="display: none" >
                                                                                                                                                                                                                                        <?php if($text_anyxml_status_false){ ?>

                                                                                                                                                                                                                                        <div class="alert alert-info" style="margin-top: 5px;"><?php echo $text_anyxml_status_false; ?></div>

                                                                                                                                                                                                                                        <?php }else{ ?>

                                                                                                                                                                                                                                        <select  onchange="$('#tab_anyxls_import select').val(0)" name="odmpro_tamplate_data[anyxml_xml_upload]" class="form-control">
                                                                                                                                                                                                                                                <?php if($tamplate_data_selected['anyxml_xml_upload']){ ?>
                                                                                                                                                                                                                                                    <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                                                                                                                                                                                                                                    <option value="0" ><?php echo $entry_disable; ?></option>
                                                                                                                                                                                                                                                <?php }else{ ?>
                                                                                                                                                                                                                                                    <option value="1"  ><?php echo $entry_enable; ?></option>
                                                                                                                                                                                                                                                    <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                                                                                                                                                                                                                                <?php } ?>
                                                                                                                                                                                                                                        </select>
                                                                                                                                                                                                                                        <label style="margin-top: 10px;">
                                                                                                                                                                                                                                        <?php echo $text_xml_specification ?>
                                                                                                                                                                                                                                        </label>
                                                                                                                                                                                                                                        <select  onchange="$('#tab_anyxls_import select').val(0)" name="odmpro_tamplate_data[xml_specification]" class="form-control">
                                                                                                                                                                                                                                                <option value="0" ><?php echo $text_xml_specification_select; ?></option>
                                                                                                                                                                                                                                                <?php foreach($xml_specifications as $xml_specification => $xml_specification_text){ ?>
                                                                                                                                                                                                                                                        <?php if(isset($tamplate_data_selected['xml_specification']) && $tamplate_data_selected['xml_specification'] == $xml_specification){ ?>
                                                                                                                                                                                                                                                <option value="<?php echo $xml_specification ?>" selected="" ><?php echo $xml_specification_text; ?></option>
                                                                                                                                                                                                                                                        <?php }else{ ?>
                                                                                                                                                                                                                                                <option value="<?php echo $xml_specification ?>" ><?php echo $xml_specification_text; ?></option> 
                                                                                                                                                                                                                                                        <?php } ?>
                                                                                                                                                                                                                                                <?php } ?>
                                                                                                                                                                                                                                        </select>

                                                                                                                                                                                                                                        <?php } ?>
                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                    <div id="tab_anyxls_import" class="tab-pane" >

                                                                                                                                                                                                                                        <?php if($text_anyxls_status_false){ ?>

                                                                                                                                                                                                                                            <div class="alert alert-info" style="margin-top: 5px;"><?php echo $text_anyxls_status_false; ?></div>

                                                                                                                                                                                                                                        <?php }else{ ?>

                                                                                                                                                                                                                                            <select onchange="$('#tab_anyxml_import select').val(0)"  name="odmpro_tamplate_data[anyxls_xls_upload]" class="form-control">
                                                                                                                                                                                                                                                    <?php if($tamplate_data_selected['anyxls_xls_upload']){ ?>
                                                                                                                                                                                                                                                        <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                                                                                                                                                                                                                                        <option value="0" ><?php echo $entry_disable; ?></option>
                                                                                                                                                                                                                                                    <?php }else{ ?>
                                                                                                                                                                                                                                                        <option value="1"  ><?php echo $entry_enable; ?></option>
                                                                                                                                                                                                                                                        <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                                                                                                                                                                                                                                    <?php } ?>
                                                                                                                                                                                                                                            </select>

                                                                                                                                                                                                                                            <?php if($xls_specifications){ ?>

                                                                                                                                                                                                                                                <label style="margin-top: 10px;">
                                                                                                                                                                                                                                                <?php echo $text_xls_specification ?>
                                                                                                                                                                                                                                                </label>
                                                                                                                                                                                                                                                <select  onchange="$('#tab_anyxml_import select').val(0)" name="odmpro_tamplate_data[xls_specification]" class="form-control">
                                                                                                                                                                                                                                                        <option value="0" ><?php echo $text_xls_specification_select; ?></option>
                                                                                                                                                                                                                                                        <?php foreach($xls_specifications as $xls_specification => $xls_specification_text){ ?>
                                                                                                                                                                                                                                                                <?php if(isset($tamplate_data_selected['xls_specification']) && $tamplate_data_selected['xls_specification'] == $xls_specification){ ?>
                                                                                                                                                                                                                                                        <option value="<?php echo $xls_specification ?>" selected="" ><?php echo $xls_specification_text; ?></option>
                                                                                                                                                                                                                                                                <?php }else{ ?>
                                                                                                                                                                                                                                                        <option value="<?php echo $xls_specification ?>" ><?php echo $xls_specification_text; ?></option> 
                                                                                                                                                                                                                                                                <?php } ?>
                                                                                                                                                                                                                                                        <?php } ?>
                                                                                                                                                                                                                                                </select>

                                                                                                                                                                                                                                            <?php } ?>



                                                                                                                                                                                                                                            <table class="table table-bordered table-hover" style="margin-top: 5px; margin-bottom: 0px;">
                                                                                                                                                                                                                                            <tr>  
                                                                                                                                                                                                                                                <td>
                                                                                                                                                                                                                                                    Укажите с какой строки файла начинать обработку (первая строка - 1)
                                                                                                                                                                                                                                                </td>
                                                                                                                                                                                                                                                <td >
                                                                                                                                                                                                                                                    <div class="input-group" >

                                                                                                                                                                                                                                                            <?php if(isset($tamplate_data_selected['anyxls_first_row']) && $tamplate_data_selected['anyxls_first_row']!=='' ){ ?>
                                                                                                                                                                                                                                                                <input name="odmpro_tamplate_data[anyxls_first_row]"  value="<?php echo $tamplate_data_selected['anyxls_first_row'] ?>" class="form-control " type="text" />
                                                                                                                                                                                                                                                            <?php }else{ ?>
                                                                                                                                                                                                                                                                <input name="odmpro_tamplate_data[anyxls_first_row]"  value="1" class="form-control " type="text" />
                                                                                                                                                                                                                                                            <?php } ?>

                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                </td>
                                                                                                                                                                                                                                            </tr>
                                                                                                                                                                                                                                            <tr>  
                                                                                                                                                                                                                                                <td >
                                                                                                                                                                                                                                                    <?php echo $text_anyxls_count_column ?>
                                                                                                                                                                                                                                                </td>
                                                                                                                                                                                                                                                <td >
                                                                                                                                                                                                                                                    <div class="input-group" >

                                                                                                                                                                                                                                                            <?php if(isset($tamplate_data_selected['anyxls_count_column']) && $tamplate_data_selected['anyxls_count_column']!=='' ){ ?>
                                                                                                                                                                                                                                                                <input name="odmpro_tamplate_data[anyxls_count_column]"  value="<?php echo $tamplate_data_selected['anyxls_count_column'] ?>" class="form-control " type="text" />
                                                                                                                                                                                                                                                            <?php }else{ ?>
                                                                                                                                                                                                                                                                <input name="odmpro_tamplate_data[anyxls_count_column]"  value="" class="form-control " type="text" />
                                                                                                                                                                                                                                                            <?php } ?>

                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                </td>
                                                                                                                                                                                                                                            </tr>
                                                                                                                                                                                                                                            <tr>  
                                                                                                                                                                                                                                                <td >
                                                                                                                                                                                                                                                    <?php echo $text_anyxls_count_rows ?>
                                                                                                                                                                                                                                                </td>
                                                                                                                                                                                                                                                <td >
                                                                                                                                                                                                                                                    <div class="input-group" >

                                                                                                                                                                                                                                                            <?php if(isset($tamplate_data_selected['anyxls_count_rows']) && $tamplate_data_selected['anyxls_count_rows']!=='' ){ ?>
                                                                                                                                                                                                                                                                <input name="odmpro_tamplate_data[anyxls_count_rows]"  value="<?php echo $tamplate_data_selected['anyxls_count_rows'] ?>" class="form-control " type="text" />
                                                                                                                                                                                                                                                            <?php }else{ ?>
                                                                                                                                                                                                                                                                <input name="odmpro_tamplate_data[anyxls_count_rows]"  value="" class="form-control " type="text" />
                                                                                                                                                                                                                                                            <?php } ?>

                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                </td>
                                                                                                                                                                                                                                            </tr>
                                                                                                                                                                                                                                            </table>






                                                                                                                                                                                                                                    </div>







                                                                                                                                                                                                                            <?php } ?>

                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                        </div>  


                                                                                                                                                                                                                        <?php } ?>
                            
                                <?php } ?>
                                
                                
                                
                                
                                
                                
                                <?php if(isset($anycsv_sinch_supplier_setting_id) && $anycsv_sinch_supplier_setting_id){ ?>
                                
                                        <h2  style="margin-top: 10px;; color: darkblue"><b><?php echo $anycsv_sinch_supplier_title; ?></b> - общие настройки</h2>
                                        <label style="margin-top: 10px;; color: darkblue">
                                            Вырезать колонки (перечислить названия через вертикальную черту: |)
                                        </label>

                                        <?php if(isset($tamplate_data_selected['anycsv_sinch_supplier_cut_columns']) && $tamplate_data_selected['anycsv_sinch_supplier_cut_columns']){ ?>
                                                    <input name="odmpro_tamplate_data[anycsv_sinch_supplier_cut_columns]" value="<?php echo $tamplate_data_selected['anycsv_sinch_supplier_cut_columns'] ?>"  class="form-control" />
                                                <?php }else{ ?>
                                                    <input name="odmpro_tamplate_data[anycsv_sinch_supplier_cut_columns]" value=''  class="form-control" />
                                        <?php } ?>



                                        <label style="margin-top: 10px;; color: darkblue">
                                            Обновление файла при вызове этого профиля или при нажатии кнопки "Проверить файл и загрузить данные для сопоставления"
                                        </label>
                                        
                                        <div class="input-group" >
                                            <select name="odmpro_tamplate_data[anycsv_sinch_supplier_update_file]"  class="form-control ">
                                                <option value="0" >Не обновлять файл всякий раз при загрузке (использовать ранее созданный)</option>
                                                <?php if(isset($tamplate_data_selected['anycsv_sinch_supplier_update_file']) && $tamplate_data_selected['anycsv_sinch_supplier_update_file']){ ?>
                                                <option value="1" selected="" >Обновлять файл всякий раз при загрузке</option>
                                                <?php }else{ ?>
                                                <option value="1" >Обновлять файл всякий раз при загрузке</option> 
                                                <?php } ?>
                                            </select>
                                        </div>
                                        
                                        <label style="margin-top: 10px;; color: darkblue">
                                            Оставить строки, если условия ниже верны
                                        </label>

                                        <table class="table table-bordered table-hover">
                                            <thead>

                                                <tr>

                                                    <td>Название колонки (точное совпадение)</td>
                                                    <td>Оператор</td>
                                                    <td>Значение</td>

                                                </tr>

                                            </thead>
                                            <?php for($i=0;$i<5;$i++){ ?>


                                                        <tr>

                                                            <td>

                                                                <div class="input-group" >

                                                                    <?php if(isset($tamplate_data_selected['anycsv_sinch_supplier_add_logic'][$i]['column']) && $tamplate_data_selected['anycsv_sinch_supplier_add_logic'][$i]['column']!=='' ){ ?>
                                                                        <input name="odmpro_tamplate_data[anycsv_sinch_supplier_add_logic][<?php echo $i ?>][column]"  value="<?php echo $tamplate_data_selected['anycsv_sinch_supplier_add_logic'][$i]['column'] ?>" class="form-control " type="text" />
                                                                    <?php }else{ ?>
                                                                        <input name="odmpro_tamplate_data[anycsv_sinch_supplier_add_logic][<?php echo $i ?>][column]" value=""  class="form-control " type="text" />
                                                                    <?php } ?>

                                                                </div>

                                                            </td>

                                                            <td>

                                                                <div class="input-group" >
                                                                    <select name="odmpro_tamplate_data[anycsv_sinch_supplier_add_logic][<?php echo $i ?>][operator]"  class="form-control ">
                                                                        <option value="0" ><?php echo $text_type_data_ignor; ?></option>
                                                                            <?php foreach($operators_anysinch as $product_field => $product_value){ ?>
                                                                                <?php if(isset($tamplate_data_selected['anycsv_sinch_supplier_add_logic'][$i]['operator']) && $tamplate_data_selected['anycsv_sinch_supplier_add_logic'][$i]['operator']==$product_field ){ ?>
                                                                        <option value="<?php echo $product_field ?>" selected="" ><?php echo $product_value; ?></option>
                                                                                <?php }else{ ?>
                                                                        <option value="<?php echo $product_field ?>" ><?php echo $product_value; ?></option> 
                                                                                <?php } ?>
                                                                            <?php } ?>
                                                                    </select>
                                                                </div>

                                                            </td>

                                                            <td>

                                                                <div class="input-group" >

                                                                    <?php if(isset($tamplate_data_selected['anycsv_sinch_supplier_add_logic'][$i]['value']) && $tamplate_data_selected['anycsv_sinch_supplier_add_logic'][$i]['value']!=='' ){ ?>
                                                                        <input name="odmpro_tamplate_data[anycsv_sinch_supplier_add_logic][<?php echo $i ?>][value]"  value="<?php echo $tamplate_data_selected['anycsv_sinch_supplier_add_logic'][$i]['value'] ?>" class="form-control " type="text" />
                                                                    <?php }else{ ?>
                                                                        <input name="odmpro_tamplate_data[anycsv_sinch_supplier_add_logic][<?php echo $i ?>][value]" value=""  class="form-control " type="text" />
                                                                    <?php } ?>

                                                                </div>

                                                            </td>

                                                        </tr>


                                            <?php } ?>
                                        </table>
                                        
                                        <?php echo $ocext_dmpro_step_one_settings_sinch_supplier;  ?>
                                        
                                
                                
                                <?php } ?>
                            
                            <?php } ?>
                            
                        
                       </div> 
                        
                        <div <?php echo $export_css; ?> >
                            
                            <?php if(isset($export_to_xls_one_set)){ ?>
                            
                                <?php echo $export_to_xls_one_set; ?>
                            
                            <?php } ?>
                        
                            <div class="input-group  export_boxes"  style="width: 100%" id="export_box_csv">
                                <label><?php echo $entry_export_file_name ?></label>
                                
                                <table>
                                    <tr>
                                        <td><?php echo HTTPS_CATALOG.'image/' ?></td>
                                        <td><input style="margin-bottom: 5px;" type="text" onchange="update_link('#export_file_name_link','<?php echo HTTPS_CATALOG.'image/' ?>',this.value,'.csv')" name="odmpro_tamplate_data[export_file_name]" value="<?php echo $tamplate_data_selected['export_file_name'] ?>" placeholder="<?php echo $entry_export_file_name ?>" class="form-control" /></td>
                                        <td>.csv</td>
                                    </tr>
                                </table>
                                <table class="table table-bordered table-hover">
                                    <tr>  
                                        <td colspan="3">
                                        <input   class="form-control" id="export_file_name_link"  readonly="" onclick="$(this).select()" value="<?php echo HTTPS_CATALOG.'image/'.$tamplate_data_selected['export_file_name'].'.csv' ?>"/>
                                        </td>
                                    </tr>
                                    
                                </table>
                                
                            </div>

                            <label style="margin-top: 10px;">Отправить файл вложением на email (если несколько через запятую)</label>
                                <?php if(isset($tamplate_data_selected['send_on_email']) && $tamplate_data_selected['send_on_email']!=='' ){ ?>
                                    <input name="odmpro_tamplate_data[send_on_email]"  value="<?php echo $tamplate_data_selected['send_on_email'] ?>" class="form-control " type="text" />
                                <?php }else{ ?>
                                    <input name="odmpro_tamplate_data[send_on_email]"  value="" class="form-control " type="text" />
                                <?php } ?>
                                <br>
                                <label style="margin-top: 5px;">Отправить файл ссылкой, если его размер больше указанного в Мб<span data-toggle="tooltip" title="" data-original-title="Укажите размер файл в Мб, после которого следует отправлять файл не вложением, а ссылкой на файл. Если необходимо отправлять файл ссылкой всегда, укажите нуль. Если необходимо отправлять файл вложением всегда - оставьте поле пустым"></span></label>
                                <?php if(isset($tamplate_data_selected['send_on_email_as_link']) && $tamplate_data_selected['send_on_email_as_link']!=='' ){ ?>
                                    <input name="odmpro_tamplate_data[send_on_email_as_link]"  value="<?php echo $tamplate_data_selected['send_on_email_as_link'] ?>" class="form-control " type="text" />
                                <?php }else{ ?>
                                    <input name="odmpro_tamplate_data[send_on_email_as_link]"  value="" class="form-control " type="text" />
                                <?php } ?>
                        
                        </div> 
                        
                    </td>
                </tr>
                
                
                
                <?php if(isset($yml_setting)){ ?>
                <tr class="xml_upload_set_import" style="border: 3px solid crimson;   <?php echo $import_css2 ?>">
                            <td class="text-left">Настройка XML, YML</td>      
                             <td class="text-left"><?php echo $yml_setting; ?></td>
                        </tr>          
                <?php } ?>
                
                <tr class="xls_upload_set_import" style="border: 3px solid forestgreen;   <?php echo $import_css2 ?>">
                        <td class="text-left">Настройка EXCEL</td>      
                        <td class="text-left">
                            
                            <div id="tab_anyxls_import" class="tab-pane" >

                            <?php if($text_anyxls_status_false){ ?>

                                <div class="alert alert-info" style="margin-top: 5px;"><?php echo $text_anyxls_status_false; ?></div>

                            <?php }else{ ?>

                            <input value="<?php echo $tamplate_data_selected['anyxls_xls_upload']; ?>" name="odmpro_tamplate_data[anyxls_xls_upload]" type="hidden" />
                            

                                <?php if($xls_specifications){ ?>

                                    <label style="margin-top: 10px;">
                                    <?php echo $text_xls_specification ?>
                                    </label>
                                    <select  onchange="$('#tab_anyxml_import select').val(0)" name="odmpro_tamplate_data[xls_specification]" class="form-control">
                                            <option value="0" ><?php echo $text_xls_specification_select; ?></option>
                                            <?php foreach($xls_specifications as $xls_specification => $xls_specification_text){ ?>
                                                    <?php if(isset($tamplate_data_selected['xls_specification']) && $tamplate_data_selected['xls_specification'] == $xls_specification){ ?>
                                            <option value="<?php echo $xls_specification ?>" selected="" ><?php echo $xls_specification_text; ?></option>
                                                    <?php }else{ ?>
                                            <option value="<?php echo $xls_specification ?>" ><?php echo $xls_specification_text; ?></option> 
                                                    <?php } ?>
                                            <?php } ?>
                                    </select>

                                <?php } ?>



                                <table class="table table-bordered table-hover" style="margin-top: 5px; margin-bottom: 0px;">
                                <tr>  
                                    <td >
                                        Укажите номер строки, в которой содержатся названия колонок
                                    </td>
                                    <td >
                                        <div class="input-group" >

                                                <?php if(isset($tamplate_data_selected['anyxls_first_row']) && $tamplate_data_selected['anyxls_first_row']!=='' ){ ?>
                                                    <input name="odmpro_tamplate_data[anyxls_first_row]"  value="<?php echo $tamplate_data_selected['anyxls_first_row'] ?>" class="form-control " type="text" />
                                                <?php }else{ ?>
                                                    <input name="odmpro_tamplate_data[anyxls_first_row]"  value="1" class="form-control " type="text" />
                                                <?php } ?>

                                        </div>
                                    </td>
                                </tr>
                                <tr>  
                                    <td >
                                        <?php echo $text_anyxls_count_column ?>
                                    </td>
                                    <td >
                                        <div class="input-group" >

                                                <?php if(isset($tamplate_data_selected['anyxls_count_column']) && $tamplate_data_selected['anyxls_count_column']!=='' ){ ?>
                                                    <input name="odmpro_tamplate_data[anyxls_count_column]"  value="<?php echo $tamplate_data_selected['anyxls_count_column'] ?>" class="form-control " type="text" />
                                                <?php }else{ ?>
                                                    <input name="odmpro_tamplate_data[anyxls_count_column]"  value="" class="form-control " type="text" />
                                                <?php } ?>

                                        </div>
                                    </td>
                                </tr>
                                <tr>  
                                    <td >
                                        <?php echo $text_anyxls_count_rows ?>
                                    </td>
                                    <td >
                                        <div class="input-group" >

                                                <?php if(isset($tamplate_data_selected['anyxls_count_rows']) && $tamplate_data_selected['anyxls_count_rows']!=='' ){ ?>
                                                    <input name="odmpro_tamplate_data[anyxls_count_rows]"  value="<?php echo $tamplate_data_selected['anyxls_count_rows'] ?>" class="form-control " type="text" />
                                                <?php }else{ ?>
                                                    <input name="odmpro_tamplate_data[anyxls_count_rows]"  value="" class="form-control " type="text" />
                                                <?php } ?>

                                        </div>
                                    </td>
                                </tr>
                                <tr>  
                                    <td >
                                        Название листа<span data-toggle="tooltip" title="" data-original-title="Укажите название листа, если нужно импортировать данные не из активного листа. Требуется точное совподение"></span>
                                    </td>
                                    <td >
                                        <div class="input-group" >

                                                <?php if(isset($tamplate_data_selected['anyxls_sheet_name']) && $tamplate_data_selected['anyxls_sheet_name']!=='' ){ ?>
                                                    <input name="odmpro_tamplate_data[anyxls_sheet_name]"  value="<?php echo $tamplate_data_selected['anyxls_sheet_name'] ?>" class="form-control " type="text" />
                                                <?php }else{ ?>
                                                    <input name="odmpro_tamplate_data[anyxls_sheet_name]"  value="" class="form-control " type="text" />
                                                <?php } ?>

                                        </div>
                                    </td>
                                </tr>
                                <tr>  
                                    <td >
                                        Выключить валидацию<span data-toggle="tooltip" title="" data-original-title="Если Вы видите сообщение об ошибке формата, выключите валидацию, чтобы попробовать загрузить файл без проверки"></span>
                                    </td>
                                    <td >
                                        <div class="input-group" >

                                                <select  name="odmpro_tamplate_data[anyxls_disable_validate]" class="form-control">
                                                        <?php if(isset($tamplate_data_selected['anyxls_disable_validate']) && $tamplate_data_selected['anyxls_disable_validate']){ ?>
                                                            <option value="1" selected="" >Включено</option>
                                                            <option value="0" >Выключено</option>
                                                        <?php }else{ ?>
                                                            <option value="1"  >Включено</option>
                                                            <option value="0" selected="">Выключено</option> 
                                                        <?php } ?>
                                                </select>

                                        </div>
                                    </td>
                                </tr>



                                </table>


                                <?php } ?>



                        </div>
                            
                            
                        </td>
                </tr>   
                
                <tr>
                    <td class="text-left">
                        Поля базы данных, доступные для настроек<span data-toggle="tooltip" title="" data-original-title="Если необходимо настраивать импорт в колонки базы данных, у которых нет вывода в админ.панели (например: product_id, date и т.п.), то должно быть выбрано 'Все поля базы данных'. В этом случае для настроек будут доступны все колонки базы данных необходимой таблицы"></span>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                        <select name="odmpro_tamplate_data[level]" class="form-control">
                                        <?php if($tamplate_data_selected['level']){ ?>
                                <option value="1" selected="" ><?php echo $entry_odmpro_tamplate_data_level_1; ?></option>
                                <option value="0" ><?php echo $entry_odmpro_tamplate_data_level_0; ?></option>
                                        <?php }else{ ?>
                                <option value="1"  ><?php echo $entry_odmpro_tamplate_data_level_1; ?></option>
                                <option value="0" selected=""><?php echo $entry_odmpro_tamplate_data_level_0; ?></option> 
                                        <?php } ?>
                        </select>
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_language ?><span data-toggle="tooltip" title="" data-original-title="Если будут использоваться текстовые данные, то они будут относиться к выбранному тут языку. При импорте - импортировать в указанный язык. При экспорте - выводиться данные, указанного языка"></span>
                    </td>
                    <td class="text-left">
                        <div class="input-group">
                        <select name="odmpro_tamplate_data[language_id]" class="form-control">
                                <option value="0" ><?php echo $entry_select; ?></option>
                                <?php foreach($languages as $language_id => $language){ ?>
                                        <?php if($tamplate_data_selected['language_id'] == $language_id){ ?>
                                <option value="<?php echo $language_id ?>" selected="" ><?php echo $language['name']; ?></option>
                                        <?php }else{ ?>
                                <option value="<?php echo $language_id ?>" ><?php echo $language['name']; ?></option> 
                                        <?php } ?>
                                <?php } ?>
                        </select>
                        </div>
                    </td>
                </tr>
                
                <?php
                
                $h_or_s = "display: none";
                
                if(isset($setting_version_functional['currency_convert'])){
                
                    $h_or_s = "";
                
                }
                
                ?>
                
                <tr style="<?php echo $h_or_s; ?>">
                    <td class="text-left">
                        <?php echo $entry_odmpro_currency ?>
                    </td>
                    <td class="text-left">
                        
                        <div class="input-group">Из валюты 
                        <select name="odmpro_tamplate_data[currency_code]" class="form-control">
                                <option value="0" ><?php echo $entry_select; ?></option>
                                <?php foreach($currencies as $currency_code => $currency){ ?>
                                        <?php if($tamplate_data_selected['currency_code'] == $currency_code){ ?>
                                <option value="<?php echo $currency_code ?>" selected="" ><?php echo $currency['name']; ?></option>
                                        <?php }else{ ?>
                                <option value="<?php echo $currency_code ?>" ><?php echo $currency['name']; ?></option> 
                                        <?php } ?>
                                <?php } ?>
                        </select>
                        </div>
                        
                        <div class="input-group">В валюту 
                        <select name="odmpro_tamplate_data[currency_code_to]" class="form-control">
                                <option value="0" ><?php echo $entry_select; ?></option>
                                <?php foreach($currencies as $currency_code => $currency){ ?>
                                        <?php if(isset($tamplate_data_selected['currency_code_to']) && $tamplate_data_selected['currency_code_to'] == $currency_code){ ?>
                                <option value="<?php echo $currency_code ?>" selected="" ><?php echo $currency['name']; ?></option>
                                        <?php }else{ ?>
                                <option value="<?php echo $currency_code ?>" ><?php echo $currency['name']; ?></option> 
                                        <?php } ?>
                                <?php } ?>
                        </select>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php echo $entry_odmpro_store ?>
                    </td>
                    <td class="text-left">
                        <div class="well well-sm" style="max-height: 70px; overflow: auto; margin-bottom: 0px;">
                        <?php foreach ($stores as $store) { ?>
                        <div class="checkbox">
                          <label>
                            <?php if ( isset($tamplate_data_selected['store_id'][$store['store_id']]) ) { ?>
                                <input type="checkbox" name="odmpro_tamplate_data[store_id][<?php echo $store['store_id'] ?>]" value="<?php echo $store['store_id'] ?>" checked="checked" />
                            <?php echo $store['name']; ?>
                            <?php } else { ?>
                                <input type="checkbox" name="odmpro_tamplate_data[store_id][<?php echo $store['store_id'] ?>]" value="<?php echo $store['store_id'] ?>" />
                            <?php echo $store['name']; ?>
                            <?php } ?>
                          </label>
                        </div>
                        <?php } ?>
                      </div>
                    </td>
                </tr>
                
                <?php if(isset($php_after_import) && $php_after_import){ ?>
                
                    <tr >

                        <?php echo $php_after_import; ?> 

                    </tr>
            
                <?php } ?>
                
                <?php if($actions_with_data_group){ ?>
                
                <tr <?php echo $import_css ?>>
                    <td><?php echo $title_group_id_box_product_data ?></td>
                    <td>
                        <a class="btn btn-primary" onclick="showHide('#title_group_id_box_disable_type'); $(this).remove()" >Показать настройки</a>
                        <div style="display: none;" id="title_group_id_box_disable_type"> 
                            
                            <h4><b style="color:#1abc9c ">Укажите условия для отбора товаров, с которыми требуется сделать какое-то действие перед импортом</b></h4>
                            
                            <div class="info-box-modal2">Например, если требуется обнулить количество определенным товарам. Например только таким, у которых в "Коде товара" есть левый префикс. Например: префикс <b>suppl5-</b>, то настройка будет <span style="cursor: pointer; border-bottom: 1px dashed;" onclick="setExample('group_id_box')">такой</span></div>
                            
                            <table class="table table-bordered table-hover">
                            <thead>
                                
                                <tr>

                                    <td><?php echo $title_group_id_box_vendor_id ?></td>
                                    <td><?php echo $title_group_id_box_vendor_operator ?></td>
                                    <td><?php echo $title_group_id_box_vendor_value ?></td>

                                </tr>
                                
                            </thead>
                            <?php for($i=0;$i<$count_group_id_box;$i++){ ?>

                            
                                        <tr>
                            
                                            <td>
                                            
                                                <div class="input-group" >
                                                    <select name="odmpro_tamplate_data[group_id_box][product_data][<?php echo $i ?>][product_field]"  class="form-control ">
                                                        <option value="0" ><?php echo $text_type_data_ignor; ?></option>
                                                            <?php foreach($product_fields_group_id_box as $product_field => $product_value){ ?>
                                                                <?php if(isset($tamplate_data_selected['group_id_box']['product_data'][$i]['product_field']) && $tamplate_data_selected['group_id_box']['product_data'][$i]['product_field']==$product_field ){ ?>
                                                        <option value="<?php echo $product_field ?>" selected="" ><?php echo $product_value; ?></option>
                                                                <?php }else{ ?>
                                                        <option value="<?php echo $product_field ?>" ><?php echo $product_value; ?></option> 
                                                                <?php } ?>
                                                            <?php } ?>
                                                    </select>
                                                </div>

                                            </td>
                                            
                                            <td>
                                            
                                                <div class="input-group" >
                                                    <select name="odmpro_tamplate_data[group_id_box][product_data][<?php echo $i ?>][operator]"  class="form-control ">
                                                        <option value="0" ><?php echo $text_type_data_ignor; ?></option>
                                                            <?php foreach($operators_group_id_box as $product_field => $product_value){ ?>
                                                                <?php if(isset($tamplate_data_selected['group_id_box']['product_data'][$i]['operator']) && $tamplate_data_selected['group_id_box']['product_data'][$i]['operator']==$product_field ){ ?>
                                                        <option value="<?php echo $product_field ?>" selected="" ><?php echo $product_value; ?></option>
                                                                <?php }else{ ?>
                                                        <option value="<?php echo $product_field ?>" ><?php echo $product_value; ?></option> 
                                                                <?php } ?>
                                                            <?php } ?>
                                                    </select>
                                                </div>

                                            </td>
                                            
                                            <td>
                                            
                                                <div class="input-group" >
                                                    
                                                    <?php if(isset($tamplate_data_selected['group_id_box']['product_data'][$i]['value']) && $tamplate_data_selected['group_id_box']['product_data'][$i]['value'] ){ ?>
                                                        <input name="odmpro_tamplate_data[group_id_box][product_data][<?php echo $i ?>][value]"  value="<?php echo $tamplate_data_selected['group_id_box']['product_data'][$i]['value'] ?>" class="form-control " type="text" />
                                                    <?php }else{ ?>
                                                        <input name="odmpro_tamplate_data[group_id_box][product_data][<?php echo $i ?>][value]" value=""  class="form-control " type="text" />
                                                    <?php } ?>
                                                                
                                                </div>

                                            </td>
                                            
                                        </tr>


                            <?php } ?>
                        </table>
                            
                            <h4><b style="color:#1abc9c "><?php echo $title_group_id_box_disable_type ?></b></h4>
                            <div class="well well-sm" style="max-height: 400px; overflow: auto; background: white; margin-bottom:">
                                <div class="checkbox">

                                        <?php echo $title_group_id_box_disable_product; ?>:
                                        <select name="odmpro_tamplate_data[group_id_box][disable_product]" class="form-control">

                                            <?php if ( isset($tamplate_data_selected['group_id_box']['disable_product']) && $tamplate_data_selected['group_id_box']['disable_product'] ) { ?>
                                                <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                                <option value="0" ><?php echo $entry_disable; ?></option>
                                            <?php }else{ ?>
                                                <option value="1"  ><?php echo $entry_enable; ?></option>
                                                <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                            <?php } ?>

                                        </select>
                                    <br>

                                        <?php echo $title_group_id_box_skip_by_quantity; ?>:
                                        <select name="odmpro_tamplate_data[group_id_box][skip_by_quantity]" class="form-control">

                                            <?php if ( isset($tamplate_data_selected['group_id_box']['skip_by_quantity']) && $tamplate_data_selected['group_id_box']['skip_by_quantity'] ) { ?>
                                                <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                                <option value="0" ><?php echo $entry_disable; ?></option>
                                            <?php }else{ ?>
                                                <option value="1"  ><?php echo $entry_enable; ?></option>
                                                <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                            <?php } ?>

                                        </select>
                                    <br>

                                        <?php echo $title_group_id_box_skip_by_price; ?>:
                                        <select name="odmpro_tamplate_data[group_id_box][skip_by_price]" class="form-control">

                                            <?php if ( isset($tamplate_data_selected['group_id_box']['skip_by_price']) && $tamplate_data_selected['group_id_box']['skip_by_price'] ) { ?>
                                                <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                                <option value="0" ><?php echo $entry_disable; ?></option>
                                            <?php }else{ ?>
                                                <option value="1"  ><?php echo $entry_enable; ?></option>
                                                <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                            <?php } ?>

                                        </select>
                                    <br>
                                        <?php if ( isset($tamplate_data_selected['group_id_box']['disable_quantity']) ) { ?>
                                        <?php echo $title_group_id_box_disable_quantity; ?>:
                                            <input type="text" name="odmpro_tamplate_data[group_id_box][disable_quantity]" value="<?php echo $tamplate_data_selected['group_id_box']['disable_quantity'] ?>" class="form-control " />

                                        <?php } else { ?>
                                        <?php echo $title_group_id_box_disable_quantity; ?>:
                                            <input type="text" name="odmpro_tamplate_data[group_id_box][disable_quantity]" value="" class="form-control " />

                                        <?php } ?>
                                    <br>
                                        <?php if ( isset($tamplate_data_selected['group_id_box']['disable_price']) ) { ?>
                                        <?php echo $title_group_id_box_disable_price; ?>:
                                            <input type="text" name="odmpro_tamplate_data[group_id_box][disable_price]" value="<?php echo $tamplate_data_selected['group_id_box']['disable_price'] ?>" class="form-control " />

                                        <?php } else { ?>
                                        <?php echo $title_group_id_box_disable_price; ?>:
                                            <input type="text" name="odmpro_tamplate_data[group_id_box][disable_price]" value="" class="form-control " />

                                        <?php } ?>

                                </div>
                            </div>
                            
                        </div>
                    </td>
                </tr>
                <?php if(isset($actions_with_data_group['category_mapping'])){ ?>
                 <tr <?php echo $import_css ?> >
                     <td><?php echo $title_group_id_box_category_matching_title ?></td>
                     <td>
                    <a class="btn btn-primary" onclick="showHide('#title_group_id_box_category_matching_title'); $(this).remove()" >Показать настройки</a>
                        <div class="well well-sm" style="max-height: 300px; overflow: auto; margin-bottom: 0px; display: none;" id="title_group_id_box_category_matching_title">
                            <div class="info-box-modal2">Используйте эту настройку, чтобы привязать товары к уже существующим категориям сайта на основе их привязки к категориям во входных данных. Чтобы сформировать дерево категорий из входных данных, и сделать сопоставления, для начала необходимо, чтобы в файле была колонка, в которой находятся категории. При этом категории должны быть заданы вместе с подкатегориями, чтобы избежать дублирования связей. Например, в файле может быть колонка "<b>Категории</b>", в которой могут находиться такие записи: Каталог<b>/</b>Одежда<b>/</b>Для женщин. В этом случае настройка будет <span style="cursor: pointer; border-bottom: 1px dashed;" onclick="setExample('cat_mapping')">такой</span>. Если настройки сделаны верно, то на следующем шаге появится область сопоставления категорий файла и категорий этого сайта</div>
                            <table class="table table-bordered table-hover" style="margin-bottom: 0px;">
                            <thead>
                                
                                <tr>

                                    <td><?php echo $title_group_id_box_category_matching_csv_column_name ?></td>
                                    <td><?php echo $title_group_id_box_category_matching_csv_delimeter ?></td>

                                </tr>
                                
                            </thead>
                            <tr>
                                            
                                <td>

                                    <div class="input-group" style="width: 100%" >

                                        <?php if(isset($tamplate_data_selected['group_id_box']['category_matching_csv_column_name']) && $tamplate_data_selected['group_id_box']['category_matching_csv_column_name'] ){ ?>
                                            <input name="odmpro_tamplate_data[group_id_box][category_matching_csv_column_name]"  value="<?php echo $tamplate_data_selected['group_id_box']['category_matching_csv_column_name'] ?>" class="form-control " type="text" />
                                        <?php }else{ ?>
                                            <input name="odmpro_tamplate_data[group_id_box][category_matching_csv_column_name]" value=""  class="form-control " type="text" />
                                        <?php } ?>

                                    </div>

                                </td>
                                <td>

                                    <div class="input-group" style="width: 100%" >

                                        <?php if(isset($tamplate_data_selected['group_id_box']['category_matching_csv_delimeter']) && $tamplate_data_selected['group_id_box']['category_matching_csv_delimeter'] ){ ?>
                                            <input name="odmpro_tamplate_data[group_id_box][category_matching_csv_delimeter]"  value="<?php echo $tamplate_data_selected['group_id_box']['category_matching_csv_delimeter'] ?>" class="form-control " type="text" />
                                        <?php }else{ ?>
                                            <input name="odmpro_tamplate_data[group_id_box][category_matching_csv_delimeter]" value=""  class="form-control " type="text" />
                                        <?php } ?>

                                    </div>

                                </td>
                                            
                            </tr>
                        </table>
                            
                        </div>
                        </td>
                </tr>
                <?php } ?>
                <tr style="display: none" >
                    <td class="text-left">
                        <?php echo $title_group_id_box ?>
                    </td>
                    <td class="text-left">
                        
                        <?php if(isset($actions_with_data_group['action_before_import']) && FALSE){ ?>
                        
                        <h3><span onclick="showHide('#title_group_id_box_disable_type');" style="border-bottom: 1px dashed; cursor: pointer"></span></h3>
                        
                        <div style="display: none;" id="title_group_id_box_disable_type2"> 
                            
                            <table class="table table-bordered table-hover" style="margin-bottom: 0px;">
                            <thead>
                                
                                <tr>

                                    <td><?php echo $title_group_id_box_vendor_id ?></td>
                                    <td><?php echo $title_group_id_box_vendor_operator ?></td>
                                    <td><?php echo $title_group_id_box_vendor_value ?></td>

                                </tr>
                                
                            </thead>
                            <?php for($i=0;$i<$count_group_id_box;$i++){ ?>

                            
                                        <tr>
                            
                                            <td>
                                            
                                                <div class="input-group" >
                                                    <select name="odmpro_tamplate_data[group_id_box][product_data][<?php echo $i ?>][product_field]"  class="form-control ">
                                                        <option value="0" ><?php echo $text_type_data_ignor; ?></option>
                                                            <?php foreach($product_fields_group_id_box as $product_field => $product_value){ ?>
                                                                <?php if(isset($tamplate_data_selected['group_id_box']['product_data'][$i]['product_field']) && $tamplate_data_selected['group_id_box']['product_data'][$i]['product_field']==$product_field ){ ?>
                                                        <option value="<?php echo $product_field ?>" selected="" ><?php echo $product_value; ?></option>
                                                                <?php }else{ ?>
                                                        <option value="<?php echo $product_field ?>" ><?php echo $product_value; ?></option> 
                                                                <?php } ?>
                                                            <?php } ?>
                                                    </select>
                                                </div>

                                            </td>
                                            
                                            <td>
                                            
                                                <div class="input-group" >
                                                    <select name="odmpro_tamplate_data[group_id_box][product_data][<?php echo $i ?>][operator]"  class="form-control ">
                                                        <option value="0" ><?php echo $text_type_data_ignor; ?></option>
                                                            <?php foreach($operators_group_id_box as $product_field => $product_value){ ?>
                                                                <?php if(isset($tamplate_data_selected['group_id_box']['product_data'][$i]['operator']) && $tamplate_data_selected['group_id_box']['product_data'][$i]['operator']==$product_field ){ ?>
                                                        <option value="<?php echo $product_field ?>" selected="" ><?php echo $product_value; ?></option>
                                                                <?php }else{ ?>
                                                        <option value="<?php echo $product_field ?>" ><?php echo $product_value; ?></option> 
                                                                <?php } ?>
                                                            <?php } ?>
                                                    </select>
                                                </div>

                                            </td>
                                            
                                            <td>
                                            
                                                <div class="input-group" >
                                                    
                                                    <?php if(isset($tamplate_data_selected['group_id_box']['product_data'][$i]['value']) && $tamplate_data_selected['group_id_box']['product_data'][$i]['value'] ){ ?>
                                                        <input name="odmpro_tamplate_data[group_id_box][product_data][<?php echo $i ?>][value]"  value="<?php echo $tamplate_data_selected['group_id_box']['product_data'][$i]['value'] ?>" class="form-control " type="text" />
                                                    <?php }else{ ?>
                                                        <input name="odmpro_tamplate_data[group_id_box][product_data][<?php echo $i ?>][value]" value=""  class="form-control " type="text" />
                                                    <?php } ?>
                                                                
                                                </div>

                                            </td>
                                            
                                        </tr>


                            <?php } ?>
                        </table>
                        
                        <h4><?php echo $title_group_id_box_disable_type ?></h4>
                        <div class="well well-sm" style="max-height: 200px; overflow: auto; margin-bottom:">
                            <div class="checkbox">
                                    
                                    <?php echo $title_group_id_box_disable_product; ?>
                                    <select name="odmpro_tamplate_data[group_id_box][disable_product]" class="form-control">
                                            
                                        <?php if ( isset($tamplate_data_selected['group_id_box']['disable_product']) && $tamplate_data_selected['group_id_box']['disable_product'] ) { ?>
                                            <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                            <option value="0" ><?php echo $entry_disable; ?></option>
                                        <?php }else{ ?>
                                            <option value="1"  ><?php echo $entry_enable; ?></option>
                                            <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                        <?php } ?>
                                                    
                                    </select>
                                <br>
                                    
                                    <?php echo $title_group_id_box_skip_by_quantity; ?>
                                    <select name="odmpro_tamplate_data[group_id_box][skip_by_quantity]" class="form-control">
                                            
                                        <?php if ( isset($tamplate_data_selected['group_id_box']['skip_by_quantity']) && $tamplate_data_selected['group_id_box']['skip_by_quantity'] ) { ?>
                                            <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                            <option value="0" ><?php echo $entry_disable; ?></option>
                                        <?php }else{ ?>
                                            <option value="1"  ><?php echo $entry_enable; ?></option>
                                            <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                        <?php } ?>
                                                    
                                    </select>
                                <br>
                                    
                                    <?php echo $title_group_id_box_skip_by_price; ?>
                                    <select name="odmpro_tamplate_data[group_id_box][skip_by_price]" class="form-control">
                                            
                                        <?php if ( isset($tamplate_data_selected['group_id_box']['skip_by_price']) && $tamplate_data_selected['group_id_box']['skip_by_price'] ) { ?>
                                            <option value="1" selected="" ><?php echo $entry_enable; ?></option>
                                            <option value="0" ><?php echo $entry_disable; ?></option>
                                        <?php }else{ ?>
                                            <option value="1"  ><?php echo $entry_enable; ?></option>
                                            <option value="0" selected=""><?php echo $entry_disable; ?></option> 
                                        <?php } ?>
                                                    
                                    </select>
                                <br>
                                <br>
                                    <?php if ( isset($tamplate_data_selected['group_id_box']['disable_quantity']) ) { ?>
                                    <?php echo $title_group_id_box_disable_quantity; ?>
                                        <input type="text" name="odmpro_tamplate_data[group_id_box][disable_quantity]" value="<?php echo $tamplate_data_selected['group_id_box']['disable_quantity'] ?>" class="form-control " />
                                    
                                    <?php } else { ?>
                                    <?php echo $title_group_id_box_disable_quantity; ?>
                                        <input type="text" name="odmpro_tamplate_data[group_id_box][disable_quantity]" value="" class="form-control " />
                                    
                                    <?php } ?>
                                <br>
                                    <?php if ( isset($tamplate_data_selected['group_id_box']['disable_price']) ) { ?>
                                    <?php echo $title_group_id_box_disable_price; ?>
                                        <input type="text" name="odmpro_tamplate_data[group_id_box][disable_price]" value="<?php echo $tamplate_data_selected['group_id_box']['disable_price'] ?>" class="form-control " />
                                    
                                    <?php } else { ?>
                                    <?php echo $title_group_id_box_disable_price; ?>
                                        <input type="text" name="odmpro_tamplate_data[group_id_box][disable_price]" value="" class="form-control " />
                                    
                                    <?php } ?>
                                
                            </div>
                        </div>
                        
                        </div>
                        <hr>
                        
                        <?php } ?>
                        
                        
                        
                        <hr>
                        
                        
                        <?php if(isset($actions_with_data_group['composite_id'])){ ?>
                        
                        <h3><span onclick="showHide('#title_group_id_box_prefix')" style="border-bottom: 1px dashed; cursor: pointer"><?php echo $title_group_id_box_prefix ?></span></h3>
                        <div class="well well-sm" style="max-height: 200px; overflow: auto; margin-bottom: 0px; display: none;" id="title_group_id_box_prefix">
                            
                            <table class="table table-bordered table-hover">
                            <tr>
                                            
                                <td>

                                    <div class="input-group" style="width: 100%" >

                                        <?php echo $title_group_id_box_left_prefix ?>

                                    </div>

                                </td>
                                <td>

                                    <div class="input-group" style="width: 100%" >

                                        <?php if(isset($tamplate_data_selected['group_id_box']['left_prefix']) && $tamplate_data_selected['group_id_box']['left_prefix']!=''){ ?>
                                            <input name="odmpro_tamplate_data[group_id_box][left_prefix]"  value="<?php echo $tamplate_data_selected['group_id_box']['left_prefix'] ?>" class="form-control" type="text" />
                                        <?php }else{ ?>
                                            <input name="odmpro_tamplate_data[group_id_box][left_prefix]" value=""  class="form-control" type="text" />
                                        <?php } ?>

                                    </div>

                                </td>
                                            
                            </tr>
                            
                            <tr>
                                            
                                <td>

                                    <div class="input-group" style="width: 100%" >

                                        <?php echo $title_group_id_box_right_prefix ?>

                                    </div>

                                </td>
                                <td>

                                    <div class="input-group" style="width: 100%" >

                                        <?php if(isset($tamplate_data_selected['group_id_box']['right_prefix']) && $tamplate_data_selected['group_id_box']['right_prefix']!=''){ ?>
                                            <input name="odmpro_tamplate_data[group_id_box][right_prefix]"  value="<?php echo $tamplate_data_selected['group_id_box']['right_prefix'] ?>" class="form-control" type="text" />
                                        <?php }else{ ?>
                                            <input name="odmpro_tamplate_data[group_id_box][right_prefix]" value=""  class="form-control" type="text" />
                                        <?php } ?>

                                    </div>

                                </td>
                                            
                            </tr>
                            
                            
                            
                        </table>
                            
                        </div>
                        
                        <?php } ?>
                        
                    </td>
                </tr>
                
                <?php } ?>

                <tr>
                        <td>Отбор данных для импорта</td>
                        <td><?php if($filter_fields){ ?>

                            <table class="table table-bordered table-hover">
                                <thead>

                                    <tr>

                                        <td>Название поля в файле импорта<span data-toggle="tooltip" title="" data-original-title="Выберите поле, чтобы сделать по нему фильтрацию и действие, как будет указано в действиях"></span></td>
                                        <td>Оператор</td>
                                        <td>Значение(я)<span data-toggle="tooltip" title="" data-original-title="Если несколько, перечислите через вертикальную черту: |. Если вертикальная черта используются внутри данных, поставьте перед чертой символ экранирования: \|"></span></td>
                                        <td>Действие<span data-toggle="tooltip" title="" data-original-title="Пропускать - строка с данными будет пропущена при импорте, если совпадет условие. Оставить - строки с данными, у которых условие не совпадет, будут пропущены"></span></td>

                                    </tr>

                                </thead>
                                <?php for($i=0;$i<5;$i++){ ?>


                                            <tr>

                                                <td>

                                                    <div class="input-group" >
                                                        <select name="odmpro_tamplate_data[filter_import][<?php echo $i ?>][field]"  class="form-control">
                                                            <option value="0" >Выбрать</option>
                                                                <?php foreach($filter_fields as $filter_field => $filter_field_name){ ?>
                                                                    <?php if(isset($tamplate_data_selected['filter_import'][$i]['field']) && $tamplate_data_selected['filter_import'][$i]['field']==$filter_field ){ ?>
                                                            <option value="<?php echo $filter_field ?>" selected="" ><?php echo $filter_field_name; ?></option>
                                                                    <?php }else{ ?>
                                                            <option value="<?php echo $filter_field ?>" ><?php echo $filter_field_name; ?></option> 
                                                                    <?php } ?>
                                                                <?php } ?>
                                                        </select>
                                                    </div>

                                                </td>

                                                <td>

                                                    <div class="input-group" >
                                                        <select name="odmpro_tamplate_data[filter_import][<?php echo $i ?>][operator]"  class="form-control">
                                                            <option value="0" >Выбрать</option>
                                                                <?php foreach($filter_operators as $product_field => $product_value){ ?>
                                                                    <?php if(isset($tamplate_data_selected['filter_import'][$i]['operator']) && $tamplate_data_selected['filter_import'][$i]['operator']==$product_field ){ ?>
                                                            <option value="<?php echo $product_field ?>" selected="" ><?php echo $product_value; ?></option>
                                                                    <?php }else{ ?>
                                                            <option value="<?php echo $product_field ?>" ><?php echo $product_value; ?></option> 
                                                                    <?php } ?>
                                                                <?php } ?>
                                                        </select>
                                                    </div>

                                                </td>

                                                <td>

                                                    <div class="input-group" >

                                                        <?php if(isset($tamplate_data_selected['filter_import'][$i]['value']) ){ ?>
                                                            <input name="odmpro_tamplate_data[filter_import][<?php echo $i ?>][value]"  value="<?php echo $tamplate_data_selected['filter_import'][$i]['value'] ?>" class="form-control " type="text" />
                                                        <?php }else{ ?>
                                                            <input name="odmpro_tamplate_data[filter_import][<?php echo $i ?>][value]" value=""  class="form-control " type="text" />
                                                        <?php } ?>

                                                    </div>

                                                </td>

                                                <td>

                                                    <div class="input-group" >
                                                        <select name="odmpro_tamplate_data[filter_import][<?php echo $i ?>][action]"  class="form-control ">
                                                                <?php foreach($filter_actions as $product_field => $product_value){ ?>
                                                                    <?php if(isset($tamplate_data_selected['filter_import'][$i]['action']) && $tamplate_data_selected['filter_import'][$i]['action']==$product_field ){ ?>
                                                            <option value="<?php echo $product_field ?>" selected="" ><?php echo $product_value; ?></option>
                                                                    <?php }else{ ?>
                                                            <option value="<?php echo $product_field ?>" ><?php echo $product_value; ?></option> 
                                                                    <?php } ?>
                                                                <?php } ?>
                                                        </select>
                                                    </div>

                                                </td>

                                            </tr>


                                <?php } ?>
                            </table>

                        <?php }else{ ?>

                            <div class="alert alert-info" style="margin-bottom: 0px;">Чтобы получить настройки для отбора данных для импорта необходимо сохранить этот профиль (на шаге 3)</div>

                        <?php } ?></td>
                        </tr>

            </tbody>
</table>

    <?php if($demo_mode && $type_process=='import'){ ?>
        <div class="info-box-modal"  style="margin-bottom: 5px;"><?php echo $text_info_box_modal_step_2_import_csv ?></div>
        
        <div class="clearfix"></div>
    <?php } ?>
    
    <?php if($type_process=='import'){ ?>

        <a onclick="getStepTwoSettings('<?php echo $type_process ?>',0);getProcessHistoryStatus('<?php echo $tamplate_data_selected_id; ?>','<?php echo $anycsv_sinch_supplier_name ?>','<?php echo $type_process ?>');" class="btn btn-primary btn-step-two"><?php echo $entry_download_field_to_file; ?></a>
        
            <?php if($process_history_info){ ?>

                    <div class="alert alert-info" style="margin-top: 5px;">

                        Последняя запись в логе (возможно данные были загружены не полностью, ознакомьтесь с информацией). <h4 style="margin-bottom: 5px !important; margin-top: 7px !important;">Информация</h4> <?php echo $process_history_info; ?>
                        <br><br>
                        <a onclick="getStepTwoSettings('<?php echo $type_process ?>',1);getProcessHistoryStatus('<?php echo $tamplate_data_selected_id; ?>','<?php echo $anycsv_sinch_supplier_name ?>','<?php echo $type_process ?>');" class="btn btn-danger btn-step-two">Загрузить файл по уже обработанным данным</a>
                        <a onclick="getStepTwoSettings('<?php echo $type_process ?>',2);getProcessHistoryStatus('<?php echo $tamplate_data_selected_id; ?>','<?php echo $anycsv_sinch_supplier_name ?>','<?php echo $type_process ?>');" class="btn btn-danger btn-step-two">Продолжить обработку с места последней остановки</a>

                    </div>

            <?php } ?>
        
    <?php }elseif($type_process=='export'){ ?>

    <script type="text/javascript"><!--
    
        $(document).ready(function() {

            getStepTwoSettings('<?php echo $type_process ?>',0);

        });

    //--></script> 
    
    <?php } ?>

<?php }else{ ?>

    <?php echo $entry_odmpro_format_data_redirect; ?>

<?php } ?>
<script type="text/javascript"><!--
    
        $(document).ready(function() {
            $('input[name=\'odmpro_tamplate_data[log_file_name]\']').change();
        });

    //--></script> 

<script type="text/javascript"><!--
    
$('#button-upload2').on('click', function() {
	$('#form-upload').remove();
	
	$('body').prepend('<form enctype="multipart/form-data" id="form-upload2" style="display: none;"><input type="file" name="file" /></form>');

	$('#form-upload2 input[name=\'file\']').trigger('click');
	
	if (typeof timer != 'undefined') {
    	clearInterval(timer);
	}
	
	timer = setInterval(function() {
		if ($('#form-upload2 input[name=\'file\']').val() != '') {
			clearInterval(timer);		
			
			$.ajax({
				url: 'index.php?route=<?php echo $path_oc_version; ?>/csv_ocext_dmpro/loadTemplateSetting&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
				type: 'post',		
				dataType: 'json',
				data: new FormData($('#form-upload2')[0]),
				cache: false,
				contentType: false,
				processData: false,		
				beforeSend: function() {
					$('#button-upload2').button('loading');
				},
				complete: function() {
					$('#button-upload2').button('reset');
				},	
				success: function(json) {
					if (json['error']) {
						alert(json['error']);
					}
								
					if (json['success']) {
						location.reload();
					}
				},			
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
});    
    
    
$('#button-upload').on('click', function() {
	$('#form-upload').remove();
	
	$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');

	$('#form-upload input[name=\'file\']').trigger('click');
	
	if (typeof timer != 'undefined') {
    	clearInterval(timer);
	}
	
	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file\']').val() != '') {
			clearInterval(timer);		
			
			$.ajax({
				url: 'index.php?route=catalog/download/upload&<?php echo $token_name; ?>=<?php echo ${$token_name}; ?>',
				type: 'post',		
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,		
				beforeSend: function() {
					$('#button-upload').button('loading');
				},
				complete: function() {
					$('#button-upload').button('reset');
				},	
				success: function(json) {
					if (json['error']) {
						alert(json['error']);
					}
								
					if (json['success']) {
						alert(json['success']);
						$('input[name=\'odmpro_tamplate_data[file_upload]\']').attr('value', json['filename']);
					}
				},			
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
});


$(document).ready(function() {
    
    

});

//--></script> 