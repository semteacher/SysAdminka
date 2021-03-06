<div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
    <?= $modal_google=='true' ? $this->element('/modal/form_google_ajax') : ''; ?>
    <?= $this->Form->create($student,['id'=>'sync_form', 'enctype'=>'multipart/form-data']);?>
    <?= $this->Flash->render() ?>
    <fieldset>
        <legend><?= __('Options sync') ?></legend>
            <div class="form-group">
                <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>
                                    <?= $this->Html->image("SyncUser.png", [
                                        "alt" => "Sync",
                                        "class"=>"col-xs-12 hidden-xs "
                                    ]);?>
                                <div class="caption">
                                    <h3 class="">Get each student from Contingent</h3>
                                   <p class="alert alert-danger">Time of synchronization can be more than 1 minute</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="all_students" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>

                                <?= $this->Html->image("SyncArchiv.png", [
                                    "alt" => "Sync",
                                    "class"=>"col-xs-12 hidden-xs"
                                ]);?>
                                <div class="caption">
                                    <h3 class="">Sync SysAdmin of archive students</h3>
                                    <p class="alert alert-danger">Time of synchronization can be more than 1 minute</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="archive" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>
                                <?= $this->Html->image("Sync.png", [
                                    "alt" => "Sync",
                                    "class"=>"col-xs-12 hidden-xs"
                                ]);?>
                                <div class="caption">
                                    <h3 class="">Get specialyty from Contingent</h3>
                                    <p class="alert alert-info">Syncs all specialities with status "use" in Contingent</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="special" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>                        
                        <div class="col-sm-6 col-md-3">
                             <div class="thumbnail sync">
                                 <fieldset >
                                 <label>
                                     <?= $this->Html->image("SyncImage.png", [
                                         "alt" => "Sync",
                                         "class"=>"col-xs-12 hidden-xs"
                                     ]);?>
                                     <div class="caption ">
                                         <h3 class="">Get photos from Contingent </h3>
                                         <p class="alert alert-danger">Time of synchronization can be more than 2 minute</p>
                                         <div class="checkbox">
                                             <label>
                                                 <input type="checkbox" name="photo" style="width:50px;height: 40px;">
                                             </label>
                                         </div>
                                     </div>
                                 </label>
                                 </fieldset>
                             </div>
                        </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-md-3">
                         <div class="thumbnail sync">
                             <fieldset>
                                 <label>
                                     <?= $this->Html->image("g.png", [
                                         "alt" => "Sync",
                                         "class"=>"col-xs-12 hidden-xs"
                                     ]);?>
                                     <div class="caption">
                                         <h3 class="">Google Sync</h3>
                                         <p class="alert alert-danger">Time of synchronization can be more than ~5 minute</p>
                                         <div class="checkbox">
                                             <label>
                                                 <input type="checkbox" name="cron_google_send" style="width:50px;height: 40px;">
                                             </label>
                                         </div>
                                     </div>
                                 </label>
                             </fieldset>
                         </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="thumbnail sync">
                            <fieldset >
                                <label>
                                    <?= $this->Html->image("Api.png", [
                                        "alt" => "Sync",
                                        "class"=>"col-xs-12 hidden-xs"
                                    ]);?>
                                    <div class="caption ">
                                        <h3 class="">Send photo to google (API)</h3>
                                        <p class="alert alert-danger">Time of synchronization can be more than ~30 minute</p>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="google_photo" style="width:50px;height: 40px;"><label><span></span></label>
                                            </label>
                                        </div>
                                    </div>
                                </label>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <button type="submit" class="btn btn-success">Start</button>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <legend><?= __('Teachers CSV -> ASU MKR Portal users creation') ?></legend>            
                </div>
                <div class="content">
                    <h4 class="">Format: CSV! Encoding: UTF8! Column headers: 'FIO', 'IPN', 'EMAIL'! Delimiter: ";"!</h4>
                    <div class="upload-frm">
                        <?php //echo $this->Form->create($uploadData, ['type' => 'file']); ?>
                        <?php echo $this->Form->input('file', ['type' => 'file', 'class' => 'form-control']); ?>
                        <?php echo $this->Form->button(__('Upload File and Process Teacher\'s Merging'), ['type'=>'submit', 'class' => 'btn btn-success']); ?>
                        <?php //echo $this->Form->end(); ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <legend><?= __('ASU MKR pre-sync tools') ?></legend>            
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>
                                    <?= $this->Html->image("SyncUser.png", [
                                        "alt" => "Sync",
                                        "class"=>"col-xs-12 hidden-xs "
                                    ]);?>
                                <div class="caption">
                                    <h3 class="">Fill ASUMKR_ID -> GAPS LDB</h3>
                                   <p class="alert alert-danger">Time of synchronization can be more than 2 hours!</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="init_all_students_asumkr" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>
                                    <?= $this->Html->image("SyncUser.png", [
                                        "alt" => "Sync",
                                        "class"=>"col-xs-12 hidden-xs "
                                    ]);?>
                                <div class="caption">
                                    <h3 class="">Create ASU MKR portal NEW users</h3>
                                   <p class="alert alert-danger">Time of synchronization can be more than 1 minute</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="init_asumkr_portal_users" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>
                                    <?= $this->Html->image("SyncUser.png", [
                                        "alt" => "Sync",
                                        "class"=>"col-xs-12 hidden-xs "
                                    ]);?>
                                <div class="caption">
                                    <h3 class="">Fix non-tdmu ASU MKR portal users email</h3>
                                   <p class="alert alert-danger">Time of synchronization can be more than 1 minute</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="fix_asumkr_portal_useremails" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>
                </div>        
            </div>
            <div class="form-group">
                <div class="row">
                    <legend><?= __('ASU MKR Sync options (postponned, do not use)') ?></legend>            
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>
                                <?= $this->Html->image("Sync.png", [
                                    "alt" => "Sync",
                                    "class"=>"col-xs-12 hidden-xs"
                                ]);?>
                                <div class="caption">
                                    <h3 class="">Get specialyty from ASU MKR</h3>
                                    <p class="alert alert-info">Syncs all specialities with status "use" in ASU MKR.</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="specials_asumkr0" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>
                                    <?= $this->Html->image("SyncUser.png", [
                                        "alt" => "Sync",
                                        "class"=>"col-xs-12 hidden-xs "
                                    ]);?>
                                <div class="caption">
                                    <h3 class="">Get each student from ASU MKR</h3>
                                   <p class="alert alert-danger">Time of synchronization can be more than 1 minute</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="all_students_asumkr0" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                             <div class="thumbnail sync">
                                 <fieldset >
                                 <label>
                                     <?= $this->Html->image("SyncImage.png", [
                                         "alt" => "Sync",
                                         "class"=>"col-xs-12 hidden-xs"
                                     ]);?>
                                     <div class="caption ">
                                         <h3 class="">Get photos from ASU MKR </h3>
                                         <p class="alert alert-danger">Time of synchronization can be more than 2 minute</p>
                                         <div class="checkbox">
                                             <label>
                                                 <input type="checkbox" name="photo_asumkr0" style="width:50px;height: 40px;">
                                             </label>
                                         </div>
                                     </div>
                                 </label>
                                 </fieldset>
                             </div>
                        </div>                        
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <legend><?= __('Contingent -> ASU MKR Migration options (postponned, do not use') ?></legend>            
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>

                                <?= $this->Html->image("SyncArchiv.png", [
                                    "alt" => "Sync",
                                    "class"=>"col-xs-12 hidden-xs"
                                ]);?>
                                <div class="caption">
                                    <h3 class="">1: LDB DB structure udgrade</h3>
                                    <p class="alert alert-danger">Time of processing less than 1 minute</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="init_ldb_dbstructure_upgrade0" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>

                                <?= $this->Html->image("SyncUser.png", [
                                    "alt" => "Sync",
                                    "class"=>"col-xs-12 hidden-xs"
                                ]);?>
                                <div class="caption">
                                    <h3 class="">2: LDB Names Clean-up</h3>
                                    <p class="alert alert-danger">Time of processing less than 2 minute</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="ldb_names_cleanup" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>
                </div>
                <div class="row">
                    <legend><?= __('3: Fill All ASU MKR new Faculty / Specialities ID`s before next steps!') ?></legend>            
                </div>
                <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>
                                    <?= $this->Html->image("Sync.png", [
                                        "alt" => "Sync",
                                        "class"=>"col-xs-12 hidden-xs "
                                    ]);?>
                                <div class="caption">
                                    <h3 class="">4: ONLY ONCE! ASU MKR faculty/speciality IDs -> LDB</h3>
                                   <p class="alert alert-danger">Time of synchronization is less than 1 minute</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="init_all_affiliation_asumkr0" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>                        
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>
                                    <?= $this->Html->image("SyncUser.png", [
                                        "alt" => "Sync",
                                        "class"=>"col-xs-12 hidden-xs "
                                    ]);?>
                                <div class="caption">
                                    <h3 class="">5: ONLY ONCE! LDB ContingentID->ASU MKR</h3>
                                   <p class="alert alert-danger">Time of synchronization can be more than 1 minute</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="init_all_students_asumkr0" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="thumbnail sync">
                                <label>
                                    <?= $this->Html->image("SyncUser.png", [
                                        "alt" => "Sync",
                                        "class"=>"col-xs-12 hidden-xs "
                                    ]);?>
                                <div class="caption">
                                    <h3 class="">ONLY ONCE! LDB -> create ASU MKR portal users</h3>
                                   <p class="alert alert-danger">Time of synchronization can be more than 1 minute</p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="init_asumkr_portal_users0" style="width:50px;height: 40px;">
                                        </label>
                                    </div>
                                </div>
                                </label>
                            </div>
                        </div>
                </div>

            </div>
    </fieldset>

    <?= $this->Form->end() ?>

</div>

<script>
    $( "#sync_form" ).submit(function( event ) {
        if ($(this).find( "[name='google_photo']" ).prop( "checked" )==='true'){
            event.preventDefault();

        }

    });


</script>