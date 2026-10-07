import { RECAPTCHA_CONFIGURED, RECAPTCHA_DISABLED_LOCAL, RECAPTCHASITEKEY } from "configs/app-global";
import ReCAPTCHA from 'react-google-recaptcha';

const Recaptcha = ({ onChange }) => {
  const handleRecaptchaChange = (value) => {
    // Pass the reCAPTCHA response value to the parent component
    onChange(value);
  };

  if (RECAPTCHA_DISABLED_LOCAL) {
    return (
      <div role='status'>
        CAPTCHA is explicitly disabled for local development; authentication still uses the Laravel API.
      </div>
    );
  }

  if (!RECAPTCHA_CONFIGURED) {
    return (
      <div role='status'>
        Login is unavailable because this environment has no reCAPTCHA site key. Configure a domain-restricted site key.
      </div>
    );
  }

  return <ReCAPTCHA sitekey={RECAPTCHASITEKEY} onChange={handleRecaptchaChange} />;
};

export default Recaptcha;