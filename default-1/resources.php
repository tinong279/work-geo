<?php
function get_css_js_link()
{
    $output =
        '
<link rel="stylesheet" href="css/w3-4.10.css">
<script src="js/jquery-3.6.0.js"></script>
<script src="js/chart-2.9.4.js"></script>
<link rel="stylesheet" href="css/leaflet-1.9.3.css">
<script src="js/leaflet-1.9.3.js"></script>
';
    echo $output;
}
