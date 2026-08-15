<div class="specials index large-10 medium-9 columns">
    <?= $this->Html->link(__('Add new speciality'), ['action' => 'add'],['class'=>'btn btn-success pull-right']) ?>
    <table cellpadding="0" cellspacing="0" class="table table-striped table-bordered table-hover ui-datatable"
           data-global-search="false"
           data-length-change="false"
           data-info="true"
           data-paging="true"
           data-page-length="30">
    <thead>
        <tr>
            <th style="max-width:60px !important;" data-filterable="text" data-sortable="true"><?= __('Special Id') ?></th>
            <th data-filterable="text" data-sortable="true"><?= __('Name') ?></th>
            <th data-filterable="text" data-sortable="true"><?= __('Code') ?></th>
            <th style="max-width:60px !important;" data-filterable="text" data-sortable="true"><?= __('Cont ID') ?></th>
            <th style="max-width:60px !important;" data-filterable="text" data-sortable="true"><?= __('Pnsp ID') ?></th>
            <th style="max-width:60px !important;" data-filterable="text" data-sortable="true"><?= __('Sp ID') ?></th>
            <th class="actions"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($specials as $special): ?>
        <tr>
            <td><?= $this->Number->format($special->special_id) ?></td>
            <td><?= $this->Text->autoParagraph(h($special->name))?></td>
            <td><?= $this->Text->autoParagraph(h($special->code))?></td>
            <td><?= $this->Number->format($special->cont_id) ?></td>
            <td><?= $this->Number->format($special->pnsp_id) ?></td>
            <td><?= $this->Number->format($special->sp_id) ?></td>
            <td class="actions">
                <?= $this->Html->link(__('Edit'), ['action' => 'edit',  $special->special_id],['class'=>'btn btn-warning']) ?>
                <?= $this->Html->link(__('Delete'), ['action' => 'delete', $special->special_id],['class'=>'btn btn-danger']) ?>
            </td>
        </tr>

    <?php endforeach; ?>
    </tbody>
    </table>
</div>
