"use client";

import React, { CSSProperties, memo, useState } from "react";
import { GoogleMap, useJsApiLoader } from "@react-google-maps/api";
import { defaultLocation } from "@/config/global";
import { IconButton } from "@/components/icon-button";
import NavigationIcon from "@/assets/icons/navigation";
import { LoadingCard } from "@/components/loading";
import { useSettings } from "@/hook/use-settings";
import PlusIcon from "@/assets/icons/plus";
import MinusIcon from "@/assets/icons/minus";
import { getGoogleMapsApiKey } from "@/config/integrations";

interface MapProps extends React.PropsWithChildren {
  center?: {
    lat: number;
    lng: number;
  };
  zoom?: number;
  containerStyles: CSSProperties;
  options?: google.maps.MapOptions;
  onClick?: (e: google.maps.MapMouseEvent) => void;
  onLoad?: (map: google.maps.Map) => void;
  onCenterChange?: () => void;
  findMyLocation?: boolean;
}

const libraries: "places"[] = ["places"];

const LoadedMap: React.FC<MapProps & { apiKey: string }> = ({
    center,
    containerStyles,
    children,
    zoom = 9,
    options,
    onClick,
    onLoad,
    onCenterChange,
    findMyLocation = true,
    apiKey,
  }) => {
    const { settings } = useSettings();
    let defaultCenter = defaultLocation;
    if (settings?.location) {
      const locationArr = settings.location.split(",");
      defaultCenter = { lat: Number(locationArr[0]), lng: Number(locationArr[1]) };
    }
    if (center) {
      defaultCenter = center;
    }
    const [currentCenter, setCurrentCenter] = useState(defaultCenter);
    const [currentZoom, setCurrentZoom] = useState(zoom);

    const { isLoaded, loadError } = useJsApiLoader({
      id: "google-map-script",
      googleMapsApiKey: apiKey,
      libraries,
    });

    const getUserCurrentLocation = () => {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
          setCurrentCenter({ lat: position.coords.latitude, lng: position.coords.longitude });
          setCurrentZoom(14);
        });
      }
    };

    const renderMap = () => (
      <GoogleMap
        mapContainerStyle={containerStyles}
        center={currentCenter}
        onLoad={onLoad}
        mapContainerClassName="!font-sans"
        zoom={currentZoom}
        options={options}
        onClick={onClick}
        onCenterChanged={onCenterChange}
      >
        {children}
        {findMyLocation && (
          <div className="absolute bottom-5 right-5 flex flex-col gap-3">
            <div className="flex flex-col bg-white rounded-[5px] items-center">
              <IconButton
                onClick={() =>
                  setCurrentZoom((prevZoom) => (prevZoom < 15 ? prevZoom + 1 : prevZoom))
                }
                type="button"
                size="large"
              >
                <PlusIcon />
              </IconButton>
              <div className="h-px bg-footerBg w-10/12 bg-opacity-20" />

              <IconButton
                onClick={() =>
                  setCurrentZoom((prevZoom) => (prevZoom > 2 ? prevZoom - 1 : prevZoom))
                }
                type="button"
                size="large"
              >
                <MinusIcon />
              </IconButton>
            </div>
            <IconButton type="button" onClick={getUserCurrentLocation} size="large" color="white">
              <NavigationIcon />
            </IconButton>
          </div>
        )}
      </GoogleMap>
    );

    if (loadError) return <div role="status" style={containerStyles} className="p-4 text-sm">Maps could not initialize. Check the browser-key API/referrer restrictions. Saved addresses remain available.</div>;
    return isLoaded ? renderMap() : <LoadingCard />;
};

export const Map: React.FC<MapProps> = memo((props) => {
  const { settings } = useSettings();
  const apiKey = getGoogleMapsApiKey(settings?.google_map_key, settings?.maps_enabled, settings?.maps_environment_permitted);

  if (!apiKey) {
    return (
      <div
        role="status"
        className="flex items-center justify-center rounded border border-gray-link p-4 text-sm text-gray-field"
        style={props.containerStyles}
      >
        {settings?.maps_enabled === "0"
          ? "Maps are disabled in platform settings. Country and city selection remain available."
          : !settings?.google_map_key?.trim()
            ? "Maps require a configured public browser key."
            : "Maps are not permitted by the current environment policy."}
      </div>
    );
  }

  return <LoadedMap {...props} apiKey={apiKey} />;
});
