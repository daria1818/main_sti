<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;

$count = Option::get("askaron.settings", "UF_SMS_SYMB_COUNT");
$numOnly = Option::get("askaron.settings", "UF_POPUP_NUM_ONLY") === '1';

$mask = '';

if (intval($count) <= 0) {
  $count = 4;
}
$symb = '';
if ($numOnly) {
  $symb = '9';
} else {
  $symb = '*';
}

$mask = str_repeat($symb, $count);
?>
<a href="#" class="close jqmClose"><i></i></a>
<div class="form">

  <form id="SMS_CONFIRMATION">
    <div class="form_head">
      <h2>Этот номер уже зарегистрирован</h2>
    </div>
    <div class="form_body">
      <div class="form-control">
        <p>Привязанная почта: <?= strip_tags($_REQUEST['email']) ?></p>
        <p>
          <button type="button" class="js-send_sms btn btn-default">Использовать уже имеющуюся</button>
        </p>
        <p>
          <button type="button" class="js-send_sms_email btn btn-default">Использовать новую</button>
        </p>
      </div>
      <div class="js-sms-code form-control" style="display: none">
		  <label><span>Введите код из смс&nbsp;<span class="star">*</span></span></label>
        <label class="error"></label>
        <input name="SMS_CODE" type="text" class="inputtext" required value="" aria-required="true">
      </div>
    </div>
    <div class="form_footer">
      <input style="display:none" type="submit" class="btn btn-default" value="Отправить">
      <p class="js-msg"></p>
    </div>
  </form>

  <script>
    $(document).ready(function() {
      $('.jqmClose').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.jqmWindow').jqmHide();
      });

      $('.popup').jqmAddClose('button[name="web_form_reset"]');
    });
  </script>

  <script>
    function checkSms(email, phoneNumber, code) {
      var query = {
        c: 'manao:sms_confirmation',
        action: 'checkSms',
        mode: 'class'
      };

      var data = {
        email,
        phoneNumber,
        code,
        newEmail: $('#SMS_CONFIRMATION input[name=newEmail]').val() || false
      };

      var request = $.ajax({
        url: '/bitrix/services/main/ajax.php?' + $.param(query, true),
        method: 'POST',
        data
      });

      request.done(function(res) {
        var submitBtn = $('#SMS_CONFIRMATION input[type=submit]');
        if (res.data.success === true) {
          submitBtn.attr('disabled', true);
          window.location.reload();
        } else {
          $('#SMS_CONFIRMATION label.error').text('Неверный код.');
          submitBtn.attr('disabled', false);
        }
      });
    }

    function sendSms(email, phoneNumber) {
      var query = {
        c: 'manao:sms_confirmation',
        action: 'sendSms',
        mode: 'class'
      };

      var request = $.ajax({
        url: '/bitrix/services/main/ajax.php?' + $.param(query, true),
        method: 'POST',
        data: {
          phoneNumber,
          email,
        }
      });

      request.done(function(res) {
        if (res.data.success === false) {
          $('.js-msg').html(res.data.msg);
        } else {
          $('.js-msg').html('');
        }
      });
    }

    $('input[name=SMS_CODE]').inputmask({
      mask: '<?= $mask ?>'
    });

    var email = $('input[name=ORDER_PROP_2]').val();
    var phoneNumber = $('input[name=ORDER_PROP_3]').val();

    $('#SMS_CONFIRMATION .form_body input[name=SMS_NEW_EMAIL]').val(email);

    $('#SMS_CONFIRMATION').submit(function(e) {
      e.preventDefault();
      var code = $('#SMS_CONFIRMATION input[name=SMS_CODE]').val();
      checkSms(email, phoneNumber, code);
    });

    $('.js-send_sms').on('click', function(e) {
      e.preventDefault();
      $(this).parents('.form-control').hide();
      sendSms(email, phoneNumber);
      $('#SMS_CONFIRMATION .js-sms-code').show();
      $('#SMS_CONFIRMATION .form_footer input').show();
    });

    $('.js-send_sms_email').on('click', function(e) {
      e.preventDefault();
      $(this).parents('.form-control').hide();
      $('#SMS_CONFIRMATION').prepend('<input type="hidden" name="newEmail" value="Y">');
      sendSms(email, phoneNumber);
      $('#SMS_CONFIRMATION .js-sms-code').show();
      $('#SMS_CONFIRMATION .form_footer input').show();
    });
  </script>
</div>