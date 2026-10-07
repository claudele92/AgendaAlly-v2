import React, { forwardRef, useId, useState } from "react";
import clsx from "clsx";
import { useTranslation } from "./translation";

const buttonColors = {
  primary: "bg-primary text-[var(--primary-button-font-color)] disabled:bg-gray-placeholder",
  black: "bg-dark text-white disabled:bg-gray-placeholder dark:bg-white dark:text-dark",
  white: "bg-white text-black",
  gray: "bg-gray-segment text-dark",
  blackOutlined: "border border-footerBg",
  whiteOutlined: "border border-white text-white",
  giantsOrange: "bg-giantsOrange text-white disabled:bg-gray-placeholder",
} as const;

const buttonSizes = {
  xsmall: "text-xs font-medium py-2 px-4",
  small: "text-sm font-semibold py-2.5 px-6",
  medium: "text-base font-medium py-[13px] px-8",
  large: "text-base font-semibold py-[18px] md:px-14 px-6",
} as const;

type ButtonProps = {
  as?: React.ElementType;
  size?: keyof typeof buttonSizes;
  color?: keyof typeof buttonColors;
  rounded?: boolean;
  fullWidth?: boolean;
  loading?: boolean;
  disabled?: boolean;
  onClick?: React.MouseEventHandler<HTMLElement>;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  className?: string;
  children?: React.ReactNode;
  [key: string]: unknown;
};

export function Button({
  as: Component = "button",
  color = "primary",
  size = "large",
  rounded,
  fullWidth,
  loading,
  disabled,
  onClick,
  className,
  children,
  leftIcon,
  rightIcon,
  ...props
}: ButtonProps) {
  return (
    <Component
      className={clsx(
        "outline-none focus:outline-none rounded-button overflow-hidden text-ellipsis whitespace-nowrap inline-flex items-center gap-2 justify-center active:translate-y-px hover:brightness-95 focus-ring disabled:cursor-not-allowed disabled:active:translate-y-0 disabled:opacity-50",
        buttonSizes[size],
        rounded && "rounded-full",
        fullWidth && "!w-full",
        buttonColors[color],
        loading && "opacity-70 active:translate-y-0",
        className,
      )}
      onClick={!disabled && !loading ? onClick : undefined}
      disabled={disabled}
      {...props}
    >
      {loading ? (
        <span aria-hidden="true" className="h-6 w-6 animate-spin rounded-full border-2 border-current border-r-transparent" />
      ) : (
        leftIcon
      )}
      {children}
      {rightIcon}
    </Component>
  );
}

export function Link({
  href,
  ...props
}: React.AnchorHTMLAttributes<HTMLAnchorElement> & { href: string }) {
  const localHref = href.startsWith("/") ? `#${href}` : href;
  return <a href={localHref} {...props} />;
}

const iconButtonColors = {
  transparent: "bg-transparent disabled:text-gray-inputBorder",
  black: "bg-black text-white hover:brightness-95 disabled:bg-gray-placeholder",
  primary: "bg-primary text-white hover:brightness-95 disabled:bg-gray-placeholder",
  white: "bg-white text-black hover:brightness-95 disabled:bg-gray-card",
  gray: "bg-dark bg-opacity-60 backdrop-blur-lg",
  lightGray: "bg-gray-segment text-dark",
  blackOutlined: "border border-dark dark:border-white",
  grayOutlined: "border border-gray-link dark:border-white",
  transparentWithHover: "bg-transparent hover:bg-gray-layout disabled:cursor-not-allowed",
} as const;

export function IconButton({
  color = "transparent",
  size = "withoutPadding",
  rounded = false,
  className,
  children,
  ...props
}: React.ButtonHTMLAttributes<HTMLButtonElement> & {
  color?: keyof typeof iconButtonColors;
  size?: "withoutPadding" | "small" | "medium" | "large" | "xlarge";
  rounded?: boolean;
}) {
  const iconSizes = {
    withoutPadding: "ring-offset-2",
    small: "p-1",
    medium: "p-2",
    large: "p-3",
    xlarge: "p-[18px]",
  };
  return (
    <button
      className={clsx(
        "focus-ring outline-none active:translate-y-[1px] aspect-square disabled:active:translate-y-0",
        rounded ? "rounded-button" : "rounded-[5px]",
        iconButtonColors[color],
        iconSizes[size],
        className,
      )}
      {...props}
    >
      {children}
    </button>
  );
}

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  fullWidth?: boolean;
  label?: string;
  error?: string;
  rightIcon?: React.ReactElement | null;
  leftIcon?: React.ReactElement | null;
  status?: "default" | "error" | "success";
  containerClassName?: string;
}

export const Input = forwardRef<HTMLInputElement, InputProps>(
  (
    {
      fullWidth,
      label,
      type,
      className,
      error,
      rightIcon,
      status = "default",
      leftIcon,
      containerClassName,
      required,
      disabled,
      ...props
    },
    ref,
  ) => {
    const [inputType, setInputType] = useState(type || "text");
    const { t } = useTranslation();
    const inputId = useId();

    const handleChangeType = () => {
      setInputType((currentType) => (currentType === "password" ? "text" : "password"));
    };

    return (
      <div className={clsx("relative flex flex-col items-start", fullWidth && "w-full", containerClassName)}>
        <div className={clsx("relative", fullWidth && "w-full")}>
          <input
            ref={ref}
            id={inputId}
            type={inputType}
            autoComplete="off"
            placeholder=" "
            disabled={disabled}
            {...props}
            className={clsx(
              "block px-4 w-full text-sm bg-transparent rounded-button border appearance-none focus:outline-none focus:ring-0 peer",
              fullWidth && "w-full",
              !!rightIcon && "pr-8",
              !!leftIcon && "pl-10",
              label ? "pt-4 pb-[12px]" : "py-[19px]",
              status === "default" && "border-gray-link focus-visible:border-primary",
              status === "error" && "border-badge-product focus-visible:border-red-700",
              status === "success" && "border-green-500 focus-visible:border-red-700",
              disabled && "text-gray-field",
              className,
            )}
          />
          {type === "password" && (
            <div className="absolute inset-y-0 right-0 z-10 flex items-center pr-3">
              <IconButton rounded type="button" onClick={handleChangeType} aria-label={inputType === "password" ? "Show password" : "Hide password"}>
                <i className={inputType === "password" ? "ri-eye-line" : "ri-eye-close-line"} />
              </IconButton>
            </div>
          )}
          {!!leftIcon && <div className="absolute inset-y-0 left-3 z-[4] flex items-center pr-3">{leftIcon}</div>}
          {!!rightIcon && <div className="absolute inset-y-0 right-0 z-[4] flex items-center pr-1.5">{rightIcon}</div>}
          <label
            htmlFor={inputId}
            className={clsx(
              "absolute top-3.5 origin-[0] -translate-y-3 scale-75 transform text-sm text-gray-placeholder duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-3.5 peer-focus:scale-75 peer-focus:text-black dark:peer-focus:text-white",
              leftIcon ? "left-10 rtl:right-10 rtl:left-auto" : "left-4 rtl:right-0 rtl:left-auto",
              disabled && "text-gray-field",
            )}
          >
            {label}
            {required && "*"}
          </label>
        </div>
        {error && <p role="alert" className="mt-1 text-sm text-red">{t(error)}</p>}
      </div>
    );
  },
);
Input.displayName = "Input";

export const PhoneInput = forwardRef<HTMLInputElement, {
  value?: string;
  onChange?: (value: string) => void;
  onBlur?: React.FocusEventHandler<HTMLInputElement>;
  name?: string;
  country?: string;
  error?: string;
}>(({
  value,
  onChange,
  onBlur,
  name,
  country = "us",
  error,
}, ref) => {
  const { t } = useTranslation();
  return (
    <div className="relative" dir="ltr">
      <div className="react-tel-input relative">
        <div className="flag-dropdown absolute inset-y-0 left-0 z-[1] flex items-center pl-3">
          <span className="selected-flag text-sm uppercase" aria-label={`Country ${country}`}>{country}</span>
        </div>
        <input
          ref={ref}
          name={name}
          type="tel"
          value={value || ""}
          onChange={(event) => onChange?.(event.target.value)}
          onBlur={onBlur}
          autoComplete="off"
          placeholder="+1 (702) 123-4567"
          aria-label={t("phone")}
          className="block w-full appearance-none rounded-button border border-gray-inputBorder bg-transparent px-4 !py-[24px] pl-14 text-sm focus:outline-none focus:ring-0 focus-visible:border-primary"
        />
      </div>
      {error && <p role="alert" className="mt-1 text-sm text-red">{t(error)}</p>}
    </div>
  );
});
PhoneInput.displayName = "PhoneInput";

export function InputTypeChanger({
  value,
  onChange,
}: {
  value: "email" | "phone";
  onChange: (type: "email" | "phone") => void;
}) {
  const { t } = useTranslation();
  return (
    <div className="relative grid grid-cols-2 rounded-2xl border border-gray-inputBorder p-1">
      <div
        className={clsx(
          "absolute top-1 h-[calc(100%-8px)] w-1/2 rounded-[14px] bg-primary transition-transform duration-200",
          value === "email" ? "translate-x-1" : "translate-x-[calc(100%-4px)]",
        )}
      />
      <button
        type="button"
        className={clsx(
          "z-[1] flex items-center justify-center gap-x-2.5 px-4 py-3 transition-colors duration-200",
          value === "email" && "text-white",
        )}
        onClick={() => onChange("email")}
      >
        <i className="ri-mail-fill text-xl" aria-hidden="true" />
        <span className="line-clamp-1 text-base leading-base tracking-[-2%] sm:text-lg sm:font-medium">{t("email")}</span>
      </button>
      <button
        type="button"
        className={clsx(
          "z-[1] flex items-center justify-center gap-x-2.5 px-4 py-3 transition-colors duration-200",
          value === "phone" && "text-white",
        )}
        onClick={() => onChange("phone")}
      >
        <i className="ri-phone-fill text-xl" aria-hidden="true" />
        <span className="line-clamp-1 text-base leading-base tracking-[-2%] sm:text-lg sm:font-medium">{t("phone")}</span>
      </button>
    </div>
  );
}