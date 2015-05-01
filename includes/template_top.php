<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $oscTemplate->buildBlocks();

  if (!$oscTemplate->hasBlocks('boxes_column_left')) {
    $oscTemplate->setGridContentWidth($oscTemplate->getGridContentWidth() + $oscTemplate->getGridColumnWidth());
  }

  if (!$oscTemplate->hasBlocks('boxes_column_right')) {
    $oscTemplate->setGridContentWidth($oscTemplate->getGridContentWidth() + $oscTemplate->getGridColumnWidth());
  }
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta charset="<?php echo CHARSET; ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo tep_output_string_protected($oscTemplate->getTitle()); ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">

<link href="ext/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="custom.css" rel="stylesheet">
<link href="user.css" rel="stylesheet">

<!--[if lt IE 9]>
   <script src="ext/js/html5shiv.js"></script>
   <script src="ext/js/respond.min.js"></script>
   <script src="ext/js/excanvas.min.js"></script>
<![endif]-->
 
<script src="ext/jquery/jquery-1.11.1.min.js"></script>
<script>
function refreshVariants() {
  var price = null;
  var availability = null;
  var model = null;

  for (c in combos) {
    id = null;

    variants_loop:
    for (group_id in combos[c]['values']) {
      for (value_id in combos[c]['values'][group_id]) {
        if (document.getElementById('variants_' + group_id) != undefined) {
          if (document.getElementById('variants_' + group_id).type == 'select-one') {
            if (value_id == document.getElementById('variants_' + group_id).value) {
              id = c;
            } else {
              id = null;

              break variants_loop;
            }
          }
        } else if (document.getElementById('variants_' + group_id + '_1') != undefined) {
          j = 0;

          while (true) {
            j++;

            if (document.getElementById('variants_' + group_id + '_' + j).type == 'radio') {
              if (document.getElementById('variants_' + group_id + '_' + j).checked) {
                if (value_id == document.getElementById('variants_' + group_id + '_' + j).value) {
                  id = c;
                } else {
                  id = null;

                  break variants_loop;
                }
              }
            }

            if (document.getElementById('variants_' + group_id + '_' + (j+1)) == undefined) {
              break;
            }
          }
        }
      }
    }

    if (id != null) {
      break;
    }
  }

  if (id != null) {
	$('button.add-cart').prop('disabled', false);	
    $('#productInfoAvailability').parent().removeClass('danger');

    price = combos[id]['price'];
    availability = productInfoAvailability;
    model = combos[id]['model'];
  } else {
	
	$('button.add-cart').prop('disabled', true);	
	$('#productInfoAvailability').parent().addClass('danger');
    
	price = originalPrice;
    availability = productInfoNotAvailable;
    model = '';
  }

  document.getElementById('productInfoPrice').innerHTML = price;
  document.getElementById('productInfoAvailability').innerHTML = availability;
  document.getElementById('productInfoModel').innerHTML = model;
}		
</script>
<!-- font awesome -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

<?php echo $oscTemplate->getBlocks('header_tags'); ?>
</head>
<body>

  <?php echo $oscTemplate->getContent('navigation'); ?>
  
  <div id="bodyWrapper" class="<?php echo BOOTSTRAP_CONTAINER; ?>">
    <div class="row">

      <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

      <div id="bodyContent" class="col-md-<?php echo $oscTemplate->getGridContentWidth(); ?> <?php echo ($oscTemplate->hasBlocks('boxes_column_left') ? 'col-md-push-' . $oscTemplate->getGridColumnWidth() : ''); ?>">
