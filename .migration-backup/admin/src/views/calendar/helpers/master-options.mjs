export const buildMasterOptions = (masters, search = '') => {
  if (!Array.isArray(masters)) {
    return [];
  }

  const query = String(search || '').trim().toLocaleLowerCase();

  return masters
    .filter(
      (master) =>
        master?.id !== null &&
        master?.id !== undefined &&
        master?.id !== '' &&
        (String(master?.firstname || '').trim() ||
          String(master?.lastname || '').trim()),
    )
    .map((master) => {
      const firstname = String(master?.firstname || '').trim();
      const lastname = String(master?.lastname || '').trim();
      const label = [firstname, lastname].filter(Boolean).join(' ');

      return {
        label,
        value: master.id,
        key: master.id,
      };
    })
    .filter(
      (option) =>
        !query || option.label.toLocaleLowerCase().includes(query),
    );
};