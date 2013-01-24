<?php defined('SYSPATH') or die('No direct script access.');

class HTTP_Exception extends Kohana_HTTP_Exception {

    /**
     * Generate a Response for all Exceptions without a more specific override
     *
     * The user should see a nice error page, however, if we are in development
     * mode we should show the normal Kohana error page.
     *
     * @return Response
     */
    public function get_response()
    {
        // Lets log the Exception, Just in case it's important!
        Kohana_Exception::log($this);

        if (Kohana::$environment >= Kohana::DEVELOPMENT)
        {
            // Show the normal Kohana error page.
            return parent::get_response();
        }
        else
        {
            // Generate a nicer looking "Oops" page.
            $view = View::factory($this->get_view_file());
            $response = Response::factory()
                ->status($this->getCode())
                ->body($view->render());

            return $response;
         }
    }

    private function get_view_file()
    {
      $view_files = array(
        'http/'. $this->getCode(),
        'http',
        $this->getCode(),
        'default'
      );
      foreach($view_files as $view_file)
      {
          $_view_file = 'errors/'.$view_file;
          if ((bool)Kohana::find_file('views', $_view_file))
            return $_view_file;
      }
    }
}
