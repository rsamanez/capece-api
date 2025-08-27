/**
 * Package Tracking API - Node.js Express Implementation
 * Created by: Rommel Samanez Carrillo
 * Donated by: BOSS.TECHNOLOGIES (https://boss.technologies)
 * License: MIT
 */

const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const path = require('path');
const trackingRoutes = require('./routes/tracking');
const evidenceRoutes = require('./routes/evidence');
const { errorHandler, notFoundHandler } = require('./middleware/errorHandlers');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(helmet());
app.use(cors());
app.use(morgan('combined'));
app.use(express.json());

// Serve static files for evidence
app.use('/uploads', express.static(path.join(__dirname, '..', 'uploads')));

// Routes
app.use('/api/v1/tracking', trackingRoutes);
app.use('/api/v1/tracking', evidenceRoutes);

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
  console.log(`📎 Evidence endpoint: http://localhost:${PORT}/api/v1/tracking/{trackingNumber}/evidence`);
  console.log(`📁 Evidence files: http://localhost:${PORT}/uploads/`);
});

module.exports = app;
