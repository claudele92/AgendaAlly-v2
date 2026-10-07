import { useMutation } from "@tanstack/react-query";
import { Coordinate } from "@/types/global";
import { useSettings } from "@/hook/use-settings";
import { getGoogleMapsApiKey } from "@/config/integrations";

export const useSearchAddress = () => {
  const { settings } = useSettings();
  return useMutation({
    mutationFn: async (location?: Partial<Coordinate>) => {
      const mapsKey = getGoogleMapsApiKey(settings?.google_map_key, settings?.maps_enabled, settings?.maps_environment_permitted);
      if (!mapsKey) {
        throw new Error(
          "Address lookup is unavailable because Google Maps is disabled for this environment.",
        );
      }
      if (typeof google === "undefined" || !google.maps?.Geocoder) {
        throw new Error("Maps is still loading or the provider failed to initialize. Your saved address is unchanged.");
      }
      if (!Number.isFinite(location?.lat) || !Number.isFinite(location?.lng)) {
        throw new Error("Choose a valid map location first.");
      }
      const result = await new google.maps.Geocoder().geocode({
        location: { lat: Number(location?.lat), lng: Number(location?.lng) },
      });
      if (!result.results.length) throw new Error("No address was found for this location. Enter it manually.");
      // Preserve the native REST-shaped consumer contract, using the
      // referrer-restricted browser SDK instead of a CORS-incompatible REST key.
      return {
        status: "OK",
        results: result.results.map((item) => ({
          ...item,
          geometry: { ...item.geometry, location: {
            lat: item.geometry.location.lat(), lng: item.geometry.location.lng(),
          } },
        })),
      };
    },
  });
};
