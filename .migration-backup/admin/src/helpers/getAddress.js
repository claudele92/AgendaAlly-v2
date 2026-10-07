import axios from 'axios';
import getMapApiKey from 'helpers/getMapApiKey';

export default async function getAddress(
  address,
  key = getMapApiKey()
) {
  if (!key) {
    throw new Error('Address lookup is unavailable because Maps is disabled for this environment.');
  }
  let params = {
    address,
    key,
  };
  return axios
    .get(`https://maps.googleapis.com/maps/api/geocode/json`, { params })
    .then(({ data }) => data.results[0])
    .catch((error) => {
      return undefined;
    });
}
