<!DOCTYPE html>
<html>
  <head>
    <title>Generic error</title>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.1.1/css/bootstrap-combined.min.css" rel="stylesheet">
    <style>
        body {
            background: #F0F0F0;
            background: -moz-radial-gradient(#F0F0F0, #BEBEBE);
            background: -webkit-radial-gradient(#F0F0F0, #BEBEBE);
            background: -o-radial-gradient(#F0F0F0, #BEBEBE);
            background: -ms-radial-gradient(#F0F0F0, #BEBEBE);
            background: radial-gradient(#F0F0F0, #BEBEBE);
        }
    .alert {
        margin: 10% 10%;
        height: auto;
    }
    .alert pre {
        margin: 5% auto;
    }
    .center {
        text-align: center;
    }
    </style>
  </head>
  <body>
    <div class="main container">
        <div class="alert alert-error">
            <?php
                echo '<pre>';
                echo  Arr::get( $error, 'message');
                echo '</pre>';
            ?>
            <br/>

        </div>
    </div>
   </body>
</html>
