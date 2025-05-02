<?php
function handleError($errno, $errstr, $errfile, $errline) {
    // Log the error
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    
    // Don't show detailed error information in production
    if (ini_get('display_errors')) {
        return false; // Let PHP handle the error
    }
    
    // Show user-friendly error message
    if ($errno == E_USER_ERROR) {
        include_once 'header.php';
        echo '<div class="error-container">';
        echo '<h2>Oops! Something went wrong</h2>';
        echo '<p>We encountered an error processing your request. Please try again later.</p>';
        echo '</div>';
        include_once 'footer.php';
        exit(1);
    }
    
    return true; // Don't execute PHP's internal error handler
}

// Set the custom error handler
set_error_handler('handleError');

// Handle fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && $error['type'] === E_ERROR) {
        handleError($error['type'], $error['message'], $error['file'], $error['line']);
    }
});
?>
