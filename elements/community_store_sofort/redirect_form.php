<?php defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
?>

<p class="alert alert-danger"><strong><?php echo t('SOFORT error'); ?></strong><br /><?php echo $error; ?></p>

<script type="text/javascript">
    $(function () {
        $("#store-checkout-redirect-form").submit(function(e){
            e.preventDefault();
        });

        $("#store-checkout-redirect-form .btn").remove();
    });
</script>