import { setOptions, importLibrary } from '@googlemaps/js-api-loader';
import { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';
import getMapApiKey from './getMapApiKey';

// Every library the app actually uses google.maps.* classes from, across
// both the direct google-maps-react map components (LatLngBounds, Size —
// part of 'maps') and the react-google-autocomplete-based address inputs
// (places.AutocompleteService, places.Autocomplete, places.PlacesService —
// part of 'places'). 'geometry' matches what the map components previously
// requested from google-maps-react's own loader.
const REQUIRED_LIBRARIES = ['maps', 'places', 'geometry'];

let readyPromise = null;
let configuredKey;

// Single shared script load for the whole app. Previously, google-maps-react
// (used by the map components) and react-google-autocomplete (used by every
// address-autocomplete input) each ran their own independent script-loading
// logic with no knowledge of each other. Whichever mounted second would find
// the other's <script> tag already in the DOM and fall back to waiting on
// that tag's native `load` event — but Google's Maps JS API fires `load`
// once its small bootstrap payload finishes executing, *before* requested
// sub-libraries like `places` are actually fetched and attached to
// `window.google.maps`. That race is what let a genuinely-loading page
// reference `google.maps.places.AutocompleteService()` before `google` was
// even defined as a global, crashing the whole page.
//
// google.maps.importLibrary (Google's own recommended "dynamic library
// import" pattern, loaded with the modern `loading=async` script attribute)
// is idempotent and promise-based, so calling this from as many components
// as need it is safe — they all await the same in-flight load.
function getGoogleMapsReadyPromise() {
  const apiKey = getMapApiKey();
  if (!apiKey) return null;
  if (!readyPromise) {
    configuredKey = apiKey;
    setOptions({ key: apiKey, v: 'weekly' });
    readyPromise = Promise.all(
      REQUIRED_LIBRARIES.map((library) => importLibrary(library)),
    );
  } else if (configuredKey !== apiKey) {
    return Promise.reject(
      new Error('Google Maps key changed while the API was loading. Reload the page to apply the new environment.'),
    );
  }
  return readyPromise;
}

export default function useGoogleMapsReady() {
  const [ready, setReady] = useState(false);
  const runtimeSettingsKey = useSelector(
    (state) => state.globalSettings.settings?.google_map_key,
  );
  const apiKey = getMapApiKey(runtimeSettingsKey);

  useEffect(() => {
    let mounted = true;

    setReady(false);
    const promise = getGoogleMapsReadyPromise();
    if (!promise) return undefined;

    promise
      .then(() => {
        if (mounted) {
          setReady(true);
        }
      })
      .catch((error) => {
        // Provider error objects can contain credential-bearing URLs.
        console.error('Google Maps failed to initialize. Check provider restrictions or reload after updating configuration.');
      });

    return () => {
      mounted = false;
    };
  }, [apiKey]);

  return !!apiKey && ready;
}
