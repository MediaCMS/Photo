<?php

    echo idn_to_utf8($_SERVER['HTTP_HOST']) . '<br />';
    
    echo '<pre>' . print_r($_SERVER, true) . '</pre>';