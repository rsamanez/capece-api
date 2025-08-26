const Joi = require('joi');

const trackingNumberSchema = Joi.string()
  .pattern(/^[A-Z0-9]{10,20}$/)
  .required();

/**
 * Validate tracking number format
 * @param {string} trackingNumber - The tracking number to validate
 * @returns {Object} Joi validation result
 */
function validateTrackingNumber(trackingNumber) {
  return trackingNumberSchema.validate(trackingNumber);
}

module.exports = {
  validateTrackingNumber
};
