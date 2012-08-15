<!doctype html>
<html lang="'en'">
<head>
    <meta charset="utf-8">
<?php
echo '<title>', $title, '</title>', PHP_EOL;

if ($favicon) {
    echo '<link rel ="shortcut icon" href="' .URL::base(TRUE, FALSE) . $favicon. '" type="image/x-icon" />';
}

foreach (array('keywords', 'description') as $property) {
    if (!$property)
        continue;
    echo '<meta name="'. $property.'" content="' .$property.'"/>';
}

foreach ($styles as $file => $type) {
    echo HTML::style($file, array('media' => $type)), PHP_EOL;
}
$development_mode = isset(Kohana::$environment) && Kohana::$environment == Kohana::DEVELOPMENT;
?>

<script>
    var development_mode = <?php echo $development_mode ? 'true' : 'false' ?>;
    var url_base = '<?php echo URL::base(true, true) ?>';
</script>

<?php
foreach ($scripts as $file) {
    echo HTML::script($file), PHP_EOL;
}
?>
</head>
    <body>