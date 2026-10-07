import { useState, useEffect } from "react";

/**
 * React hook that detects if the current device is an Apple product
 * @returns {boolean} True if the device is an Apple product (iPhone, iPad, iPod, Mac), false otherwise
 */
export const useIsApple = (): boolean => {
  const [isApple, setIsApple] = useState<boolean>(false);

  useEffect(() => {
    // Check if code is running in browser environment
    if (typeof window !== "undefined" && typeof navigator !== "undefined") {
      const userAgent = navigator.userAgent.toLowerCase();

      // Modern approach to detect Apple devices using only user agent
      const isAppleDevice =
        /iphone|ipad|ipod|macintosh|mac os x|iphone os|ios/.test(userAgent) ||
        // Check for Safari browser as it's only available on Apple devices
        (/safari/.test(userAgent) && !/chrome|chromium|edg/.test(userAgent));

      setIsApple(isAppleDevice);
    }
  }, []);

  return isApple;
};
