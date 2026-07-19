<?php if($type_data_and_column_additional){ ?>

    <h5 style="color: orange; font-size: 12px; font-weight: bold; margin-bottom: 10px;"><?php echo $entry_type_data_column_title ?> <span data-toggle="tooltip" title="" data-original-title='<?php echo $entry_type_data_column_title_help; ?>' ></span></h5>
    <div class="additional_forms">
        
    <?php $box_title = ''; ?>
        
    <?php foreach($type_data_and_column_additional as $j_additional => $additional){ ?>
    
        <?php if(isset($additional['hide_this_additinal_data']) && $additional['hide_this_additinal_data']){ ?>
    
        <div style='display:none' >
        
        <?php } ?>
        
        
        <?php if(isset($additional['box-title']) && $additional['box-title'] && $additional['box-title']!==$box_title){ ?>
        
            <?php $box_title = $additional['box-title']; ?>
        
            
            
            <div style="padding-top: 10px;"><?php echo $box_title; ?></div>
        
        <?php } ?>
        
        
                <?php if(isset($additional["help"]) && $additional["help"]){ ?>
                    <div class="help-box" title="" data-original-title='<?php echo $additional["help"]; ?>' ><?php echo $additional["help"]; ?>
                
                        <?php unset($additional["help"]); ?>   
                        
                    </div>
                <?php } ?>
            
                <?php if(isset($additional['element']) && $additional['element']=='input' && isset($additional['group'])){ ?>
            
                <div ><div class="help-box" style="margin-top: 10px; "><?php echo $additional["data-original-title"]; ?></div></div>
                
                <table class="table table-bordered table-bordered">
                    <thead>
                        <tr>
                            <th>Точное название значения в ячейке</th>
                            <th>Соответствует количеству товара</th>
                        </tr>
                    </thead>
                    <?php foreach($additional['group'] as $g => $group_info){ ?>
                        <tr>
                            <td><input style='margin-top:5px; display: inline-block;' type="text" name="<?php echo $additional['group'][$g]['field']['name'] ?>" value="<?php echo $additional['group'][$g]['field']['value'] ?>" class='form-control'  /></td>
                        <td><input style='margin-top:5px;display: inline-block; width: 92%' type="text" name="<?php echo $additional['group'][$g]['value']['name'] ?>" value="<?php echo $additional['group'][$g]['value']['value'] ?>" class='form-control'  /></td>
                        </tr>
                    <?php } ?>
                    </table>
                
                <?php }elseif(isset($additional['element']) && $additional['element']=='input' && isset($additional['range'])){ ?>
            
                <div>
                    <div class="help-box" style="margin-top: 10px; "><?php echo $additional["data-original-title"]; ?></div>  
                </div>
                <div>
                <table class="table table-bordered table-bordered">
                    <thead>
                        <tr>
                            <th>ОТ (≥)</th>
                            <th>ДО (<)</th>
                            <th>x</th>
                            <th>+</th>
                        </tr>
                    </thead>
                    <?php foreach($additional['range'] as $g => $group_info){ ?>
                    <tr>
                    <td><input style='margin-top:5px; display: inline-block;' type="text" name="<?php echo $additional['range'][$g]['from']['name'] ?>" value="<?php echo $additional['range'][$g]['from']['value'] ?>" class='form-control'  /></td>
                    <td><input style='margin-top:5px;display: inline-block; width: 92%' type="text" name="<?php echo $additional['range'][$g]['to']['name'] ?>" value="<?php echo $additional['range'][$g]['to']['value'] ?>" class='form-control'  /></td>
                    <td><input style='margin-top:5px;display: inline-block; width: 92%' type="text" name="<?php echo $additional['range'][$g]['multiply']['name'] ?>" value="<?php echo $additional['range'][$g]['multiply']['value'] ?>" class='form-control'  /></td>
                    <td><input style='margin-top:5px;display: inline-block; width: 92%' type="text" name="<?php echo $additional['range'][$g]['plus']['name'] ?>" value="<?php echo $additional['range'][$g]['plus']['value'] ?>" class='form-control'  /></td>
                    </tr>
                    <?php } ?>
                    </table>
                    </div>
                <?php }elseif(isset($additional['element']) && $additional['element']=='textarea2' && !isset($additional['group']) && !isset($additional['range'])){ ?>
            
                    <?php if(!isset($additional['style'])){
                    
                        $additional['style'] = '';
                    
                    } ?>
                    
                    <div style="margin-top: 10px; <?php echo $additional['style']; ?> ">
                        <b><?php echo $additional["function-title"]; ?></b>
                    </div>  
                
                    <textarea style='margin-top:5px; <?php echo $additional['style']; ?>' name="<?php echo $additional['name'] ?>" class='form-control'  ><?php echo $additional['value'] ?></textarea>
                
                
                <?php }elseif(isset($additional['element']) && $additional['element']=='textarea' && !isset($additional['group']) && !isset($additional['range'])){ ?>
            
                    <?php if(!isset($additional['style'])){
                    
                        $additional['style'] = '';
                    
                    } ?>
                    
                    <div style="margin-top: 10px; <?php echo $additional['style']; ?> ">
                        <span style="border-bottom: 1px dashed; cursor: pointer; color: #1abc9c" onclick="showHide('#function-title-help-<?php echo md5($additional['name']).'-'.$j_additional ?>')"><b><?php echo $additional["function-title"]; ?></b></span>
                        <div style="display: none; width: 100%; margin-top: 8px;" id="function-title-help-<?php echo md5($additional['name']).'-'.$j_additional ?>"><?php echo $additional["function-title-help"]; ?></div>
                    </div>  
                
                    <textarea style='margin-top:5px; <?php echo $additional['style']; ?>' name="<?php echo $additional['name'] ?>" class='form-control'  ><?php echo $additional['value'] ?></textarea>

                    <?php if(isset($additional["help"]) && $additional["help"]){ ?>
                    <div data-toggle="help-box" title="" data-original-title='<?php echo $additional["help"]; ?>' style="<?php echo $additional['style']; ?>" ></div>
                    <?php } ?>
                
                <?php }elseif(isset($additional['element']) && $additional['element']=='input' && !isset($additional['group']) && !isset($additional['range'])){ ?>
            
                    <?php if(!isset($additional['style'])){
                    
                        $additional['style'] = '';
                    
                    } ?>
                
                    <input style='margin-top:5px;width:90% !important; display: inline-block; <?php echo $additional['style']; ?>' type="<?php echo $additional['type'] ?>" name="<?php echo $additional['name'] ?>" value="<?php echo $additional['value'] ?>" placeholder="<?php echo $additional['placeholder'] ?>" class='form-control'  />

                    <?php if(isset($additional["data-original-title"]) && $additional["data-original-title"]){ ?>
                        <span data-toggle="tooltip" title="" data-original-title='<?php echo $additional["data-original-title"]; ?>' style="<?php echo $additional['style']; ?>" ></span>
                    <?php } ?>

                    <?php if(isset($additional["help"]) && $additional["help"]){ ?>
                    <div data-toggle="help-box" title="" data-original-title='<?php echo $additional["help"]; ?>' style="<?php echo $additional['style']; ?>" ></div>
                    <?php } ?>
                
                <?php }elseif(isset($additional['element']) && $additional['element']=='select'){ ?>
                
                <select name="<?php echo $additional['name'] ?>" class="form-control select-type-data-column-additional select-type-data-column-additional-<?php echo md5($additional['name']) ?>" onchange="<?php echo $additional['onchange'] ?>" style="margin-top: 5px;<?php echo $additional['style'] ?>; width:90%; display: inline-block">
                    
                    <?php $optiongroup = ''; ?>
                    
                    <?php foreach($additional['options'] as $option){ ?>
                        
                        <?php if(isset($option['optiongroup']) && $optiongroup!=$option['optiongroup']){ ?>
                            <?php $optiongroup = $option['optiongroup']; ?>
                            <optgroup label="<?php echo $option['optiongroup'] ?>" <?php if(isset($option['style'])){ echo " style='".$option['style']."' "; } ?>  >
                        <?php } ?>
                        
                            <option value="<?php echo $option['value'] ?>" <?php echo $option['selected'] ?> ><?php echo $option['text'] ?></option>
                        
                        <?php if(isset($option['optiongroup']) && $optiongroup!=$option['optiongroup']){ ?>
                            <?php $optiongroup = $option['optiongroup']; ?>
                            </optgroup>
                            <optgroup label="<?php echo $option['optiongroup'] ?>" <?php if(isset($option['style'])){ echo " style='".$option['style']."' "; } ?>  >
                        <?php }elseif(!isset($option['optiongroup']) && $optiongroup){ ?>
                            </optgroup>
                        <?php } ?>
                        
                    <?php } ?>
                    
                        <?php if($optiongroup){ ?>
                            </optgroup>
                        <?php } ?>
                    
                </select>
            
                <?php if(isset($additional["data-original-title"]) && $additional["data-original-title"]){ ?>
                    <span data-toggle="tooltip" title="" data-original-title='<?php echo $additional["data-original-title"]; ?>' ></span>
                <?php } ?>
                
                <script type="text/javascript"><!--
    
                $(document).ready(function() {
                    $('.select-type-data-column-additional-<?php echo md5($additional['name']) ?>').change();
                });

            //--></script> 
                
                
                <?php if($box_title && ( !isset( $type_data_and_column_additional[$j_additional+1]['box-title']) || !$type_data_and_column_additional[$j_additional+1]['box-title'] || (isset( $type_data_and_column_additional[$j_additional+1]['box-title']) && $type_data_and_column_additional[$j_additional+1]['box-title']!==$box_title) ) ){ ?>
    
                
                <?php $box_title = ''; ?>
                
                <hr>    

                <?php } ?>
                
                <?php if(!isset($additional['hide_this_additinal_data']) || !$additional['hide_this_additinal_data']){ ?>
                </div>
                <?php } ?>
                
            <?php } ?>
        </div>    
    <?php } ?>
    
    <script type="text/javascript"><!--
    
    $(document).ready(function() {
        updateSaveButton('<?php echo $type_process ?>');
    });
    
//--></script> 
    
<?php } ?>