import { store } from 'redux/store';
import { getMapsKey } from '../configs/runtime-config.mjs';

const getMapApiKey = (providedKey) => {
  const { google_map_key, maps_enabled, maps_environment_permitted } = store.getState()?.globalSettings?.settings || {};

  return getMapsKey(
    import.meta.env,
    import.meta.env.PROD ? 'production' : 'development',
    providedKey || google_map_key,
    maps_enabled,
    maps_environment_permitted,
  );
};

export default getMapApiKey;
