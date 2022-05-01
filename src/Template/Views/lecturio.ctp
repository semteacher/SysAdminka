<div class=" col-sm-6 col-md-6 col-xs-11">
    <?= $this->Form->create($view); ?>
    <fieldset>
        <legend><?php echo $title ?></legend>
        <?php
        echo $this->Form->input('school_id', ['options' => $schools,'class'=>'form-control']);
        echo $this->Form->input('status_id',['options' => $status,'class'=>'form-control']);
        ?>
    </fieldset>
    <br/>
    <?= $this->Form->button(__('Download'),['class'=>'btn btn-success']) ?>
    <?= $this->Form->end() ?>
    <br/>
</div>
