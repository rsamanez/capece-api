const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const trackingRoutes = require('./routes/tracking');
const { errorHandler, notFoundHandler } = require('./middleware/errorHandlers');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(helmet());
app.use(cors());
app.use(morgan('combined'));
app.use(express.json());

// Routes
app.use('/api/v1/tracking', trackingRoutes);

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'OK', timestamp: new Date().toISOString() });
});

// Error handling
app.use(notFoundHandler);
app.use(errorHandler);

app.listen(PORT, () => {
  console.log(`🚀 Node.js Express server running on port ${PORT}`);
  console.log(`📡 API endpoint: http://localhost:${PORT}/api/v1/tracking/{trackingNumber}`);
});

module.exports = app;
