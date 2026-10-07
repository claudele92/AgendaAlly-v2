export function getRequestErrorMessage(error, translate = (message) => message) {
  const params = error?.response?.data?.params;
  const parameterMessage = params
    ? Object.values(params)
        .map((value) => (Array.isArray(value) ? value[0] : value))
        .find((value) => typeof value === 'string' && value.trim())
    : undefined;
  const responseMessage =
    parameterMessage || error?.response?.data?.message;

  if (typeof responseMessage === 'string' && responseMessage.trim()) {
    const translated = translate(responseMessage);
    if (typeof translated === 'string' && translated.trim()) return translated;
    return responseMessage;
  }

  if (typeof error?.message === 'string' && error.message.trim()) {
    return error.message;
  }

  if (error?.response?.status) {
    return `Request failed with status ${error.response.status}.`;
  }

  return 'The request failed. Please try again.';
}