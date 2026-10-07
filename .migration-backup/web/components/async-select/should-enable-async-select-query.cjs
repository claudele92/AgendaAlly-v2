/**
 * @param {{
 *   queryKey?: string;
 *   queryEnabled?: boolean;
 *   isInputFocused: boolean;
 *   isMobile: boolean;
 *   isMobileDrawerOpen: boolean;
 * }} state
 */
const shouldEnableAsyncSelectQuery = ({
  queryKey,
  queryEnabled,
  isInputFocused,
  isMobile,
  isMobileDrawerOpen,
}) => {
  if (!queryKey || queryEnabled === false) {
    return false;
  }

  return queryEnabled === true || isInputFocused || (isMobile && isMobileDrawerOpen);
};

module.exports = shouldEnableAsyncSelectQuery;
