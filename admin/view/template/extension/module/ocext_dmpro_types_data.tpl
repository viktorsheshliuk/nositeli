<?php if($columns){ ?>
    <select onchange="getTypesDataColumnsAdditional(this.value,'<?php echo $field ?>','#type_data_column_additional_<?php echo md5($field) ?>','<?php echo $type_process ?>')" name="odmpro_tamplate_data[type_data_column][<?php echo $field ?>][db_table___db_column]"  class="form-control select-type-data-column select-type-data-column_<?php echo md5($field) ?>">
                        <option value="0" ><?php echo $entry_select; ?></option>
            <?php foreach($columns as $db_table => $columns_table){ ?>
            
               <optgroup label="<?php echo ${'types_data_option_group_name_'.$db_table}; ?>">
                <?php foreach($columns_table as $db_column => $db_column_name){ ?>
                    <?php if(isset($tamplate_data_selected['type_data_column'][$field]['db_table___db_column']) && $tamplate_data_selected['type_data_column'][$field]['db_table___db_column'] == $db_table.'___'.$db_column ){ ?>
                        <option value="<?php echo $db_table.'___'.$db_column ?>" selected="" ><?php echo $db_column_name; ?></option>
                    <?php }else{ ?>
                        <option value="<?php echo $db_table.'___'.$db_column ?>" ><?php echo $db_column_name; ?></option> 
                    <?php } ?>
                <?php } ?>
                </optgroup>
            <?php } ?>
    </select>

    <div id="type_data_column_additional_<?php echo md5($field) ?>" class="well" style="margin-top: 5px; display: none;">
        
    </div>

<?php } ?>
<script type="text/javascript"><!--
    
    $(document).ready(function() {
        changeTypeDataColumn('select-type-data-column_<?php echo md5($field) ?>');
    });
    
//--></script> 