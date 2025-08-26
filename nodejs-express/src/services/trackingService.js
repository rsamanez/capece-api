const { trackingData } = require('../data/mockData');

/**
 * Get tracking information for a package
 * @param {string} trackingNumber - The tracking number
 * @returns {Object|null} Tracking information or null if not found
 */
async function getTrackingInfo(trackingNumber) {
  // In a real implementation, this would query a database or external API
  return trackingData[trackingNumber] || null;
}

module.exports = {
  getTrackingInfo
};
