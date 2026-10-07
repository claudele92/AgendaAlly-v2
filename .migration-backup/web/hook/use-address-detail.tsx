import { useMutation } from "@tanstack/react-query";
import { useSettings } from "@/hook/use-settings";
import { getGoogleMapsApiKey } from "@/config/integrations";

export const useAddressDetail = () => {
  const { settings } = useSettings();
  return useMutation({
    mutationFn: async (placeId?: string) => {
      const mapsKey = getGoogleMapsApiKey(settings?.google_map_key, settings?.maps_enabled, settings?.maps_environment_permitted);
      if (!mapsKey) {
        throw new Error(
          "Place details are unavailable because Google Maps is disabled for this environment.",
        );
      }
      if (!placeId) throw new Error("Choose a place first.");
      if (typeof google === "undefined" || !google.maps?.places?.PlacesService) {
        throw new Error("Maps is still loading or the provider failed to initialize.");
      }
      return new Promise<{ status: string; result: { formatted_address?: string; geometry: { location: { lat: number; lng: number } } } }>((resolve, reject) => {
        const service = new google.maps.places.PlacesService(document.createElement("div"));
        service.getDetails({ placeId, fields: ["formatted_address", "geometry", "address_components"] }, (result, status) => {
          if (status !== google.maps.places.PlacesServiceStatus.OK || !result?.geometry?.location) {
            reject(new Error("The place lookup was rejected or no location was found. Enter your address manually."));
            return;
          }
          resolve({
            status: "OK",
            result: { ...result, geometry: { location: {
              lat: result.geometry.location.lat(), lng: result.geometry.location.lng(),
            } } },
          });
        });
      });
    },
  });
};
