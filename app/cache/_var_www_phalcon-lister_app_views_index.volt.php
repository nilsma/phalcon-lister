<!DOCTYPE html>
<html>
	<head>
	    <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <link rel="stylesheet" type="text/css" href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-combined.min.css"/>
	    <?php echo $this->assets->outputCss(); ?>
	    <?php echo $this->assets->outputJs(); ?>
		<title>Lister Application</title>
		<script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

          ga('create', 'UA-40419390-2', 'auto');
          ga('send', 'pageview');

        </script>
	</head>
	<body>
	<div id="main-container" class="container-fluid">
	<div id="inner-container">
		<?php echo $this->getContent(); ?>
    </div>
    </div>
	</body>
</html>