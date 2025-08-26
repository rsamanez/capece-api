/**
 * Error handling middleware
 */
function errorHandler(err, req, res, next) {
  console.error(err.stack);

  if (err.name === 'ValidationError') {
    return res.status(400).json({
      error: 'validation_error',
      message: err.message
    });
  }

  res.status(500).json({
    error: 'internal_server_error',
    message: 'An unexpected error occurred'
  });
}

/**
 * 404 handler for undefined routes
 */
function notFoundHandler(req, res) {
  res.status(404).json({
    error: 'not_found',
    message: 'Endpoint not found'
  });
}

module.exports = {
  errorHandler,
  notFoundHandler
};
