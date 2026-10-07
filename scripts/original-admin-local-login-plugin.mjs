import { realpathSync, existsSync, readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const workspace = realpathSync(fileURLToPath(new URL('..', import.meta.url)));
const adminRoot = path.join(workspace, '.local/agendaally-preview/admin');
const originalLogin = path.join(workspace, '.migration-backup/admin/src/views/login/index.jsx');
const runtimeLogin = path.join(adminRoot, 'src/views/login/index.jsx');
const originalLogout = path.join(workspace, '.migration-backup/admin/src/context/path-logout.jsx');
const runtimeLogout = path.join(adminRoot, 'src/context/path-logout.jsx');

// User-approved, development-only overlay. It never writes original or runtime
// application files, returns a challenge token, or changes backend auth.
export function isolatedLocalLoginPlugin() {
  let allowed = false;
  return {
    name: 'agendaally-owned-preview-local-login',
    enforce: 'pre',
    configResolved(config) {
      const domain = process.env.REPLIT_DEV_DOMAIN;
      if (config.command !== 'serve' || config.mode !== 'development' ||
          process.env.AGENDAALLY_PREVIEW_LOCAL_LOGIN !== 'approved' ||
          process.env.AGENDAALLY_PREVIEW_INSTALLER_REDIRECT !== 'approved' ||
          !domain || !/^[a-zA-Z0-9.-]+$/.test(domain) ||
          process.env.VITE_BASE_URL !== `https://${domain}:8000` ||
          realpathSync(config.root) !== realpathSync(adminRoot) ||
          !existsSync(path.join(workspace, '.local/agendaally-preview/backend/.original-http-preview-owned'))) {
        throw new Error('Local login overlay is restricted to the owned development preview and isolated API.');
      }
      allowed = true;
    },
    transform(code, id) {
      const modulePath = id.split('?')[0];
      if (modulePath !== runtimeLogin && modulePath !== runtimeLogout) return null;
      const originalPath = modulePath === runtimeLogin ? originalLogin : originalLogout;
      if (!allowed || code !== readFileSync(originalPath, 'utf8')) {
        throw new Error('Local login overlay refused changed or unapproved original login source.');
      }
      const replacements = modulePath === runtimeLogout ? [
        [".catch(() => {\n        navigate('/welcome');",
          `.catch((error) => {
        // Approved local preview exception: the installer stays disabled.
        // Do not hide network errors or any other installer response.
        if (error?.config?.url === 'install/init/check' &&
            error?.response?.status === 404 &&
            (error?.response?.data?.message === 'Installer endpoints are disabled.' ||
             (error?.response?.data?.status === false &&
              error?.response?.data?.statusCode === 'ERROR_404'))) {
          return;
        }
        navigate('/welcome');`],
      ] : [
        ["import Recaptcha from 'components/recaptcha';", ''],
        ['<Recaptcha onChange={handleRecaptchaChange} />',
          '<p role="note">Isolated local preview: reCAPTCHA is disabled and the disabled-installer redirect is skipped. Installer endpoints remain disabled. Sign in with a synthetic local account; external providers are unavailable.</p>'],
        ['disabled={!Boolean(recaptcha)}', 'disabled={false}'],
      ];
      for (const [before, after] of replacements) {
        if (code.split(before).length !== 2) {
          throw new Error('Original login source changed; local overlay must be reviewed.');
        }
        code = code.replace(before, after);
      }
      return { code, map: null };
    },
  };
}