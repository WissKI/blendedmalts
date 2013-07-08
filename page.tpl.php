<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
   <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
   <title><?php print $head_title ?></title>
   <?php print $head; ?>
   <?php print $styles; ?>
   <?php print $scripts; ?>
</head>
<body class="<?php print $body_classes; ?>">
<!-- CORPUS -->
<div id="wki-bodywrap">
    <!-- HEADER -->
    <div id="wki-header">
        <div id="wki-nav">
            <div id="wki-nav-primary"><?php print theme('links', $primary_links); ?></div>
            <div id="wki-nav-secondary"><?php print theme('links', $secondary_links); ?></div>
        </div>
        <div id="wki-logo"><a href="<?php print $front_page; ?>"><img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" id="wki-logo-image" /></a></div>
        <div id="wki-sitename"><?php print $site_name; ?></div>
        <div id="wki-siteslogan"><?php print $site_slogan; ?></div>
    </div>
    <!-- /HEADER -->
    <!-- BORDERS -->
    <div id="wki-border-left"></div>
    <div id="wki-border-right"></div>
    <!-- /BORDERS -->
    <!-- CORPUS -->
    <div id="wki-corpus">
        <!-- TOPBAR -->
        <div id="wki-topbar">
            <?php if ($title || $maltedtitle): ?>
            <div id="wki-title">
                <?php if (!empty($maltedtitle)) {print $maltedtitle;} else {print $title;} ?>
            </div>
            <?php endif; ?>
            <?php if ($tabs): ?>
                <div id="wki-tabs"><?php print $tabs; ?></div>
            <?php endif; ?>
            <?php if ($breadcrumb): ?>
                <!--<div id="wki-breadcrumb"><?php print $breadcrumb; ?></div>-->
            <?php endif; ?>
            <?php if ($mission): ?>
                <div id="wki-mission"><?php print $mission; ?></div>
            <?php endif; ?>
            <?php if ($help): ?>
                <!--<div id="wki-help"><?php print $help; ?></div>-->
            <?php endif; ?>
            <?php if ($messages): ?>
                <div id="wki-messages"><?php print $messages; ?></div>
            <?php endif; ?>
            <?php print $top ?>
        </div>
        <!-- /TOPBAR -->
        <!-- RIGHTBLOCK -->
        <div id="wki-block-right">
            <?php print $right; ?>
        </div>
        <!-- /RIGHTBLOCK -->
        <!-- LEFTBLOCK -->
        <div id="wki-block-left">
        <?php if ( $pictures ): ?>
            <div id="wki-nodeimages">
                <div class="block-inner">
                    <h2 class="title">Images</h2>
                    <?php print $pictures ?>
                </div>
            </div>
        <?php endif; ?>
            <?php print $left; ?>
        </div>
        <!-- /LEFTBLOCK -->
        <!-- CONTENT -->
	<div id="wki-content">
	    <?php print $content; ?>
        </div>
        <!-- /CONTENT -->
    </div>
    <!-- /CORPUS -->
    <!-- FOOTER -->
    <div id="wki-footer">
        <div id="wki-footer-box">
            <?php print $footerbox; ?>
        </div>
        <div id="wki-footer-text">
            <?php print $footer_message; ?>
            <?php print $footer; ?>
        </div>
    </div>
    <!-- /FOOTER -->
</div>
<!-- /BODYWRAP -->
<?php print $closure; ?>
</body>
</html>
