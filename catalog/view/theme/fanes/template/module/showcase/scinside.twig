<div id="sc-<?php echo $module; ?>" class="sc-main <?php echo $sc_class; ?>">
  <?php if ($title) { ?>
  <div class="sc-heading">
    <h3><?php echo $title; ?></h3>
  </div>
  <?php } ?>
  <div class="<?php echo $items_carousel ? 'sc-carousel sc-theme' : 'sc-grid'; ?>">
    <?php foreach ($items as $item) { ?>
    <div class="item-container">
      <div class="item-wrapper img-<?php echo $item_img_pos; ?><?php echo $item['active'] ? ' active' : ''; ?>">
        <?php if ($item_heading && $item_img_pos == 'bottom') { ?>
        <div class="item-heading">
          <a href="<?php echo $item['href']; ?>">
            <?php echo $item['name']; ?>
            <?php if ($count_status && $item['count']) { ?>
            <span class="item-count"><?php echo $item['count']; ?></span>
            <?php } ?>
          </a>
        </div>
        <?php } ?>
        <?php if ($item_image) { ?>
        <div class="item-image">
          <a href="<?php echo $item['href']; ?>">
            <img src="<?php echo $item['thumb']; ?>" alt="<?php echo $item['name']; ?>">
          </a>
        </div>
        <?php } ?>
        <?php if ($item_heading && $item_img_pos !== 'bottom' || $item_desc || $item_btn || $subitems_status) { ?>
        <div class="info-wrapper">
          <div class="item-info">
            <?php if ($item_heading && $item_img_pos !== 'bottom') { ?>
            <div class="item-heading">
              <a href="<?php echo $item['href']; ?>">
                <?php echo $item['name']; ?>
                <?php if ($count_status && $item['count']) { ?>
                <span class="item-count"><?php echo $item['count']; ?></span>
                <?php } ?>
              </a>
            </div>
            <?php } ?>
            <?php if ($item_desc && $item['item_sd']) { ?>
            <div class="item-description"><?php echo $item['item_sd']; ?></div>
            <?php } ?>
            <?php if ($subitems_status && $item['subitems']) { ?>
            <?php if ($sublist) { ?>
            <ul class="sublist">
              <?php foreach ($item['subitems'] as $subitem) { ?>
              <li><a <?php echo $subitem['active'] ? 'class="active"' : ''; ?> href="<?php echo $subitem['href']; ?>"><?php echo $subitem['name']; ?></a></li>
              <?php } ?>
            </ul>
            <?php } else { ?>
            <div class="subcolumn">
              <?php foreach (array_chunk($item['subitems'], ceil(count($item['subitems'])/$column)) as $subitems) { ?>
              <ul>
                <?php foreach ($subitems as $subitem) { ?>
                <li><a <?php echo $subitem['active'] ? 'class="active"' : ''; ?> href="<?php echo $subitem['href']; ?>"><?php echo $subitem['name']; ?></a></li>
                <?php } ?>
              </ul>
              <?php } ?>
            </div>
            <?php } ?>
            <?php } ?>
          </div>
          <?php if ($item_btn) { ?>
          <div class="item-btn">
            <a class="<?php echo $btn_class; ?>" href="<?php echo $item['href']; ?>"><?php echo $item_btn_text; ?></a>
          </div>
          <?php } ?>
        </div>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
  </div>
</div>
<script type="text/javascript"><!--
<?php if ($items_carousel) { ?>
  var itemscarousel = $('#sc-<?php echo $module; ?> .sc-carousel');
  itemscarousel.sccCarousel({
    margin: <?php echo $item_margin; ?>,
    nav: <?php echo $items_nav; ?>,
    navText: ['<?php echo $items_prev_nav; ?>', '<?php echo $items_next_nav; ?>'],
    navSpeed: <?php echo $items_nav_speed; ?>,
    dots: <?php echo $items_dots; ?>,
    slideBy: 2,
    <?php if ($autoplay) { ?>
    autoplay: true,
    loop: true,
    autoplayTimeout: <?php echo $autoplay_timeout; ?>,
    autoplaySpeed: <?php echo $autoplay_speed; ?>,
    autoplayHoverPause: true,
    <?php } ?>
    mouseDrag: <?php echo $items_drag; ?>,
    responsive: {0:{items:<?php echo $items_xs; ?>}, 768:{items:<?php echo $items_sm; ?>}, 992:{items:<?php echo $items_md; ?>}, 1200:{ items:<?php echo $items_lg; ?>}},
    onRefreshed : function(event) {
      var cw = itemscarousel.width();
      var scitem = $('#sc-<?php echo $module; ?> .sc-carousel').find('.scc-stage').children();
      var itemscount = event.item.count;
      var size = event.page.size;
      if (itemscount < size) {
        scitem.parent().width(cw+<?php echo $item_margin; ?> + 'px');
        scitem.width(((cw+<?php echo $item_margin; ?>)-(itemscount*<?php echo $item_margin; ?>))/itemscount + 'px');
      }
      var iteminfo = scitem.find('.item-info');
      iteminfo.height('auto');
      var imh = 0;
      $(iteminfo).each(function() {
        imh = Math.max(imh, $(this).height());
      }).height(imh);
      <?php if ($item_img_pos == 'bottom') { ?>
        var heading = scitem.find('.item-heading');
        heading.height('auto');
        var hmh = 0;
        $(heading).each(function() {
            hmh = Math.max(hmh, $(this).height());
        }).height(hmh);
      <?php } ?>
    }
  });

  <?php if ($items_mousewheel) { ?>
  itemscarousel.on('mousewheel', '.scc-stage', function(e) {
    if (e.deltaY>0) {
      $(this).trigger('next.scc');
    } else {
      $(this).trigger('prev.scc');
    }
    e.preventDefault();
  });
  <?php } ?>
<?php } else { ?>
  $('#sc-<?php echo $module; ?> .sc-grid').css('margin-right','-<?php echo $item_margin+1; ?>px');
  $('#sс-<?php echo $module; ?> .sc-grid, #sс-<?php echo $module; ?> .sc-heading').css('opacity','0');
  $('#sc-<?php echo $module; ?> .sc-grid').children().css('margin-right','<?php echo $item_margin; ?>px');
  var count = <?php echo $items_lg; ?>;
  function dimension(){
    $('#sc-<?php echo $module; ?> .sc-grid').each(function() {
      var row_width = $(this).parent().width()+<?php echo $item_margin; ?>;
      var item_width = 0;
      if ($(window).width() < 768) {
        item_width = Math.floor(row_width/<?php echo $items_xs; ?>-<?php echo $item_margin; ?>);
        count = <?php echo $items_xs; ?>;
      } 
      if ($(window).width() >= 768 && $(window).width() < 992) {
        item_width = Math.floor(row_width/<?php echo $items_sm; ?>-<?php echo $item_margin; ?>);
        count = <?php echo $items_sm; ?>;
      }
      if ($(window).width() >= 992 && $(window).width() < 1200) {
        item_width = Math.floor(row_width/<?php echo $items_md; ?>-<?php echo $item_margin; ?>);
        count = <?php echo $items_md; ?>;
      }
      if ($(window).width() >= 1200) {
        item_width = Math.floor(row_width/<?php echo $items_lg; ?>-<?php echo $item_margin; ?>);
        count = <?php echo $items_lg; ?>;
      }

      var items = $(this).children();
      items.width(item_width);

      for (var i = 0; i < items.length; i+=count) {
        var row = items.slice(i, i+count);

        // Centering items
        // var iml = 0;
        // row.css('margin-left', iml);
        // if (row.length < count) {
        //   iml = (row_width-(item_width+<?php echo $item_margin; ?>)*row.length)/2;
        // }
        // row.first().css('margin-left', iml);

        // Full Width
        if (row.length < count) {
          row.width(Math.floor(row_width/row.length - <?php echo $item_margin; ?>));
        }

        $(this).fadeIn(300);
        var info = row.find('.item-info');
        info.height('auto');
        var imh = 0;
        $(info).each(function() {
          imh = Math.max(imh, $(this).height());
        }).height(imh);

        <?php if ($item_img_pos == 'bottom') { ?>
          var heading = row.find('.item-heading');
          heading.height('auto');
          var hmh = 0;
          $(heading).each(function() {
            hmh = Math.max(hmh, $(this).height());
          }).height(hmh);
        <?php } ?>
      }
    });
  }
  dimension();
  $(window).resize(dimension);
<?php } ?>
//--></script>