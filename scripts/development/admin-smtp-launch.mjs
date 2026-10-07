// The only optional transport relaxation in the owned, ordinary Admin preview.
export const DEFAULT_DISABLED_FUNCTIONS = [
  'curl_exec', 'curl_multi_exec', 'fsockopen', 'pfsockopen', 'stream_socket_client',
  'socket_connect', 'mail', 'exec', 'shell_exec', 'passthru', 'popen', 'system',
];

export function adminSmtpLaunchOptions({ enabled, backend, port, environment, published }) {
  if (!enabled) return { directives: [], disabled: DEFAULT_DISABLED_FUNCTIONS.join(',') };
  const on = value => /^(true|1)$/i.test(String(value));
  const mode = (key, fallback) => String(environment[key] ?? fallback).toLowerCase();
  if (published || port !== '8000' || environment.APP_ENV !== 'local'
      || !on(environment.DEVELOPMENT_MODE) || !on(environment.AGENDAALLY_DEVELOPMENT_DATABASE)
      || on(environment.APP_DEBUG) || environment.DB_CONNECTION !== 'sqlite'
      || !['log', 'disabled'].includes(mode('EMAIL_MODE', 'log'))
      || !['log', 'array'].includes(mode('MAIL_MAILER', 'log'))
      || mode('PAYMENT_MODE', 'disabled') !== 'disabled'
      || !['log', 'disabled'].includes(mode('SMS_MODE', 'log'))
      || on(environment.FIREBASE_ENABLED) || on(environment.MAPS_ENABLED)) {
    throw new Error('Normal Admin SMTP testing requires the owned, debug-off, outbound-suppressed local preview.');
  }
  return {
    directives: ['-d', `agendaally.normal_admin_smtp_test_authority=${backend}`],
    // PHPMailer's verified TLS socket only. Other SDK/HTTP/mail/command gates stay shut.
    disabled: DEFAULT_DISABLED_FUNCTIONS.filter(name => name !== 'stream_socket_client').join(','),
  };
}
