<?php defined('SYSPATH') or die('No direct script access.'); ?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo UTF8::ucfirst(__('oops,_an_error_occurred')) ;?></title>
    <meta content="">
    <style media="screen" type="text/css">
        html, body, div, dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6,
        pre, code, form, fieldset, legend, input, textarea, p, blockquote,
        th, td {
            margin: 0;
            padding: 0;
        }
        body {  font: 12px Arial,Helvetica,sans-serif;}
        html, body {     height: 100%;}
        .content {
            background: linear-gradient(bottom, #242424 15%, #807E85 47%, #252929 85%);
            background: -o-linear-gradient(bottom, #242424 15%, #807E85 47%, #252929 85%);
            background: -moz-linear-gradient(bottom, #242424 15%, #807E85 47%, #252929 85%);
            background: -webkit-linear-gradient(bottom, #242424 15%, #807E85 47%, #252929 85%);
            background: -ms-linear-gradient(bottom, #242424 15%, #807E85 47%, #252929 85%);
            height: 100%;
            min-height: 100%;
        }

        .error-container {
            background: none repeat scroll 0 0 white;
            border: 3px solid #F2F2F2;
            border-radius: 5px 5px 5px 5px;
            box-shadow: 0 0 0 1px #BFBFBF, 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            position: relative;
            width: 50%;
            top: 30%;
            min-height: 150px;
            color: #8E8E8E;
        }
        .error-container h1 {
            margin: 20px 0 0;
            text-align: center;
        }
        .error-container hr {
            color: #bfbfbf;
            background-color: #bfbfbf;
            width: 90%;
        }
        .error-container div.text {
            font-size: 14px;
            padding: 10px;
            text-indent: 30px;
            
        }
        .error-container div.links {
            display: inline-block;
            margin: 10px 44%;
            position: relative;
        }
        .error-container div.links a{
            margin: 4px;
        }
    </style>
  </head>
  <body>
    <div class="content">
        <div class="error-container">
            <?php 
            echo '<h1>'. UTF8::ucfirst(__('oops,_an_error_occurred')) . '.</h1>';
            
            echo '<hr/>';
            echo '<div class="text">';
                echo  Arr::get( $error, 'message');
            echo '</div>';
            echo '<div class="links">';
                echo HTML::anchor(URL::site('/'), UTF8::ucfirst(__('home')));
                $referer = Arr::get($_SERVER, 'HTTP_REFERER');
                if ( $referer)
                {
                    echo HTML::anchor($referer, UTF8::ucfirst(__('back')));
                }
            echo '</div>';
            ?>
        </div>
    </div>
  </body>
</html>