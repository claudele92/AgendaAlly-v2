export function resolveServiceMediaUrl<T>(
  src: T,
  config?: {
    nodeEnvironment?: string;
    developmentMode?: string;
    appEnvironment?: string;
    imageBaseUrl?: string;
  },
): T;