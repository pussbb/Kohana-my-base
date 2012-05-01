<?php
// from https://github.com/synapsestudios/kohana-errors
class Error
{

    public $type = NULL;
    public $code = NULL;
    public $message = NULL;
    public $file = NULL;
    public $line = NULL;
    public $text = NULL;
    public $trace = array();
    public $display = NULL;

    /**
     * Replaces Kohana's `Kohana::exception_handler()` method. This does the
     * same thing, but also adds email functionality and the ability to perform
     * an action in response to the exception. These actions and emails are
     * customizable per type in the config file for this module.
     *
     * @uses    Kohana::exception_text
     * @param   object   exception object
     * @return  boolean
     */
    public static function handler(Exception $e)
    {
        try
        {
            $error = new Error();
            // Get the exception information
            $error->type = get_class($e);
            $error->code = $e->getCode();
            $error->message = $e->getMessage();
            $error->file = $e->getFile();
            $error->line = $e->getLine();

            // Create a text version of the exception
            $error->text = $e->getMessage();

            if (Kohana::$is_cli) 
            {
                // Just display the text of the exception
                echo "\n{$error->text}\n";

                return TRUE;
            }

            // Get the exception backtrace
            $error->trace = $e->getTrace();

            if ($e instanceof ErrorException) 
            {
                if (isset(Kohana_Exception::$php_errors[$error->code])) 
                {
                    // Use the human-readable error name
                    $error->code = Kohana_Exception::$php_errors[$error->code];
                }

                if (version_compare(PHP_VERSION, '5.3', '<')) 
                {
                    // Workaround for a bug in ErrorException::getTrace() that exists in
                    // all PHP 5.2 versions. @see http://bugs.php.net/bug.php?id=45895
                    for ($i = count($error->trace) - 1; $i > 0; --$i)
                    {
                        if (isset($error->trace[$i - 1]['args'])) 
                        {
                            // Re-position the args
                            $error->trace[$i]['args'] = $error->trace[$i - 1]['args'];

                            // Remove the args
                            unset($error->trace[$i - 1]['args']);
                        }
                    }
                }
            }
            /// need to rewrite next block
            if ( ! headers_sent()) 
            {
                // Make sure the proper content type is sent with a 500 status
                $status = 500;
                if ($error->code == 404)
                    $status = 404;
                header('Content-Type: text/html; charset=' . Kohana::$charset, TRUE, $status);
            }
            if ( Request::$current !== NULL && Request::current()->is_ajax() === TRUE)
            {
                // Just display the text of the exception
                echo "\n{$error->text}\n";
                exit(1);
            }
            
            // Get the contents of the output buffer
            $error->display = $error->render();
            // Log the error
            $error->log();
            // Email the error
            $error->email();
            // Respond to the error
            $error->action();
            return TRUE;
        }
        catch (Exception $e)
        {
            // Clean the output buffer if one exists
            ob_get_level() and ob_clean();

            // Display the exception text
            echo Kohana_Exception::text($e), "\n";

            // Exit with an error status
            exit(1);
        }
    }

    /**
     * Replace Kohana's `Kohana::shutdown_handler()` method with one that will
     * use our error handler. This is to catch errors that are not normally
     * caught by the error handler, such as E_PARSE.
     *
     * @uses    Error::handler
     * @return  void
     */
    public static function shutdown_handler()
    {
        if (Kohana::$errors AND $error = error_get_last() AND (error_reporting() & $error['type'])) 
        {
            // If an output buffer exists, clear it
            ob_get_level() and ob_clean();

            // Fake an exception for nice debugging
            Error::handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

            // Shutdown now to avoid a "death loop"
            exit(1);
        }
    }

    /**
     * Retrieves the config settings for the exception type, and cascades down
     * to the _default settings if there is nothing relavant to the type.
     *
     * @param   string  $key      The config key
     * @param   mixed   $default  A default value to return
     * @return  mixed
     */
    public function config($key, $default = NULL)
    {
        $config = Kohana::$config->load('error.' . $this->type . ':' . $this->code . '.' . $key);
        $config = !is_null($config) ? $config : Kohana::$config->load('error.' . $this->type . '.' . $key);
        $config = !is_null($config) ? $config : Kohana::$config->load('error._default.' . $key);
        return !is_null($config) ? $config : $default;
    }

    /**
     * Renders an error with a view file. The view library is not used because
     * there is a chance that it will fail within this context.
     * Include the exception HTML cascading system /views/errors/
     *             1. $view
     *             2. error type - folder and error code - php file
     *             3. error type - php file
     *             4. error code - php file
     * @param   string  $view  The view file
     * @return  string
     */
    public function render($view = NULL)
    {
        // Start an output buffer
        ob_start();

        // Import error variables into View's scope
        $error = get_object_vars($this);
        unset($error['display']);
        extract($error);

        if ( Kohana::$environment == Kohana::DEVELOPMENT)
        {
            include Kohana::find_file('views', 'kohana/error');
            return ob_get_clean();
        }

        $file = Kohana::find_file('views', $view);
        $file = !empty($file) ? $file : Kohana::find_file('views', 'errors/' . strtolower($this->type) . '/' . $this->code);
        $file = !empty($file) ? $file : Kohana::find_file('views', 'errors/' . strtolower($this->type));
        $file = !empty($file) ? $file : Kohana::find_file('views', 'errors/' . strtolower($this->code));

        include !empty($file) ? $file : Kohana::find_file('views', 'kohana/error');
        // Get the contents of the output buffer
        return ob_get_clean();
    }

    /**
     * Performs the logging is enabled
     */
    public function log()
    {
        if ($this->config('log', TRUE) AND is_object(Kohana::$log)) 
        {
            Kohana::$log->add(Log::ERROR, $this->text);
        }
    }

    /**
     * Sends the email if enabled
     *
     * @return  void
     */
    public function email()
    {
        $send_mail = $this->config('send_error_notifications', FALSE);

        if ( ! $send_mail || Kohana::$environment == Kohana::DEVELOPMENT)
            return;

        $email_available = class_exists('Mailer');
        if ( ! $email_available) 
        {
            throw new Exception('The email functionality of the Synapse Studios Error module requires the Synapse Studios Email module.');
        }

        $errors_to_mail = Kohana::$config->load('mailer.default.errors_to_mail_mail', FALSE);
        $from = Kohana::$config->load('mailer.default.options.username', FALSE);
        if ( ! $errors_to_mail || ! $from)
            return;

        $error_msg = 'Code : ' . $this->code . '<br/>';
        $error_msg .= 'Request url: ' . Kohana_Request::detect_uri() . '<br/>';
        if ( isset($_SERVER['HTTP_REFERER'])) {
            $error_msg .= 'Request referrer: ' . $_SERVER['HTTP_REFERER'] . '<br/>';
        }
        if ( isset($_SERVER['HTTP_USER_AGENT'])) {
            $error_msg .= 'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'] . '<br/>';
        }
        $error_msg .= 'message : ' . $this->message . '<br/>';
        $error_msg .= 'file : ' . $this->file . '<br/>';
        $error_msg .= 'line : ' . $this->line . '<br/>';
        $error_msg .= 'trace :<pre>' . print_r($this->trace, TRUE) . '<pre><br/>';

        $status = Mailer::instance()
            ->to($errors_to_mail)
            ->from($from)
            ->subject('Error ' . $this->type . ' at ' . URL::base(TRUE, TRUE))
            ->html($error_msg)
            ->send();
        return $status;
    }

    /**
     * Performs the action set in configuration
     *
     * @return  boolean
     */
    public function action()
    {
        $type = '_action_' . $this->config('action.type', NULL);
        $options = $this->config('action.options', array());
        $this->$type($options);
        return TRUE;
    }

    /**
     * Redirects the user upon error
     *
     * @param   array  $options  Options from config
     * @return  void
     */
    protected function _action_redirect(array $options = array())
    {
        if ($this->code === 'Parse Error') 
        {
            echo '<p><strong>NOTE:</strong> Cannot redirect on a parse error, because it might cause a redirect loop.</p>';
            echo $this->display;
            return;
        }

        $notices_available = (class_exists('Flash') AND method_exists('Flash', 'set'));
        $message = Arr::get($options, 'message', FALSE);
        if ($notices_available AND $message) 
        {
            Flash::set('error', $message);
        }

        $url = Arr::get($options, 'url');
        if ( strpos($url, '://') === FALSE) 
        {
            // Make the URI into a URL
            $url = URL::site($url, TRUE);
        }
        header("Location: $url", TRUE);
        exit;
    }

    /**
     * Displays the error
     *
     * @param   array  $options  Options from config
     * @return  void
     */
    protected function _action_display(array $options = array())
    {
        $view = Arr::get($options, 'view', 'errors/_default');

        $this->display = $this->render($view);

        echo $this->display;
    }

    /**
     * Performs a callback on the error before displaying
     *
     * @param   array  $options  Options from config
     * @return  void
     */
    protected function _action_callback(array $options = array())
    {
        $callback = Arr::get($options, 'callback');
        @list($method,) = Arr::callback($callback);
        if ( is_callable($method)) 
        {
            call_user_func($method, $this);
        }

        echo $this->display;
    }

    /**
     * CatchAll for actions. Just displays the error.
     *
     * @param  string  $method
     * @param  array   $args
     */
    public function __call($method, $args)
    {
        echo $this->display;
    }

    /**
     * This is a demo callback that serves an example for how to use the
     * callback action type.
     *
     * @param  object  $error  The error object
     */
    public static function demo_callback($error)
    {
        $error->display = '<p>THERE WAS AN ERROR!</p>';
    }

}
/*
// ERROR HANDLING SETTINGS
	'_default' => array
	(
		 * LOGGING
		 *
		 * If `log` is TRUE, then the error will be logged. If FALSE, then it
		 * will not be logged.

		'log'    => TRUE,

		 * EMAIL
		 *
		 * If `email` is TRUE, then the default email will be sent. If FALSE,
		 * no email will be sent. If it is a string, then the string will
		 * be treated as a path to a view which will replace the default email.
		'email'  => FALSE,

		 * ACTION
		 *
		 * If `action` is not an array or has an invalid or missing type, then
		 * the error will be displayed just like the normal
		 * `Kohana::exception_handler`. If it is an array, then the specified
		 * action will be taken with the options specified.

		'action' => array
		(
// -----------------------------------------------------------------------------
// EXAMPLE: "display"
// -----------------------------------------------------------------------------
//			'type'    => 'display',
//			'options' => array
//			(
//				// View used to replace the default error display
//				'view'     => 'errors/_default',
//			),

// -----------------------------------------------------------------------------
// EXAMPLE: "callback"
// -----------------------------------------------------------------------------
//			'type'    => 'callback',
//			'options' => array
//			(
//				// Callback to apply to the error (uses `Arr::callback` syntax)
//				'callback' => 'Error::demo_callback',
//			),

// -----------------------------------------------------------------------------
// EXAMPLE: "redirect"
// -----------------------------------------------------------------------------
//			'type'    => 'redirect',
//			'options' => array
//			(
//				// This is where the user will be redirected to
//				'url'     => 'welcome/index',
//
//				// The message to be sent as a Notice (requires Notices module)
//				'message' => 'There was an error which prevented the page you requested from being loaded.',
//			),
*/