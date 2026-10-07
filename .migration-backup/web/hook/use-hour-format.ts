import { useSettings } from "@/hook/use-settings";

export const useHourFormat = () => {
  const { settings } = useSettings();

  return {
    hourFormat: settings?.hour_format || "HH:mm",
  };
};
