<!DOCTYPE html>
<html>
	<head>
	    <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <link rel="stylesheet" type="text/css" href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-combined.min.css"/>
	    <?php echo $this->assets->outputCss(); ?>
	    <?php echo $this->assets->outputJs(); ?>
		<title>Phalcon PHP Framework</title>
	</head>
	<body>
	<div id="main-container" class="container-fluid">
	<div id="inner-container">
		<?php echo $this->getContent(); ?>
    </div>
    </div>
	</body>
</html>