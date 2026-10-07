import { ReactElement, ReactNode } from "react";
import clsx from "clsx";

interface AlertProps {
  icon: ReactElement<any>;
  message: ReactNode;
  type: "success" | "warning" | "error" | "info";
}

const styles = {
  success: "bg-green-600",
  warning: "bg-yellow-600",
  error: "bg-red-600",
  info: "bg-blue-600",
};

export const Alert = ({ icon, message, type }: AlertProps) => (
  <div className={clsx("flex-1 rounded-3xl flex items-center gap-3")}>
    <div
      className={clsx(
        "w-6 h-6 rounded-xl flex items-center justify-center text-white",
        styles[type]
      )}
    >
      {icon}
    </div>
    <div className="text-base text-black dark:text-white">{message}</div>
  </div>
);
