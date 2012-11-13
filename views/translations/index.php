<?php defined('SYSPATH') or die('No direct script access.'); ?>

<h3 class="well">
    <?php echo UTF8::ucfirst(tr('source code translations')); ?>
</h3>
<hr/>
<?php
$button =  '<button class="btn btn-success" type="button">';
$button .= '<i class="icon-refresh"></i>&nbsp;'.UTF8::ucfirst(tr('load translations from sources'));
$button .= '</button>';
    echo HTML::anchor(
        "translations/parse_sources",
        $button,
        array('id' => 'update_from_sources')
    );
echo '&nbsp;';
$button =  '<button class="btn btn-warning" type="button">';
$button .= '<i class="icon-resize-small"></i>&nbsp;'.UTF8::ucfirst(tr('compile translations'));
$button .= '</button>';
    echo HTML::anchor(
        "translations/compile_translations",
        $button,
        array('id' => 'compile_translations')
    );
?>

<div class="btn-group right">
    <button id="show-all" class="btn"><?php echo UTF8::ucfirst(tr('show all'));?></button>
    <button data-toggle="dropdown" class="btn dropdown-toggle">
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <li id="move-not-translated-up"><a href="#"><?php echo UTF8::ucfirst(tr('move not translated up'));?></a></li>
        <li id="show-translated"><a href="#"><?php echo UTF8::ucfirst(tr('translated'));?></a></li>
        <li id="show-not-translated"><a href="#"><?php echo UTF8::ucfirst(tr('not translated'));?></a></li>
    </ul>
</div>
<div class="clear"></div>
<table id="translations-table" class="translation">
    <thead>
        <tr>
            <th>
              <?php echo UTF8::ucfirst(tr('identifier')); ?>
            </th>
            <?php
            foreach ($languages as $language) {
                echo '<th>';
                echo UTF8::ucfirst(tr($language->name));
                echo '</th>';
            } ?>
        </tr>
      </thead>
      <tbody>
          <?php
          ksort($translations);
          foreach ($translations as $identifier => $translation_data)
          {
              echo '<tr>';
                echo '<td class="identifier" title="'.$identifier.'">';
                        echo $identifier;
              echo '</td>';
              $files = Arr::get($translation_data, 'files', array());
              $traslations = Arr::get($translation_data, 'translations', array());
              foreach($languages as $language)
              {
                  echo '<td>';
                    echo '<div class="editable-area" data-identifier="'.$identifier.'" data-language-id="'.$language->id.'">';
                        echo '<span class="translation">';
                            echo Arr::get($traslations, $language->code);
                        echo '</span>';
                        echo '<a class="inline_edit" href="#"><span class="label label-warning"><i class="icon-pencil"></i> '.UTF8::ucfirst(tr('edit')).'</span></a>';
                  echo '</div>';
                  echo '</td>';
              }
              echo '</tr>';
          }
          ?>
    </tbody>
</table>
