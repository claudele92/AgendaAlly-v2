import React from 'react';

const message =
  'Maps are unavailable because no API key is configured or Maps are disabled for this environment. Enable Maps and configure its environment-specific public API key.';

export default function MapUnavailable({ height = 400, inline = false }) {
  if (inline) {
    return (
      <span role='status' className='text-muted d-block mt-1'>
        {message}
      </span>
    );
  }

  return (
    <div
      className='map-container d-flex align-items-center justify-content-center'
      style={{ height, width: '100%' }}
      role='status'
    >
      {message}
    </div>
  );
}