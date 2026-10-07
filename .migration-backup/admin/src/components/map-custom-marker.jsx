import React from 'react';
import GoogleMapReact from 'google-map-react';
import { shallowEqual, useSelector } from 'react-redux';
import getMapApiKey from 'helpers/getMapApiKey';
import MapUnavailable from './map-unavailable';

export default function MapCustomMarker({ center, handleLoadMap, children }) {
  const { google_map_key } = useSelector(
    (state) => state.globalSettings.settings,
    shallowEqual,
  );
  const mapApiKey = getMapApiKey(google_map_key);

  if (!mapApiKey) {
    return <MapUnavailable />;
  }

  return (
    <GoogleMapReact
      bootstrapURLKeys={{
        key: mapApiKey,
      }}
      defaultZoom={12}
      defaultCenter={center}
      options={{
        fullscreenControl: false,
      }}
      yesIWantToUseGoogleMapApiInternals
      onGoogleApiLoaded={({ map, maps }) => handleLoadMap(map, maps)}
    >
      {children}
    </GoogleMapReact>
  );
}
