export function safeEmailSettingForm(data = {}) {
  const { password, ...safe } = data;
  return { ...safe, password: '' };
}

export function emailSettingPayload(values = {}) {
  const {
    smtp_auth, smtp_debug, port, from_to, host, active, from_site, password,
  } = values;
  return {
    smtp_auth, smtp_debug, port, from_to, host,
    active: typeof active === 'boolean' ? Number(active) : active, from_site,
    ...(typeof password === 'string' && password.trim() !== '' ? { password } : {}),
  };
}

export function passwordStatusText(status) {
  if (status === 'configured') return 'Password configured. Leave blank to keep it, or enter a replacement.';
  if (status === 'replacement_required') return 'Legacy credential is blocked. Enter and save a new password, or request the secure in-place transition.';
  if (status === 'unavailable') return 'Credential cannot be decrypted. Enter and save a replacement.';
  return 'No SMTP password configured. Enter and save a password.';
}
