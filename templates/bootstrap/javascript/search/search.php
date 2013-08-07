<script type="text/javascript"><!--
function check_form() {
    var error_message = "<?php echo $GLOBALS['osC_Language']->get('js_error'); ?>";
  var error_found = false;
  var error_field;
  var keywords = document.getElementById('keywords').value;
  var pfrom = document.getElementById('pfrom').value;
  var pto = document.getElementById('pto').value;
  var pfrom_float;
  var pto_float;
  var dfrom;
  var dfrom_days = document.getElementById('datefrom_days').value;
  var dfrom_months = document.getElementById('datefrom_months').value;
  var dfrom_years = document.getElementById('datefrom_years').value;
  var dto;
  var dto_days = document.getElementById('dateto_days').value;
  var dto_months = document.getElementById('dateto_months').value;
  var dto_years = document.getElementById('dateto_years').value;
  
  dfrom = Date.UTC(dfrom_years, dfrom_months, dfrom_days);
  dto = Date.UTC(dto_years, dto_months, dto_days);
  
  if (dfrom > dto) {
    error_message = error_message + "* <?php echo $GLOBALS['osC_Language']->get('error_search_to_date_less_than_from_date'); ?>\n";
    error_field = document.getElementById('dateto_days');
    error_found = true;
  }

  if (pfrom.length > 0) {
    pfrom_float = parseFloat(pfrom);
    if (isNaN(pfrom_float)) {
      error_message = error_message + "* <?php echo $GLOBALS['osC_Language']->get('error_search_price_from_not_numeric'); ?>\n";
      error_field = document.getElementById('pfrom');
      error_found = true;
    }
  } else {
    pfrom_float = 0;
  }

  if (pto.length > 0) {
    pto_float = parseFloat(pto);
    if (isNaN(pto_float)) {
      error_message = error_message + "* <?php echo $GLOBALS['osC_Language']->get('error_search_price_to_not_numeric'); ?>\n";
      error_field = document.getElementById('pto');
      error_found = true;
    }
  } else {
    pto_float = 0;
  }

  if ( (pfrom.length > 0) && (pto.length > 0) ) {
    if ( (!isNaN(pfrom_float)) && (!isNaN(pto_float)) && (pto_float < pfrom_float) ) {
      error_message = error_message + "* <?php echo $GLOBALS['osC_Language']->get('error_search_price_to_less_than_price_from'); ?>\n";
      error_field = document.getElementById('pto');
      error_found = true;
    }
  }

  if (error_found == true) {
    alert(error_message);
    error_field.focus();
    return false;
  } else {
    return true;
  }
}

function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=520,height=320,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
