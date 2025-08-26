const express = require('express');
const Joi = require('joi');
const { getTrackingInfo } = require('../services/trackingService');
const { validateTrackingNumber } = require('../utils/validators');

const router = express.Router();

// GET /api/v1/tracking/:trackingNumber
router.get('/:trackingNumber', async (req, res, next) => {
  try {
    const { trackingNumber } = req.params;

    // Validate tracking number format
    const { error, value } = validateTrackingNumber(trackingNumber);
    if (error) {
      return res.status(400).json({
        error: 'invalid_tracking_number',
        message: 'Invalid tracking number format',
        trackingNumber
      });
    }

    // Get tracking information
    const trackingInfo = await getTrackingInfo(value);
    
    if (!trackingInfo) {
      return res.status(404).json({
        error: 'tracking_not_found',
        message: 'Tracking number not found',
        trackingNumber
      });
    }

    res.json(trackingInfo);
  } catch (error) {
    next(error);
  }
});

module.exports = router;
