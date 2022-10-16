<div class=" col-sm-6 col-md-6 col-xs-11">
    <?= $this->Form->create($view); ?>
    <fieldset>
        <legend><?php echo $title ?></legend>
        <?php
        echo $this->Form->input('school_id', ['options' => $schools,'class'=>'form-control']);
        echo $this->Form->input('status_id',['options' => $status,'class'=>'form-control']);
        echo $this->Form->input('grade_level',['options' =>[
            '0' => 'All',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6'
        ],'class'=>'form-control','label'=>'Year of study']);
        ?>
    </fieldset>
    <br/>
    <?= $this->Form->button(__('Download'),['class'=>'btn btn-success']) ?>
    <?= $this->Form->end() ?>
    <br/>
</div>
