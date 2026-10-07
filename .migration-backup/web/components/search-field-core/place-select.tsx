import { useEffect, useMemo, useState } from "react";
import { useCombobox } from "downshift";
import ArrowLeftLineIcon from "remixicon-react/ArrowLeftLineIcon";
import { useTranslation } from "react-i18next";
import { Input } from "@/components/input";
import { useSearch } from "@/context/search";
import { Types } from "@/context/search/search.reducer";
import { Button } from "@/components/button";
import LocationIcon from "@/assets/icons/location";
import SearchIcon from "@/assets/icons/search";
import clsx from "clsx";
import { SearchErrorMessage } from "./place-search-error-message";
import { BoldUserText } from "./bold-user-text";
import { NearbySearch } from "./nearby-search";
import { useSettings } from "@/hook/use-settings";
import { getGoogleMapsApiKey } from "@/config/integrations";

interface PlaceSelectProps {
  closeModal?: () => void;
  onSelect?: () => void;
}

export const PlaceSelect = (props: PlaceSelectProps) => {
  const { settings } = useSettings();
  const mapsApiKey = getGoogleMapsApiKey(settings?.google_map_key, settings?.maps_enabled, settings?.maps_environment_permitted);
  const [mapsLoaded, setMapsLoaded] = useState(false);

  useEffect(() => {
    if (!mapsApiKey) {
      return;
    }

    const updateMapsLoaded = () => {
      setMapsLoaded(typeof google !== "undefined" && !!google.maps?.places);
    };

    updateMapsLoaded();
    window.addEventListener("google-maps-script-loaded", updateMapsLoaded);
    return () => window.removeEventListener("google-maps-script-loaded", updateMapsLoaded);
  }, [mapsApiKey]);

  if (mapsApiKey && mapsLoaded) {
    return <GooglePlaceSelect {...props} />;
  }

  return <ManualPlaceSelect {...props} mapsApiKeyAvailable={!!mapsApiKey} />;
};

const ManualPlaceSelect = ({
  closeModal,
  onSelect,
  mapsApiKeyAvailable,
}: PlaceSelectProps & { mapsApiKeyAvailable: boolean }) => {
  const { t } = useTranslation();
  const { dispatch, state } = useSearch();
  const [value, setValue] = useState(state.location.query || "");

  const handleSelectLocation = () => {
    if (value.trim()) {
      dispatch({
        type: Types.SetLocation,
        payload: { query: value.trim(), geolocation: undefined },
      });
    }
    closeModal?.();
    onSelect?.();
  };

  return (
    <div
      className={clsx(
        "md:pt-6 md:px-5 h-full md:h-auto flex flex-col justify-between md:justify-start",
        !!closeModal && "pb-6"
      )}
    >
      <div>
        {!!closeModal && (
          <button className="md:flex items-center gap-2.5 text-base hidden" onClick={closeModal}>
            <ArrowLeftLineIcon size={24} />
            {t("back")}
          </button>
        )}
        <div className="mb-5 md:mt-5">
          <Input
            fullWidth
            leftIcon={<LocationIcon />}
            value={value}
            onChange={(event) => setValue(event.target.value)}
            label={t("search.services")}
            rightIcon={mapsApiKeyAvailable ? <NearbySearch onSuccess={setValue} /> : undefined}
          />
        </div>
      </div>
      <Button
        onClick={handleSelectLocation}
        size="medium"
        color="black"
        fullWidth
        className="mt-10"
      >
        {t("search")}
      </Button>
    </div>
  );
};

const GooglePlaceSelect = ({ closeModal, onSelect }: PlaceSelectProps) => {
  const { t } = useTranslation();
  const { dispatch, state } = useSearch();
  const [value, setValue] = useState(state.location.query || "");

  const service = new google.maps.places.AutocompleteService();
  const sessionToken = useMemo(
    () => new google.maps.places.AutocompleteSessionToken(),
    [google.maps.places.AutocompleteSessionToken]
  );

  const handlePredictions = (
    predictions: google.maps.places.AutocompletePrediction[] | null,
    status: string
  ) => {
    if (status === "OK") {
      const autocompleteSuggestions = predictions?.map((prediction) => ({
        id: prediction.place_id,
        name: {
          string: prediction.structured_formatting.main_text,
          length: prediction.structured_formatting.main_text_matched_substrings[0].length,
          offset: prediction.structured_formatting.main_text_matched_substrings[0].offset,
        },
        address: {
          string: prediction.structured_formatting.secondary_text,
        },
      }));
      dispatch({
        type: Types.SetSearchPredictions,
        payload: {
          predictions: autocompleteSuggestions,
          status: "OK",
        },
      });
    } else {
      dispatch({
        type: Types.SetSearchPredictions,
        payload: {
          predictions: [],
          status,
        },
      });
    }
  };
  const { getInputProps, getItemProps, getMenuProps } = useCombobox({
    items: state.location.predictions || [],
    onInputValueChange: ({ inputValue }) => {
      setValue(inputValue || "");
      dispatch({
        type: Types.SetLocation,
        payload: { query: inputValue || "", geolocation: undefined },
      });
      if (!inputValue) {
        dispatch({
          type: Types.SetSearchPredictions,
          payload: {
            predictions: [],
            status: "",
          },
        });
        return;
      }
      service.getPlacePredictions(
        {
          input: inputValue || "",
          sessionToken,
        },
        handlePredictions
      );
    },
    itemToString: (item) => item?.name.string || "",
    onSelectedItemChange: (currentState) => {
      setValue(currentState.selectedItem?.name.string || "");
    },
    inputValue: value,
  });

  const handleSelectLocation = () => {
    if (value.length !== 0) {
      dispatch({
        type: Types.SetLocation,
        payload: { query: value || "", geolocation: undefined },
      });
    }
    if (closeModal) {
      closeModal();
    }
  };
  return (
    <div
      className={clsx(
        "md:pt-6 md:px-5 h-full md:h-auto flex flex-col justify-between md:justify-start",
        !!closeModal && "pb-6"
      )}
    >
      <div>
        {!!closeModal && (
          <button className="md:flex items-center gap-2.5 text-base hidden" onClick={closeModal}>
            <ArrowLeftLineIcon size={24} />
            {t("back")}
          </button>
        )}
        <div className="mb-5 md:mt-5">
          <Input
            fullWidth
            leftIcon={<LocationIcon />}
            {...getInputProps()}
            label={t("search.services")}
            rightIcon={<NearbySearch onSuccess={(geoName) => setValue(geoName)} />}
          />
        </div>
        <ul {...getMenuProps()} className="flex flex-col gap-2">
          {state.location?.predictions && state?.location?.predictions?.length > 0
            ? state.location.predictions?.map((item, index) => (
                <li
                  key={item.id}
                  {...getItemProps({
                    item,
                    index,
                  })}
                  className="flex items-center gap-1"
                >
                  <div className="flex items-center justify-center w-10 h-10 aspect-square rounded-full border border-gray-link">
                    <SearchIcon />
                  </div>
                  <div>
                    <p
                      className="text-sm font-medium"
                      dangerouslySetInnerHTML={{ __html: BoldUserText(item.name) }}
                    />
                    <p className="text-xs">{item?.address?.string}</p>
                  </div>
                </li>
              ))
            : null}
        </ul>
        <SearchErrorMessage status={state.location.status} />
      </div>
      <Button
        onClick={() => {
          handleSelectLocation();
          if (onSelect) {
            onSelect();
          }
        }}
        size="medium"
        color="black"
        fullWidth
        className="mt-10"
      >
        {t("search")}
      </Button>
    </div>
  );
};
