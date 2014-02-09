<?php defined('SYSPATH') or die('No direct script access.');
$media = Base_Media::instance();
foreach ($media->styles($position) as $file => $data) {
    echo HTML::style($file, $data), PHP_EOL;
}

echo $media->inline_style($position);

foreach($media->js_templates($position) as $name => $data) {
    echo '<script id="'.$name.'" type="text/template">'.$data.'</script>',PHP_EOL;
}

foreach ( $media->scripts($position) as $file => $data) {
    echo HTML::script($file, $data), PHP_EOL;
}

echo  $media->inline_script($position);


