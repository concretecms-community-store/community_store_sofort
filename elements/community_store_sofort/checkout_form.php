<?php defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
$instruction = \Config::get('community_store_sofort.sofortInstruction');
if (!$instruction) {
    $instruction = t("Click \"Complete Order\" to proceed to the SOFORT website.");
}
?>

<p><?php echo $instruction; ?></p>


<script>
    // 1. Wait for the page to load
    $(function () {
        var form = $('#store-checkout-form-group-payment');
        var submitButton = form.find("[data-payment-method-id=\"<?= $pmID; ?>\"] .store-btn-complete-order");

        form.submit(function (e) {
             submitButton.prop('disabled', true);
        });
    });
</script>