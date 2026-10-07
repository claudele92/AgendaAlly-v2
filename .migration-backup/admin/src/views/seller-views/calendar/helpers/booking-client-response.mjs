export function bookingClientsFromResponse(response) {
  return response?.data;
}

export function bookingClientFromCreateResponse(response) {
  return {
    client: response?.data,
    reusedExisting: response?.reused_existing === true,
  };
}