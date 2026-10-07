import "./_group.css";
import React, { useState } from "react";
import { Lock, Mail } from "lucide-react";
import {
  Button,
  Card,
  Col,
  Form,
  Input,
  Recaptcha,
  Row,
  Typography,
} from "./_shared/AdminAntdAdapter";
import cls from "./_shared/admin-login.module.css";
import { useTranslation } from "./_shared/translation";

const PROJECT_NAME = "AgendaAlly";
const RECAPTCHA_DISABLED_LOCAL = true;

export function CurrentBusinessLogin() {
  const { t } = useTranslation();
  const [loading] = useState(false);
  const [recaptcha, setRecaptcha] = useState<string | null>(null);
  const [previewMessage, setPreviewMessage] = useState("");
  const settings = { title: "AgendaAlly" };

  const handleRecaptchaChange = (value: string | null) => {
    setRecaptcha(value);
  };

  const handleLogin = (_values: Record<string, string>) => {
    setPreviewMessage("Local preview only — no sign-in request was sent.");
  };

  return (
    <div className={`agendaally-admin-login ${cls.loginContainer}`}>
      <div className="container d-flex flex-column justify-content-center h-100 align-items-end">
        <Row justify="center">
          <Col>
            <Card className="card">
              <div className="my-4 pl-4 pr-4 w-100">
                <div className={`${cls.appBrand} text-center`}>
                  <Typography.Title className={cls.brandLogo}>
                    {settings.title || PROJECT_NAME}
                  </Typography.Title>
                </div>
                <Row justify="center">
                  <Col>
                    <Form
                      name="login-form"
                      layout="vertical"
                      onFinish={handleLogin}
                      style={{ width: "100%", maxWidth: "420px" }}
                    >
                      <Form.Item
                        name="email"
                        label={t("email")}
                        rules={[
                          {
                            required: true,
                            message: "Please enter your email",
                          },
                        ]}
                      >
                        <Input
                          prefix={<Mail className="site-form-item-icon" size={14} />}
                          placeholder={t("example@info.com")}
                          autoComplete="username"
                        />
                      </Form.Item>
                      <Form.Item
                        name="password"
                        label={t("password")}
                        rules={[
                          {
                            required: true,
                            message: "Please enter your password",
                          },
                        ]}
                      >
                        <Input.Password
                          prefix={<Lock className="site-form-item-icon" size={14} />}
                          placeholder={t("password")}
                          autoComplete="current-password"
                        />
                      </Form.Item>
                      <Recaptcha onChange={handleRecaptchaChange} />
                      <Form.Item className="login-input mt-4">
                        <Button
                          type="primary"
                          htmlType="submit"
                          className={cls.loginFormButton}
                          loading={loading}
                          disabled={!RECAPTCHA_DISABLED_LOCAL && !Boolean(recaptcha)}
                        >
                          {t("login")}
                        </Button>
                      </Form.Item>
                      {!RECAPTCHA_DISABLED_LOCAL && !recaptcha && (
                        <Typography.Text role="status">
                          Enter a configured CAPTCHA response before logging in.
                        </Typography.Text>
                      )}
                      {previewMessage && (
                        <Typography.Text role="status" className="local-preview-message">
                          {previewMessage}
                        </Typography.Text>
                      )}
                    </Form>
                  </Col>
                </Row>
              </div>
            </Card>
          </Col>
        </Row>
      </div>
    </div>
  );
}

export default CurrentBusinessLogin;