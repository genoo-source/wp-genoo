<!DOCTYPE html>
<html lang="en-US" class="no-js gn-customizer-window-wrapper">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $customizer->title; ?></title>
    <?php wp_head(); ?>
    <script type="text/javascript">
        // Turn off nav menus
        if (typeof _wpCustomizeSettings !== 'undefined') {
            _wpCustomizeSettings.activePanels.nav_menus = false;
        }
    </script>
    <style>
        #wp-a11y-speak-assertive,
        #wp-a11y-speak-polite { display: none !important; }
    </style>
</head>
<body class="gn-customizer-window">
<?php wp_footer(); ?>
</body>
</html>