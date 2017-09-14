<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if(!has_site_icon()){ ?>
  <link rel="icon" href="<?php echo TEMPLATE_URI;?>/img/favicon.png">
<?php } ?>
<?php wp_head(); ?>
</head>

<body <?php body_class();?>>

	<header>
		<div class="container">
			<nav class="navbar navbar-expand-lg">
			  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
			    <span class="navbar-toggler-icon"></span>
			  </button>

			  <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
			    <?php
					  display_main_menu();
					?>
			  </div>
			</nav>
		</div>
	</header>
