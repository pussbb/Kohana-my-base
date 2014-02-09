<!doctype html>
<?php $lang = Language::get()->code;?>
<!--[if lt IE 7 ]><html class="ie ie6" lang="<?php echo $lang;?>"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="<?php echo $lang;?>"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="<?php echo $lang;?>"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="<?php echo $lang;?>"> <!--<![endif]-->
<head>
    <meta charset="<?php echo Gettext::$encoding;?>"/>
<?php
echo '<title>'.URL::title($title).'</title>', PHP_EOL;

if ($favicon) {
    echo '<link rel ="shortcut icon" href="' .URL::base(TRUE, FALSE) . $favicon. '" type="image/x-icon" />';
}


foreach($meta as $key => $attr) {
    echo '<meta '.HTML::attributes($attr).' />';
}
?>
<script>
    var url_base = '<?php echo URL::base(true, true) ?>';
</script>

<?php
echo Media::render(Base_Media::POSITION_HEAD);
?>

</head>
    <body>
