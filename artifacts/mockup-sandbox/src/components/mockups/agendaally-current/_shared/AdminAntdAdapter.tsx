import React from "react";

type LayoutProps = React.PropsWithChildren<{
  className?: string;
  justify?: string;
}>;

export function Row({ children, className, justify }: LayoutProps) {
  return <div className={`ant-row ${justify ? `ant-row-${justify}` : ""} ${className || ""}`}>{children}</div>;
}

export function Col({ children, className }: LayoutProps) {
  return <div className={`ant-col ${className || ""}`}>{children}</div>;
}

export function Card({ children, className }: LayoutProps) {
  return (
    <div className={`ant-card ant-card-bordered ${className || ""}`}>
      <div className="ant-card-body">{children}</div>
    </div>
  );
}

type FormProps = React.PropsWithChildren<{
  name?: string;
  layout?: string;
  onFinish: (values: Record<string, string>) => void;
  style?: React.CSSProperties;
}>;

function FormRoot({ name, layout, onFinish, style, children }: FormProps) {
  return (
    <form
      name={name}
      className={`ant-form ${layout === "vertical" ? "ant-form-vertical" : ""}`}
      style={style}
      onSubmit={(event) => {
        event.preventDefault();
        const values = Object.fromEntries(new FormData(event.currentTarget).entries()) as Record<string, string>;
        onFinish(values);
      }}
    >
      {children}
    </form>
  );
}

function FormItem({
  name,
  label,
  rules,
  className,
  children,
}: React.PropsWithChildren<{
  name?: string;
  label?: React.ReactNode;
  rules?: { required?: boolean; message?: string }[];
  className?: string;
}>) {
  const required = Boolean(rules?.some((rule) => rule.required));
  const child = React.isValidElement(children)
    ? React.cloneElement(children as React.ReactElement<Record<string, unknown>>, {
        ...(name ? { name } : {}),
        ...(required ? { required } : {}),
      })
    : children;
  return (
    <div className={`ant-form-item ${className || ""}`}>
      {label && (
        <div className="ant-form-item-label">
          <label className={required ? "ant-form-item-required" : ""}>{label}</label>
        </div>
      )}
      <div className="ant-form-item-control">{child}</div>
    </div>
  );
}

export const Form = Object.assign(FormRoot, { Item: FormItem });

type InputProps = Omit<React.InputHTMLAttributes<HTMLInputElement>, "prefix"> & { prefix?: React.ReactNode };

function TextInput({ prefix, className, ...props }: InputProps) {
  return (
    <span className="ant-input-affix-wrapper">
      {prefix && <span className="ant-input-prefix">{prefix}</span>}
      <input className={`ant-input ${className || ""}`} {...props} />
    </span>
  );
}

function PasswordInput(props: InputProps) {
  return <TextInput {...props} type="password" />;
}

export const Input = Object.assign(TextInput, { Password: PasswordInput });

export function Button({
  type: _variant,
  htmlType = "button",
  className,
  loading,
  disabled,
  children,
  ...props
}: Omit<React.ButtonHTMLAttributes<HTMLButtonElement>, "type"> & {
  type?: "primary" | "default" | "dashed" | "link" | "text";
  htmlType?: "button" | "submit" | "reset";
  loading?: boolean;
}) {
  return (
    <button
      type={htmlType}
      className={`ant-btn ant-btn-primary ${className || ""}`}
      disabled={disabled || loading}
      {...props}
    >
      {loading && <span className="ant-btn-loading-icon" aria-hidden="true" />}
      {children}
    </button>
  );
}

export const Typography = {
  Title: ({ children, className }: React.PropsWithChildren<{ className?: string }>) => (
    <h1 className={`ant-typography ${className || ""}`}>{children}</h1>
  ),
  Text: ({
    children,
    className,
    role,
  }: React.PropsWithChildren<{ className?: string; role?: string }>) => (
    <span className={`ant-typography ${className || ""}`} role={role}>{children}</span>
  ),
};

export const notification = {
  error: ({ message }: { message: string }) => {
    console.info(message);
  },
};

export function Recaptcha(_props: { onChange?: (value: string | null) => void }) {
  return null;
}