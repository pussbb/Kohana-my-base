<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Translations extends Controller_Core {

    public $languages = NULL;

    protected $check_access = FALSE;

    private $translations = NULL;

    public function before()
    {
        parent::before();
        $this->languages = Model_Language::find_all()->records;
    }

    public function action_index()
    {
        $this->translations = $this->get_translations();
        $this->view->translations = $this->translations;
        $this->view->languages = $this->languages;
    }

    public function action_update()
    {
        $this->render_nothing();
        $language_id = Arr::get($_REQUEST, 'language_id');
        $languages = Collection::hash($this->languages, 'id');
        $language = Arr::get($languages, $language_id);
        if ( ! $language)
            throw new Exception('Wrong language_id');

        $translation = trim(Arr::get($_REQUEST, 'translation'));
        if ( ! $translation)
            return;
        $identifier = Arr::get($_REQUEST, 'identifier');
//        debug($identifier, $translation, $language, TRUE);
        $this->save_translation(
            $identifier,
            $translation,
            $language
        );
    }

    public function action_parse_sources()
    {
        Tools_Language::parse_source();
        $this->redirect('translations');
    }

    public function action_compile_translations()
    {
        Tools_Language::compile_translations();
        $this->redirect('translations');
    }

    private function parse_translations($filename)
    {
        $file = fopen($filename, 'rt');
        $parts = explode("\n\n", fread($file, filesize($filename)));

        //removing header part
        if (isset($parts[0]) && strpos($parts[0], 'Project-Id-Version') !== FALSE)
            unset($parts[0]);

        $result = array();
        foreach ($parts as $key => $part)
        {
            $lines = explode("\n", $part);
            $files = array();
            foreach ($lines as $line)
            {
                if (strpos($line, '#:') === 0)
                {
                    $line = substr($line, 3);
                    $source_file = explode(':', $line);
                    $files[] = array(
                        $source_file[1] => $source_file[0]
                    );
                }
                elseif(strpos($line, 'msgid') === 0)
                {
                    preg_match('/\"(?<id>[^>]*)\"/', $line, $substr);
                    $result[$key]['id'] = Arr::get($substr, 'id', '');
                }
                elseif(strpos($line, 'msgstr') === 0)
                {
                    preg_match('/\"(?<translation>[^>]*)\"/', $line, $substr);
                    $result[$key]['translation'] = Arr::get($substr, 'translation', '');
                }
                elseif(isset($result[$key]['id']) && empty($result[$key]['id']))
                {
                    preg_match('/\"(?<id>[^>]*)\"/', $line, $substr);
                    $result[$key]['id'] = Arr::get($substr, 'id', '');
                }
                $result[$key]['files'] = $files;
            }
        }
        return array_values($result);
    }

    private function get_translations()
    {
        $translations=  array();
        foreach ($this->languages as $language)
        {
            $filename = Gettext::absolute_file_path($language->locale);
            if ( ! file_exists($filename))
                continue;

            $translations[$language->code] = $this->parse_translations($filename);
        }
        $result = array();
        foreach ($translations as $language_code => $data)
        {
            foreach ($data as $value)
            {
                $identifier = Arr::get($value, 'id');
                if ( ! $identifier)
                    continue;
                $files = Arr::get($value, 'files');
                $translation = Arr::get($value, 'translation');
                $result[$identifier]['translations'][$language_code] = $translation;
                $result[$identifier]['files'] = $files;
            }
        }
        return $result;
    }

    private function save_translation($identifier, $translation, $language)
    {
        $pattern = '/msgid "'.$identifier.'"\nmsgstr "(\s\S)*"/';
        $replacement = 'msgid "'.$identifier.'"'."\n".'msgstr "'.$translation.'"';
        File::sed(
            Gettext::absolute_file_path($language->locale),
            $pattern,
            $replacement
        );
    }

}