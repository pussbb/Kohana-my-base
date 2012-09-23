<!doctype html>
<html lang="<?php echo Language::get()->code;?>">
<head>
    <meta charset="<?php echo I18n::$encoding;?>"/>
<?php
echo "<title>$title</title>", PHP_EOL;

if ($favicon) {
    echo '<link rel ="shortcut icon" href="' .URL::base(TRUE, FALSE) . $favicon. '" type="image/x-icon" />';
}
$media = Media::instance();
foreach (array('keywords', 'description') as $property) {
    if (!$property)
        continue;
    echo "<meta name=\"$property\" content=\"$property\"/>";
}

foreach ($media->styles() as $file => $type) {
    echo HTML::style($file, array('media' => $type)), PHP_EOL;
}
echo $media->inline_style();
$development_mode = isset(Kohana::$environment) && Kohana::$environment == Kohana::DEVELOPMENT;
?>

<script>
    var development_mode = <?php echo $development_mode ? 'true' : 'false' ?>;
    var url_base = '<?php echo URL::base(true, true) ?>';
</script>

<?php
foreach ($media->scripts() as $file) {
    echo HTML::script($file), PHP_EOL;
}
echo $media->inline_script();
?>
</head>
    <body>