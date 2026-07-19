    <?php echo $header; ?>
<div class="container">
    <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
    </ul>
        <div class="row"><?php echo $column_left; ?>
            <?php if ($column_left && $column_right) { ?>
            <?php $class = 'col-sm-6'; ?>
            <?php } elseif ($column_left || $column_right) { ?>
            <?php $class = 'col-sm-9'; ?>
            <?php } else { ?>
            <?php $class = 'col-sm-12'; ?>
            <?php } ?>
            <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
                <?php if ($success != '') { ?>
            <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?></div>
            <?php } ?>
                <?php if ($error_name) { ?>
                <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_name; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php } ?>
                <?php if ($error_email) { ?>
                <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_email; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php } ?>
                <?php if ($error_comment) { ?>
                <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_comment; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php } ?>
                <?php if ($error_captcha) { ?>
                <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_captcha; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php } ?>

            <h1><?php echo $heading_title; ?></h1>
            <?php
                    if(isset($shop_rating_authorized)){
                        if(isset($customer_id) && $customer_id != 0){
                            $show_form = true;
                        }else{
                            $show_form = false;
                        }

                    }else{ $show_form = true; }
                ?>


                <div class="shop_summary_rating">
                    <?php if(isset($shop_rating_summary)){ ?>
                    <div class="shop_summary_general">
                        <div class="shop_summary_general_title"><?php echo $text_summary; ?></div>
                        <div class="shop_summary_general_rating"><?php echo $general['summ']?></div>
                        <div class="summary-rate-star-show">
                            <div class="summary-rate-star-show star-change" style="<?php echo 'width: '.$general['summ_perc'].'%'; ?>"></div>
                        </div>
                        <div class="shop_summary_general_desc"><?php echo $text_count; ?> <?php echo $general['count'];?></div>
                    </div>
                    <div class="shop_summary_detail">
                        <div class="shop_summary_detail_line">
                            <span class="star">5</span>
                            <span class="percent_line" style=" <?php echo 'width:'.round($general['5']*100/$general['count']).'px';?>"></span>
                            <span><?php echo $general['5'];?>x</span>
                        </div>
                        <div class="shop_summary_detail_line">
                            <span class="star">4</span>
                            <span class="percent_line" style="<?php echo 'width:'.round($general['4']*100/$general['count']).'px';?>"></span>
                            <span><?php echo $general['4'];?>x</span>
                        </div>
                        <div class="shop_summary_detail_line">
                            <span class="star">3</span>
                            <span class="percent_line" style="<?php echo 'width:'.round($general['3']*100/$general['count']).'px';?>"></span>
                            <span><?php echo $general['3'];?>x</span>
                        </div>
                        <div class="shop_summary_detail_line">
                            <span class="star">2</span>
                            <span class="percent_line" style="<?php echo 'width:'.round($general['2']*100/$general['count']).'px';?>"></span>
                            <span><?php echo $general['2'];?>x</span>
                        </div>
                        <div class="shop_summary_detail_line">
                            <span class="star">1</span>
                            <span class="percent_line" style="<?php echo 'width:'.round($general['1']*100/$general['count']).'px';?>"></span>
                            <span><?php echo $general['1'];?>x</span>
                        </div>
                    </div>
                    <div class="shop_summary_add_rating">

                        <?php if($show_form){ ?>
                        
                        <button class="rating_btn add_rating-button" data-remodal-target="ratingModal"><?php echo $send_rating;?></button>
                        
                        <?php } else { ?>
                        <?php echo $text_login; ?>
                        <?php } ?>
                    </div>
                    <div style="clear: both"></div>
                    <?php }else{ ?>
                    <?php if($show_form){ ?>
                    <button class="rating_btn add_rating-button" data-remodal-target="ratingModal"><?php echo $send_rating;?></button>
                    <?php } else { ?>
                    <?php echo $text_login; ?>

                    <?php } ?>
                    <?php } ?>
                </div>

                <div class="shop_rates_list">
                    <?php foreach($ratings as $rating){ ?>
                    <div class="ratings-item">
                        <div class="ratings-item-header">
                            <div class="rating-sender">
                                <b><?php echo $rating['customer_name'];?></b>
                                <?php if(isset($rating['customer_id']) && $rating['customer_id'] != 0){ ?>
                                <span>(<?php echo $registered_customer_text; ?>)</span>
                                <?php } ?>
                            </div>
                            <div class="rating-date"><?php echo date('d.m.Y', strtotime($rating['date_added']));?></div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="rates-line">
                            <div class="ratings-item-rates" <?php if($rating['customs']){ echo 'style="width: 47%; float:left"'; }?>>
                            <?php if(isset($shop_rating_shop_rating)){ ?>
                            <div class="ratings-item-rates-item shop-rates" <?php if($rating['customs']){ echo 'style="width: 100%"'; }?>>
                            <div class="rates-title"><?php echo $entry_rate; ?> </div>
                            <div class="rate-stars">
                                <div class="rate-star-show <?php if($rating['shop_rate'] >= 1)echo 'star-change';?>"></div>
                                <div class="rate-star-show <?php if($rating['shop_rate'] >= 2)echo 'star-change';?>"></div>
                                <div class="rate-star-show <?php if($rating['shop_rate'] >= 3)echo 'star-change';?>"></div>
                                <div class="rate-star-show <?php if($rating['shop_rate'] >= 4)echo 'star-change';?>"></div>
                                <div class="rate-star-show <?php if($rating['shop_rate'] == 5)echo 'star-change';?>"></div>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if(isset($shop_rating_site_rating)){ ?>
                        <div class="ratings-item-rates-item shop-rates" <?php if($rating['customs']){ echo 'style="width: 100%"'; } ?> >
                        <div class="rates-title"><?php echo $entry_site_rate; ?> </div>
                        <div class="rate-stars">
                            <div class="rate-star-show <?php if($rating['site_rate'] >= 1)echo 'star-change';?>"></div>
                            <div class="rate-star-show <?php if($rating['site_rate'] >= 2)echo 'star-change';?>"></div>
                            <div class="rate-star-show <?php if($rating['site_rate'] >= 3)echo 'star-change';?>"></div>
                            <div class="rate-star-show <?php if($rating['site_rate'] >= 4)echo 'star-change';?>"></div>
                            <div class="rate-star-show <?php if($rating['site_rate'] == 5)echo 'star-change';?>"></div>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="clearfix"></div>
                </div>
                <?php if($rating['customs']){ ?>
                <div class="ratings-item-rates" style="width: 47%; float: right; margin-top: 15px">
                    <?php foreach($rating['customs'] as $custom_rate){ ?>
                    <div class="ratings-item-rates-item custom-rate">
                        <div class="rates-title"><?php echo $custom_rate['title']; ?> </div>
                        <div class="rate-stars">
                            <div class="rate-star-show small-stars <?php if($custom_rate['value'] >= 1)echo 'star-change';?>"></div>
                            <div class="rate-star-show small-stars <?php if($custom_rate['value'] >= 2)echo 'star-change';?>"></div>
                            <div class="rate-star-show small-stars <?php if($custom_rate['value'] >= 3)echo 'star-change';?>"></div>
                            <div class="rate-star-show small-stars <?php if($custom_rate['value'] >= 4)echo 'star-change';?>"></div>
                            <div class="rate-star-show small-stars <?php if($custom_rate['value'] == 5)echo 'star-change';?>"></div>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="clearfix"></div>
                </div>
                <?php } ?>
                <div class="clearfix"></div>
            </div>
            <div class="ratings-item-comment"><?php echo nl2br($rating['comment']);?></div>
            <?php if(isset($shop_rating_good_bad)){ ?>
            <div class="ratings-item-good-bad">
                <div class="good">
                    <ul>
                        <?php $goodonce = explode("\n", nl2br($rating['good'])) ?>
                        <?php foreach($goodonce as $good_item){ ?>
                        <?php if($good_item != ''){ ?>
                        <li><?php echo $good_item;?></li>
                        <?php }?>
                        <?php }?>
                    </ul>

                </div>
                <div class="bad">

                    <ul>
                        <?php $badonce = explode("\n", nl2br($rating['bad'])) ?>
                        <?php foreach($badonce as $bad_item){ ?>
                        <?php if($bad_item != ''){ ?>
                        <li><?php echo $bad_item;?></li>
                        <?php }?>
                        <?php }?>
                    </ul>
                </div>
                <div class="clearfix"></div>
            </div>
            <?php } ?>
            <?php if(isset($rating_answers[$rating['rate_id']]) && $rating_answers[$rating['rate_id']] != ''){ ?>
            <div class="ratings-item-answer">
                <div class="ratings-item-answer-title"><?php echo $answer; ?></div>
                <div class="ratings-item-answer-content"><p><?php echo nl2br($rating_answers[$rating['rate_id']]); ?></p></div>

            </div>
            <?php } ?>

        </div>


        <?php }?>

    </div>
    <div class="shop_rates_pagination">
        <div class="pagination"><?php echo $pagination; ?></div>
    </div>


    <?php if($show_form){ ?>
    <div class="remodal newShoprRating" data-remodal-id="ratingModal" role="dialog" aria-labelledby="ratingModalTitle" aria-describedby="ratingModalDesc">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" class="newShopRatingForm">
            <button data-remodal-action="close" class="remodal-close" aria-label="Close"></button>
            <div>
                <h2 id="ratingModalTitle"><?php echo $send_rating;?></h2>
                <div id="ratingModalDesc">
                    <div class="grid grid-2">
                        <div class="col">
                            <label class="rating-form-label" for="input-name"><?php echo $entry_name; ?></label>
                            <div class="">
                                <input type="text" name="name" value="<?php echo $name; ?>" id="input-name" class="form-control" required/>
                                <?php if ($error_name) { ?>
                                <div class="text-danger"><?php echo $error_name; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col">
                            <label class="rating-form-label" for="input-email"><?php echo $entry_email; ?></label>
                            <div class="">
                                <input type="text" name="email" value="<?php echo $email; ?>" id="input-email" class="form-control" required/>
                                <?php if ($error_email) { ?>
                                <div class="text-danger"><?php echo $error_email; ?></div>
                                <?php } ?>
                                <div class="field_desc"><?php echo $text_email_desc; ?></div>
                            </div>
                        </div>
                        <div style="clear: both"></div>

                    </div>
                    <div class="rate_separator"></div>
                    <div class="grid grid-2">
                        <?php if(!$form_custom_types){ ?>
                        <?php if(isset($shop_rating_shop_rating)){ ?>

                        <div class="col">
                            <label class="rating-form-label" for="shop_rate-input"><?php echo $entry_rate; ?></label>
                            <div class="hidden">
                                <input name="shop_rate-input" type="hidden" value="0">
                            </div>
                            <div class="rate-stars">
                                <div class="rate-star" id="shop_rate-1"></div>
                                <div class="rate-star" id="shop_rate-2"></div>
                                <div class="rate-star" id="shop_rate-3"></div>
                                <div class="rate-star" id="shop_rate-4"></div>
                                <div class="rate-star" id="shop_rate-5"></div>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if(isset($shop_rating_site_rating)){ ?>

                        <div class="col">
                            <label class="rating-form-label" for="site_rate-input"><?php echo $entry_site_rate; ?></label>
                            <div class="">
                                <input name="site_rate-input" type="hidden" value="0">
                            </div>
                            <div class="rate-stars">
                                <div class="rate-star" id="site_rate-1"></div>
                                <div class="rate-star" id="site_rate-2"></div>
                                <div class="rate-star" id="site_rate-3"></div>
                                <div class="rate-star" id="site_rate-4"></div>
                                <div class="rate-star" id="site_rate-5"></div>
                            </div>
                        </div>
                        <?php } ?>
                        <?php }else{ ?>
                        <div class="col">
                            <?php if(isset($shop_rating_shop_rating)){ ?>
                            <label class="rating-form-label" for="shop_rate-input"><?php echo $entry_rate; ?></label>
                            <div class="hidden">
                                <input name="shop_rate-input" type="hidden" value="0">
                            </div>
                            <div class="rate-stars">
                                <div class="rate-star" id="shop_rate-1"></div>
                                <div class="rate-star" id="shop_rate-2"></div>
                                <div class="rate-star" id="shop_rate-3"></div>
                                <div class="rate-star" id="shop_rate-4"></div>
                                <div class="rate-star" id="shop_rate-5"></div>
                            </div>
                            <?php } ?>
                            <?php if(isset($shop_rating_site_rating)){ ?>
                            <label class="rating-form-label" for="site_rate-input"><?php echo $entry_site_rate; ?></label>
                            <div class="">
                                <input name="site_rate-input" type="hidden" value="0">
                            </div>
                            <div class="rate-stars">
                                <div class="rate-star" id="site_rate-1"></div>
                                <div class="rate-star" id="site_rate-2"></div>
                                <div class="rate-star" id="site_rate-3"></div>
                                <div class="rate-star" id="site_rate-4"></div>
                                <div class="rate-star" id="site_rate-5"></div>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <?php if($form_custom_types){ ?>
                        <div class="col">
                            <?php foreach($form_custom_types as $custom){ ?>
                            <div class="grid grid-2">
                                <div class="col-mb">
                                    <label class="rating-form-label custom-types" for="site_rate-input"><?php echo $custom['title']; ?></label>                                 <input name="custom_<?php echo $custom['id'] ?>_rate-input" type="hidden" value="0">
                                </div>
                                <div class="col-mb">
                                    <div class="rate-stars custom-types">
                                        <div class="rate-star small-stars" id="custom_<?php echo $custom['id'] ?>_rate-1"></div>
                                        <div class="rate-star small-stars" id="custom_<?php echo $custom['id'] ?>_rate-2"></div>
                                        <div class="rate-star small-stars" id="custom_<?php echo $custom['id'] ?>_rate-3"></div>
                                        <div class="rate-star small-stars" id="custom_<?php echo $custom['id'] ?>_rate-4"></div>
                                        <div class="rate-star small-stars" id="custom_<?php echo $custom['id'] ?>_rate-5"></div>
                                    </div>
                                </div>
                                <div style="clear: both"></div>
                            </div>
                            <?php }?>
                        </div>
                        <?php }?>
                    </div>
                    <div style="clear: both"></div>
                    <div class="rate_separator"></div>
                    <div class="grid" style="margin-bottom: 20px">
                        <div class="col">
                            <label class=" control-label" for="input-comment"><?php echo $entry_comment; ?></label>
                            <div class="">
                                <textarea name="comment" rows="6" id="input-comment" class="form-control" required style="height: 100px"><?php echo $comment; ?></textarea>
                                <?php if ($error_comment) { ?>
                                <div class="text-danger"><?php echo $error_comment; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div style="clear: both"></div>
                    </div>
                    <?php if(isset($shop_rating_good_bad)){ ?>
                    <div class="grid grid-2">
                        <div class="col">
                            <label class="rating-form-label text-success" for="input-good"><i class="fa fa-plus fa-fw"></i><?php echo $entry_good; ?></label>
                            <div class="">
                                <textarea type="text" name="good" id="input-good" class="form-control" style="height: 75px"><?php echo $good; ?></textarea>
                            </div>
                        </div>
                        <div class="col">
                            <label class="rating-form-label text-danger " for="input-bad"><i class="fa fa-minus fa-fw "></i><?php echo $entry_bad; ?></label>
                            <div class="">
                                <textarea type="text" name="bad"  id="input-bad" class="form-control"  style="height: 75px"><?php echo $bad; ?></textarea>
                            </div>
                        </div>
                        <div style="clear: both"></div>
                        <div class="field_desc"><?php echo $god_bad_desc; ?></div>
                    </div>
                    <?php } ?>

                    <div class="grid">
                        <div class="col"><?php echo $captcha; ?></div>
                    </div>

                </div>
            </div>
            <br>
            <button type="submit" class="remodal-confirm add_rating-button"><?php echo $button_submit; ?></button>
            <button data-remodal-action="cancel" class="remodal-cancel"><?php echo $button_close; ?></button>
        </form>
    </div>
    <?php } ?>

    <script src="../catalog/view/javascript/jquery/remodal/remodal.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function(){
        $('[data-remodal-id=ratingModal]').remodal();
        $('.rate-star').hover(
                function(){
                    var params = this.id.split('-');
                    var type = params[0];
                    var id = params[1];
                    $('[id^='+type+']').removeClass('star-hover');

                    for(var i = 1; i<=id; i++){
                        $('#'+type+'-'+i).addClass('star-hover');
                    }
                },function(){
                    var params = this.id.split('-');
                    var type = params[0];
                    var id = params[1];

                    $('[id^='+type+']').removeClass('star-hover');
                }
        );
        $('.rate-star').click(function(){
            var params = this.id.split('-');
            var type = params[0];
            var id = params[1];
            $('[id^='+type+']').removeClass('star-change');

            for(var i = 1; i<=id; i++){
                $('#'+type+'-'+i).addClass('star-change');
            }
            //$("input[name="+type+"-input]").attr('checked', false);
            //$("input[name="+type+"-input][value=" + id + "]").attr('checked', true);
            $("input[name="+type+"-input]").val(id);
        });
    });

</script>
    </div>
<?php echo $column_right; ?>
    </div></div>
<?php echo $footer; ?>