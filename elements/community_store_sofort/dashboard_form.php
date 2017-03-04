<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
?>
<div class="form-group">
    <?php echo $form->label('sofortCurrency',t("Currency")); ?>
    <?php echo $form->select('sofortCurrency',$currencies,$sofortCurrency?$sofortCurrency:'USD');?>
</div>
<div class="form-group">
    <?php echo $form->label('sofortConfigKey',t("Configuration Key")); ?>
    <input type="text" name="sofortConfigKey" value="<?php echo $sofortConfigKey?>" class="form-control">
</div>

<div class="form-group">
    <?php echo $form->label('sofortReason',t("Transaction Reason")); ?>
    <input type="text" maxlength="27" name="sofortReason" value="<?php echo $sofortReason; ?>" class="form-control" placeholder="<?php echo t('Defaults to site name');?>">
</div>

<div class="form-group">
    <?php echo $form->label('sofortInstruction',t("Payment instruction (optional)")); ?>
    <input type="text" maxlength="27" name="sofortInstruction" value="<?php echo $sofortInstruction; ?>" class="form-control">
</div>