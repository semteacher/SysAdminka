<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/Pages/display"><?= isset($site_name) ? $site_name : 'Default' ?></a>
    </div>

    <?= $this->element('menu/topmenu');?>
    <?= $this->element('menu/rightmenu');?>
    <!-- /.navbar-collapse -->
</nav>