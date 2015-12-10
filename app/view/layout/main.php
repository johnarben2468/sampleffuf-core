<!DOCTYPE html>
<html>
<head>
    <?php $this->section('head'); ?>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="<?php echo $_csrfToken; ?>">
    <base href="<?php echo $_baseurl; ?>">
    <title><?php $this->section('title'); ?><?php echo isset($_title)
            ? $_title : 'App-Name'; ?><?php $this->end(); ?></title>
    <?php $this->section('stylesheets'); ?>



    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css"/>
    <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.css"/>
    <link rel="stylesheet" type="text/css" href="assets/css/zabuto_calendar.css"/>
    <link rel="stylesheet" type="text/css" href="assets/js/gritter/css/jquery.gritter.css"/>
    <link rel="stylesheet" type="text/css" href="assets/lineicons/style.css"/>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css"/>


    <?php $this->end(); ?>
</head>
<body>


    <section id="main-content">
        <section class="wrapper">

            <h3><i class="fa fa-angle-right"></i><?php $this->section('title'); ?><?php echo isset($_title)
                    ? $_title : 'App-Name'; ?><?php $this->end(); ?></h3>


    <?php $this->section('content'); ?>
    <?= $this->getChildBuffer();?>
    <?php $this->end(); ?>
        </section>
    </section>
<?php $this->section('javascripts'); ?>



<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>


<!-- js placed at the end of the document so the pages load faster -->

<script type="text/javascript" src="assets/js/jquery.js"></script>

<script type="text/javascript" src="assets/js/jquery-1.8.3.min.js"></script>

<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>

<script type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>

<script type="text/javascript" src="assets/js/jquery.scrollTo.min.js"></script>

<script type="text/javascript" src="assets/js/jquery.nicescroll.js"></script>

<!--common script for all pages-->

<script type="text/javascript" src="assets/js/jquery.sparkline.js"></script>

<script type="text/javascript" src="assets/js/common-scripts.js"></script>

<script type="text/javascript" src="assets/js/gritter/js/jquery.gritter.js"></script>

<script type="text/javascript" src="assets/js/gritter-conf.js"></script>


<?php $this->end(); ?>
</body>
</html>