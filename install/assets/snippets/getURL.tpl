//<?php
/**
 * getURL
 * 
 * Page URL
 *
 * @category snippet
 * @version 1.0
 * @internal @modx_category Content
 */
    
echo '[(site_url)]' . substr($_SERVER['REQUEST_URI'], 1);