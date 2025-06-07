<?php
http_response_code(200);
echo "OK";
file_put_contents('debug.log', date('Y-m-d H:i:s') . " Healthcheck called\n", FILE_APPEND);
// ... existing code ...
